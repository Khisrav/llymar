<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictApiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the application URL from config
        $appUrl = config('app.url');
        $allowedOrigins = [
            $appUrl,
            parse_url($appUrl, PHP_URL_HOST), // Just the host without protocol
        ];

        // Check if request has origin header
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');

        // For API requests, be strict about origins
        if ($request->is('api/*')) {
            // Allow requests without origin (like direct API calls from server)
            if ($origin && !$this->isOriginAllowed($origin, $allowedOrigins)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: Invalid origin'
                ], 403);
            }

            // Also check referer as additional security
            if ($referer && !$this->isOriginAllowed($referer, $allowedOrigins)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: Invalid referer'
                ], 403);
            }
        }

        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Check if the origin is allowed
     */
    private function isOriginAllowed(string $origin, array $allowedOrigins): bool
    {
        foreach ($allowedOrigins as $allowed) {
            if (str_contains($origin, $allowed)) {
                return true;
            }
        }
        return false;
    }
}
