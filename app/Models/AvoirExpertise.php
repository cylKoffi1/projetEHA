<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvoirExpertise extends Model
{
    use HasFactory;


    public $timestamps = false;

    protected $table = 'avoir_expertise'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = ['code_personnel', 'id', 'sous_domaine', 'date'];


    public function sous_domaine()
    {
        return $this->belongsTo(SousDomaine::class, 'sous_domaine', 'code');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }
}
