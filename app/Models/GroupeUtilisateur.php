<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Spatie\Permission\Models\Permission;

class GroupeUtilisateur extends Model implements RoleContract
{
    use HasFactory, HasRoles, RefreshesPermissionCache;

    protected $table = 'groupe_utilisateur';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guard_name = 'web';
    protected $fillable = ['code', 'libelle_groupe', 'guard_name', 'type_utilisateur_id','created_at', 'updated_at'];

    /**
     * ✅ Déclaration correcte pour la compatibilité avec Spatie
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }
    public function typeUtilisateur()
    {
        return $this->belongsTo(TypeUtilisateur::class, 'type_utilisateur_id');
    }

    /**
     * ✅ Assigner une permission à un groupe utilisateur (équivalent rôle)
     */
   /* public function givePermissionTo($permission)
    {
        $permissionModel = Permission::findByName($permission);
        if ($permissionModel && !$this->hasPermissionTo($permission)) {
            $this->permissions()->attach($permissionModel->id);
        }
    }*/

    /**
     * ✅ Révoquer une permission d'un groupe utilisateur
     */
    /*public function revokePermissionTo($permission)
    {
        $permissionModel = Permission::findByName($permission);
        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }*/

    /**
     * ✅ Vérifier si un groupe utilisateur a une permission
     */
    /*public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }*/

    /**
     * ✅ Trouver un rôle par son nom
     */
    public static function findByName(string $name, ?string $guardName = null): RoleContract
    {
        $role = self::where('libelle_groupe', $name)->where('guard_name', $guardName ?? 'web')->first();
        if (!$role) {
            throw \Spatie\Permission\Exceptions\RoleDoesNotExist::create($name);
        }
        return $role;
    }

    /**
     * ✅ Trouver un rôle par son ID
     */
    public static function findById(int|string $id, ?string $guardName = null): RoleContract
    {
        $role = self::where('code', $id)->first();
        if (!$role) {
            throw \Spatie\Permission\Exceptions\RoleDoesNotExist::withId($id, $guardName ?? 'web');
        }
        return $role;
    }

    /**
     * ✅ Trouver ou créer un rôle
     */
    public static function findOrCreate(string $name, ?string $guardName = null): RoleContract
    {
        return self::firstOrCreate(['code' => $name, 'libelle_groupe' => $name, 'guard_name' => $guardName ?? 'web']);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'groupe_utilisateur_id', 'code');
    }
}
