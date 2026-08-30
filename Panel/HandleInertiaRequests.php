<?php

namespace App\Http\Middleware;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            // Vue Inertia logged user details
            'auth' => [
                'user' => $request->user(),
                'role' => [
                    'superadmin' => $request->user()?->isSuperAdmin(),
                    'admin' => $request->user()?->isAdmin(),
                    'writer' => $request->user()?->isWriter(),
                ],
                'permission' => [
                    'profil_update' => $request->user()?->can('profil_update'),
                    'account_delete' => $request->user()?->can('account_delete'),
                ],
                'roles' => $request->user()?->allRoles(),
                'permissions' => $request->user()?->allPermissions(),
                'allowed_roles' => RoleEnum::cases(),
                'allowed_permissions' => PermissionEnum::cases(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                // Custom toast
                'message' => fn() => $request->session()->get('message'),
                'error'   => fn() => $request->session()->get('error'),
                // Nie trzeba tu dodawać użyj Inertia::flash() w kontrolerach
                // 'toast'   => fn() => $request->session()->get('toast'),
            ],
        ];
    }
}
