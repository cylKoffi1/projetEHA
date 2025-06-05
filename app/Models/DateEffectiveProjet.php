<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateEffectiveProjet extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'dates_effectives_projet';
    protected $fillable = ['code_projet', 'date_debut_effective', 'date_fin_effective', 'description'];
    public function projet(){
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
}
