<?php

namespace Modules\Core\Contracts;

interface PermissionRegistryInterface
{
    /**
     * Rejestracja uprawnień dla konkretnego modułu.
     */
    public function register(string $module, array $permissions): void;

    /**
     * Sprawdzenie, czy uprawnienie istnieje (np. w formacie "blog.create").
     */
    public function has(string $permission): bool;

    /**
     * Pobranie wszystkich uprawnień pogrupowanych po modułach.
     */
    public function all(): array;
}
