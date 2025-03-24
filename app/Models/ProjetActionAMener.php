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



}
