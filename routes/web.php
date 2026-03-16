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
use App\Http\Controllers\FieldAssessmentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SmsController;
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
    Route::get('api/beneficiaries/{beneficiary}/summary', [BeneficiaryController::class, 'summary'])
        ->name('beneficiaries.summary');

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

    // Field Assessments
    Route::resource('field-assessments', FieldAssessmentController::class)
        ->parameters(['field-assessments' => 'fieldAssessment']);

    // SMS Broadcast
    Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
    Route::post('sms/preview', [SmsController::class, 'preview'])->name('sms.preview');
    Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');

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

    // Field Assessment Approval (Admin Only)
    Route::post('field-assessments/{fieldAssessment}/approve', [FieldAssessmentController::class, 'approve'])
        ->name('field-assessments.approve');
    Route::post('field-assessments/{fieldAssessment}/reject', [FieldAssessmentController::class, 'reject'])
        ->name('field-assessments.reject');
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
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/agencies/list', [SystemSettingsController::class, 'listAgencies'])->name('settings.agencies.list');
    Route::get('settings/purposes/list', [SystemSettingsController::class, 'listPurposes'])->name('settings.purposes.list');
    Route::get('settings/resource-types/list', [SystemSettingsController::class, 'listResourceTypes'])->name('settings.resource-types.list');

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

    // Settings — Form Fields
    Route::get('settings/form-fields/list', [SystemSettingsController::class, 'listFormFields'])->name('settings.form-fields.list');
    Route::post('settings/form-fields', [SystemSettingsController::class, 'storeFormField'])->name('settings.form-fields.store');
    Route::put('settings/form-fields/{formFieldOption}', [SystemSettingsController::class, 'updateFormField'])->name('settings.form-fields.update');
    Route::delete('settings/form-fields/{formFieldOption}', [SystemSettingsController::class, 'destroyFormField'])->name('settings.form-fields.destroy');
    Route::post('settings/form-fields/reorder', [SystemSettingsController::class, 'reorderFormFields'])->name('settings.form-fields.reorder');
});

require __DIR__.'/auth.php';

