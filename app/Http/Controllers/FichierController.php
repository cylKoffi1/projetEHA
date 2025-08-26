<?php

namespace App\Http\Controllers;

use App\Services\GridFsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierController extends Controller
{
    public function download(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $row = DB::table('fichiers')->where('id', $id)->first();
        abort_if(!$row, 404);
    
        // Optionnel: contrôle d’accès ici
    
        $grid = app(\App\Services\GridFsService::class);
    
        // Si mime manquant en base, essayer de le lire depuis GridFS
        $mime = $row->mime_type ?: 'application/octet-stream';
        if ($mime === 'application/octet-stream') {
            try {
                $stat = $grid->stat($row->gridfs_id);
                if (!empty($stat['metadata']['mime'])) {
                    $mime = $stat['metadata']['mime'];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    
        // Nettoyer les buffers pour éviter "headers already sent"
        while (ob_get_level() > 0) { @ob_end_clean(); }
    
        return response()->stream(function () use ($grid, $row) {
            try {
                $grid->downloadToOutput($row->gridfs_id);
            } catch (\Throwable $e) {
                \Log::error('GridFS download failed', ['id' => $row->id, 'gridfs_id' => $row->gridfs_id, 'err' => $e->getMessage()]);
                // En cas d’erreur pendant le stream, on coupe proprement
            }
        }, 200, [
            'Content-Type'        => $mime, // <- image/jpeg, image/png, etc.
            'Content-Disposition' => 'inline; filename="'.addslashes($row->filename).'"',
            'Cache-Control'       => 'public, max-age=86400',
            'Content-Length'      => (string)($row->size_bytes ?? ''), // aide certains navigateurs
            'Accept-Ranges'       => 'bytes',
            'X-Accel-Buffering'   => 'no',
        ]);
    }
    
    public function show(int $id): StreamedResponse
    {
        $row = DB::table('fichiers')->where('id', $id)->first();
        abort_if(!$row, 404);

        return response()->stream(function () use ($row) {
            app(GridFsService::class)->downloadToOutput($row->gridfs_id);
        }, 200, [
            'Content-Type' => $row->mime_type,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function destroy(int $id)
    {
        $row = DB::table('fichiers')->where('id', $id)->first();
        abort_if(!$row, 404);

        // TODO: ACL

        app(GridFsService::class)->delete($row->gridfs_id);
        DB::table('fichiers')->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
