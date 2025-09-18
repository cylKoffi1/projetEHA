<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renforcement extends Model
{
    use HasFactory;

    protected $table = 'renforcement_capacites';
    protected $primaryKey = 'code_renforcement';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code_renforcement',
        'titre',
        'description',
        'actionTypeId',
        'thematique',
        'public_cible',
        'organisme',
        'lieu',
        'modaliteId',
        'nb_participants_prev',
        'nb_participants_effectif',
        'cout_previsionnel',
        'cout_reel',
        'source_financement',
        'statutId',
        'motif_annulation',
        'date_debut',
        'date_fin',
    ];
    public function fichiers()
    {
        // owner_id est une string (code_renforcement)
        return $this->hasMany(Fichier::class, 'owner_id', 'code_renforcement')
            ->where('owner_type', 'Renforcement')
            ->orderByDesc('uploaded_at');
    }

    /* ================= Relations ================= */

public function beneficiaires()
{
    return $this->belongsToMany(
        Acteur::class,
        'renforcement_beneficiaire',
        'code_renforcement',
        'code_acteur'
    )->withTimestamps(); // <-- important si ta table possède created_at/updated_at
}

public function projets()
{
    return $this->belongsToMany(
        Projet::class,
        'renforcement_projet',
        'code_renforcement',
        'code_projet'
    )->withTimestamps();
}

    public function financier(){
        return $this->belongsTo(Acteur::class, 'source_financement', 'code_acteur');
    }
    public function actionType()
    {
        return $this->belongsTo(ActionType::class, 'actionTypeId', 'id');
    }
    public function modalite()
    {
        return $this->belongsTo(Modalite::class, 'modaliteId', 'id');
    }

    public function statut()
    {
        return $this->belongsTo(StatutOperation::class, 'statutId', 'Id');
    }

    /* ================= Utilitaires ================= */

    // Générer un code unique type CIV_BTP_RF_2025_08_001
    public static function generateCodeRenforcement($country, $group)
    {
        $month = now()->format('m');
        $year  = now()->format('Y');
        $base  = "{$country}_{$group}_RF_{$year}_{$month}_";

        $last = self::where('code_renforcement', 'like', $base.'%')
                    ->orderByDesc('code_renforcement')
                    ->first();

        if ($last) {
            $lastNumber = intval(substr($last->code_renforcement, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $base.$newNumber;
    }

    // Supprimer automatiquement les relations pivot lors de la suppression
    protected static function booted()
    {
        static::deleting(function ($renforcement) {
            $renforcement->beneficiaires()->detach();
            $renforcement->projets()->detach();
        });
    }
}
