<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecoupageAdminPays extends Model
{
    use HasFactory;

    protected $table = 'decoupage_admin_pays'; // Nom de la table
    protected $fillable = ['id_pays', 'num_niveau_decoupage', 'code_decoupage'];

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'id');
    }

    public function couvrirRegions()
    {
        return $this->hasMany(CouvrirRegion::class, 'code_niveau_administratif', 'num_niveau_decoupage');
    }
}
