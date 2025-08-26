<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Fichier extends Model
{
    // Table & clé
    protected $table = 'fichiers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps: on mappe created_at -> uploaded_at, pas de updated_at
    const CREATED_AT = 'uploaded_at';
    const UPDATED_AT = null;

    // Champs modifiables
    protected $fillable = [
        'owner_type',
        'owner_id',
        'categorie',
        'filename',
        'mime_type',
        'size_bytes',
        'md5',
        'sha256',
        'gridfs_id',
        'extra_json',
        'uploaded_by',
        'uploaded_at',
        'is_active',
    ];

    // Casts
    protected $casts = [
        'owner_id'    => 'integer',
        'size_bytes'  => 'integer',
        'uploaded_by' => 'integer',
        'uploaded_at' => 'datetime',
        'is_active'   => 'boolean',
        'extra_json'  => 'array',
    ];

    // Attributs additionnels exposés
    protected $appends = ['url'];

    /**
     * Relation polymorphe vers l'entité propriétaire (Acteur, Pays, Projet, etc.)
     * Usage: $fichier->owner
     */
    public function owner()
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    /**
     * URL de téléchargement/affichage (stream inline)
     * -> pointe sur la route /fichiers/{id}
     */
    public function getUrlAttribute(): string
    {
        return url('/fichiers/'.$this->id);
    }

    /**
     * Scopes pratiques
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeForOwner($query, string $type, int $id)
    {
        return $query->where('owner_type', $type)->where('owner_id', $id);
    }

    public function scopeForCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Hook modèle: lors de la suppression, on efface aussi le binaire GridFS
     * (utile si tu fais $fichier->delete()).
     */
    protected static function booted()
    {
        static::deleting(function (Fichier $f) {
            try {
                app(\App\Services\GridFsService::class)->delete($f->gridfs_id);
            } catch (\Throwable $e) {
                Log::warning('GridFS delete failed', [
                    'fichier_id' => $f->id,
                    'gridfs_id'  => $f->gridfs_id,
                    'err'        => $e->getMessage(),
                ]);
            }
        });
    }
}
