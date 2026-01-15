<?php
// app/Models/EtudeLivrable.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtudeLivrable extends Model
{
    protected $table = 'etude_livrables';
    protected $fillable = ['code','libelle'];

    public function etudes()
    {
        return $this->belongsToMany(
            EtudeProjet::class,
            'etude_projet_livrables',
            'livrable_id',
            'code_projet_etude',   // clé autre modèle dans le pivot
            'id',
            'code_projet_etude'
        );
    }
}
