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
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
            $authUser = $this->findUser($user, $provider);

            if ($authUser) {
                Auth::login($authUser, true);
                return redirect()->intended('/app');
            }

            return redirect()->route('auth');
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('InvalidStateException: ' . $e->getMessage());
            return redirect()->route('auth')->withErrors(['error' => 'Invalid state parameter.']);
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
