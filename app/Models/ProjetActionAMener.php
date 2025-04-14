<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetActionAMener extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'projet_action_a_mener'; // Nom de la table
    protected $primaryKey = 'code';
    protected $fillable = [
        'code',
        'code_projet',
        'Num_ordre',
        'Action_mener',
        'Quantite',
        'Infrastrucrues_id',
    ];

    public function beneficiairesActeurs()
    {
        return $this->hasMany(Beneficier::class, 'code_projet', 'code_projet');
    }

    public function beneficiairesLocalites()
    {
        return $this->hasMany(Profiter::class, 'code_projet', 'code_projet');
    }

    public function beneficiairesInfrastructures()
    {
        return $this->hasMany(Jouir::class, 'code_projet', 'code_projet');
    }
    public function beneficiaires()
    {
        return collect()
            ->merge($this->beneficiairesActeurs)
            ->merge($this->beneficiairesLocalites)
            ->merge($this->beneficiairesInfrastructures);
    }

}
