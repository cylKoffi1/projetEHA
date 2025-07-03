<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValeurCaracteristique extends Model
{
    use HasFactory;

    protected $table = 'valeurs_saisies';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'infrastructure_code',
        'idCaracteristique',
        'valeur',
        'idUnite',
        'parent_saisie_id',
        'ordre'
    ];

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'idCaracteristique');
    }

    public function unite()
    {
        return $this->belongsTo(Unite::class, 'idUnite');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_saisie_id');
    }

    public function enfants()
    {
        return $this->hasMany(self::class, 'parent_saisie_id');
    }
}
