<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function show()
    {
        return Inertia::render('App/Account/Settings', [
            'user' => Auth::user(),
        ]);
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:255'],
            'kpp' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'legal_address' => ['nullable', 'string', 'max:255'],
            'current_account' => ['nullable', 'string', 'max:255'],
            'correspondent_account' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
