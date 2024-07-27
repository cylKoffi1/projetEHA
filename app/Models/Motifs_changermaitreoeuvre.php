<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motifs_changermaitreoeuvre extends Model
{
    use HasFactory;
    protected $table = 'motifs_changermaitreoeuvre';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
