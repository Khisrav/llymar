<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class AppCalculatorController extends Controller
{
    public function index() {
        return Inertia::render('App/Calculator');
    }
}
