<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\Controler;
use App\Models\Ecran;
use App\Models\Executer;
use App\Models\InstanceApprobation;
use App\Models\StatutInstance;
use App\Models\MotifStatutProjet;
use App\Models\Projet;
use App\Models\EtudeProjet;
use App\Models\AppuiProjet;
use App\Models\ProjetStatut;
use App\Models\SecteurActivite;
use App\Models\StatutProjet;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;   
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProjetController extends Controller
{
    

    
        /*** ======== CONSTANTES WORKFLOW ======== ***/
        private const WF_TYPE = 'CREATION'; // selon ta table modules_workflow_disponibles
    
        /** Vérifie qu’une instance approuvée existe pour (module, code). */
        private function approvedInstanceExistsFor(string $moduleCode, string $idCible): bool
        {
            $approvedId = StatutInstance::where('code', 'APPROUVE')->value('id');
    
            return InstanceApprobation::query()
                ->where('module_code', $moduleCode)
                ->where('type_cible',  self::WF_TYPE)
                ->where('id_cible',    $idCible)
                ->where('statut_id',   $approvedId)
                ->exists();
        }
    
        /**
         * Ajoute un whereExists workflow approuvé pour une source donnée.
         *
         * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $q
         * @param string $moduleCode  PROJET|ETUDE|APPUI
         * @param string $qualifiedCodeColumn  ex: 'projets.code_projet' / 'etude_projets.code_projet_etude'
         */
        private function whereWorkflowApproved($q, string $moduleCode, string $qualifiedCodeColumn): void
        {
            $approvedId = StatutInstance::where('code', 'APPROUVE')->value('id');
    
            $q->whereExists(function ($sub) use ($approvedId, $moduleCode, $qualifiedCodeColumn) {
                $sub->from('instances_approbation as ia')
                    ->whereRaw("ia.id_cible = {$qualifiedCodeColumn}")
                    ->where('ia.module_code', $moduleCode)
                    ->where('ia.type_cible',  self::WF_TYPE)
                    ->where('ia.statut_id',   $approvedId);
            });
        }
    
        /**************************************
         *          VUE “Chef de projet”
         **************************************/
        public function projet(Request $request)
        {
            $ecran   = Ecran::find($request->input('ecran_id'));
            $country = (string) session('pays_selectionne');
            // Chefs
            $chefs = Acteur::where('type_acteur','etp')->where('code_pays',$country)->get();
            
            if ($request->filled('type_projet')) {
                $code = strtoupper($request->input('type_projet'));
                abort_if(\Gate::denies('projettype.select', $code), 403, "Vous n'êtes pas autorisé à sélectionner le type $code.");
            }
            // Pas de liste projets ici : chargée dynamiquement par type
            $contrats = Controler::with('acteur')->where('is_active', true)->get();
    
            return view('projets.DefinitionProjet.projet', compact('ecran','chefs','contrats'));
        }
    
        // --- ENDPOINT AJAX : liste des projets par TYPE ---
        public function optionsProjets(Request $request)
        {
            $type = strtoupper((string) $request->query('type', ''));
            
            if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
                return response()->json([], 200);
            }
    
            $country = (string) session('pays_selectionne');   // ex: CIV
            $group   = (string) session('projet_selectionne'); // ex: BAT
            if ($country === '' || $group === '') {
                return response()->json([], 200);
            }
               
            // Préfixes par famille (ajuste si besoin)
            $prefixProjet = $country . $group . '%';
            $prefixEtude  = 'ET_'    . $country . '_' . $group . '%';
            $prefixAppui  = 'APPUI_' . $country . '_' . $group . '%';
    
            // id du statut "APPROUVE" dans ref statut instances
            $approvedId = (int) (StatutInstance::where('code','APPROUVE')->value('id') ?? 0);
           
            if ($type === 'PROJET') {
                $rows = Projet::query()
                    ->from('projets as p')
                    ->selectRaw('p.code_projet as code, p.libelle_projet as label')
                    ->join('projet_statut as ps','ps.code_projet','=','p.code_projet')
                    ->where('p.code_alpha3_pays', $country)
                    ->where('p.code_projet','like',$prefixProjet)
                    ->whereNotIn('p.code_projet', function($q){
                        $q->select('code_projet')->from('controler')->where('is_active',1);
                    })
                    ->where('ps.type_statut', 1)
                    ->whereExists(function($q) use ($approvedId){
                        $q->from('instances_approbation as ia')
                          ->whereColumn('ia.id_cible','p.code_projet')
                          ->where('ia.module_code','PROJET')
                          ->where('ia.type_cible','CREATION')
                          ->where('ia.statut_id', $approvedId);
                    })
                    ->orderBy('p.code_projet')
                    ->get();
    
                return response()->json($rows);
            }
    
            if ($type === 'ETUDE') {
                $rows = EtudeProjet::query()
                    ->from('etude_projets as ep')
                    ->selectRaw('ep.code_projet_etude as code, ep.intitule as label')
                    ->join('projet_statut as ps','ps.code_projet','=','ep.code_projet_etude')
                    ->where('ep.code_pays', $country)
                    ->where('ep.code_projet_etude','like',$prefixEtude)
                    ->whereNotIn('ep.code_projet_etude', function($q){
                        $q->select('code_projet')->from('controler')->where('is_active',1);
                    })
                    ->where('ps.type_statut', 1)
                    ->whereExists(function($q) use ($approvedId){
                        $q->from('instances_approbation as ia')
                        ->whereColumn('ia.id_cible','ep.code_projet_etude')
                        ->where('ia.module_code','ETUDE')
                        ->where('ia.type_cible', self::WF_TYPE) // ← si tu harmonises
                        ->where('ia.statut_id', $approvedId);
                    })
                    ->orderBy('ep.code_projet_etude')
                    ->get();

    
                return response()->json($rows);
            }
    
            // APPUI
            $rows = AppuiProjet::query()
                ->from('appui_projets as ap')
                ->selectRaw('ap.code_projet_appui as code, ap.intitule as label')
                ->join('projet_statut as ps','ps.code_projet','=','ap.code_projet_appui')
                ->where('ap.code_pays', $country)
                ->where('ap.code_projet_appui','like',$prefixAppui)
                ->whereNotIn('ap.code_projet_appui', function($q){
                    $q->select('code_projet')->from('controler')->where('is_active',1);
                })
                ->where('ps.type_statut', 1)
                ->whereExists(function($q) use ($approvedId){
                    $q->from('instances_approbation as ia')
                      ->whereColumn('ia.id_cible','ap.code_projet_appui')
                      ->where('ia.module_code','APPUI')
                      ->where('ia.type_cible','CREATION')
                      ->where('ia.statut_id', $approvedId);
                })
                ->orderBy('ap.code_projet_appui')
                ->get();
    
            return response()->json($rows);
        }
    
        /**************************************
         *       CRÉATION / MÀJ CONTRAT
         **************************************/
        public function store(Request $request)
        {
            try {
                $validated = $this->validateContrat($request);
        
                return DB::transaction(function () use ($validated, $request) {
                    $contrat = Controler::create([
                        'code_projet' => $validated['projet_id'],
                        'code_acteur' => $validated['chef_projet_id'],
                        'date_debut'  => $validated['date_debut'],
                        'date_fin'    => $validated['date_fin'],
                        'is_active'   => true,
                    ]);
        
                    Log::info('Contrat créé', ['user_id' => auth()->id(), 'contrat_id' => $contrat->id]);
        
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => 'Contrat enregistré avec succès.',
                            'data'    => $contrat,
                        ], 201);
                    }
                    return back()->with('success', 'Contrat enregistré avec succès.');
                });
            } catch (ValidationException $e) {
                // ⬇️ renvoyer un 422 propre pour ton fetch()
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Des erreurs ont été détectées.",
                        'errors'  => $e->errors(),
                    ], 422);
                }
                return back()->withErrors($e->errors())->withInput();
            } catch (\Throwable $e) {
                Log::error('Erreur création contrat', [
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                    'user_id' => auth()->id(),
                ]);
        
                if ($request->expectsJson()) {
                    return response()->json([
                        'error'  => "Erreur lors de l'enregistrement du contrat.",
                        'detail' => $e->getMessage()
                    ], 500);
                }
                return back()->with('error', "Erreur lors de l'enregistrement du contrat : " . $e->getMessage());
            }
        }
        
    
        public function update(Request $request, string $id)
        {
            try {
                $validated = $this->validateContrat($request, $id);
    
                return DB::transaction(function () use ($validated, $id, $request) {
                    $contrat = controler::findOrFail($id);
    
                    $contrat->update([
                        'code_projet' => $validated['projet_id'],
                        'code_acteur' => $validated['chef_projet_id'],
                        'date_debut'  => $validated['date_debut'],
                        'date_fin'    => $validated['date_fin'],
                    ]);
    
                    Log::info('Contrat mis à jour', ['user_id' => auth()->id(), 'contrat_id' => $contrat->id]);
    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => 'Contrat mis à jour avec succès.',
                            'data'    => $contrat,
                        ]);
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Erreur update contrat', [
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                    'user_id' => auth()->id(),
                ]);
    
                if ($request->expectsJson()) {
                    return response()->json([
                        'error'  => "Erreur lors de la mise à jour du contrat.",
                        'detail' => $e->getMessage()
                    ], 500);
                }
            }
        }
    
        /**************************************
         *     PDF + FICHE CONTRAT (show)
         **************************************/
        public function pdf($id)
        {
            // On charge TOUTES les relations possibles en une fois
            $contrat = \App\Models\Controler::with([
                'acteur',
                // PROJET (infrastructure)
                'projet.maitreOuvrage.acteur',
                'projet.localisations.localite.decoupage',
                // ETUDE
                'projetEtude', // ajoute ici d’autres relations si tu en as (maitreOuvrage, localisations…)
                // APPUI
                'projetAppui', // idem
            ])->find($id);
        
            if (!$contrat) {
                return response()->json(['error' => 'Aucune donnée disponible pour générer cette fiche.']);
            }
        
            // Déterminer dynamiquement le "type" et l’objet associé
            [$type, $objet] = $this->resolveObjetContrat($contrat);
            if (!$objet) {
                return response()->json(['success' => 'Impossible de déterminer le type de projet (projet/étude/appui) pour ce contrat.']);
            }
        
            // Passer à la vue un “view-model” unifié
            $vm = $this->buildPdfViewModel($contrat, $type, $objet);
        
            $pdf = Pdf::loadView('contracts.fiche_chef_projet', [
                'contrat' => $contrat,
                'type'    => $type,  // 'PROJET' | 'ETUDE' | 'APPUI'
                'objet'   => $objet, // instance Projet | EtudeProjet | AppuiProjet
                'vm'      => $vm,    // tableau prêt à afficher
            ]);
        
            $prefix = match($type) {
                'ETUDE' => 'ETUDE',
                'APPUI' => 'APPUI',
                default => 'PROJET',
            };
        
            return $pdf->download(
                Str::of("fiche_contrat_{$prefix}_{$contrat->id}.pdf")->replace(' ', '_')
            );
        }
        
        public function fiche($id)
        {
            $contrat = \App\Models\Controler::with([
                'acteur',
                'projet.maitreOuvrage.acteur',
                'projet.localisations.localite.decoupage',
                'projetEtude',
                'projetAppui',
            ])->findOrFail($id);
        
            [$type, $objet] = $this->resolveObjetContrat($contrat);
            $vm = $this->buildPdfViewModel($contrat, $type, $objet);
        
            return view('contracts.fiche_chef_projet', [
                'contrat' => $contrat,
                'type'    => $type,
                'objet'   => $objet,
                'vm'      => $vm,
            ]);
        }
        
        /**
         * Retourne ['PROJET'|'ETUDE'|'APPUI', $objetEloquent]
         */
        private function resolveObjetContrat(\App\Models\Controler $contrat): array
        {
            if ($contrat->projet)      return ['PROJET', $contrat->projet];
            if ($contrat->projetEtude) return ['ETUDE',  $contrat->projetEtude];
            if ($contrat->projetAppui) return ['APPUI',  $contrat->projetAppui];
            return [null, null];
        }
        
        /**
         * Construit un “view-model” commun pour la vue PDF.
         * - code
         * - intitule
         * - client_label (si dispo)
         * - localisations[] (si dispo)
         */
        private function buildPdfViewModel(\App\Models\Controler $contrat, string $type, $objet): array
        {
            // Code & Intitulé selon la famille
            $code = match($type) {
                'ETUDE' => $objet->code_projet_etude,
                'APPUI' => $objet->code_projet_appui,
                default => $objet->code_projet,
            };
        
            $intitule = $objet->libelle_projet
                ?? $objet->intitule
                ?? '';
        
            // Client (maître d’ouvrage) — disponible nativement côté Projet
            $client = '';
            if ($type === 'PROJET' && $contrat->projet?->maitreOuvrage?->acteur) {
                $a = $contrat->projet->maitreOuvrage->acteur;
                $client = trim(($a->libelle_court ?? '').' '.($a->libelle_long ?? ''));
            }
        
            // Localisations — disponible nativement côté Projet
            $localisations = [];
            if ($type === 'PROJET' && $contrat->projet?->localisations) {
                foreach ($contrat->projet->localisations as $loc) {
                    $localisations[] = [
                        'libelle'   => $loc->localite?->libelle,
                        'decoupage' => $loc->localite?->decoupage?->libelle_decoupage,
                    ];
                }
            }
        
            return [
                'code'          => $code,
                'intitule'      => $intitule,
                'client_label'  => $client,
                'localisations' => $localisations,
            ];
        }
    
        /**************************************
         *     VALIDATION MÉTIER COMMUNE
         **************************************/
        
        
        private function validateContrat(Request $request, ?string $updateId = null): array
        {
            $validator = Validator::make(
                $request->all(),
                [
                    'projet_id'      => ['required','string'],
                    // ⚠️ Vérifie le vrai nom de ta table: 'acteur' ou 'acteurs'
                    'chef_projet_id' => ['required','string', Rule::exists('acteur','code_acteur')],
                    'date_debut'     => ['required','date','before_or_equal:date_fin'],
                    'date_fin'       => ['required','date','after_or_equal:date_debut'],
                ]
            );
        
            $validator->after(function ($validator) use ($request, $updateId) {
                $code = (string) $request->input('projet_id');
        
                $family = $this->resolveFamily($code); // PROJET|ETUDE|APPUI|null
                if (!$family) {
                    $validator->errors()->add('projet_id', "Code projet introuvable dans projets/etude_projets/appui_projets.");
                    return;
                }
        
                if (!$this->hasTypeStatutOne($family, $code)) {
                    $validator->errors()->add('projet_id', "Le projet n’a pas type_statut=1 dans la famille {$family}.");
                }
        
                if (!$this->approvedInstanceExistsFor($family, $code)) {
                    $validator->errors()->add('projet_id', "Le projet n’est pas validé par le workflow ({$family}).");
                }
        
                $start = Carbon::parse($request->input('date_debut'));
                $end   = Carbon::parse($request->input('date_fin'));
        
                if ($end->lt(today())) {
                    $validator->errors()->add('date_fin', "La date de fin ne peut pas être antérieure à aujourd'hui.");
                }
                if ($start->diffInMonths($end) < 1) {
                    $validator->errors()->add('date_fin', "La durée d’un contrat ne peut pas être inférieure à 1 mois.");
                }
                if ($start->equalTo($end)) {
                    $validator->errors()->add('date_fin', "La période doit couvrir au moins une journée.");
                }
        
                $overlapQ = Controler::query()
                    ->where('code_acteur', $request->input('chef_projet_id'))
                    ->when(Schema::hasColumn((new Controler)->getTable(), 'is_active'),
                        fn($q) => $q->where('is_active', true))
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('date_debut', [$start, $end])
                          ->orWhereBetween('date_fin',   [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->where('date_debut', '<=', $start)
                                 ->where('date_fin',   '>=', $end);
                          });
                    });
                if ($updateId) $overlapQ->where('id', '<>', $updateId);
                if ($overlapQ->exists()) {
                    $validator->errors()->add('date_debut', "Ce chef de projet a déjà un contrat actif qui chevauche ces dates.");
                    $validator->errors()->add('date_fin',   "Ce chef de projet a déjà un contrat actif qui chevauche ces dates.");
                }
        
                $minStart = $this->familyPlannedStart($family, $code); // Carbon|null
                if ($minStart && $start->lt($minStart)) {
                    $validator->errors()->add('date_debut',
                        "La date de début du contrat doit être ≥ à la date de début prévue ({$minStart->toDateString()})."
                    );
                }
            });
        
            // ⬇️ Laisse Laravel lever une ValidationException si erreurs
            return $validator->validate();
        }
        
    
        /** Renvoie PROJET|ETUDE|APPUI|null selon où se trouve le code. */
        private function resolveFamily(string $code): ?string
        {
            if (Projet::where('code_projet', $code)->exists()) return 'PROJET';
            if (EtudeProjet::where('code_projet_etude', $code)->exists()) return 'ETUDE';
            if (AppuiProjet::where('code_projet_appui', $code)->exists()) return 'APPUI';
            return null;
        }
    
        /** Vérifie type_statut = 1 dans la table statut correspondant à la famille. */
        private function hasTypeStatutOne(string $family, string $code): bool
        {
            return DB::table('projet_statut')
                ->where('code_projet', $code)
                ->where('type_statut', 1)
                ->exists();
        }

    
        /** Date de début prévue (normalisée) selon la famille. */
        private function familyPlannedStart(string $family, string $code): ?Carbon
        {
            return match ($family) {
                'PROJET' => optional(Projet::where('code_projet', $code)->first())->date_demarrage_prevue
                    ? Carbon::parse(Projet::where('code_projet', $code)->value('date_demarrage_prevue'))
                    : null,
                'ETUDE'  => optional(EtudeProjet::where('code_projet_etude', $code)->first())->date_debut_previsionnel
                    ? Carbon::parse(EtudeProjet::where('code_projet_etude', $code)->value('date_debut_previsionnel'))
                    : null,
                'APPUI'  => optional(AppuiProjet::where('code_projet_appui', $code)->first())->date_debut_previsionnel
                    ? Carbon::parse(AppuiProjet::where('code_projet_appui', $code)->value('date_debut_previsionnel'))
                    : null,
                default  => null,
            };
        }
    
    
    

    /*************************REATTRIBUTION DE CHEF DE PROJET */
    public function changerChef(Request $request)
    {
        $ecran   = Ecran::find($request->input('ecran_id'));
        $country = (string) session('pays_selectionne');
    
        $chefs = Acteur::where('type_acteur', 'etp')
            ->where('code_pays', $country)
            ->get();

        if ($request->filled('type_projet')) {
            $code = strtoupper($request->input('type_projet'));
            abort_if(\Gate::denies('projettype.select', $code), 403, "Vous n'êtes pas autorisé à sélectionner le type $code.");
        }
    
        // On n’envoie PAS la liste des contrats ici : elle est chargée via AJAX selon le type
        return view('projets.DefinitionProjet.changementChefProjet', compact('ecran','chefs'));
    }
    
    /**
     * POST: Applique le changement de chef.
     * - désactive l’ancien contrat
     * - crée un contrat actif pour le nouveau chef (date_debut=aujourd’hui, date_fin = ancienne fin)
     * - validations: existe, actif, pas le même chef, pas d’overlap pour le nouveau, bornes de dates
    */
    public function changerChefUpdate(Request $request)
    {
        try {
            $data = $request->validate([
                'contrat_id'      => ['required','exists:controler,id'],
                'nouveau_chef_id' => ['required', Rule::exists('acteur','code_acteur')],
                'motif'           => ['required','string','max:1000'],
            ]);
    
            return DB::transaction(function() use ($data) {
                /** @var Controler $old */
                $old = Controler::with('acteur')->lockForUpdate()->findOrFail($data['contrat_id']);
    
                if (!$old->is_active) {
                    return response()->json(['error' => 'Le contrat sélectionné est déjà inactif.']);
                }
                if ($old->code_acteur === $data['nouveau_chef_id']) {
                    return response()->json(['error' => 'Le nouveau chef est identique à l’actuel.']);
                }
    
                $today = Carbon::today();
                $end   = Carbon::parse($old->date_fin);
    
                if ($end->lt($today)) {
                    return response()->json(['error' => "Impossible: la date de fin du contrat existant est passée ({$end->toDateString()})."]);
                }
    
                // Vérifier overlap pour le nouveau chef (un seul actif à la fois)
                $overlap = Controler::query()
                    ->where('code_acteur', $data['nouveau_chef_id'])
                    ->where('is_active', true)
                    ->where(function($q) use ($today, $end) {
                        $q->whereBetween('date_debut', [$today, $end])
                          ->orWhereBetween('date_fin',   [$today, $end])
                          ->orWhere(function($q2) use ($today, $end) {
                              $q2->where('date_debut','<=',$today)->where('date_fin','>=',$end);
                          });
                    })
                    ->exists();
    
                if ($overlap) {
                    return response()->json(['error' => "Chevauchement détecté: le nouveau chef a déjà un contrat actif sur la même période."]);
                }
    
                // Désactiver l’ancien
                $old->update([
                    'is_active' => false,
                    'motif'     => $data['motif'],
                ]);
    
                // Créer le nouveau
                $new = Controler::create([
                    'code_projet' => $old->code_projet,
                    'code_acteur' => $data['nouveau_chef_id'],
                    'date_debut'  => $today->toDateString(),
                    'date_fin'    => $end->toDateString(),
                    'is_active'   => true,
                    'motif'       => $data['motif'],
                ]);
    
                Log::info("Changement de chef sur code {$old->code_projet} : {$old->code_acteur} -> {$data['nouveau_chef_id']} (contrat #{$old->id} -> #{$new->id})");
                
                return response()->json(['success' => "Le chef de projet a été changé avec succès."]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Erreur changement chef', ['error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['error' => "Erreur lors du changement de chef: .$e->getMessage()"]);
        }
    }

    /**
     * AJAX: retourne les contrats actifs éligibles au changement selon le TYPE.
     * Répond: [{id, code_projet, chef_label, date_debut, date_fin}]
    */
    public function optionsContrats(Request $request)
    {
        $type = strtoupper((string) $request->query('type', ''));
        if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
            return response()->json([], 200);
        }

        $country = (string) session('pays_selectionne');   // ex: CIV
        $group   = (string) session('projet_selectionne'); // ex: BAT
        if ($country === '' || $group === '') {
            return response()->json([], 200);
        }

        $prefixProjet = $country . $group . '%';
        $prefixEtude  = 'ET_'    . $country . '_' . $group . '%';
        $prefixAppui  = 'APPUI_' . $country . '_' . $group . '%';
        $approvedId   = (int) (StatutInstance::where('code','APPROUVE')->value('id') ?? 0);

        $q = Controler::query()
            ->with('acteur')
            ->where('controler.is_active', true);

        // Joins pour relier à la bonne famille
        if ($type === 'PROJET') {
            $q->leftJoin('projets as p','p.code_projet','=','controler.code_projet')
            ->whereNotNull('p.code_projet')
            ->where('p.code_alpha3_pays',$country)
            ->where('p.code_projet','like',$prefixProjet)
            ->whereExists(function($qq){
                $qq->from('projet_statut as ps')
                    ->whereColumn('ps.code_projet','controler.code_projet')
                    ->where('ps.type_statut',1);
            })
            ->whereExists(function($qq) use ($approvedId){
                $qq->from('instances_approbation as ia')
                    ->where('ia.module_code','PROJET')
                    ->where('ia.type_cible', self::WF_TYPE)
                    ->whereColumn('ia.id_cible','controler.code_projet')
                    ->where('ia.statut_id',$approvedId);
            });
        } elseif ($type === 'ETUDE') {
            $q->leftJoin('etude_projets as ep','ep.code_projet_etude','=','controler.code_projet')
            ->whereNotNull('ep.code_projet_etude')
            ->where('ep.code_pays',$country)
            ->where('ep.code_projet_etude','like',$prefixEtude)
            ->whereExists(function($qq){
                $qq->from('projet_statut as ps')
                    ->whereColumn('ps.code_projet','controler.code_projet')
                    ->where('ps.type_statut',1);
            })
            ->whereExists(function($qq) use ($approvedId){
                $qq->from('instances_approbation as ia')
                    ->where('ia.module_code','ETUDE')
                    ->where('ia.type_cible', self::WF_TYPE)
                    ->whereColumn('ia.id_cible','controler.code_projet')
                    ->where('ia.statut_id',$approvedId);
            });
        } else { // APPUI
            $q->leftJoin('appui_projets as ap','ap.code_projet_appui','=','controler.code_projet')
            ->whereNotNull('ap.code_projet_appui')
            ->where('ap.code_pays',$country)
            ->where('ap.code_projet_appui','like',$prefixAppui)
            ->whereExists(function($qq){
                $qq->from('projet_statut as ps')
                    ->whereColumn('ps.code_projet','controler.code_projet')
                    ->where('ps.type_statut',1);
            })
            ->whereExists(function($qq) use ($approvedId){
                $qq->from('instances_approbation as ia')
                    ->where('ia.module_code','APPUI')
                    ->where('ia.type_cible', self::WF_TYPE)
                    ->whereColumn('ia.id_cible','controler.code_projet')
                    ->where('ia.statut_id',$approvedId);
            });
        }

        $rows = $q->select('controler.*')->orderBy('controler.code_projet')->get()
            ->map(function(Controler $c){
                return [
                    'id'          => $c->id,
                    'code_projet' => $c->code_projet,
                    'chef_label'  => trim(($c->acteur->libelle_court ?? '').' '.($c->acteur->libelle_long ?? '')),
                    'date_debut'  => (string) $c->date_debut,
                    'date_fin'    => (string) $c->date_fin,
                ];
            });

        return response()->json($rows, 200);
    }

    /************************* ÉCRAN : RÉATTRIBUTION MAÎTRE D’ŒUVRE *************************/
    public function reatributionProjet(Request $request)
    {
         // augmente le timeout à 300s juste pour cette requête
        ini_set('max_execution_time', 300); // 300 secondes = 5 minutes
        set_time_limit(300);
        $pays  = (string) session('pays_selectionne');
        $group = (string) session('projet_selectionne');
        $ecran = Ecran::find($request->input('ecran_id'));

        // Liste initiale (projets classiques) uniquement pour la 1ère charge — ensuite on recharge via AJAX
        $projets = Projet::query()
            ->where('code_alpha3_pays', $pays)
            ->where('code_projet', 'like', $pays.$group.'%')
            ->select('code_projet')
            ->orderBy('code_projet')
            ->get();

        $acteurs = Acteur::where('type_acteur','etp')
            ->where('code_pays',$pays)
            ->orderBy('libelle_long')
            ->get();

        $executions = Executer::with('acteur','secteurActivite')
            ->where('is_active', true)
            ->where('code_projet', 'like', $pays.$group.'%')
            ->orderBy('code_projet')
            ->get();
        
        if ($request->filled('projet_type_code')) {
            $code = strtoupper($request->input('projet_type_code'));
            abort_if(\Gate::denies('projettype.select', $code), 403, "Vous n'êtes pas autorisé à sélectionner le type $code.");
        }

        $SecteurActivites = SecteurActivite::orderBy('libelle')->get();

        return view('projets.GestionExceptions.reattributionProjet', compact(
            'ecran', 'projets', 'acteurs', 'executions', 'SecteurActivites'
        ));
    }

    public function reattOptionsProjets(Request $request)
    {
        $type    = strtoupper((string) $request->query('type', 'PROJET'));
        $country = (string) session('pays_selectionne');
        $group   = (string) session('projet_selectionne');

        if ($country === '' || $group === '' || !in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
            return response()->json([], 200);
        }

        $prefixProjet = $country.$group.'%';
        $prefixEtude  = 'ET_'.$country.'_'.$group.'%';
        $prefixAppui  = 'APPUI_'.$country.'_'.$group.'%';

        if ($type === 'ETUDE') {
            $rows = EtudeProjet::query()
                ->from('etude_projets as ep')
                ->selectRaw('ep.code_projet_etude as code, ep.intitule as label')
                ->where('ep.code_pays', $country)
                ->where('ep.code_projet_etude', 'like', $prefixEtude)
                ->orderBy('ep.code_projet_etude')
                ->get();

            return response()->json($rows);
        }

        if ($type === 'APPUI') {
            $rows = AppuiProjet::query()
                ->from('appui_projets as ap')
                ->selectRaw('ap.code_projet_appui as code, ap.intitule as label')
                ->where('ap.code_pays', $country)
                ->where('ap.code_projet_appui', 'like', $prefixAppui)
                ->orderBy('ap.code_projet_appui')
                ->get();

            return response()->json($rows);
        }

        // PROJET (par défaut)
        $rows = Projet::query()
            ->from('projets as p')
            ->selectRaw('p.code_projet as code, p.libelle_projet as label')
            ->where('p.code_alpha3_pays', $country)
            ->where('p.code_projet', 'like', $prefixProjet)
            ->orderBy('p.code_projet')
            ->get();

        return response()->json($rows);
    }
    
    private function familyFromCode(string $code): string
    {
        if (str_starts_with($code, 'ET_'))     return 'ETUDE';
        if (str_starts_with($code, 'APPUI_'))  return 'APPUI';
        return 'PROJET';
    }
    
    public function getProjetCard(string $code)
    {
        $famille = $this->familyFromCode($code);

        if ($famille === 'PROJET') {
            $p = Projet::with([
                    'statuts.statut',
                    'sousDomaine.Domaine',
                    'maitresOeuvre.acteur',
                    'maitreOuvrage.acteur',
                    'localisations.localite',
                ])
                ->where('code_projet', $code)
                ->first();

            if (!$p) return response()->json(null);

            return response()->json([
                'code_projet'              => $p->code_projet,
                'libelle_projet'           => $p->libelle_projet,
                'nature'                   => $p->statuts?->statut?->libelle,
                'domaine'                  => $p->sousDomaine?->Domaine?->libelle,
                'sousDomaine'              => $p->sousDomaine?->lib_sous_domaine,
                'cout'                     => $p->cout_projet,
                'devise'                   => $p->code_devise,
                'maitreOeuvre'             => $p->maitresOeuvre->map(fn($e) => $e->acteur->libelle_long ?? null)->filter()->values(),
                'maitreOuvrage'            => $p->maitreOuvrage?->acteur?->libelle_long,
                'localite'                 => $p->localisations->map(fn($l) => $l->localite?->libelle)->filter()->values(),
                'date_demarrage_prevue'    => $p->date_demarrage_prevue,
                'date_fin_prevue'          => $p->date_fin_prevue,
            ]);
        }

        if ($famille === 'ETUDE') {
            // champs courants supposés sur etude_projets : adapte si tes colonnes diffèrent
            $e = EtudeProjet::query()
                ->with([
                    'statuts.statut',
                    'sousDomaine.Domaine',
                    'maitresOeuvre.acteur',
                    'maitreOuvrage.acteur',
                    ]) // ajoute des relations si tu en as (ex: sousDomaine, maitreOuvrage…)
                ->where('code_projet_etude', $code)
                ->first();

            if (!$e) return response()->json(null);

            return response()->json([
                'code_projet'              => $e->code_projet_etude,
                'libelle_projet'           => $e->intitule,                 // (équivalent libellé)
                'nature'                   => $e->statuts?->statut?->libelle,                         // mets une relation si tu en as
                'domaine'                  => $e->sousDomaine?->Domaine?->libelle,
                'sousDomaine'              => $e->sousDomaine?->lib_sous_domaine,
                'cout'                     => $e->montant_budget_previsionnel ?? null,             // adapte au vrai champ si présent
                'devise'                   => $e->code_devise ?? null,      // idem
                'maitreOeuvre'             => $e->maitresOeuvre->map(fn($e) => $e->acteur->libelle_long ?? null)->filter()->values(),                           // à remplir si relation présente
                'maitreOuvrage'            => $e->maitreOuvrage?->acteur?->libelle_long,                         // idem
                'localite'                 => [],                            // idem
                'date_demarrage_prevue'    => $e->date_debut_previsionnel ?? null,
                'date_fin_prevue'          => $e->date_fin_previsionnel ?? null,
            ]);
        }

        // APPUI
        $a = AppuiProjet::query()
            ->with([
                'statuts.statut',
                'sousDomaine.Domaine',
                'maitresOeuvre.acteur',
                'maitreOuvrage.acteur',
                'localisations.localite',
            ]) // ajoute des relations si disponibles
            ->where('code_projet_appui', $code)
            ->first();

        if (!$a) return response()->json(null);

        return response()->json([
            'code_projet'              => $a->code_projet_appui,
            'libelle_projet'           => $a->intitule,
            'nature'                   => $a->statuts?->statut?->libelle,
            'domaine'                  => $a->sousDomaine?->Domaine?->libelle,
            'sousDomaine'              => $a->sousDomaine?->lib_sous_domaine,
            'cout'                     => $a->montant_budget_previsionnel ?? null,             // adapte
            'devise'                   => $a->code_devise ?? null,      // adapte
            'maitreOeuvre'             => $a->maitresOeuvre->map(fn($e) => $e->acteur->libelle_long ?? null)->filter()->values(),
            'maitreOuvrage'            => $a->maitreOuvrage?->acteur?->libelle_long,
            'localite'                 => $a->localisations->map(fn($l) => $l->localite?->libelle)->filter()->values(),
            'date_demarrage_prevue'    => $a->date_debut_previsionnel ?? null,
            'date_fin_prevue'          => $a->date_fin_previsionnel ?? null,
        ]);
    }

        /**
     * AJAX – options projets par TYPE (PROJET|ETUDE|APPUI),
     * filtrés par pays/groupe, statut=1, workflow approuvé.
     * Réponse: [{code, label}]
     */
    public function optionsProjetsMOE(Request $request)
    {
        $type = strtoupper((string) $request->query('type', ''));
        if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
            return response()->json([], 200);
        }

        $country = (string) session('pays_selectionne');   // ex: CIV
        $group   = (string) session('projet_selectionne'); // ex: BAT
        if ($country === '' || $group === '') {
            return response()->json([], 200);
        }

        // Préfixes
        $prefixProjet = $country . $group . '%';
        $prefixEtude  = 'ET_'    . $country . '_' . $group . '%';
        $prefixAppui  = 'APPUI_' . $country . '_' . $group . '%';

        // id statut APPROUVE
        $approvedId = (int) (StatutInstance::where('code','APPROUVE')->value('id') ?? 0);

        if ($type === 'PROJET') {
            $rows = Projet::query()
                ->from('projets as p')
                ->selectRaw('p.code_projet as code, p.libelle_projet as label')
                ->join('projet_statut as ps','ps.code_projet','=','p.code_projet')
                ->where('p.code_alpha3_pays', $country)
                ->where('p.code_projet','like',$prefixProjet)
              
                ->orderBy('p.code_projet')
                ->get();

            return response()->json($rows, 200);
        }

        if ($type === 'ETUDE') {
            $rows = EtudeProjet::query()
                ->from('etude_projets as ep')
                ->selectRaw('ep.code_projet_etude as code, ep.intitule as label')
                ->join('projet_statut as ps','ps.code_projet','=','ep.code_projet_etude')
                ->where('ep.code_pays', $country)
                ->where('ep.code_projet_etude','like',$prefixEtude)
                
                ->orderBy('ep.code_projet_etude')
                ->get();

            return response()->json($rows, 200);
        }

        // APPUI
        $rows = AppuiProjet::query()
            ->from('appui_projets as ap')
            ->selectRaw('ap.code_projet_appui as code, ap.intitule as label')
            ->join('projet_statut as ps','ps.code_projet','=','ap.code_projet_appui')
            ->where('ap.code_pays', $country)
            ->where('ap.code_projet_appui','like',$prefixAppui)
            
            ->orderBy('ap.code_projet_appui')
            ->get();

        return response()->json($rows, 200);
    }
    

        /**
     * Exécution active (MOE) d’un projet donné.
     */
    public function getExecutionByProjet($code_projet)
    {
        $execution = Executer::with('acteur','secteurActivite')
            ->where('code_projet', $code_projet)
            ->where('is_active', true)
            ->first();

        if (!$execution) {
            return response()->json(null);
        }

        return response()->json([
            'id'              => $execution->id,
            'code_projet'     => $execution->code_projet,
            'code_acteur'     => $execution->code_acteur,
            'acteur_nom'      => trim(($execution->acteur->libelle_court ?? '').' '.($execution->acteur->libelle_long ?? '')),
            'acteur_type'     => $execution->acteur->type_acteur,  // 'eta','clt','etp', etc.
            'secteur_id'      => $execution->secteur_id,
            'secteur_libelle' => $execution->secteurActivite->libelle ?? null,
            'motif'           => $execution->motif,
        ]);
    }
    public function storeReatt(Request $request)
    {
        try {
            $validated = $request->validate([
                'projet_id' => ['required','string'],
                'acteur_id' => ['required','string', Rule::exists('acteur','code_acteur')],
                'secteur_id'=> ['nullable','string'],
                'motif'     => ['required','string','max:255'],
            ]);

            $execution = Executer::create([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['acteur_id'],
                'secteur_id'  => $validated['secteur_id'] ?: null,
                'motif'       => $validated['motif'],
                'is_active'   => true,
            ]);

            Log::info('Maître d’œuvre affecté', ['user_id'=>auth()->id(), 'data'=>$execution]);

            return response()->json(['success' => "Maître d’œuvre attribué avec succès."]);
        } catch (ValidationException $e) {
            return response()->json(['message'=>"Des erreurs ont été détectées.", 'errors'=>$e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Erreur attribution MOE', ['user_id'=>auth()->id(),'message'=>$e->getMessage()]);
            return response()->json(['error' => "Erreur lors de l'attribution du maître d’œuvre."], 500);
        }
    }

    /*************** UPDATE ***************/
    public function updateReatt(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'projet_id' => ['required','string'],
                'acteur_id' => ['required','string', Rule::exists('acteur','code_acteur')],
                'secteur_id'=> ['nullable','string'],
                'motif'     => ['required','string','max:255'],
            ]);

            $execution = Executer::findOrFail($id);

            $execution->update([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['acteur_id'],
                'secteur_id'  => $validated['secteur_id'] ?: null,
                'motif'       => $validated['motif'],
            ]);

            Log::info('Maître d’œuvre modifié', ['user_id'=>auth()->id(),'data'=>$execution]);

            return response()->json(['success' => "Maître d’œuvre mis à jour."]);
        } catch (ValidationException $e) {
            return response()->json(['message'=>"Des erreurs ont été détectées.", 'errors'=>$e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Erreur modification MOE', ['user_id'=>auth()->id(),'message'=>$e->getMessage()]);
            return response()->json(['error' => "Erreur lors de la mise à jour du maître d’œuvre."], 500);
        }
    }

    /*************** DELETE ***************/
    public function destroyReatt($id)
    {
        try {
            $execution = Executer::findOrFail($id);
            $execution->delete();

            Log::info('Maître d’œuvre supprimé', ['user_id'=>auth()->id(),'id'=>$id]);

            return response()->json(['success' => "Maître d’œuvre supprimé avec succès."]);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression MOE', ['user_id'=>auth()->id(),'message'=>$e->getMessage()]);
            return response()->json(['error' => "Erreur lors de la suppression."], 500);
        }
    }







    public function destroy($id)
    {
        try {
            $contrat = controler::findOrFail($id);
            $contrat->delete();

            Log::info('Contrat supprimé', ['user_id' => auth()->id(), 'contrat_id' => $id]);

            return response()->json(['success' => 'Contrat supprimé avec succès.']);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression contrat', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de la suppression du contrat.'], 500);
        }
    }




    /*
        public function update(Request $request, $id)
        {
            $data = $request->validate([
                'chef_projet_id' => 'required|exists:acteur,code_acteur',
                'projet_id' => 'required|exists:projets,code_projet',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            try {
                $contrat = Controler::findOrFail($id);
                $contrat->update([
                    'code_projet' => $data['projet_id'],
                    'code_acteur' => $data['chef_projet_id'],
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                ]);

                return redirect()->route('projet', ['ecran_id' => $ecran->id])->with('success', 'Contrat modifié avec succès.');

            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour du contrat: ' . $e->getMessage());
                return back()->with('error', 'Une erreur est survenue lors de la mise à jour du contrat.');
            }
        }

        public function destroy($id, Request $request)
        {
            $ecran = Ecran::find($request->input('ecran_id'));
            $contrat = controler::findOrFail($id);
            $contrat->delete();

            return redirect()->route('projet', ['ecran_id' => $ecran->id])->with('success', 'Contrat supprimé.');
        }
    */


    // === ANNULATION : form + options + store (3 familles) ===

    public function formAnnulation(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));

        // Liste des projets annulés (toutes familles)
        $pays  = (string) session('pays_selectionne');
        $group = (string) session('projet_selectionne');

        $prefixProjet = $pays.$group.'%';
        $prefixEtude  = 'ET_'.$pays.'_'.$group.'%';
        $prefixAppui  = 'APPUI_'.$pays.'_'.$group.'%';

        // On prend les dernières lignes statut = 4 (Annulé)
        $annules = ProjetStatut::query()
            ->where('type_statut', 4)
            ->where(function($q) use ($prefixProjet,$prefixEtude,$prefixAppui){
                $q->where('code_projet','like',$prefixProjet)
                ->orWhere('code_projet','like',$prefixEtude)
                ->orWhere('code_projet','like',$prefixAppui);
            })
            ->orderByDesc('date_statut')
            ->get()
            ->map(function(ProjetStatut $ps){
                // Récupérer libellé selon famille
                $code = $ps->code_projet;
                $lib = Projet::where('code_projet',$code)->value('libelle_projet')
                ?? EtudeProjet::where('code_projet_etude',$code)->value('intitule')
                ?? AppuiProjet::where('code_projet_appui',$code)->value('intitule')
                ?? '';
                return (object)[
                    'code_projet'    => $code,
                    'libelle_projet' => $lib,
                    'date_statut'    => $ps->date_statut,
                    'statut_libelle' => 'Annulé',
                ];
            });

        // On ne précharge pas la liste des projets : chargée via AJAX par type
        $projets = collect(); // pour compat vue si besoin

        return view('projets.GestionExceptions.annulationProjet', [
            'ecran'           => $ecran,
            'projets'         => $projets,
            'projetsAnnules'  => $annules,
        ]);
    }
    

    /**
     * Options projets éligibles à l’annulation par TYPE (PROJET|ETUDE|APPUI)
     * Critère d’éligibilité: dernier statut ∈ [1,2,5,6] (comme ton implémentation précédente).
     */
    public function annulationOptionsProjets(Request $request)
    {
        $type    = strtoupper((string) $request->query('type', 'PROJET'));
        if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) return response()->json([],200);
    
        $country = (string) session('pays_selectionne');
        $group   = (string) session('projet_selectionne');
        if ($country === '' || $group === '') return response()->json([],200);
    
        $prefixProjet = $country.$group.'%';
        $prefixEtude  = 'ET_'.$country.'_'.$group.'%';
        $prefixAppui  = 'APPUI_'.$country.'_'.$group.'%';
    
        if ($type === 'ETUDE') {
            $rows = EtudeProjet::query()
                // ❌ pas d'alias ici
                ->selectRaw('etude_projets.code_projet_etude as code, etude_projets.intitule as label')
                ->where('etude_projets.code_pays', $country)
                ->where('etude_projets.code_projet_etude','like',$prefixEtude)
                ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,5,6]))
                ->orderBy('etude_projets.code_projet_etude')
                ->get();
    
            return response()->json($rows);
        }
    
        if ($type === 'APPUI') {
            $rows = AppuiProjet::query()
                // ❌ pas d'alias ici
                ->selectRaw('appui_projets.code_projet_appui as code, appui_projets.intitule as label')
                ->where('appui_projets.code_pays', $country)
                ->where('appui_projets.code_projet_appui','like',$prefixAppui)
                ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,5,6]))
                ->orderBy('appui_projets.code_projet_appui')
                ->get();
    
            return response()->json($rows);
        }
    
        // PROJET
        $rows = Projet::query()
            // ❌ pas d'alias ici
            ->selectRaw('projets.code_projet as code, projets.libelle_projet as label')
            ->where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet','like',$prefixProjet)
            ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,5,6]))
            ->orderBy('projets.code_projet')
            ->get();
    
        return response()->json($rows);
    }
    
    /** Trouve la famille d’un code parmi les 3 tables. */
    private function resolveFamilyAny(string $code): ?string
    {
        if (Projet::where('code_projet',$code)->exists()) return 'PROJET';
        if (EtudeProjet::where('code_projet_etude',$code)->exists()) return 'ETUDE';
        if (AppuiProjet::where('code_projet_appui',$code)->exists()) return 'APPUI';
        return null;
    }

    /** Annuler (statut=4) — toutes familles */
    public function annulerProjet(Request $request)
    {
        $validated = $request->validate([
            'code_projet' => 'required|string',
            'motif'       => 'required|string|min:5',
        ]);

        $code = $validated['code_projet'];
        $fam  = $this->resolveFamilyAny($code);
        if (!$fam) {
            return back()->with('error','Code projet introuvable dans PROJET/ETUDE/APPUI.');
        }

        try {
            DB::transaction(function () use ($code, $validated) {
                $dejaAnnule = ProjetStatut::where('code_projet',$code)
                                ->where('type_statut',4)->exists();
                if ($dejaAnnule) {
                    throw ValidationException::withMessages([
                        'code_projet' => 'Ce projet est déjà annulé.',
                    ]);
                }

                ProjetStatut::create([
                    'code_projet' => $code,
                    'type_statut' => 4, // Annulé
                    'date_statut' => now(),
                    'motif'       => $validated['motif'],
                ]);

                // Optionnel : désactiver exécutions en cours (toutes familles utilisent code_projet identique)
                // Executer::where('code_projet',$code)->update(['is_active'=>false]);
            });

            Log::info('Projet annulé', ['code_projet'=>$code,'user_id'=>auth()->id()]);
            return redirect()->route('projets.annulation.form')->with('success','Projet annulé avec succès.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Erreur annulation projet', ['code'=>$code,'err'=>$e->getMessage()]);
            return back()->with('error', "Erreur lors de l’annulation du projet.");
        }
    }


    /**
     * Liste / consultation des projets (alimente resources/views/users/create.blade.php)
     */
    public function ConsultationProjet(Request $request)
    {
        try {
            // Filtres de contexte (si tu les utilises déjà)
            $pays   = session('pays_selectionne');
            $groupe = session('projet_selectionne');

            // Charger ce qu'il faut pour la vue
            $projets = Projet::query()
                ->with([
                    'devise',                                    // pour $projet->devise->code_long
                    'statuts.statut',                            // dernier statut (si besoin)
                    'localisations.localite.decoupage',          // pour extraire région/district
                    'sousDomaine',                               // libellé du sous-domaine
                    'sousDomaine.domaine',                       // libellé du domaine
                ])
                // Filtre optionnel par code pays+groupe
                ->when($pays && $groupe, function ($q) use ($pays, $groupe) {
                    $q->where('code_projet', 'like', $pays.$groupe.'%');
                })
                ->orderByDesc('created_at')
                ->get();

            // Adapter les attributs pour coller exactement à ta vue (champs en MAJ / alias)
            $projets->each(function (Projet $p) {
                // Champs attendus par la vue en MAJ / alias
                $p->CodeProjet              = $p->code_projet;
                $p->Date_demarrage_prevue   = $p->date_demarrage_prevue;
                // la vue utilise déjà "date_fin_prevue" et "cout_projet" en snake_case, on laisse tel quel

                // Domaine / Sous-domaine
                $p->domaine_libelle        = $p->sousDomaine->domaine->libelle ?? null;
                $p->sous_domaine_libelle   = $p->sousDomaine->libelle ?? null;

                // Region / District depuis la première localisation (si présente)
                $firstLoc = $p->localisations->first();
                $p->region_libelle   = $firstLoc->localite->decoupage->region_libelle  // si ton découpage expose ce champ
                                       ?? $firstLoc->localite->region_libelle
                                       ?? null;

                $p->district_libelle = $firstLoc->localite->decoupage->district_libelle
                                       ?? $firstLoc->localite->district_libelle
                                       ?? null;
            });

            // Toutes les lignes de statuts (la vue affiche potentiellement plusieurs statuts par projet)
            // On renvoie une collection avec ->CodeProjet et ->statut_libelle pour rester plug&play avec la vue
            $Statuts = ProjetStatut::query()
                ->with('statut')
                ->whereIn('code_projet', $projets->pluck('code_projet'))
                ->get()
                ->map(function (ProjetStatut $ps) {
                    return (object) [
                        'CodeProjet'     => $ps->code_projet,
                        'statut_libelle' => $ps->statut->libelle ?? '-',
                    ];
                });

            return view('projets.consultation', compact('projets', 'Statuts'));
        } catch (\Throwable $e) {
            Log::error('Erreur chargement consultation projets', ['error' => $e->getMessage()]);
            return back()->with('error', "Impossible de charger la liste des projets.");
        }
    }
    /**
     * Vue "Suspendre un projet" + liste des projets suspendus/redémarrés (derniers statuts 5/6)
     */
    public function formSuspension(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $pays  = (string) session('pays_selectionne');
        $group = (string) session('projet_selectionne');

        $prefixProjet = $pays.$group.'%';
        $prefixEtude  = 'ET_'.$pays.'_'.$group.'%';
        $prefixAppui  = 'APPUI_'.$pays.'_'.$group.'%';

        // On rassemble tous les statuts 5/6, puis on agrège par code_projet
        $ps = ProjetStatut::query()
            ->whereIn('type_statut', [5,6]) // 5 = suspendu, 6 = redémarré
            ->where(function($q) use ($prefixProjet,$prefixEtude,$prefixAppui){
                $q->where('code_projet','like',$prefixProjet)
                  ->orWhere('code_projet','like',$prefixEtude)
                  ->orWhere('code_projet','like',$prefixAppui);
            })
            ->orderBy('date_statut')
            ->get()
            ->groupBy('code_projet')
            ->map(function($items, $code){
                $last      = $items->last(); // dernier statut chronologique
                $firstSusp = $items->where('type_statut',5)->first();
                $lastStart = $items->where('type_statut',6)->last();

                $libelle = Projet::where('code_projet',$code)->value('libelle_projet')
                      ?? EtudeProjet::where('code_projet_etude',$code)->value('intitule')
                      ?? AppuiProjet::where('code_projet_appui',$code)->value('intitule')
                      ?? '';

                return (object)[
                    'code_projet'      => $code,
                    'libelle_projet'   => $libelle,
                    'date_suspension'  => $firstSusp->date_statut ?? null,
                    'motif_suspension' => $firstSusp->motif       ?? null,
                    'dernier_type'     => $last?->type_statut,                // 5 ou 6
                    'date_redemarrage' => $lastStart->date_statut ?? null,
                    'statut_libelle'   => ($last?->type_statut === 6) ? 'Redémarré' : 'Suspendu',
                ];
            })
            ->values();

        // La sélection du projet est chargée en AJAX (par type), donc $projets peut rester vide
        $projets = collect();

        return view('projets.GestionExceptions.suspendreProjet', compact('ecran','projets','ps'))
            ->with('projetsSuspendus', $ps);
    }

    /**
     * Options projets éligibles à la suspension par type (PROJET|ETUDE|APPUI)
     * Dernier statut éligible ∈ [1,2,6] (pas déjà suspendu)
     * Réponse: [{ code, label }]
     */
    public function suspensionOptionsProjets(Request $request)
    {
        $type    = strtoupper((string) $request->query('type', 'PROJET'));
        if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
            return response()->json([], 200);
        }

        $country = (string) session('pays_selectionne');
        $group   = (string) session('projet_selectionne');
        if ($country === '' || $group === '') return response()->json([], 200);

        $prefixProjet = $country.$group.'%';
        $prefixEtude  = 'ET_'.$country.'_'.$group.'%';
        $prefixAppui  = 'APPUI_'.$country.'_'.$group.'%';

        if ($type === 'ETUDE') {
            $rows = EtudeProjet::query()
                ->selectRaw('etude_projets.code_projet_etude as code, etude_projets.intitule as label')
                ->where('etude_projets.code_pays', $country)
                ->where('etude_projets.code_projet_etude','like',$prefixEtude)
                ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,6]))
                ->orderBy('etude_projets.code_projet_etude')
                ->get();

            return response()->json($rows);
        }

        if ($type === 'APPUI') {
            $rows = AppuiProjet::query()
                ->selectRaw('appui_projets.code_projet_appui as code, appui_projets.intitule as label')
                ->where('appui_projets.code_pays', $country)
                ->where('appui_projets.code_projet_appui','like',$prefixAppui)
                ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,6]))
                ->orderBy('appui_projets.code_projet_appui')
                ->get();

            return response()->json($rows);
        }

        $rows = Projet::query()
            ->selectRaw('projets.code_projet as code, projets.libelle_projet as label')
            ->where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet','like',$prefixProjet)
            ->whereHas('dernierStatut', fn($q)=> $q->whereIn('type_statut',[1,2,6]))
            ->orderBy('projets.code_projet')
            ->get();

        return response()->json($rows);
    }


    /**
     * Suspension (statut 5)
     */
    public function suspendreProjet(Request $request)
    {
        $data = $request->validate([
            'code_projet' => ['required','string'],
            'motif'       => ['required','string','min:5'],
        ]);

        $code = $data['code_projet'];

        // Dernier statut ne doit PAS être 5 (déjà suspendu)
        $dernier = ProjetStatut::where('code_projet', $code)
                    ->orderByDesc('date_statut')->orderByDesc('id')->first();

        if ($dernier && (int)$dernier->type_statut === 5) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Le projet est déjà suspendu.'], 422)
                : back()->with('error','Le projet est déjà suspendu.')->withInput();
        }

        if ($dernier && !in_array((int)$dernier->type_statut, [1,2,6], true)) {
            return $request->expectsJson()
                ? response()->json(['message' => "Statut actuel incompatible avec la suspension."], 422)
                : back()->with('error',"Statut actuel incompatible avec la suspension.")->withInput();
        }

        DB::transaction(function () use ($code, $data) {
            ProjetStatut::create([
                'code_projet' => $code,
                'type_statut' => 5,
                'date_statut' => now(),
                'motif'       => $data['motif'],
            ]);
        });

        Log::info('Projet suspendu', ['code_projet'=>$code,'user_id'=>auth()->id()]);

        return $request->expectsJson()
            ? response()->json(['success'=>true,'message'=>'Projet suspendu avec succès.'])
            : redirect()->route('projets.suspension.form')->with('success','Projet suspendu avec succès.');
    }

    /**
     * Redémarrer (statut 6)
     */
    public function redemarrerProjet(Request $request)
    {
        $data = $request->validate([
            'code_projet'     => ['required','string'],
            'dateRedemarrage' => ['required','date'],
        ]);
        $code  = $data['code_projet'];
        $dateR = Carbon::parse($data['dateRedemarrage'])->startOfDay();

        $lastSusp = ProjetStatut::where('code_projet', $code)
            ->where('type_statut', 5)
            ->orderByDesc('date_statut')->orderByDesc('id')->first();

        if (!$lastSusp) {
            return $request->expectsJson()
                ? response()->json(['error' => "Ce projet n’est pas en état 'suspendu'."], 422)
                : back()->with('error',"Ce projet n’est pas en état 'suspendu'.")->withInput();
        }

        $suspDate = Carbon::parse($lastSusp->date_statut)->startOfDay();
        if ($dateR->lte($suspDate)) {
            return $request->expectsJson()
                ? response()->json(['error' => "La date de redémarrage doit être > {$suspDate->toDateString()}."], 422)
                : back()->with('error',"La date de redémarrage doit être > {$suspDate->toDateString()}.")->withInput();
        }

        $alreadyRestarted = ProjetStatut::where('code_projet',$code)
            ->where('type_statut',6)
            ->where('date_statut','>=',$lastSusp->date_statut)
            ->exists();

        if ($alreadyRestarted) {
            return $request->expectsJson()
                ? response()->json(['error' => "Déjà redémarré après la dernière suspension."], 422)
                : back()->with('error',"Déjà redémarré après la dernière suspension.")->withInput();
        }

        DB::transaction(function() use ($code,$dateR){
            ProjetStatut::create([
                'code_projet' => $code,
                'type_statut' => 6,
                'date_statut' => $dateR->toDateString(),
            ]);
        });

        Log::info('Projet redémarré', ['code_projet'=>$code,'date'=>$dateR->toDateString(),'user_id'=>auth()->id()]);

        return $request->expectsJson()
            ? response()->json(['success' => 'Projet redémarré avec succès.'])
            : redirect()->route('projets.suspension.form')->with('success','Projet redémarré avec succès.');
    }
        /**
     * Fiche compacte d’un projet (quel que soit le type) — utilisée pour la carte d’infos
     */
    public function getProjetCardSus(string $code)
    {
        $famille = str_starts_with($code,'ET_') ? 'ETUDE' : (str_starts_with($code,'APPUI_') ? 'APPUI' : 'PROJET');

        if ($famille === 'PROJET') {
            $p = Projet::with(['statuts.statut','sousDomaine.domaine','maitreOuvrage.acteur','localisations.localite'])->where('code_projet',$code)->first();
            if (!$p) return response()->json(null);
            return response()->json([
                'code_projet'              => $p->code_projet,
                'libelle_projet'           => $p->libelle_projet,
                'nature'                   => $p->statuts?->statut?->libelle,
                'domaine'                  => $p->sousDomaine?->domaine?->libelle,
                'sousDomaine'              => $p->sousDomaine?->lib_sous_domaine ?? $p->sousDomaine?->libelle,
                'cout'                     => $p->cout_projet,
                'devise'                   => $p->code_devise,
                'maitreOuvrage'            => $p->maitreOuvrage?->acteur?->libelle_long,
                'localite'                 => $p->localisations->pluck('localite.libelle')->filter()->values(),
                'date_demarrage_prevue'    => $p->date_demarrage_prevue,
                'date_fin_prevue'          => $p->date_fin_prevue,
            ]);
        }

        if ($famille === 'ETUDE') {
            $e = EtudeProjet::with(['statuts.statut','sousDomaine.domaine','maitreOuvrage.acteur'])->where('code_projet_etude',$code)->first();
            if (!$e) return response()->json(null);
            return response()->json([
                'code_projet'              => $e->code_projet_etude,
                'libelle_projet'           => $e->intitule,
                'nature'                   => $e->statuts?->statut?->libelle,
                'domaine'                  => $e->sousDomaine?->domaine?->libelle,
                'sousDomaine'              => $e->sousDomaine?->lib_sous_domaine ?? $e->sousDomaine?->libelle,
                'cout'                     => $e->montant_budget_previsionnel,
                'devise'                   => $e->code_devise,
                'maitreOuvrage'            => $e->maitreOuvrage?->acteur?->libelle_long,
                'localite'                 => [],
                'date_demarrage_prevue'    => $e->date_debut_previsionnel,
                'date_fin_prevue'          => $e->date_fin_previsionnel,
            ]);
        }

        $a = AppuiProjet::with(['statuts.statut','sousDomaine.domaine','maitreOuvrage.acteur','localisations.localite'])->where('code_projet_appui',$code)->first();
        if (!$a) return response()->json(null);
        return response()->json([
            'code_projet'              => $a->code_projet_appui,
            'libelle_projet'           => $a->intitule,
            'nature'                   => $a->statuts?->statut?->libelle,
            'domaine'                  => $a->sousDomaine?->domaine?->libelle,
            'sousDomaine'              => $a->sousDomaine?->lib_sous_domaine ?? $a->sousDomaine?->libelle,
            'cout'                     => $a->montant_budget_previsionnel,
            'devise'                   => $a->code_devise,
            'maitreOuvrage'            => $a->maitreOuvrage?->acteur?->libelle_long,
            'localite'                 => $a->localisations->pluck('localite.libelle')->filter()->values(),
            'date_demarrage_prevue'    => $a->date_debut_previsionnel,
            'date_fin_prevue'          => $a->date_fin_previsionnel,
        ]);
    }


}

