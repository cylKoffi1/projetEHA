<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModuleWorkflowDisponible extends Model
{
    protected $table = 'modules_workflow_disponibles';

    protected $fillable = [
        'code_module','libelle_module','type_cible','libelle_type',
        'classe_modele','champ_identifiant','actif','metadata'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'metadata' => 'array'
    ];

    public function liaisons()
    {
        return $this->hasMany(LiaisonWorkflow::class, 'module_type_id');
    }

    /**
     * Retourne instances pour autocomplete (id,label,raw)
     */
    public function getInstances(array $filters = [])
    {
        if (empty($this->classe_modele) || !class_exists($this->classe_modele)) {
            return collect();
        }

        $model = app($this->classe_modele);
        $query = $model::query();

        // optional scope filters if available on the model
        if (!empty($filters['pays_code']) && method_exists($model, 'scopePays')) {
            $query->pays($filters['pays_code']);
        }
        if (!empty($filters['groupe_projet_id']) && method_exists($model, 'scopeGroupeProjet')) {
            $query->groupeProjet($filters['groupe_projet_id']);
        }

        $idCol = $this->champ_identifiant ?: 'id';

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function($w) use ($idCol, $q, $model) {
                $w->where($idCol, 'like', "%{$q}%");
                foreach (['libelle','nom','titre','intitule'] as $c) {
                    if (Schema::hasColumn($model->getTable(), $c)) {
                        $w->orWhere($c, 'like', "%{$q}%");
                    }
                }
            });
        }

        $labelCol = null;
        foreach (['libelle','nom','titre','intitule'] as $c) {
            if (Schema::hasColumn($model->getTable(), $c)) { $labelCol = $c; break; }
        }

        $select = [$idCol];
        if ($labelCol) $select[] = $labelCol;

        return $query->select($select)->limit(100)->get()->map(function($r) use ($idCol, $labelCol) {
            return [
                'id' => (string)($r->$idCol),
                'label' => $labelCol ? (string)($r->$labelCol) : (string)($r->$idCol),
                'raw' => $r,
            ];
        });
    }
}
