<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfrastructureImage extends Model
{
    use HasFactory;

    protected $table = 'infrastructureimage';

    public $timestamps = true; 
    protected $primaryKey = 'id';
    protected $fillable = [
        'infrastructure_code',
        'chemin_image',
    ];
    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class, 'infrastructure_code', 'code');
    }
    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        $v = $this->chemin_image;
        if (!$v) return null;

        // Si c’est un ID en base (entier) -> route de stream
        if (ctype_digit((string)$v)) {
            return url('/api/fichiers/'.$v);
        }

        // Sinon, fallback legacy (chemin disque ou URL)
        if (str_starts_with($v, 'http')) return $v;
        return asset($v);
    }
}
