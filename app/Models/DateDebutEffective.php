<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateDebutEffective extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'date_debut_effective';
    protected $primaryKey = 'date';
    protected $fillable = ['date', 'code_projet', 'commentaire'];

}
