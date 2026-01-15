<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeocodeLocalites extends Command
{
    protected $signature = 'geo:localites {--limit=500}';
    protected $description = 'Géocodage automatique des localités avec contrainte pays stricte';

    public function handle()
    {
        $limit = $this->option('limit');

        $localites = DB::table('localites_pays as l')
            ->join('pays as p', 'p.alpha3', '=', 'l.id_pays')
            ->select(
                'l.id',
                'l.libelle',
                'l.code_rattachement',
                'p.nom_en_gb',
                'p.nom_fr_fr',
                'p.alpha2'
            )
            ->where('l.geo_status', 0)
            ->limit($limit)
            ->get();

        foreach ($localites as $loc) {

            $countryCode = strtolower($loc->alpha2);

            // ==========================
            // 1) Tentative avec nom anglais
            // ==========================
            $query = trim($loc->libelle . ', ' . $loc->nom_en_gb);
            $geo = $this->callNominatim($query, $countryCode);

            // ==========================
            // 2) Fallback nom français
            // ==========================
            if (!$geo) {
                $query = trim($loc->libelle . ', ' . $loc->nom_fr_fr);
                $geo = $this->callNominatim($query, $countryCode);
            }

            // ==========================
            // 3) Sauvegarde résultat
            // ==========================
            if ($geo) {

                DB::table('localites_pays')
                    ->where('id', $loc->id)
                    ->update([
                        'latitude'   => $geo['lat'],
                        'longitude'  => $geo['lon'],
                        'geo_status' => 1
                    ]);

                $this->info("OK → {$query}");

            } else {

                DB::table('localites_pays')
                    ->where('id', $loc->id)
                    ->update([
                        'geo_status' => 2
                    ]);

                $this->error("ECHEC → {$loc->libelle}");
            }

        }

        $this->info("Batch terminé.");
    }


    /**
     * Appel Nominatim avec contrainte pays stricte
     */
    private function callNominatim(string $query, string $countryCode): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LaravelGeocoder/1.0'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => $countryCode,
            'addressdetails' => 0
        ]);

        if ($response->successful() && count($response->json()) > 0) {
            return $response->json()[0];
        }

        return null;
    }
}
