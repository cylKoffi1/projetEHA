<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAction extends Model
{
    use HasFactory;
    protected $table = 'ref_action_type';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
