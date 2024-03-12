<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    protected $table = 'region'; // Nom de la table
    protected $primaryKey = 'code';
    protected $keyType = 'string';

    public function district()
    {
        return $this->belongsTo(District::class, 'code_district');
    }
}
