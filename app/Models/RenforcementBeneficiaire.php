<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenforcementBeneficiaire extends Model
{
    use HasFactory;
    protected $table = 'renforcement_beneficiaire';
    protected $fillable = ['renforcement_capacite', 'code_acteur','created_at', 'updated_at'];
    
}
