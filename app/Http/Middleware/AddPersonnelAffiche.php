<?php

namespace App\Http\Middleware;

use App\Models\AgenceExecution;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Bailleur;
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
            if (!auth()->user()->latestFonction) {
                $personnelAffiche = '';
            } else {
            // Logique pour déterminer la valeur de $personnelAffiche en fonction du rôle de l'utilisateur
            switch (auth()->user()->latestFonction->fonctionUtilisateur->code) {
                case 'cp': // Chef de projet
                   // Récupérer le nom du ministère
                   $chefprojet = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                   if ($chefprojet) {
                        if($chefprojet->type_structure=='agence_execution')
                        {
                            // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                            $chefprojetInfo = AgenceExecution::where('code_agence_execution', $chefprojet->code_structure)->first();
                            $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->nom_agence : '---';

                        }else if($chefprojet->type_structure=='ministere'){

                            // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                            $chefprojetInfo = Ministere::where('code', $chefprojet->code_structure)->first();
                            $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->libelle : '---';

                        }else{

                        }

                   }
                   break;
                case 'ba': // Bailleur
                    // Récupérer le nom du ministère
                    $bailleur = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                    if ($bailleur) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $bailleurInfo = Bailleur::where('code_bailleur', $bailleur->code_structure)->first();
                        $personnelAffiche = $bailleurInfo ? $bailleurInfo->libelle_long : '---';

                    }
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
                case 'dh': // Directeur hydrocarbure
                    $personnelAffiche='';
                    break;
                case 'en': // Employé ministère
                    $employeministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($employeministere) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $employeministereInfo = Ministere::where('code', $employeministere->code_structure)->first();
                        $personnelAffiche = $employeministereInfo ? $employeministereInfo->libelle : '---';
                    }
                    break;
                case 'm': // Ministre
                    // Récupérer le nom du ministère
                    $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($ministere) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                        $personnelAffiche = $ministereInfo ? $ministereInfo->libelle : '---';
                    }
                    break;
                case 'mo': // Maitre d'oeuvre
                    // Récupérer le nom du ministère
                    $maitreoeurvre = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($maitreoeurvre) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $maitreoeurvreInfo = AgenceExecution::where('code_agence_execution', $maitreoeurvre->code_structure)->first();
                        $personnelAffiche = $maitreoeurvreInfo ? $maitreoeurvreInfo->nom_agence : '---';
                    }

                    break;
                case 'mr': // Maire
                    $maire = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($maire) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $maireInfo = Bailleur::where('code_bailleur', $maire->code_structure)->first();
                        $personnelAffiche = $maireInfo ? $maireInfo->libelle_long : '---';
                    }
                    break;
                case 'pr': // Président
                    $personnelAffiche='';
                    break;
                case 're': // Représentant
                    $personnelAffiche='';
                    break;
                case 'rf': // Régisseur financier
                    // Récupérer le nom du ministère
                    $regie = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($regie) {
                        // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                        $regieInfo = AgenceExecution::where('code_agence_execution', $regie->code_structure)->first();
                        $personnelAffiche = $regieInfo ? $regieInfo->nom_agence : '---';
                    }
                    break;
                default:
                    // Valeur par défaut si aucun cas ne correspond
                    $personnelAffiche = 'Autre';
                    break;
            }
        }
        }

        // Partagez la variable avec toutes les vues
        View::share('personnelAffiche', $personnelAffiche);

        return $next($request);
    }
}
