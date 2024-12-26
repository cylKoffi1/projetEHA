<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomaineUser extends Model
{
    use HasFactory;

    protected $table = 'domaine_user';
    protected $fillable = ['codeDomaine', 'codeUser'];

    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'codeDomaine', 'code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'code_user', 'acteur_id');
    }
}
