<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Rubriques extends Model
{
    protected $table = 'rubriques';
    protected $primaryKey = 'code';
    public $timestamps = false;

    public function sousMenus()
    {
        return $this->hasMany(SousMenu::class, 'code_rubrique', 'code');
    }
    public function ecrans()
    {
        return $this->hasMany(Ecran::class, 'code_rubrique', 'code');
    }
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }

}
