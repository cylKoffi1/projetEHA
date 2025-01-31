<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taches extends Model
{
    use HasFactory;

    // Table associée au modèle
    protected $table = 'taches';
    protected $appends = ["open"];

    public function getOpenAttribute(){
         return true;
    }
    protected $fillable = ['text', 'start_date', 'duration', 'progress', 'parent', 'sortorder', 'is_deleted',  'CodeProjet'];
    protected $casts = [
        'start_date' => 'datetime',
    ];
    // Vous pouvez également ajouter une méthode pour récupérer les tâches non supprimées
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'CodeProjet');
    }
}
