<?php

namespace App\Http\Controllers;

use App\Models\Company;
use ContractTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContractController extends Controller
{
    public function index() {
        return Inertia::render('App/Contract', [
            'company_performers' => Company::all(),
            'templates' => ContractTemplate::all(),
        ]);
    }
}
