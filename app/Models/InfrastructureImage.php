<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfrastructureImage extends Model
{
    use HasFactory;

    protected $table = 'InfrastructureImage';

    protected $fillable = [
        'infrastructure_code',
        'chemin_image',
    ];
    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class, 'infrastructure_code', 'code');
    }
    
    public $timestamps = true; 
}
