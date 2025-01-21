<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class SousMenu extends Model
{
    protected $table = 'sous_menus';
    protected $primaryKey = 'code';
    public $timestamps = false;
    protected $fillable = ['code', 'libelle','ordre', 'niveau', 'code_rubrique', 'sous_menu_parent', 'permission_id'];

    public function rubrique()
    {
        return $this->belongsTo(Rubriques::class, 'code_rubrique', 'code');
    }

    public function ecrans()
    {
        return $this->hasMany(Ecran::class, 'code_sous_menu', 'code');
    }
    public function sousSousMenus()
    {
        return $this->hasMany(SousMenu::class, 'sous_menu_parent', 'code');
    }

    // Relation pour récupérer récursivement les sous-sous-menus
    public function sousSousMenusRecursive()
    {
        return $this->hasMany(SousMenu::class, 'sous_menu_parent', 'code')
                    ->with('sousSousMenusRecursive', 'ecrans');
    }
    public function sm_parent()
    {
        return $this->belongsTo(SousMenu::class, 'sous_menu_parent', 'code');
    }
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }

}
