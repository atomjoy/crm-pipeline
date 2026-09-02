<?php

namespace Modules\Core\Services;

use Modules\Core\Contracts\PermissionRegistryInterface;

class ModularPermissionRegistry implements PermissionRegistryInterface
{
    protected array $permissions = [];

    public function register(string $module, array $permissions, array $defaultRoles = ['admin']): void
    {
        if (!isset($this->permissions[$module])) {
            $this->permissions[$module] = [];
        }

        $this->permissions[$module] = array_unique(
            array_merge($this->permissions[$module], $permissions)
        );
    }

    public function has(string $permission): bool
    {
        if (str_contains($permission, '.')) {
            [$module, $action] = explode('.', $permission, 2);
            return isset($this->permissions[$module]) && in_array($action, $this->permissions[$module]);
        }

        foreach ($this->permissions as $modulePermissions) {
            if (in_array($permission, $modulePermissions)) {
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
