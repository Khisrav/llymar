<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of customer companies for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user is a manager - they cannot access companies
        $userRole = $user->roles->first()?->name;
        if ($userRole === 'Manager') {
            abort(403, 'Managers cannot access companies');
        }

        // Get companies of type 'customer' belonging to the authenticated user
        $companies = Company::where('type', 'customer')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('App/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user is a manager - they cannot access companies
        $userRole = $user->roles->first()?->name;
        if ($userRole === 'Manager') {
            abort(403, 'Managers cannot access companies');
        }

        // Ensure the company belongs to the authenticated user
        if ($company->user_id !== $user->id) {
            abort(403, 'Unauthorized to view this company');
        }

        // Load company bills
        $company->load('companyBills');

        return Inertia::render('App/Companies/View', [
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user is a manager - they cannot create companies
        $userRole = $user->roles->first()?->name;
        if ($userRole === 'Manager') {
            abort(403, 'Managers cannot create companies');
        }

        $validated = $request->validate([
            'short_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:500'],
            'boss_name' => ['nullable', 'string', 'max:255'],
            'boss_title' => ['nullable', 'string', 'in:director,ceo,chief,supervisor'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'phone_2' => ['nullable', 'string', 'max:255'],
            'phone_3' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'inn' => ['required', 'integer'],
            'kpp' => ['nullable', 'integer'],
            'ogrn' => ['nullable', 'integer'],
            'vat' => ['required', 'integer', 'in:0,5,7,10,20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);

        // Add user_id, type, and warehouse_id to the validated data
        $validated['user_id'] = $user->id;
        $validated['type'] = 'customer';
        $validated['warehouse_id'] = 1; // Default warehouse

        Company::create($validated);

        return redirect()->back();
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user is a manager - they cannot update companies
        $userRole = $user->roles->first()?->name;
        if ($userRole === 'Manager') {
            abort(403, 'Managers cannot update companies');
        }

        // Ensure the company belongs to the authenticated user
        if ($company->user_id !== $user->id) {
            abort(403, 'Unauthorized to update this company');
        }

        $validated = $request->validate([
            'short_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:500'],
            'boss_name' => ['nullable', 'string', 'max:255'],
            'boss_title' => ['nullable', 'string', 'in:director,ceo,chief,supervisor'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'phone_2' => ['nullable', 'string', 'max:255'],
            'phone_3' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'inn' => ['required', 'integer'],
            'kpp' => ['nullable', 'integer'],
            'ogrn' => ['nullable', 'integer'],
            'vat' => ['required', 'integer', 'in:0,5,7,10,20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);

        $company->update($validated);

        return redirect()->back();
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user is a manager - they cannot delete companies
        $userRole = $user->roles->first()?->name;
        if ($userRole === 'Manager') {
            abort(403, 'Managers cannot delete companies');
        }

        // Ensure the company belongs to the authenticated user
        if ($company->user_id !== $user->id) {
            abort(403, 'Unauthorized to delete this company');
        }

        $company->delete();

        return redirect()->back();
    }
}
