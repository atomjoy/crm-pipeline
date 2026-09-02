<?php

namespace Modules\Community\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\PermissionRegistryInterface;

class CommunityServiceProvider1 extends ServiceProvider
{
    public function boot(PermissionRegistryInterface $registry): void
    {
        // Moduł Community sam definiuje uprawnienia i przypisuje role
        $registry->register('community', [
            'comment.view'     => ['admin', 'moderator', 'user'],
            'comment.moderate' => ['admin', 'moderator'], // Twoje customowe uprawnienie
            'comment.delete'   => ['admin'],
        ]);
    }
}
