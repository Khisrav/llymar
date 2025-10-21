<?php

namespace App\Http\Controllers;

use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RegistrationLinkAppController extends Controller
{
    /**
     * Display a listing of registration links
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if user has all required permissions
        if (!$this->hasAllRequiredPermissions($user)) {
            abort(403, 'У вас нет доступа к управлению ссылками регистрации');
        }

        // Build query based on user role
        $query = RegistrationLink::with(['creator:id,name,email', 'registeredUser:id,name,email']);

        // Super-Admin sees all links, others see only their created links
        if (!$user->hasRole('Super-Admin')) {
            $query->where('created_by', $user->id);
        }

        $registrationLinks = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'token' => $link->token,
                    'url' => $link->url,
                    'creator' => [
                        'id' => $link->creator->id,
                        'name' => $link->creator->name,
                        'email' => $link->creator->email,
                    ],
                    'reward_fee' => $link->reward_fee,
                    'is_used' => $link->is_used,
                    'used_at' => $link->used_at,
                    'registered_user' => $link->registered_user_id ? [
                        'id' => $link->registeredUser->id,
                        'name' => $link->registeredUser->name,
                        'email' => $link->registeredUser->email,
                    ] : null,
                    'expires_at' => $link->expires_at,
                    'created_at' => $link->created_at,
                    'is_valid' => $link->isValid(),
                    'status' => $this->getLinkStatus($link),
                ];
            });

        return Inertia::render('App/RegistrationLinks/Index', [
            'registrationLinks' => $registrationLinks,
            'canManageLinks' => true,
        ]);
    }

    /**
     * Store a newly created registration link
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$this->hasAllRequiredPermissions($user)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reward_fee' => ['required', 'numeric', 'min:0', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        try {
            DB::beginTransaction();

            $registrationLink = RegistrationLink::create([
                'created_by' => $user->id,
                'reward_fee' => $validated['reward_fee'],
                'expires_at' => $validated['expires_at'] ?? now()->addHours(24),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Ссылка регистрации успешно создана');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating registration link: ' . $e->getMessage());
            
            return redirect()->back()->withErrors(['error' => 'Ошибка при создании ссылки: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified registration link
     */
    public function update(Request $request, RegistrationLink $registrationLink)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check permissions
        if (!$this->hasAllRequiredPermissions($user)) {
            abort(403, 'Unauthorized');
        }

        // Check ownership (Super-Admin can update all, others only their own)
        if (!$user->hasRole('Super-Admin') && $registrationLink->created_by !== $user->id) {
            abort(403, 'Вы можете изменять только свои ссылки');
        }

        $validated = $request->validate([
            'reward_fee' => ['required', 'numeric', 'min:0', 'max:100'],
            // 'expires_at' => ['required', 'date'],
        ]);

        try {
            $registrationLink->update([
                'reward_fee' => $validated['reward_fee'],
                // 'expires_at' => $validated['expires_at'],
            ]);

            return redirect()->back()->with('success', 'Ссылка регистрации успешно обновлена');

        } catch (\Exception $e) {
            Log::error('Error updating registration link: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Ошибка при обновлении ссылки: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified registration link
     */
    public function destroy(RegistrationLink $registrationLink)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check permissions
        if (!$this->hasAllRequiredPermissions($user)) {
            abort(403, 'Unauthorized');
        }

        // Check ownership (Super-Admin can delete all, others only their own)
        if (!$user->hasRole('Super-Admin') && $registrationLink->created_by !== $user->id) {
            return response()->json(['message' => 'Вы можете удалять только свои ссылки'], 403);
        }

        try {
            $registrationLink->delete();
            
            return response()->json(['message' => 'Ссылка регистрации успешно удалена'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting registration link: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка при удалении ссылки: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if user has all required permissions
     */
    private function hasAllRequiredPermissions(User $user): bool
    {
        // Check if user has the required role
        if (!$user->hasAnyRole(['Super-Admin', 'Operator', 'ROP', 'Dealer'])) {
            return false;
        }

        // Additional permission checks can be added here if needed
        // For example, checking specific permissions:
        // return $user->can('view-any RegistrationLink') &&
        //        $user->can('create RegistrationLink') &&
        //        $user->can('update RegistrationLink') &&
        //        $user->can('delete RegistrationLink');

        return true;
    }

    /**
     * Get the status of a registration link
     */
    private function getLinkStatus(RegistrationLink $link): string
    {
        if ($link->is_used) {
            return 'used';
        }
        if ($link->expires_at->isPast()) {
            return 'expired';
        }
        return 'active';
    }
}

