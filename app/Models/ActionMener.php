<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionMener extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'action_mener'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    public function beneficiaire() {
        return $this->belongsToMany(Beneficiaire::class, 'action_beneficiaire');
    }
}
