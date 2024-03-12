<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Ecran extends Model
{
    protected $table = 'views';
    protected $primaryKey = 'id'; // Assurez-vous que le nom de la clé primaire est correct
    public $timestamps = false;

    public function sousMenu()
    {
        return $this->belongsTo(SousMenu::class, 'code_sous_menu', 'code');
    }
    public function rubrique()
    {
        return $this->belongsTo(Rubriques::class, 'code_rubrique', 'code');
    }
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }

}
