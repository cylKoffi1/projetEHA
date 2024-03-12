<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class RegisterPermissions extends Command
{
    protected $signature = 'register:permissions';
    protected $description = 'Register permissions for each table in the database';

    public function handle()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            // Ajoutez les permissions nécessaires pour chaque table
            $this->registerTablePermissions($table);
        }

        $this->info('Permissions registered successfully.');
    }

    private function registerTablePermissions($table)
    {
        // Définissez les permissions pour chaque table
        $permissions = [
            "edit {$table}",
            "list {$table}",
            "delete {$table}",
            "add {$table}",
            // Ajoutez d'autres permissions au besoin
        ];

        // Enregistrez les permissions dans la table 'permissions' de Spatie
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
