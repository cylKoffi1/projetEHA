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
    protected $fillable = ['code_personnel', 'code_niveau_administratif', 'date', 'id_pays'];

    public function personne()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'code_personnel', 'code_personnel');
    }

    public function niveauAdministratif()
    {
        return $this->belongsTo(DecoupageAdminPays::class, 'code_niveau_administratif', 'num_niveau_decoupage');
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'id');
    }
}
