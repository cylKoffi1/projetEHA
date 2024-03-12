<?php 

// app/Models/View.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    protected $table = 'views';

    protected $fillable = ['path'];

    // Ajoutez d'autres propriétés ou méthodes au besoin
}
