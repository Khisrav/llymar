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
                'can_access_admin_panel' => false,
                'can_access_app_cart' => false,
                'can_access_wholesale_factors' => false,
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
            'can_access_admin_panel' => $user->can('access admin panel'),
            'can_access_app_cart' => $user->can('access app cart'),
            'can_access_wholesale_factors' => $user->can('access wholesale-factors'),
        ];
    }
}
