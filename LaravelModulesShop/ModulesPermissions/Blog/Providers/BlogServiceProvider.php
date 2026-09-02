<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\PermissionRegistryInterface;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(PermissionRegistryInterface $registry): void
    {
        $registry->register('blog', [
            'create',
            'edit',
            'delete',
        ]);
    }
}
