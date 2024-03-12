<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = 'district'; // Nom de la table
    protected $primaryKey = 'code';
    protected $keyType = 'string';

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays');
    }
}
