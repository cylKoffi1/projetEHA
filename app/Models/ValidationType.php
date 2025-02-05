<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationType extends Model
{
    use HasFactory;

    protected $table = 'validation_types';
    protected $fillable = ['libelle', 'description'];

    public function demandes()
    {
        return $this->hasMany(DemandeValidation::class, 'type_id');
    }
}
