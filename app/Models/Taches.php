<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taches extends Model
{
    use HasFactory;

    protected $table = 'taches';
    protected $appends = ['open'];

    public function getOpenAttribute()
    {
        return true;
    }

    protected $fillable = [
        'text', 'start_date', 'duration', 'progress',
        'parent', 'sortorder', 'is_deleted',
        'CodeProjet', 'type'
    ];

    protected $casts = [
        'start_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'CodeProjet');
    }
}
