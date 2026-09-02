<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\PermissionRegistryInterface1;
use Modules\Core\Services\ModularPermissionRegistry1;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rejestrujemy jako singleton powiązany z interfejsem
        $this->app->singleton(PermissionRegistryInterface1::class, function ($app) {
            return new ModularPermissionRegistry1();
        });
    }
}
