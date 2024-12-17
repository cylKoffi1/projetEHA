<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupProjectPermission extends Model
{
    use HasFactory;

    protected $table = 'group_project_permissions';

    protected $fillable = [
        'role_source',
        'max_projects',
        'can_assign',
    ];

    public $timestamps = false;

    public function source()
    {
        return $this->belongsTo(GroupeUtilisateur::class, 'role_source', 'code');
    }
}
