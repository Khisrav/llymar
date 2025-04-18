<?php

use App\Http\Controllers\AppCalculatorController;
use App\Http\Controllers\AppCartController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\OrderController;
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

Route::get('/dxf', function (Request $request) {
    // Parse the payload data
    $data = $request->json()->all();

    // Initialize DXFighter
    $dxf = new DXFighter();

    // Process polylines from payload
    if (isset($data['polylines'])) {
        foreach ($data['polylines'] as $polylineData) {
            $polyline = new Polyline();
            $polyline->setFlag(0, 1);
            $polyline->setColor($polylineData['color'] ?? 98);
            foreach ($polylineData['points'] as $point) {
                $polyline->addPoint([$point['x'], $point['y'], $point['z'] ?? 0]);
            }
            $dxf->addEntity($polyline);
        }
    }

    // Process texts from payload
    if (isset($data['texts'])) {
        foreach ($data['texts'] as $textData) {
            $text = new Text($textData['content'], [$textData['x'], $textData['y'], $textData['z'] ?? 0], $textData['height']);
            $dxf->addEntity($text);
        }
    }

    // Process lines from payload
    if (isset($data['lines'])) {
        foreach ($data['lines'] as $lineData) {
            $line = new Line([$lineData['start']['x'], $lineData['start']['y'], $lineData['start']['z'] ?? 0], [$lineData['end']['x'], $lineData['end']['y'], $lineData['end']['z'] ?? 0]);
            $line->setColor($lineData['color'] ?? 240);
            $dxf->addEntity($line);
        }
    }

    // Process circles from payload
    if (isset($data['circles'])) {
        Log::info($data['circles']);
        foreach ($data['circles'] as $circleData) {
            Log::info($circleData);
            $circle = new Ellipse(
                [
                    $circleData['centerPoint']['X'], 
                    $circleData['centerPoint']['Y'], 
                    $circleData['centerPoint']['z'] ?? 0
                ], [
                    $circleData['radius'] + $circleData['centerPoint']['X'], 
                    $circleData['radius'] + $circleData['centerPoint']['Y'], 
                    $circleData['centerPoint']['z'] ?? 0
                ], 1);
            $dxf->addEntity($circle);
        }
    }

    // Process rectangles from payload
    if (isset($data['rects'])) {
        foreach ($data['rects'] as $rectData) {
            $lines = [
                $rectData['line1'],
                $rectData['line2'],
                $rectData['line3'],
                $rectData['line4']
            ];
            foreach ($lines as $lineData) {
                $line = new Line([$lineData['firstPoint']['X'], $lineData['firstPoint']['Y'], 0], [$lineData['secondPoint']['X'], $lineData['secondPoint']['Y'], 0]);
                $dxf->addEntity($line);
            }
        }
    }

    // Define the file path
    $filePath = storage_path('app/output.dxf');

    // Save the DXF file
    $dxf->saveAs($filePath);

    // Return the file as a downloadable response
    return response()->download($filePath)/*->deleteFileAfterSend(true)*/;
});


/*
 *  PUBLIC ROUTES
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('auth'),
    ]);
});


Route::get('/orders/{orderId}/list-pdf', [OrderController::class, 'listPDF'])
    ->name('orders.list_pdf');
Route::post('/orders/list-pdf-from-calc', [OrderController::class, 'listFromCalcPDF'])
    ->name('orders.calc_list_pdf');
Route::post('/orders/commercial-offer', [OrderController::class, 'commercialOfferPDF'])
    ->name('orders.commercial_offer_pdf');
// Route::post('/orders/sketch', [OrderController::class, 'sketchPDF'])
//     ->name('orders.sketch_pdf');
Route::get('/orders/sketch', function() {
    // return view('orders.sketch_pdf');
    $pdf = Pdf::loadView('orders.sketch_pdf', [
        // 'openings' => $request->openings,
    ])
    ->setPaper('a4', 'portrait')
    ->setOptions(['isRemoteEnabled' => true]);

    $pdfName = "sketch_" . date('Y-m-d') . ".pdf";
    return $pdf->stream($pdfName);
})->name('orders.sketch_pdf');

Route::get('/auth', function() {
    if (Auth::check()) {
        return redirect()->route('app.home');
    }
    return Inertia::render('Auth/Index', [
        'canLogin' => Route::has('auth'),
    ]);
})->name('auth');

/*
 *  PRIVATE ROUTES
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/app', function () {
        return Inertia::render('App/Index');
    })->name('app.home');

    Route::get('/app/calculator', [AppCalculatorController::class, 'index'])->name('app.calculator');

    Route::get('/app/history', [OrderController::class, 'index'])->name('app.history');
    
    Route::get('/app/cart', [AppCartController::class, 'index'])->name('app.cart');
    
    Route::post('/app/checkout', [OrderController::class, 'store'])->name('app.checkout');
    
    Route::get('/app/account/settings', [UserController::class, 'show'])->name('app.account.settings');
    Route::post('/app/account/settings', [UserController::class, 'update'])->name('app.account.settings.update');
    
    Route::get('/app/orders/sketcher/{order_id}', [OrderController::class, 'sketcherPage'])->name('app.sketcher');
    Route::post('/app/order/sketch/download', [OrderController::class, 'sketchPDF'])->name('app.sketch');
    Route::post('/app/order/sketch/save', [OrderController::class, 'saveSketch'])->name('app.save.sketch');
    Route::delete('/app/order/{order_id}/delete', [OrderController::class, 'destroy'])->name('app.order.delete');
    
    Route::get('/app/contract', [ContractController::class, 'index'])->name('app.contract');
});

/*
 *  SOCIALITE ROUTES
 */
Route::get('/login/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/login/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

require __DIR__.'/auth.php';
