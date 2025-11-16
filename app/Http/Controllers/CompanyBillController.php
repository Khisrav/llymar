<?php

namespace App\Http\Controllers;

use App\Models\CompanyBill;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyBillController extends Controller
{
    /**
     * Store a newly created company bill.
     */
    public function store(Request $request, Company $company)
    {
        $validated = $request->validate([
            'current_account' => 'required|string|max:255',
            'correspondent_account' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_address' => 'required|string|max:255',
            'bik' => 'required|string|max:255',
            'is_main' => 'boolean',
        ]);

        DB::transaction(function () use ($company, $validated) {
            // If this bill is set as main, unset any existing main bills
            if (isset($validated['is_main']) && $validated['is_main']) {
                $company->companyBills()->update(['is_main' => false]);
            }

            $company->companyBills()->create($validated);
        });

        return back()->with('success', 'Банковский счет успешно добавлен');
    }

    /**
     * Update the specified company bill.
     */
    public function update(Request $request, Company $company, CompanyBill $bill)
    {
        // Ensure bill belongs to company
        if ($bill->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'current_account' => 'required|string|max:255',
            'correspondent_account' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_address' => 'required|string|max:255',
            'bik' => 'required|string|max:255',
        ]);

        $bill->update($validated);

        return back()->with('success', 'Банковский счет успешно обновлен');
    }

    /**
     * Remove the specified company bill.
     */
    public function destroy(Company $company, CompanyBill $bill)
    {
        // Ensure bill belongs to company
        if ($bill->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }

        $bill->delete();

        return back()->with('success', 'Банковский счет успешно удален');
    }

    /**
     * Toggle the main status of a company bill.
     */
    public function toggleMain(Company $company, CompanyBill $bill)
    {
        // Ensure bill belongs to company
        if ($bill->company_id !== $company->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($company, $bill) {
            // Unset all main bills for this company
            $company->companyBills()->update(['is_main' => false]);
            
            // Set this bill as main
            $bill->update(['is_main' => true]);
        });

        return back()->with('success', 'Основной счет успешно установлен');
    }
}
