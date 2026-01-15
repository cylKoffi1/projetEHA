<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\DecoupageAdministratif;
use App\Models\DecoupageAdminPays;
use App\Models\Devise;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FamilleInfrastructure;
use App\Models\Financer;
use App\Models\FormeJuridique;
use App\Models\Genre;
use App\Models\GroupeProjet;
use App\Models\GroupeProjetPaysUser;
use App\Models\GroupeUtilisateur;
use App\Models\Infrastructure;
use App\Models\LocalitesPays;
use App\Models\Modalite;
use App\Models\NatureTravaux;
use App\Models\Pays;
use App\Models\Pieceidentite;
use App\Models\Projet;
use App\Models\ProjetActionAMener;
use App\Models\ProjetDocument;
use App\Models\ProjetInfrastructure;
use App\Models\ProjetLocalisation;
use App\Models\ProjetStatut;
use App\Models\SecteurActivite;
use App\Models\SousDomaine;
use App\Models\StatutOperation;
use App\Models\TypeFinancement;
use App\Models\TypeCaracteristique;
use App\Models\UniteDerivee;
use App\Models\ValeurCaracteristique;
use App\Models\FamilleDomaine;
use App\Models\AppuiProjet;
use App\Models\Beneficier;
use App\Models\Jouir;
use App\Models\Profiter;
use App\Services\FileProcService;
use App\Support\ApprovesWithWorkflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProjetAppuiController extends Controller
{
    use ApprovesWithWorkflow; // pour startApproval()

    /*************************  VUE PRINCIPALE (STEP WIZARD)  *************************/
    public function createNaissanceAppui(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));

        // code projet "défaut" (visuel) — 100% facultatif
        $generatedCodeProjet = $this->generateSimpleVisualCode();

        $paysSelectionne    = session('pays_selectionne');   // ex: alpha3
        $groupeSelectionne  = session('projet_selectionne'); // ex: code groupe

        $user   = auth()->user();
        $groupe = GroupeUtilisateur::where('code', $user->groupe_utilisateur_id)->first();

        // Référentiels (limités aux colonnes utiles)
        $NaturesTravaux   = NatureTravaux::orderBy('libelle')->get();
        $GroupeProjets    = GroupeProjet::orderBy('libelle')->get(['code','libelle']);
        $Domaines         = Domaine::where('groupe_projet_code', $groupeSelectionne)->orderBy('libelle')->get(['code','libelle']);
        $SousDomaines     = SousDomaine::orderBy('lib_sous_domaine')->get(['code_sous_domaine','lib_sous_domaine']);
        $SecteurActivites = SecteurActivite::orderBy('libelle')->get(['id','code','libelle']);

        $Pays = GroupeProjetPaysUser::with('pays')
            ->select('pays_code')
            ->distinct()
            ->where('pays_code', $paysSelectionne)
            ->get()
            ->pluck('pays.nom_fr_fr', 'pays.alpha3')
            ->sort();

        $Dpays = Pays::where('alpha3', $paysSelectionne)->firstOrFail();

        $deviseCouts = Devise::select('libelle', 'code_long')
            ->where('code_long', $Dpays->code_devise)
            ->first();


        $Devises = Pays::where('alpha3', $paysSelectionne)->get(['alpha3','code_devise']); // pour Step 5 (affichage devise)
        $typeFinancements = TypeFinancement::all();
        $unitesDerivees = UniteDerivee::with('uniteBase')->get()->groupBy('id_unite_base');

        // acteurs initialisables si besoin
        $acteurs = Acteur::where('code_pays', $paysSelectionne)->get(['code_acteur','libelle_court','libelle_long','type_acteur']);

        $directMode = false; // Mode avec validation
        
        return view('projet_appui.naissance', compact(
            'ecran', 'typeFinancements','generatedCodeProjet','NaturesTravaux','GroupeProjets','Domaines','SousDomaines',
            'SecteurActivites','Pays','deviseCouts','groupeSelectionne','Devises','unitesDerivees','acteurs', 'directMode'
        ));
    }


    private function generateSimpleVisualCode(): string
    {
        return 'TMP-'.Str::upper(Str::random(6));
    }

    /*************************  STEP 1  *************************/
    public function saveAppuiStep1(Request $request)
    {
        $request->validate([
            'libelle_projet'        => 'required|string|max:255',
            'code_domaine'          => 'required|string|max:10',  // important pour Step 2
            'code_sous_domaine'     => 'required|string|max:10',
            'date_demarrage_prevue' => 'required|date',
            'date_fin_prevue'       => 'required|date|after_or_equal:date_demarrage_prevue',
            'cout_projet'           => 'required|numeric|min:0',
            'code_devise'           => 'required|string|max:3',
            'code_nature'           => 'required|string|max:10',
            'code_pays'             => 'required|string|max:3',
            'commentaire'           => 'nullable|string|max:500',
        ]);

        session(['form_step1' => $request->only([
            'libelle_projet','commentaire','code_domaine','code_sous_domaine',
            'date_demarrage_prevue','date_fin_prevue','cout_projet','code_devise',
            'code_nature','code_pays'
        ])]);

        Log::info('[Step1] session saved', session('form_step1'));

        return response()->json(['success' => true]);
    }

    /*************************  STEP 2  *************************/
    // payload attendu: { projets: [...], localite: {...} }
    public function saveAppuiStep2(Request $request)
    {
        $request->validate([
            'projets'                        => 'required|array|min:1',
            'projets.*.code_projet'          => 'required|string',
            'projets.*.libelle'              => 'nullable|string',
            'projets.*.details'              => 'nullable|array',
            'localite'                       => 'nullable|array',
            'localite.id'                    => 'nullable|integer',
            'localite.code_rattachement'     => 'nullable|string',
            'localite.niveau'                => 'nullable|string',
            'localite.code_decoupage'        => 'nullable|string',
            'localite.libelle'               => 'nullable|string',
            'localite.libelle_decoupage'     => 'nullable|string',
        ]);

        session(['form_step2' => $request->only(['projets','localite'])]);
        if ($request->filled('localite.code_rattachement')) {
            session(['code_localisation' => $request->input('localite.code_rattachement')]);
        }

        Log::info('[Step2] session saved', session('form_step2'));

        return response()->json(['success' => true, 'message' => 'Étape 2 OK']);
    }
    public function saveAppuiStep3(Request $request)
    {
        $codeProjet     = $request->input('code_projet');
        $beneficiaires  = $request->input('beneficiaires', []);
    
        if (!$codeProjet) {
            return response()->json([
                'success' => false,
                'message' => 'Code projet manquant.'
            ], 400);
        }
    
        // reset ancien état
        Beneficier::where('code_projet', $codeProjet)->delete(); // acteurs
        Profiter::where('code_projet', $codeProjet)->delete();   // localités
        Jouir::where('code_projet', $codeProjet)->delete();      // infrastructures
    
        foreach ($beneficiaires as $b) {
            if (!isset($b['type'], $b['code'])) {
                continue;
            }
    
            switch ($b['type']) {
                case 'acteur':
                    Beneficier::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $b['code'],
                        'is_active'   => true,
                    ]);
                    break;
    
                case 'localite':
                    Profiter::create([
                        'code_projet'     => $codeProjet,
                        'code_pays'       => $b['pays'] ?? session('pays_selectionne'),
                        'code_rattachement'=> $b['code'],
                    ]);
                    break;
    
                case 'infrastructure':
                    Jouir::create([
                        'code_projet'        => $codeProjet,
                        'code_Infrastructure'=> $b['code'],
                    ]);
                    break;
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaires enregistrés avec succès.'
        ]);
    }
    

    /*************************  STEP 3  (MOA)  *************************/
    // payload: { type_ouvrage, priveMoeType, descriptionMoe, acteurs:[{code_acteur,is_assistant,secteur_code}] }
    public function saveAppuiStep4(Request $request)
    {
        $request->validate([
            'type_ouvrage'   => 'nullable|string|in:Public,Privé',
            'priveMoeType'   => 'nullable|string|in:Entreprise,Individu',
            'descriptionMoe' => 'nullable|string|max:2000',
            'acteurs'        => 'required|array|min:1',
            'acteurs.*.code_acteur'   => 'required|string|exists:acteur,code_acteur',
            'acteurs.*.is_assistant'  => 'boolean',
            'acteurs.*.secteur_code'  => 'nullable|string',
        ]);

        session(['form_step4' => $request->only([
            'type_ouvrage','priveMoeType','descriptionMoe','acteurs'
        ])]);

        Log::info('[Step3] session saved', session('form_step4'));

        return response()->json(['success' => true, 'message' => 'Étape 3 OK']);
    }

    /*************************  STEP 4  (MOE)  *************************/
    // payload: { acteurs:[{code_acteur, secteur_id}] }
    public function saveAppuiStep5(Request $request)
    {
        $request->validate([
            'acteurs'                 => 'required|array|min:1',
            'acteurs.*.code_acteur'   => 'required|string|exists:acteur,code_acteur',
            'acteurs.*.secteur_id'    => 'nullable|string',
        ]);

        session(['form_step5' => $request->only(['acteurs'])]);

        Log::info('[Step4] session saved', session('form_step5'));

        return response()->json(['success' => true, 'message' => 'Étape 4 OK']);
    }

    /*************************  STEP 5  (Financements)  *************************/
    // payload: { type_financement, financements:[{bailleur, montant, enChargeDe, devise, local, commentaire}] }
    public function saveAppuiStep6(Request $request)
    {
        $request->validate([
            'type_financement'           => 'required|string|exists:type_financement,code_type_financement',
            'financements'               => 'required|array|min:1',
            'financements.*.bailleur'    => 'required|string|exists:acteur,code_acteur',
            'financements.*.montant'     => 'required|numeric|min:0',
            'financements.*.devise'      => 'required|string|max:3',
            'financements.*.local'       => 'required|in:1,0,Oui,Non,oui,non,true,false',
            'financements.*.enChargeDe'  => 'nullable|string',
            'financements.*.commentaire' => 'nullable|string|max:500',
        ]);

        session(['form_step6' => $request->only(['type_financement','financements'])]);
        Log::info('[Step5] session saved', session('form_step6'));

        return response()->json(['success' => true, 'message' => 'Étape 5 OK']);
    }

    /*************************  STEP 6  (Upload docs)  *************************/
    public function saveAppuiStep7(Request $request)
    {
        $request->validate([
            'fichiers'   => 'required|array|min:1',
            'fichiers.*' => 'required|file|max:102400|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,dwg,dxf,ifc',
        ]);

        $uploaded = [];
        foreach ($request->file('fichiers', []) as $file) {
            if (!$file || !$file->isValid()) continue;

            $path = $file->store('temp/projet', 'local');
            $uploaded[] = [
                'original_name' => $file->getClientOriginalName(),
                'extension'     => $file->getClientOriginalExtension(),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'storage_path'  => $path,
            ];
        }

        session(['form_step7' => ['fichiers' => $uploaded]]);
        Log::info('[Step6] files staged', ['count' => count($uploaded)]);

        return response()->json(['success' => true, 'message' => 'Documents mis en file.']);
    }

    /*************************  FINALISATION  *************************/
    // Le front envoie: { code_projet_temp, type_financement } mais on relit toutes les sessions
    public function finaliser(Request $request)
    {
        return DB::transaction(function () {
            // 1) Récupération des steps (en session)
            $step1 = session('form_step1', []);
            $step2 = session('form_step2', []);
            $step4 = session('form_step4', []);
            $step5 = session('form_step5', []);
            $step6 = session('form_step6', []);
            $step7 = session('form_step7', []);
    
            // 2) Garde-fous (on exige au minimum 1,2,5)
            foreach (['form_step1' => $step1, 'form_step2' => $step2, 'form_step6' => $step6] as $k => $v) {
                if (empty($v)) {
                    throw new Exception("Données manquantes ($k).");
                }
            }
    
            // 3) Générer le code d’APPUI (PAS de localisation)
            $codeAppui = $this->buildAppuiCode(
                alpha3:          data_get($step1, 'code_pays'),
                groupe:          session('projet_selectionne'),
                typeFin:         data_get($step6, 'type_financement'),
                codeSousDomaine: data_get($step1, 'code_sous_domaine'),
                date:            Carbon::parse(data_get($step1, 'date_demarrage_prevue'))
            );
    
            // 4) Créer l’APPUI (mapping des champs step1 -> appui_projets)
            $appui = AppuiProjet::create([
                'code_projet_appui'          => $codeAppui,
                'groupe_projet_code'         => session('projet_selectionne'),
                'intitule'                   => data_get($step1, 'libelle_projet'),
                'description'                => data_get($step1, 'commentaire'),
                'code_pays'                  => data_get($step1, 'code_pays'),
                'code_domaine'               => data_get($step1, 'code_domaine'),
                'code_sous_domaine'          => data_get($step1, 'code_sous_domaine'),
                'date_debut_previsionnel'    => data_get($step1, 'date_demarrage_prevue'),
                'date_fin_previsionnel'      => data_get($step1, 'date_fin_prevue'),
                'montant_budget_previsionnel'=> data_get($step1, 'cout_projet'),
                'code_devise'=> data_get($step1, 'code_devise'),
            ]);
    
            // 5) Associer les projets choisis à l’étape 2 (table pivot projet_appui_projet)
            //    Le step2 que tu m’as montré contient un tableau "projets" -> on attache par code
            $projetsStep2 = (array) data_get($step2, 'projets', []);
            foreach ($projetsStep2 as $p) {
                $codeProjet = data_get($p, 'code_projet');
                if ($codeProjet) {
                    DB::table('projet_appui_projet')->updateOrInsert(
                        ['code_projet_appui' => $codeAppui, 'code_projet' => $codeProjet],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
            foreach (($step2['localites'] ?? []) as $idx => $loc) {
                Log::info('[Finaliser] Localisation', ['index' => $idx, 'loc' => $loc]);

                ProjetLocalisation::create([
                    'code_projet'  => $codeAppui,
                    'code_localite'=> $loc['code_rattachement'] ?? null,
                    'niveau'       => $loc['niveau'] ?? null,
                    'decoupage'    => $loc['code_decoupage'] ?? null,
                    'pays_code'    => $step1['code_pays'] ?? null,
                ]);
            }
    
            // 6) Acteurs MOA (Step 3) — on les rattache à l’APPUI
            //    Adapte les noms de tables si différents (ex: appui_posseder / posseder)
            foreach ((array) data_get($step4, 'acteurs', []) as $a) {
                DB::table('posseder')->insert([
                    'code_projet' => $codeAppui,
                    'code_acteur'       => $a['code_acteur'],
                    'secteur_id'        => $a['secteur_code'] ?? null,
                    'isAssistant'       => !empty($a['is_assistant']),
                    'is_active'         => true,
                    'date'              => now(),
                ]);
            }
    
            // 7) Acteurs MOE (Step 4) — on les rattache à l’APPUI
            foreach ((array) data_get($step5, 'acteurs', []) as $a) {
                DB::table('executer')->insert([
                    'code_projet' => $codeAppui,
                    'code_acteur'       => $a['code_acteur'],
                    'secteur_id'        => $a['secteur_id'] ?? null,
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
    
            // 8) Financements (Step 5) — si tu as une table dédiée à l’appui (recommandé)
            //    Exemple avec une table "appui_financer" (adapte si tu gardes "financer"):
            foreach ((array) data_get($step6, 'financements', []) as $fin) {
                DB::table('financer')->insert([
                    'code_projet'  => $codeAppui,
                    'code_acteur'        => $fin['bailleur'],
                    'montant_finance'    => $fin['montant'],
                    'devise'             => $fin['devise'],
                    'financement_local'  => in_array(strtolower((string)$fin['local']), ['1', 'oui', 'true'], true),
                    'commentaire'        => $fin['commentaire'] ?? null,
                    'FinancementType'    => data_get($step6, 'type_financement'),
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

    
            // 9) Documents (Step 6) — stockage local dans /public/Data/documentAppui
            foreach ((array) data_get($step7, 'fichiers', []) as $f) {
                $tmpPath = storage_path('app/' . $f['storage_path']);
                if (!is_file($tmpPath)) {
                    continue;
                }

                // Dossier de destination publique
                $destinationDir = public_path('Data/document/Appui');
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0775, true);
                }

                // Nom final
                $filename = time() . '_' . ($f['original_name'] ?? basename($tmpPath));
                $destinationPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                // Déplacement du fichier
                rename($tmpPath, $destinationPath);

                // Enregistrement dans la table des documents Appui
                DB::table('projet_documents')->insert([
                    'code_projet' => $codeAppui,
                    'file_name'         => $filename,
                    'file_path'         => 'Data/document/Appui/' . $filename, // chemin relatif public
                    'file_type'         => $f['mime_type'] ?? null,
                    'file_size'         => $f['size'] ?? filesize($destinationPath),
                    'file_category'     => 'DOC_APPUI',
                    'uploaded_at'       => now(),
                ]);
            }
            
            ProjetStatut::create([
                'code_projet' => $codeAppui,
                'type_statut' => 1,
                'date_statut' => now(),
            ]);
    
            // 10) (Optionnel) Workflow d’approbation – on passe le code APPUI
            try {
                $codeLocalisation = session('code_localisation');
                $snapshot = array_filter([
                    // --- Hints standard (clés que le normalizer comprend) ---
                    'owner_user_id'         => optional(auth()->user())->getKey(),
                    'owner_email'           => optional(auth()->user())->email,
                    'owner_acteur_code'     => optional(auth()->user())->acteur_id,
                    'demandeur_acteur_code' => data_get($step4, 'acteurs.0.code_acteur'),
    
                    // --- Champs métier utiles aux règles/aprobateurs dynamiques ---
                    'code_projet'           => $codeAppui,
                    'pays_code'             => session('pays_selectionne'),
                    'groupe_projet_id'      => session('projet_selectionne'),
                    'cout_projet'           => $step1['cout_projet'] ?? null,
                    'code_devise'           => $step1['code_devise'] ?? null,
                    'type_financement'      => $step6['type_financement'] ?? null,
                    'date_demarrage_prevue' => $step1['date_demarrage_prevue'] ?? null,
                    'date_fin_prevue'       => $step1['date_fin_prevue'] ?? null,
                    'localisation_code'     => $codeLocalisation ?? null,
                ], fn($v) => !is_null($v) && $v !== '');
                $res = $this->startApproval('APPUI', 'CREATION', (string) $codeAppui, $snapshot);
                Log::info('[Finaliser Appui] workflow', ['created' => (int)($res['created'] ?? 0)]);
            } catch (\Throwable $e) {
                Log::warning('[Finaliser Appui] workflow not started', ['err' => $e->getMessage()]);
            }
    
            // 11) Nettoyage sessions
            $this->resetWizardSessions();
    
            return response()->json([
                'success'            => true,
                'message'            => 'Projet d`\'appui crée avec succès.',
                'code_projet_appui'  => $codeAppui,
            ]);
        });
    }

    /**
     * Finalisation directe SANS validation (enregistrement immédiat)
     * Version sans workflow pour les projets d'appui qui ne nécessitent pas d'approbation
     */
    public function finaliserDirect(Request $request)
    {
        return DB::transaction(function () {
            $step1 = session('form_step1', []);
            $step2 = session('form_step2', []);
            $step4 = session('form_step4', []);
            $step5 = session('form_step5', []);
            $step6 = session('form_step6', []);
            $step7 = session('form_step7', []);
    
            // Garde-fous
            foreach (['form_step1' => $step1, 'form_step2' => $step2, 'form_step6' => $step6] as $k => $v) {
                if (empty($v)) {
                    throw new Exception("Données manquantes ($k).");
                }
            }
    
            // Générer le code d'APPUI
            $codeAppui = $this->buildAppuiCode(
                alpha3:          data_get($step1, 'code_pays'),
                groupe:          session('projet_selectionne'),
                typeFin:         data_get($step6, 'type_financement'),
                codeSousDomaine: data_get($step1, 'code_sous_domaine'),
                date:            Carbon::parse(data_get($step1, 'date_demarrage_prevue'))
            );
    
            // Créer l'APPUI (même logique)
            $appui = AppuiProjet::create([
                'code_projet_appui'          => $codeAppui,
                'groupe_projet_code'         => session('projet_selectionne'),
                'intitule'                   => data_get($step1, 'libelle_projet'),
                'description'                => data_get($step1, 'commentaire'),
                'code_pays'                  => data_get($step1, 'code_pays'),
                'code_domaine'               => data_get($step1, 'code_domaine'),
                'code_sous_domaine'          => data_get($step1, 'code_sous_domaine'),
                'date_debut_previsionnel'    => data_get($step1, 'date_demarrage_prevue'),
                'date_fin_previsionnel'      => data_get($step1, 'date_fin_prevue'),
                'montant_budget_previsionnel'=> data_get($step1, 'cout_projet'),
                'code_devise'=> data_get($step1, 'code_devise'),
            ]);
    
            // Associer les projets
            $projetsStep2 = (array) data_get($step2, 'projets', []);
            foreach ($projetsStep2 as $p) {
                $codeProjet = data_get($p, 'code_projet');
                if ($codeProjet) {
                    DB::table('projet_appui_projet')->updateOrInsert(
                        ['code_projet_appui' => $codeAppui, 'code_projet' => $codeProjet],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Localisations
            foreach (($step2['localites'] ?? []) as $loc) {
                ProjetLocalisation::create([
                    'code_projet'  => $codeAppui,
                    'code_localite'=> $loc['code_rattachement'] ?? null,
                    'niveau'       => $loc['niveau'] ?? null,
                    'decoupage'    => $loc['code_decoupage'] ?? null,
                    'pays_code'    => $step1['code_pays'] ?? null,
                ]);
            }
    
            // Acteurs MOA
            foreach ((array) data_get($step4, 'acteurs', []) as $a) {
                DB::table('posseder')->insert([
                    'code_projet' => $codeAppui,
                    'code_acteur'       => $a['code_acteur'],
                    'secteur_id'        => $a['secteur_code'] ?? null,
                    'isAssistant'       => !empty($a['is_assistant']),
                    'is_active'         => true,
                    'date'              => now(),
                ]);
            }
    
            // Acteurs MOE
            foreach ((array) data_get($step5, 'acteurs', []) as $a) {
                DB::table('executer')->insert([
                    'code_projet' => $codeAppui,
                    'code_acteur'       => $a['code_acteur'],
                    'secteur_id'        => $a['secteur_id'] ?? null,
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
    
            // Financements
            foreach ((array) data_get($step6, 'financements', []) as $fin) {
                DB::table('financer')->insert([
                    'code_projet'  => $codeAppui,
                    'code_acteur'        => $fin['bailleur'],
                    'montant_finance'    => $fin['montant'],
                    'devise'             => $fin['devise'],
                    'financement_local'  => in_array(strtolower((string)$fin['local']), ['1', 'oui', 'true'], true),
                    'commentaire'        => $fin['commentaire'] ?? null,
                    'FinancementType'    => data_get($step6, 'type_financement'),
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // Documents
            foreach ((array) data_get($step7, 'fichiers', []) as $f) {
                $tmpPath = storage_path('app/' . $f['storage_path']);
                if (!is_file($tmpPath)) continue;

                $destinationDir = public_path('Data/document/Appui');
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0775, true);
                }

                $filename = time() . '_' . ($f['original_name'] ?? basename($tmpPath));
                $destinationPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                rename($tmpPath, $destinationPath);

                DB::table('projet_documents')->insert([
                    'code_projet' => $codeAppui,
                    'file_name'         => $filename,
                    'file_path'         => 'Data/document/Appui/' . $filename,
                    'file_type'         => $f['mime_type'] ?? null,
                    'file_size'         => $f['size'] ?? filesize($destinationPath),
                    'file_category'     => 'DOC_APPUI',
                    'uploaded_at'       => now(),
                ]);
            }
            
            ProjetStatut::create([
                'code_projet' => $codeAppui,
                'type_statut' => 1,
                'date_statut' => now(),
            ]);
    
            // ⚠️ PAS DE WORKFLOW - Enregistrement direct
            Log::info('[Finaliser Direct Appui] Appui créé SANS validation', ['code_appui' => $codeAppui]);
    
            // Nettoyage sessions
            $this->resetWizardSessions();
    
            return response()->json([
                'success'            => true,
                'message'            => 'Projet d\'appui enregistré directement avec succès (sans validation).',
                'code_projet_appui'  => $codeAppui,
            ]);
        });
    }

    private function buildAppuiCode(
        string $alpha3,
        string $groupe,
        string $typeFin,
        string $codeSousDomaine,
        Carbon $date
    ): string {
        // AP_{ALPHA3}_{GROUPE}_{TYPEFIN}_{SDOM}_{YYYY}_{NN}
        $prefix = sprintf(
            'APPUI_%s_%s_%s_%s_%s',
            strtoupper($alpha3),
            strtoupper($groupe),
            strtoupper($typeFin),
            strtoupper($codeSousDomaine),
            $date->format('Y')
        );
    
        $ordre = AppuiProjet::where('code_projet_appui', 'like', $prefix.'_%')->count() + 1;
    
        return $prefix . '_' . str_pad((string)$ordre, 2, '0', STR_PAD_LEFT);
    }
    

    private function resetWizardSessions(): void
    {
        foreach (session('form_step7.fichiers', []) as $file) {
            $full = storage_path('app/'.$file['storage_path']);
            if (is_file($full)) @unlink($full);
        }
        session()->forget(['form_step1','form_step2','form_step4','form_step5','form_step6','form_step7','code_localisation']);
    }

    /*************************  RECHERCHE PROJETS + INFO  *************************/
    // Sous-domaines par domaine (pour l'autocomplete du Step 1)
    public function getSousDomaines($codeDomaine)
    {
        $groupe = session('projet_selectionne');
        $rows = SousDomaine::query()
            ->when($groupe, fn($q) => $q->where('code_groupe_projet', $groupe))
            ->where('code_domaine', $codeDomaine)
            ->orderBy('lib_sous_domaine')
            ->get(['code_sous_domaine','lib_sous_domaine']);
        return response()->json($rows);
    }
    public function searchProjets(Request $request)
    {
        $pays        = session('pays_selectionne');        // CIV
        $domaine     = $request->query('domaine');     // 03
        $sousDomaine = $request->query('SousDomaine'); // 0303
        $groupeCode  = session('projet_selectionne');  // TIC, BAT, etc.

        $q = Projet::query();

        // Pays
        if ($pays) {
            $q->where('code_alpha3_pays', $pays);
        }

        // Groupe (à partir du code_projet)
        if ($groupeCode) {
            // code_projet commence par "CIVTIC…" ⇒ on cherche "%CIVTIC%"
            // On sécurise aussi le pays dans le préfixe si présent.
            $prefix = $pays ? $pays . $groupeCode : $groupeCode;
            $q->where('code_projet', 'like', $prefix . '%');
        }

        // Domaine (si colonne existe) sinon fallback via LEFT(code_sous_domaine,2)
        if ($domaine) {
            if (Schema::hasColumn('projets', 'code_domaine')) {
                $q->where('code_domaine', $domaine);
            } else {
                $q->where(DB::raw('LEFT(code_sous_domaine,2)'), $domaine);
            }
        }

        // Sous-domaine
        if ($sousDomaine) {
            $q->where('code_sous_domaine', $sousDomaine);
        }

        $rows = $q->orderBy('date_demarrage_prevue', 'desc')
            ->get([
                'code_projet',
                'libelle_projet',
                // 'code_domaine', // si elle n’existe pas, commentez cette ligne
                'code_sous_domaine',
                'code_alpha3_pays',
                'date_demarrage_prevue',
                'date_fin_prevue',
                'cout_projet',
                'code_devise',
            ])
            ->map(function ($p) {
                // optionnel: enrichir de libellés
                $p->domaine_libelle = null;
                $p->sous_domaine_libelle = null;

                if (Schema::hasColumn('projets', 'code_domaine') && $p->code_domaine) {
                    $dom = Domaine::where('code', $p->code_domaine)->first();
                    $p->domaine_libelle = $dom?->libelle;
                } else {
                    // fallback: domaine = 2 premiers chiffres du sous-domaine
                    $domCode = substr((string)$p->code_sous_domaine, 0, 2);
                    $dom = Domaine::where('code', $domCode)->first();
                    $p->domaine_libelle = $dom?->libelle;
                }

                if ($p->code_sous_domaine) {
                    $sd = SousDomaine::where('code_sous_domaine', $p->code_sous_domaine)->first();
                    $p->sous_domaine_libelle = $sd?->lib_sous_domaine;
                }

                return $p;
            })
            ->values();

        return response()->json($rows);
    }

    public function getProjetInfo($codeProjet)
    {
        $p = Projet::where('code_projet', $codeProjet)->firstOrFail();

        // Domaine : direct ou via les 2 premiers digits du sous-domaine
        $domaine = null;
        if (Schema::hasColumn('projets', 'code_domaine') && $p->code_domaine) {
            $domaine = $p->code_domaine;
        } else {
            $domaine = substr((string)$p->code_sous_domaine, 0, 2);
        }

        return response()->json([
            'code_projet'            => $p->code_projet,
            'libelle_projet'         => $p->libelle_projet,
            'pays'                   => $p->code_alpha3_pays,
            'domaine'                => $domaine,
            'sous_domaine'           => $p->code_sous_domaine,
            'date_demarrage_prevue'  => optional($p->date_demarrage_prevue)->format('Y-m-d'),
            'date_fin_prevue'        => optional($p->date_fin_prevue)->format('Y-m-d'),
            'cout_projet'            => $p->cout_projet,
            'code_devise'            => $p->code_devise,
        ]);
    }

    public function getBeneficiairesProjet($codeProjet)
    {
        $sql = "
            SELECT 'acteur' AS type, b.code_acteur AS code, 
                   COALESCE(a.libelle_long, a.libelle_court, '') AS libelle, 
                   a.code_pays AS code_pays, 
                   NULL AS extra
            FROM beneficier b
            LEFT JOIN acteur a ON a.code_acteur = b.code_acteur
            WHERE b.code_projet = ?
    
            UNION ALL
    
            SELECT 'localite' AS type, p.code_rattachement AS code, 
                   lp.libelle AS libelle, 
                   p.code_pays AS code_pays, 
                   lp.libelle AS extra
            FROM profiter p
            LEFT JOIN localites_pays lp 
                ON lp.code_rattachement = p.code_rattachement
                AND lp.id_pays = p.code_pays
            WHERE p.code_projet = ?
    
            UNION ALL
    
            SELECT 'infrastructure' AS type, j.code_infrastructure AS code, 
                   i.libelle AS libelle, 
                   i.code_pays AS code_pays, 
                   i.code_localite AS extra
            FROM jouir j
            LEFT JOIN infrastructures i ON i.code = j.code_infrastructure
            WHERE j.code_projet = ?
        ";
    
        $beneficiaires = DB::select($sql, [$codeProjet, $codeProjet, $codeProjet]);
    
        return response()->json([
            'beneficiaires' => $beneficiaires
        ]);
    }
    
}
