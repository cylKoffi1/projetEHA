<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalite extends Model
{
    use HasFactory;

    protected $table = 'modalite';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'Libelle',
        'Description',
    ];

    /**
     * Un modalité peut être utilisée dans plusieurs renforcements.
     */
    public function renforcements()
    {
        return $this->hasMany(Renforcement::class, 'modaliteId', 'id');
    }
}
