<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkflowApprobation extends Model
{
    protected $table = 'workflows_approbation';

    protected $fillable = [
        'code',  // ⚠️ NE PAS remplir manuellement : généré automatiquement
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
        static::creating(function ($wf) {
            if (empty($wf->code)) {
                $wf->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $prefix = 'WF-' . $year . '-';
    
        $next = DB::transaction(function () use ($prefix) {
            // On compte les workflows de l’année en cours
            $count = DB::table('workflows_approbation')
                ->where('code', 'like', $prefix.'%')
                ->lockForUpdate() // évite les doublons en cas d'accès simultané
                ->count();
    
            return $count + 1;
        });
    
        return sprintf('%s%04d', $prefix, $next);
    }

    /** Relations utiles */
    public function versions()
    {
        return $this->hasMany(VersionWorkflow::class, 'workflow_id');
    }
}
