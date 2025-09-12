<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materiau extends Model
{
    use HasFactory;
    protected $table = 'materiaux';
    protected $fillable = ['libelle','categorie','unite_id'];
    public function unite() { return $this->belongsTo(Unite::class,'unite_id','idUnite');} 
}
