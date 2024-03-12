<?php


use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRolesToGroupsCommand extends Command
{
    // ...

    public function handle()
    {
        $groupes = DB::table('groupe_utilisateur')->get();

        foreach ($groupes as $groupe) {
            $role = Role::where('name', $groupe->code)->first();

            if ($role) {
                // Assurez-vous que le rôle existe avant d'attribuer
                $role->assignRoleToGroup();
            }
        }
    }
}
