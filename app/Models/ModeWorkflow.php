<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeWorkflow extends Model
{
    use HasFactory;
    protected $table = 'ref_workflow_mode';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
