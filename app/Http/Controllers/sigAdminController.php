<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\GroupeProjetUser;
use App\Models\Infrastructure;
use App\Models\Pays;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\StatutProjet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        /*$bailleur = Bailleur::all();
        $statut = StatutProjet::all();*/
        $user = Auth::user();

        // Vérifiez si l'utilisateur a un pays assigné
        if (is_null($user->paysUser) || is_null($user->paysUser->pays)) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'êtes pas assigné à un pays.');
        }

        $countryName = $user->paysUser->pays->nom_fr_fr; // Assurez-vous que ces relations existent
        $codeAlpha3 = Pays::getAlpha3Code($countryName);
        $codeZoom = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();
        $codeGroupeProjet = $user->groupe_projet_id;
        // Récupérer les utilisateurs associés (peut-être plusieurs, bien que cela semble étrange ici)
        $users = User::where('acteur_id', $user->acteur_id)->get();

        // Récupérer les libellés et codes des domaines associés à chaque utilisateur
        $domainesAssocie = $users->flatMap(function ($user) {
            return GroupeProjetUser::where('user_code', $user->acteur_id)
                ->with('groupeProjet.domaine')
                ->get()
                ->flatMap(function ($groupeProjetUser) {
                    // Vérifiez que les relations existent avant d'y accéder
                    return $groupeProjetUser->groupeProjet?->domaine?->map(function ($domaine) {
                        return [
                            'code' => $domaine->code,
                            'libelle' => $domaine->libelle,
                        ];
                    }) ?? []; // Retourne une liste vide si la relation est null
                });
        });


                // Récupérez les domaines associés à l'utilisateur
        $code = Domaine::where('groupe_projet_code', $codeGroupeProjet)->
                            select('code', 'libelle')->get();

        // Récupérez les niveaux administratifs
        $niveau = DecoupageAdminPays::where('id_pays', $user->paysUser->pays->id)
        ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
        ->select('decoupage_admin_pays.code_decoupage', 'decoupage_admin_pays.num_niveau_decoupage', 'decoupage_administratif.libelle_decoupage')
        ->get();

        return view('sigAdmin', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie'));
    }



}
