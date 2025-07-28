<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

/*
 *  WEBHOOK ROUTES
 */
Route::post('/webhook/tochka', function (Request $request) {
    $tochkaService = new \App\Services\TochkaBankService();
    $tochkaService->receiveWebhook($request);
    return response()->json(['status' => 'ok']);
})->name('webhook.tochka');
