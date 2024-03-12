<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bailleur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'bailleurss'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code_bailleur';


    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays');
    }

    public function type_bailleur()
    {
        return $this->belongsTo(TypeBailleur::class, 'code_type_bailleur');
    }
}
