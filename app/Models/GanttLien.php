<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanttLien extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'target',
        'type'
    ];

    public $timestamps = false;
}
