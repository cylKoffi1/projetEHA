<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecoupageAdministratif extends Model
{
    use HasFactory;

    protected $table = 'decoupage_administratif';
    protected $fillable = ['code_decoupage', 'libelle_decoupage'];

    public function localites()
    {
        return $this->hasMany(LocalitesPays::class, 'code_decoupage', 'code_decoupage');
    }
}
