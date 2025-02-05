<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SituationMatrimonial extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table ='situation_matrimoniale';
    protected $primaryKey ='id';
}
