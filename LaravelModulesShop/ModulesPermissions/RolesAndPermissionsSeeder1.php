<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Contracts\PermissionRegistryInterface;
use Modules\Users\Models\Permission;
use Modules\Users\Models\Role;

class RolesAndPermissionsSeeder1 extends Seeder
{
    protected PermissionRegistryInterface $registry;

    public function __construct(PermissionRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function run(): void
    {
        // Upewniamy się, że podstawowe role istnieją w bazie danych
        $rolesCache = [
            'admin'     => Role::updateOrCreate(['slug' => 'admin'], ['name' => 'Administrator']),
            'moderator' => Role::updateOrCreate(['slug' => 'moderator'], ['name' => 'Moderator']),
            'user'      => Role::updateOrCreate(['slug' => 'user'], ['name' => 'Użytkownik']),
        ];

        // Przygotowujemy koszyki na ID uprawnień dla każdej roli
        $rolePermissionsMap = [];
        foreach ($rolesCache as $slug => $roleModel) {
            $rolePermissionsMap[$roleModel->id] = [];
        }

        // Pobieramy absolutnie wszystko, co zebrał nasz singleton
        $modulesStructure = $this->registry->all();

        foreach ($modulesStructure as $moduleName => $actions) {
            foreach ($actions as $actionName => $targetRoles) {
                $fullName = $moduleName . '.' . $actionName;

                // Zapisujemy uprawnienie do bazy (np. "community.comment.moderate")
                $permission = Permission::updateOrCreate(
                    ['name' => $fullName],
                    ['module' => $moduleName, 'action' => $actionName]
                );

                // Dorzucamy to uprawnienie do odpowiednich ról w pamięci
                foreach ($targetRoles as $roleSlug) {
                    if (isset($rolesCache[$roleSlug])) {
                        $roleId = $rolesCache[$roleSlug]->id;
                        $rolePermissionsMap[$roleId][] = $permission->id;
                    }
                }
            }
        }

        // Synchronizujemy relacje wiele-do-wielu w bazie danych
        foreach ($rolesCache as $roleModel) {
            $roleModel->permissions()->sync($rolePermissionsMap[$roleModel->id]);
        }
    }
}
