<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecteurActivite extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'secteurs_activite';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Les attributs qui peuvent être remplis en masse.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'actif' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Désactiver les timestamps automatiques si nécessaire.
     * Laravel gère automatiquement `created_at` et `updated_at`, mais si vos colonnes sont `date_creation` et `date_modification`, vous devrez désactiver cette fonctionnalité.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Définir un mutator pour le champ `date_creation`.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->date_creation = now();
        });

        static::updating(function ($model) {
            $model->date_modification = now();
        });
    }
}
