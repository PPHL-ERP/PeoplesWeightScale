<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeightTransactionController;
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
use App\Http\Controllers\WVendorController;
use App\Http\Controllers\WCustomerController;
use App\Http\Controllers\WMaterialController;

// 🔽 RBAC management controllers (UI)
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserRoleController; // (users ↔ roles assign UI)

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

 //weight_transactions
 Route::get('/weight_transactions', [WeightTransactionController::class, 'index'])->name('weight_transactions.index');
 Route::get('/weight_transactions/create', [WeightTransactionController::class, 'create'])->name('weight_transactions.create');
 Route::post('/weight_transactions', [WeightTransactionController::class, 'store'])->name('weight_transactions.store');
 Route::get('/weight_transactions/{id}/edit', [WeightTransactionController::class, 'edit'])->name('weight_transactions.edit');
 Route::get('/weight_transactions/{id}', [WeightTransactionController::class, 'show'])->name('weight_transactions.show');
 Route::put('/weight_transactions/{id}', [WeightTransactionController::class, 'update'])->name('weight_transactions.update');
 Route::delete('/weight_transactions/{id}', [WeightTransactionController::class, 'destroy'])->name('weight_transactions.destroy');

 //w_vendor
 Route::get('/w_vendors', [WVendorController::class, 'index'])->name('w_vendors.index');
 Route::get('/w_vendors/create', [WVendorController::class, 'create'])->name('w_vendors.create');
 Route::post('/w_vendors', [WVendorController::class, 'store'])->name('w_vendors.store');
 Route::get('/w_vendors/{id}/edit', [WVendorController::class, 'edit'])->name('w_vendors.edit');
 Route::get('/w_vendors/{id}', [WVendorController::class, 'show'])->name('w_vendors.show');
 Route::put('/w_vendors/{id}', [WVendorController::class, 'update'])->name('w_vendors.update');
 Route::delete('/w_vendors/{id}', [WVendorController::class, 'destroy'])->name('w_vendors.destroy');

//w_customer
Route::get('/w_customers', [WCustomerController::class, 'index'])->name('w_customers.index')->middleware('permission:customers.view');
Route::get('/w_customers/create', [WCustomerController::class, 'create'])->name('w_customers.create')->middleware('permission:customers.create');
Route::post('/w_customers', [WCustomerController::class, 'store'])->name('w_customers.store')->middleware('permission:customers.create');
Route::get('/w_customers/{id}/edit', [WCustomerController::class, 'edit'])->name('w_customers.edit')->middleware('permission:customers.edit');
Route::get('/w_customers/{id}', [WCustomerController::class, 'show'])->name('w_customers.show')->middleware('permission:customers.view');
Route::put('/w_customers/{id}', [WCustomerController::class, 'update'])->name('w_customers.update')->middleware('permission:customers.edit');
Route::delete('/w_customers/{id}', [WCustomerController::class, 'destroy'])->name('w_customers.destroy')->middleware('permission:customers.delete');

//w_material
Route::get('/w_materials', [WMaterialController::class, 'index'])->name('w_materials.index')->middleware('permission:materials.view');
Route::get('/w_materials/create', [WMaterialController::class, 'create'])->name('w_materials.create')->middleware('permission:materials.create');
Route::post('/w_materials', [WMaterialController::class, 'store'])->name('w_materials.store')->middleware('permission:materials.create');
Route::get('/w_materials/{id}/edit', [WMaterialController::class, 'edit'])->name('w_materials.edit')->middleware('permission:materials.edit');
Route::get('/w_materials/{id}', [WMaterialController::class, 'show'])->name('w_materials.show')->middleware('permission:materials.view');
Route::put('/w_materials/{id}', [WMaterialController::class, 'update'])->name('w_materials.update')->middleware('permission:materials.edit');
Route::delete('/w_materials/{id}', [WMaterialController::class, 'destroy'])->name('w_materials.destroy')->middleware('permission:materials.delete');


    // Route::get('/', function () {
    //     return view('welcome');
    // });
    // Route::get('/dashboard', [WeightTransactionController::class, 'showTable'])
    //     ->name('weight.transactions.table');
// Route::get('/dashboard', [WeightTransactionController::class, 'showTable'])->name('dashboard');
        // A4 Print
Route::get('/weight-transactions/{id}/print-a4', [WeightTransactionController::class, 'printA4'])->name('transactions.printA4');

// POS Print
Route::get('/weight-transactions/{id}/print-pos', [WeightTransactionController::class, 'printPOS'])->name('transactions.printPOS');

Route::get('/print/invoice/{id}', [PrintController::class, 'printInvoice'])->name('print.invoice');
Route::get('/print/pos/{id}', [PrintController::class, 'printPOS'])->name('print.pos');


/* =========================
     * RBAC Management (Admin only)
     * ========================= */
    // Route::middleware('role:Admin')->group(function () {
    //     // Roles CRUD (show ছাড়া)
    //     Route::resource('roles', RoleController::class)->except(['show']);

    //     // Permissions CRUD (index/create/store/edit/update/destroy)
    //     Route::resource('permissions', PermissionController::class)
    //         ->only(['index','create','store','edit','update','destroy']);

    //     // Users ↔ Roles assignment UI (উদাহরণ)
    //     Route::get('/users/{id}/roles', [UserRoleController::class, 'edit'])
    //         ->name('users.roles.edit');
    //     Route::put('/users/{id}/roles', [UserRoleController::class, 'update'])
    //         ->name('users.roles.update');
    // });

// ---- DEV: RBAC quick check ----
// Route::get('/dev/rbac-check', function () {
//     $email = 'superadmin@gmail.com'; // 👉 এখানে তোমার অ্যাডমিন ইমেইল দাও

//     $u = \App\Models\User::where('email', $email)->first();

//     return response()->json([
//         'env'                => app()->environment(),
//         'user_found'         => (bool) $u,
//         'user_id'            => optional($u)->id,
//         'user_email'         => optional($u)->email,
//         'isSuperAdmin'       => optional($u)->isSuperAdmin,
//         'user_roles'         => optional($u)->roles()->pluck('roleName'),
//         'has_materials_view' => $u?->hasPermission('materials.view'),
//         'total_permissions'  => \App\Models\Permission::count(),
//     ]);
// })->middleware('web.jwt');
    //
    Route::middleware(['web.jwt','role:Admin'])->group(function () {
        Route::get('/users', [UserRoleController::class, 'index'])->name('users.index'); // NEW

        Route::get('/users/{id}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
        Route::put('/users/{id}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['show']);
        Route::resource('permissions', \App\Http\Controllers\PermissionController::class)
            ->only(['index','create','store','edit','update','destroy']);
    });


    // Users CRUD (minimal)
Route::middleware(['web.jwt','role:Admin'])->group(function () {
    Route::get('/users', [UserRoleController::class, 'index'])->name('users.index');

    // NEW:
    Route::get('/users/create', [UserRoleController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('/users', [UserRoleController::class, 'store'])->name('users.store')->middleware('permission:users.create');

    Route::get('/users/{id}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
    Route::put('/users/{id}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
});

});