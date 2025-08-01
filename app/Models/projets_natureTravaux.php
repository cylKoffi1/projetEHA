<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class projets_natureTravaux extends Model
{
    use HasFactory;
    protected $table = 'projets_naturetravaux';
    protected $primaryKey = 'id_PNT'; 
    public $timestamps = false; 
    // Colonnes modifiables
    protected $fillable = [
        'code_projet',
        'code_nature',
        'date'
    ];
    public function natureTravaux(){
        return $this->belongsTo(NatureTravaux::class,  'code_nature', 'code');
    }
}
