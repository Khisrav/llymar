<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            return $next($request);
        }
        
        // Allow access to settings page and logo upload routes
        if ($request->routeIs('app.account.settings') || 
            $request->routeIs('app.account.settings.update') ||
            $request->routeIs('app.account.logo.upload') ||
            $request->routeIs('app.account.logo.delete')) {
            return $next($request);
        }
        
        // Check if profile is completed
        if (!$user->profile_completed) {
            return redirect()->route('app.account.settings')->with('info', 'Пожалуйста, заполните свой профиль для продолжения работы.');
        }
        
        return $next($request);
    }
}
