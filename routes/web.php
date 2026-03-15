<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceTypeController;
use App\Http\Controllers\DistributionEventController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\GeoMapController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Shared Routes (Admin & Staff)
|--------------------------------------------------------------------------
| Both admin and staff can access these routes.
*/
Route::middleware(['auth', 'verified', 'role:admin,staff'])->group(function () {
    // Beneficiaries
    Route::resource('beneficiaries', BeneficiaryController::class);
    Route::post('beneficiaries/{beneficiary}/send-sms', [BeneficiaryController::class, 'sendSms'])
        ->name('beneficiaries.sendSms');

    // Resource Types
    Route::resource('resource-types', ResourceTypeController::class)->only(['index', 'store', 'update', 'destroy']);

    // Distribution Events
    Route::resource('distribution-events', DistributionEventController::class)
        ->parameters(['distribution-events' => 'event']);
    Route::post('distribution-events/{event}/status', [DistributionEventController::class, 'updateStatus'])
        ->name('distribution-events.updateStatus');

    // Allocations
    Route::post('allocations', [AllocationController::class, 'store'])
        ->name('allocations.store');
    Route::post('allocations/bulk', [AllocationController::class, 'storeBulk'])
        ->name('allocations.storeBulk');
    Route::put('allocations/{allocation}', [AllocationController::class, 'update'])
        ->name('allocations.update');
    Route::post('allocations/{allocation}/distribute', [AllocationController::class, 'markDistributed'])
        ->name('allocations.markDistributed');

    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

    // Geo Map
    Route::get('geo-map', [GeoMapController::class, 'index'])->name('geo-map.index');
    Route::get('geo-map/data', [GeoMapController::class, 'mapData'])->name('geo-map.data');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Allocation Deletion
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])
        ->name('allocations.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
| Only users with the admin role can access these routes.
*/
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // System Settings
    // Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    // Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';

