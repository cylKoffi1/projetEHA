<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FileProcService
{
    private const ALLOWED_CATEGORIES = ['PHOTO','PLAN','BDC','RECEPTION','FACTURE','OP','DOC_PROJET','REGLEMENT','ATTESTATION','ARMOIRIE','DRAPEAU','AUTRE',
    'AVANCEMENT_PHOTO','INFRA_IMAGE'];
    private const BLOCKED_MIME = ['application/x-msdownload','application/x-dosexec','application/x-sh','application/x-executable'];

    private Client $mongo;
    private string $dbName;
    private string $bucketName;

    public function __construct()
    {
        $this->mongo      = new Client(config('database.mongodb.uri') ?? env('MONGODB_URI'));
        $this->dbName     = env('MONGODB_DB', 'btpfiles');
        $this->bucketName = env('MONGODB_BUCKET', 'fs');
    }

    /**
     * Stocke dans GridFS + enregistre `fichiers`
     * Retourne: ['id','gridfs_id','url','filename','mime','size', 'duplicate?']
     */
    public function handle(array $p): array
    {
        foreach (['owner_type','owner_id','categorie','file'] as $k) {
            if (!array_key_exists($k, $p) || empty($p[$k])) {
                throw new \InvalidArgumentException("Paramètre manquant: $k");
            }
        }
        if (!in_array($p['categorie'], self::ALLOWED_CATEGORIES, true)) {
            throw new \InvalidArgumentException("Catégorie non autorisée: {$p['categorie']}");
        }
        if (!$p['file'] instanceof UploadedFile) {
            throw new \InvalidArgumentException("Le paramètre 'file' doit être un UploadedFile.");
        }

        $file = $p['file'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file->getRealPath()) ?: $file->getMimeType();
        if (in_array($mime, self::BLOCKED_MIME, true)) {
            throw new \RuntimeException("Type de fichier interdit ($mime).");
        }

        $sha256 = hash_file('sha256', $file->getRealPath());
        $md5    = md5_file($file->getRealPath()) ?: null;

        $dup = DB::table('fichiers')
            ->where('owner_type', $p['owner_type'])
            ->where('owner_id',   $p['owner_id'])
            ->where('categorie',  $p['categorie'])
            ->where('sha256',     $sha256)
            ->first();
        if ($dup) {
            return [
                'id'        => (int)$dup->id,
                'gridfs_id' => $dup->gridfs_id,
                'url'       => url('/fichiers/'.$dup->id),
                'filename'  => $dup->filename,
                'mime'      => $dup->mime_type,
                'size'      => (int)$dup->size_bytes,
                'duplicate' => true,
            ];
        }

        $bucket = $this->mongo->selectDatabase($this->dbName)->selectGridFSBucket(['bucketName' => $this->bucketName]);
        $gridId = null;

        DB::beginTransaction();
        try {
            $stream = fopen($file->getRealPath(), 'rb');
            try {
                $gridId = $bucket->uploadFromStream(
                    $file->getClientOriginalName(),
                    $stream,
                    ['metadata' => [
                        'owner_type'  => $p['owner_type'],
                        'owner_id'    => (int)$p['owner_id'],
                        'categorie'   => $p['categorie'],
                        'uploaded_by' => $p['uploaded_by'] ?? null,
                        'mime'        => $mime,
                        'size'        => $file->getSize(),
                        'sha256'      => $sha256,
                    ]]
                );
            } finally { fclose($stream); }

            // désactiver anciennes photos actives si demandé
            if (!empty($p['uniquePerCategory'])) {
                DB::table('fichiers')
                    ->where('owner_type', $p['owner_type'])
                    ->where('owner_id',   $p['owner_id'])
                    ->where('categorie',  $p['categorie'])
                    ->update(['is_active' => 0]);
            }

            $id = DB::table('fichiers')->insertGetId([
                'owner_type'  => $p['owner_type'],
                'owner_id'    => $p['owner_id'],
                'categorie'   => $p['categorie'],
                'filename'    => $file->getClientOriginalName(),
                'mime_type'   => $mime,
                'size_bytes'  => $file->getSize(),
                'md5'         => $md5,
                'sha256'      => $sha256,
                'gridfs_id'   => (string)$gridId,
                'uploaded_by' => $p['uploaded_by'] ?? null,
                'is_active'   => 1,
            ]);

            DB::commit();

            return [
                'id'        => $id,
                'gridfs_id' => (string)$gridId,
                'url'       => url('/fichiers/'.$id),
                'filename'  => $file->getClientOriginalName(),
                'mime'      => $mime,
                'size'      => $file->getSize(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($gridId) { try { $bucket->delete(new ObjectId((string)$gridId)); } catch (\Throwable $ee) {} }
            throw $e;
        }
    }
    public function handlePath(array $p): array
    {
        // $p attendu: owner_type, owner_id, categorie, path, original_name, uploaded_by?
        foreach (['owner_type','owner_id','categorie','path','original_name'] as $k) {
            if (empty($p[$k])) throw new \InvalidArgumentException("Paramètre manquant: $k");
        }
        if (!file_exists($p['path'])) {
            throw new \RuntimeException("Fichier introuvable: {$p['path']}");
        }
    
        // Déduction MIME
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($p['path'])
            ?: MimeTypes::getDefault()->guessMimeType($p['path'])
            ?: 'application/octet-stream';
    
        // On fabrique un UploadedFile “virtuel” pour réutiliser handle()
        $uploaded = new \Illuminate\Http\UploadedFile(
            $p['path'],
            $p['original_name'],
            $mime,
            null,
            true // test mode => pas besoin que ce soit “vraiment” uploadé via HTTP
        );
    
        return $this->handle([
            'owner_type'        => $p['owner_type'],
            'owner_id'          => $p['owner_id'],
            'categorie'         => $p['categorie'],
            'file'              => $uploaded,
            'uploaded_by'       => $p['uploaded_by'] ?? null,
            'uniquePerCategory' => false,
        ]);
    }
    /** Spécifique Acteur/PHOTO — remplace la photo (unique par acteur) */
    public function storeActeurPhoto(int $acteurId, UploadedFile $file, ?int $userId = null): array
    {
        return $this->handle([
            'owner_type'        => 'Acteur',
            'owner_id'          => $acteurId,
            'categorie'         => 'PHOTO',
            'file'              => $file,
            'uploaded_by'       => $userId,
            'uniquePerCategory' => true,
        ]);
    }
}
