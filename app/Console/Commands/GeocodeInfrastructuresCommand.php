<?php

namespace App\Console\Commands;

use App\Http\Controllers\AutresRequetesController;
use App\Models\Pays;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class GeocodeInfrastructuresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode:infrastructures 
                            {--country= : Code alpha3 du pays (ex: CIV)}
                            {--limit=10 : Nombre de localités à traiter par exécution}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Géocode automatiquement les infrastructures sans coordonnées GPS via Nominatim';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alpha3 = $this->option('country');
        $limit = (int) $this->option('limit');

        if (!$alpha3) {
            // Si pas de pays spécifié, on prend le pays de la session ou on demande
            $alpha3 = session('pays_selectionne');
            if (!$alpha3) {
                $this->error('Aucun pays spécifié. Utilisez --country=CIV ou définissez pays_selectionne en session.');
                return 1;
            }
        }

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) {
            $this->error("Pays '{$alpha3}' introuvable.");
            return 1;
        }

        $this->info("Géocodage des infrastructures pour le pays: {$pays->libelle} ({$alpha3})");
        $this->info("Limite: {$limit} localités par exécution");

        // Simuler la session pour le contrôleur
        Session::put('pays_selectionne', $alpha3);

        // Vérifier d'abord combien d'infrastructures existent pour ce pays
        $totalInfras = \App\Models\Infrastructure::where('code_pays', $alpha3)->count();
        $infrasWithCoords = \App\Models\Infrastructure::where('code_pays', $alpha3)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->count();
        $infrasWithoutCoords = $totalInfras - $infrasWithCoords;

        $this->info("📊 Statistiques:");
        $this->info("   - Total infrastructures: {$totalInfras}");
        $this->info("   - Avec coordonnées: {$infrasWithCoords}");
        $this->info("   - Sans coordonnées: {$infrasWithoutCoords}");

        if ($infrasWithoutCoords === 0) {
            $this->info("✅ Toutes les infrastructures ont déjà des coordonnées.");
            return 0;
        }

        try {
            $controller = new AutresRequetesController();
            $count = $controller->ensureInfrastructureCoordinates($alpha3, $limit);
            
            if ($count > 0) {
                $this->info("✅ Géocodage terminé: {$count} infrastructure(s) mise(s) à jour.");
            } else {
                $this->warn("⚠️  Aucune infrastructure géocodée. Vérifiez les logs pour plus de détails.");
                $this->info("   (Causes possibles: pas de code_localite, localité introuvable dans LocalitesPays, ou erreur Nominatim)");
            }
            return 0;
        } catch (\Throwable $e) {
            $this->error('❌ Erreur lors du géocodage: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
