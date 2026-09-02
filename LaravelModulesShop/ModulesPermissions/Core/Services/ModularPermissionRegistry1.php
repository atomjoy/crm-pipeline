<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\PermissionRegistryInterface;

class ModularPermissionRegistry implements PermissionRegistryInterface
{
    protected array $permissions = [];

    public function register(string $module, array $permissionsWithRoles): void
    {
        if (!isset($this->permissions[$module])) {
            $this->permissions[$module] = [];
        }

        foreach ($permissionsWithRoles as $action => $roles) {
            // Jeśli ktoś poda płaską tablicę ['create', 'edit'], domyślnie dajemy to adminowi
            if (is_int($action)) {
                $action = $roles;
                $roles = ['admin'];
            }

            $this->permissions[$module][$action] = array_unique($roles);
        }
    }

    public function has(string $permission): bool
    {
        if (str_contains($permission, '.')) {
            [$module, $action] = explode('.', $permission, 2);
            return isset($this->permissions[$module][$action]);
        }

        foreach ($this->permissions as $moduleActions) {
            if (isset($moduleActions[$permission])) {
                return true;
            }
        }

        return false;
    }

    public function all(): array
    {
        return $this->permissions;
    }
}
