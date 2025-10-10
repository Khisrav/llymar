<?php

namespace App\Http\Controllers;

use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RegistrationLinkController extends Controller
{
    /**
     * Show the registration form
     */
    public function show(string $token)
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();

        if (!$link->isValid()) {
            return Inertia::render('Auth/RegistrationLinkExpired', [
                'isUsed' => $link->is_used,
                'expiredAt' => $link->expires_at,
            ]);
        }

        return Inertia::render('Auth/RegisterViaLink', [
            'token' => $token,
            'expiresAt' => $link->expires_at,
        ]);
    }

    /**
     * Handle the registration
     */
    public function register(Request $request, string $token)
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();

        if (!$link->isValid()) {
            return response()->json([
                'message' => 'Ссылка регистрации недействительна или уже использована',
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'company' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            
            // Requisites (optional)
            'inn' => ['nullable', 'string', 'max:12'],
            'kpp' => ['nullable', 'string', 'max:9'],
            'current_account' => ['nullable', 'string', 'max:20'],
            'correspondent_account' => ['nullable', 'string', 'max:20'],
            'bik' => ['nullable', 'string', 'max:9'],
            'bank' => ['nullable', 'string', 'max:255'],
            'legal_address' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'telegram' => $validated['telegram'],
                'country' => $validated['country'],
                'region' => $validated['region'],
                'address' => $validated['address'],
                'company' => $validated['company'],
                'password' => Hash::make($validated['password']),
                'parent_id' => $link->created_by,
                'reward_fee' => $link->reward_fee,
                'default_factor' => 'kz',
                
                // Requisites
                'inn' => $validated['inn'] ?? null,
                'kpp' => $validated['kpp'] ?? null,
                'current_account' => $validated['current_account'] ?? null,
                'correspondent_account' => $validated['correspondent_account'] ?? null,
                'bik' => $validated['bik'] ?? null,
                'bank' => $validated['bank'] ?? null,
                'legal_address' => $validated['legal_address'] ?? null,
            ]);

            // Assign Dealer role
            $dealerRole = Role::where('name', 'Dealer')->first();
            if ($dealerRole) {
                $user->assignRole($dealerRole);
            }

            // Set DXF access if enabled
            if ($link->can_access_dxf) {
                $user->givePermissionTo('access dxf');
            }

            // Mark link as used
            $link->markAsUsed($user);

            DB::commit();

            return response()->json([
                'message' => 'Регистрация успешно завершена!',
                'redirect' => route('login'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Ошибка при регистрации: ' . $e->getMessage(),
            ], 500);
        }
    }
}
