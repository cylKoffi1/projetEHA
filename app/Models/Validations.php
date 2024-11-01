<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validations extends Model
{
    use HasFactory;

    protected $table = 'validations'; // Nom de la table
    protected $fillable = ['codeEtudeProjets', 'codeAppro','is_approved', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
