<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\BeneficiaryAttachmentController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceTypeController;
use App\Http\Controllers\DistributionEventController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\DirectAssistanceController;
use App\Http\Controllers\GeoMapController;
use App\Http\Controllers\RecordAttachmentController;
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

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| SMS Gateway Webhook Routes (Public - E5 SMS Gateway)
|--------------------------------------------------------------------------
*/
Route::post('/api/webhooks/sms/delivery-callback', [SmsController::class, 'handleDeliveryCallback'])
    ->name('sms.delivery-callback')
    ->withoutMiddleware('csrf'); // Webhook from external service

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
    Route::post('beneficiaries/bulk-status', [BeneficiaryController::class, 'bulkUpdateStatus'])
        ->name('beneficiaries.bulkStatus');
    Route::get('beneficiaries/{beneficiary}/attachments/upload', [BeneficiaryAttachmentController::class, 'create'])
        ->name('beneficiaries.attachments.create');
    Route::post('beneficiaries/{beneficiary}/attachments', [BeneficiaryAttachmentController::class, 'store'])
        ->name('beneficiaries.attachments.store');
    Route::get('beneficiaries/{beneficiary}/attachments/{attachment}/view', [BeneficiaryAttachmentController::class, 'view'])
        ->name('beneficiaries.attachments.view');
    Route::get('beneficiaries/{beneficiary}/attachments/{attachment}', [BeneficiaryAttachmentController::class, 'download'])
        ->name('beneficiaries.attachments.download');
    Route::delete('beneficiaries/{beneficiary}/attachments/{attachment}', [BeneficiaryAttachmentController::class, 'destroy'])
        ->name('beneficiaries.attachments.destroy');

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
    Route::post('distribution-events/{event}/compliance', [DistributionEventController::class, 'updateCompliance'])
        ->name('distribution-events.updateCompliance');
    Route::post('distribution-events/{event}/attachments', [RecordAttachmentController::class, 'storeForEvent'])
        ->name('distribution-events.attachments.store');
    Route::get('distribution-events/{event}/attachments/{recordAttachment}/view', [RecordAttachmentController::class, 'viewForEvent'])
        ->name('distribution-events.attachments.view');
    Route::get('distribution-events/{event}/attachments/{recordAttachment}/download', [RecordAttachmentController::class, 'downloadForEvent'])
        ->name('distribution-events.attachments.download');
    Route::delete('distribution-events/{event}/attachments/{recordAttachment}', [RecordAttachmentController::class, 'destroyForEvent'])
        ->name('distribution-events.attachments.destroy');

    // Allocations
    Route::post('allocations', [AllocationController::class, 'store'])
        ->name('allocations.store');
    Route::post('allocations/bulk', [AllocationController::class, 'storeBulk'])
        ->name('allocations.storeBulk');
    Route::post('allocations/import-csv', [AllocationController::class, 'importCsv'])
        ->name('allocations.importCsv');
    Route::get('allocations/import-csv-template', [AllocationController::class, 'downloadImportCsvTemplate'])
        ->name('allocations.importCsvTemplate');
    Route::get('allocations/import-csv-errors/{report}', [AllocationController::class, 'downloadImportCsvErrorsReport'])
        ->where('report', '[^/]+')
        ->name('allocations.importCsvErrorsReport');
    Route::put('allocations/{allocation}', [AllocationController::class, 'update'])
        ->name('allocations.update');
    Route::post('allocations/{allocation}/mark-ready-for-release', [AllocationController::class, 'markReadyForRelease'])
        ->name('allocations.mark-ready-for-release');
    Route::post('allocations/{allocation}/distribute', [AllocationController::class, 'markDistributed'])
        ->name('allocations.markDistributed');
    Route::post('allocations/{allocation}/not-received', [AllocationController::class, 'markNotReceived'])
        ->name('allocations.markNotReceived');
    Route::post('allocations/bulk-release-outcome', [AllocationController::class, 'bulkUpdateReleaseOutcome'])
        ->name('allocations.bulkReleaseOutcome');
    Route::post('allocations/{allocation}/attachments', [RecordAttachmentController::class, 'storeForAllocation'])
        ->name('allocations.attachments.store');
    Route::get('allocations/{allocation}/attachments/{recordAttachment}/view', [RecordAttachmentController::class, 'viewForAllocation'])
        ->name('allocations.attachments.view');
    Route::get('allocations/{allocation}/attachments/{recordAttachment}/download', [RecordAttachmentController::class, 'downloadForAllocation'])
        ->name('allocations.attachments.download');
    Route::delete('allocations/{allocation}/attachments/{recordAttachment}', [RecordAttachmentController::class, 'destroyForAllocation'])
        ->name('allocations.attachments.destroy');

    // Direct Assistance
    Route::resource('direct-assistance', DirectAssistanceController::class);
    Route::post('direct-assistance/{direct_assistance}/mark-ready-for-release', [DirectAssistanceController::class, 'markReadyForRelease'])
        ->name('direct-assistance.mark-ready-for-release');
    Route::post('direct-assistance/{direct_assistance}/mark-released', [DirectAssistanceController::class, 'markReleased'])
        ->name('direct-assistance.mark-released');
    // Legacy alias for older UI/tests calling mark-distributed.
    Route::post('direct-assistance/{direct_assistance}/mark-distributed', [DirectAssistanceController::class, 'markDistributed'])
        ->name('direct-assistance.mark-distributed');
    Route::post('direct-assistance/{direct_assistance}/mark-not-received', [DirectAssistanceController::class, 'markNotReceived'])
        ->name('direct-assistance.mark-not-received');
    Route::get('direct-assistance-barangay-analytics', [DirectAssistanceController::class, 'barangayAnalytics'])
        ->name('direct-assistance.barangay-analytics');
    Route::post('direct-assistance/{direct_assistance}/attachments', [RecordAttachmentController::class, 'storeForDirectAssistance'])
        ->name('direct-assistance.attachments.store');
    Route::get('direct-assistance/{direct_assistance}/attachments/{recordAttachment}/view', [RecordAttachmentController::class, 'viewForDirectAssistance'])
        ->name('direct-assistance.attachments.view');
    Route::get('direct-assistance/{direct_assistance}/attachments/{recordAttachment}/download', [RecordAttachmentController::class, 'downloadForDirectAssistance'])
        ->name('direct-assistance.attachments.download');
    Route::delete('direct-assistance/{direct_assistance}/attachments/{recordAttachment}', [RecordAttachmentController::class, 'destroyForDirectAssistance'])
        ->name('direct-assistance.attachments.destroy');

    // API endpoints for eligible programs (used by allocation forms)
    Route::get('api/eligible-programs/{beneficiary}', [DirectAssistanceController::class, 'getEligiblePrograms'])
        ->name('api.eligible-programs');
    Route::get('api/allocations/eligible-programs/{beneficiary}', [AllocationController::class, 'getEligiblePrograms'])
        ->name('api.allocations.eligible-programs');
    Route::get('api/beneficiaries/search', [AllocationController::class, 'searchBeneficiaries'])
        ->name('api.beneficiaries.search');
    Route::get('api/programs/{program}/resource-types', [AllocationController::class, 'getResourceTypesByAgency'])
        ->name('api.programs.resource-types');

    // SMS Broadcast
    Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
    Route::post('sms/settings/automation', [SmsController::class, 'updateAutomationSettings'])
        ->name('sms.settings.automation');
    Route::get('sms/beneficiaries', [SmsController::class, 'beneficiaries'])->name('sms.beneficiaries');
    Route::post('sms/preview', [SmsController::class, 'preview'])->name('sms.preview');
    Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');

    // Operational read pages
    Route::get('allocations', [AllocationController::class, 'index'])->name('allocations.index');
    Route::get('allocations/{allocation}', [AllocationController::class, 'show'])->name('allocations.show');
    Route::get('beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    Route::get('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'show'])->name('beneficiaries.show');
    Route::get('api/beneficiaries/{beneficiary}/summary', [BeneficiaryController::class, 'summary'])
        ->name('beneficiaries.summary');
    Route::get('distribution-events', [DistributionEventController::class, 'index'])->name('distribution-events.index');
    Route::get('distribution-events/{event}', [DistributionEventController::class, 'show'])->name('distribution-events.show');
    Route::get('distribution-events/{event}/distribution-list', [DistributionEventController::class, 'distributionList'])
        ->name('distribution-events.distributionList');
    Route::get('distribution-events/{event}/distribution-list/pdf', [DistributionEventController::class, 'distributionListPdf'])
        ->name('distribution-events.distributionListPdf');
    Route::get('distribution-events/{event}/distribution-list/csv', [DistributionEventController::class, 'distributionListCsv'])
        ->name('distribution-events.distributionListCsv');
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('geo-map', [GeoMapController::class, 'index'])->name('geo-map.index');
    Route::get('geo-map/data', [GeoMapController::class, 'mapData'])->name('geo-map.data');
});

/*
|--------------------------------------------------------------------------
| Partner Agency Routes (E4 - Read-Only Access)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:partner'])->group(function () {
    // Read-only reports for national partner agencies
    // TODO: Add partner-specific filtering when partner role is fully implemented
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
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // System Settings
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');

    // Settings — Separate Pages (Multi-page Interface)
    Route::get('settings/agencies', [SystemSettingsController::class, 'indexAgencies'])->name('settings.agencies.index');
    Route::get('settings/purposes', [SystemSettingsController::class, 'indexPurposes'])->name('settings.purposes.index');
    Route::get('settings/resource-types', [SystemSettingsController::class, 'indexResourceTypes'])->name('settings.resource-types.index');
    Route::get('settings/program-names', [SystemSettingsController::class, 'indexProgramNames'])->name('settings.program-names.index');
    Route::get('settings/form-fields', [SystemSettingsController::class, 'indexFormFields'])->name('settings.form-fields.index');

    // Settings — API List Endpoints
    Route::get('settings/agencies/list', [SystemSettingsController::class, 'listAgencies'])->name('settings.agencies.list');
    Route::get('settings/agencies/active', [SystemSettingsController::class, 'getActiveAgencies'])->name('settings.agencies.active');
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

