<?php

namespace App\Models\GF;

use Illuminate\Database\Eloquent\Model;

class AchatTotal extends Model
{
    protected $table = 'gf_achats_v_totaux';
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $guarded = [];
}