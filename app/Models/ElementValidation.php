<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementValidation extends Model
{
    use HasFactory;

    protected $table = 'elements_validation';
    protected $fillable = ['type_element', 'reference', 'description'];

    public function demandes()
    {
        return $this->hasMany(DemandeValidation::class, 'element_id');
    }
}
