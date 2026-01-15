<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\NotificationValidationProjet;
use App\Mail\ProjetRefuseNotification;
use App\Services\WorkflowApproval;
use App\Services\ApprovalNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;


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
use App\Models\ModuleWorkflowDisponible;

class WorkflowValidationController extends Controller
{
    /***********************************************************
     *                    HELPERS ERREURS JSON (NOUVEAU)
     ***********************************************************/
    /**
     * Construit une 422 JSON avec toutes les erreurs concaténées dans `message`
     */
    private function throw422FromValidator(\Illuminate\Contracts\Validation\Validator $validator): never
    {
        $all = $validator->errors()->all(); // toutes les lignes d'erreur
        $message = implode("\n", array_map(fn($m) => "• ".$m, $all));

        throw new HttpResponseException(
            response()->json([
                'message' => $message,
                'errors'  => $validator->errors(), // on garde le détail pour debug éventuel
            ], 422)
        );
    }

    /**
     * Remplace $request->validate() par une version qui jette une 422 propre
     */
    private function validateJson(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = [],
        ?\Closure $after = null
    ): array {
        $validator = \Validator::make($request->all(), $rules, $messages, $attributes);
        if ($after) {
            $validator->after(fn($v) => $after($v));
        }
        if ($validator->fails()) {
            $this->throw422FromValidator($validator);
        }
        return $validator->validated();
    }

    /***********************************************************
     *                         VUES
     ***********************************************************/
    public function objectView(string $module, string $type, string $id)
    {
        // 1) Config module/type (table "modules_workflow_disponibles")
        $cfg = DB::table('modules_workflow_disponibles')
            ->where('code_module', $module)
            ->where('type_cible', $type)
            ->first();
    
        if (!$cfg) {
            return response()->json(['message' => "Configuration module/type introuvable : {$module} • {$type}"], 404);
           
        }
    
        $modelClass = $cfg->classe_modele ?? null;
        $idField    = $cfg->champ_identifiant ?? 'id';
        if (!$modelClass || !class_exists($modelClass)) {
            return response()->json(['message' => "Classe modèle non configurée pour {$module} • {$type}"], 404);
           
        }
    
        // 2) Chargement de l’enregistrement cible
        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $modelClass::query()->where($idField, $id)->first();
        if (!$record) {
            return response()->json(['message' => "Objet introuvable pour {$module} • {$type} avec {$idField}={$id}"], 404);
           
        }
    
        // 3) Résolution de la vue spécifique -> fallback
        $viewSpecific = "approbations.objects.{$module}.".strtolower($type);
        $viewFallback = "approbations.objects.{$module}.show";
    
        $view = view()->exists($viewSpecific) ? $viewSpecific
               : (view()->exists($viewFallback) ? $viewFallback : null);
            if (!$view) {
                return response()->json(['message' => "La vue de consultation n’est pas encore implémentée"], 404);
            }
            
    
        // 4) Contexte optionnel
        $ctx = [
            'pays'   => session('pays_selectionne'),
            'projet' => session('projet_selectionne'),
        ];
    
        return view($view, [
            'record'     => $record,
            'module'     => $module,
            'type'       => $type,
            'object_id'  => $id,
            'id_field'   => $idField,
            'ctx'        => $ctx,
        ]);
    }
    
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
        // Précharger référentiels pour éviter les N+1
        $modeById = \App\Models\ModeWorkflow::query()->pluck('code', 'id');
        $opById   = \App\Models\OperateurRegle::query()->pluck('code', 'id');

        $workflow = WorkflowApprobation::with([
            'versions' => function ($q) {
                $q->with(['etapes' => function ($q) {
                    $q->with(['approbateurs', 'regles.operateur']);
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
                'etapes'               => $last->etapes->map(function ($e) use ($modeById, $opById) {
                    return [
                        'position'                => $e->position,
                        'mode_code'               => $modeById[$e->mode_id] ?? null,
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
                        'regles'                  => $e->regles->map(function ($r) use ($opById) {
                            return [
                                'champ'          => $r->champ,
                                'operateur_code' => optional($r->operateur)->code ?? ($opById[$r->operateur_id] ?? null),
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
            'publishedVersions' => $wf->versions,
            'bindings'          => $bindings,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $actorCode = optional($user?->acteur)->code_acteur;
        $activeTab = $request->get('tab', 'current'); // 'current' ou 'history'

        if (!$actorCode) {
            return view('approbations.dashboard', [
                'currentRows'    => collect(),
                'historyRows'    => collect(),
                'Users'          => collect(),
                'pendingCount'   => 0,
                'approvedCount'  => 0,
                'rejectedCount'  => 0,
                'activeTab'      => $activeTab,
            ]);
        }

        $stEnCours = $this->statutEtape('EN_COURS')->id;
        $stPending = $this->statutEtape('PENDING')->id;
        $stAppr    = $this->statutEtape('APPROUVE')->id;
        $stRejet   = $this->statutEtape('REJETE')->id;

        // APPROBATIONS ACTUELLES (en cours et en attente)
        $currentQ = \App\Models\InstanceEtape::query()
            ->where(function ($q) use ($actorCode) {
                $q->whereHas('etape.approbateurs', function ($q) use ($actorCode) {
                    $q->where('type_approbateur', 'ACTEUR')
                      ->where('reference_approbateur', $actorCode);
                })
                ->orWhereHas('actions', function ($qq) use ($actorCode) {
                    $qq->whereHas('type', fn($t) => $t->where('code', 'DELEGUER'))
                       ->where('meta->delegate_to', $actorCode);
                });
            })
            ->whereIn('statut_id', [$stEnCours, $stPending]);

        // Comptes pour les approbations actuelles
        $pendingCount  = (clone $currentQ)->count();
        $approvedCount = \App\Models\InstanceEtape::where(function ($q) use ($actorCode) {
                $q->whereHas('etape.approbateurs', fn($qq)=>$qq->where('type_approbateur','ACTEUR')->where('reference_approbateur',$actorCode))
                ->orWhereHas('actions', fn($qq)=>$qq->whereHas('type', fn($t)=>$t->where('code','DELEGUER'))->where('meta->delegate_to',$actorCode));
            })
            ->where('statut_id', $stAppr)
            ->count();
        
        $rejectedCount = \App\Models\InstanceEtape::where(function ($q) use ($actorCode) {
                $q->whereHas('etape.approbateurs', fn($qq)=>$qq->where('type_approbateur','ACTEUR')->where('reference_approbateur',$actorCode))
                ->orWhereHas('actions', fn($qq)=>$qq->whereHas('type', fn($t)=>$t->where('code','DELEGUER'))->where('meta->delegate_to',$actorCode));
            })
            ->where('statut_id', $stRejet)
            ->count();
    

        // Liste paginée des approbations actuelles
        $currentSteps = (clone $currentQ)
            ->with([
                'instance.version.workflow',
                'instance',
                'etape.approbateurs',
                'actions.type',
            ])
            ->orderByDesc('id')
            ->paginate(25, ['*'], 'current_page')
            ->withQueryString();

        // HISTORIQUE (déjà traitées)
        $historyQ = \App\Models\InstanceEtape::query()
            ->where(function ($q) use ($actorCode) {
                $q->whereHas('etape.approbateurs', function ($q) use ($actorCode) {
                    $q->where('type_approbateur', 'ACTEUR')
                      ->where('reference_approbateur', $actorCode);
                })
                ->orWhereHas('actions', function ($qq) use ($actorCode) {
                    $qq->whereHas('type', fn($t) => $t->where('code', 'DELEGUER'))
                       ->where('meta->delegate_to', $actorCode);
                });
            })
            ->whereIn('statut_id', [$stAppr, $stRejet]);

        // Liste paginée de l'historique
        $historySteps = $historyQ
            ->with([
                'instance.version.workflow',
                'instance',
                'etape.approbateurs',
                'actions.type',
            ])
            ->orderByDesc('id')
            ->paginate(25, ['*'], 'history_page')
            ->withQueryString();

        // Mapping des données pour l'affichage
        $currentRows = $currentSteps->getCollection()->map(fn($si) => $this->mapStepToRow($si));
        $historyRows = $historySteps->getCollection()->map(fn($si) => $this->mapStepToRow($si));

        // Remettre les collections mappées dans les paginators
        $currentSteps->setCollection($currentRows);
        $historySteps->setCollection($historyRows);

        // Sélecteur délégation
        $Users = \App\Models\User::where('is_active', true)
            ->whereHas('acteur')
            ->select('acteur_id')
            ->with(['acteur:code_acteur,libelle_court,libelle_long'])
            ->orderBy('acteur_id')
            ->get();

        return view('approbations.dashboard', [
            'currentRows'    => $currentSteps,
            'historyRows'    => $historySteps,
            'Users'          => $Users,
            'pendingCount'   => $pendingCount,
            'approvedCount'  => $approvedCount,
            'rejectedCount'  => $rejectedCount,
            'activeTab'      => $activeTab,
        ]);
    }

    
    /**
     * Helper pour mapper InstanceEtape vers format d'affichage
     */
    private function mapStepToRow(\App\Models\InstanceEtape $si): array
    {
        $inst = $si->instance;
        $wf   = optional($inst->version)->workflow;

        // approbateurs attendus (codes)
        $expApprovers = $si->etape->approbateurs
            ->whereIn('type_approbateur', ['ACTEUR','FIELD_ACTEUR'])
            ->map(function ($a) use ($inst) {
                if ($a->type_approbateur === 'ACTEUR') {
                    return (string) $a->reference_approbateur;
                }
                $snap = (array) ($inst->instantane ?? []);
                $code = (string) data_get($snap, $a->reference_approbateur);
                return $code ?: null;
            })
            ->filter()
            ->values();

        $acteurs = \App\Models\Acteur::whereIn('code_acteur', $expApprovers)
            ->get(['code_acteur','libelle_court','libelle_long'])
            ->keyBy('code_acteur');

        $approvers = $expApprovers->map(function ($code) use ($acteurs, $si) {
            $a = $acteurs->get($code);
            $label    = $this->labelFromActor($a, $code);
            $initials = $this->initialsFromActor($a);
            return [
                'code'      => $code,
                'label'     => $label,
                'initials'  => $initials ?: '?',
                'status'    => $this->approverStatusFor($si, $code),
            ];
        });

        return [
            'step_id'        => $si->id,
            'instance_id'    => $inst->id,
            'module'         => $inst->module_code,
            'type'           => $inst->type_cible,
            'target_id'      => $inst->id_cible,
            'workflow_name'  => $wf?->nom ?? 'N/A',
            'version'        => $inst->version?->numero_version ?? null,
            'step_pos'       => $si->etape->position,
            'status_code'    => $this->codeStatutEtape($si->statut_id),
            'approvers'      => $approvers,
            'created_at'     => $si->date_debut ?: $si->created_at,
            'updated_at'     => $si->updated_at,
        ];
    }

    public function HistoriqueApprobation(Request $request)
    {
        // --------- Filtres ----------
        $module      = trim((string) $request->get('module'));
        $type        = trim((string) $request->get('type'));
        $cible       = trim((string) $request->get('id_cible'));
        $actor       = trim((string) $request->get('actor'));
        $actionCode  = trim((string) $request->get('action_code'));
        $instStatus  = trim((string) $request->get('instance_status'));
        $from        = $request->get('from'); // yyyy-mm-dd
        $to          = $request->get('to');   // yyyy-mm-dd
        $mine        = (bool) $request->boolean('mine');

        // Contexte optionnel (pays / projet)
        $ctxPays   = session('pays_selectionne');
        $ctxProjet = session('projet_selectionne');

        // --------- Tables ----------
        $tA  = app(ActionApprobation::class)->getTable();
        $tSI = app(InstanceEtape::class)->getTable();
        $tIA = app(InstanceApprobation::class)->getTable();
        $tTA = app(TypeAction::class)->getTable();
        $tVW = app(VersionWorkflow::class)->getTable();
        $tWA = app(WorkflowApprobation::class)->getTable();
        $tSTI= app(StatutInstance::class)->getTable();
        $tSTE= app(StatutEtapeInstance::class)->getTable();
        $tAC = app(Acteur::class)->getTable();

        // --------- Base query ----------
        $q = DB::table("$tA as a")
            ->join("$tSI as si", 'si.id', '=', 'a.instance_etape_id')
            ->join("$tIA as ia", 'ia.id', '=', 'si.instance_approbation_id')
            ->leftJoin("$tTA as ta", 'ta.id', '=', 'a.action_type_id')
            ->leftJoin("$tVW as vw", 'vw.id', '=', 'ia.version_workflow_id')
            ->leftJoin("$tWA as wa", 'wa.id', '=', 'vw.workflow_id')
            ->leftJoin("$tSTI as sti", 'sti.id', '=', 'ia.statut_id')
            ->leftJoin("$tSTE as ste", 'ste.id', '=', 'si.statut_id')
            ->leftJoin("$tAC as ac", 'ac.code_acteur', '=', 'a.code_acteur')
            ->selectRaw("
                a.id               as action_id,
                a.created_at       as action_at,
                a.commentaire      as action_comment,
                a.code_acteur      as actor_code,
                ta.code            as action_code,

                si.id              as step_id,
                ste.code           as step_status,

                ia.id              as instance_id,
                ia.module_code     as module_code,
                ia.type_cible      as type_cible,
                ia.id_cible        as id_cible,
                sti.code           as instance_status,

                vw.numero_version  as version,
                wa.nom             as workflow_nom,
                wa.code_pays       as code_pays,
                wa.groupe_projet_id as groupe_projet,

                ac.libelle_court   as actor_short,
                ac.libelle_long    as actor_long
            ");

        // --------- Filtres ----------
        if ($module !== '')    $q->where('ia.module_code', $module);
        if ($type !== '')      $q->where('ia.type_cible',  $type);
        if ($cible !== '')     $q->where('ia.id_cible',    $cible);
        if ($actionCode !== '')$q->where('ta.code',        $actionCode);
        if ($instStatus !== '')$q->where('sti.code',       $instStatus);

        if ($from) $q->where('a.created_at', '>=', Carbon::parse($from)->startOfDay());
        if ($to)   $q->where('a.created_at', '<=', Carbon::parse($to)->endOfDay());

        if ($mine && ($me = optional(auth()->user()?->acteur)->code_acteur)) {
            $q->where('a.code_acteur', $me);
        }

        if ($actor !== '') {
            $q->where(function ($w) use ($actor) {
                $w->where('a.code_acteur', 'like', "%$actor%")
                  ->orWhere('ac.libelle_court', 'like', "%$actor%")
                  ->orWhere('ac.libelle_long',  'like', "%$actor%");
            });
        }

        if ($ctxPays)   $q->where('wa.code_pays', $ctxPays);
        if ($ctxProjet) $q->where('wa.groupe_projet_id', $ctxProjet);

        $q->orderByDesc('a.id');
        $rows = $q->paginate(50)->withQueryString();

        $actionCodes = TypeAction::query()->orderBy('code')->pluck('code')->all();
        $instanceStatuses = StatutInstance::query()->orderBy('code')->pluck('code')->all();

        return view('approbations.history', [
            'rows'              => $rows,
            'filters'           => [
                'module' => $module, 'type' => $type, 'id_cible' => $cible,
                'actor'  => $actor,  'action_code' => $actionCode, 'instance_status' => $instStatus,
                'from'   => $from,   'to' => $to, 'mine' => $mine,
            ],
            'actionCodes'       => $actionCodes,
            'instanceStatuses'  => $instanceStatuses,
            'ctx'               => [
                'pays'   => $ctxPays,
                'projet' => $ctxProjet,
            ],
        ]);
    }

    /** Vue détail d’une instance + actions -> resources/views/approbations/show.blade.php */
    public function instanceView($instanceId)
    {
        // $this->authorize('approval.view', $instance);
        $ctx = $this->buildViewContext();
        return view('approbations.show', ['instanceId' => (int)$instanceId, 'ctx' => $ctx]);
    }

    /**
     * POST /workflows/{workflowId}/bind-dynamic
     * Upsert binding (module_type_id + scope).
     */
    public function bindDynamic(Request $request, $workflowId)
    {
        // $this->authorize('workflow.admin');
        $data = $this->validateJson($request, [
            'module_type_id'   => ['required','exists:modules_workflow_disponibles,id'],
            'numero_version'   => ['required','integer','min:1'],
            'id_cible'         => ['nullable','string','max:150'],
            'par_defaut'       => ['nullable','boolean'],
            'code_pays'        => ['nullable','string','size:3'],
            'groupe_projet_id' => ['nullable','string'],
        ]);

        $moduleType = ModuleWorkflowDisponible::findOrFail($data['module_type_id']);
        $wf = WorkflowApprobation::findOrFail($workflowId);
        $version = VersionWorkflow::where('workflow_id', $wf->id)
            ->where('numero_version', $data['numero_version'])
            ->where('publie', 1)
            ->firstOrFail();

        return DB::transaction(function () use ($data, $moduleType, $version) {
            if (!empty($data['par_defaut'])) {
                LiaisonWorkflow::where('module_code', $moduleType->code_module)
                    ->where('type_cible', $moduleType->type_cible)
                    ->where(function ($q) use ($data) {
                        $q->whereNull('code_pays')->orWhere('code_pays', $data['code_pays'] ?? null);
                    })
                    ->where(function ($q) use ($data) {
                        $q->whereNull('groupe_projet_id')->orWhere('groupe_projet_id', $data['groupe_projet_id'] ?? null);
                    })
                    ->update(['par_defaut' => 0]);
            }

            $exists = LiaisonWorkflow::where('module_code', $moduleType->code_module)
                ->where('type_cible', $moduleType->type_cible)
                ->where('id_cible', $data['id_cible'] ?? null)
                ->where('code_pays', $data['code_pays'] ?? null)
                ->where('groupe_projet_id', $data['groupe_projet_id'] ?? null)
                ->first();

            if ($exists) {
                $exists->update([
                    'version_workflow_id' => $version->id,
                    'par_defaut'          => (int)($data['par_defaut'] ?? 0),
                    'module_type_id'      => $moduleType->id,
                ]);
                return response()->json(['message' => 'Liaison mise à jour', 'liaison' => $exists], 200);
            }

            $binding = LiaisonWorkflow::create([
                'version_workflow_id' => $version->id,
                'module_code'         => $moduleType->code_module,
                'type_cible'          => $moduleType->type_cible,
                'id_cible'            => $data['id_cible'] ?? null,
                'par_defaut'          => (int)($data['par_defaut'] ?? 0),
                'module_type_id'      => $moduleType->id,
                'code_pays'           => $data['code_pays'] ?? null,
                'groupe_projet_id'    => $data['groupe_projet_id'] ?? null,
            ]);

            return response()->json(['message' => 'Liaison créée', 'liaison' => $binding->load('moduleType')], 201);
        });
    }

    /**
     * DELETE /workflows/{workflow}/bindings/{binding}
     */
    public function destroyBinding(Request $request, $workflowId, $bindingId)
    {
        // $this->authorize('workflow.admin');
        $wf = WorkflowApprobation::findOrFail($workflowId);
        $b = LiaisonWorkflow::where('id', $bindingId)
            ->whereIn('version_workflow_id', VersionWorkflow::where('workflow_id', $wf->id)->pluck('id'))
            ->firstOrFail();

        $b->delete();
        return response()->json(['message' => 'Liaison supprimée']);
    }

    /**
     * GET /workflow/model-candidates
     * Scan app/Models for classes (non abstract, subclass of Model).
     */
    public function modelCandidates(Request $request)
    {
        // $this->authorize('workflow.admin');
        $cacheKey = 'workflow.modelCandidates';
        $ttl = config('workflow.models_cache_ttl', 60);

        $candidates = Cache::remember($cacheKey, $ttl, function () {
            $basePath = app_path('Models');
            if (!is_dir($basePath)) return collect();
            $files = \Illuminate\Support\Facades\File::allFiles($basePath);

            $list = collect($files)
                ->map(function ($f) use ($basePath) {
                    $relative = str_replace([$basePath . DIRECTORY_SEPARATOR, '.php'], '', $f->getPathname());
                    $class = 'App\\Models\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
                    return $class;
                })
                ->unique()
                ->filter(function ($class) {
                    if (!class_exists($class)) return false;
                    $ref = new \ReflectionClass($class);
                    return !$ref->isAbstract() && $ref->isSubclassOf(Model::class);
                })
                ->map(function ($class) {
                    return [
                        'class' => $class,
                        'short' => class_basename($class),
                        'label' => class_basename($class),
                    ];
                })->values();

            // whitelist optionnelle
            if ($allow = config('workflow.allowed_models', null)) {
                if (is_array($allow) && count($allow) > 0) {
                    $list = $list->filter(fn($c) => in_array($c['class'], $allow, true))->values();
                }
            }

            return $list;
        });

        return response()->json($candidates);
    }

    /**
     * GET /workflow/model-fields?class=App\Models\...
     * Retourne colonnes & un petit échantillon.
     */
    public function modelFields(Request $request)
    {
        // Normaliser le FQCN : enlever les "\" de tête et trim
        $class = ltrim((string) $request->query('class', ''), '\\');
        if ($class === '' || !class_exists($class)) {
            return response()->json(['error' => "Modèle introuvable: {$class}"], 404);
        }
    
        $ref = new \ReflectionClass($class);
        if ($ref->isAbstract() || !$ref->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
            return response()->json(['error' => "Classe non valide (doit étendre Eloquent Model): {$class}"], 400);
        }
    
        /** @var \Illuminate\Database\Eloquent\Model $m */
        $m = app($class);
    
        // Utiliser la connexion du modèle (et pas la connexion globale)
        $table       = $m->getTable();
        $connection  = $m->getConnectionName(); // peut être null => default
        $schema      = \Illuminate\Support\Facades\Schema::connection($connection);
    
        if (!$schema->hasTable($table)) {
            return response()->json(['error' => "Table '{$table}' introuvable sur la connexion '".($connection ?: config('database.default'))."'"], 400);
        }
    
        // Colonnes via la connexion du modèle
        $columns = $schema->getColumnListing($table);
    
        // Tri "intelligent" pour proposer d’abord les champs utiles
        $priorities = ['id','code','slug','reference','libelle','titre','title','name'];
        $sorted = collect($columns)
            ->map(fn($c) => ['column' => $c])
            ->sortBy(function ($item) use ($priorities) {
                $col = $item['column'];
                foreach ($priorities as $i => $p) {
                    if (str_starts_with($col, $p) || str_contains($col, $p)) return $i - 100;
                }
                return 1000;
            })
            ->values();
    
        $fillable = method_exists($m, 'getFillable') ? array_values($m->getFillable()) : [];
        $guarded  = method_exists($m, 'getGuarded')  ? array_values($m->getGuarded())  : [];
    
        // Petit échantillon — sur la bonne connexion
        $sample = [];
        try {
            $sampleRows = $m->newQuery()->limit(6)->get(array_slice($columns, 0, 10));
            $sample = $sampleRows->map(function ($r) use ($columns) {
                $out = [];
                foreach (array_slice($columns, 0, 10) as $c) {
                    $out[$c] = (string) data_get($r, $c);
                }
                return $out;
            });
        } catch (\Throwable $e) {
            // On renvoie quand même sans sample, mais on expose l’info utile
            return response()->json([
                'table'    => $table,
                'columns'  => $sorted,
                'fillable' => $fillable,
                'guarded'  => $guarded,
                'sample'   => [],
                'warning'  => 'Impossible de lire un échantillon: '.$e->getMessage(),
            ], 200);
        }
    
        return response()->json([
            'table'    => $table,
            'columns'  => $sorted,
            'fillable' => $fillable,
            'guarded'  => $guarded,
            'sample'   => $sample,
        ]);
    }
    

    /**
     * GET /modules-workflow/{id}/instances
     * Retourne instances du modèle (autocomplete)
     */
    public function moduleInstances(Request $request, $id)
    {
        $m = ModuleWorkflowDisponible::findOrFail($id);

        if (empty($m->classe_modele) || !class_exists($m->classe_modele)) {
            return response()->json([], 200);
        }

        $filters = [
            'pays_code' => $request->string('pays_code', null),
            'groupe_projet_id' => $request->string('groupe_projet_id', null),
            'q' => $request->string('q', null),
            'limit' => $request->integer('limit', 50),
        ];

        $instances = $m->getInstances($filters);

        // pagination/limite
        $instances = $instances->slice(0, min(100, max(10, $filters['limit'])))->values();

        return response()->json($instances);
    }

    /***********************************************************
     *      CONTEXTE PAYS / GROUPE PROJET POUR LES VUES
     ***********************************************************/
    private function buildViewContext(): array
    {
        $user = auth()->user();

        // 1) Pays sélectionné
        $alpha3Selected = session('pays_selectionne');
        $userPaysCodes  = collect();

        if ($user) {
            $userPaysCodes = GroupeProjetPaysUser::query()
                ->where('user_id', $user->id)
                ->pluck('pays_code')
                ->unique()
                ->values();
        }

        if (!$alpha3Selected) {
            $alpha3Selected = $userPaysCodes->first();
        }

        // 2) Liste des pays accessibles
        $paysOptionsQuery = Pays::query()->orderBy('nom_fr_fr');
        if ($userPaysCodes->isNotEmpty()) {
            $paysOptionsQuery->whereIn('alpha3', $userPaysCodes);
        }
        $paysOptions = $paysOptionsQuery->get(['alpha3','nom_fr_fr']);

        // 3) Groupe projet sélectionné
        $gpSelected = session('projet_selectionne');
        $groupesDansPays = collect();

        if ($user && $alpha3Selected) {
            $gppus = GroupeProjetPaysUser::query()
                ->where('user_id', $user->id)
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

        // 4) Liste des groupes accessibles
        $groupeOptions = $groupesDansPays->isNotEmpty()
            ? $groupesDansPays->map(fn($g)=>['code'=>$g->code,'libelle'=>$g->libelle])->values()
            : GroupeProjet::query()->orderBy('libelle')->get(['code','libelle'])->map(fn($g)=>['code'=>$g->code,'libelle'=>$g->libelle]);

        return [
            'pays_selected'    => $alpha3Selected,
            'projet_selected'  => $gpSelected,
            'pays_options'     => $paysOptions,
            'groupe_options'   => $groupeOptions,
        ];
    }

    private function getApproverUsers(?string $alpha3 = null, ?string $gpCode = null)
    {
        $alpha3  = $alpha3 ?: (string) session('pays_selectionne');
        $gpCode  = $gpCode ?: (string) session('projet_selectionne');

        $q = User::query()
            ->where('is_active', true)
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

        return $q->get(['id','acteur_id','login','email']);
    }

    /**
     * GET /modules-workflow
     * Retourne modules disponibles (registres) pour le select
     */
    public function modulesDisponibles(Request $request)
    {
        // $this->authorize('workflow.admin');
        $cacheKey = 'workflow.modules:' . md5(json_encode($request->only(['module','type','only_with_model','q'])));
        $useCache = !$request->boolean('nocache');

        $query = ModuleWorkflowDisponible::query()->where('actif', true);

        if ($request->filled('module')) $query->where('code_module', $request->string('module'));
        if ($request->filled('type')) $query->where('type_cible', $request->string('type'));
        if ($request->boolean('only_with_model')) $query->whereNotNull('classe_modele')->where('classe_modele', '!=', '');
        if ($request->filled('q')) {
            $term = '%' . trim($request->string('q')) . '%';
            $query->where(function($w) use ($term) {
                $w->where('code_module', 'like', $term)
                  ->orWhere('libelle_module', 'like', $term)
                  ->orWhere('type_cible', 'like', $term)
                  ->orWhere('libelle_type', 'like', $term);
            });
        }
        $query->orderBy('code_module')->orderBy('type_cible');

        if ($useCache) {
            $result = Cache::remember($cacheKey, config('workflow.models_cache_ttl', 60), fn() => $query->get());
        } else {
            $result = $query->get();
        }

        return response()->json($result);
    }

    /** Hydrate code_pays / groupe_projet_id depuis la session si absents */
    private function hydrateContextFromSession(Request $request): void
    {
        if (!$request->filled('code_pays') && ($alpha3 = session('pays_selectionne'))) {
            $request->merge(['code_pays' => $alpha3]);
        }
        // NOTE : on NE force PAS groupe_projet_id (volontairement)
    }

    /** Helper pour construire la query (réutilisable) */
    private function buildModulesQuery(Request $request)
    {
        $q = ModuleWorkflowDisponible::query()->where('actif', true);

        if ($request->filled('module')) {
            $q->where('code_module', $request->string('module'));
        }

        if ($request->filled('type')) {
            $q->where('type_cible', $request->string('type'));
        }

        if ($request->boolean('only_with_model')) {
            $q->whereNotNull('classe_modele')->where('classe_modele', '!=', '');
        }

        if ($request->filled('q')) {
            $term = '%' . trim($request->string('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('code_module', 'like', $term)
                    ->orWhere('libelle_module', 'like', $term)
                    ->orWhere('type_cible', 'like', $term)
                    ->orWhere('libelle_type', 'like', $term);
            });
        }

        $q->orderBy('code_module')->orderBy('type_cible');

        return $q;
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

        if ($request->filled('pays')) {
            $q->where('code_pays', $request->string('pays'));
        } else {
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
                    $q->with(['approbateurs', 'regles.operateur']);
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
                'groupe_projet_id' => array_key_exists('groupe_projet_id',$data) ? ($data['groupe_projet_id'] ?? null) : $workflow->groupe_projet_id,
                'meta'             => $data['meta'] ?? $workflow->meta,
            ]);

            /*if ($mode === 'update_existing' && !empty($data['version']['numero_version'])) {
                $existing = VersionWorkflow::where('workflow_id', $workflow->id)
                    ->where('numero_version', $data['version']['numero_version'])
                    ->first();

                if ($existing && ($existing->publie || $this->versionIsInUse($existing))) {
                    abort(response()->json([
                        'message' => "Impossible de modifier une version publiée ou déjà utilisée. Créez une nouvelle version.",
                        'code'    => 'VERSION_IN_USE'
                    ], 409));
                }
            }*/

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
        $inp = $this->validateJson($request, [
            'numero_version' => ['required','integer','min:1']
        ]);

        $workflow = WorkflowApprobation::findOrFail($id);

        DB::transaction(function() use ($workflow, $inp) {
            VersionWorkflow::where('workflow_id', $workflow->id)->update(['publie' => 0]);
            VersionWorkflow::where('workflow_id', $workflow->id)
                ->where('numero_version', $inp['numero_version'])
                ->update(['publie' => 1]);
        });

        $version = VersionWorkflow::where('workflow_id', $workflow->id)
            ->where('numero_version', $inp['numero_version'])
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
        $data = $this->validateJson($request, [
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
                'code_pays'           => session(('pays_selectionne')),
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
    /** Démarrer une instance d’approbation pour un objet. (via Service) */
    public function start(Request $request, WorkflowApproval $svc)
    {
        // $this->authorize('approval.start', [$module,$type,$id]);
        $data = $this->validateJson($request, [
            'module_code' => ['required', 'string', 'max:100'],
            'type_cible'  => ['required', 'string', 'max:100'],
            'id_cible'    => ['required', 'string', 'max:150'],
            'instantane'  => ['nullable', 'array'],
        ]);

        try {
            $res = $svc->start(
                $data['module_code'],
                $data['type_cible'],
                $data['id_cible'],
                $data['instantane'] ?? []
            );
        } catch (\DomainException $e) {
            throw new HttpResponseException(
                response()->json(['message' => $e->getMessage()], 422)
            );
        }

        return response()->json([
            'message'  => $res['created'] ? 'Instance démarrée' : 'Instance déjà active',
            'created'  => $res['created'],
            'instance' => $res['instance']->load('etapes'),
        ], $res['created'] ? 201 : 200);
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
    public function act(\App\Http\Requests\WorkflowActionRequest $request, int $stepInstanceId, ApprovalNotifier $notifier)
    {
        // Validation effectuée par FormRequest
        $data = $request->validated();
    
        // Type d'action (avec cache pour performance)
        $type = Cache::remember(
            "type_action_{$data['action_code']}",
            now()->addHours(24),
            fn() => TypeAction::where('code', $data['action_code'])->first()
        );
        
        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Type d\'action invalide',
            ], 422);
        }
    
        try {
            return DB::transaction(function () use ($stepInstanceId, $type, $data, $notifier) {
    
                /** @var InstanceEtape $si */
                $si = InstanceEtape::with(['instance', 'etape', 'actions'])
                    ->lockForUpdate()
                    ->findOrFail($stepInstanceId);
    
                $inst = $si->instance;
    
                // Étape encore active ?
                if (!in_array($si->statut_id, [
                    $this->statutEtape('EN_COURS')->id,
                    $this->statutEtape('PENDING')->id,
                ], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Étape non active',
                    ], 409);
                }
    
                // Autorisation "fonctionnelle" (approbateur autorisé)
                $actorCode = optional(auth()->user()?->acteur)->code_acteur;
                if (!$this->actorCanActOnStep($actorCode, $si)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acteur non autorisé pour cette étape',
                    ], 403);
                }
    
                // Empêcher décisions multiples par le même acteur (optimisé avec cache)
                $decisionTypeIds = Cache::remember(
                    'type_action_decision_ids',
                    now()->addHours(24),
                    fn() => TypeAction::whereIn('code', ['APPROUVER', 'REJETER'])->pluck('id')->toArray()
                );
                
                $alreadyDecided = $si->actions()
                    ->whereIn('action_type_id', $decisionTypeIds)
                    ->where('code_acteur', $actorCode)
                    ->exists();
                
                if ($alreadyDecided && in_array($type->code, ['APPROUVER','REJETER'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous avez déjà pris une décision sur cette étape.',
                    ], 409);
                }
    
                // Enregistrement de l’action
                ActionApprobation::create([
                    'instance_etape_id' => $si->id,
                    'code_acteur'       => $actorCode,
                    'action_type_id'    => $type->id,
                    'commentaire'       => $data['commentaire'] ?? null,
                    'meta'              => $data['meta'] ?? null,
                ]);
    
                // Effets métier selon l’action
                if ($type->code === 'APPROUVER') {
                    $si->increment('nombre_approbations');
    
                    if (
                        $si->nombre_approbations >= $si->quorum_requis
                        && $this->requiredApproversMet($si)
                    ) {
                        // Étape approuvée
                        $si->update([
                            'statut_id' => $this->statutEtape('APPROUVE')->id,
                            'date_fin'  => now(),
                        ]);
                        // Avancer le workflow
                        $this->advance($inst, $si, $notifier);
                    } else {
                        // Démarre l’étape si elle était encore PENDING
                        if ((int)$si->statut_id === (int)$this->statutEtape('PENDING')->id) {
                            $si->update([
                                'statut_id'  => $this->statutEtape('EN_COURS')->id,
                                'date_debut' => now(),
                            ]);
                        }
                    }
    
                } elseif ($type->code === 'REJETER') {
                    // Étape rejetée + instance rejetée
                    $si->update([
                        'statut_id' => $this->statutEtape('REJETE')->id,
                        'date_fin'  => now(),
                    ]);
                    $inst->update(['statut_id' => $this->statutInstance('REJETE')->id]);
    
                    // Notifier le porteur
                    app(ApprovalNotifier::class)->notifyOwnerOnRejection($inst, $data['commentaire'] ?? '');
    
                    return response()->json([
                        'success' => true,
                        'message' => 'Action enregistrée',
                        'etape'   => $si->fresh('actions'),
                    ]);
    
                } elseif ($type->code === 'DELEGUER') {
                    $to = data_get($data, 'meta.delegate_to');
                    if (!$to) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Le code acteur destinataire est requis pour la délégation.',
                        ], 422);
                    }
                    
                    // Vérifier que l'acteur destinataire existe et est actif
                    $destActor = Acteur::where('code_acteur', $to)->first();
                    if (!$destActor) {
                        return response()->json([
                            'success' => false,
                            'message' => 'L\'acteur destinataire n\'existe pas.',
                        ], 422);
                    }
                    
                    $notifier->notifyOnDelegation($si, $to);
                    
                    // Démarre l'étape si elle était PENDING
                    if ((int)$si->statut_id === (int)$this->statutEtape('PENDING')->id) {
                        $si->update([
                            'statut_id'  => $this->statutEtape('EN_COURS')->id,
                            'date_debut' => now(),
                        ]);
                    }
    
                } else { // COMMENTER
                    // Démarre l’étape si elle était PENDING
                    if ((int)$si->statut_id === (int)$this->statutEtape('PENDING')->id) {
                        $si->update([
                            'statut_id'  => $this->statutEtape('EN_COURS')->id,
                            'date_debut' => now(),
                        ]);
                    }
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Action enregistrée',
                    'etape'   => $si->fresh('actions'),
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Étape introuvable',
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('Approval act error', [
                'message' => $e->getMessage(),
                'step_instance_id' => $stepInstanceId,
                'user_id' => auth()->id(),
                'action_code' => $data['action_code'] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => config('app.debug') 
                    ? 'Erreur serveur: ' . $e->getMessage() 
                    : 'Une erreur est survenue lors du traitement de votre demande. Veuillez réessayer.',
            ], 500);
        }
    }

    /***********************************************************
     *                    SIMULATION & SLA (JSON)
     ***********************************************************/
    /**
     * POST /workflow/modules
     * Créer / mettre à jour ModuleWorkflowDisponible (minimale).
     */
    public function storeModule(Request $request)
    {
        // $this->authorize('workflow.admin');
        $data = $this->validateJson($request, [
            'id'               => ['nullable','integer','exists:modules_workflow_disponibles,id'],
            'code_module'      => ['required','string','max:100'],
            'libelle_module'   => ['nullable','string','max:200'],
            'type_cible'       => ['required','string','max:100'],
            'libelle_type'     => ['nullable','string','max:200'],
            'classe_modele'    => ['nullable','string'],
            'champ_identifiant'=> ['nullable','string','max:100'],
            'actif'            => ['nullable','boolean'],
        ]);

        if (!empty($data['classe_modele']) && !class_exists($data['classe_modele'])) {
            throw new HttpResponseException(
                response()->json(['message' => "Classe modèle introuvable: {$data['classe_modele']}"], 422)
            );
        }

        $payload = [
            'code_module'       => $data['code_module'],
            'libelle_module'    => $data['libelle_module'] ?? null,
            'type_cible'        => $data['type_cible'],
            'libelle_type'      => $data['libelle_type'] ?? null,
            'classe_modele'     => $data['classe_modele'] ?? null,
            'champ_identifiant' => $data['champ_identifiant'] ?? null,
            'actif'             => (int)($data['actif'] ?? 1),
        ];

        if (!empty($data['id'])) {
            $m = ModuleWorkflowDisponible::findOrFail($data['id']);
            $m->update($payload);
            return response()->json(['message'=>'Module mis à jour','module'=>$m], 200);
        }

        $m = ModuleWorkflowDisponible::create($payload);
        return response()->json(['message'=>'Module créé','module'=>$m], 201);
    }


    /** Simulation de parcours (sans créer d’instance) */
    public function simulate(Request $request, $workflowId)
    {
        $wf = WorkflowApprobation::findOrFail($workflowId);
        $inp = $this->validateJson($request, [
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
    public function slaTick(Request $request, ApprovalNotifier $notifier)
    {
        $now = Carbon::now();
        $count = 0;
    
        InstanceEtape::query()
            ->with(['instance', 'etape'])
            ->where('statut_id', $this->statutEtape('EN_COURS')->id)
            ->orderBy('id') // important pour chunkById
            ->chunkById(500, function ($chunk) use ($now, $notifier, &$count) {
                foreach ($chunk as $si) {
                    $slaHours = $si->etape->sla_heures;
                    if (!$slaHours || !$si->date_debut) continue;
    
                    if (Carbon::parse($si->date_debut)->addHours($slaHours)->lt($now)) {
                        $exists = EscaladeApprobation::where('instance_etape_id', $si->id)
                            ->where('raison', 'SLA')
                            ->exists();
                        if (!$exists) {
                            EscaladeApprobation::create([
                                'instance_etape_id' => $si->id,
                                'acteur_source'     => null,
                                'acteur_cible'      => null,
                                'raison'            => 'SLA',
                            ]);
    
                            // Limiter la notif: pas plus d'une fois par étape (grâce à Escalade + notifie_le)
                            try {
                                $notifier->notifyActiveApprovers($si->instance);
                            } catch (\Throwable $e) {
                                \Log::warning('SLA notify error: '.$e->getMessage());
                            }
    
                            $count++;
                        }
                    }
                }
            });
    
        return response()->json(['message' => "SLA traités", 'escalades' => $count]);
    }
    

    /***********************************************************
     *                     HELPERS PRIVÉS
     ***********************************************************/
    private function approverContactsForStep(InstanceEtape $si): array
    {
        $codes = $this->expandApproverCodes($si);
        if (empty($codes)) return [];

        $acteurs = Acteur::whereIn('code_acteur', $codes)
            ->get(['code_acteur','libelle_court','libelle_long','email'])
            ->keyBy('code_acteur');

        $users = User::with('acteur:code_acteur')
            ->whereHas('acteur', fn($q)=>$q->whereIn('code_acteur',$codes))
            ->get(['email','acteur_id']);

        $byActor = [];
        foreach ($codes as $code) {
            $a = $acteurs->get($code);
            $mail = $a?->email ?: $users->first(fn($u)=>optional($u->acteur)->code_acteur===$code)?->email;
            if ($mail) {
                $byActor[] = [
                    'code'  => $code,
                    'email' => $mail,
                    'libelle_court' => (string)($a->libelle_court ?? ''),
                    'libelle_long'  => (string)($a->libelle_long  ?? ''),
                ];
            }
        }
        $seen=[]; return array_values(array_filter($byActor,fn($x)=>!isset($seen[$x['email']])&&($seen[$x['email']]=1)));
    }

    private function versionIsInUse(VersionWorkflow $version): bool
    {
        $stepIds = $version->etapes()->pluck('id');
        return InstanceEtape::whereIn('etape_workflow_id', $stepIds)->exists();
    }

    // Trouve le "propriétaire/demandeur" d’une instance, de façon générique
    private function resolveOwnerContact(InstanceApprobation $inst): ?array
    {
        $snap = (array)($inst->instantane ?? []);

        // a) email direct
        foreach (['owner_email', 'demandeur_email', 'requester_email', 'created_by_email'] as $k) {
            if ($email = data_get($snap, $k)) {
                return ['email' => $email];
            }
        }

        // b) codes acteurs
        foreach (['owner_acteur_code','demandeur_acteur_code','chef_projet_code','requester_acteur_code','created_by_acteur_code'] as $k) {
            if ($code = data_get($snap, $k)) {
                if ($c = $this->emailFromActorCode($code)) {
                    return $c;
                }
            }
        }

        // c) user IDs
        foreach (['owner_user_id','demandeur_user_id','requester_user_id','created_by','user_id'] as $k) {
            if ($uid = data_get($snap, $k)) {
                if ($email = User::whereKey($uid)->value('email')) {
                    $codeActeur = User::with('acteur:code_acteur')->find($uid)?->acteur?->code_acteur;
                    return ['email' => $email, 'code' => $codeActeur];
                }
            }
        }

        // d) fallback éventuel (si champ sur InstanceApprobation)
        if (property_exists($inst, 'created_by_user_id') && $inst->created_by_user_id) {
            if ($email = User::whereKey($inst->created_by_user_id)->value('email')) {
                $codeActeur = User::with('acteur:code_acteur')->find($inst->created_by_user_id)?->acteur?->code_acteur;
                return ['email' => $email, 'code' => $codeActeur];
            }
        }

        return null;
    }

    private function emailFromActorCode(?string $code): ?array
    {
        if (!$code) return null;

        $a = Acteur::where('code_acteur', $code)->first();
        $email = $a?->email;

        if (!$email) {
            $email = User::whereHas('acteur', fn($q)=>$q->where('code_acteur',$code))
                ->value('email');
        }

        if (!$email) return null;

        return [
            'code'          => $code,
            'email'         => $email,
            'libelle_court' => (string)($a->libelle_court ?? ''),
            'libelle_long'  => (string)($a->libelle_long  ?? ''),
        ];
    }

    private function validateWorkflowPayload(Request $request, bool $updating = false): array
    {
        $rules = [
            // workflow
            'nom'                 => [$updating ? 'sometimes' : 'required', 'string', 'max:200'],
            'mode_version'        => ['nullable','in:update_existing,new_version'],
            'code_pays'           => ['required', 'string', 'size:3', Rule::exists('pays', 'alpha3')],
            'groupe_projet_id'    => ['nullable', 'string', Rule::exists('groupe_projet', 'code')],
            'meta'                => ['nullable', 'array'],

            // version
            'version'             => [$updating ? 'sometimes' : 'required', 'array'],
            'version.numero_version'       => ['nullable', 'integer', 'min:1'],
            'version.politique_changement' => ['nullable', 'string', 'max:50'],
            'version.metadonnees'          => ['nullable', 'array'],
            'version.publie'               => ['nullable', 'boolean'],

            // étapes
            'version.etapes'                  => ['required', 'array', 'min:1'],
            'version.etapes.*.position'       => ['required', 'integer', 'min:1'],
            'version.etapes.*.mode_code'      => ['required', 'string', Rule::exists('ref_workflow_mode','code')],
            'version.etapes.*.quorum'         => ['nullable', 'integer', 'min:1'],
            'version.etapes.*.sla_heures'     => ['nullable', 'integer', 'min:1'],
            'version.etapes.*.delegation_autorisee' => ['nullable', 'boolean'],
            'version.etapes.*.sauter_si_vide'       => ['nullable', 'boolean'],
            'version.etapes.*.politique_reapprobation' => ['nullable', 'array'],
            'version.etapes.*.metadonnees'    => ['nullable', 'array'],

            // approbateurs (avec FIELD_ACTEUR)
            'version.etapes.*.approbateurs' => ['nullable', 'array'],
            'version.etapes.*.approbateurs.*.type_approbateur' => [
                'required_with:version.etapes.*.approbateurs',
                'string',
                'in:ACTEUR,FIELD_ACTEUR,ROLE,GROUPE',
            ],
            'version.etapes.*.approbateurs.*.reference_approbateur' => [
                'required_with:version.etapes.*.approbateurs',
                'string',
                'max:150',
            ],
            'version.etapes.*.approbateurs.*.obligatoire' => ['nullable','boolean'],

            // règles
            'version.etapes.*.regles' => ['nullable','array'],
            'version.etapes.*.regles.*.champ' => ['required_with:version.etapes.*.regles', 'string', 'max:100'],
            'version.etapes.*.regles.*.operateur_code' => [
                'required_with:version.etapes.*.regles',
                'string',
                'in:EQ,NE,GT,GTE,LT,LTE,IN,NOT_IN,BETWEEN'
            ],
            'version.etapes.*.regles.*.valeur' => ['required_with:version.etapes.*.regles'],
        ];

        $validator = \Validator::make($request->all(), $rules);

        $validator->after(function($v) use ($request) {
            $etapes = data_get($request->all(), 'version.etapes', []);
            $positions = [];

            foreach ($etapes as $i => $e) {
                $pos = (int)($e['position'] ?? 0);
                if ($pos <= 0) {
                    $v->errors()->add("version.etapes.$i.position", "La position doit être ≥ 1.");
                }
                if (in_array($pos, $positions, true)) {
                    $v->errors()->add("version.etapes.$i.position", "Chaque étape doit avoir une position unique.");
                } else {
                    $positions[] = $pos;
                }

                $nbApp = count($e['approbateurs'] ?? []);
                $quorum = (int)($e['quorum'] ?? 1);
                if ($nbApp > 0 && $quorum > $nbApp) {
                    $v->errors()->add("version.etapes.$i.quorum", "Le quorum ($quorum) ne peut pas dépasser le nombre d’approbateurs ($nbApp).");
                }

                foreach (($e['approbateurs'] ?? []) as $j => $a) {
                    $t = $a['type_approbateur'] ?? null;
                    $ref = (string)($a['reference_approbateur'] ?? '');
                    if ($t === 'FIELD_ACTEUR' && !preg_match('/^[A-Za-z0-9_.]+$/', $ref)) {
                        $v->errors()->add("version.etapes.$i.approbateurs.$j.reference_approbateur",
                            "Pour FIELD_ACTEUR, utiliser un chemin simple (ex: chef_mission_code).");
                    }
                    if ($t === 'ACTEUR' && $ref !== '' && !\App\Models\Acteur::where('code_acteur', $ref)->exists()) {
                        $v->errors()->add("version.etapes.$i.approbateurs.$j.reference_approbateur",
                            "Code acteur introuvable : $ref");
                    }
                }

                foreach (($e['regles'] ?? []) as $k => $r) {
                    $op  = $r['operateur_code'] ?? null;
                    $val = $r['valeur'] ?? null;

                    $decoded = is_string($val) ? (json_decode($val, true) ?? $val) : $val;

                    if (in_array($op, ['IN','NOT_IN'], true) && !is_array($decoded)) {
                        $v->errors()->add("version.etapes.$i.regles.$k.valeur", "Pour $op, fournir un tableau JSON (ex: [\"A\",\"B\"]).");
                    }
                    if ($op === 'BETWEEN') {
                        $ok = is_array($decoded) && count($decoded) === 2 && is_numeric($decoded[0]) && is_numeric($decoded[1]);
                        if (!$ok) {
                            $v->errors()->add("version.etapes.$i.regles.$k.valeur", "Pour BETWEEN, fournir [min,max] numériques.");
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            $this->throw422FromValidator($validator);
        }
        return $validator->validated();
    }

    private function upsertVersionGraph(
        int $workflowId,
        array $versionData,
        string $mode = 'new_version'
    ): VersionWorkflow {
        $existing = null;
        if (($mode === 'update_existing') && !empty($versionData['numero_version'])) {
            $existing = VersionWorkflow::where('workflow_id', $workflowId)
                ->where('numero_version', $versionData['numero_version'])
                ->first();
        }

        if ($mode === 'update_existing' && $existing) {
            $existing->update([
                'politique_changement' => $versionData['politique_changement'] ?? $existing->politique_changement,
                'metadonnees'          => $versionData['metadonnees'] ?? $existing->metadonnees,
                'publie'               => (int)($versionData['publie'] ?? $existing->publie),
            ]);

            $stepIds = $existing->etapes()->pluck('id');
            EtapeApprobateur::whereIn('etape_workflow_id', $stepIds)->delete();
            EtapeRegle::whereIn('etape_workflow_id', $stepIds)->delete();
            $existing->etapes()->delete();

            $this->createEtapesGraph($existing, $versionData['etapes'] ?? []);
            return $existing->load('etapes.approbateurs','etapes.regles');
        }

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
                    'valeur'            => $this->parseRuleValue($r['valeur']),
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
                    'valeur'            => $this->parseRuleValue($r['valeur']),
                ]);
            }
        }

        return $version->load('etapes.approbateurs', 'etapes.regles');
    }

    private function advance(InstanceApprobation $inst, InstanceEtape $justFinished = null, ApprovalNotifier $notifier = null): void
    {
        $steps = InstanceEtape::query()
            ->where('instance_approbation_id', $inst->id)
            ->with('etape')
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
            if ($notifier) {
                $notifier->notifyActiveApprovers($inst);
            } else {
                app(ApprovalNotifier::class)->notifyActiveApprovers($inst);
            }
        }
    }

    private function approverStatusFor(InstanceEtape $step, string $actorCode): string
    {
        // a-t-il approuvé ?
        $hasApproved = $step->actions()
            ->whereHas('type', fn($q) => $q->where('code', 'APPROUVER'))
            ->where('code_acteur', $actorCode)
            ->exists();
        if ($hasApproved) return 'APPROUVE';

        // a-t-il rejeté ?
        $hasRejected = $step->actions()
            ->whereHas('type', fn($q) => $q->where('code', 'REJETER'))
            ->where('code_acteur', $actorCode)
            ->exists();
        if ($hasRejected) return 'REJETE';

        // statut de l'étape
        $codeStep = $this->codeStatutEtape($step->statut_id);
        return match ($codeStep) {
            'EN_COURS' => 'EN_COURS',
            'PENDING'  => 'PENDING',
            default    => 'EN_ATTENTE',
        };
    }

    private function shouldSkip(EtapeWorkflow $etape, ?array $snapshot): bool
    {
        if (!$etape->sauter_si_vide) return false;
        $rules = $etape->regles()->get();
        if ($rules->isEmpty()) return false;

        // Si AUCUNE règle ne match -> on saute
        foreach ($rules as $rule) {
            if ($this->ruleMatches($rule, $snapshot)) {
                return false;
            }
        }
        return true;
    }

    private function ruleMatches(EtapeRegle $r, ?array $snapshot): bool
    {
        $field = $r->champ;
        $val   = data_get($snapshot, $field);
        $op    = OperateurRegle::find($r->operateur_id)?->code;

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

    public function actorCanActOnStep(?string $actorCode, InstanceEtape $si): bool
    {
        if (!$actorCode) return false;
        $codes = $this->expandApproverCodes($si);
        $delegations = $si->actions()->whereHas('type', fn ($q) => $q->where('code', 'DELEGUER'))->get();
        foreach ($delegations as $del) {
            if (data_get($del->meta,'delegate_to') === $actorCode) return true;
        }
        return in_array($actorCode, $codes, true);
    }

    private function requiredApproversMet(InstanceEtape $si): bool
    {
        $required = $si->etape->approbateurs()->where('obligatoire', 1)->get();
        if ($required->isEmpty()) return true;

        $snapshot = (array)($si->instance->instantane ?? []);
        foreach ($required as $req) {
            $requiredCode = match ($req->type_approbateur) {
                'ACTEUR'       => $req->reference_approbateur,
                'FIELD_ACTEUR' => (string) data_get($snapshot, $req->reference_approbateur),
                default        => null, // TODO: ROLE/GROUPE
            };

            if (!$requiredCode) return false;

            $ok = $si->actions()
                ->whereHas('type', fn ($q) => $q->where('code', 'APPROUVER'))
                ->where('code_acteur', $requiredCode)
                ->exists();

            if (!$ok) return false;
        }
        return true;
    }

    private function initialsFromActor(?Acteur $a): string
    {
        $base = trim((string)($a?->libelle_court ?: $a?->libelle_long ?: ''));
        if ($base === '') {
            $login = auth()->user()?->login ?? auth()->user()?->email ?? '??';
            $p = preg_split('/[@\.\s_]+/',$login);
            $c1 = mb_substr($p[0]??'?',0,1); $c2 = mb_substr($p[count($p)-1]??'?',0,1);
            return mb_strtoupper($c1.$c2);
        }

        if (!$a) return '?';

        $short = trim((string) $a->libelle_court);
        $long  = trim((string) $a->libelle_long);

        if ($short !== '' && $long !== '') {
            $last = preg_split('/\s+/', $long);
            $li   = $last ? ($last[count($last)-1] ?: $long) : $long;
            $c1   = mb_substr($short, 0, 1) ?: substr($short, 0, 1);
            $c2   = mb_substr($li,    0, 1) ?: substr($li,    0, 1);
            return mb_strtoupper($c1.$c2);
        }

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

    private function labelFromActor(?Acteur $a, ?string $fallback = null): string
    {
        if ($a) {
            $pieces = array_filter([trim((string)$a->libelle_court), trim((string)$a->libelle_long)]);
            if (!empty($pieces)) return implode(' · ', $pieces);
        }
        return $fallback ?: '';
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

    private function parseRuleValue($raw) {
        if (is_array($raw) || is_object($raw)) return $raw;
        if (is_null($raw)) return null;
        if (is_string($raw)) {
            $t = trim($raw);
            if ($t === '') return null;
            $decoded = json_decode($t, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $t;
        }
        return $raw;
    }

    public function expandApproverCodes(InstanceEtape $si): array
    {
        $snapshot = (array)($si->instance->instantane ?? []);
        $codes = [];
        foreach ($si->etape->approbateurs as $a) {
            switch ($a->type_approbateur) {
                case 'ACTEUR':
                    if ($a->reference_approbateur) $codes[] = $a->reference_approbateur;
                    break;
                case 'FIELD_ACTEUR':
                    $field = (string)$a->reference_approbateur;
                    $code  = data_get($snapshot, $field);
                    if ($code) $codes[] = $code;
                    break;
                case 'ROLE':
                    $codes = array_merge($codes, $this->resolveByRole($a->reference_approbateur, $snapshot));
                    break;
                case 'GROUPE':
                    $codes = array_merge($codes, $this->resolveByGroup($a->reference_approbateur, $snapshot));
                    break;
            }
        }
        return array_values(array_unique(array_filter($codes)));
    }

    private function resolveByRole(string $roleCode, array $snapshot): array {
        // TODO: branchement réel
        return [];
    }
    private function resolveByGroup(string $groupCode, array $snapshot): array {
        // TODO: branchement réel
        return [];
    }
}
