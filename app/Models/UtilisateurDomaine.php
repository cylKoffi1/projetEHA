<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilisateurDomaine extends Model
{
    use HasFactory;
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'utilisateur_domaine';


    protected $fillable = [
        'code_personnel',
        'code_domaine',
        'created_at',
        'updated_at',
    ];

    public function personnel() 
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }



    public function domaine() 
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code');
    }
}
