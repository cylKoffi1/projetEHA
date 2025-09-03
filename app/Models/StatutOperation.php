<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutOperation extends Model
{
    use HasFactory;

    protected $table = 'StatutOperation';
    protected $primaryKey = 'Id';
    public $incrementing = false; // car clé primaire VARCHAR(5)
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Id',
        'Libelle',
        'Description',
    ];

    /**
     * Un statut peut concerner plusieurs renforcements.
     */
    public function renforcements()
    {
        return $this->hasMany(Renforcement::class, 'statutId', 'Id');
    }
}
