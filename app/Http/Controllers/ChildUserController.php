<?php

namespace App\Http\Controllers;

use App\Mail\UserCredentialsMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class ChildUserController extends Controller
{
    /**
     * Display a listing of child users
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user || !$user->can('access app users')) {
            abort(403, 'Unauthorized');
        }

        $childUsers = User::where('parent_id', $user->id)
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'telegram' => $user->telegram,
                    'company' => $user->company,
                    'website' => $user->website,
                    'country' => $user->country,
                    'region' => $user->region,
                    'city' => $user->city,
                    'address' => $user->address,
                    'reward_fee' => $user->reward_fee,
                    'private_note' => $user->private_note,
                    // 'can_access_dxf' => $user->can('access dxf'),
                    'role' => $user->roles->first()?->display_name ?? $user->roles->first()?->name,
                    'created_at' => $user->created_at,
                ];
            });

        return Inertia::render('App/Users/Index', [
            'childUsers' => $childUsers,
            'canManageUsers' => true,
            'userRole' => $user->roles->first()?->name ?? '',
        ]);
    }

    /**
     * Store a newly created child user
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->hasAnyRole(['Super-Admin', 'Operator', 'ROP', 'Dealer', 'Dealer Ch', 'Dealer R'])) {
            abort(403, 'Unauthorized');
        }

        // Determine which role to assign based on parent's role
        $parentRole = $user->roles->first()?->name;
        $targetRole = null;
        
        // Role assignment logic
        if ($parentRole === 'Dealer' || $parentRole === 'Dealer Ch') {
            $targetRole = 'Manager';
        } elseif ($parentRole === 'ROP' || $parentRole === 'Operator') {
            $targetRole = 'Dealer';
        } elseif ($parentRole === 'Dealer R' || $parentRole === 'Super-Admin') {
            // These roles can choose, so get from request
            $targetRole = $request->input('role');
            if (!in_array($targetRole, ['Dealer', 'Manager'])) {
                return redirect()->back()->withErrors(['error' => 'Недопустимая роль']);
            }
        } else {
            abort(403, 'Unauthorized to create users');
        }

        // Base validation rules
        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Generate an 8-character random password with letters and numbers
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $plainPassword = '';
            for ($i = 0; $i < 8; $i++) {
                $plainPassword .= $characters[random_int(0, strlen($characters) - 1)];
            }

            $newUser = User::create([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'website' => $validated['website'] ?? null,
                'password' => Hash::make($plainPassword),
                'parent_id' => $user->id,
                'profile_completed' => false,
            ]);

            // Assign the determined role
            $role = Role::where('name', $targetRole)->first();
            if ($role) {
                $newUser->assignRole($role);
            }

            // Send credentials email to the new user
            try {
                Mail::to($newUser->email)->send(
                    new UserCredentialsMail(
                        userName: $newUser->name ?? $newUser->email,
                        userEmail: $newUser->email,
                        userPassword: $plainPassword,
                        loginUrl: url('/login')
                    )
                );
            } catch (\Exception $mailException) {
                // Log the error but don't fail the user creation
                Log::warning('Failed to send credentials email to user: ' . $newUser->email, [
                    'error' => $mailException->getMessage()
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Пользователь успешно создан');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->withErrors(['error' => 'Ошибка при создании пользователя: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified child user
     */
    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();

        if (!$authUser || $user->parent_id !== $authUser->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone,' . $user->id],
            'telegram' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'company' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'reward_fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'private_note' => ['nullable', 'string', 'max:1000'],
            // 'can_access_dxf' => ['boolean'],
        ]);

        try {
            $user->update([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'telegram' => $validated['telegram'] ?? null,
                'country' => $validated['country'] ?? null,
                'region' => $validated['region'] ?? null,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'company' => $validated['company'] ?? null,
                'website' => $validated['website'] ?? null,
                'reward_fee' => $validated['reward_fee'] ?? 0,
                'private_note' => $validated['private_note'] ?? null,
            ]);

            // Update DXF access
            // if ($validated['can_access_dxf'] ?? false) {
            //     $user->givePermissionTo('access dxf');
            // } else {
            //     $user->revokePermissionTo('access dxf');
            // }

            return redirect()->back()->with('success', 'Пользователь успешно обновлен');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ошибка при обновлении пользователя: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified child user
     */
    public function destroy(User $user)
    {
        $authUser = Auth::user();

        if (!$authUser || $user->parent_id !== $authUser->id) {
            abort(403, 'Unauthorized');
        }

        try {
            DB::beginTransaction();

            // Set parent_id to null for all child users
            $user->children()->update(['parent_id' => null]);

            $user->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Пользователь успешно удален'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка при удалении пользователя: ' . $e->getMessage()], 500);
        }
    }
}
