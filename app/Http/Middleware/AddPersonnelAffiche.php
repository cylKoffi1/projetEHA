<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\CouvrirRegion;
use App\Models\Ministere;
use App\Models\Region;
use App\Models\StructureRattachement;

class AddPersonnelAffiche
{
    public function handle($request, Closure $next)
    {
        $personnelAffiche = '';

        // Assurez-vous que l'utilisateur est connecté avant de récupérer ses informations
        if (Auth::check()) {
            // Logique pour déterminer la valeur de $personnelAffiche en fonction du rôle de l'utilisateur
            switch (auth()->user()->latestFonction->fonctionUtilisateur->code) {
                case 'cp': // Chef de projet
                    $personnelAffiche = 'Personnel';
                    break;
                case 'ba': // Bailleur
                    $personnelAffiche = 'bailleur';
                    break;
                case 'dc': // Directeur de cabinet
                    // Récupérer le nom du ministère
                    $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($ministere) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                        $personnelAffiche = $ministereInfo ? $ministereInfo->libelle : '---';
                    }
                    break;
                case 'dr': // Directeur Régional
                    // Récupérer le nom de la région de l'utilisateur
                    $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                    if ($region) {
                        // Si la région est trouvée, récupérer son libellé depuis la table Region
                        $regionInfo = Region::where('code', $region->code_region)->first();
                        $personnelAffiche = $regionInfo ? $regionInfo->libelle : '---';
                    }
                    break;
                default:
                    // Valeur par défaut si aucun cas ne correspond
                    $personnelAffiche = 'Autre';
                    break;
            }
        }

        // Partagez la variable avec toutes les vues
        View::share('personnelAffiche', $personnelAffiche);

        return $next($request);
    }
}
