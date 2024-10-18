<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Links extends Model
{
    use HasFactory;

    // Table associée au modèle
    protected $table = 'links';
protected $fillable = ['source', 'target', 'type', 'is_deleted', 'CodeProjet'];
}
