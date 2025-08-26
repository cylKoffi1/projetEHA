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
        'uploaded_at',
        'fichier_id'
    ];
    
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
    protected $appends = ['url'];
    // URL unique (GridFS si fichier_id, sinon legacy asset)
    public function getUrlAttribute(): ?string
    {
        if ($this->fichier_id) {
            return url('/api/fichiers/'.$this->fichier_id);
        }
        return $this->file_path ? asset($this->file_path) : null;
    }

    
    // (facultatif) compat :
        public function getFileUrlAttribute() { return $this->url; }

        public function projet()
        {
            return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
        }
    
    
    public function getFullPathAttribute()
    {
        return public_path($this->file_path);
    }
     
}