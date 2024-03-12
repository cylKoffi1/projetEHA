<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinistereProjet extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ministere_projet'; // Nom de la table
    protected $primaryKey = 'id';

    protected $fillable = ['code_ministere', 'codeProjet'];

    public function ministere()
    {
        return $this->belongsTo(Ministere::class, 'code_ministere', 'code');
    }

    public function projet()
    {
        return $this->belongsTo(ProjetEha2::class, 'codeProjet', 'CodeProjet');
    }
}
