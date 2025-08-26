<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeojsonImport extends Command
{
    protected $signature = 'geojson:import {alpha3} {dir=public/geojson}';
    protected $description = 'Importe gadm41_{ALPHA3}_{1..3}.json(.js) vers GridFS';

    public function handle()
    {
        $alpha3 = strtoupper($this->argument('alpha3'));
        $dir    = base_path($this->argument('dir'));

        for ($level=0; $level<1; $level++) {
            $candidates = [
                "gadm41_{$alpha3}_{$level}.json.js" => ['format' => 'json.js', 'mime' => 'application/javascript'],
                "gadm41_{$alpha3}_{$level}.json"    => ['format' => 'json',    'mime' => 'application/json'],
            ];
            $found = null;
            foreach ($candidates as $name => $meta) {
                $path = $dir.DIRECTORY_SEPARATOR.$name;
                if (file_exists($path)) { $found = [$path, $name, $meta]; break; }
            }
            if (!$found) { $this->warn("Niveau {$level}: fichier manquant"); continue; }

            [$path, $filename, $meta] = $found;

            $grid = app(\App\Services\GridFsService::class);
            $stream = fopen($path, 'rb');
            $gridId = $grid->uploadStream($filename, $stream, [
                'kind'    => 'geo',
                'country' => $alpha3,
                'level'   => $level,
                'content' => $meta['format'],
                'var'     => 'statesDataLevel'.$level,
            ]);

            DB::table('fichiers')->insert([
                'owner_type' => 'Geo',
                'owner_id'   => $alpha3.'_L'.$level,
                'categorie'  => 'GEOJSON',
                'filename'   => $filename,
                'mime_type'  => $meta['mime'],
                'size_bytes' => filesize($path),
                'md5'        => optional($grid->stat($gridId))['md5'] ?? null,
                'gridfs_id'  => $gridId,
                'uploaded_at'=> now(),
            ]);

            $this->info("Import OK: {$filename}");
        }

        return self::SUCCESS;
    }
}
