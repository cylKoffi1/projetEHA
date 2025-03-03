<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representants extends Model
{
    use HasFactory;
    protected $table = 'representants ';

    protected $primaryKey = 'id';

    protected $fillable = ['entreprise_id', 'representant_id', 'role'];

}
