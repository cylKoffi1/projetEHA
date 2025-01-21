<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class GroupeUtilisateur extends Model implements RoleContract
{
    use HasFactory, HasPermissions, RefreshesPermissionCache;

    protected $table = 'groupe_utilisateur';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guard_name = 'web';
    protected $guarded = [];
    protected $fillable = ['code', 'libelle_groupe','guard_name', 'created_at', 'updated_at'];

    public function utilisateurs()
    {
        return $this->hasMany(User::class, 'groupe_utilisateur_id', 'code');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'groupe_utilisateur_has_permissions',
            'groupe_utilisateur_id',
            'permission_id'
        );
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function revokePermissionTo(string $permissionName): void
    {
        $permission = Permission::findByName($permissionName, $this->guard_name);
        $this->permissions()->detach($permission->id);
    }

    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        $guardName = $guardName ?? 'web';
        return $this->permissions()->where('name', $permission)->where('guard_name', $guardName)->exists();
    }

    public static function findByName(string $name, ?string $guardName = null): RoleContract
    {
        $role = self::where('libelle_groupe', $name)->where('guard_name', $guardName ?? 'web')->first();
        if (!$role) {
            throw \Spatie\Permission\Exceptions\RoleDoesNotExist::named($name, $guardName ?? 'web');
        }
        return $role;
    }


    public static function findById(int|string $id, ?string $guardName = null): RoleContract
    {
        $role = self::where('code', $id)->first();
        if (!$role) {
            throw \Spatie\Permission\Exceptions\RoleDoesNotExist::withId($id, $guardName ?? 'web');
        }
        return $role;
    }

    public static function findOrCreate(string $name, ?string $guardName = null): RoleContract
    {
        $role = self::where('libelle_groupe', $name)->where('guard_name', $guardName ?? 'web')->first();
        if (!$role) {
            return self::create(['code' => $name, 'libelle_groupe' => $name, 'guard_name' => $guardName ?? 'web']);
        }
        return $role;
    }

}
