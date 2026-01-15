<?php

namespace App\Services;

use App\Models\LiaisonWorkflow;
use App\Models\VersionWorkflow;
use App\Models\InstanceApprobation;
use App\Models\InstanceEtape;
use App\Models\EtapeWorkflow;
use App\Models\EtapeRegle;
use App\Models\OperateurRegle;
use App\Support\SnapshotNormalizer;
use Illuminate\Support\Facades\DB;
use DomainException;

/**
 * Service d’exécution des workflows d’approbation (côté “start”).
 * - Résout la version publiée via les liaisons
 * - Crée l’instance + ses étapes
 * - Active la première étape
 * - Notifie automatiquement les approbateurs de l’étape active
 */
class WorkflowApproval
{
    public function __construct(
        private SnapshotNormalizer $normalizer,
        private ApprovalNotifier   $notifier,   // ✅ notifications d’étape active
    ) {}

    /**
     * Lance (ou récupère) une instance d’approbation pour un objet.
     *
     * @return array{instance: InstanceApprobation, created: bool, message?: string}
     * @throws DomainException si aucune liaison/version publiée trouvée
     */
    public function start(string $module, string $type, string $id, array $snapshot = []): array
    {
        // 0) Normaliser/enrichir le snapshot (owner_*/demandeur_*…)
        $snapshot = $this->normalizer->normalize($snapshot);

        // 1) Trouver la version publiée
        // appeler resolveVersion avec scope s'ils sont dans $snapshot
        $pays = $snapshot['pays_code'] ?? ($snapshot['code_pays'] ?? null);
        $groupe = $snapshot['groupe_projet_id'] ?? null;

        $version = $this->resolveVersion($module, $type, $id, $pays, $groupe);
        if (!$version) {
            throw new DomainException("Aucune version publiée liée à ($module, $type, $id).");
        }

        // 2) Idempotence : une instance active existe-t-elle déjà ?
        $active = InstanceApprobation::query()
            ->where('module_code', $module)
            ->where('type_cible',  $type)
            ->where('id_cible',    $id)
            ->whereIn('statut_id', [
                $this->statutInstanceId('PENDING'),
                $this->statutInstanceId('EN_COURS'),
            ])
            ->first();

        if ($active) {
            return ['instance' => $active, 'created' => false, 'message' => 'Instance déjà active'];
        }

        // 3) Créer l’instance et ses étapes
        $created = DB::transaction(function () use ($version, $module, $type, $id, $snapshot) {

            $inst = InstanceApprobation::create([
                'version_workflow_id' => $version->id,
                'module_code'         => $module,
                'type_cible'          => $type,
                'id_cible'            => $id,
                'statut_id'           => $this->statutInstanceId('PENDING'),
                'instantane'          => $snapshot ?: null,
            ]);

            // Charger les sous-relations nécessaires
            $version->loadMissing('etapes.approbateurs', 'etapes.regles');

            // Générer toutes les étapes d’instance
            foreach ($version->etapes()->orderBy('position')->get() as $etape) {
                $statut = $this->shouldSkip($etape, $inst->instantane)
                    ? $this->statutEtapeId('SAUTE')
                    : $this->statutEtapeId('PENDING');

                InstanceEtape::create([
                    'instance_approbation_id' => $inst->id,
                    'etape_workflow_id'       => $etape->id,
                    'statut_id'               => $statut,
                    'quorum_requis'           => $etape->quorum,
                    'nombre_approbations'     => 0,
                    'date_debut'              => null,
                    'date_fin'                => null,
                ]);
            }

            // Activer la première étape non sautée
            $this->activateNextStep($inst);

            // 🔔 Notifier automatiquement les approbateurs de l’étape active
            $this->notifier->notifyActiveApprovers($inst);

            return $inst->fresh(['etapes']);
        });

        return ['instance' => $created, 'created' => true];
    }

    /* =========================================================
     *                      Helpers privés
     * ========================================================= */

    /**
     * Version publiée via (ordre de priorité) :
     * 1) Liaison spécifique (module,type,id) + scope match
     * 2) Liaison spécifique (module,type,id) (ignore scope)
     * 3) Liaison par défaut (module,type,id=null, par_defaut=1) + scope match
     * 4) Liaison par défaut globale (module,type,id=null, par_defaut=1)
     * ⚠️ On ne retombe plus sur "n'importe quelle" liaison par défaut sans filtrer module/type.
     */
    private function resolveVersion(string $module, string $type, string $id, ?string $pays = null, ?string $groupeProjet = null): ?VersionWorkflow
    {
        // candidates (ordre de priorité : le plus spécifique -> le plus global)
        $candidates = [];

        // exact (module,type,id) + scope match
        $candidates[] = fn($q) => $q->where('module_code',$module)->where('type_cible',$type)->where('id_cible',$id)
            ->where(function($s) use($pays){ $s->whereNull('code_pays')->orWhere('code_pays',$pays); })
            ->where(function($s) use($groupeProjet){ $s->whereNull('groupe_projet_id')->orWhere('groupe_projet_id',$groupeProjet); });

        // exact id (ignore scope)
        $candidates[] = fn($q) => $q->where('module_code',$module)->where('type_cible',$type)->where('id_cible',$id);

        // par_defaut scoped
        $candidates[] = fn($q) => $q->where('module_code',$module)->where('type_cible',$type)->whereNull('id_cible')->where('par_defaut',1)
            ->where(function($s) use($pays){ $s->whereNull('code_pays')->orWhere('code_pays',$pays); })
            ->where(function($s) use($groupeProjet){ $s->whereNull('groupe_projet_id')->orWhere('groupe_projet_id',$groupeProjet); });

        // par_defaut global (module,type)
        $candidates[] = fn($q) => $q->where('module_code',$module)->where('type_cible',$type)->whereNull('id_cible')->where('par_defaut',1);

        foreach ($candidates as $build) {
            $liaison = LiaisonWorkflow::whereHas('version', fn($q)=> $q->where('publie',1))
                ->where(function($q) use ($build){ $build($q); })
                ->latest('id')->first();

            if ($liaison) {
                return $liaison->version()->with('etapes.approbateurs','etapes.regles')->first();
            }
        }

        return null;
    }

    /**
     * Règle “sauter_si_vide” : si AUCUNE règle ne matche → on saute l’étape.
     */
    private function shouldSkip(EtapeWorkflow $etape, ?array $snapshot): bool
    {
        if (!$etape->sauter_si_vide) return false;

        $rules = $etape->relationLoaded('regles')
            ? $etape->regles
            : $etape->regles()->get();

        if ($rules->isEmpty()) return false;

        foreach ($rules as $r) {
            if ($this->ruleMatches($r, $snapshot)) {
                return false; // au moins une règle matche → on NE saute PAS
            }
        }
        return true; // aucune règle ne matche → on saute
    }

    /**
     * Évalue une règle sur le snapshot.
     */
    private function ruleMatches(EtapeRegle $r, ?array $snap): bool
    {
        $val = data_get($snap, $r->champ);
        $op  = $this->opCode($r);
        $exp = is_string($r->valeur) ? (json_decode($r->valeur, true) ?? $r->valeur) : $r->valeur;

        return match ($op) {
            'EQ'      => $val == $exp,
            'NE'      => $val != $exp,
            'GT'      => is_numeric($val) && $val >  $exp,
            'GTE'     => is_numeric($val) && $val >= $exp,
            'LT'      => is_numeric($val) && $val <  $exp,
            'LTE'     => is_numeric($val) && $val <= $exp,
            'IN'      => is_array($exp) && in_array($val, $exp, true),
            'NOT_IN'  => is_array($exp) && !in_array($val, $exp, true),
            'BETWEEN' => is_array($exp) && count($exp)===2 && is_numeric($val) && $val >= $exp[0] && $val <= $exp[1],
            default   => false,
        };
    }

    /**
     * Récupère le code opérateur de la règle (relation ou fallback par ID).
     */
    private function opCode(EtapeRegle $r): ?string
    {
        if ($r->relationLoaded('operateur') && $r->operateur) {
            return $r->operateur->code;
        }
        if ($r->operateur_id) {
            return OperateurRegle::find($r->operateur_id)?->code;
        }
        return null;
    }

    /**
     * Active la première étape PENDING (par position) ou clôt l’instance si tout est approuvé/sauté.
     */
    private function activateNextStep(InstanceApprobation $inst): void
    {
        $steps = $inst->etapes()->with('etape')->get();

        // Tout approuvé/sauté ?
        $allApproved = $steps->every(function ($s) {
            $code = $this->codeStatutEtape($s->statut_id);
            return in_array($code, ['APPROUVE', 'SAUTE']);
        });

        if ($allApproved) {
            $inst->update([
                'statut_id' => $this->statutInstanceId('APPROUVE'),
                'date_fin'  => now(),
            ]);
            return;
        }

        // Activer la première étape PENDING (ordre position)
        $next = $steps
            ->filter(fn($s) => in_array($this->codeStatutEtape($s->statut_id), ['PENDING','EN_COURS']))
            ->sortBy(fn($s) => $s->etape->position)
            ->first();

        if ($next && $this->codeStatutEtape($next->statut_id) === 'PENDING') {
            $next->update([
                'statut_id'  => $this->statutEtapeId('EN_COURS'),
                'date_debut' => now(),
            ]);
        }
    }

    /* ---------------- Raccourcis statuts ---------------- */

    private function statutInstanceId(string $code): int
    {
        return \App\Models\StatutInstance::where('code', $code)->firstOrFail()->id;
    }

    private function statutEtapeId(string $code): int
    {
        return \App\Models\StatutEtapeInstance::where('code', $code)->firstOrFail()->id;
    }

    private function codeStatutEtape(int $id): ?string
    {
        return \App\Models\StatutEtapeInstance::find($id)?->code;
    }
}
