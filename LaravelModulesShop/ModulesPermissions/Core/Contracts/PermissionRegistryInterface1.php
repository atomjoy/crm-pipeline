<?php

namespace Modules\Core\Contracts;

interface PermissionRegistryInterface
{
    /**
     * Rejestruje uprawnienia z przypisanymi rolami domyślnymi.
     * Format: ['comment.moderate' => ['admin', 'moderator']]
     */
    public function register(string $module, array $permissionsWithRoles): void;

    /**
     * Sprawdza, czy uprawnienie zostało zarejestrowane.
     */
    public function has(string $permission): bool;

    /**
     * Zwraca całą strukturę modułów, akcji i ich ról domyślnych.
     */
    public function all(): array;
}
