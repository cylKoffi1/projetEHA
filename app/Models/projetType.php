<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetType extends Model
{
    protected $table = 'projettype';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['Code','Libelle','Description'];
    // Un type peut concerner plusieurs études
    public function etudes()
    {
        // EtudeProjet.type_etude_code -> EtudeType.code
        return $this->hasMany(EtudeProjet::class, 'type_etude_code', 'code');
    }
    // Accessors pratiques
    public function getCodeAttribute()     { return $this->attributes['Code']; }
    public function getLibelleAttribute()  { return $this->attributes['Libelle']; }
    public function getDescriptionAttribute(){ return $this->attributes['Description']; }
}
