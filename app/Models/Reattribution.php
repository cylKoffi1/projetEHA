<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reattribution extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'reattributions';
    protected $primaryKey = 'code_reattribution';
    protected $fillable = [
        'code_projet',
        'code_agence',
        'code_chef',
        'changement',
        'motifs',
        'motif'
    ];

    protected $casts = [
        'motifs' => 'array'
    ];
}
