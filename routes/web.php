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
use App\Http\Controllers\Api\AgencyFormFieldController;
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
    Route::patch('distribution-events/{event}/compliance', [DistributionEventController::class, 'updateCompliance'])
        ->name('distribution-events.updateCompliance');
    Route::post('distribution-events/{event}/attachments', [RecordAttachmentController::class, 'storeForEvent'])
        ->name('distribution-events.attachments.store');
    Route::get('distribution-events/{event}/attachments/{recordAttachment}/view', [RecordAttachmentController::class, 'viewForEvent'])
        ->name('distribution-events.attachments.view');
    Route::get('distribution-events/{event}/attachments/{recordAttachment}/download', [RecordAttachmentController::class, 'downloadForEvent'])
        ->name('distribution-events.attachments.download');
    Route::delete('distribution-events/{event}/attachments/{recordAttachment}', [RecordAttachmentController::class, 'destroyForEvent'])
        ->name('distribution-events.attachments.destroy');
    Route::get('distribution-events/{event}/allocations/{allocation}', [AllocationController::class, 'showForEvent'])
        ->name('distribution-events.allocations.show');

    // Allocations
    Route::get('allocations', [AllocationController::class, 'index'])->name('allocations.index');
    Route::get('allocations/create', [AllocationController::class, 'create'])->name('allocations.create');
    Route::get('allocations/{allocation}', [AllocationController::class, 'show'])->name('allocations.show');
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
    Route::get('api/barangay/{barangay}/beneficiaries', [GeoMapController::class, 'getBeneficiariesByBarangay'])
        ->name('api.barangay.beneficiaries');

    // API endpoints for eligible programs (used by allocation forms)
    Route::get('api/eligible-programs/{beneficiary}', [AllocationController::class, 'getEligiblePrograms'])
        ->name('api.eligible-programs');
    Route::get('api/allocations/eligible-programs/{beneficiary}', [AllocationController::class, 'getEligiblePrograms'])
        ->name('api.allocations.eligible-programs');
    Route::get('api/beneficiaries/search', [AllocationController::class, 'searchBeneficiaries'])
        ->name('api.beneficiaries.search');
    Route::get('api/programs/{program}/resource-types', [AllocationController::class, 'getResourceTypesByAgency'])
        ->name('api.programs.resource-types');
    Route::get('api/beneficiaries/{beneficiary}/recent-allocations', [AllocationController::class, 'checkRecentAllocations'])
        ->name('api.beneficiaries.recent-allocations');
    Route::get('api/agencies/by-classification', [AgencyFormFieldController::class, 'getByClassification'])
        ->name('api.agencies.by-classification');
    Route::get('api/agencies/form-fields', [AgencyFormFieldController::class, 'getFormFields'])
        ->name('api.agencies.form-fields');
    Route::get('api/classifications', [AgencyFormFieldController::class, 'getClassifications'])
        ->name('api.classifications');

    // SMS Broadcast
    Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
    Route::get('sms/beneficiaries', [SmsController::class, 'beneficiaries'])->name('sms.beneficiaries');
    Route::post('sms/preview', [SmsController::class, 'preview'])->name('sms.preview');
    Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send');
    Route::post('sms/templates', [SmsController::class, 'storeTemplate'])->name('sms.templates.store');
    Route::put('sms/templates/{template}', [SmsController::class, 'updateTemplate'])->name('sms.templates.update');
    Route::delete('sms/templates/{template}', [SmsController::class, 'destroyTemplate'])->name('sms.templates.destroy');
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
    // Settings — Program Names
    Route::get('settings/program-names', [SystemSettingsController::class, 'indexProgramNames'])->name('settings.program-names.index');
    Route::get('settings/program-names/list', [SystemSettingsController::class, 'listProgramNames'])->name('settings.program-names.list');
    Route::post('settings/program-names', [SystemSettingsController::class, 'storeProgramName'])->name('settings.program-names.store');
    Route::put('settings/program-names/{programName}', [SystemSettingsController::class, 'updateProgramName'])->name('settings.program-names.update');
    Route::patch('settings/program-names/{programName}/toggle-status', [SystemSettingsController::class, 'toggleProgramStatus'])->name('settings.program-names.toggle-status');
    Route::delete('settings/program-names/{programName}', [SystemSettingsController::class, 'destroyProgramName'])->name('settings.program-names.destroy');

    // Settings — Program Legal Requirements
    Route::post('settings/program-names/{programName}/legal-requirements', [SystemSettingsController::class, 'uploadProgramLegalRequirement'])->name('settings.program-names.legal-requirements.upload');
    Route::get('settings/program-names/{programName}/legal-requirements', [SystemSettingsController::class, 'listProgramLegalRequirements'])->name('settings.program-names.legal-requirements.list');
    Route::get('settings/program-names/{programName}/legal-requirements/{requirement}/view', [SystemSettingsController::class, 'viewProgramLegalRequirement'])->name('settings.program-names.legal-requirements.view');
    Route::get('settings/program-names/{programName}/legal-requirements/{requirement}/download', [SystemSettingsController::class, 'downloadProgramLegalRequirement'])->name('settings.program-names.legal-requirements.download');
    Route::delete('settings/program-names/{programName}/legal-requirements/{requirement}', [SystemSettingsController::class, 'deleteProgramLegalRequirement'])->name('settings.program-names.legal-requirements.delete');

    // Program Detail View
    Route::get('programs/{programName}', [SystemSettingsController::class, 'showProgramDetail'])->name('programs.detail');

    // Program API Endpoints (for preview modal)
    Route::get('programs/{programName}/legal-requirements-count', [SystemSettingsController::class, 'getProgramLegalRequirementsCount']);
    Route::get('programs/{programName}/legal-requirements', [SystemSettingsController::class, 'getProgramLegalRequirements']);
    Route::get('programs/{programName}/details', [SystemSettingsController::class, 'getProgramDetails']);
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // System Settings
    Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/diagnostics', [SystemSettingsController::class, 'diagnostics'])->name('settings.diagnostics');

    // Settings — Legacy URLs (redirect to single tabbed interface)
    Route::get('settings/agencies', function () {
        return redirect()->route('admin.settings.index', ['tab' => 'agencies']);
    })->name('settings.agencies.index');
    Route::get('settings/resource-types', function () {
        return redirect()->route('admin.settings.index', ['tab' => 'resource-types']);
    })->name('settings.resource-types.index');
    Route::get('settings/form-fields', function () {
        return redirect()->route('admin.settings.index', ['tab' => 'form-fields']);
    })->name('settings.form-fields.index');

    // Settings — API List Endpoints
    Route::get('settings/agencies/list', [SystemSettingsController::class, 'listAgencies'])->name('settings.agencies.list');
    Route::get('settings/agencies/active', [SystemSettingsController::class, 'getActiveAgencies'])->name('settings.agencies.active');
    Route::get('settings/purposes/list', [SystemSettingsController::class, 'listPurposes'])->name('settings.purposes.list');
    Route::get('settings/resource-types/list', [SystemSettingsController::class, 'listResourceTypes'])->name('settings.resource-types.list');

    // Settings — Agencies
    Route::get('settings/agencies/{agency}', [SystemSettingsController::class, 'getAgency'])->name('settings.agencies.show');
    Route::post('settings/agencies', [SystemSettingsController::class, 'storeAgency'])->name('settings.agencies.store');
    Route::put('settings/agencies/{agency}', [SystemSettingsController::class, 'updateAgency'])->name('settings.agencies.update');
    Route::patch('settings/agencies/{agency}/status', [SystemSettingsController::class, 'updateAgencyStatus'])->name('settings.agencies.status');
    Route::get('settings/agencies/{agency}/classification', [SystemSettingsController::class, 'resolveAgencyClassification'])->name('settings.agencies.classification');
    Route::delete('settings/agencies/{agency}', [SystemSettingsController::class, 'destroyAgency'])->name('settings.agencies.destroy');
    Route::get('settings/agencies/{agency}/form-fields', [SystemSettingsController::class, 'getAgencyFormFields'])->name('settings.agencies.form-fields.index');
    Route::delete('settings/agencies/{agency}/form-fields/cleanup-reserved', [SystemSettingsController::class, 'cleanupReservedAgencyFormFields'])->name('settings.agencies.form-fields.cleanup-reserved');
    Route::get('settings/agencies/{agency}/form-fields/{fieldId}', [SystemSettingsController::class, 'getFormField'])->name('settings.agencies.form-fields.show');
    Route::post('settings/agencies/{agency}/form-fields', [SystemSettingsController::class, 'addFormField'])->name('settings.agencies.form-fields.store');
    Route::put('settings/agencies/{agency}/form-fields/{fieldId}', [SystemSettingsController::class, 'updateAgencyFormField'])->name('settings.agencies.form-fields.update');
    Route::delete('settings/agencies/{agency}/form-fields/{fieldId}', [SystemSettingsController::class, 'deleteFormField'])->name('settings.agencies.form-fields.destroy');

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

    // Settings — Classification Core Fields (Farmer/Fisherfolk, DAR under Farmer)
    Route::get('settings/classification-core-fields/list', [SystemSettingsController::class, 'listClassificationCoreFields'])
        ->name('settings.classification-core-fields.list');
    Route::put('settings/classification-core-fields/{fieldName}', [SystemSettingsController::class, 'updateClassificationCoreField'])
        ->name('settings.classification-core-fields.update');
    Route::patch('settings/classification-core-fields/{fieldName}/required', [SystemSettingsController::class, 'updateClassificationCoreFieldRequired'])
        ->name('settings.classification-core-fields.required');
    Route::post('settings/form-fields/reorder', [SystemSettingsController::class, 'reorderFormFields'])->name('settings.form-fields.reorder');
});

require __DIR__.'/auth.php';
