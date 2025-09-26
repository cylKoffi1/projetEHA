<?php

namespace App\Http\Controllers;

use App\Models\ActionMener;
use App\Models\Approbateur;
use App\Models\Banque;
use App\Models\Devise;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FamilleInfrastructure;
use App\Models\Fonction_groupe_utilisateur;
use App\Models\FonctionUtilisateur;
use App\Models\Genre;
use App\Models\OccuperFonction;
use App\Models\SousDomaine;
use Illuminate\Http\Request;
use App\Models\Pays;
use App\Models\TypeCaracteristique;
use App\Models\Caracteristique;
use App\Models\FamilleCaracteristique;
use App\Models\FamilleDomaine;
use App\Models\GroupeProjet;
use App\Models\Infrastructure;
use App\Models\LocalitesPays;
use App\Models\Projet;
use App\Models\Unite;
use App\Models\ValeurPossible;
use App\Services\CaracteristiqueBuilderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;


class PlateformeController extends Controller
{
    
    /******************* BANQUES  ***********************/
        public function indexBanques(Request $request)
        {
            // L’écran est utile pour les permissions en Blade
            $ecran = Ecran::find($request->input('ecran_id'));
            $pays = Pays::orderBy('nom_fr_fr')->where('alpha3', session('pays_selectionne'))->first();
            return view('GestionFinanciere.banques', compact('pays','ecran'));
        }

        public function listBanques(Request $request)
        {
            try {
                $banques = Banque::with(['pays:id,alpha3,nom_fr_fr'])
                ->where('code_pays', session('pays_selectionne'))->orderBy('nom')->get();
                return response()->json(['ok' => true, 'data' => $banques]);
            } catch (\Throwable $e) {
                Log::error('Banque list error', ['ex' => $e]);
                return response()->json(['ok' => false, 'message' => "Erreur lors du chargement."], 500);
            }
        }

        public function storeBanques(Request $request)
        {
            try {
                $ecran = Ecran::find($request->input('ecran_id'));
                Gate::authorize("ajouter_ecran_{$ecran->id}");

                $v = Validator::make($request->all(), [
                    'nom' => 'required|string|max:191',
                    'sigle' => 'nullable|string|max:50',
                    'est_internationale' => 'required|boolean',
                    'code_pays' => 'nullable|string|size:3',
                    'code_swift' => 'nullable|string|max:11',
                    'adresse' => 'nullable|string',
                    'telephone' => 'nullable|string|max:50',
                    'email' => 'nullable|email|max:191',
                    'site_web' => 'nullable|url|max:191',
                    'actif' => 'required|boolean',
                ]);
                if ($v->fails()) {
                    return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
                }

                // Normalisation
                $data = $v->validated();
                $data['code_pays'] = $data['est_internationale'] ? null : (isset($data['code_pays']) ? strtoupper($data['code_pays']) : null);

                $banque = Banque::create($data);

                Log::info('Banque created', ['id' => $banque->id, 'by' => $request->user()->id]);

                return response()->json(['ok' => true, 'message' => 'Banque créée avec succès.', 'data' => $banque]);
            } catch (\Throwable $e) {
                Log::error('Banque store error', ['ex' => $e, 'payload' => $request->all()]);
                return response()->json(['ok' => false, 'message' => "Création impossible."], 500);
            }
        }

        public function updateBanques($id, Request $request)
        {
            try {
                $ecran = Ecran::find($request->input('ecran_id'));
                Gate::authorize("modifier_ecran_{$ecran->id}");

                $banque = Banque::findOrFail($id);

                $v = Validator::make($request->all(), [
                    'nom' => 'required|string|max:191',
                    'sigle' => 'nullable|string|max:50',
                    'est_internationale' => 'required|boolean',
                    'code_pays' => 'nullable|string|size:3',
                    'code_swift' => 'nullable|string|max:11',
                    'adresse' => 'nullable|string',
                    'telephone' => 'nullable|string|max:50',
                    'email' => 'nullable|email|max:191',
                    'site_web' => 'nullable|url|max:191',
                    'actif' => 'required|boolean',
                ]);
                if ($v->fails()) {
                    return response()->json(['ok' => false, 'message' => $v->errors()->first()], 422);
                }

                $data = $v->validated();
                $data['code_pays'] = $data['est_internationale'] ? null : (isset($data['code_pays']) ? strtoupper($data['code_pays']) : null);

                $banque->update($data);

                Log::info('Banque updated', ['id' => $banque->id, 'by' => $request->user()->id]);

                return response()->json(['ok' => true, 'message' => 'Banque mise à jour.', 'data' => $banque]);
            } catch (\Throwable $e) {
                Log::error('Banque update error', ['ex' => $e, 'id' => $id, 'payload' => $request->all()]);
                return response()->json(['ok' => false, 'message' => "Mise à jour impossible."], 500);
            }
        }

        public function destroyBanques($id, Request $request)
        {
            try {
                $ecran = Ecran::find($request->input('ecran_id'));
                Gate::authorize("supprimer_ecran_{$ecran->id}");

                $banque = Banque::findOrFail($id);
                $banque->delete();

                Log::info('Banque deleted', ['id' => $banque->id, 'by' => $request->user()->id]);

                return response()->json(['ok' => true, 'message' => 'Banque supprimée.']);
            } catch (\Throwable $e) {
                Log::error('Banque delete error', ['ex' => $e, 'id' => $id]);
                return response()->json(['ok' => false, 'message' => "Suppression impossible."], 500);
            }
        }
    /******************* FIN BANQUES  ***********************/   
   
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
            $ecran   = Ecran::find($request->input('ecran_id'));
            $devises = Devise::orderBy('libelle', 'asc')->get();

            // Pays disponibles + mapping devise -> pays
            $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
            $paysParDevise = $pays->groupBy('code_devise'); // ['XOF' => [pays...], ...]

            return view('parGeneraux.devises', [
                'devises'       => $devises,
                'ecran'         => $ecran,
                'pays'          => $pays,
                'paysParDevise' => $paysParDevise,
            ]);
        }

        public function getDevise($code)
        {
            $devise = Devise::find($code);
            if (!$devise) {
                return response()->json(['error' => 'Dévise non trouvé'], 404);
            }

            // IDs des pays actuellement liés à cette dévise
            $paysIds = Pays::where('code_devise', $devise->code)->pluck('id')->all();

            // On renvoie la dévise + la liste des pays liés (IDs) pour pré-sélection
            $payload = $devise->toArray();
            $payload['pays_ids'] = $paysIds;

            return response()->json($payload);
        }

        public function storeDevise(Request $request)
        {
            $request->validate([
                'code'       => 'required|string|max:10|unique:devise,code',
                'libelle'    => 'required|string|max:255',
                'monnaie'    => 'required|string|max:255',
                'code_long'  => 'required|string|max:255',
                'code_court' => 'required|string|max:255',
                'pays_ids'   => 'array',
                'pays_ids.*' => 'integer|exists:pays,id',
            ]);

            $devise = new Devise;
            $devise->code       = $request->input('code');
            $devise->libelle    = $request->input('libelle');
            $devise->monnaie    = $request->input('monnaie');
            $devise->code_long  = $request->input('code_long');
            $devise->code_court = $request->input('code_court');
            $devise->save();

            // Associer aux pays sélectionnés (on ne détache pas les autres)
            $paysIds = $request->input('pays_ids', []);
            if (!empty($paysIds)) {
                Pays::whereIn('id', $paysIds)->update(['code_devise' => $devise->code]);
            }

            $ecran_id = $request->input('ecran_id');
            return redirect()->route('parGeneraux.devises', ['ecran_id' => $ecran_id])
                ->with('success', 'Dévise enregistrée avec succès.');
        }

        public function updateDevise(Request $request)
        {
            $request->validate([
                'code_edit'   => 'required|exists:devise,code',
                'libelle_edit'=> 'required|string|max:255',
                'monnaie_edit'=> 'required|string|max:255',
                'code_long_edit' => 'required|string|max:255',
                'code_court_edit'=> 'required|string|max:255',
                'pays_ids'    => 'array',
                'pays_ids.*'  => 'integer|exists:pays,id',
            ]);

            $devise = Devise::find($request->input('code_edit'));
            if (!$devise) {
                return response()->json(['error' => 'Dévise non trouvé'], 404);
            }

            $devise->libelle    = $request->input('libelle_edit');
            $devise->monnaie    = $request->input('monnaie_edit');
            $devise->code_long  = $request->input('code_long_edit');
            $devise->code_court = $request->input('code_court_edit');
            $devise->save();

            // Associer aux pays sélectionnés (sans détacher par défaut)
            $paysIds = $request->input('pays_ids', []);
            if (!empty($paysIds)) {
                Pays::whereIn('id', $paysIds)->update(['code_devise' => $devise->code]);
            }

            $ecran_id = $request->input('ecran_id');
            return redirect()->route('parGeneraux.devises', ['ecran_id' => $ecran_id])
                ->with('success', 'Dévise mise à jour avec succès.');
        }


    //***************** ACTIONS A MENER ************* */
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

   

    // Enregistrer une liste d’approbateurs
    public function storeApprobation(Request $request)
    {
        $request->validate([
            'approbateurs' => 'required|json'
        ]);

        $pays   = session('pays_selectionne');
        $projet = session('projet_selectionne');

        if (!$pays || !$projet) {
            return back()->withErrors(['Session invalide : pays ou projet non défini.']);
        }

        $payload = json_decode($request->input('approbateurs'), true) ?: [];
        $errors  = [];

        // Doublons intra-payload
        $userCodes = [];
        $orders    = [];
        foreach ($payload as $item) {
            if (in_array($item['userCode'], $userCodes, true)) {
                $errors[] = "L'utilisateur {$item['userCode']} est en double dans la requête.";
            } else {
                $userCodes[] = $item['userCode'];
            }
            if (in_array((int)$item['nordre'], $orders, true)) {
                $errors[] = "Le numéro d'ordre {$item['nordre']} est en double dans la requête.";
            } else {
                $orders[] = (int)$item['nordre'];
            }
        }
        if ($errors) return back()->withErrors($errors)->withInput();

        DB::beginTransaction();
        try {
            foreach ($payload as $row) {
                $userCode = $row['userCode'];
                $nordre   = (int)$row['nordre'];

                // Unicités côté DB (scope pays/projet)
                $existsUser = Approbateur::scoped($pays, $projet)->where('code_acteur', $userCode)->exists();
                if ($existsUser) {
                    $errors[] = "L'utilisateur $userCode est déjà un approbateur.";
                    continue;
                }

                $existsOrder = Approbateur::scoped($pays, $projet)->where('numOrdre', $nordre)->exists();
                if ($existsOrder) {
                    $errors[] = "Le numéro d'ordre $nordre est déjà utilisé.";
                    continue;
                }

                Approbateur::create([
                    'code_acteur'    => $userCode,
                    'numOrdre'       => $nordre,
                    'groupeProjetId' => $projet,
                    'codePays'       => $pays,
                ]);
            }

            if ($errors) {
                DB::rollBack();
                return back()->withErrors($errors)->withInput();
            }

            DB::commit();
            return back()->with('success', 'Approbateurs enregistrés avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur enregistrement approbateurs', ['err' => $e->getMessage()]);
            return back()->withErrors(['error' => "Une erreur est survenue : {$e->getMessage()}"])->withInput();
        }
    }

    // Modifier un approbateur
    public function updateApprobateur(Request $request)
    {
        $pays   = session('pays_selectionne');
        $projet = session('projet_selectionne');

        $request->validate([
            'numOrdreId'  => 'required|integer|min:1',
            'editNordre'  => 'required|integer|min:1',
            'editUserapp' => 'required|string',
        ]);

        $approbateur = Approbateur::scoped($pays, $projet)
            ->where('numOrdre', (int)$request->input('numOrdreId'))
            ->first();

        if (!$approbateur) {
            return back()->with('error', 'Approbateur non trouvé.');
        }

        $newOrder = (int)$request->input('editNordre');
        $newUser  = $request->input('editUserapp');

        // Vérifier conflits
        if ($newOrder !== (int)$approbateur->numOrdre) {
            $orderTaken = Approbateur::scoped($pays, $projet)->where('numOrdre', $newOrder)->exists();
            if ($orderTaken) return back()->with('error', "Le niveau $newOrder est déjà utilisé.");
        }
        if ($newUser !== $approbateur->code_acteur) {
            $userTaken = Approbateur::scoped($pays, $projet)->where('code_acteur', $newUser)->exists();
            if ($userTaken) return back()->with('error', "Cet utilisateur est déjà approbateur.");
        }

        $approbateur->update([
            'numOrdre'   => $newOrder,
            'code_acteur'=> $newUser,
        ]);

        return back()->with('success', 'Approbateur modifié avec succès.');
    }

    // Supprimer un approbateur (et re-numeroter dans le scope)
    public function deleteApprobation($id)
    {
        $approbateur = Approbateur::find($id);
        if (!$approbateur) {
            return response()->json(['error' => "L'approbateur n'existe pas."], 404);
        }

        try {
            DB::transaction(function () use ($approbateur) {
                $pays   = $approbateur->codePays;
                $projet = $approbateur->groupeProjetId;
                $num    = $approbateur->numOrdre;

                $approbateur->delete();

                Approbateur::scoped($pays, $projet)
                    ->where('numOrdre', '>', $num)
                    ->decrement('numOrdre');
            });

            return response()->json(['success' => 'Approbateur supprimé avec succès'], 200);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression approbateur', ['err' => $e->getMessage()]);
            return response()->json(['error' => "Erreur lors de la suppression."], 500);
        }
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
        $localites = LocalitesPays::all(); // Assuming you have a Localite model
        
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
        $localites = LocalitesPays::all();
        
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
        $groupes = Role::orderBy('libelle_groupe', 'asc')->get();
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


}
