<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renforcement extends Model
{
    use HasFactory;

    protected $table = 'renforcement_capacites';

    protected $primaryKey = 'code_renforcement';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['code_renforcement', 'titre', 'description', 'date_debut', 'date_fin'];

    // Relation avec les bénéficiaires
    public function beneficiaires()
    {
        return $this->belongsToMany(User::class, 'renforcement_beneficiaire', 'renforcement_capacite', 'code_personnel');
    }

    // Relation avec les projets
    public function projets()
    {
        return $this->belongsToMany(ProjetEha2::class, 'renforcement_projet', 'renforcement_capacite', 'CodeProjet');
    }

    public static function generateCodeRenforcement()
    {
        $latest = self::latest()->first();
        $orderNumber = $latest ? intval(substr($latest->code_renforcement, -3)) + 1 : 1;
        $month = now()->format('m');
        $year = now()->format('Y');
        return 'EHA_RF_' . $month . '_' . $year . '_' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);
    }
    // Cette méthode est appelée lorsque l'événement 'deleting' est déclenché
    public static function boot()
    {
        parent::boot();

        // Supprimer les relations avec les tables pivot lors de la suppression d'un renforcement
        static::deleting(function ($renforcement) {
            // Détacher les bénéficiaires liés
            $renforcement->beneficiaires()->detach();

            // Détacher les projets liés
            $renforcement->projets()->detach();
        });
    }
}
