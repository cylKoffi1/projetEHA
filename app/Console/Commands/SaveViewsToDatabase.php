<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\View; // Assurez-vous d'importer le modèle View

class SaveViewsToDatabase extends Command
{
    protected $signature = 'views:save-to-database';
    protected $description = 'Enregistre toutes les vues du dossier views dans la base de données';

    public function handle()
    {
        // Obtenez tous les fichiers .blade.php du dossier views
        $viewFiles = File::glob(resource_path('views/**/*.blade.php'));

        foreach ($viewFiles as $viewPath) {
            // Obtenez le chemin relatif à partir de la racine du dossier views
            $relativePath = str_replace(resource_path('views/'), '', $viewPath);

            // Ajoutez ou mettez à jour la vue dans la table views
            View::updateOrCreate(['path' => $relativePath]);
        }

        $this->info('Toutes les vues ont été enregistrées dans la base de données avec succès.');
    }
}
