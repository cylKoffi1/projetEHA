<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepenseRealisee extends Model
{
    use HasFactory;
    protected $table = 'depenses_realisees';
    protected $primaryKey = 'code';
    public $timestamps = false;
}
