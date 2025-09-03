<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenforcementProjet  extends Model
{
    use HasFactory;
    
    protected $table = 'renforcement_projet';
    public $timestamps = false;
    protected $fillable = ['renforcement_capacite', 'code_projet','created_at', 'updated_at'];
    


}
