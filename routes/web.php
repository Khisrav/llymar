<?php

use App\Http\Controllers\AppCalculatorController;
use App\Http\Controllers\AppCartController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\CommercialOfferController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SketchController;
use App\Http\Controllers\UserController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

use DXFighter\DXFighter;
use DXFighter\lib\Polyline;
use DXFighter\lib\Text;
use DXFighter\lib\Line;
use DXFighter\lib\Ellipse;
use DXFighter\lib\Circle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/*
 *  PUBLIC ROUTES
 */
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');

Route::get('/', function () {
    // Get landing page options
    $options = \App\Models\LandingPageOption::orderBy('group')->orderBy('order')->get();
    $optionsMap = [];
    foreach ($options as $option) {
        $optionsMap[$option->key] = $option->value;
    }
    
    // Get latest 8 portfolios for carousel
    $portfolios = \App\Models\Portfolio::latest()
        ->take(8)
        ->get([
            'id',
            'title',
            'description',
            'images',
            'area',
            'color',
            'glass',
            'location',
            'year',
            'created_at'
        ]);
    
    // Get active hero carousels
    $heroCarousels = \App\Models\HeroCarousel::active()->ordered()->get();
    
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('auth'),
        'landingOptions' => $optionsMap,
        'initialPortfolio' => $portfolios,
        'heroCarousels' => $heroCarousels,
    ]);
});

Route::get('/about-glazing-system', function () {
    return Inertia::render('AboutGlazingSystem');
});

// News routes
Route::get('/articles', [NewsController::class, 'index'])->name('news.index');
Route::get('/articles/{news:slug}', [NewsController::class, 'show'])->name('news.show');

// Portfolio routes
Route::get('/portfolio', function () {
    $portfolios = \App\Models\Portfolio::latest()->get([
        'id',
        'title',
        'description',
        'images',
        'area',
        'color',
        'glass',
        'location',
        'year',
        'created_at'
    ]);
    
    return Inertia::render('Portfolio/Index', [
        'portfolios' => $portfolios,
    ]);
})->name('portfolio.index');

Route::get('/portfolio/{portfolio}', function (App\Models\Portfolio $portfolio) {
    return Inertia::render('Portfolio/Show', [
        'portfolio' => $portfolio
    ]);
})->name('portfolio.show');

Route::get('/orders/{orderId}/list-pdf', [OrderController::class, 'listPDF'])
    ->name('orders.list_pdf');
Route::post('/orders/list-pdf-from-calc', [OrderController::class, 'listFromCalcPDF'])
    ->name('orders.calc_list_pdf');
Route::post('/orders/simple-list-from-calc', [OrderController::class, 'simpleListFromCalcPDF'])
    ->name('orders.simple_list_from_calc');
Route::post('/orders/commercial-offer', [CommercialOfferController::class, 'commercialOfferPDF'])
    ->name('orders.commercial_offer_pdf');
Route::post('/orders/sketch', [OrderController::class, 'sketchPDF'])
    ->name('orders.sketch_pdf');
Route::get('/orders/{order}/download-bill', [OrderController::class, 'downloadBill'])
    ->name('orders.download_bill');
Route::post('/orders/{order_id}/dxf', [SketchController::class, 'generateDXF']);
// Route::get('/orders/sketch', function() {
//     // return view('orders.sketch_pdf');
//     $pdf = Pdf::loadView('orders.sketch_pdf', [
//         // 'openings' => $request->openings,
//     ])
//     ->setPaper('a4', 'portrait')
//     ->setOptions(['isRemoteEnabled' => true]);

//     $pdfName = "sketch_" . date('Y-m-d') . ".pdf";
//     return $pdf->stream($pdfName);
// })->name('orders.sketch_pdf');

Route::get('/auth', function() {
    if (Auth::check()) {
        return redirect()->route('app.history');
    }
    return Inertia::render('Auth/Index', [
        'canLogin' => Route::has('auth'),
    ]);
})->name('auth');

/*
 *  PRIVATE ROUTES
 */
Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::get('/app', function () {
        return Inertia::render('App/Index');
    })->name('app.home');

    Route::get('/app/calculator', [AppCalculatorController::class, 'index'])->name('app.calculator');

    Route::get('/app/history', [OrderController::class, 'index'])->name('app.history');
    Route::get('/app/orders/{order}', [OrderController::class, 'show'])->name('app.orders.show');
    Route::put('/app/orders/{order}', [OrderController::class, 'update'])->name('app.orders.update');
    
    Route::get('/app/commercial-offers', [CommercialOfferController::class, 'index'])->name('app.commercial_offers');
    Route::post('/app/commercial-offers', [CommercialOfferController::class, 'store'])->name('app.commercial_offers.store');
    Route::put('/app/commercial-offers/{commercialOffer}', [CommercialOfferController::class, 'update'])->name('app.commercial_offers.update');
    Route::patch('/app/commercial-offers/{commercialOffer}/file-name', [CommercialOfferController::class, 'updateFileName'])->name('app.commercial_offers.update_file_name');
    Route::delete('/app/commercial-offers/{commercialOffer}', [CommercialOfferController::class, 'destroy'])->name('app.commercial_offers.delete');
    Route::get('/app/commercial-offers/{commercialOffer}/pdf', [CommercialOfferController::class, 'downloadPDF'])->name('app.commercial_offers.pdf');
    
    Route::get('/app/cart', [AppCartController::class, 'index'])->name('app.cart');
    Route::post('/app/cart/confirm', [AppCartController::class, 'index_order_confirmation'])->name('app.cart.confirm');
    Route::get('/app/cart/confirm', function () {
        //redirect to calculator
        return redirect()->route('app.calculator');
    })->name('app.cart.confirm-redirect');
    // Route::post('/app/checkout', [OrderController::class, 'store'])->name('app.checkout');
    Route::post('/app/checkout', [AppCartController::class, 'store'])->name('app.checkout');
    
    Route::get('/app/account/settings', [UserController::class, 'show'])->name('app.account.settings');
    Route::post('/app/account/settings', [UserController::class, 'update'])->name('app.account.settings.update');
    Route::post('/app/account/logo/upload', [UserController::class, 'uploadLogo'])->name('app.account.logo.upload');
    Route::delete('/app/account/logo/delete', [UserController::class, 'deleteLogo'])->name('app.account.logo.delete');
    
    // Company Management Routes
    Route::get('/app/companies', [App\Http\Controllers\CompanyController::class, 'index'])->name('app.companies');
    Route::get('/app/companies/{company}', [App\Http\Controllers\CompanyController::class, 'show'])->name('app.companies.show');
    Route::post('/app/companies', [App\Http\Controllers\CompanyController::class, 'store'])->name('app.companies.store');
    Route::put('/app/companies/{company}', [App\Http\Controllers\CompanyController::class, 'update'])->name('app.companies.update');
    Route::delete('/app/companies/{company}', [App\Http\Controllers\CompanyController::class, 'destroy'])->name('app.companies.destroy');
    Route::post('/app/companies/{company}/toggle-main', [App\Http\Controllers\CompanyController::class, 'toggleMain'])->name('app.companies.toggle_main');
    
    // Company Bills Management Routes
    Route::post('/app/companies/{company}/bills', [App\Http\Controllers\CompanyBillController::class, 'store'])->name('app.companies.bills.store');
    Route::put('/app/companies/{company}/bills/{bill}', [App\Http\Controllers\CompanyBillController::class, 'update'])->name('app.companies.bills.update');
    Route::delete('/app/companies/{company}/bills/{bill}', [App\Http\Controllers\CompanyBillController::class, 'destroy'])->name('app.companies.bills.destroy');
    
    Route::get('/app/orders/sketcher/{order_id}', [OrderController::class, 'sketcherPage'])->name('app.sketcher');
    Route::post('/app/order/sketch/download', [OrderController::class, 'sketchPDF'])->name('app.sketch');
    Route::post('/app/order/sketch/save', [OrderController::class, 'saveSketch'])->name('app.save.sketch');
    Route::get('/app/orders/{order_id}/sketch-pdf', [OrderController::class, 'downloadSketchPDF'])->name('app.sketch.pdf');
    Route::get('/app/orders/{order_id}/sketch-dxf', [SketchController::class, 'downloadSketchDXF'])->name('app.sketch.dxf');
    Route::delete('/app/order/{order_id}/delete', [OrderController::class, 'destroy'])->name('app.order.delete');
    
    Route::get('/app/contract', [ContractController::class, 'index'])->name('app.contract');
    
    // Commission Credits Routes
    Route::get('/app/commission-credits', [App\Http\Controllers\CommissionCreditController::class, 'index'])->name('app.commission_credits');
    
    // Child Users Management Routes
    Route::get('/app/users', [App\Http\Controllers\ChildUserController::class, 'index'])->name('app.users');
    Route::post('/app/users', [App\Http\Controllers\ChildUserController::class, 'store'])->name('app.users.store');
    Route::put('/app/users/{user}', [App\Http\Controllers\ChildUserController::class, 'update'])->name('app.users.update');
    Route::delete('/app/users/{user}', [App\Http\Controllers\ChildUserController::class, 'destroy'])->name('app.users.destroy');
});

/*
 *  SOCIALITE ROUTES
 */
Route::get('/login/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/login/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

require __DIR__.'/auth.php';
