<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanttLien extends Model
{
    use HasFactory;
    protected $table = 'gantt_lien';
/*
    protected $fillable = [
        'source',
        'target',
        'type'
    ];

*/   protected $fillable = [
        'project_id', 'source', 'target', 'type'
    ];
    public $timestamps = false;
}
