<?php

namespace App\Support;

use App\Services\WorkflowApproval;
use DomainException;

trait ApprovesWithWorkflow
{
    /**
     * Lance un workflow pour un objet métier.
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
     * Helper pour construire un snapshot “propre” depuis un modèle Eloquent.
     */
    protected function snapshotFromModel($model, array $fields): array
    {
        $snap = [];
        foreach ($fields as $f) {
            $snap[$f] = data_get($model, $f);
        }
        return $snap;
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
