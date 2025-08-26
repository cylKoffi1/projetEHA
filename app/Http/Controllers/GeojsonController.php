<?php

namespace App\Http\Controllers;

use App\Services\GridFsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeojsonController extends Controller
{
    /**
     * Upload d’un fichier geojson (.json ou .json.js) pour un pays et un niveau
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'alpha3' => 'required|string|size:3',   // ex: CIV, MDG
            'level'  => 'required|in:0,1,2,3,4',    // 0..4 (tu as L0 & L4)
            'format' => 'required|in:json,json.js', // comment on le servira
            'file'   => 'required|file|max:512000', // 500 Mo max
        ]);

        $file   = $data['file'];
        $alpha3 = strtoupper($data['alpha3']);
        $level  = (int) $data['level'];

        $stream = fopen($file->getRealPath(), 'rb');

        /** @var GridFsService $grid */
        $grid = app(GridFsService::class);

        // Upload vers GridFS avec méta
        $gridId = $grid->uploadStream(
            $file->getClientOriginalName(),
            $stream,
            [
                'kind'    => 'geo',
                'country' => $alpha3,
                'level'   => $level,
                'content' => $data['format'],           // 'json' ou 'json.js'
                'var'     => 'statesDataLevel'.$level,  // utile si wrapping JS
            ]
        );

        // Miroir MySQL
        DB::table('fichiers')->insert([
            'owner_type' => 'Geo',
            'owner_id'   => $alpha3.'_L'.$level,        // VARCHAR (ex: MDG_L1)
            'categorie'  => 'GEOJSON',
            'filename'   => $file->getClientOriginalName(),
            'mime_type'  => $data['format'] === 'json' ? 'application/json' : 'application/javascript',
            'size_bytes' => $file->getSize(),
            'md5'        => optional($grid->stat($gridId))['md5'] ?? null,
            'gridfs_id'  => $gridId,
            'uploaded_by'=> optional($request->user())->id,
            'uploaded_at'=> now(),                      // utilisé pour orderByDesc
            'is_active'  => 1,
        ]);

        return response()->json(['ok' => true, 'gridfs_id' => $gridId], 201);
    }

    /**
     * /geojson/CIV/1.json.js
     * - si le fichier stocké est déjà JS (mime application/javascript) -> on renvoie tel quel
     * - sinon (JSON) -> on wrappe en "var statesDataLevelX = {...};"
     */
    public function serveJs(string $alpha3, int $level): StreamedResponse
    {
        $alpha3 = strtoupper($alpha3);

        $row = DB::table('fichiers')
            ->where('categorie', 'GEOJSON')
            ->where('owner_type', 'Geo')
            ->where('owner_id', $alpha3.'_L'.$level)
            ->orderByDesc('uploaded_at')
            ->first();

        abort_if(!$row, 404, 'GeoJSON non trouvé');

        /** @var GridFsService $grid */
        $grid = app(GridFsService::class);

        // 1) Déjà JS -> renvoie tel quel
        if ($row->mime_type === 'application/javascript') {
            return response()->stream(function () use ($grid, $row) {
                try {
                    $grid->downloadToOutput($row->gridfs_id);
                } catch (\Throwable $e) {
                    Log::error('serveJs download error', ['e' => $e, 'gridfs_id' => $row->gridfs_id]);
                    abort(500, 'Erreur de lecture GridFS');
                }
            }, 200, [
                'Content-Type'      => 'application/javascript; charset=utf-8',
                'Cache-Control'     => 'public, max-age=86400',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        // 2) JSON -> wrap "var statesDataLevelX = ..."
        return response()->stream(function () use ($grid, $row, $level) {
            try {
                // entête une seule fois
                echo "var statesDataLevel{$level} = ";
                // stream du JSON brut
                $grid->streamWithWrapper($row->gridfs_id, function ($raw, $out) {
                    if (is_resource($out)) {
                        fwrite($out, $raw);
                    } else {
                        echo $raw;
                    }
                });
                // fin d'affectation
                echo ";\n";
            } catch (\Throwable $e) {
                Log::error('serveJs wrap error', ['e' => $e, 'gridfs_id' => $row->gridfs_id]);
                abort(500, 'Erreur de lecture GridFS');
            }
        }, 200, [
            'Content-Type'      => 'application/javascript; charset=utf-8',
            'Cache-Control'     => 'public, max-age=86400',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * /geojson/CIV/1.json -> JSON pur
     */
    public function serveJson(string $alpha3, int $level): StreamedResponse
    {
        $alpha3 = strtoupper($alpha3);

        $row = DB::table('fichiers')
            ->where('categorie', 'GEOJSON')
            ->where('owner_type', 'Geo')
            ->where('owner_id', $alpha3.'_L'.$level)
            ->orderByDesc('uploaded_at')
            ->first();

        abort_if(!$row, 404, 'GeoJSON non trouvé');

        return response()->stream(function () use ($row) {
            try {
                app(GridFsService::class)->downloadToOutput($row->gridfs_id);
            } catch (\Throwable $e) {
                Log::error('serveJson stream error', ['e' => $e, 'gridfs_id' => $row->gridfs_id]);
                abort(500, 'Erreur de lecture GridFS');
            }
        }, 200, [
            'Content-Type'      => 'application/json; charset=utf-8',
            'Cache-Control'     => 'public, max-age=86400',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
