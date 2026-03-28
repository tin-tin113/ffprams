<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceTypeController;
use App\Http\Controllers\DistributionEventController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\GeoMapController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
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
| Operational Routes (Admin & Staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin,staff'])->group(function () {
    // Beneficiaries (create, store, edit, update, destroy)
    Route::resource('beneficiaries', BeneficiaryController::class)->except(['index', 'show']);
    Route::post('beneficiaries/{beneficiary}/send-sms', [BeneficiaryController::class, 'sendSms'])
        ->name('beneficiaries.sendSms');

    // Resource Types (read-only for Admin & Staff)
    Route::resource('resource-types', ResourceTypeController::class)->only(['index']);

    // Distribution Events (create, store, edit, update, destroy)
    Route::resource('distribution-events', DistributionEventController::class)
        ->parameters(['distribution-events' => 'event'])
        ->except(['index', 'show']);
    Route::post('distribution-events/{event}/status', [DistributionEventController::class, 'updateStatus'])
        ->name('distribution-events.updateStatus');
    Route::post('distribution-events/{event}/approve-beneficiary-list', [DistributionEventController::class, 'approveBeneficiaryList'])
        ->name('distribution-events.approveBeneficiaryList');

    // Allocations
    Route::post('allocations', [AllocationController::class, 'store'])
        ->name('allocations.store');
    Route::post('allocations/bulk', [AllocationController::class, 'storeBulk'])
        ->name('allocations.storeBulk');
    Route::put('allocations/{allocation}', [AllocationController::class, 'update'])
        ->name('allocations.update');
    Route::post('allocations/{allocation}/distribute', [AllocationController::class, 'markDistributed'])
        ->name('allocations.markDistributed');
    Route::post('allocations/{allocation}/not-received', [AllocationController::class, 'markNotReceived'])
        ->name('allocations.markNotReceived');

    // SMS Broadcast
    Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
    Route::post('sms/preview', [SmsController::class, 'preview'])->name('sms.preview');
    Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');

    // Operational read pages
    Route::get('allocations', [AllocationController::class, 'index'])->name('allocations.index');
    Route::get('beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    Route::get('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'show'])->name('beneficiaries.show');
    Route::get('api/beneficiaries/{beneficiary}/summary', [BeneficiaryController::class, 'summary'])
        ->name('beneficiaries.summary');
    Route::get('distribution-events', [DistributionEventController::class, 'index'])->name('distribution-events.index');
    Route::get('distribution-events/{event}', [DistributionEventController::class, 'show'])->name('distribution-events.show');
    Route::get('distribution-events/{event}/distribution-list', [DistributionEventController::class, 'distributionList'])
        ->name('distribution-events.distributionList');
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('geo-map', [GeoMapController::class, 'index'])->name('geo-map.index');
    Route::get('geo-map/data', [GeoMapController::class, 'mapData'])->name('geo-map.data');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Admin-only allocation deletion endpoint
    Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])
        ->name('allocations.destroy');

    // Resource Types (admin-only write actions)
    Route::resource('resource-types', ResourceTypeController::class)->only(['store', 'update', 'destroy']);
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // System Settings
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/agencies/list', [SystemSettingsController::class, 'listAgencies'])->name('settings.agencies.list');
    Route::get('settings/purposes/list', [SystemSettingsController::class, 'listPurposes'])->name('settings.purposes.list');
    Route::get('settings/resource-types/list', [SystemSettingsController::class, 'listResourceTypes'])->name('settings.resource-types.list');
    Route::get('settings/program-names/list', [SystemSettingsController::class, 'listProgramNames'])->name('settings.program-names.list');

    // Settings — Agencies
    Route::post('settings/agencies', [SystemSettingsController::class, 'storeAgency'])->name('settings.agencies.store');
    Route::put('settings/agencies/{agency}', [SystemSettingsController::class, 'updateAgency'])->name('settings.agencies.update');
    Route::delete('settings/agencies/{agency}', [SystemSettingsController::class, 'destroyAgency'])->name('settings.agencies.destroy');

    // Settings — Assistance Purposes
    Route::post('settings/purposes', [SystemSettingsController::class, 'storePurpose'])->name('settings.purposes.store');
    Route::put('settings/purposes/{purpose}', [SystemSettingsController::class, 'updatePurpose'])->name('settings.purposes.update');
    Route::delete('settings/purposes/{purpose}', [SystemSettingsController::class, 'destroyPurpose'])->name('settings.purposes.destroy');

    // Settings — Resource Types
    Route::post('settings/resource-types', [SystemSettingsController::class, 'storeResourceType'])->name('settings.resource-types.store');
    Route::put('settings/resource-types/{resourceType}', [SystemSettingsController::class, 'updateResourceType'])->name('settings.resource-types.update');
    Route::delete('settings/resource-types/{resourceType}', [SystemSettingsController::class, 'destroyResourceType'])->name('settings.resource-types.destroy');

    // Settings — Program Names
    Route::post('settings/program-names', [SystemSettingsController::class, 'storeProgramName'])->name('settings.program-names.store');
    Route::put('settings/program-names/{programName}', [SystemSettingsController::class, 'updateProgramName'])->name('settings.program-names.update');
    Route::delete('settings/program-names/{programName}', [SystemSettingsController::class, 'destroyProgramName'])->name('settings.program-names.destroy');

    // Settings — Form Fields
    Route::get('settings/form-fields/list', [SystemSettingsController::class, 'listFormFields'])->name('settings.form-fields.list');
    Route::post('settings/form-fields', [SystemSettingsController::class, 'storeFormField'])->name('settings.form-fields.store');
    Route::put('settings/form-fields/{formFieldOption}', [SystemSettingsController::class, 'updateFormField'])->name('settings.form-fields.update');
    Route::delete('settings/form-fields/{formFieldOption}', [SystemSettingsController::class, 'destroyFormField'])->name('settings.form-fields.destroy');
    Route::post('settings/form-fields/reorder', [SystemSettingsController::class, 'reorderFormFields'])->name('settings.form-fields.reorder');
});

require __DIR__.'/auth.php';

