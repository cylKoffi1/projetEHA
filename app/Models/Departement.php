<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'departement'; // Nom de la table
    protected $primaryKey = 'code';
    protected $keyType = 'string';

    public function region()
    {
        return $this->belongsTo(Region::class, 'code_region');
    }
}
