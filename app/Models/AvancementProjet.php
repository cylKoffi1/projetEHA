<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvancementProjet extends Model
{
    use HasFactory;

    protected $table = 'avancement_projets';

    protected $fillable = [
        'code_projet',
        'num_ordre',
        'quantite',
        'pourcentage',
        'date_avancement',
        'photos',
        'date_fin_effective',
        'description_finale',
        'code_acteur',
    ];

    protected $casts = [
        'date_avancement' => 'date',
        'date_fin_effective' => 'date',
        'quantite' => 'decimal:2',
        'pourcentage' => 'decimal:2',
    ];
    protected $appends = ['photos_list','photos_urls'];

    public function getPhotosListAttribute()
    {
        $val = $this->attributes['photos'] ?? '';
        $arr = array_filter(array_map('trim', explode(',', (string)$val)));
        // ne garder que les ID numériques (si fichiers.id est BIGINT)
        return array_values(array_filter($arr, fn($x) => ctype_digit((string)$x)));
    }

    public function getPhotosUrlsAttribute()
    {
        return array_map(fn($id) => url('/fichiers/'.$id), $this->photos_list);
    }

    /**
     * Relation avec le projet (via code_projet)
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

        /**
     * Relation avec le projet (via code_projet)
     */
    public function projetInfrastructures()
    {
        return $this->belongsTo(ProjetInfrastructure::class, 'code_projet', 'code_projet');
    }

    /**
     * Relation avec l'acteur utilisateur (ingénieur, superviseur, etc.)
     */
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Photos séparées par virgules => tableau
     */


    public function setPhotosListAttribute($array)
    {
        $this->attributes['photos'] = implode(',', $array);
    }
}
