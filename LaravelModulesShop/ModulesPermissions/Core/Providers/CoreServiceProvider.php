<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\PermissionRegistryInterface;
use Modules\Core\Services\ModularPermissionRegistry;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionRegistryInterface::class, function ($app) {
            return new ModularPermissionRegistry();
        });
    }
}
