<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\GridFS\Bucket;

class GridFsService
{
    private Client $client;
    private Bucket $bucket;
    private string $dbName;
    private string $bucketName;

    public function __construct()
    {
        $uri = config('database.mongodb.uri') ?? env('MONGODB_URI');
        $this->dbName = env('MONGODB_DB', 'btpfiles');
        $this->bucketName = env('MONGODB_BUCKET', 'fs');

        $this->client = new Client($uri);
        $db = $this->client->selectDatabase($this->dbName);
        $this->bucket = $db->selectGridFSBucket(['bucketName' => $this->bucketName]);
    }

    /** @return string Mongo ObjectId (hex) */
    public function uploadStream(string $filename, $stream, array $metadata = []): string
    {
        $opts = [];
        if (!empty($metadata)) $opts['metadata'] = $metadata;
        $id = $this->bucket->uploadFromStream($filename, $stream, $opts);
        return (string)$id;
    }

    public function downloadToOutput(string $id): void
    {
        $out = fopen('php://output', 'wb');
        try {
            $this->bucket->downloadToStream(new \MongoDB\BSON\ObjectId($id), $out);
        } finally {
            fclose($out);
        }
    }
    

    public function delete(string $id): void
    {
        $this->bucket->delete(new ObjectId($id));
    }

    /** Métadonnées fs.files (ou null) */
    public function stat(string $id): ?array
    {
        $file = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($this->bucketName.'.files')
            ->findOne(['_id' => new ObjectId($id)]);
        return $file ? json_decode(json_encode($file), true) : null;
    }

    // app/Services/GridFsService.php

    public function downloadToString(string $id): string
    {
        $tmp = fopen('php://temp', 'w+b');
        $this->bucket->downloadToStream(new \MongoDB\BSON\ObjectId($id), $tmp);
        rewind($tmp);
        $bytes = stream_get_contents($tmp);
        fclose($tmp);
        return $bytes !== false ? $bytes : '';
    }
    public function downloadAsString(string $id): string
    {
        $tmp = fopen('php://temp', 'w+');
        $this->bucket->downloadToStream(new \MongoDB\BSON\ObjectId($id), $tmp);
        rewind($tmp);
        $raw = stream_get_contents($tmp);
        fclose($tmp);
        return $raw ?: '';
    }
    
    /**
     * Stream avec "wrapper" : on te laisse transformer le contenu avant sortie
     * $wrapper reçoit la *string* JSON originale.
     */
    public function streamWithWrapper(string $id, callable $wrapper): void
    {
        $raw = $this->downloadAsString($id);
        $out = fopen('php://output', 'wb');
        try {
            $wrapper($raw, $out); // tu écris ce que tu veux dans $out
        } finally {
            fclose($out);
        }
    }
}
