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

    protected $fillable = ['code_renforcement', 'titre', 'description', 'date_debut', 'date_fin'];

    // ✅ Bénéficiaires via table pivot
    public function beneficiaires()
    {
        return $this->belongsToMany(Acteur::class, 'renforcement_beneficiaire', 'renforcement_capacite', 'code_acteur');
    }

    // ✅ Projets via table pivot
    public function projets()
    {
        return $this->belongsToMany(Projet::class, 'renforcement_projet', 'renforcement_capacite', 'code_projet');
    }
    
    public static function generateCodeRenforcement($country, $group)
    {
        $month = now()->format('m');
        $year = now()->format('Y');
        $base = "{$country}_{$group}_RF_{$year}_{$month}_";
    
        // Cherche le dernier code avec cette racine
        $last = self::where('code_renforcement', 'like', $base . '%')
                    ->orderByDesc('code_renforcement')
                    ->first();
    
        if ($last) {
            $lastNumber = intval(substr($last->code_renforcement, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
    
        return $base . $newNumber;
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
