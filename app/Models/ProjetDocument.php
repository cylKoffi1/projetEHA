<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetDocument extends Model
{
    protected $table = 'projet_documents';
    public $timestamps = false;
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'file_category',
        'code_projet',
        'uploaded_at'
    ];
    
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
    
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    
    public function getFullPathAttribute()
    {
        return public_path($this->file_path);
    }
    
    public function getFileUrlAttribute()
    {
        return asset($this->file_path);
    }
}