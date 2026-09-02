<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Contracts\PermissionRegistryInterface;
use Modules\Users\Models\User;

class UserController extends Controller
{
    public function edit(User $user, PermissionRegistryInterface $registry)
    {
        // Add permissions example
        // $registry->register('invoices', ['create', 'view', 'download']);

        // Pobiera tablicę: ['blog' => ['create', 'edit'], 'shop' => ['manage-products']]
        $allPermissionsByModule = $registry->all();

        // Pobiera uprawnienia, które użytkownik już posiada (np. z bazy danych)
        $userPermissions = $user->permissions()->pluck('name')->toArray();

        return view('users::edit', [
            'user' => $user,
            'modules' => $allPermissionsByModule,
            'userPermissions' => $userPermissions
        ]);
    }
}
