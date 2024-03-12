<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionMenerFinancier extends Model
{
    protected $table = 'action_mener_financier';
    protected $primaryKey = 'code_Action';
    public $timestamps = false;
    protected $fillable = [ 'code_projet', 'Num_ordre', 'code_bailleur'];

}


