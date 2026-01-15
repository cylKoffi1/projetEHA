<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\Devise;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\EtudeLivrable;
use App\Models\EtudeLivrableAttendu;
use App\Models\EtudeProjet;
use App\Models\EtudeType;
use App\Models\GroupeProjet;
use App\Models\GroupeProjetPaysUser;
use App\Models\GroupeUtilisateur;
use App\Models\Livrable;
use App\Models\NatureTravaux;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\SecteurActivite;
use App\Models\SousDomaine;
use App\Models\TypeFinancement;
use App\Models\UniteDerivee;
use App\Support\ApprovesWithWorkflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProjetEtudeController extends Controller
{
    use ApprovesWithWorkflow; // pour startApproval()

    /*************************  VUE PRINCIPALE (STEP WIZARD)  *************************/
    public function createNaissanceEtude(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));

        // code projet "défaut" (visuel) — 100% facultatif
        $generatedCodeProjet = $this->generateSimpleVisualCode();

        $paysSelectionne    = session('pays_selectionne');   // ex: alpha3
        $groupeSelectionne  = session('projet_selectionne'); // ex: code groupe

        $user   = auth()->user();
        $groupe = GroupeUtilisateur::where('code', $user->groupe_utilisateur_id)->first();

        // Référentiels (limités aux colonnes utiles)
        $NaturesTravaux   = NatureTravaux::orderBy('libelle')->first();
        $GroupeProjets    = GroupeProjet::orderBy('libelle')->get(['code','libelle']);
        $Domaines         = Domaine::where('groupe_projet_code', $groupeSelectionne)->orderBy('libelle')->get(['code','libelle']);
        $SousDomaines     = SousDomaine::orderBy('lib_sous_domaine')->get(['code_sous_domaine','lib_sous_domaine']);
        $SecteurActivites = SecteurActivite::orderBy('libelle')->get(['id','code','libelle']);
        $EtudeTypes =       EtudeType::orderBy('libelle')->get(['code','libelle']);
        $Livrables = EtudeLivrable::orderBy('libelle')->get(['id','code','libelle']);


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
        
        return view('projet_etude.naissance', compact('EtudeTypes','Livrables',
            'ecran', 'typeFinancements','generatedCodeProjet','NaturesTravaux','GroupeProjets','Domaines','SousDomaines',
            'SecteurActivites','Pays','deviseCouts','groupeSelectionne','Devises','unitesDerivees','acteurs', 'directMode'
        ));
    }


    private function generateSimpleVisualCode(): string
    {
        return 'TMP-'.Str::upper(Str::random(6));
    }

    /*************************  STEP 1  *************************/
    public function saveEtudeStep1(Request $request)
    {
        $request->validate([
            'libelle_projet'            => 'required|string|max:255',
            'code_domaine'              => 'required|string|max:10',
            'code_sous_domaine'         => 'required|string|max:10',
            'date_demarrage_prevue'     => 'required|date',
            'date_fin_prevue'           => 'required|date|after_or_equal:date_demarrage_prevue',
            'cout_projet'               => 'required|numeric|min:0',
            'code_devise'               => 'required|string|max:3',
            'code_nature'               => 'required|string|max:10',
            'code_pays'                 => 'required|string|max:3',
            'commentaire'               => 'nullable|string|max:2000',

            // Ajouts propres à l’étude
            'type_etude_code'           => 'required|string|max:20',       // réf. etude_types.code_type
            'livrables_attendus'        => 'nullable|array',
            'livrables_attendus.*'      => 'integer|exists:etude_livrables,id',
            'livrables_commentaires'    => 'nullable|string|max:2000',
        ]);

        session([
            'form_step1' => $request->only([
                'libelle_projet','commentaire',
                'code_domaine','code_sous_domaine',
                'date_demarrage_prevue','date_fin_prevue',
                'cout_projet','code_devise',
                'code_nature','code_pays',
                // spécifiques étude :
                'type_etude_code','livrables_attendus','livrables_commentaires',
            ]),
        ]);

        Log::info('[Etude Step1] OK', session('form_step1'));

        return response()->json(['success' => true, 'message' => 'Étape 1 enregistrée.']);
    }

    /**************************************************************************
     * STEP 2 : MOA (Maître d’Ouvrage) — acteurs
     * payload: { type_ouvrage, priveMoeType, descriptionMoe, acteurs:[{code_acteur,is_assistant,secteur_code}] }
     **************************************************************************/
    public function saveEtudeStep2(Request $request)
    {
        // on valide "is_assistant" avec un jeu de valeurs souple puis on normalise
        $request->validate([
            'type_ouvrage'                 => 'nullable|string|in:Public,Privé',
            'priveMoeType'                 => 'nullable|string|in:Entreprise,Individu',
            'descriptionMoe'               => 'nullable|string|max:2000',
            'acteurs'                      => 'required|array|min:1',
            'acteurs.*.code_acteur'        => 'required|string|exists:acteur,code_acteur',
            'acteurs.*.is_assistant'       => 'nullable|in:1,0,true,false,oui,non,on,off',
            'acteurs.*.secteur_code'       => 'nullable|string',
        ]);

        // Normalisation booleans
        $acteurs = collect($request->input('acteurs', []))->map(function ($a) {
            $raw = Arr::get($a, 'is_assistant', false);
            $bool = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $a['is_assistant'] = (bool) ($bool ?? in_array(strtolower((string)$raw), ['1','oui','on'], true));
            return $a;
        })->values()->all();

        session([
            'form_step2' => [
                'type_ouvrage'   => $request->input('type_ouvrage'),
                'priveMoeType'   => $request->input('priveMoeType'),
                'descriptionMoe' => $request->input('descriptionMoe'),
                'acteurs'        => $acteurs,
            ]
        ]);

        Log::info('[Etude Step2] OK', session('form_step2'));

        return response()->json(['success' => true, 'message' => 'Étape 2 enregistrée.']);
    }

    /**************************************************************************
     * STEP 3 : MOE (Maître d’œuvre) — acteurs
     * payload: { acteurs:[{code_acteur, secteur_id}] }
     **************************************************************************/
    public function saveEtudeStep3(Request $request)
    {
        $request->validate([
            'acteurs'                      => 'required|array|min:1',
            'acteurs.*.code_acteur'        => 'required|string|exists:acteur,code_acteur',
            'acteurs.*.secteur_id'         => 'nullable|string',
        ]);

        session(['form_step3' => $request->only('acteurs')]);

        Log::info('[Etude Step3] OK', session('form_step3'));

        return response()->json(['success' => true, 'message' => 'Étape 3 enregistrée.']);
    }

    /**************************************************************************
     * STEP 4 : Financements
     * payload: { type_financement, financements:[{bailleur, montant, devise, local, enChargeDe, commentaire}] }
     **************************************************************************/
    public function saveEtudeStep4(Request $request)
    {
        $request->validate([
            'type_financement'              => 'required|string|exists:type_financement,code_type_financement',
            'financements'                  => 'required|array|min:1',
            'financements.*.bailleur'       => 'required|string|exists:acteur,code_acteur',
            'financements.*.montant'        => 'required|numeric|min:0',
            'financements.*.devise'         => 'required|string|max:3',
            'financements.*.local'          => 'required|in:1,0,Oui,Non,oui,non,true,false,on,off',
            'financements.*.enChargeDe'     => 'nullable|string',
            'financements.*.commentaire'    => 'nullable|string|max:500',
        ]);

        // Normalise "local" en bool
        $financements = collect($request->input('financements', []))->map(function ($f) {
            $raw = Arr::get($f, 'local', 0);
            $bool = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $f['local'] = (bool) ($bool ?? in_array(strtolower((string)$raw), ['1','oui','on','true'], true));
            return $f;
        })->values()->all();

        session([
            'form_step4' => [
                'type_financement' => $request->input('type_financement'),
                'financements'     => $financements
            ]
        ]);

        Log::info('[Etude Step4] OK', session('form_step4'));

        return response()->json(['success' => true, 'message' => 'Étape 4 enregistrée.']);
    }

    /**************************************************************************
     * STEP 5 : Upload documents (staging en storage)
     **************************************************************************/
    public function saveEtudeStep5(Request $request)
    {
        $request->validate([
            'fichiers'   => 'required|array|min:1',
            'fichiers.*' => 'required|file|max:102400|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,dwg,dxf,ifc',
        ]);

        $uploaded = [];
        foreach ($request->file('fichiers', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            // staging en storage/app/temp/etude
            $path = $file->store('temp/etude', 'local');
            $uploaded[] = [
                'original_name' => $file->getClientOriginalName(),
                'extension'     => $file->getClientOriginalExtension(),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'storage_path'  => $path,
            ];
        }

        session(['form_step5' => ['fichiers' => $uploaded]]);

        Log::info('[Etude Step5] files staged', ['count' => count($uploaded)]);

        return response()->json(['success' => true, 'message' => 'Documents mis en file.']);
    }

    /**************************************************************************
     * FINALISATION : création étude + pivots + docs publics + workflow (optionnel)
     **************************************************************************/
    public function finaliser(Request $request)
    {
        return DB::transaction(function () {
            // Récup steps
            $step1 = session('form_step1', []);
            $step2 = session('form_step2', []);
            $step3 = session('form_step3', []);
            $step4 = session('form_step4', []);
            $step5 = session('form_step5', []); // docs

            // Garde-fous minimaux
            foreach (['form_step1' => $step1, 'form_step2' => $step2, 'form_step4' => $step4] as $k => $v) {
                if (empty($v)) {
                    throw new Exception("Données manquantes ($k).");
                }
            }

            // Code d’étude
            $codeEtude = $this->buildEtudeCode(
                alpha3: data_get($step1, 'code_pays'),
                groupe: session('projet_selectionne'), // ex: TIC/BAT…
                typeFin: data_get($step4, 'type_financement'),
                date: Carbon::parse(data_get($step1, 'date_demarrage_prevue'))
            );

            // Création de l’étude
            $etude = EtudeProjet::create([
                'code_projet_etude'            => $codeEtude,
                'groupe_projet_code'           => session('projet_selectionne'),
                'intitule'                     => data_get($step1, 'libelle_projet'),
                'description'                  => data_get($step1, 'commentaire'),
                'code_pays'                    => data_get($step1, 'code_pays'),
                'code_domaine'                 => data_get($step1, 'code_domaine'),
                'code_sous_domaine'            => data_get($step1, 'code_sous_domaine'),
                'date_debut_previsionnel'      => data_get($step1, 'date_demarrage_prevue'),
                'date_fin_previsionnel'        => data_get($step1, 'date_fin_prevue'),
                'montant_budget_previsionnel'  => data_get($step1, 'cout_projet'),
                'code_devise'                  => data_get($step1, 'code_devise'),
                'objectif_general'             => null,
                'type_etude_code'              => data_get($step1, 'type_etude_code'),
                'livrables_commentaires'       => data_get($step1, 'livrables_commentaires'),
            ]);

            // Pivot livrables attendus
            $livrablesIds = (array) data_get($step1, 'livrables_attendus', []);
            if (!empty($livrablesIds)) {
                // ⚠️ si tu as une relation belongsToMany définie sur le modèle :
                // $etude->livrables()->sync($livrablesIds);
                // Sinon, insertion brute :
                $rows = collect($livrablesIds)->unique()->map(fn($id) => [
                    'code_projet_etude' => $codeEtude,
                    'livrable_id'       => (int) $id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ])->values()->all();

                DB::table('etude_projet_livrables')->insert($rows);
            }

            // MOA (Step 2) — ⚠️ adapter le nom de la table pivot si besoin
            foreach ((array) data_get($step2, 'acteurs', []) as $a) {
                DB::table('posseder')->insert([
                    'code_projet'  => $codeEtude,            // colonne “code_projet” utilisée pour code étude
                    'code_acteur'  => $a['code_acteur'],
                    'secteur_id'   => $a['secteur_code'] ?? null,
                    'isAssistant'  => !empty($a['is_assistant']),
                    'is_active'    => true,
                    'date'         => now(),
                ]);
            }

            // MOE (Step 3) — ⚠️ adapter le nom de la table pivot si besoin
            foreach ((array) data_get($step3, 'acteurs', []) as $a) {
                DB::table('executer')->insert([
                    'code_projet'  => $codeEtude,
                    'code_acteur'  => $a['code_acteur'],
                    'secteur_id'   => $a['secteur_id'] ?? null,
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // Financements (Step 4) — ⚠️ si tu as une table dédiée aux études, remplace “financer”
            foreach ((array) data_get($step4, 'financements', []) as $fin) {
                DB::table('financer')->insert([
                    'code_projet'       => $codeEtude,
                    'code_acteur'       => $fin['bailleur'],
                    'montant_finance'   => $fin['montant'],
                    'devise'            => $fin['devise'],
                    'financement_local' => (bool) $fin['local'],
                    'commentaire'       => $fin['commentaire'] ?? null,
                    'FinancementType'   => data_get($step4, 'type_financement'),
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

          // Documents (Step 5) → stockage public dans /public/Data/document/EtudeProjet
            foreach ((array) data_get($step5, 'fichiers', []) as $f) {
                $tmpPath = storage_path('app/' . $f['storage_path']);
                if (!is_file($tmpPath)) {
                    continue;
                }

                // Nouveau dossier cible
                $destinationDir = public_path('Data/document/EtudeProjet');
                if (!is_dir($destinationDir)) {
                    @mkdir($destinationDir, 0775, true);
                }

                // Génération du nom de fichier final
                $filename = time() . '_' . Str::slug(pathinfo($f['original_name'] ?? basename($tmpPath), PATHINFO_FILENAME));
                $ext      = $f['extension'] ?? pathinfo($tmpPath, PATHINFO_EXTENSION);
                $final    = $filename . ($ext ? '.' . $ext : '');
                $destPath = $destinationDir . DIRECTORY_SEPARATOR . $final;

                // Déplacement du fichier temporaire vers le dossier public
                @rename($tmpPath, $destPath);

                // Insertion en base
                DB::table('projet_documents')->insert([
                    'code_projet'   => $codeEtude,
                    'file_name'     => $final,
                    'file_path'     => 'Data/document/EtudeProjet/' . $final,  // chemin relatif public
                    'file_type'     => $f['mime_type'] ?? null,
                    'file_size'     => $f['size'] ?? @filesize($destPath),
                    'file_category' => 'DOC_ETUDE',
                    'uploaded_at'   => now(),
                ]);
            }

            ProjetStatut::create([
                'code_projet' => $codeEtude,
                'type_statut' => 1,
                'date_statut' => now(),
            ]);

            // (Optionnel) Workflow
            try {
                $snapshot = array_filter([
                    'owner_user_id'         => optional(auth()->user())->getKey(),
                    'owner_email'           => optional(auth()->user())->email,
                    'code_projet'           => $codeEtude,
                    'pays_code'             => data_get($step1, 'code_pays'),
                    'groupe_projet_id'      => session('projet_selectionne'),
                    'cout_projet'           => data_get($step1, 'cout_projet'),
                    'code_devise'           => data_get($step1, 'code_devise'),
                    'type_financement'      => data_get($step4, 'type_financement'),
                    'date_demarrage_prevue' => data_get($step1, 'date_demarrage_prevue'),
                    'date_fin_prevue'       => data_get($step1, 'date_fin_prevue'),
                    'type_etude_code'       => data_get($step1, 'type_etude_code'),
                ], fn($v) => !is_null($v) && $v !== '');

                // $this->startApproval('ETUDE', 'CREATION', (string)$codeEtude, $snapshot);
                $this->startApproval('ETUDE', 'CREATION', (string)$codeEtude, $snapshot);
            } catch (\Throwable $e) {
                Log::warning('[Finaliser Etude] workflow not started', ['err' => $e->getMessage()]);
            }

            // Nettoyage session + temporaires (déjà déplacés)
            $this->resetWizardSessions();

            return response()->json([
                'success'           => true,
                'message'           => 'Projet étude crée avec succès.',
                'code_projet_etude' => $codeEtude,
            ]);
        });
    }

    /**
     * Finalisation directe SANS validation (enregistrement immédiat)
     * Version sans workflow pour les études qui ne nécessitent pas d'approbation
     */
    public function finaliserDirect(Request $request)
    {
        return DB::transaction(function () {
            $step1 = session('form_step1', []);
            $step2 = session('form_step2', []);
            $step3 = session('form_step3', []);
            $step4 = session('form_step4', []);
            $step5 = session('form_step5', []);

            // Garde-fous minimaux
            foreach (['form_step1' => $step1, 'form_step2' => $step2, 'form_step4' => $step4] as $k => $v) {
                if (empty($v)) {
                    throw new Exception("Données manquantes ($k).");
                }
            }

            // Code d'étude
            $codeEtude = $this->buildEtudeCode(
                alpha3: data_get($step1, 'code_pays'),
                groupe: session('projet_selectionne'),
                typeFin: data_get($step4, 'type_financement'),
                date: Carbon::parse(data_get($step1, 'date_demarrage_prevue'))
            );

            // Création de l'étude (même logique)
            $etude = EtudeProjet::create([
                'code_projet_etude'            => $codeEtude,
                'groupe_projet_code'           => session('projet_selectionne'),
                'intitule'                     => data_get($step1, 'libelle_projet'),
                'description'                  => data_get($step1, 'commentaire'),
                'code_pays'                    => data_get($step1, 'code_pays'),
                'code_domaine'                 => data_get($step1, 'code_domaine'),
                'code_sous_domaine'            => data_get($step1, 'code_sous_domaine'),
                'date_debut_previsionnel'      => data_get($step1, 'date_demarrage_prevue'),
                'date_fin_previsionnel'        => data_get($step1, 'date_fin_prevue'),
                'montant_budget_previsionnel'  => data_get($step1, 'cout_projet'),
                'code_devise'                  => data_get($step1, 'code_devise'),
                'objectif_general'             => null,
                'type_etude_code'              => data_get($step1, 'type_etude_code'),
                'livrables_commentaires'       => data_get($step1, 'livrables_commentaires'),
            ]);

            // Pivot livrables
            $livrablesIds = (array) data_get($step1, 'livrables_attendus', []);
            if (!empty($livrablesIds)) {
                $rows = collect($livrablesIds)->unique()->map(fn($id) => [
                    'code_projet_etude' => $codeEtude,
                    'livrable_id'       => (int) $id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ])->values()->all();
                DB::table('etude_projet_livrables')->insert($rows);
            }

            // MOA
            foreach ((array) data_get($step2, 'acteurs', []) as $a) {
                DB::table('posseder')->insert([
                    'code_projet'  => $codeEtude,
                    'code_acteur'  => $a['code_acteur'],
                    'secteur_id'   => $a['secteur_code'] ?? null,
                    'isAssistant'  => !empty($a['is_assistant']),
                    'is_active'    => true,
                    'date'         => now(),
                ]);
            }

            // MOE
            foreach ((array) data_get($step3, 'acteurs', []) as $a) {
                DB::table('executer')->insert([
                    'code_projet'  => $codeEtude,
                    'code_acteur'  => $a['code_acteur'],
                    'secteur_id'   => $a['secteur_id'] ?? null,
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // Financements
            foreach ((array) data_get($step4, 'financements', []) as $fin) {
                DB::table('financer')->insert([
                    'code_projet'       => $codeEtude,
                    'code_acteur'       => $fin['bailleur'],
                    'montant_finance'   => $fin['montant'],
                    'devise'            => $fin['devise'],
                    'financement_local' => (bool) $fin['local'],
                    'commentaire'       => $fin['commentaire'] ?? null,
                    'FinancementType'   => data_get($step4, 'type_financement'),
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            // Documents
            foreach ((array) data_get($step5, 'fichiers', []) as $f) {
                $tmpPath = storage_path('app/' . $f['storage_path']);
                if (!is_file($tmpPath)) continue;

                $destinationDir = public_path('Data/document/EtudeProjet');
                if (!is_dir($destinationDir)) {
                    @mkdir($destinationDir, 0775, true);
                }

                $filename = time() . '_' . Str::slug(pathinfo($f['original_name'] ?? basename($tmpPath), PATHINFO_FILENAME));
                $ext      = $f['extension'] ?? pathinfo($tmpPath, PATHINFO_EXTENSION);
                $final    = $filename . ($ext ? '.' . $ext : '');
                $destPath = $destinationDir . DIRECTORY_SEPARATOR . $final;

                @rename($tmpPath, $destPath);

                DB::table('projet_documents')->insert([
                    'code_projet'   => $codeEtude,
                    'file_name'     => $final,
                    'file_path'     => 'Data/document/EtudeProjet/' . $final,
                    'file_type'     => $f['mime_type'] ?? null,
                    'file_size'     => $f['size'] ?? @filesize($destPath),
                    'file_category' => 'DOC_ETUDE',
                    'uploaded_at'   => now(),
                ]);
            }

            ProjetStatut::create([
                'code_projet' => $codeEtude,
                'type_statut' => 1,
                'date_statut' => now(),
            ]);

            // ⚠️ PAS DE WORKFLOW - Enregistrement direct
            Log::info('[Finaliser Direct Etude] Étude créée SANS validation', ['code_etude' => $codeEtude]);

            // Nettoyage session
            $this->resetWizardSessions();

            return response()->json([
                'success'           => true,
                'message'           => 'Projet étude enregistré directement avec succès (sans validation).',
                'code_projet_etude' => $codeEtude,
            ]);
        });
    }

    /**************************************************************************
     * Helpers
     **************************************************************************/
    private function buildEtudeCode(string $alpha3, string $groupe, string $typeFin, Carbon $date): string
    {
        // ET_{ALPHA3}_{GROUPE}_{TYPEFIN}_{YYYY}_{NN}
        $prefix = sprintf('ET_%s_%s_%s_%s',
            strtoupper($alpha3),
            strtoupper($groupe),
            strtoupper($typeFin),
            $date->format('Y')
        );

        $ordre = EtudeProjet::where('code_projet_etude', 'like', $prefix.'_%')->count() + 1;

        return $prefix . '_' . str_pad((string)$ordre, 2, '0', STR_PAD_LEFT);
    }

    private function resetWizardSessions(): void
    {
        // supprimer les fichiers restants en staging si le move a échoué (par prudence)
        foreach (session('form_step5.fichiers', []) as $file) {
            $full = storage_path('app/'.$file['storage_path']);
            if (is_file($full)) @unlink($full);
        }
        session()->forget([
            'form_step1','form_step2','form_step3','form_step4','form_step5'
        ]);
    }

    /*************************  RECHERCHE PROJETS + INFO  *************************/
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

}
