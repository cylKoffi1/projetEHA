<?php

namespace App\Support;

use App\Services\WorkflowApproval;

trait ApprovesWithWorkflow
{
    /**
     * Lance un workflow pour un objet métier.
     * Le snapshot est automatiquement normalisé pour inclure les hints standard.
     *
     * @return array{instance: \App\Models\InstanceApprobation, created: bool, message?: string}
     */
    protected function startApproval(
        string $module,
        string $type,
        string $idObjet,
        array $snapshot = []
    ): array {
        /** @var WorkflowApproval $svc */
        $svc = app(WorkflowApproval::class);
        return $svc->start($module, $type, $idObjet, $snapshot);
    }

    /**
     * Helper simple : extrait des champs du modèle (data_get) vers snapshot.
     */
    protected function snapshotFromModel($model, array $fields): array
    {
        $snap = [];
        foreach ($fields as $f) {
            $snap[$f] = data_get($model, $f);
        }
        return $snap;
    }

    /**
     * Helper “prêt à l’emploi” :
     * génère un snapshot standard en mappant différents schémas possibles
     * vers les clés hints (owner_* demandeur_*).
     *
     * @param  mixed $model  Ton modèle métier (Projet, Dossier, etc.)
     * @param  array $map    Carte des colonnes du modèle -> hints standard
     *                       (si tu ne passes rien, on tente les conventions par défaut)
     */
    protected function standardSnapshot($model, array $map = []): array
    {
        // Conventions par défaut (sur ton modèle)
        $defaults = [
            // emails
            'owner_email'              => ['owner_email', 'demandeur_email', 'requester_email', 'created_by_email'],
            // user ids
            'owner_user_id'            => ['owner_user_id', 'demandeur_user_id', 'requester_user_id', 'created_by', 'user_id'],
            // acteur codes
            'owner_acteur_code'        => ['owner_acteur_code', 'demandeur_acteur_code', 'requester_acteur_code', 'chef_projet_code', 'created_by_acteur_code'],
        ];

        // Si tu passes un $map, il écrase/complète
        // ex: ['owner_user_id' => 'demandeur_id', 'demandeur_acteur_code' => 'demandeur_code']
        foreach ($map as $hint => $source) {
            $defaults[$hint] = (array) $source;
        }

        $snap = [];

        // Remplir avec la première colonne disponible pour chaque hint
        foreach ($defaults as $hint => $candidates) {
            foreach ($candidates as $col) {
                $val = data_get($model, $col);
                if (!is_null($val) && $val !== '') { $snap[$hint] = $val; break; }
            }
        }

        // Laisse le normaliseur enrichir (emails depuis user_id/acteur_code, alias…)
        /** @var \App\Support\SnapshotNormalizer $normalizer */
        $normalizer = app(\App\Support\SnapshotNormalizer::class);
        return $normalizer->normalize($snap);
    }
}

/*
<?php

namespace App\Http\Controllers;

use App\Models\ProjetAppui;
use Illuminate\Http\Request;
use App\Support\ApprovesWithWorkflow;

class ProjetAppuiController extends Controller
{
    use ApprovesWithWorkflow;

    public function store(Request $request)
    {
        // 1) Tu sauvegardes normalement ton objet
        $proj = ProjetAppui::create($request->all());

        // 2) Quand il faut lancer l’approbation (bouton “Envoyer en approbation” ou règle)
        $snap = $this->snapshotFromModel($proj, [
            'montant_total', 'pays_code', 'categorie', 'chef_projet_code'
        ]);

        try {
            $res = $this->startApproval(
                module:  'PROJET',
                type:    'projet_appui',
                idObjet: (string) $proj->code, // ou $proj->id
                snapshot: $snap
            );
        } catch (\DomainException $e) {
            // Aucune liaison/version publiée trouvée
            return back()->with('error', $e->getMessage());
        }

        // 3) Feedback UI
        if ($res['created']) {
            return redirect()->route('approvals.dashboard')
                ->with('success', "Approbation lancée (#{$res['instance']->id}).");
        } else {
            return redirect()->route('approvals.dashboard')
                ->with('info', "Une instance est déjà active (#{$res['instance']->id}).");
        }
    }
}
*/