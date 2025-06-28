<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AuthProvider;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        // Store the current domain in session to redirect back to it after auth
        session(['auth_return_domain' => request()->getSchemeAndHttpHost()]);
        
        // Update the Socialite configuration dynamically for current domain
        $this->updateSocialiteConfig($provider);
        
        return Socialite::driver($provider)->redirect();
    }
    
    /**
     * Update Socialite configuration with current domain redirect URL
     */
    private function updateSocialiteConfig($provider)
    {
        $currentDomain = request()->getSchemeAndHttpHost();
        
        // Validate domain for security (optional - add your trusted domains here)
        if (!$this->isTrustedDomain($currentDomain)) {
            // Fallback to main domain if not trusted
            $currentDomain = config('app.url');
        }
        
        $redirectUrl = $currentDomain . '/login/' . $provider . '/callback';
        
        config(["services.{$provider}.redirect" => $redirectUrl]);
    }
    
    /**
     * Check if domain is trusted (customize this method for your domains)
     */
    private function isTrustedDomain($domain)
    {
        // Add your trusted domains here, or implement your own validation logic
        $trustedDomains = [
            // Add your main domain and mirror domains here
            // Example: 'https://yourdomain.com', 'https://mirror1.yourdomain.com'
        ];
        
        // If no trusted domains configured, allow all (for development)
        if (empty($trustedDomains)) {
            return true;
        }
        
        return in_array($domain, $trustedDomains);
    }

    public function handleProviderCallback($provider)
    {
        try {
            // Update Socialite config for callback as well
            $this->updateSocialiteConfig($provider);
            
            $user = Socialite::driver($provider)->user();
            $authUser = $this->findUser($user, $provider);

            if ($authUser) {
                Auth::login($authUser, true);
                
                // Get the original domain from session or fallback to current domain
                $returnDomain = session('auth_return_domain', request()->getSchemeAndHttpHost());
                session()->forget('auth_return_domain');
                
                // Redirect to the same domain the user started from
                return redirect($returnDomain . '/app/history');
            }

            // If auth fails, redirect to auth page on the same domain
            $returnDomain = session('auth_return_domain', request()->getSchemeAndHttpHost());
            session()->forget('auth_return_domain');
            return redirect($returnDomain . '/auth');
            
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('InvalidStateException: ' . $e->getMessage());
            
            // Redirect to auth page on the same domain
            $returnDomain = session('auth_return_domain', request()->getSchemeAndHttpHost());
            session()->forget('auth_return_domain');
            return redirect($returnDomain . '/auth')->withErrors(['error' => 'Invalid state parameter.']);
        }
    }

    protected function findUser($socialiteUser, $provider)
    {
        $authProvider = AuthProvider::where('provider', $provider)
            ->where('provider_id', $socialiteUser->id)
            ->first();

        if ($authProvider) {
            return User::find($authProvider->user_id);
        }

        $user = User::where('email', $socialiteUser->email)->first();

        if (!$user) { return null; }

        AuthProvider::create([
            'provider'    => $provider,
            'provider_id' => $socialiteUser->id,
            'user_id'     => $user->id,
        ]);

        return $user;
    }

    // public function logout(Request $request)
    // {
    //     Auth::logout();

    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return redirect('/');
    // }
}
