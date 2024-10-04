<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilleInfrastructure extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = '<famill>                    </famill>e_infrastructure'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'nom_famille'];
}
