<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouvrirRegion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'couvrir_region'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = ['code_personnel', 'code_region', 'date', 'code_departement', 'code_district', 'id_pays'];


    public function personne()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'code_region', 'code');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'code_departement', 'code');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'code_district', 'code');
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'id');
    }
    
}
