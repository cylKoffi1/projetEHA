<?php

namespace App\Http\Controllers;

use App\Models\Acquifere;
use App\Models\Acteur;
use App\Models\ActionMener;
use App\Models\AgenceExecution;
use App\Models\ApartenirGroupeUtilisateur;
use App\Models\Approbateur;
use App\Models\Bailleur;
use App\Models\Bassin;
use App\Models\CourDeau;
use App\Models\Devise;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\Etablissement;
use App\Models\FamilleInfrastructure;
use App\Models\Fonction_groupe_utilisateur;
use App\Models\FonctionUtilisateur;
use App\Models\Genre;
use App\Models\GroupeUtilisateur;
use App\Models\Localite;
use App\Models\MaterielStockage;
use App\Models\Ministere;
use App\Models\NiveauAccesDonnees;
use App\Models\NiveauEtablissement;
use App\Models\OccuperFonction;
use App\Models\OutilsCollecte;
use App\Models\OuvrageTransport;
use App\Models\ProjetEha2;
use App\Models\SousDomaine;
use App\Models\TypeBailleur;
use App\Models\TypeEtablissement;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Pays;
use App\Models\Personnel;
use App\Models\StatutProjet;
use App\Models\StructureRattachement;
use App\Models\TypeInstrument;
use App\Models\TypeMateriauxConduite;
use App\Models\TypeResaux;
use App\Models\TypeStation;
use App\Models\TypeStockage;
use App\Models\UniteDistance;
use App\Models\UniteMesure;
use App\Models\UniteStockage;
use App\Models\UniteSurface;
use App\Models\UniteTraitement;
use App\Models\uniteVolume;
use App\Models\TypeCaracteristique;
use App\Models\Caracteristique;
use App\Models\FamilleCaracteristique;
use App\Models\FamilleDomaine;
use App\Models\GroupeProjet;
use App\Models\Infrastructure;
use App\Models\Projet;
use App\Models\Unite;
use App\Models\ValeurPossible;
use App\Services\CaracteristiqueBuilderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;


class PlateformeController extends Controller
{
    //***************** AGENCES ************* */
    public function agences(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        return view('parSpecifique.agences', ['agences' => $agences, 'ecran' => $ecran, ]);
    }

    public function getAgence($code)
    {
        $agence = AgenceExecution::find($code);

        if (!$agence) {
            return response()->json(['error' => 'agence non trouvé'], 404);
        }

        return response()->json($agence);
    }

    public function deleteAgence($code)
    {
        $agence = AgenceExecution::find($code);

        if (!$agence) {
            return response()->json(['error' => 'agence non trouvé'], 404);
        }

        $agence->delete();

        return response()->json(['success' => 'agence supprimé avec succès']);
    }

    public function checkAgenceCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $agence = AgenceExecution::where('code_agence_execution', $code)->exists();

        return response()->json(['exists' => $agence]);
    }

    public function storeAgence(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $agence = new AgenceExecution;
        $agence->code_agence_execution = $request->input('code');
        $agence->nom_agence = $request->input('nom_agence');
        $agence->telephone = $request->input('tel');
        $agence->email = $request->input('email');
        $agence->addresse = $request->input('addresse');

        $agence->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.agences', ['ecran_id' => $ecran_id])->with('success', 'Agence enregistré avec succès.');
    }
    public function updateAgence(Request $request)
    {

        $agence = AgenceExecution::find($request->input('edit_code'));

        if (!$agence) {
            return response()->json(['error' => 'Agence non trouvé'], 404);
        }

        $agence->nom_agence = $request->input('edit_nom_agence');
        $agence->telephone = $request->input('edit_tel');
        $agence->email = $request->input('edit_email');
        $agence->addresse = $request->input('edit_addresse');


        // Vous pouvez également valider les données ici si nécessaire

        $agence->save();
        $ecran_id = $request->input('ecran_id');
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.agences', ['ecran_id' => $ecran_id])->with('success', 'Agence mis à jour avec succès.');
    }



    //***************** BAILLEURS ************* */
    public function bailleurs(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $bailleurs = Bailleur::with('pays')->with('type_bailleur')->orderBy('libelle_long', 'asc')->get();
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
        $type_bailleurs = TypeBailleur::all();
        $devises = Devise::all();
        return view('parSpecifique.bailleurs', ['bailleurs' => $bailleurs,'ecran' => $ecran, 'devises' => $devises, 'type_bailleurs' => $type_bailleurs, 'pays' => $pays]);
    }

    public function getBailleur($code)
    {
        $bailleur = Bailleur::find($code);

        if (!$bailleur) {
            return response()->json(['error' => 'bailleur non trouvé'], 404);
        }

        return response()->json($bailleur);
    }

    public function deleteBailleur($code)
    {
        $bailleur = Bailleur::find($code);

        if (!$bailleur) {
            return response()->json(['error' => 'bailleur non trouvé'], 404);
        }

        $bailleur->delete();

        return response()->json(['success' => 'bailleur supprimé avec succès']);
    }

    public function checkBailleurCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $bailleur = Bailleur::where('code', $code)->exists();

        return response()->json(['exists' => $bailleur]);
    }

    public function storeBailleur(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $bailleur = new Bailleur;
        $bailleur->code = $request->input('code');
        $bailleur->nom = $request->input('nom');
        $bailleur->telephone = $request->input('tel');
        $bailleur->email = $request->input('email');
        $bailleur->addresse = $request->input('addresse');
        $bailleur->code_devise = $request->input('id_devise');
        $bailleur->code_type_bailleur = $request->input('id_tb');
        $bailleur->code_pays = $request->input('id_pays');
        $bailleur->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.bailleurs', ['ecran_id' => $ecran_id])->with('success', 'bailleur enregistré avec succès.');
    }
    public function updateBailleur(Request $request)
    {

        $bailleur = Bailleur::find($request->input('edit_code'));

        if (!$bailleur) {
            return response()->json(['error' => 'Agence non trouvé'], 404);
        }

        $bailleur->nom = $request->input('nom');
        $bailleur->telephone = $request->input('tel');
        $bailleur->email = $request->input('email');
        $bailleur->addresse = $request->input('addresse');
        $bailleur->code_devise = $request->input('edit_id_devise');
        $bailleur->code_type_bailleur = $request->input('edit_id_tb');
        $bailleur->code_pays = $request->input('edit_id_pays');


        // Vous pouvez également valider les données ici si nécessaire

        $bailleur->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.bailleurs', ['ecran_id' => $ecran_id])->with('success', 'bailleur mis à jour avec succès.');
    }






    //***************** ETABLISSEMENTS ************* */
    public function etablissements(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $etablissements = Etablissement::with('localite')->with('genre')->with('niveaux')->orderBy('nom_etablissement', 'asc')->get();
        $genres = Genre::orderBy('libelle_genre', 'asc')->get();
        $niveaux = NiveauEtablissement::all();
        $type_etablissements = TypeEtablissement::all();
        $localites = Localite::orderBy('localite', 'asc')->get();
        return view('etablissements', ['etablissements' => $etablissements,'ecran' => $ecran, 'genres' => $genres, 'type_etablissements' => $type_etablissements, 'niveaux' => $niveaux, 'localites' => $localites]);
    }
    public function getEtablissement($code)
    {
        $etablissement = Etablissement::with('niveaux')->find($code);

        if (!$etablissement) {
            return response()->json(['error' => 'Etablissement non trouvé'], 404);
        }

        return response()->json($etablissement);
    }


    public function deleteEtablissement($code)
    {
        $etablissement = Etablissement::find($code);

        if (!$etablissement) {
            return response()->json(['error' => 'Etablissement non trouvé'], 404);
        }

        $etablissement->delete();

        return response()->json(['success' => 'Etablissement supprimé avec succès']);
    }

    public function checkEtablissementCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $etablissement = Etablissement::where('code', $code)->exists();

        return response()->json(['exists' => $etablissement]);
    }

    public function storeEtablissement(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $etablissement = new Etablissement;
        $etablissement->code = $request->input('code');
        $etablissement->nom_etablissement = $request->input('nom_etablissement');
        $etablissement->nom_court = $request->input('nom_court');
        $etablissement->public = $request->boolean('public');
        $etablissement->code_genre = $request->input('code_genre');
        $etablissement->code_localite = $request->input('code_localite');
        $etablissement->code_niveau = $request->input('code_niveau');
        $etablissement->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('etablissements', ['ecran_id' => $ecran_id])->with('success', 'Etablissement enregistré avec succès.');
    }
    public function updateEtablissement(Request $request)
    {

        $etablissement = Etablissement::find($request->input('edit_code'));

        if (!$etablissement) {
            return response()->json(['error' => 'Etablissement non trouvé'], 404);
        }

        $etablissement->nom_etablissement = $request->input('edit_nom_etablissement');
        $etablissement->nom_court = $request->input('edit_nom_court');
        $etablissement->public = $request->input('edit_public');
        $etablissement->code_genre = $request->input('edit_code_genre');
        $etablissement->code_localite = $request->input('edit_code_localite');
        $etablissement->code_niveau = $request->input('edit_code_niveau');
        $etablissement->save();


        // Vous pouvez également valider les données ici si nécessaire

        $etablissement->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('etablissements', ['ecran_id' => $ecran_id])->with('success', 'Etablissement mis à jour avec succès.');
    }

    public function getNiveaux(Request $request, $typeId)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $niveaux = NiveauEtablissement::where('code_type_etablissement', $typeId)->get();

        // Créez un tableau d'options pour les districts
        $niveauxOptions = [];
        foreach ($niveaux as $niveau) {
            $niveauxOptions[$niveau->code] = $niveau->libelle_long;
        }

        return response()->json(['niveaux' => $niveauxOptions]);
    }

    //***************** MINISTÈRES ************* */
    public function ministeres(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        return view('parSpecifique.ministeres', ['ministeres' => $ministeres, 'ecran' => $ecran,]);
    }




    // ********************* GESTION DOMAINES ET SOUS-DOMAINES *************************//


    public function checkDomaineCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Domaine::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }
    public function checkSousDomaineCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = SousDomaine::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }
    public function storeDomaine(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:20|unique:domaine_intervention,code',
                'libelle' => 'required|string|max:255',
                'ecran_id' => 'required'
            ]);
    
            $domaine = new Domaine;
            $domaine->code = $request->input('code');
            $domaine->libelle = $request->input('libelle');
            $domaine->groupe_projet_code = session('projet_selectionne');
            $domaine->save();
    
            return response()->json(['success' => 'Domaine enregistré avec succès.']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du domaine.'], 500);
        }
    }
        
    public function updateDomaine(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'libelle' => 'required|string|max:255'
            ]);
    
            $domaine = Domaine::where('code', $request->input('code'))
            ->where('groupe_projet_code', session('projet_selectionne'))
            ->first();
    
            if (!$domaine) {
                return response()->json(['error' => 'Domaine non trouvé.'], 404);
            }
    
            $domaine->libelle = $request->input('libelle');
            $domaine->save();
    
            return response()->json(['success' => 'Domaine mis à jour avec succès.']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['error' => 'Erreur lors de la mise à jour du domaine.'], 500);
        }
    }
    
    public function storeSousDomaine(Request $request)
    {
        try {
            $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('sous_domaine', 'code_sous_domaine')
                        ->where(function ($query) {
                            return $query->where('code_groupe_projet', session('projet_selectionne'));
                        }),
                ],
                'libelle' => 'required|string|max:255',
                'domaine' => 'required|string|exists:domaine_intervention,code'
            ]);
    
            $sousDomaine = new SousDomaine;
            $sousDomaine->code_sous_domaine = $request->input('code');
            $sousDomaine->lib_sous_domaine = $request->input('libelle');
            $sousDomaine->code_domaine = $request->input('domaine');
            $sousDomaine->code_groupe_projet = session('projet_selectionne');
            $sousDomaine->save();
    
            return response()->json(['success' => 'Sous-domaine enregistré avec succès.']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du sous-domaine.'], 500);
        }
    }
    
    public function updateSousDomaine(Request $request)
    {
        try {
            $request->validate([
                'libelle_edit' => 'required|string|max:255',
                'domaine_edit' => 'required|string|exists:domaine_intervention,code'
            ]);
    
            $sousDomaine = SousDomaine::where('code_sous_domaine', $request->input('code'))
                ->where('code_groupe_projet', session('projet_selectionne'))
                ->first();
    
            if (!$sousDomaine) {
                return response()->json(['error' => 'Sous-domaine non trouvé.'], 404);
            }
    
            $sousDomaine->lib_sous_domaine = $request->input('libelle');
            $sousDomaine->save();
    
            return response()->json(['success' => 'Sous-domaine mis à jour avec succès.']);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['error' => 'Erreur lors de la mise à jour du sous-domaine.'], 500);
        }
    }
    public function domaines(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))
        ->orderBy('libelle', 'asc')->get();
        return view('parGeneraux.domaines', ['domaines' => $domaines,'ecran' => $ecran, ]);
    }


    public function sousDomaines(Request $request)
    {
        $ecran = Ecran::find($request->ecran_id); // ou autre logique
        $sous_domaines = SousDomaine::where('code_groupe_projet', session('projet_selectionne'))
        ->orderBy('lib_sous_domaine', 'asc')->get();
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))
        ->orderBy('libelle', 'asc')->get();

        return view('parGeneraux.sous_domaines', [
            'ecran' => $ecran,
            'domaines' => $domaines,
            'sous_domaines' => $sous_domaines,
        ]);
    }
    
    public function deleteDomaine($code)
    {
        $domaine = Domaine::where('code',$code)
        ->where('groupe_projet_code', session('projet_selectionne'))
        ->first();

        if (!$domaine) {
            return response()->json(['error' => 'Domaine non trouvé'], 404);
        }
       
        $groupeProjet = session('projet_selectionne');

        // Vérifie si des sous-domaines sont liés à ce domaine
        $hasSousDomaines = SousDomaine::where('code_domaine', $code)->exists();
        if ($hasSousDomaines) {
            return response()->json([
                'error' => 'Suppression interdite : Des sous-domaines sont rattachés à ce domaine.'
            ], 403);
        }

        $projet = Projet::whereRaw("SUBSTRING(code_sous_domaine, 1, 2) = ?", [$code])
        ->whereRaw("SUBSTRING(code_projet, 4, 3) = ?", [$groupeProjet])
        ->first();
    

        if ($projet) {
            return response()->json(['error' => "Suppression interdite : Le domaine est utilisé dans d'autres tables"], 404);
        }
        $domaine->delete();

        return response()->json(['success' => 'Domaine supprimé avec succès']);
    }
    public function deleteSousDomaine($code)
    {
        $s_domaine = SousDomaine::where('code_sous_domaine',$code)
        ->where('code_groupe_projet', session('projet_selectionne'))->first();

        if (!$s_domaine) {
            return response()->json(['error' => 'Sous-domaine non trouvé'], 404);
        }
        $projet = Projet::where('code_sous_domaine', $code)
        ->whereRaw("SUBSTRING(code_projet, 4, 3) = ?", [session('projet_selectionne')])
        ->first();


        if ($projet) {
            return response()->json(['error' => "Suppression interdite : Le Sous-domaine est utilisé dans d'autres tables"], 404);
        }
        $s_domaine->delete();

        return response()->json(['success' => 'Sous-domaine supprimé avec succès']);
    }


    public function getDomaine($code)
    {
        $domaine = Domaine::where('code', $code)
        ->where('groupe_projet_code', session('projet_selectionne'))
        ->first();

        if (!$domaine) {
            return response()->json(['error' => 'Domaine non trouvé'], 404);
        }

        return response()->json($domaine);
    }

    public function getSousDomaine($code)
    {
        $s_domaine = SousDomaine::where('code_sous_domaine', $code)
        ->where('code_groupe_projet', session('projet_selectionne'))
        ->first();

        if (!$s_domaine) {
            return response()->json(['error' => 'Sous-domaine non trouvé'], 404);
        }

        return response()->json($s_domaine);
    }



    //***************** DÉVISES ************* */
    public function devises(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $devises = Devise::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.devises', ['devises' => $devises,'ecran' => $ecran, ]);
    }
    public function getDevise($code)
    {
        $devise = Devise::find($code);

        if (!$devise) {
            return response()->json(['error' => 'Dévise non trouvé'], 404);
        }

        return response()->json($devise);
    }

    public function storeDevise(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $devise = new Devise;
        $devise->code = $request->input('code');
        $devise->libelle = $request->input('libelle');
        $devise->monnaie = $request->input('monnaie');
        $devise->code_long = $request->input('code_long');
        $devise->code_court = $request->input('code_court');

        $devise->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.devises', ['ecran_id' => $ecran_id])->with('success', 'Dévises enregistré avec succès.');
    }

    public function updateDevise(Request $request)
    {
        $devise = Devise::find($request->input('code_edit'));

        if (!$devise) {
            return response()->json(['error' => 'Dévise non trouvé'], 404);
        }

        $devise->libelle = $request->input('libelle_edit');
        $devise->monnaie = $request->input('monnaie_edit');
        $devise->code_long = $request->input('code_long_edit');
        $devise->code_court = $request->input('code_court_edit');
        $devise->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.devises', ['ecran_id' => $ecran_id])->with('success', 'Dévise mise à jour avec succès.');
    }
    public function deleteDevise($code)
    {
        $devise = Devise::find($code);

        if (!$devise) {
            return response()->json(['error' => 'Dévise non trouvé'], 404);
        }
        $projet = ProjetEha2::where('code_devise', $code)->first();

        if ($projet) {
            return response()->json(['error' => "Suppression interdite : La dévise est utilisé dans d'autres tables"], 404);
        }
        $devise->delete();

        return response()->json(['success' => 'Dévise supprimé avec succès']);
    }

    public function checkDeviseCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Devise::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

    //***************** ACQUIFERE ************* */
    public function acquifere(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $acquifere = Acquifere::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.acquifere', ['acquifere' => $acquifere,  'ecran' => $ecran]);
    }


    public function getAcquifere($code)
    {
        $acquifere = Acquifere::find($code);

        if (!$acquifere) {
            return response()->json(['error' => 'Acquifère non trouvé'], 404);
        }

        return response()->json($acquifere);
    }

    public function storeAcquifere(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $acquifere = new Acquifere;
        $acquifere->code = $request->input('code');
        $acquifere->libelle = $request->input('libelle');

        $acquifere->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.acquifere', ['ecran_id' => $ecran_id])->with('success', 'Acquifère enregistré avec succès.');
    }
    public function updateAcquifere(Request $request)
    {
        $acquifere = Acquifere::find($request->input('code_edit'));

        if (!$acquifere) {
            return response()->json(['error' => 'Acquifère non trouvé'], 404);
        }

        $acquifere->libelle = $request->input('libelle_edit');


        $acquifere->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.acquifere', ['ecran_id' => $ecran_id])->with('success', 'Acquifère mis à jour avec succès.');
    }

    public function deleteAcquifere($code)
    {
        $acquifere = Acquifere::find($code);

        if (!$acquifere) {
            return response()->json(['error' => 'Acquifere non trouvé'], 404);
        }
        //$projet = ProjetEha2::where('code_domaine', $code)->first();

        // if ($projet) {
        //     return response()->json(['error' => "Suppression interdite : Le domaine est utilisé dans d'autres tables"], 404);
        // }
        $acquifere->delete();

        return response()->json(['success' => 'Acquifere supprimé avec succès']);
    }

    public function checkAcquifereCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Acquifere::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

        //***************** ACQUIFERE ************* */
        public function actionMener(Request $request)
        {
           $ecran = Ecran::find($request->input('ecran_id'));
            $actionMener = ActionMener::orderBy('libelle', 'asc')->get();
            return view('parGeneraux.actionmener', ['actionMener' => $actionMener,  'ecran' => $ecran]);
        }


        public function getActionMener($code)
        {
            $actionMener = ActionMener::find($code);

            if (!$actionMener) {
                return response()->json(['error' => 'Action à mener non trouvé'], 404);
            }

            return response()->json($actionMener);
        }

        public function storeActionMener(Request $request)
        {
            // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

            // Créez un nouveau district dans la base de données.
            $actionMener = new ActionMener;
            $actionMener->code = $request->input('code');
            $actionMener->libelle = $request->input('libelle');

            $actionMener->save();
            $ecran_id = $request->input('ecran_id');

            // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
            return redirect()->route('actionMener', ['ecran_id' => $ecran_id])->with('success', 'Action  à mener enregistré avec succès.');
        }
        public function updateActionMener(Request $request)
        {
            $actionMener = ActionMener::find($request->input('code_edit'));

            if (!$actionMener) {
                return response()->json(['error' => 'Acton à mener non trouvé'], 404);
            }

            $actionMener->libelle = $request->input('libelle_edit');


            $actionMener->save();
            $ecran_id = $request->input('ecran_id');
            // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
            return redirect()->route('actionMener', ['ecran_id' => $ecran_id])->with('success', 'Action à mener mis à jour avec succès.');
        }

        public function deleteActionMener($code)
        {
            $actionMener = ActionMener::find($code);

            if (!$actionMener) {
                return response()->json(['error' => 'Action à mener non trouvé'], 404);
            }
            //$projet = ProjetEha2::where('code_domaine', $code)->first();

            // if ($projet) {
            //     return response()->json(['error' => "Suppression interdite : Le domaine est utilisé dans d'autres tables"], 404);
            // }
            $actionMener->delete();

            return response()->json(['success' => 'Action à mener supprimé avec succès']);
        }

        public function checkActionMenerCode(Request $request)
        {
            $code = $request->input('code');

            // Check if a district with the provided code already exists in your database
            $exists = ActionMener::where('code', $code)->exists();

            return response()->json(['exists' => $exists]);
        }

    //***************** BASSIN ************* */
    public function bassin(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $bassin = Bassin::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.bassin', ['bassin' => $bassin, 'ecran' => $ecran,]);
    }

    public function getBassin($code)
    {
        $bassin = Bassin::find($code);

        if (!$bassin) {
            return response()->json(['error' => 'BAssin non trouvé'], 404);
        }

        return response()->json($bassin);
    }

    public function storeBassin(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $bassin = new Bassin;
        $bassin->code = $request->input('code');
        $bassin->libelle = $request->input('libelle');

        $bassin->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.bassin', ['ecran_id' => $ecran_id])->with('success', 'Bassin enregistré avec succès.');
    }
    public function updateBassin(Request $request)
    {
        $bassin = bassin::find($request->input('code_edit'));

        if (!$bassin) {
            return response()->json(['error' => 'Bassin non trouvé'], 404);
        }

        $bassin->libelle = $request->input('libelle_edit');


        $bassin->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.bassin', ['ecran_id' => $ecran_id])->with('success', 'Bassin mis à jour avec succès.');
    }

    public function deleteBassin($code)
    {
        $bassin = Bassin::find($code);

        if (!$bassin) {
            return response()->json(['error' => 'Bassin non trouvé'], 404);
        }
        //$projet = ProjetEha2::where('code_domaine', $code)->first();

        // if ($projet) {
        //     return response()->json(['error' => "Suppression interdite : Le domaine est utilisé dans d'autres tables"], 404);
        // }
        $bassin->delete();

        return response()->json(['success' => 'Bassin supprimé avec succès']);
    }

    public function checkBassinCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Bassin::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

    //*****************Approbation***************/
    public function approbation(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $acteurs = Acteur::where('type_acteur', 'etp')
        ->whereIn('code_pays', [session('pays_selectionne')])->get();
        // Récupérer le dernier numéro d'ordre enregistré
        $lastOrder = Approbateur::orderBy('numOrdre', 'desc')->first();
        $nextOrder = $lastOrder ? $lastOrder->numOrdre + 1 : 1;

        $approbateurs = Approbateur::with('acteur')->get();
        return view('parGeneraux.approbateur', compact('nextOrder','ecran', 'acteurs', 'approbateurs'));
    }
    public function storeApprobation(Request $request)
    {
        $approbateurs = json_decode($request->input('approbateurs'), true);
        Log::info('Request Data:', $request->all()); // Log all request data
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($approbateurs as $approbateur) {
                Log::info('Processing Approver:', $approbateur);

                // Recherche de l'utilisateur par code personnel
                $user = Acteur::where('code_acteur', $approbateur['userCode'])->first();

                if ($user) {
                    Log::info('User Found:', $user->toArray());

                    // Vérification de l'existence de l'approbateur avec le même code personnel
                    $existingApprobateur = Approbateur::where('code_acteur', $user->code_acteur)->first();
                    if ($existingApprobateur) {
                        $errors[] = "L'utilisateur {$user?->libelle_court} {$user?->libelle_long} est déjà un approbateur.";
                        Log::info("L'utilisateur {$user?->libelle_court} {$user?->libelle_long} est déjà un approbateur.");
                        continue;
                    }

                    // Vérification de l'unicité du numéro d'ordre
                    $existingOrder = Approbateur::where('numOrdre', $approbateur['nordre'])->first();
                    if ($existingOrder) {
                        $errors[] = "Le numéro d'ordre {$approbateur['nordre']} est déjà utilisé.";
                        Log::info("Le numéro d'ordre {$approbateur['nordre']} est déjà utilisé.");
                        continue;
                    }

                    // Enregistrement de l'approbateur
                    Approbateur::create([
                        'code_acteur' => $user->code_acteur,
                        'numOrdre' => $approbateur['nordre']
                    ]);
                } else {
                    Log::warning('User Not Found:', $approbateur);
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return redirect()->back()->withErrors($errors)->withInput();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Approbateurs enregistrés avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during saving approbateurs:', ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateApprobateur(Request $request)
    {
        // Rechercher l'approbateur par le numéro d'ordre
        $approbateur = Approbateur::where('numOrdre', $request->input('numOrdreId'))->first();

        // Vérifier si l'approbateur existe
        if (!$approbateur) {
            return redirect()->back()->with('error', 'Approbateur non trouvé.');
        }

        // Mettre à jour les champs
        $approbateur->numOrdre = $request->input('editNordre'); // Assurez-vous de mettre à jour le numéro d'ordre si nécessaire
        $approbateur->code_acteur = $request->input('editUserapp');
        // Sauvegarder les modifications
        $approbateur->save();

        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Approbateur modifié avec succès.');
    }

    public function deleteApprobation($id)
    {
        $approbateur = Approbateur::find($id);

        if (!$approbateur) {
            return response()->json(['error' => 'L\'approbateur que vous essayez de supprimer n\'existe pas.'], 404);
        }

        $numOrdreToDelete = $approbateur->numOrdre; // Récupérer le numOrdre de l'approbateur à supprimer

        try {
            // Supprimer l'approbateur
            $approbateur->delete();

            // Mettre à jour les numOrdre des approbateurs restants
            Approbateur::where('numOrdre', '>', $numOrdreToDelete)
                ->decrement('numOrdre');

            return response()->json(['success' => 'Approbateur supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression de l\'approbateur.'], 500);
        }
    }

   

    //***************** COURS D'EAU ************* */
    public function courdeau(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $courdeau = CourDeau::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.courdeau', ['courdeau' => $courdeau,'ecran' => $ecran, ]);
    }

    public function getCourDeau($code)
    {
        $courdeau = CourDeau::find($code);

        if (!$courdeau) {
            return response()->json(['error' => 'Cour d\'eau non trouvé'], 404);
        }

        return response()->json($courdeau);
    }

    public function storeCourDeau(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $courdeau = new CourDeau;
        $courdeau->code = $request->input('code');
        $courdeau->libelle = $request->input('libelle');

        $courdeau->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.courdeau', ['ecran_id' => $ecran_id])->with('success', 'Cour d\'eau enregistré avec succès.');
    }
    public function updateCourDeau(Request $request)
    {
        $courdeau = CourDeau::find($request->input('code_edit'));

        if (!$courdeau) {
            return response()->json(['error' => 'Bassin non trouvé'], 404);
        }

        $courdeau->libelle = $request->input('libelle_edit');


        $courdeau->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.courdeau', ['ecran_id' => $ecran_id])->with('success', 'Cour d\'eau mis à jour avec succès.');
    }

    public function deleteCourDeau($code)
    {
        $courdeau = CourDeau::find($code);

        if (!$courdeau) {
            return response()->json(['error' => 'Cour d\'eau non trouvé'], 404);
        }

        $courdeau->delete();

        return response()->json(['success' => 'Cour d\'eau supprimé avec succès']);
    }

    public function checkCourDeauCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = CourDeau::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    private function genererCodeFamilleUnique()
    {
        do {
            // Génère 3 lettres aléatoires (ex : "QZT")
            $code = strtoupper(Str::random(3));
        } while (FamilleInfrastructure::where('code_Ssys', $code)->exists());

        return $code;
    }
    //***************** FAMILLE INFRASTRUCTURE  ************* */

    public function familleinfrastructure(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $unites = Unite::orderBy('libelleUnite')->get();
        $codeFamilleGenere = $this->genererCodeFamilleUnique();
        $typesCaracteristique = TypeCaracteristique::all();
        $groupeProjets = GroupeProjet::all();
        $domaine = Domaine::all();
        $sous_domaines = SousDomaine::all();
        $familleinfrastructure = FamilleInfrastructure::orderBy('libelleFamille', 'asc')->get();
        $caracteristiques = Caracteristique::with('type')->get();
    
        // 👇 Si une famille est déjà sélectionnée, générer sa structure
        $structure = [];
        if ($familleinfrastructure->isNotEmpty()) {
            $structure = (new CaracteristiqueBuilderService())->buildFromFamille($familleinfrastructure->first());
        }
    
        return view('infrastructures.famille.familleinfrastructure', [
            'domaine' => $domaine,
            'sous_domaines' => $sous_domaines,
            'familleinfrastructure' => $familleinfrastructure,
            'ecran' => $ecran,
            'caracteristiques' => $caracteristiques,
            'typesCaracteristique' => $typesCaracteristique,
            'groupeProjets' => $groupeProjets,
            'unites' => $unites,
            'codeFamilleGenere' => $codeFamilleGenere,
            'structure' => $structure
         ]);
    }
    public function getFamilleinfrastructure($code)
    {
        $familleinfrastructure = FamilleInfrastructure::find($code);

        if (!$familleinfrastructure) {
            return response()->json(['error' => 'Famille d\'infrastructure non trouvé'], 404);
        }

        return response()->json($familleinfrastructure);
    }
    public function getStructureCaracteristiques($id)
    {
        $famille = FamilleInfrastructure::with('caracteristiques')->findOrFail($id);
        $structure = (new CaracteristiqueBuilderService())->buildFromFamille($famille);
    
        return response()->json([
            'status' => 'success',
            'data' => $structure
        ]);
    }
    public function deleteFamilleInfrastructure($id)
    {
        $famille = FamilleInfrastructure::find($id);
    
        if (!$famille) {
            return redirect()->back()->with('error', 'Famille non trouvée.');
        }
    
        $famille->delete();
    
        return redirect()->back()->with('success', 'Famille supprimée avec succès.');
    }
    public function supprimerCaracteristiqueFamille($famille_id, $caracteristique_id)
    {
        $association = FamilleCaracteristique::where('idFamille', $famille_id)
            ->where('idCaracteristique', $caracteristique_id)
            ->first();
    
        if ($association) {
            $association->delete();
            return response()->json(['status' => 'success', 'message' => 'Caractéristique supprimée.']);
        }
    
        return response()->json(['status' => 'error', 'message' => 'Association introuvable.'], 404);
    }
    
    public function storeFamilleinfrastructure(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $request->validate([
                'libelle' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:familleinfrastructure,code_Ssys',
                'domaine' => 'required|array',
                'SDomaine' => 'nullable|array',
                'groupeProjet' => 'required|array',
            ]);
            $mapping = json_decode($request->input('domaine_mapping'), true);

            if (!is_array($mapping)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le mapping domaine/sous-domaine est invalide ou vide.',
                ]);
            }
            
            // Création de la famille
            $famille = new FamilleInfrastructure();
            $famille->libelleFamille = $request->input('libelle');
            $famille->code_Ssys = $request->input('code');
            $famille->save();
    
            $insertions = 0;
    
            foreach ($mapping as $row) {
                if (!empty($row['domaine']) && !empty($row['groupeProjet'])) {
                    FamilleDomaine::create([
                        'code_Ssys' => $famille->code_Ssys,
                        'code_domaine' => $row['domaine'],
                        'code_sdomaine' => $row['sdomaine'] ?? null, // peut rester null
                        'code_groupe_projet' => $row['groupeProjet'],
                    ]);
                    $insertions++;
                }
            }
            

    
            if ($insertions === 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucune association valide domaine / groupe projet fournie.',
                ]);
            }
            
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Famille créée avec succès.',
                'idFamille' => $famille->idFamille,
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
            ]);
        }
    }
    
    public function updateCaracteristiques(Request $request, $id)
    {
        try {
            $request->validate([
                'caracteristiques_json' => 'required'
            ]);
    
            $caracs = json_decode($request->caracteristiques_json, true);
    
            // Supprimer toutes les anciennes associations pour repartir proprement
            FamilleCaracteristique::where('idFamille', $id)->delete();
    
            foreach ($caracs as $carac) {
                // Vérifier si la caractéristique existe déjà
                $caracteristique = Caracteristique::firstOrCreate(
                    [
                        'libelleCaracteristique' => $carac['libelle'],
                        'idTypeCaracteristique' => $carac['type_id'],
                    ]
                );
    
                // Si type "liste", ajouter les valeurs possibles
                if (strtolower($carac['type_label']) === 'liste' && !empty($carac['valeurs_possibles'])) {
                    $valeurs = array_map('trim', explode(',', $carac['valeurs_possibles']));
                    foreach ($valeurs as $valeur) {
                        ValeurPossible::firstOrCreate([
                            'idCaracteristique' => $caracteristique->idCaracteristique,
                            'valeur' => $valeur
                        ]);
                    }
                }
    
                // Si type "nombre", associer une unité
                if (strtolower($carac['type_label']) === 'nombre') {
                    if (!empty($carac['unite_id']) && $carac['unite_id'] !== 'autre') {
                        $caracteristique->idUnite = $carac['unite_id'];
                        $caracteristique->save();
                    } elseif (!empty($carac['unite_libelle']) && !empty($carac['unite_symbole'])) {
                        $unite = Unite::firstOrCreate([
                            'libelleUnite' => $carac['unite_libelle'],
                            'symbole' => $carac['unite_symbole']
                        ]);
                        $caracteristique->idUnite = $unite->idUnite;
                        $caracteristique->save();
                    }
                }
    
                // Associer la caractéristique à la famille
                FamilleCaracteristique::firstOrCreate([
                    'idFamille' => $id,
                    'idCaracteristique' => $caracteristique->idCaracteristique
                ]);
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Caractéristiques mises à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }
    protected function enregistrerCaracteristiques(array $caracs, $familleId, $parentId = null)
    {
        foreach ($caracs as $carac) {
            $caracteristique = Caracteristique::create([
                'libelleCaracteristique' => $carac['libelle'],
                'idTypeCaracteristique' => $carac['type_id'],
                'idUnite' => $carac['unite_id'] ?? null,
                'parent_id' => $parentId,
                'is_repetable' => $carac['is_repetable'] ?? false,
            ]);

            // Valeurs possibles
            if ($carac['type_label'] === 'liste' && !empty($carac['valeurs_possibles'])) {
                foreach ($carac['valeurs_possibles'] as $valeur) {
                    ValeurPossible::create([
                        'idCaracteristique' => $caracteristique->idCaracteristique,
                        'valeur' => $valeur,
                    ]);
                }
            }

            FamilleCaracteristique::create([
                'idFamille' => $familleId,
                'idCaracteristique' => $caracteristique->idCaracteristique
            ]);

            // Récursion
            if (!empty($carac['children'])) {
                $this->enregistrerCaracteristiques($carac['children'], $familleId, $caracteristique->idCaracteristique);
            }
        }
    }

    public function storeCaracteristiquesFamille(Request $request)
    {
        try {
            $request->validate([
                'idFamille' => 'required|exists:familleinfrastructure,idFamille',
                'caracteristiques_json' => 'required'
            ]);
    
            $caracs = json_decode($request->caracteristiques_json, true);
    
            foreach ($caracs as $carac) {
                // Vérifie si caractéristique existe
                $existing = Caracteristique::where('libelleCaracteristique', $carac['libelle'])
                    ->where('idTypeCaracteristique', $carac['type_id'])
                    ->first();
    
                if (!$existing) {
                    $caracteristiqueData = [
                        'libelleCaracteristique' => $carac['libelle'],
                        'idTypeCaracteristique' => $carac['type_id'],
                    ];
                    $existing = Caracteristique::create($caracteristiqueData);

                    if (strtolower($carac['type_label']) === 'liste' && !empty($carac['valeurs_possibles'])) {
                        $valeurs = array_map('trim', explode(',', $carac['valeurs_possibles']));
                        foreach ($valeurs as $valeur) {
                            ValeurPossible::create([
                                'idCaracteristique' => $existing->idCaracteristique,
                                'valeur' => $valeur
                            ]);
                        }
                    }
                    if (strtolower($carac['type_label']) === 'nombre') {
                        if (!empty($carac['unite_id']) && $carac['unite_id'] !== 'autre') {
                            $caracteristiqueData['idUnite'] = $carac['unite_id'];
                        } elseif (!empty($carac['unite_libelle']) && !empty($carac['unite_symbole'])) {
                            $unite = Unite::firstOrCreate([
                                'libelleUnite' => $carac['unite_libelle'],
                                'symbole' => $carac['unite_symbole'],
                            ]);
                            $caracteristiqueData['idUnite'] = $unite->idUnite;
                        }
                    }

                    
    
                    
    
                    
                }
    
                FamilleCaracteristique::firstOrCreate([
                    'idFamille' => $request->idFamille,
                    'idCaracteristique' => $existing->idCaracteristique
                ]);
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Caractéristiques enregistrées avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }

    public function getCaracteristiquesFamille($id)
    {
        $caracs = FamilleCaracteristique::with(['caracteristique.type', 'caracteristique.valeursPossibles', 'caracteristique.unite'])
            ->where('idFamille', $id)
            ->get()
            ->map(function ($fc) {
                return [
                    'id' => $fc->caracteristique->idCaracteristique,
                    'libelle' => $fc->caracteristique->libelleCaracteristique,
                    'type_id' => $fc->caracteristique->type->idTypeCaracteristique,
                    'type_label' => $fc->caracteristique->type->libelleTypeCaracteristique,
                    'valeurs_possibles' => $fc->caracteristique->valeursPossibles->pluck('valeur')->toArray(),
                    'unite_libelle' => $fc->caracteristique->unite?->libelleUnite,
                    'unite_symbole' => $fc->caracteristique->unite?->symbole,
                ];
            });
    
        return response()->json($caracs);
    }

    public function getDomaineByGroupeProjet($code)
    {
        // Découpe la chaîne en tableau si plusieurs codes sont envoyés : "GP1,GP2,..."
        $codes = explode(',', $code);
    
        $domaines = Domaine::whereIn('groupe_projet_code', $codes)->get();
    
        if ($domaines->isEmpty()) {
            return response()->json(['error' => 'Aucun domaine trouvé'], 404);
        }
    
        return response()->json($domaines);
    }
    
    
    
    
    public function getSousDomaines($codeDomaine, $codeGroupeProjet)
    {
        $sousDomaines = SousDomaine::where('code_domaine', $codeDomaine)
                                    ->where('code_groupe_projet', $codeGroupeProjet)
                                    ->get();

        if ($sousDomaines->isEmpty()) {
            return response()->json(['error' => 'Aucun sous-domaine trouvé'], 404);
        }

        return response()->json($sousDomaines);
    }

    public function updateFamilleinfrastructure(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            $request->validate([
                'libelle' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:familleinfrastructure,code_Ssys,' . $id . ',idFamille',
                'domaine' => 'required|array',
                'SDomaine' => 'nullable|array',
                'groupeProjet' => 'required|array',
            ]);
    
            $mapping = json_decode($request->input('domaine_mapping'), true);
    
            if (!is_array($mapping)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le mapping domaine/sous-domaine est invalide ou vide.',
                ]);
            }
    
            // Récupérer la famille
            $famille = FamilleInfrastructure::findOrFail($id);
            $famille->libelleFamille = $request->input('libelle');
            $famille->code_Ssys = $request->input('code');
            $famille->save();
    
            // Supprimer les anciennes relations
            FamilleDomaine::where('code_Ssys', $famille->code_Ssys)->delete();
    
            $insertions = 0;
    
            foreach ($mapping as $row) {
                if (!empty($row['domaine']) && !empty($row['groupeProjet'])) {
                    FamilleDomaine::create([
                        'code_Ssys' => $famille->code_Ssys,
                        'code_domaine' => $row['domaine'],
                        'code_sdomaine' => $row['sdomaine'] ?? null,
                        'code_groupe_projet' => $row['groupeProjet'],
                    ]);
                    $insertions++;
                }
            }
    
            if ($insertions === 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucune association valide domaine / groupe projet fournie.',
                    
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Famille mise à jour avec succès.',
                'idFamille' => $famille->idFamille,
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ]);
        }
    }
    
    
    
    
    
    public function indexInfrastructure(){
        $infrastructures = Infrastructure::with(['familleInfrastructure', 'localisation'])
            ->where('code_groupe_projet', session('projet_selectionne'))
            ->where('code_pays', session('pays_selectionne'))
            ->get();
            
        return view('Infrastructures.index', compact('infrastructures'));
    }
    public function create()
    {
        $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))->get();
        $localites = Localite::all(); // Assuming you have a Localite model
        
        return view('infrastructures.create', compact('familles', 'localites'));
    }

    public function storeInfrastructure(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'code_famille_infrastructure' => 'required|exists:familleinfrastructure,idFamille',
            'code_commune' => 'required|exists:localites,code_commune',
            'date_operation' => 'required|date',
            'nature_travaux' => 'required|string|max:255',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
        ]);
        
        $infrastructure = new Infrastructure();
        $infrastructure->libelle = $request->libelle;
        $infrastructure->code_famille_infrastructure = $request->code_famille_infrastructure;
        $infrastructure->code_groupe_projet = session('projet_selectionne');
        $infrastructure->code_commune = $request->code_commune;
        $infrastructure->date_operation = $request->date_operation;
        $infrastructure->nature_travaux = $request->nature_travaux;
        $infrastructure->longitude = $request->longitude;
        $infrastructure->latitude = $request->latitude;
        $infrastructure->save();

        return redirect()->route('infrastructures.index')
            ->with('success', 'Infrastructure créée avec succès.');
    }

    public function editInfrastructure($id)
    {
        $infrastructure = Infrastructure::findOrFail($id);
        $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))->get();
        $localites = Localite::all();
        
        return view('infrastructures.edit', compact('infrastructure', 'familles', 'localites'));
    }

    //***************** FONCTION UTILISATEUR  ************* */
    public function fonctionUtilisateur(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $fonctionUtilisateur = FonctionUtilisateur::orderBy('libelle_fonction', 'asc')->get();
        return view('parGeneraux.fonctionUtilisateur', ['fonctionUtilisateur' => $fonctionUtilisateur, 'ecran' => $ecran,]);
    }



    public function getFonctionUtilisateur($code)
    {
        $fonctionUtilisateur = FonctionUtilisateur::find($code);

        if (!$fonctionUtilisateur) {
            return response()->json(['error' => 'Fonction Utilisateur non trouvé'], 404);
        }

        return response()->json($fonctionUtilisateur);
    }

    public function storeFonctionUtilisateur(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $fonctionUtilisateur = new FonctionUtilisateur;
        $fonctionUtilisateur->code = $request->input('code');
        $fonctionUtilisateur->libelle_fonction = $request->input('libelle');

        $fonctionUtilisateur->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.fonctionUtilisateur', ['ecran_id' => $ecran_id])->with('success', 'Fonction Utilisateur enregistré avec succès.');
    }
    public function updateFonctionUtilisateur(Request $request)
    {
        $fonctionUtilisateur = FonctionUtilisateur::find($request->input('code_edit'));

        if (!$fonctionUtilisateur) {
            return response()->json(['error' => 'Fonction Utilisateur non trouvé'], 404);
        }

        $fonctionUtilisateur->libelle_fonction = $request->input('libelle_edit');


        $fonctionUtilisateur->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.fonctionUtilisateur', ['ecran_id' => $ecran_id])->with('success', 'Fonction Utilisateur mis à jour avec succès.');
    }

    public function deleteFonctionUtilisateur($code)
    {
        $fonctionUtilisateur = FonctionUtilisateur::find($code);

        if (!$fonctionUtilisateur) {
            return response()->json(['error' => 'Fonction Utilisateur non trouvé'], 404);
        }
        $ocuper_fonction = OccuperFonction::where('code_fonction', $code)->first();

        if ($ocuper_fonction) {
            return response()->json(['error' => "Suppression interdite : La fonction est utilisée dans d'autres tables"], 404);
        }
        $fonctionUtilisateur->delete();

        return response()->json(['success' => 'Fonction Utilisateur supprimé avec succès']);
    }

    public function checkFonctionUtilisateurCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = FonctionUtilisateur::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    //***************** FONCTION GROUPES  ************* */
    public function fonctionGroupe(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $fonctionGroupe = Fonction_groupe_utilisateur::with('groupeUtilisateur')->with('fonction')->get();
        $fonctions = FonctionUtilisateur::orderBy('libelle_fonction', 'asc')->get();
        $groupes = Role::orderBy('name', 'asc')->get();
        return view('parGeneraux.fonctionGroupe', ['fonctionGroupe' => $fonctionGroupe,'ecran' => $ecran,  'fonctions' => $fonctions, 'groupes' => $groupes,]);
    }
    public function storeFonctionGroupe(Request $request)
    {
        $groupesSel = json_decode($request->input('groupes'), true);
        $groupesSelect = $groupesSel['groupes'];

        // Ajoutez les nouvelles associations sélectionnées
        foreach ($groupesSelect as $gs) {
            Fonction_groupe_utilisateur::updateOrCreate(
                [
                    'code_fonction' => $request->input('fonction'),
                    'code_groupe_utilisateur' => $gs
                ]
            );
        }
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return response()->json(['success' => 'Fonction Groupe enregistré avec succès.', 'donnees' => $groupesSelect]);
        //return redirect()->route('parGeneraux.fonctionUtilisateur', ['ecran_id' => $ecran_id])->with('success', 'Fonction Utilisateur enregistré avec succès.');
    }


    public function deleteFonctionGroupe($code)
    {
        $fonctionGroupe = Fonction_groupe_utilisateur::find($code);

        if (!$fonctionGroupe) {
            return response()->json(['error' => 'Fonction Groupe Utilisateur non trouvé'], 404);
        }
        // $ocuper_fonction = OccuperFonction::where('code_fonction', $fonctionGroupe->fonction->code)->first();
        // $apartenirGroupeUtilisateur = ApartenirGroupeUtilisateur::where('code_groupe_utilisateur',$fonctionGroupe->groupeUtilisateur->code)->first();

        // if ($apartenirGroupeUtilisateur) {
        //      return response()->json(['error' => "Suppression interdite : Le Groupe Utilisateur est utilisé dans d'autres tables"], 404);
        //  }
        // if ($ocuper_fonction) {
        //     return response()->json(['error' => "Suppression interdite : La fonction est utilisée dans d'autres tables"], 404);
        // }
        $fonctionGroupe->delete();

        return response()->json(['success' => 'Fonction Groupe Utilisateur supprimé avec succès']);
    }

    //*****************GENRE  ************* */
    public function genre(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $genre = Genre::orderBy('libelle_genre', 'asc')->get();
        return view('parGeneraux.genre', ['genre' => $genre,'ecran' => $ecran, ]);
    }



    public function getGenre($code)
    {
        $genre = Genre::find($code);

        if (!$genre) {
            return response()->json(['error' => 'Genre non trouvé'], 404);
        }

        return response()->json($genre);
    }

    public function storeGenre(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $genre = new Genre;
        $genre->code_genre = $request->input('code');
        $genre->libelle_genre = $request->input('libelle');

        $genre->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.genre', ['ecran_id' => $ecran_id])->with('success', 'Genre enregistré avec succès.');
    }
    public function updateGenre(Request $request)
    {
        $Genre = Genre::find($request->input('code_edit'));

        if (!$Genre) {
            return response()->json(['error' => 'Genre non trouvé'], 404);
        }

        $Genre->libelle_genre = $request->input('libelle_edit');


        $Genre->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.genre', ['ecran_id' => $ecran_id])->with('success', 'Genre mis à jour avec succès.');
    }

    public function deleteGenre($code)
    {
        $Genre = Genre::find($code);

        if (!$Genre) {
            return response()->json(['error' => 'Genre non trouvé'], 404);
        }
        // $ocuper_fonction = OccuperFonction::where('code_fonction', $code)->first();

        // if ($ocuper_fonction) {
        //     return response()->json(['error' => "Suppression interdite : La fonction est utilisée dans d'autres tables"], 404);
        // }
        $Genre->delete();

        return response()->json(['success' => 'Genre supprimé avec succès']);
    }

    public function checkGenreCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Genre::where('code_genre', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    //*****************UNITE TRAITEMENT  ************* */
    public function uniteTraitement(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $uniteTraitement = UniteTraitement::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.uniteTraitement', ['uniteTraitement' => $uniteTraitement,'ecran' => $ecran, ]);
    }





    //*****************MATERIEL STOCKAGE  ************* */
    public function materielStockage(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $materielStockage = MaterielStockage::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.materielStockage', ['materielStockage' => $materielStockage,'ecran' => $ecran, ]);
    }

    public function getMaterielStockage($code)
    {
        $materielStockage = MaterielStockage::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Materiel de Stockage non trouvé'], 404);
        }

        return response()->json($materielStockage);
    }

    public function storeMaterielStockage(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $materielStockage = new MaterielStockage;
        $materielStockage->code = $request->input('code');
        $materielStockage->libelle = $request->input('libelle');

        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.materielStockage', ['ecran_id' => $ecran_id])->with('success', 'Materiel de Stockage enregistré avec succès.');
    }
    public function updateMaterielStockage(Request $request)
    {
        $materielStockage = MaterielStockage::find($request->input('code_edit'));

        if (!$materielStockage) {
            return response()->json(['error' => 'Materiel de Stockage non trouvé'], 404);
        }

        $materielStockage->libelle = $request->input('libelle_edit');


        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.materielStockage', ['ecran_id' => $ecran_id])->with('success', 'Materiel de Stockage mis à jour avec succès.');
    }

    public function deleteMaterielStockage($code)
    {
        $materielStockage = MaterielStockage::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'MaterielStockage non trouvé'], 404);
        }
        // $apartenirGroupeUtilisateur = ApartenirGroupeUtilisateur::where('code_groupe_utilisateur', $code)->first();

        // if ($apartenirGroupeUtilisateur) {
        //      return response()->json(['error' => "Suppression interdite : Le Groupe Utilisateur est utilisé dans d'autres tables"], 404);
        //  }
        $materielStockage->delete();

        return response()->json(['success' => 'Materiel de Stockage supprimé avec succès']);
    }

    public function checkMaterielStockageCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = MaterielStockage::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    //*****************NIVEAU ACCES AU DONNEES  ************* */
    public function niveauAccesDonnees(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $niveauAccesDonnees = NiveauAccesDonnees::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.niveauAccesDonnees', ['niveauAccesDonnees' => $niveauAccesDonnees,'ecran' => $ecran, ]);
    }


    public function getNiveauAccesDonnees($code)
    {
        $materielStockage = NiveauAccesDonnees::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Niveau non trouvé'], 404);
        }

        return response()->json($materielStockage);
    }

    public function storeNiveauAccesDonnees(Request $request)
    {
        $materielStockage = new NiveauAccesDonnees;
        $materielStockage->id = $request->input('code');
        $materielStockage->libelle = $request->input('libelle');

        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.niveauAccesDonnees', ['ecran_id' => $ecran_id])->with('success', 'Niveau enregistré avec succès.');
    }
    public function updateNiveauAccesDonnees(Request $request)
    {
        $materielStockage = NiveauAccesDonnees::find($request->input('code_edit'));

        if (!$materielStockage) {
            return response()->json(['error' => 'Niveau non trouvé'], 404);
        }

        $materielStockage->libelle = $request->input('libelle_edit');


        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.niveauAccesDonnees', ['ecran_id' => $ecran_id])->with('success', 'Niveau mis à jour avec succès.');
    }

    public function deleteNiveauAccesDonnees($code)
    {
        $materielStockage = NiveauAccesDonnees::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Niveau non trouvé'], 404);
        }
        $user = User::where('niveau_acces_id', $code)->first();

        if ($user) {
            return response()->json(['error' => "Suppression interdite : Le niveau est utilisé dans d'autres tables"], 404);
        }
        $materielStockage->delete();

        return response()->json(['success' => 'Niveau supprimé avec succès']);
    }

    public function checkNiveauAccesDonneesCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = NiveauAccesDonnees::where('id', $code)->exists();

        return response()->json(['exists' => $exists]);
    }




    //***************** OUTILS DE COLLECTE  ************* */
    public function outilsCollecte(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $outilsCollecte = OutilsCollecte::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.outilsCollecte', ['outilsCollecte' => $outilsCollecte, 'ecran' => $ecran,]);
    }

    public function getOutilsCollecte($code)
    {
        $materielStockage = OutilsCollecte::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Outils de Collecte non trouvé'], 404);
        }

        return response()->json($materielStockage);
    }

    public function storeOutilsCollecte(Request $request)
    {
        $materielStockage = new OutilsCollecte;
        $materielStockage->code = $request->input('code');
        $materielStockage->libelle = $request->input('libelle');

        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.outilsCollecte', ['ecran_id' => $ecran_id])->with('success', 'Outils de Collecte enregistré avec succès.');
    }
    public function updateOutilsCollecte(Request $request)
    {
        $materielStockage = OutilsCollecte::find($request->input('code_edit'));

        if (!$materielStockage) {
            return response()->json(['error' => 'Outils de Collecte non trouvé'], 404);
        }

        $materielStockage->libelle = $request->input('libelle_edit');


        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.outilsCollecte', ['ecran_id' => $ecran_id])->with('success', 'Outils de Collecte mis à jour avec succès.');
    }

    public function deleteOutilsCollecte($code)
    {
        $materielStockage = OutilsCollecte::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Outils de Collecte non trouvé'], 404);
        }
        // $user = User::where('niveau_acces_id', $code)->first();

        // if ($user) {
        //     return response()->json(['error' => "Suppression interdite : Le niveau est utilisé dans d'autres tables"], 404);
        // }
        $materielStockage->delete();

        return response()->json(['success' => 'Outils de Collecte supprimé avec succès']);
    }

    public function checkOutilsCollecteCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = OutilsCollecte::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    //***************** OUVRAGE DE TRANSPORT  ************* */
    public function ouvrageTransport(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $ouvrageTransport = OuvrageTransport::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.ouvrageTransport', ['ouvrageTransport' => $ouvrageTransport, 'ecran' => $ecran,]);
    }


    public function getOuvrageTransport($code)
    {
        $materielStockage = OuvrageTransport::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Ouvrage de Transport non trouvé'], 404);
        }

        return response()->json($materielStockage);
    }

    public function storeOuvrageTransport(Request $request)
    {
        $materielStockage = new OuvrageTransport;
        $materielStockage->code = $request->input('code');
        $materielStockage->libelle = $request->input('libelle');

        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.ouvrageTransport', ['ecran_id' => $ecran_id])->with('success', 'Ouvrage de Transport enregistré avec succès.');
    }
    public function updateOuvrageTransport(Request $request)
    {
        $materielStockage = OuvrageTransport::find($request->input('code_edit'));

        if (!$materielStockage) {
            return response()->json(['error' => 'Outils de Collecte non trouvé'], 404);
        }

        $materielStockage->libelle = $request->input('libelle_edit');


        $materielStockage->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parGeneraux.ouvrageTransport', ['ecran_id' => $ecran_id])->with('success', 'Ouvrage de Transport mis à jour avec succès.');
    }

    public function deleteOuvrageTransport($code)
    {
        $materielStockage = OuvrageTransport::find($code);

        if (!$materielStockage) {
            return response()->json(['error' => 'Ouvrage de Transport non trouvé'], 404);
        }
        // $user = User::where('niveau_acces_id', $code)->first();

        // if ($user) {
        //     return response()->json(['error' => "Suppression interdite : Le niveau est utilisé dans d'autres tables"], 404);
        // }
        $materielStockage->delete();

        return response()->json(['success' => 'Ouvrage de Transport supprimé avec succès']);
    }

    public function checkOuvrageTransportCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = OuvrageTransport::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }




    //*****************  STATUT PROJET  ************* */
    public function statutProjet(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $statutProjet = StatutProjet::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.statutProjet', ['statutProjet' => $statutProjet,'ecran' => $ecran, ]);
    }

    //*****************  TYPE BAILLEUR  ************* */
    public function typeBailleur(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeBailleur = typeBailleur::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeBailleur', ['typeBailleur' => $typeBailleur, 'ecran' => $ecran,]);
    }

    //*****************  TYPE ETABLISSEMENT  ************* */
    public function typeEtablissement(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeEtablissement = TypeEtablissement::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeEtablissement', ['typeEtablissement' => $typeEtablissement,'ecran' => $ecran, ]);
    }

    //*****************  TYPE MATERIAUX DE CONDUITE  ************* */
    public function typeMateriauxConduite(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeMateriauxConduite = TypeMateriauxConduite::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeMateriauxConduite', ['typeMateriauxConduite' => $typeMateriauxConduite,'ecran' => $ecran, ]);
    }
    //*****************  TYPE RESEAUX  ************* */
    public function typeResaux(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeResaux = TypeResaux::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeResaux', ['typeResaux' => $typeResaux, 'ecran' => $ecran,]);
    }

    //*****************  TYPE STATTION  ************* */
    public function typeStation(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeStation = TypeStation::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeStation', ['typeStation' => $typeStation,'ecran' => $ecran, ]);
    }


    //*****************  TYPE STOCKAGE  ************* */
    public function typeStockage(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeStockage = TypeStockage::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeStockage', ['typeStockage' => $typeStockage, 'ecran' => $ecran,]);
    }

    //*****************  UNITE RESEAUX  ************* */
    public function uniteStockage(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        //$uniteStockage = UniteStockage::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.uniteStockage', [ /*'uniteStockage' => $uniteStockage, */'ecran' => $ecran,]);
    }
    //*****************  UNITE DISTANCE  ************* */
    public function uniteDistance(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $uniteDistance = UniteDistance::orderBy('libelle_long', 'asc')->get();
        return view('parGeneraux.uniteDistance', ['uniteDistance' => $uniteDistance,'ecran' => $ecran, ]);
    }

    //*****************  UNITE DE MESURE  ************* */
    public function uniteMesure(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $uniteMesure = UniteMesure::orderBy('libelle_long', 'asc')->get();
        return view('parGeneraux.uniteMesure', ['uniteMesure' => $uniteMesure, 'ecran' => $ecran,]);
    }

    //*****************  UNITE DE SURFACE  ************* */
    public function uniteSurface(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $uniteSurface = UniteSurface::orderBy('libelle_long', 'asc')->get();
        return view('parGeneraux.uniteSurface', ['uniteSurface' => $uniteSurface, 'ecran' => $ecran,]);
    }
    //*****************  UNITE DE VOLUME  ************* */
    public function uniteVolume(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $uniteVolume = uniteVolume::orderBy('libelle_long', 'asc')->get();
        return view('parGeneraux.uniteVolume', ['uniteVolume' => $uniteVolume, 'ecran' => $ecran,]);
    }
    //*****************  TYPE DE RESERVOUR  ************* */
    public function typeReservoire(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        //$typeReservoire = TypeReservoire::orderBy('libelle', 'asc')->get();
        return view('parGeneraux.typeReservoire', [ /*'typeReservoire' => $typeReservoire, */'ecran' => $ecran,]);
    }

    //*****************  TYPE D'INSTRUMENT  ************* */
    public function typeInstrument(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $typeInstrument = TypeInstrument::orderBy('code', 'asc')->get();
        return view('parGeneraux.typeInstrument', ['typeInstrument' => $typeInstrument, 'ecran' => $ecran,]);
    }

}
