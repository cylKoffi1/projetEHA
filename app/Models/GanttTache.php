<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanttTache extends Model
{
    use HasFactory;

/*
    protected $fillable = [
        'project_id',
        'text',
        'start_date',
        'duration',
        'progress',
        'parent',
        'priority'
    ];
*/
protected $table = 'gantt_tache';
protected $fillable = [
    'project_id', 'text', 'start_date', 'end_date', 'duration', 'progress',
    'priority', 'parent', 'order', 'open'
];
    public $timestamps = false;
}
