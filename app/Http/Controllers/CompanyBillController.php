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
        ]);

        $company->companyBills()->create($validated);

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
}
