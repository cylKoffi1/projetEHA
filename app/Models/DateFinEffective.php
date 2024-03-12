<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateFinEffective extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'date_fin_effective';
    protected $primaryKey = 'date';


    protected $fillable = ['date', 'code_projet', 'commentaire', 'cout_effectif', 'devise'];
}
