<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WeightTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\DashboardTableController;

Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/', function () {

    if (!session()->has('jwt_token')) {
        return redirect()->route('login');
    }
    return redirect()->route('dashboard');
});
// Protected Routes
Route::middleware('web.jwt')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Data table page
    Route::get('/dashboard-table', [DashboardTableController::class, 'index'])->name('dashboard-table');


    // Route::get('/', function () {
    //     return view('welcome');
    // });
    // Route::get('/dashboard', [WeightTransactionController::class, 'showTable'])
    //     ->name('weight.transactions.table');
        Route::get('/dashboard', [WeightTransactionController::class, 'showTable'])->name('dashboard');
        // A4 Print
Route::get('/weight-transactions/{id}/print-a4', [WeightTransactionController::class, 'printA4'])->name('transactions.printA4');

// POS Print
Route::get('/weight-transactions/{id}/print-pos', [WeightTransactionController::class, 'printPOS'])->name('transactions.printPOS');

Route::get('/print/invoice/{id}', [PrintController::class, 'printInvoice'])->name('print.invoice');
Route::get('/print/pos/{id}', [PrintController::class, 'printPOS'])->name('print.pos');

});


