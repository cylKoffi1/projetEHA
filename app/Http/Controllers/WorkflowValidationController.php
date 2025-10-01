<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\NotificationValidationProjet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ------- Modèles Références -------
use App\Models\ModeWorkflow;
use App\Models\StatutInstance;
use App\Models\StatutEtapeInstance;
use App\Models\TypeAction;
use App\Models\OperateurRegle;

// ------- Modèles Conception -------
use App\Models\WorkflowApprobation;
use App\Models\VersionWorkflow;
use App\Models\EtapeWorkflow;
use App\Models\EtapeApprobateur;
use App\Models\EtapeRegle;
use App\Models\LiaisonWorkflow;

// ------- Modèles Exécution -------
use App\Models\InstanceApprobation;
use App\Models\InstanceEtape;
use App\Models\ActionApprobation;
use App\Models\EscaladeApprobation;

// ------- Contexte (pays/projet) -------
use App\Models\Pays;
use App\Models\GroupeProjet;
use App\Models\GroupeProjetPaysUser;
use App\Models\User;
use App\Models\Acteur;
use Illuminate\Support\Facades\Mail; 

class WorkflowValidationController extends Controller
{
    /***********************************************************
     *                         VUES
     ***********************************************************/

    /** Page liste front -> resources/views/workflows/index.blade.php */
    public function ui()
    {
        // $this->authorize('workflow.viewAny');
        $ctx = $this->buildViewContext();
        return view('workflows.index', ['ctx' => $ctx]);
    }

    /** Formulaire de création -> resources/views/workflows/form.blade.php */
    public function createForm()
    {
        $ctx = $this->buildViewContext();
        $approverUsers = $this->getApproverUsers($ctx['pays_selected'] ?? null, $ctx['projet_selected'] ?? null);
    
        return view('workflows.form', [
            'ctx'           => $ctx,
            'approverUsers' => $approverUsers,
        ]);
    }
    


    /** Formulaire d’édition/design -> resources/views/workflows/form.blade.php */
    public function designForm($id)
    {
        // $this->authorize('workflow.update', $workflow);
        $workflow = WorkflowApprobation::with([
            'versions' => function ($q) {
                $q->with(['etapes' => function ($q) {
                    $q->with(['approbateurs', 'regles']);
                }])->orderByDesc('numero_version');
            }
        ])->findOrFail($id);

        $last = $workflow->versions->first();

        $prefill = [
            'workflow' => [
                'code'              => $workflow->code,
                'nom'               => $workflow->nom,
                'code_pays'         => $workflow->code_pays,
                'groupe_projet_id'  => $workflow->groupe_projet_id,
            ],
            'version' => $last ? [
                'numero_version'       => $last->numero_version,
                'politique_changement' => $last->politique_changement,
                'publie'               => (bool)$last->publie,
                'etapes'               => $last->etapes->map(function ($e) {
                    return [
                        'position'                => $e->position,
                        'mode'                    => optional(ModeWorkflow::find($e->mode_id))->code,
                        'quorum'                  => $e->quorum,
                        'sla_heures'              => $e->sla_heures,
                        'delegation_autorisee'    => (bool)$e->delegation_autorisee,
                        'sauter_si_vide'          => (bool)$e->sauter_si_vide,
                        'politique_reapprobation' => $e->politique_reapprobation,
                        'approbateurs'            => $e->approbateurs->map(fn ($a) => [
                            'type_approbateur'      => $a->type_approbateur,
                            'reference_approbateur' => $a->reference_approbateur,
                            'obligatoire'           => (bool)$a->obligatoire,
                        ]),
                        'regles'                  => $e->regles->map(function ($r) {
                            return [
                                'champ'          => $r->champ,
                                'operateur_code' => optional(OperateurRegle::find($r->operateur_id))->code,
                                'valeur'         => $r->valeur,
                            ];
                        }),
                    ];
                }),
            ] : null,
        ];

        $ctx = $this->buildViewContext();

        $approverUsers = $this->getApproverUsers(
            $ctx['pays_selected'] ?? null,
            $ctx['projet_selected'] ?? null
        );
    
        return view('workflows.form', [
            'workflowId'    => (int)$workflow->id,
            'prefill'       => $prefill,
            'ctx'           => $ctx,
            'approverUsers' => $approverUsers,
        ]);
    }

    /** Vue liaisons/bindings -> resources/views/workflows/bindings.blade.php */

    public function bindingsView($id)
    {
        $wf = WorkflowApprobation::with([
            'versions' => fn($q) => $q->where('publie', 1)->orderBy('numero_version')
        ])->findOrFail($id);

        // liaisons de TOUTES les versions de ce workflow, avec numéro de version
        $bindings = LiaisonWorkflow::with(['version:id,workflow_id,numero_version'])
            ->whereIn('version_workflow_id', VersionWorkflow::where('workflow_id', $wf->id)->pluck('id'))
            ->orderByDesc('id')
            ->get();

        return view('workflows.bindings', [
            'workflowId'        => (int) $wf->id,
            'publishedVersions' => $wf->versions, // -> foreach dans le select Version
            'bindings'          => $bindings,     // -> foreach dans le select & le tableau
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $actorCode = optional($user?->acteur)->code_acteur;
    
        // Étapes où l'utilisateur est approbateur (ACTEUR) et encore actives
        $pendingSteps = InstanceEtape::query()
            ->with([
                'instance.version.workflow',             // contexte (nom workflow)
                'instance.etapes.etape.approbateurs',    // pour "avant / après"
                'etape.approbateurs',                    // approbateurs de l’étape courante
                'etape'                                  // position
            ])
            ->whereIn('statut_id', [
                $this->statutEtape('PENDING')->id,
                $this->statutEtape('EN_COURS')->id,
            ])
            ->whereHas('etape.approbateurs', function($q) use ($actorCode) {
                $q->where('type_approbateur', 'ACTEUR')
                  ->where('reference_approbateur', $actorCode);
            })
            ->orderBy('date_debut', 'asc')
            ->get();
            $Users = User::select('acteur_id')  
            ->with(['acteur:code_acteur,libelle_court,libelle_long'])
            ->get();
    
        // Prépare des DTO simples pour la vue
        $rows = $pendingSteps->map(function($si) {
            $currentPos = $si->etape->position;
        
            // approbateurs attendus de l'étape courante
            $expApprovers = $si->etape->approbateurs
                ->where('type_approbateur','ACTEUR')
                ->pluck('reference_approbateur')
                ->values();

            $acteurs = Acteur::whereIn('code_acteur', $expApprovers)
                ->get(['code_acteur','libelle_court','libelle_long'])
                ->keyBy('code_acteur');

            $approvers = $expApprovers->map(function ($code) use ($acteurs, $si) {
                $a = $acteurs->get($code); // \App\Models\Acteur|null

                // libellé et initiales à partir de libelle_court / libelle_long
                $label    = $this->labelFromActor($a, $code);         // fallback = code
                $initials = $this->initialsFromActor($a);             // calcule les initiales

                return [
                    'code'      => $code,
                    'label'     => $label,
                    'initials'  => $initials ?: '?',
                    'status'    => $this->approverStatusFor($si, $code),
                ];
            });


        
            return [
                'step_id'        => $si->id,
                'instance_id'    => $si->instance->id,
                'module'         => $si->instance->module_code,
                'type'           => $si->instance->type_cible,
                'target_id'      => $si->instance->id_cible,
                'workflow_name'  => $si->instance->version->workflow->nom ?? 'N/A',
                'version'        => $si->instance->version->numero_version ?? null,
                'step_pos'       => $si->etape->position,
                'status_code'    => $this->codeStatutEtape($si->statut_id),
                'approvers'      => $approvers, // <<< NOUVEAU
            ];
        });
        
    
        return view('approbations.dashboard', compact('Users', 'rows'));
    }
    

    /** Vue détail d’une instance + actions -> resources/views/approbations/show.blade.php */
    public function instanceView($instanceId)
    {
        // $this->authorize('approval.view', $instance);
        $ctx = $this->buildViewContext();
        return view('approbations.show', ['instanceId' => (int)$instanceId, 'ctx' => $ctx]);
    }

    /***********************************************************
     *      CONTEXTE PAYS / GROUPE PROJET POUR LES VUES
     ***********************************************************/
    private function buildViewContext(): array
    {
        $user = auth()->user();

        // 1) Pays sélectionné (session) ou premier pays accessible par l'utilisateur
        $alpha3Selected = session('pays_selectionne');
        $userPaysCodes  = collect();

        if ($user) {
            // via la table de jointure (groupe_projet_pays_user)
            $userPaysCodes = GroupeProjetPaysUser::query()
                ->where('user_id', $user->acteur_id)
                ->pluck('pays_code')
                ->unique()
                ->values();
        }

        if (!$alpha3Selected) {
            $alpha3Selected = $userPaysCodes->first(); // fallback: le 1er pays de l'utilisateur
        }

        // 2) Liste des pays accessibles
        $paysOptionsQuery = Pays::query()->orderBy('nom_fr_fr');
        if ($userPaysCodes->isNotEmpty()) {
            $paysOptionsQuery->whereIn('alpha3', $userPaysCodes);
        }
        $paysOptions = $paysOptionsQuery->get(['alpha3','nom_fr_fr']);

        // 3) Groupe projet sélectionné (session) ou premier groupe du pays sélectionné
        $gpSelected = session('projet_selectionne');
        $groupesDansPays = collect();

        if ($user && $alpha3Selected) {
            $gppus = GroupeProjetPaysUser::query()
                ->where('user_id', $user->acteur_id)
                ->where('pays_code', $alpha3Selected)
                ->with('groupeProjet')
                ->get();

            $groupesDansPays = $gppus
                ->map(fn($r) => $r->groupeProjet)
                ->filter()
                ->unique('code')
                ->values();

            if (!$gpSelected && $groupesDansPays->isNotEmpty()) {
                $gpSelected = $groupesDansPays->first()->code;
            }
        }

        // 4) Liste des groupes accessibles dans le pays sélectionné
        $groupeOptions = $groupesDansPays->isNotEmpty()
            ? $groupesDansPays->map(fn($g)=>['code'=>$g->code,'libelle'=>$g->libelle])->values()
            : GroupeProjet::query()->orderBy('libelle')->get(['code','libelle'])->map(fn($g)=>['code'=>$g->code,'libelle'=>$g->libelle]);

        return [
            'pays_selected'    => $alpha3Selected,
            'projet_selected'  => $gpSelected,
            'pays_options'     => $paysOptions,    // collection de {alpha3, nom_fr_fr}
            'groupe_options'   => $groupeOptions,  // collection de {code, libelle}
        ];
    }
    private function getApproverUsers(?string $alpha3 = null, ?string $gpCode = null)
    {
        $alpha3  = $alpha3 ?: (string) session('pays_selectionne');
        $gpCode  = $gpCode ?: (string) session('projet_selectionne');
    
        $q = User::query()
            ->where('is_active', true)
            // Filtre via la table pivot groupe_projet_pays_user
            ->whereHas('projetsPays', function ($q) use ($alpha3, $gpCode) {
                if ($alpha3) {
                    $q->where('pays_code', $alpha3);
                }
                if ($gpCode) {
                    $q->where('groupe_projet_id', $gpCode);
                }
            })
            ->with(['acteur' => function($q){
                $q->select('code_acteur','libelle_long','libelle_court','email');
            }])
            ->orderBy('login');
    
        // On récupère les colonnes utiles du user
        return $q->get(['acteur_id','login','email']);
    }
    
    /** Hydrate code_pays / groupe_projet_id depuis la session si absents */
    private function hydrateContextFromSession(Request $request): void
    {
        if (!$request->filled('code_pays') && ($alpha3 = session('pays_selectionne'))) {
            $request->merge(['code_pays' => $alpha3]);
        }
        if (!$request->filled('groupe_projet_id') && ($gp = session('projet_selectionne'))) {
            $request->merge(['groupe_projet_id' => $gp]);
        }
    }

    /***********************************************************
     *              LISTE / LECTURE DESIGN (JSON)
     ***********************************************************/
    public function index(Request $request)
    {
        // $this->authorize('workflow.viewAny');
        $q = WorkflowApprobation::query()
            ->with(['versions' => function ($q) {
                $q->orderByDesc('numero_version');
            }])
            ->orderBy('code_pays')->orderBy('code');

        // pays explicitement demandé ?
        if ($request->filled('pays')) {
            $q->where('code_pays', $request->string('pays'));
        } else {
            // sinon => filtre sur le pays sélectionné en session si disponible
            if ($alpha3 = session('pays_selectionne')) {
                $q->where('code_pays', $alpha3);
            }
        }

        if ($request->filled('code')) {
            $q->where('code', 'like', '%' . $request->string('code') . '%');
        }

        return response()->json($q->paginate(30));
    }

    public function show($id)
    {
        // $this->authorize('workflow.view', $workflow);
        $workflow = WorkflowApprobation::with([
            'versions' => function ($q) {
                $q->with(['etapes' => function ($q) {
                    $q->with(['approbateurs', 'regles']);
                }])->orderBy('numero_version');
            },
            'versions.liaisons'
        ])->findOrFail($id);

        return response()->json($workflow);
    }

    /***********************************************************
     *          CRÉATION / MISE À JOUR / PUBLICATION (JSON)
     ***********************************************************/
    public function store(Request $request)
    {
        $this->hydrateContextFromSession($request);
        $data = $this->validateWorkflowPayload($request);
    
        return DB::transaction(function () use ($data) {
           
            $wf = WorkflowApprobation::create([
                //'code'              => 'WF_' . strtoupper($data['code_pays']) . strtoupper($data['groupe_projet_id']) . '_' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT),
                'nom'              => $data['nom'],
                'code_pays'        => $data['code_pays'],
                'groupe_projet_id' => $data['groupe_projet_id'] ?? null,
                'actif'            => 1,
                'meta'             => $data['meta'] ?? null,
            ]);
    
            // NEW WORKFLOW -> crée toujours une nouvelle version (1)
            $this->upsertVersionGraph($wf->id, $data['version'] ?? [], 'new_version');
    
            return response()->json([
                'message'  => 'Workflow créé',
                'workflow' => $wf->load('versions.etapes.approbateurs', 'versions.etapes.regles')
            ], 201);
        });
    }
    
    public function update(Request $request, $id)
    {
        $workflow = WorkflowApprobation::findOrFail($id);
        $this->hydrateContextFromSession($request);
        $data = $this->validateWorkflowPayload($request, updating: true);
        $mode = $data['mode_version'] ?? 'new_version';
    
        return DB::transaction(function () use ($workflow, $data, $mode) {
            $workflow->update([
                'nom'              => $data['nom'] ?? $workflow->nom,
                'code_pays'        => $data['code_pays'] ?? $workflow->code_pays,
                'groupe_projet_id' => $data['groupe_projet_id'] ?? $workflow->groupe_projet_id,
                'meta'             => $data['meta'] ?? $workflow->meta,
            ]);
    
            if ($mode === 'update_existing' && !empty($data['version']['numero_version'])) {
                $existing = VersionWorkflow::where('workflow_id', $workflow->id)
                    ->where('numero_version', $data['version']['numero_version'])
                    ->first();
    
                if ($existing && ($existing->publie || $this->versionIsInUse($existing))) {
                    // 409 = conflit logique
                    abort(response()->json([
                        'message' => "Impossible de modifier une version publiée ou déjà utilisée. Créez une nouvelle version.",
                        'code'    => 'VERSION_IN_USE'
                    ], 409));
                }
            }
    
            $this->upsertVersionGraph($workflow->id, $data['version'] ?? [], $mode);
            return response()->json([
                'message'  => $mode === 'update_existing' ? 'Version mise à jour' : 'Nouvelle version créée',
                'workflow' => $workflow->load('versions.etapes.approbateurs', 'versions.etapes.regles')
            ]);
        });
    }
    

    /** Publier une version (exclusif) */
    public function publish(Request $request, $id)
    {
        // $this->authorize('workflow.publish', $workflow);
        $request->validate(['numero_version' => ['required','integer','min:1']]);
        $workflow = WorkflowApprobation::findOrFail($id);

        DB::transaction(function() use ($workflow, $request) {
            VersionWorkflow::where('workflow_id', $workflow->id)->update(['publie' => 0]);
            VersionWorkflow::where('workflow_id', $workflow->id)
                ->where('numero_version', $request->integer('numero_version'))
                ->update(['publie' => 1]);
        });

        $version = VersionWorkflow::where('workflow_id', $workflow->id)
            ->where('numero_version', $request->integer('numero_version'))
            ->firstOrFail();

        return response()->json([
            'message' => "Version {$version->numero_version} publiée",
            'version' => $version->load('etapes.approbateurs', 'etapes.regles')
        ]);
    }

    /** Supprimer un workflow (cascade via FK) */
    public function destroy($id)
    {
        // $this->authorize('workflow.delete', $workflow);
        $wf = WorkflowApprobation::findOrFail($id);
        $wf->delete();
        return response()->json(['message' => 'Workflow supprimé']);
    }

    /***********************************************************
     *                        LIAISONS (JSON)
     ***********************************************************/
    /** Attache une version publiée à un module/objet. */
    public function bind(Request $request, $id)
    {
        // $this->authorize('workflow.bind', $workflow);
        $data = $request->validate([
            'numero_version' => ['required', 'integer', 'min:1'],
            'module_code'    => ['required', 'string', 'max:100'],
            'type_cible'     => ['required', 'string', 'max:100'],
            'id_cible'       => ['nullable', 'string', 'max:150'],
            'par_defaut'     => ['boolean']
        ]);

        $wf = WorkflowApprobation::findOrFail($id);
        $version = VersionWorkflow::where('workflow_id', $wf->id)
            ->where('numero_version', $data['numero_version'])
            ->where('publie', 1)
            ->firstOrFail();

        $binding = LiaisonWorkflow::updateOrCreate(
            [
                'version_workflow_id' => $version->id,
                'module_code'         => $data['module_code'],
                'type_cible'          => $data['type_cible'],
                'id_cible'            => $data['id_cible'],
            ],
            ['par_defaut' => (int)($data['par_defaut'] ?? 0)]
        );

        return response()->json([
            'message' => 'Liaison enregistrée',
            'liaison' => $binding
        ], 201);
    }

    /** Lister les liaisons d’un workflow (toutes versions) */
    public function bindings($id)
    {
        $wf = WorkflowApprobation::findOrFail($id);

        $bindings = LiaisonWorkflow::with(['version:id,workflow_id,numero_version'])
            ->whereIn('version_workflow_id',
                VersionWorkflow::where('workflow_id', $wf->id)->pluck('id')
            )
            ->get()
            ->map(function ($b) {
                return [
                    'id'                   => $b->id,
                    'version_workflow_id'  => $b->version_workflow_id,
                    'numero_version'       => optional($b->version)->numero_version,
                    'module_code'          => $b->module_code,
                    'type_cible'           => $b->type_cible,
                    'id_cible'             => $b->id_cible,
                    'par_defaut'           => (bool)$b->par_defaut,
                    'created_at'           => $b->created_at?->toDateTimeString(),
                ];
            });

        return response()->json($bindings->values());
    }


    /***********************************************************
     *                  EXÉCUTION : INSTANCES (JSON)
     ***********************************************************/
    /** Démarrer une instance d’approbation pour un objet. */
    public function start(Request $request)
    {
        // $this->authorize('approval.start', [$module,$type,$id]);
        $data = $request->validate([
            'module_code' => ['required', 'string', 'max:100'],
            'type_cible'  => ['required', 'string', 'max:100'],
            'id_cible'    => ['required', 'string', 'max:150'],
            'instantane'  => ['nullable', 'array']
        ]);

        $version = $this->resolveVersion($data['module_code'], $data['type_cible'], $data['id_cible']);
        if (!$version) {
            return response()->json(['error' => 'Aucune version publiée liée à ce module/objet'], 422);
        }

        return DB::transaction(function () use ($data, $version) {

            // Unicité instance par objet
            $exists = InstanceApprobation::where([
                'module_code' => $data['module_code'],
                'type_cible'  => $data['type_cible'],
                'id_cible'    => $data['id_cible'],
            ])->whereIn('statut_id', [
                $this->statutInstance('PENDING')->id,
                $this->statutInstance('EN_COURS')->id
            ])->lockForUpdate()->exists();

            if ($exists) {
                return response()->json(['error' => 'Une instance active existe déjà pour cet objet'], 409);
            }

            $inst = InstanceApprobation::create([
                'version_workflow_id' => $version->id,
                'module_code'         => $data['module_code'],
                'type_cible'          => $data['type_cible'],
                'id_cible'            => $data['id_cible'],
                'statut_id'           => $this->statutInstance('PENDING')->id,
                'instantane'          => $data['instantane'] ?? null,
            ]);

            // Créer les étapes d’instance (PENDING/SAUTE)
            foreach ($version->etapes()->orderBy('position')->get() as $etape) {
                if ($this->shouldSkip($etape, $inst->instantane)) {
                    $this->spawnStep($inst, $etape, $this->statutEtape('SAUTE')->id);
                } else {
                    $this->spawnStep($inst, $etape, $this->statutEtape('PENDING')->id, $etape->quorum);
                }
            }

            // Démarrer la première étape active
            $this->advance($inst);
            $this->notifyActiveApprovers($inst);

            return response()->json([
                'message'  => 'Instance démarrée',
                'instance' => $inst->load('etapes')
            ], 201);
        });
    }

    /** Détail d’une instance */
    public function showInstance($id)
    {
        $inst = InstanceApprobation::with([
            'version.workflow',
            'etapes.etape',
            'etapes.actions'
        ])->findOrFail($id);
        return response()->json($inst);
    }

    /** Action sur une étape (APPROUVER / REJETER / DELEGUER / COMMENTER). */
    public function act(Request $request, $stepInstanceId)
    {
        // $this->authorize('approval.act', $stepInstance);
        $data = $request->validate([
            'action_code' => ['required', 'string', 'max:30'], // APPROUVER|REJETER|DELEGUER|COMMENTER
            'commentaire' => ['nullable', 'string'],
            'meta'        => ['nullable', 'array']
        ]);

        $type = TypeAction::where('code', $data['action_code'])->first();
        if (!$type) return response()->json(['error' => 'Type d’action invalide'], 422);

        return DB::transaction(function () use ($stepInstanceId, $type, $data) {

            /** @var InstanceEtape $si */
            $si = InstanceEtape::with(['instance', 'etape', 'actions'])->lockForUpdate()->findOrFail($stepInstanceId);
            $inst = $si->instance;

            if (!in_array($si->statut_id, [
                $this->statutEtape('EN_COURS')->id,
                $this->statutEtape('PENDING')->id,
            ])) {
                return response()->json(['error' => 'Étape non active'], 409);
            }

            // Vérifier droit approbateur
            $actorCode = optional(auth()->user()?->acteur)->code_acteur;
            if (!$this->actorCanActOnStep($actorCode, $si)) {
                return response()->json(['error' => 'Acteur non autorisé pour cette étape'], 403);
            }

            // Enregistrer l’action
            ActionApprobation::create([
                'instance_etape_id' => $si->id,
                'code_acteur'       => $actorCode,
                'action_type_id'    => $type->id,
                'commentaire'       => $data['commentaire'] ?? null,
                'meta'              => $data['meta'] ?? null,
            ]);

            // Effets métier
            if ($type->code === 'APPROUVER') {
                $si->increment('nombre_approbations');
                if ($si->nombre_approbations >= $si->quorum_requis && $this->requiredApproversMet($si)) {
                    $si->update([
                        'statut_id' => $this->statutEtape('APPROUVE')->id,
                        'date_fin'  => now()
                    ]);
                    $this->advance($inst, $si);
                } else {
                    if ($si->statut_id == $this->statutEtape('PENDING')->id) {
                        $si->update(['statut_id' => $this->statutEtape('EN_COURS')->id, 'date_debut' => now()]);
                    }
                }
            } elseif ($type->code === 'REJETER') {
                $si->update([
                    'statut_id' => $this->statutEtape('REJETE')->id,
                    'date_fin'  => now()
                ]);
                $inst->update(['statut_id' => $this->statutInstance('REJETE')->id]);

                // 🔔 notifier le propriétaire / demandeur
                if ($owner = $this->resolveOwnerContact($inst)) {
                    $codeProjet    = $inst->id_cible;
                    $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;
                    $commentaire   = $data['commentaire'] ?? '';

                    \Mail::to($owner['email'])->queue(
                        new \App\Mail\ProjetRefuseNotification($codeProjet, $libelleProjet, $commentaire, $owner)
                    );
                }

                return response()->json([
                    'message' => 'Action enregistrée',
                    'etape'   => $si->fresh('actions')
                ]);



            } elseif ($type->code === 'DELEGUER') {
                $to = data_get($data, 'meta.delegate_to');
            if ($to) {
                // créer une “fausse” étape pour résolution d’email
                $targets = $this->approverContactsForStep($si);
                // si le délégué n’est pas dans la liste d’origine, résous-le à part :
                if (!collect($targets)->firstWhere('code', $to)) {
                    $a = \App\Models\Acteur::where('code_acteur', $to)->first();
                    $email = $a?->email; // ou via User lié
                    if ($email) {
                        $targets[] = ['code'=>$to,'email'=>$email,'libelle_court'=>$a->libelle_court,'libelle_long'=>$a->libelle_long];
                    }
                }
                foreach ($targets as $t) {
                    if ($t['code'] !== $to) continue;
                    $codeProjet    = $inst->id_cible;
                    $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;
                    Mail::to($t['email'])->queue(
                        new NotificationValidationProjet($codeProjet, $libelleProjet, $t)
                    );
                }
            }
                // La délégation est honorée via actorCanActOnStep() (meta.delegate_to)
                if ($si->statut_id == $this->statutEtape('PENDING')->id) {
                    $si->update(['statut_id' => $this->statutEtape('EN_COURS')->id, 'date_debut' => now()]);
                }
            } else { // COMMENTER
                if ($si->statut_id == $this->statutEtape('PENDING')->id) {
                    $si->update(['statut_id' => $this->statutEtape('EN_COURS')->id, 'date_debut' => now()]);
                }
            }

            return response()->json([
                'message' => 'Action enregistrée',
                'etape'   => $si->fresh('actions')
            ]);
        });
    }

    /***********************************************************
     *                    SIMULATION & SLA (JSON)
     ***********************************************************/
    /** Simulation de parcours (sans créer d’instance) */
    public function simulate(Request $request, $workflowId)
    {
        $wf = WorkflowApprobation::findOrFail($workflowId);
        $inp = $request->validate([
            'numero_version' => ['required', 'integer', 'min:1'],
            'instantane'     => ['nullable', 'array']
        ]);

        $version = VersionWorkflow::where('workflow_id', $wf->id)
            ->where('numero_version', $inp['numero_version'])
            ->firstOrFail();

        $result = [];
        foreach ($version->etapes()->orderBy('position')->get() as $etape) {
            $skip = $this->shouldSkip($etape, $inp['instantane'] ?? null);
            $result[] = [
                'position'     => $etape->position,
                'mode'         => ModeWorkflow::find($etape->mode_id)?->code,
                'quorum'       => $etape->quorum,
                'sera_saute'   => $skip,
                'approbateurs' => $etape->approbateurs()->get(['type_approbateur', 'reference_approbateur', 'obligatoire'])
            ];
        }
        return response()->json([
            'workflow' => $wf->only('id', 'code', 'nom'),
            'version'  => $version->numero_version,
            'parcours' => $result
        ]);
    }

    /** Tick SLA (pour Scheduler/CRON) */
    public function slaTick(Request $request)
    {
        $now = Carbon::now();
        $enCours = InstanceEtape::query()
            ->with(['instance', 'etape'])
            ->where('statut_id', $this->statutEtape('EN_COURS')->id)
            ->get();

        $count = 0;
        foreach ($enCours as $si) {
            $slaHours = $si->etape->sla_heures;
            if (!$slaHours || !$si->date_debut) continue;

            if (Carbon::parse($si->date_debut)->addHours($slaHours)->lt($now)) {
                $exists = EscaladeApprobation::where('instance_etape_id', $si->id)
                    ->where('raison', 'SLA')->exists();
                if (!$exists) {
                    EscaladeApprobation::create([
                        'instance_etape_id' => $si->id,
                        'acteur_source'     => null,
                        'acteur_cible'      => null, // À définir si tu as une hiérarchie
                        'raison'            => 'SLA',
                    ]);
                    $count++;
                }
            }
        }
        return response()->json(['message' => "SLA traités", 'escalades' => $count]);
    }

    /***********************************************************
     *                     HELPERS PRIVÉS
     ***********************************************************/
    private function approverContactsForStep(InstanceEtape $si): array
    {
        // approbateurs ACTEUR attendus pour l’étape $si
        $codes = $si->etape->approbateurs()
            ->where('type_approbateur','ACTEUR')
            ->pluck('reference_approbateur')->all();

        if (empty($codes)) return [];

        // emails possibles : acteur.email OU user.email lié à cet acteur
        $acteurs = Acteur::whereIn('code_acteur', $codes)
            ->get(['code_acteur','libelle_court','libelle_long','email'])
            ->keyBy('code_acteur');

        // si tu as une relation user<->acteur :
        $users = User::with('acteur:code_acteur')
            ->whereHas('acteur', fn($q) => $q->whereIn('code_acteur',$codes))
            ->get(['email','acteur_id']);

        $byActor = [];
        foreach ($codes as $code) {
            $a = $acteurs->get($code);
            $mail = null;

            // 1) email sur ACTEUR si renseigné
            if ($a && !empty($a->email)) $mail = $a->email;

            // 2) sinon prendre le mail du user attaché à l’acteur
            if (!$mail) {
                $u = $users->first(fn($uu) => optional($uu->acteur)->code_acteur === $code);
                if ($u && !empty($u->email)) $mail = $u->email;
            }

            if ($mail) {
                $byActor[] = [
                    'code'  => $code,
                    'email' => $mail,
                    'libelle_court' => (string)($a->libelle_court ?? ''),
                    'libelle_long'  => (string)($a->libelle_long  ?? ''),
                ];
            }
        }

        // déduplique par email
        $seen = [];
        return array_values(array_filter($byActor, function($x) use (&$seen){
            if (isset($seen[$x['email']])) return false;
            $seen[$x['email']] = true;
            return true;
        }));
    }
    private function versionIsInUse(VersionWorkflow $version): bool
    {
        $stepIds = $version->etapes()->pluck('id');
        return InstanceEtape::whereIn('etape_workflow_id', $stepIds)->exists();
    }

    // Trouve le "propriétaire/demandeur" d’une instance (adapté à ton modèle PROJET)
    private function resolveOwnerContact(InstanceApprobation $inst): ?array
    {
        // Cas PROJET • projet_appui (adapte si ton modèle/colonnes diffèrent)
        if ($inst->module_code === 'PROJET' && $inst->type_cible === 'projet_appui') {
            $proj = \App\Models\ProjetAppui::where('code',$inst->id_cible)
                    ->orWhere('id',$inst->id_cible)->first();
            if ($proj) {
                // 1) colonnes user id possibles (prends le 1er trouvé)
                foreach (['demandeur_user_id','owner_user_id','created_by','user_id'] as $col) {
                    if (!empty($proj->{$col})) {
                        if ($email = User::whereKey($proj->{$col})->value('email')) {
                            $codeActeur = User::with('acteur:code_acteur')
                                ->find($proj->{$col})?->acteur?->code_acteur;
                            return ['email'=>$email, 'code'=>$codeActeur];
                        }
                    }
                }
                // 2) colonnes acteur possibles (chef, porteur, demandeur…)
                foreach (['demandeur_acteur_code','chef_projet_code','porteur_acteur_code'] as $col) {
                    if (!empty($proj->{$col})) {
                        if ($c = $this->emailFromActorCode($proj->{$col})) return $c;
                    }
                }
            }
        }

        // 3) fallback générique via snapshot (instantané)
        $snap = (array)($inst->instantane ?? []);
        foreach (['owner_acteur_code','demandeur_acteur_code','chef_projet_code'] as $k) {
            if ($code = data_get($snap,$k)) {
                if ($c = $this->emailFromActorCode($code)) return $c;
            }
        }
        foreach (['owner_user_id','demandeur_user_id'] as $k) {
            if ($uid = data_get($snap,$k)) {
                if ($email = User::whereKey($uid)->value('email')) {
                    return ['email'=>$email];
                }
            }
        }

        return null;
    }
    private function emailFromActorCode(?string $code): ?array
    {
        if (!$code) return null;

        $a = Acteur::where('code_acteur', $code)->first();
        $email = $a?->email;

        // fallback: email du user rattaché à cet acteur
        if (!$email) {
            $email = User::whereHas('acteur', fn($q)=>$q->where('code_acteur',$code))
                ->value('email'); // <- renvoie directement la string d’email
        }

        if (!$email) return null;

        return [
            'code'          => $code,
            'email'         => $email,
            'libelle_court' => (string)($a->libelle_court ?? ''),
            'libelle_long'  => (string)($a->libelle_long  ?? ''),
        ];
    }
    private function notifyActiveApprovers(InstanceApprobation $inst): void
    {
        // étape active = EN_COURS
        $active = $inst->etapes()
            ->where('statut_id', $this->statutEtape('EN_COURS')->id)
            ->with('etape')
            ->first();

        if (!$active) return;

        $targets = $this->approverContactsForStep($active);
        if (empty($targets)) return;

        // Contexte “projet” à adapter : ici on envoie module/type/id
        $codeProjet    = $inst->id_cible;
        $libelleProjet = $inst->module_code.' • '.$inst->type_cible.' #'.$inst->id_cible;

        foreach ($targets as $t) {
            // tu peux passer l’objet “approbateur” (libellés) à ton Mailable
            Mail::to($t['email'])->queue(
                new NotificationValidationProjet($codeProjet, $libelleProjet, $t)
            );
        }
    }

    private function validateWorkflowPayload(Request $request, bool $updating = false): array
    {
        $rules = [
            // 'code' ❌ (généré automatiquement)
            'nom'              => [$updating ? 'sometimes' : 'required', 'string', 'max:200'],
            'mode_version' => ['nullable','in:update_existing,new_version'],
            // code_pays : obligatoire, 3 lettres, présent dans 'pays.alpha3'
            'code_pays'        => ['required', 'string', 'size:3', Rule::exists('pays', 'alpha3')],

            // groupe projet : optionnel mais doit exister si fourni
            'groupe_projet_id' => ['nullable', 'string', Rule::exists('groupe_projet', 'code')],
            'meta'             => ['nullable', 'array'],

            'version'          => [$updating ? 'sometimes' : 'required', 'array'],
            'version.numero_version'       => ['nullable', 'integer', 'min:1'],
            'version.politique_changement' => ['nullable', 'string', 'max:50'],
            'version.metadonnees'          => ['nullable', 'array'],
            'version.publie'               => ['nullable', 'boolean'],

            'version.etapes'               => ['required', 'array', 'min:1'],
            'version.etapes.*.position'    => ['required', 'integer', 'min:1'],
            'version.etapes.*.mode_code'   => ['required', 'string', 'in:SERIAL,PARALLEL'],
            'version.etapes.*.quorum'      => ['nullable', 'integer', 'min:1'],
            'version.etapes.*.sla_heures'  => ['nullable', 'integer', 'min:1'],
            'version.etapes.*.delegation_autorisee' => ['nullable', 'boolean'],
            'version.etapes.*.sauter_si_vide'       => ['nullable', 'boolean'],
            'version.etapes.*.politique_reapprobation' => ['nullable', 'array'],
            'version.etapes.*.metadonnees' => ['nullable', 'array'],

            'version.etapes.*.approbateurs' => ['nullable', 'array'],
            'version.etapes.*.approbateurs.*.type_approbateur' => ['required_with:version.etapes.*.approbateurs', 'string', 'in:ACTEUR,ROLE,GROUPE'],
            'version.etapes.*.approbateurs.*.reference_approbateur' => ['required_with:version.etapes.*.approbateurs', 'string', 'max:150'],
            'version.etapes.*.approbateurs.*.obligatoire' => ['nullable', 'boolean'],

            'version.etapes.*.regles' => ['nullable', 'array'],
            'version.etapes.*.regles.*.champ' => ['required_with:version.etapes.*.regles', 'string', 'max:100'],
            'version.etapes.*.regles.*.operateur_code' => ['required_with:version.etapes.*.regles', 'string', 'in:EQ,NE,GT,GTE,LT,LTE,IN,NOT_IN,BETWEEN'],
            'version.etapes.*.regles.*.valeur' => ['required_with:version.etapes.*.regles'], // JSON libre (string/json)
        ];

        return $request->validate($rules);
    }
    private function upsertVersionGraph(
        int $workflowId,
        array $versionData,
        string $mode = 'new_version' // 'update_existing' | 'new_version'
    ): VersionWorkflow {
        // Trouver la version cible si 'update_existing'
        $existing = null;
        if (($mode === 'update_existing') && !empty($versionData['numero_version'])) {
            $existing = VersionWorkflow::where('workflow_id', $workflowId)
                ->where('numero_version', $versionData['numero_version'])
                ->first();
        }
    
        if ($mode === 'update_existing' && $existing) {
            // Met à jour entête
            $existing->update([
                'politique_changement' => $versionData['politique_changement'] ?? $existing->politique_changement,
                'metadonnees'          => $versionData['metadonnees'] ?? $existing->metadonnees,
                'publie'               => (int)($versionData['publie'] ?? $existing->publie),
            ]);
    
            // Purge enfants (étapes -> approbateurs/règles)
            $stepIds = $existing->etapes()->pluck('id');
            EtapeApprobateur::whereIn('etape_workflow_id', $stepIds)->delete();
            EtapeRegle::whereIn('etape_workflow_id', $stepIds)->delete();
            $existing->etapes()->delete();
    
            // Recrée le graph
            $this->createEtapesGraph($existing, $versionData['etapes'] ?? []);
    
            return $existing->load('etapes.approbateurs','etapes.regles');
        }
    
        // Sinon, NEW VERSION (toujours une version libre)
        $nextVersion = VersionWorkflow::where('workflow_id', $workflowId)->max('numero_version');
        $numero = ($nextVersion ?? 0) + 1;
    
        $version = VersionWorkflow::create([
            'workflow_id'          => $workflowId,
            'numero_version'       => $numero,
            'politique_changement' => $versionData['politique_changement'] ?? 'REAPPROUVER_SUR_RISQUE',
            'metadonnees'          => $versionData['metadonnees'] ?? null,
            'publie'               => (int)($versionData['publie'] ?? 0),
        ]);
    
        $this->createEtapesGraph($version, $versionData['etapes'] ?? []);
    
        return $version->load('etapes.approbateurs','etapes.regles');
    }

    private function createEtapesGraph(VersionWorkflow $version, array $etapes): void
    {
        foreach ($etapes as $e) {
            $mode = ModeWorkflow::where('code', $e['mode_code'])->firstOrFail();

            $etape = EtapeWorkflow::create([
                'version_workflow_id'    => $version->id,
                'position'               => (int)$e['position'],
                'mode_id'                => $mode->id,
                'quorum'                 => (int)($e['quorum'] ?? 1),
                'sla_heures'             => $e['sla_heures'] ?? null,
                'delegation_autorisee'   => (int)($e['delegation_autorisee'] ?? 1),
                'sauter_si_vide'         => (int)($e['sauter_si_vide'] ?? 1),
                'politique_reapprobation'=> $e['politique_reapprobation'] ?? null,
                'metadonnees'            => $e['metadonnees'] ?? null,
            ]);

            foreach (($e['approbateurs'] ?? []) as $a) {
                EtapeApprobateur::create([
                    'etape_workflow_id'     => $etape->id,
                    'type_approbateur'      => $a['type_approbateur'],
                    'reference_approbateur' => $a['reference_approbateur'],
                    'obligatoire'           => (int)($a['obligatoire'] ?? 0),
                ]);
            }

            foreach (($e['regles'] ?? []) as $r) {
                $op = OperateurRegle::where('code', $r['operateur_code'])->firstOrFail();
                EtapeRegle::create([
                    'etape_workflow_id' => $etape->id,
                    'champ'             => $r['champ'],
                    'operateur_id'      => $op->id,
                    'valeur'            => $r['valeur'],
                ]);
            }
        }
    }

    private function createVersionGraph(int $workflowId, array $versionData): VersionWorkflow
    {
        $version = VersionWorkflow::create([
            'workflow_id'          => $workflowId,
            'numero_version'       => $versionData['numero_version'],
            'politique_changement' => $versionData['politique_changement'] ?? 'REAPPROUVER_SUR_RISQUE',
            'metadonnees'          => $versionData['metadonnees'] ?? null,
            'publie'               => (int)($versionData['publie'] ?? 0),
        ]);

        foreach ($versionData['etapes'] as $e) {
            $mode = ModeWorkflow::where('code', $e['mode_code'])->firstOrFail();
            $etape = EtapeWorkflow::create([
                'version_workflow_id'    => $version->id,
                'position'               => (int)$e['position'],
                'mode_id'                => $mode->id,
                'quorum'                 => (int)($e['quorum'] ?? 1),
                'sla_heures'             => $e['sla_heures'] ?? null,
                'delegation_autorisee'   => (int)($e['delegation_autorisee'] ?? 1),
                'sauter_si_vide'         => (int)($e['sauter_si_vide'] ?? 1),
                'politique_reapprobation'=> $e['politique_reapprobation'] ?? null,
                'metadonnees'            => $e['metadonnees'] ?? null,
            ]);

            foreach (($e['approbateurs'] ?? []) as $a) {
                EtapeApprobateur::create([
                    'etape_workflow_id'     => $etape->id,
                    'type_approbateur'      => $a['type_approbateur'],
                    'reference_approbateur' => $a['reference_approbateur'],
                    'obligatoire'           => (int)($a['obligatoire'] ?? 0),
                ]);
            }
            foreach (($e['regles'] ?? []) as $r) {
                $op = OperateurRegle::where('code', $r['operateur_code'])->firstOrFail();
                EtapeRegle::create([
                    'etape_workflow_id' => $etape->id,
                    'champ'             => $r['champ'],
                    'operateur_id'      => $op->id,
                    'valeur'            => $r['valeur'], // peut être string JSON ou array/json
                ]);
            }
        }

        return $version->load('etapes.approbateurs', 'etapes.regles');
    }

    private function resolveVersion(string $module, string $type, string $id): ?VersionWorkflow
    {
        // 1) Liaison spécifique à l’objet
        $liaison = LiaisonWorkflow::where([
            'module_code' => $module,
            'type_cible'  => $type,
            'id_cible'    => $id,
        ])->whereHas('version', fn($q) => $q->where('publie', 1))
          ->latest('id')->first();

        if ($liaison) return $liaison->version()->with('etapes.approbateurs', 'etapes.regles')->first();

        // 2) Liaison par défaut pour le type
        $liaison = LiaisonWorkflow::where([
            'module_code' => $module,
            'type_cible'  => $type,
            'id_cible'    => null,
            'par_defaut'  => 1
        ])->whereHas('version', fn($q) => $q->where('publie', 1))
          ->latest('id')->first();

        if ($liaison) return $liaison->version()->with('etapes.approbateurs', 'etapes.regles')->first();

        return null;
    }

    private function spawnStep(InstanceApprobation $inst, EtapeWorkflow $etape, int $statutId, int $quorum = 1): InstanceEtape
    {
        return InstanceEtape::create([
            'instance_approbation_id' => $inst->id,
            'etape_workflow_id'       => $etape->id,
            'statut_id'               => $statutId,
            'quorum_requis'           => $quorum,
            'nombre_approbations'     => 0,
            'date_debut'              => null,
            'date_fin'                => null,
        ]);
    }

    private function advance(InstanceApprobation $inst, InstanceEtape $justFinished = null): void
    {
        $steps = InstanceEtape::query()
            ->where('instance_approbation_id', $inst->id)
            ->with('etape') // etape->position
            ->get();

        // 1) clôture si toutes les étapes non SAUTE sont APPROUVE
        $allApproved = $steps->every(function ($s) {
            $code = $this->codeStatutEtape($s->statut_id);
            return in_array($code, ['APPROUVE', 'SAUTE']);
        });

        if ($allApproved) {
            $inst->update([
                'statut_id' => $this->statutInstance('APPROUVE')->id,
                'date_fin'  => now(),
            ]);
            return;
        }

        // 2) activer la prochaine PENDING selon la position
        $next = $steps
            ->filter(fn($s) => in_array($this->codeStatutEtape($s->statut_id), ['PENDING','EN_COURS']))
            ->sortBy(fn($s) => $s->etape->position)
            ->first();

        if ($next && $this->codeStatutEtape($next->statut_id) === 'PENDING') {
            $next->update([
                'statut_id'  => $this->statutEtape('EN_COURS')->id,
                'date_debut' => now(),
            ]);
            // 🔔 nouvelle étape active → notifier ses approbateurs
            $this->notifyActiveApprovers($inst);
        }
    }

    private function shouldSkip(EtapeWorkflow $etape, ?array $snapshot): bool
    {
        if (!$etape->sauter_si_vide) return false;
        $rules = $etape->regles()->get();
        if ($rules->isEmpty()) return false;

        // Si AUCUNE règle ne match -> on saute
        foreach ($rules as $rule) {
            if ($this->ruleMatches($rule, $snapshot)) {
                return false; // au moins une match -> on ne saute pas
            }
        }
        return true; // aucune règle ne match
    }

    private function ruleMatches(EtapeRegle $r, ?array $snapshot): bool
    {
        $field = $r->champ;
        $val   = data_get($snapshot, $field);
        $op    = OperateurRegle::find($r->operateur_id)?->code;

        // valeur en base : string JSON OU scalaire ; on tente un json_decode souple
        $exp = is_string($r->valeur) ? json_decode($r->valeur, true) ?? $r->valeur : $r->valeur;

        return match ($op) {
            'EQ'      => $val == $exp,
            'NE'      => $val != $exp,
            'GT'      => is_numeric($val) && $val > $exp,
            'GTE'     => is_numeric($val) && $val >= $exp,
            'LT'      => is_numeric($val) && $val < $exp,
            'LTE'     => is_numeric($val) && $val <= $exp,
            'IN'      => is_array($exp) && in_array($val, $exp, true),
            'NOT_IN'  => is_array($exp) && !in_array($val, $exp, true),
            'BETWEEN' => is_array($exp) && count($exp) === 2 && is_numeric($val) && $val >= $exp[0] && $val <= $exp[1],
            default   => false,
        };
    }

    private function actorCanActOnStep(?string $actorCode, InstanceEtape $si): bool
    {
        if (!$actorCode) return false;

        // Lister approbateurs attendus
        $approvers = $si->etape->approbateurs;
        foreach ($approvers as $a) {
            if ($a->type_approbateur === 'ACTEUR' && $a->reference_approbateur === $actorCode) {
                return true;
            }
            // ROLE/GROUPE: à implémenter selon tes tables
        }

        // Délégation (meta.delegate_to)
        $delegations = $si->actions()->whereHas('type', fn ($q) => $q->where('code', 'DELEGUER'))->get();
        foreach ($delegations as $del) {
            $to = data_get($del->meta, 'delegate_to');
            if ($to && $to === $actorCode) return true;
        }

        return false;
    }

    private function requiredApproversMet(InstanceEtape $si): bool
    {
        $required = $si->etape->approbateurs()->where('obligatoire', 1)->get();
        if ($required->isEmpty()) return true;

        foreach ($required as $req) {
            $ok = $si->actions()
                ->whereHas('type', fn ($q) => $q->where('code', 'APPROUVER'))
                ->where('code_acteur', $req->reference_approbateur)
                ->exists();
            if (!$ok) return false;
        }
        return true;
    }
    private function initialsFromActor(?\App\Models\Acteur $a): string
    {
        if (!$a) return '?';
    
        $short = trim((string) $a->libelle_court);
        $long  = trim((string) $a->libelle_long);
    
        // si les 2 existent : 1ère du court + 1ère du dernier mot du long
        if ($short !== '' && $long !== '') {
            $last = preg_split('/\s+/', $long);
            $li   = $last ? ($last[count($last)-1] ?: $long) : $long;
            $c1   = mb_substr($short, 0, 1) ?: substr($short, 0, 1);
            $c2   = mb_substr($li,    0, 1) ?: substr($li,    0, 1);
            return mb_strtoupper($c1.$c2);
        }
    
        // sinon : 2 lettres à partir du champ disponible
        $base = $short !== '' ? $short : $long;
        if ($base === '') return '?';
    
        $parts = preg_split('/\s+/', $base);
        if (count($parts) === 1) {
            $p = $parts[0];
            $c = (mb_substr($p, 0, 2) ?: substr($p, 0, 2));
            return mb_strtoupper($c);
        }
    
        $p1 = $parts[0];
        $p2 = $parts[count($parts)-1];
        $c1 = (mb_substr($p1, 0, 1) ?: substr($p1, 0, 1));
        $c2 = (mb_substr($p2, 0, 1) ?: substr($p2, 0, 1));
        return mb_strtoupper($c1.$c2);
    }
    
    
    private function labelFromActor(?\App\Models\Acteur $a, ?string $fallback = null): string
    {
        if ($a) {
            $pieces = array_filter([trim((string)$a->libelle_court), trim((string)$a->libelle_long)]);
            if (!empty($pieces)) return implode(' · ', $pieces);
        }
        return $fallback ?: '';
    }
    
    
    private function approverStatusFor(InstanceEtape $step, string $actorCode): string
    {
        // a-t-il approuvé ?
        $hasApproved = $step->actions()
            ->whereHas('type', fn($q)=>$q->where('code','APPROUVER'))
            ->where('code_acteur', $actorCode)
            ->exists();
        if ($hasApproved) return 'APPROUVE';
    
        // a-t-il rejeté ?
        $hasRejected = $step->actions()
            ->whereHas('type', fn($q)=>$q->where('code','REJETER'))
            ->where('code_acteur', $actorCode)
            ->exists();
        if ($hasRejected) return 'REJETE';
    
        // statut de l'étape
        $codeStep = $this->codeStatutEtape($step->statut_id);
        // EN_COURS = jaune (à faire), PENDING/SAUTE/APPROUVE/REJETE...
        if ($codeStep === 'EN_COURS') return 'EN_COURS';
        if ($codeStep === 'PENDING')  return 'PENDING';
    
        // étapes déjà passées & approuvées sans action de cet acteur → considéré "non requis" => gris
        return 'EN_ATTENTE';
    }
    
    /* -------- Raccourcis ref_* -------- */
    private function statutInstance(string $code): StatutInstance
    {
        return StatutInstance::where('code', $code)->firstOrFail();
    }

    private function statutEtape(string $code): StatutEtapeInstance
    {
        return StatutEtapeInstance::where('code', $code)->firstOrFail();
    }

    private function codeStatutEtape(int $id): ?string
    {
        return StatutEtapeInstance::find($id)?->code;
    }
}
