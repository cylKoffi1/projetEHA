<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanttTache extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'text',
        'start_date',
        'duration',
        'progress',
        'parent',
        'priority'
    ];

    public $timestamps = false;
}
