<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'permissions';

    /**
     * Relation avec les groupes utilisateurs (équivalents des rôles).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            GroupeUtilisateur::class,
            'groupe_utilisateur_has_permissions',
            'permission_id',
            'groupe_utilisateur_id'
        );
    }

    /**
     * Trouver une permission par son nom et son guard.
     */
    public static function findByName(string $name, ?string $guardName = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = self::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }
        return $permission;
    }

    /**
     * Associer une permission à un groupe utilisateur (équivalent rôle).
     */
    public function assignToGroup(GroupeUtilisateur $group): void
    {
        if (!$this->roles->contains($group)) {
            $this->roles()->attach($group);
        }
    }

    /**
     * Retirer une permission d'un groupe utilisateur.
     */
    public function removeFromGroup(GroupeUtilisateur $group): void
    {
        if ($this->roles->contains($group)) {
            $this->roles()->detach($group);
        }
    }
}
