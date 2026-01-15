<?php
// app/Models/TypeUtilisateur.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeUtilisateur extends Model
{
    use HasFactory;

    protected $table = 'type_utilisateur';

    protected $fillable = [
        'code',
        'libelle',
        'description',
    ];

    public function groupesUtilisateurs()
    {
        return $this->hasMany(GroupeUtilisateur::class, 'type_utilisateur_id', 'id');
    }
}
