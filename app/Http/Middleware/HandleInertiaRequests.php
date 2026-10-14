<?php

namespace App\Http\Middleware;

use App\Http\Middleware\TrackUserActivity;
use App\Support\PriceFactor;
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

        if ($user) {
            TrackUserActivity::recordForUser($user);
        }
    
        if (!$user) {
            return [
                ...parent::share($request),
                'auth' => ['user' => null],
                'ziggy' => fn () => [
                    ...(new Ziggy)->toArray(),
                    'location' => $request->url(),
                ],
            ];
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map(fn ($role) => ['name' => $role->name])->values(),
                ],
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'can_access_app_calculator' => $user->can('access app calculator'),
            'can_access_app_history' => $user->can('access app history'),
            'can_access_admin_panel' => $user->can('access admin panel'),
            'can_access_app_cart' => $user->can('access app cart'),
            // 'can_access_dxf' => ($user->can('access dxf') && $user->can_access_dxf) || $user->hasRole('Super-Admin'),
            'can_access_factors' => $user->can('access factors'),
            'can_access_sketcher' => $user->can('access app sketcher'),
            'user_default_factor' => PriceFactor::normalize($user->default_factor),
            'factor_ranges' => PriceFactor::ranges(),
            'can_access_app_users' => $user->can('access app users'),
            'can_access_commission_credits' => $user->can('access app commission-credits'),
            'can_access_companies' => !$user->hasRole('Manager'),
            'user_role' => $user->roles->pluck('name')->first(),
        ];
    }
}
