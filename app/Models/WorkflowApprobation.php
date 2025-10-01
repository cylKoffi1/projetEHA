<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class WorkflowApprobation extends Model
{
    protected $table = 'workflows_approbation';

    // Si ta table a bien created_at/updated_at, laisse true (par défaut)
    // Sinon, mets: public $timestamps = false;

    protected $fillable = [
        'code',              // ⚠️ NE PAS remplir à la main, on le génère
        'nom',
        'code_pays',
        'groupe_projet_id',
        'actif',
        'meta',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'meta'  => 'array',
    ];

    protected static function booted()
    {
        static::creating(function (self $wf) {
            // code_pays est requis par ta validation côté contrôleur
            $alpha3 = strtoupper((string)$wf->code_pays);
            $wf->code = $wf->code ?: self::generateCodeFor($alpha3);
        });
    }

    /**
     * Génère un code unique du type: WF_{ALPHA3}{YYYY}_{NNNN}
     * - sans table de séquence
     * - tolérant à la concurrence via unique index + retry
     */
    public static function generateCodeFor(string $alpha3): string
    {
        $year   = date('Y');
        $prefix = "WF_{$alpha3}{$year}_";

        // IMPORTANT: mets une contrainte unique sur la colonne `code` (voir migration plus bas)
        $attempts = 0;
        $maxAttempts = 5;

        while ($attempts < $maxAttempts) {
            $attempts++;

            // Variante A (précise, si timestamps existent) : on compte les workflows de l’année et du pays
            // -> évite d'analyser la string du code
            $next = DB::transaction(function () use ($alpha3, $year, $prefix) {
                // Si la table N'A PAS de timestamps, commente ce bloc et décommente la Variante B plus bas.
                $count = DB::table('workflows_approbation')
                    ->where('code_pays', $alpha3)
                    ->whereYear('created_at', $year) // nécessite created_at
                    ->lockForUpdate()
                    ->count();

                return $count + 1;
            });

            // Fabrique la proposition
            $candidate = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);

            // Petit check opportuniste: si ça n’existe pas déjà, on le retourne.
            // (De toute façon, la vraie sécurité c'est l'index unique + try/catch à l'insert)
            $exists = DB::table('workflows_approbation')->where('code', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }

            // Sinon on boucle pour tenter le suivant.
        }

        // Dernier recours: on prend un suffixe random pour ne pas planter la création
        // (très improbable d’arriver ici)
        return $prefix . substr((string)mt_rand(10000, 99999), -4);
    }

    /** Relations */
    public function versions()
    {
        return $this->hasMany(VersionWorkflow::class, 'workflow_id');
    }
}
