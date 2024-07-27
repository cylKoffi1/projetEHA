<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetChefProjet extends Model
{
    use HasFactory;
    protected $table = 'projet_chef_projet';
    protected $primaryKey = 'code_projet';
    public $timestamps = false;
    protected $fillable = [
        'code_projet',
        'code_personnel',
        'date'
    ];
    public function Personne()
    {
        return $this->hasMany(Personnel::class, 'code_personnel', 'code_personnel');
    }
}
