<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
    
        if (!$user) {
            return [
                ...parent::share($request),
                'auth' => ['user' => null],
                'ziggy' => fn () => [
                    ...(new Ziggy)->toArray(),
                    'location' => $request->url(),
                ],
                'can_access_app_calculator' => false,
                'can_access_app_history' => false,
                'can_access_admin_panel' => false,
                'can_access_app_cart' => false,
                'can_access_dxf' => false,
                'can_access_factors' => false,
                'can_access_sketcher' => false,
                'can_access_users' => false,
                'can_access_commission_credits' => false,
                'user_role' => null,
            ];
        }
        
        return [
            ...parent::share($request),
            'auth' => ['user' => $user],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'can_access_app_calculator' => $user->can('access app calculator'),
            'can_access_app_history' => $user->can('access app history'),
            'can_access_admin_panel' => $user->can('access admin panel'),
            'can_access_app_cart' => $user->can('access app cart'),
            'can_access_dxf' => ($user->can('access dxf') && $user->can_access_dxf) || $user->hasRole('Super-Admin'),
            'can_access_factors' => $user->can('access factors'),
            'can_access_sketcher' => $user->can('access app sketcher'),
            'user_default_factor' => $user->default_factor ?? 'kz',
            'can_access_app_users' => ($user->can('view-any User') && $user->can('create User') && $user->can('update User') && $user->can('delete User')) || $user->hasRole('Super-Admin'),
            'can_access_commission_credits' => $user->can('access app commission-credits'),
            'user_role' => $user->roles->pluck('name')->first(),
        ];
    }
}
