<?php

namespace App\Services;

use App\Models\LiaisonWorkflow;
use App\Models\VersionWorkflow;
use App\Models\InstanceApprobation;
use App\Models\InstanceEtape;
use App\Models\ModeWorkflow;
use App\Models\EtapeRegle;
use Illuminate\Support\Facades\DB;
use DomainException;

class WorkflowApproval
{
    /**
     * Lance (ou récupère) une instance d’approbation pour un objet.
     * @return array{instance: InstanceApprobation, created: bool, message?: string}
     * @throws DomainException si aucune liaison/version publiée trouvée
     */
    public function start(string $module, string $type, string $id, array $snapshot = []): array
    {
        // 1) Résoudre la version publiée via les liaisons
        $version = $this->resolveVersion($module, $type, $id);
        if (!$version) {
            throw new DomainException("Aucune version publiée liée à ($module, $type, $id).");
        }

        // 2) Si une instance active existe déjà → on la renvoie (idempotent)
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

        // 3) Créer l’instance + ses étapes
        $created = DB::transaction(function () use ($version, $module, $type, $id, $snapshot) {

            $inst = InstanceApprobation::create([
                'version_workflow_id' => $version->id,
                'module_code'         => $module,
                'type_cible'          => $type,
                'id_cible'            => $id,
                'statut_id'           => $this->statutInstanceId('PENDING'),
                'instantane'          => $snapshot ?: null,
            ]);

            // Générer les étapes d’instance à partir des étapes de la version
            $version->loadMissing('etapes.approbateurs', 'etapes.regles');

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

            return $inst->fresh(['etapes']);
        });

        return ['instance' => $created, 'created' => true];
    }

    /* --------------------- Helpers privés --------------------- */

    private function resolveVersion(string $module, string $type, string $id): ?VersionWorkflow
    {
        // 1) Liaison spécifique (module, type, id)
        $liaison = LiaisonWorkflow::where([
            'module_code' => $module,
            'type_cible'  => $type,
            'id_cible'    => $id,
        ])->whereHas('version', fn($q) => $q->where('publie', 1))
          ->latest('id')->first();

        if ($liaison) {
            return $liaison->version()->with('etapes.approbateurs','etapes.regles')->first();
        }

        // 2) Liaison par type (module, type, id=null, par_defaut=1)
        $liaison = LiaisonWorkflow::where([
            'module_code' => $module,
            'type_cible'  => $type,
            'id_cible'    => null,
            'par_defaut'  => 1,
        ])->whereHas('version', fn($q) => $q->where('publie', 1))
          ->latest('id')->first();

        if ($liaison) {
            return $liaison->version()->with('etapes.approbateurs','etapes.regles')->first();
        }

        return null;
    }

    private function shouldSkip($etape, ?array $snapshot): bool
    {
        if (!$etape->sauter_si_vide) return false;
        $rules = $etape->regles ?? collect();
        if ($rules->isEmpty()) return false;

        // "Sauter si vide" = si AUCUNE règle ne matche
        foreach ($rules as $r) {
            if ($this->ruleMatches($r, $snapshot)) {
                return false;
            }
        }
        return true;
    }

    private function ruleMatches(EtapeRegle $r, ?array $snap): bool
    {
        $val = data_get($snap, $r->champ);
        $op  = optional($r->operateur)->code ?? optional($r->operateur_id && \App\Models\OperateurRegle::find($r->operateur_id))->code;

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

    private function activateNextStep(InstanceApprobation $inst): void
    {
        $steps = $inst->etapes()->with('etape')->get();

        // si toutes les étapes sont APPROUVE/SAUTE → approuver l'instance
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

        // activer la première PENDING par position
        $next = $steps->filter(fn($s) => in_array($this->codeStatutEtape($s->statut_id), ['PENDING','EN_COURS']))
                      ->sortBy(fn($s) => $s->etape->position)
                      ->first();

        if ($next && $this->codeStatutEtape($next->statut_id) === 'PENDING') {
            $next->update([
                'statut_id'  => $this->statutEtapeId('EN_COURS'),
                'date_debut' => now(),
            ]);
        }
    }

    /* ---- raccourcis statut ---- */
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
        return optional(\App\Models\StatutEtapeInstance::find($id))->code;
    }
}
