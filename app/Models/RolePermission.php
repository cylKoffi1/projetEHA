<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';

    protected $fillable = [
        'role_source',
        'role_target',
        'can_assign',
    ];

    public $timestamps = false;

    // Relations pour récupérer les libellés des rôles
    public function source()
    {
        return $this->belongsTo(GroupeUtilisateur::class, 'role_source', 'code');
    }

    public function target()
    {
        return $this->belongsTo(GroupeUtilisateur::class, 'role_target', 'code');
    }
}
