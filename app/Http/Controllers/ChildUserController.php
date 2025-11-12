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
                    'country' => $user->country,
                    'region' => $user->region,
                    'address' => $user->address,
                    'reward_fee' => $user->reward_fee,
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
        ];

        // Add address validation for Dealers
        if ($targetRole === 'Dealer') {
            $rules['address'] = ['required', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Generate a random password
            $plainPassword = bin2hex(random_bytes(8));

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'password' => Hash::make($plainPassword),
                'parent_id' => $user->id,
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
                        userName: $newUser->name,
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone,' . $user->id],
            'telegram' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'company' => ['required', 'string', 'max:255'],
            'reward_fee' => ['required', 'numeric', 'min:0', 'max:100'],
            // 'can_access_dxf' => ['boolean'],
            
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
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'telegram' => $validated['telegram'] ?? null,
                'country' => $validated['country'],
                'region' => $validated['region'],
                'address' => $validated['address'],
                'company' => $validated['company'],
                'reward_fee' => $validated['reward_fee'],
                
                // Requisites
                'inn' => $validated['inn'] ?? null,
                'kpp' => $validated['kpp'] ?? null,
                'current_account' => $validated['current_account'] ?? null,
                'correspondent_account' => $validated['correspondent_account'] ?? null,
                'bik' => $validated['bik'] ?? null,
                'bank' => $validated['bank'] ?? null,
                'legal_address' => $validated['legal_address'] ?? null,
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
