@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @php
        $complianceStatusLabels = [
            'provided' => 'Provided',
            'not_available_yet' => 'Not available yet',
            'not_applicable' => 'Not applicable',
            'to_be_verified' => 'To be verified',
        ];
    @endphp

    {{-- Page Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <a href="{{ route('distribution-events.index') }}" class="btn btn-light border shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </div>
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item text-muted small"><a href="{{ route('distribution-events.index') }}" class="text-decoration-none">Distribution Events</a></li>
                    <li class="breadcrumb-item active small" aria-current="page">Create New</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">Create Distribution Event</h4>
        </div>
    </div>

    <div id="distributionEventCreateAjaxNotice" class="alert d-none shadow-sm border-0 mb-4" role="alert"></div>

    <form id="distributionEventCreateForm" action="{{ route('distribution-events.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            {{-- Left Column: Core Details --}}
            <div class="col-12 col-xl-8">
                {{-- Type Selection Card --}}
                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-body p-4">
                        <label class="form-label fw-bold text-uppercase small text-muted mb-3 tracking-wider">
                            <i class="bi bi-layers me-1"></i> Distribution Type
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="type" id="type_physical" value="physical"
                                       {{ old('type', 'physical') === 'physical' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary w-100 p-4 text-start d-flex align-items-center border-2 rounded-3" for="type_physical">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3 text-primary">
                                        <i class="bi bi-box-seam fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5">Physical Resources</div>
                                        <div class="small opacity-75">Seeds, fertilizers, tools, etc.</div>
                                    </div>
                                    <i class="bi bi-check-circle-fill ms-auto fs-4 check-icon invisible"></i>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="type" id="type_financial" value="financial"
                                       {{ old('type') === 'financial' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 p-4 text-start d-flex align-items-center border-2 rounded-3" for="type_financial">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3 text-success">
                                        <i class="bi bi-cash-stack fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5">Financial Assistance</div>
                                        <div class="small opacity-75">Cash grants, subsidies, etc.</div>
                                    </div>
                                    <i class="bi bi-check-circle-fill ms-auto fs-4 check-icon invisible"></i>
                                </label>
                            </div>
                        </div>
                        @error('type')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Unified Configuration Card --}}
                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="bi bi-gear-fill me-2 text-primary"></i>
                            Event Configuration
                        </h6>
                    </div>
                    <div class="card-body p-4 pt-2">
                        {{-- Row 1: Core Details --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0 bg-light @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Event Name" required>
                                    <label for="name">Event Name <span class="text-danger">*</span></label>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-0 bg-light @error('barangay_id') is-invalid @enderror"
                                            id="barangay_id" name="barangay_id" required>
                                        <option value="" disabled {{ old('barangay_id') ? '' : 'selected' }}>Select Barangay</option>
                                        @foreach($barangays as $barangay)
                                            <option value="{{ $barangay->id }}" {{ old('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                                {{ $barangay->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="barangay_id">Target Barangay <span class="text-danger">*</span></label>
                                </div>
                                @error('barangay_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date"
                                           class="form-control border-0 bg-light @error('distribution_date') is-invalid @enderror"
                                           id="distribution_date" name="distribution_date"
                                           value="{{ old('distribution_date') }}" required>
                                    <label for="distribution_date">Distribution Date <span class="text-danger">*</span></label>
                                </div>
                                @error('distribution_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="border-light mb-4">

                        {{-- Row 2: Resource Details --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-0 bg-light @error('program_name_id') is-invalid @enderror"
                                            id="program_name_id" name="program_name_id" required>
                                        <option value="" disabled {{ old('program_name_id') ? '' : 'selected' }}>Select Program</option>
                                        @foreach($programNames as $program)
                                            <option value="{{ $program->id }}"
                                                    data-agency-id="{{ $program->agency_id }}"
                                                    {{ old('program_name_id') == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="program_name_id">Funding Program <span class="text-danger">*</span></label>
                                </div>
                                @error('program_name_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-0 bg-light @error('resource_type_id') is-invalid @enderror"
                                            id="resource_type_id" name="resource_type_id" required>
                                        <option value="" disabled {{ old('resource_type_id') ? '' : 'selected' }}>Select Resource Type</option>
                                        @foreach($resourceTypes as $type)
                                            <option value="{{ $type->id }}"
                                                    data-unit="{{ $type->unit }}"
                                                    data-agency-id="{{ $type->agency_id }}"
                                                    {{ old('resource_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="resource_type_id">Resource Type <span class="text-danger">*</span></label>
                                </div>
                                <span id="unitDisplay" class="position-absolute end-0 top-50 translate-middle-y me-4 badge bg-primary bg-opacity-10 text-primary d-none fw-bold" style="z-index: 5;"></span>
                                @error('resource_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-none transition-all" id="totalFundGroup">
                                <div class="p-3 rounded bg-success bg-opacity-5 border border-success border-opacity-10 mt-2">
                                    <label for="total_fund_amount" class="form-label fw-bold text-success small mb-2 text-uppercase tracking-wider">
                                        <i class="bi bi-cash me-1"></i> Total Fund Budget (PHP)
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text border-0 bg-white text-success fw-bold">₱</span>
                                        <input type="number" step="0.01" min="1" max="9999999999.99"
                                               class="form-control border-0 bg-white @error('total_fund_amount') is-invalid @enderror"
                                               id="total_fund_amount" name="total_fund_amount"
                                               value="{{ old('total_fund_amount') }}"
                                               placeholder="0.00">
                                    </div>
                                    @error('total_fund_amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Financial Compliance Section (Moved to Full Width below main card) --}}
                <div id="financialComplianceFields" class="d-none animate__animated animate__fadeIn">
                    <div class="card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-success bg-opacity-10 py-3 border-0">
                            <h6 class="mb-0 fw-bold text-success d-flex align-items-center">
                                <i class="bi bi-shield-check me-2"></i>
                                Compliance & Regulatory Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                {{-- Legal Basis Group --}}
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Authorization</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light" id="legal_basis_type" name="legal_basis_type">
                                                    <option value="" selected disabled>Select type...</option>
                                                    <option value="resolution">Resolution</option>
                                                    <option value="ordinance">Ordinance</option>
                                                    <option value="memo">Memo</option>
                                                    <option value="special_order">Special Order</option>
                                                    <option value="other">Other</option>
                                                </select>
                                                <label for="legal_basis_type">Legal Basis Type</label>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0 bg-light" id="legal_basis_reference_no" name="legal_basis_reference_no" placeholder="Ref No.">
                                                <label for="legal_basis_reference_no">Reference No.</label>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-floating">
                                                <input type="date" class="form-control border-0 bg-light" id="legal_basis_date" name="legal_basis_date">
                                                <label for="legal_basis_date">Basis Date</label>
                                            </div>
                                        </div>
                                        <div id="legalRemarksGroup" class="col-12 d-none">
                                            <div class="form-floating">
                                                <textarea class="form-control border-0 bg-light" id="legal_basis_remarks" name="legal_basis_remarks" style="height: 100px" placeholder="Remarks"></textarea>
                                                <label for="legal_basis_remarks">Legal Basis Remarks</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Fund Control Group --}}
                                <div class="col-md-6 border-start border-light ps-md-4">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Fund Control</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light" id="fund_source" name="fund_source">
                                                    <option value="" selected disabled>Select source...</option>
                                                    <option value="lgu_trust_fund">LGU Trust Fund</option>
                                                    <option value="nga_transfer">NGA Transfer</option>
                                                    <option value="local_program">Local Program</option>
                                                    <option value="other">Other</option>
                                                </select>
                                                <label for="fund_source">Fund Source</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="trustAccountGroup">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0 bg-light" id="trust_account_code" name="trust_account_code" placeholder="Code">
                                                <label for="trust_account_code">Trust Account Code</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0 bg-light" id="fund_release_reference" name="fund_release_reference" placeholder="Ref">
                                                <label for="fund_release_reference">Release Reference (DV/Check No.)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 border-light"></div>

                                {{-- Liquidation Group --}}
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Liquidation Monitoring</h6>
                                    <div class="row g-2">
                                        <div class="col-12 mb-2">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light" id="liquidation_status" name="liquidation_status">
                                                    <option value="not_required">Not Required</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="submitted">Submitted</option>
                                                    <option value="verified">Verified</option>
                                                </select>
                                                <label for="liquidation_status">Liquidation Status</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="liquidationDueDateGroup">
                                            <div class="form-floating mb-2">
                                                <input type="date" class="form-control border-0 bg-light" id="liquidation_due_date" name="liquidation_due_date">
                                                <label for="liquidation_due_date">Due Date</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="liquidationSubmittedAtGroup">
                                            <div class="form-floating mb-2">
                                                <input type="datetime-local" class="form-control border-0 bg-light" id="liquidation_submitted_at" name="liquidation_submitted_at">
                                                <label for="liquidation_submitted_at">Date Submitted</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="liquidationReferenceGroup">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0 bg-light" id="liquidation_reference_no" name="liquidation_reference_no" placeholder="Ref">
                                                <label for="liquidation_reference_no">Liquidation Ref No.</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Flags --}}
                                <div class="col-md-6 border-start border-light ps-md-4">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Special Endorsements</h6>
                                    <div class="row g-2">
                                        <div class="col-12 mb-2">
                                            <div class="form-check form-switch p-3 bg-light rounded-3 shadow-none border-0">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <label class="form-check-label small fw-bold text-muted mb-0" for="requires_farmc_endorsement">
                                                        <i class="bi bi-person-check me-2"></i>Requires FARMC Endorsement
                                                    </label>
                                                    <input class="form-check-input ms-0" type="checkbox" role="switch" id="requires_farmc_endorsement" name="requires_farmc_endorsement" value="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="farmcReferenceGroup" class="col-12 d-none mb-2">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0 bg-light" id="farmc_reference_no" name="farmc_reference_no" placeholder="Ref">
                                                <label for="farmc_reference_no">FARMC Reference No.</label>
                                            </div>
                                        </div>
                                        <div id="farmcEndorsedAtGroup" class="col-12 d-none">
                                            <div class="form-floating">
                                                <input type="datetime-local" class="form-control border-0 bg-light" id="farmc_endorsed_at" name="farmc_endorsed_at">
                                                <label for="farmc_endorsed_at">Endorsement Date</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 border-light"></div>

                                {{-- General Audit Status --}}
                                <div class="col-12">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-4">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light fw-bold" id="compliance_overall_status" name="compliance_overall_status">
                                                    @foreach($complianceStatusLabels as $statusValue => $statusLabel)
                                                        <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="compliance_overall_status">Verification Status</label>
                                            </div>
                                        </div>
                                        <div class="col-md-8 d-none" id="compliance_overall_reason_group">
                                            <div class="form-floating">
                                                <textarea class="form-control border-0 bg-light" id="compliance_overall_reason" name="compliance_overall_reason" placeholder="Reason" style="height: 60px"></textarea>
                                                <label for="compliance_overall_reason">Pending Reason / Missing Documents</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Side Actions & Compliance (if financial) --}}
            <div class="col-12 col-xl-4">
                <div class="sticky-top" style="top: 1.5rem; z-index: 10;">
                    {{-- Action Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm py-3 fw-bold">
                                    <i class="bi bi-plus-circle me-2"></i> Create Event
                                </button>
                                <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary py-2">
                                    Cancel
                                </a>
                            </div>
                            <hr class="my-4">
                            <div class="text-start">
                                <h6 class="small fw-bold text-uppercase text-muted mb-3">Quick Checklist</h6>
                                <div class="d-flex align-items-center mb-2 small text-muted">
                                    <i class="bi bi-check2-circle text-success me-2"></i> Ensure date is correct
                                </div>
                                <div class="d-flex align-items-center mb-2 small text-muted">
                                    <i class="bi bi-check2-circle text-success me-2"></i> Resource type matches agency
                                </div>
                                <div class="d-flex align-items-center small text-muted">
                                    <i class="bi bi-check2-circle text-success me-2"></i> Beneficiaries can be added later
                                </div>
                            </div>
                        </div>
                    </div>


    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title d-flex align-items-center" id="confirmationModalLabel">
                        <i class="bi bi-shield-check me-2 fs-4"></i>
                        Confirm Event Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted mb-4">Please review the event configuration before proceeding. These details define how resources will be distributed.</p>
                    <div id="summaryContent" class="mb-0">
                        <!-- Summary will be injected here -->
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-pencil me-1"></i> Make Changes
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 shadow-sm" id="confirmSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Proceed to Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast and AJAX Notice Templates --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="distributionEventCreateToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex p-2">
                <div class="toast-body fw-medium" id="distributionEventCreateToastMessage"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .btn-check:checked + .btn-outline-primary,
    .btn-check:checked + .btn-outline-success {
        border-width: 2px;
        background-color: transparent !important;
        color: inherit !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .btn-check:checked + .btn-outline-primary { border-color: var(--bs-primary) !important; color: var(--bs-primary) !important; }
    .btn-check:checked + .btn-outline-success { border-color: var(--bs-success) !important; color: var(--bs-success) !important; }
    
    .btn-check:checked + label .check-icon {
        visibility: visible !important;
    }
    
    .input-group-text {
        border-color: #dee2e6;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        line-height: 1;
        vertical-align: middle;
    }
    
    /* Skeleton Loading State */
    .skeleton {
        background: #f6f7f8;
        background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-repeat: no-repeat;
        background-size: 800px 104px; 
        display: inline-block;
        position: relative; 
        animation-duration: 1s;
        animation-fill-mode: forwards; 
        animation-iteration-count: infinite;
        animation-name: placeholderShimmer;
        animation-timing-function: linear;
        border-radius: 4px;
    }

    @keyframes placeholderShimmer {
        0% { background-position: -468px 0; }
        100% { background-position: 468px 0; }
    }

    .skeleton-text { height: 1rem; width: 100%; margin-bottom: 0.5rem; }
    .skeleton-label { height: 0.75rem; width: 40%; margin-bottom: 0.5rem; }
    .skeleton-input { height: 2.5rem; width: 100%; margin-bottom: 1rem; }
    .skeleton-card { height: 200px; width: 100%; }

    #formLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        padding: 2rem;
    }

    .form-floating > .form-control:focus, 
    .form-floating > .form-control:not(:placeholder-shown),
    .form-floating > .form-select {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }

    .form-floating > label {
        padding-left: 1rem;
        color: #6c757d;
        font-weight: 500;
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        border-radius: 12px !important;
        font-weight: 500;
    }

    #unitDisplay {
        pointer-events: none;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border: none !important;
    }

    .input-group-lg > .form-control {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .card {
        border-radius: 16px !important;
    }

    .btn-lg {
        border-radius: 12px !important;
    }

    .bg-opacity-5 { --bs-bg-opacity: 0.05; }
    
    .tracking-wider { letter-spacing: 0.05em; }

    .animate__fadeIn {
        animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formLoader = document.getElementById('formLoader');
    if (formLoader) {
        setTimeout(() => {
            formLoader.style.transition = 'opacity 0.5s ease';
            formLoader.style.opacity = '0';
            setTimeout(() => formLoader.remove(), 500);
        }, 300);
    }

    const resourceSelect = document.getElementById('resource_type_id');
    const programSelect = document.getElementById('program_name_id');
    const unitDisplay = document.getElementById('unitDisplay');
    const totalFundGroup = document.getElementById('totalFundGroup');
    const totalFundInput = document.getElementById('total_fund_amount');
    const financialComplianceFields = document.getElementById('financialComplianceFields');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const legalBasisType = document.getElementById('legal_basis_type');
    const legalRemarksGroup = document.getElementById('legalRemarksGroup');
    const legalRemarks = document.getElementById('legal_basis_remarks');
    const fundSource = document.getElementById('fund_source');
    const trustAccountGroup = document.getElementById('trustAccountGroup');
    const trustAccount = document.getElementById('trust_account_code');
    const liquidationStatus = document.getElementById('liquidation_status');
    const liquidationDueDateGroup = document.getElementById('liquidationDueDateGroup');
    const liquidationDueDate = document.getElementById('liquidation_due_date');
    const liquidationSubmittedAtGroup = document.getElementById('liquidationSubmittedAtGroup');
    const liquidationSubmittedAt = document.getElementById('liquidation_submitted_at');
    const liquidationReferenceGroup = document.getElementById('liquidationReferenceGroup');
    const liquidationReference = document.getElementById('liquidation_reference_no');
    const requiresFarmc = document.getElementById('requires_farmc_endorsement');
    const farmcReferenceGroup = document.getElementById('farmcReferenceGroup');
    const farmcReference = document.getElementById('farmc_reference_no');
    const farmcEndorsedAtGroup = document.getElementById('farmcEndorsedAtGroup');
    const farmcEndorsedAt = document.getElementById('farmc_endorsed_at');
    const allResourceOptions = Array.from(resourceSelect.options);
    const allProgramOptions = Array.from(programSelect.options);

    function setGroupState(groupEl, inputEl, show, required = false) {
        if (!groupEl || !inputEl) return;
        groupEl.classList.toggle('d-none', !show);
        inputEl.disabled = !show;
        inputEl.required = false;
        if (!show && inputEl.type !== 'checkbox') {
            inputEl.value = '';
        }
    }

    function updateComplianceDependencies() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';

        if (!isFinancial) {
            setGroupState(legalRemarksGroup, legalRemarks, false);
            setGroupState(trustAccountGroup, trustAccount, false);
            setGroupState(liquidationDueDateGroup, liquidationDueDate, false);
            setGroupState(liquidationSubmittedAtGroup, liquidationSubmittedAt, false);
            setGroupState(liquidationReferenceGroup, liquidationReference, false);
            setGroupState(farmcReferenceGroup, farmcReference, false);
            setGroupState(farmcEndorsedAtGroup, farmcEndorsedAt, false);
            return;
        }

        const legalType = legalBasisType?.value ?? '';
        setGroupState(legalRemarksGroup, legalRemarks, true, legalType === 'other');

        const source = fundSource?.value ?? '';
        setGroupState(trustAccountGroup, trustAccount, source === 'lgu_trust_fund', source === 'lgu_trust_fund');

        const liq = liquidationStatus?.value ?? 'not_required';
        const dueRequired = ['pending', 'submitted', 'verified'].includes(liq);
        const submittedRequired = ['submitted', 'verified'].includes(liq);
        setGroupState(liquidationDueDateGroup, liquidationDueDate, dueRequired, dueRequired);
        setGroupState(liquidationSubmittedAtGroup, liquidationSubmittedAt, submittedRequired, submittedRequired);
        setGroupState(liquidationReferenceGroup, liquidationReference, submittedRequired, submittedRequired);

        const farmcRequired = !!requiresFarmc?.checked;
        setGroupState(farmcReferenceGroup, farmcReference, farmcRequired, farmcRequired);
        setGroupState(farmcEndorsedAtGroup, farmcEndorsedAt, farmcRequired, false);
    }

    function updateUnit() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        if (selected && selected.dataset.unit) {
            unitDisplay.textContent = selected.dataset.unit;
            unitDisplay.classList.remove('d-none');
        } else {
            unitDisplay.classList.add('d-none');
        }
    }

    function updateResourceTypeOptions() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';
        const programSelected = programSelect.options[programSelect.selectedIndex];
        const agencyId = programSelected ? programSelected.dataset.agencyId : '';
        const currentValue = resourceSelect.value;

        resourceSelect.innerHTML = '';

        allResourceOptions.forEach(function (opt) {
            if (opt.value === '') {
                resourceSelect.appendChild(opt.cloneNode(true));
                return;
            }

            const matchesType = isFinancial ? (opt.dataset.unit === 'PHP') : (opt.dataset.unit !== 'PHP');
            const matchesAgency = !agencyId || opt.dataset.agencyId === agencyId;

            if (matchesType && matchesAgency) {
                resourceSelect.appendChild(opt.cloneNode(true));
            }
        });

        // Restore selection if still valid
        const exists = Array.from(resourceSelect.options).some(o => o.value === currentValue);
        if (exists) {
            resourceSelect.value = currentValue;
        } else {
            resourceSelect.selectedIndex = 0;
        }

        updateUnit();
    }

    function toggleType() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';
        const financialFields = financialComplianceFields
            ? financialComplianceFields.querySelectorAll('input, select, textarea')
            : [];

        // Show/hide total fund amount
        if (isFinancial) {
            totalFundGroup.classList.remove('d-none');
            totalFundInput.required = true;
            totalFundInput.disabled = false;
            financialComplianceFields.classList.remove('d-none');
            financialFields.forEach(function (field) {
                field.disabled = false;
            });
        } else {
            totalFundGroup.classList.add('d-none');
            totalFundInput.required = false;
            totalFundInput.disabled = true;
            financialComplianceFields.classList.add('d-none');
            financialFields.forEach(function (field) {
                field.disabled = true;
                field.required = false;
            });
        }

        updateComplianceDependencies();
        updateResourceTypeOptions();
    }

    typeRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleType);
    });

    resourceSelect.addEventListener('change', function () {
        updateUnit();
    });

    programSelect.addEventListener('change', function () {
        updateResourceTypeOptions();
    });

    legalBasisType?.addEventListener('change', updateComplianceDependencies);
    fundSource?.addEventListener('change', updateComplianceDependencies);
    liquidationStatus?.addEventListener('change', updateComplianceDependencies);
    requiresFarmc?.addEventListener('change', updateComplianceDependencies);

    const overallComplianceStatus = document.getElementById('compliance_overall_status');
    const overallComplianceReasonGroup = document.getElementById('compliance_overall_reason_group');
    const overallComplianceReason = document.getElementById('compliance_overall_reason');

    function updateOverallComplianceReasonState() {
        if (!overallComplianceStatus || !overallComplianceReasonGroup) {
            return;
        }

        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';
        const showReason = isFinancial && overallComplianceStatus.value !== 'provided';
        overallComplianceReasonGroup.classList.toggle('d-none', !showReason);
        if (overallComplianceReason) {
            overallComplianceReason.disabled = !isFinancial;
            overallComplianceReason.required = showReason;
        }
    }

    overallComplianceStatus?.addEventListener('change', updateOverallComplianceReasonState);
    updateOverallComplianceReasonState();

    toggleType();
    updateComplianceDependencies();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('distributionEventCreateForm');
    if (!form) return;

    var submitButton = form.querySelector('button[type="submit"]');
    var ajaxNotice = document.getElementById('distributionEventCreateAjaxNotice');
    var toastEl = document.getElementById('distributionEventCreateToast');
    var toastMessageEl = document.getElementById('distributionEventCreateToastMessage');
    var toast = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }) : null;

    function showToast(type, message) {
        if (!toast || !toastEl || !toastMessageEl) {
            return;
        }

        var bgClass = 'text-bg-primary';
        if (type === 'success') bgClass = 'text-bg-success';
        if (type === 'error') bgClass = 'text-bg-danger';
        if (type === 'warning') bgClass = 'text-bg-warning';

        toastEl.className = 'toast align-items-center border-0 ' + bgClass;
        toastMessageEl.textContent = message;
        toast.show();
    }

    function clearNotice() {
        if (!ajaxNotice) return;
        ajaxNotice.className = 'alert d-none';
        ajaxNotice.textContent = '';
    }

    function showNotice(type, message, linkUrl, linkText) {
        if (!ajaxNotice) return;

        var cssClass = 'alert-info';
        if (type === 'success') cssClass = 'alert-success';
        if (type === 'error') cssClass = 'alert-danger';
        if (type === 'warning') cssClass = 'alert-warning';

        ajaxNotice.className = 'alert ' + cssClass;
        ajaxNotice.textContent = message;

        if (linkUrl && linkText) {
            var spacer = document.createTextNode(' ');
            var link = document.createElement('a');
            link.href = linkUrl;
            link.className = 'alert-link';
            link.textContent = linkText;
            ajaxNotice.appendChild(spacer);
            ajaxNotice.appendChild(link);
        }
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback.js-invalid-feedback').forEach(function (el) {
            el.remove();
        });

        form.querySelectorAll('.text-danger.js-inline-error').forEach(function (el) {
            el.remove();
        });
    }

    function setFieldError(fieldName, message) {
        var escapedFieldName = fieldName.replace(/"/g, '\\"');
        var selectors = ['[name="' + escapedFieldName + '"]'];

        if (fieldName.indexOf('.') !== -1) {
            var parts = fieldName.split('.');
            var bracketName = parts[0];

            for (var i = 1; i < parts.length; i++) {
                bracketName += '[' + parts[i] + ']';
            }

            selectors.push('[name="' + bracketName.replace(/"/g, '\\"') + '"]');
        }

        var elements = [];
        selectors.some(function (selector) {
            elements = Array.from(form.querySelectorAll(selector));
            return elements.length > 0;
        });

        if (!elements.length) {
            return;
        }

        var target = Array.from(elements).find(function (el) { return el.type !== 'hidden'; }) || elements[0];
        var isChoiceGroup = target.type === 'radio' || (target.type === 'checkbox' && elements.length > 1);

        elements.forEach(function (el) {
            if (el.type !== 'hidden') {
                el.classList.add('is-invalid');
            }
        });

        var feedbackParent = target.closest('.col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-12') || target.parentElement;
        if (!feedbackParent) {
            return;
        }

        if (isChoiceGroup) {
            var existingInline = feedbackParent.querySelector('.text-danger.small.js-inline-error');
            if (existingInline) {
                existingInline.textContent = message;
                return;
            }

            var inlineError = document.createElement('div');
            inlineError.className = 'text-danger small mt-1 js-inline-error';
            inlineError.textContent = message;
            feedbackParent.appendChild(inlineError);
            return;
        }

        var existingFeedback = feedbackParent.querySelector('.invalid-feedback.js-invalid-feedback');
        if (existingFeedback) {
            existingFeedback.textContent = message;
            return;
        }

        var feedback = document.createElement('div');
        feedback.className = 'invalid-feedback js-invalid-feedback';
        feedback.textContent = message;
        feedbackParent.appendChild(feedback);
    }

    function setSubmittingState(isSubmitting) {
        if (!submitButton) return;

        if (isSubmitting) {
            submitButton.disabled = true;
            submitButton.dataset.originalHtml = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...';
            return;
        }

        submitButton.disabled = false;
        if (submitButton.dataset.originalHtml) {
            submitButton.innerHTML = submitButton.dataset.originalHtml;
        }
    }

    function parseResponse(response) {
        return response.text().then(function (raw) {
            var data = {};
            if (raw) {
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    data = {};
                }
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    }

    function resetFormForNextEntry() {
        form.reset();
        clearFieldErrors();

        var physicalType = document.getElementById('type_physical');
        if (physicalType) {
            physicalType.checked = true;
            physicalType.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var resourceTypeSelect = document.getElementById('resource_type_id');
        if (resourceTypeSelect) {
            resourceTypeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function submitCreateRequest() {
        setSubmittingState(true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(parseResponse)
        .then(function (result) {
            if (result.ok) {
                showToast('success', result.data.message || 'Distribution event created successfully.');
                showNotice('success', result.data.message || 'Distribution event created successfully.', result.data.redirect_url, 'Open event details');
                resetFormForNextEntry();
                return;
            }

            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function (field) {
                    var messages = result.data.errors[field] || [];
                    if (messages.length > 0) {
                        setFieldError(field, messages.join(' '));
                    }
                });

                showToast('error', 'Please fix the highlighted fields and try again.');
                showNotice('error', 'Some required fields are missing or invalid. Please review the highlighted fields.');
                return;
            }

            showToast('error', result.data.message || 'Unable to create distribution event.');
            showNotice('error', result.data.message || 'Unable to create distribution event.');
        })
        .catch(function () {
            showToast('error', 'Network error. Please check your connection and try again.');
            showNotice('error', 'Network error. Please check your connection and try again.');
        })
        .finally(function () {
            setSubmittingState(false);
        });
    }

    var confirmationModalEl = document.getElementById('confirmationModal');
    var confirmationModal = confirmationModalEl ? new bootstrap.Modal(confirmationModalEl) : null;
    var confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    var summaryContent = document.getElementById('summaryContent');

    function getLabelFor(name) {
        var input = form.querySelector('[name="' + name + '"]');
        if (!input) return name;
        
        var id = input.id;
        if (id) {
            var label = form.querySelector('label[for="' + id + '"]');
            if (label) return label.textContent.replace('*', '').trim();
        }
        
        // Try parent label if it's a checkbox/radio
        var parentLabel = input.closest('.form-check')?.querySelector('.form-check-label');
        if (parentLabel) return parentLabel.textContent.trim();

        // Try preceding label
        var prevLabel = input.previousElementSibling;
        if (prevLabel && prevLabel.tagName === 'LABEL') return prevLabel.textContent.replace('*', '').trim();

        return name;
    }

    function updateSummary() {
        if (!summaryContent) return;
        
        var formData = new FormData(form);
        var html = '<div class="row g-3">';

        function addSummarySection(title, fields, icon, colorClass = 'primary') {
            var sectionHtml = '<div class="col-12 mb-2">' +
                '<div class="d-flex align-items-center mb-2">' +
                    '<div class="bg-' + colorClass + ' bg-opacity-10 text-' + colorClass + ' rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">' +
                        '<i class="bi ' + icon + '"></i>' +
                    '</div>' +
                    '<h6 class="mb-0 fw-bold text-uppercase small tracking-wider">' + title + '</h6>' +
                '</div>' +
                '<div class="card border-0 shadow-sm bg-white overflow-hidden">' +
                    '<div class="card-body p-3">' +
                        '<div class="row g-3">';
            
            var hasFields = false;

            fields.forEach(function(field) {
                var value = '';
                var label = '';
                
                if (typeof field === 'string') {
                    label = getLabelFor(field);
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input && input.tagName === 'SELECT') {
                        value = input.options[input.selectedIndex]?.text || 'N/A';
                    } else if (input && (input.type === 'checkbox' || input.type === 'radio')) {
                        if (input.type === 'radio') {
                             var checkedRadio = form.querySelector('input[name="' + field + '"]:checked');
                             // Find the label text for the radio
                             if (checkedRadio) {
                                 var labelEl = form.querySelector('label[for="' + checkedRadio.id + '"]');
                                 value = labelEl ? labelEl.textContent.trim() : checkedRadio.value;
                             } else {
                                 value = 'N/A';
                             }
                        } else {
                             value = input.checked ? 'Yes' : 'No';
                        }
                    } else {
                        value = formData.get(field) || 'N/A';
                    }
                } else {
                    label = field.label;
                    value = field.value;
                }

                if (value && value !== 'N/A' && !value.includes('Select')) {
                    hasFields = true;
                    sectionHtml += '<div class="col-md-6">' +
                        '<div class="small text-muted mb-0" style="font-size: 0.75rem;">' + label + '</div>' +
                        '<div class="fw-bold text-dark">' + value + '</div>' +
                    '</div>';
                }
            });

            sectionHtml += '</div></div></div></div>';
            if (hasFields) html += sectionHtml;
        }

        // 1. Core Configuration
        addSummarySection('Core Configuration', [
            'type',
            'name',
            'barangay_id',
            'distribution_date'
        ], 'bi-info-circle', 'primary');

        // 2. Resource Details
        addSummarySection('Resource Details', [
            'program_name_id',
            'resource_type_id'
        ], 'bi-box-seam', 'primary');

        // 3. Financial & Compliance (if financial)
        var type = form.querySelector('input[name="type"]:checked')?.value;
        if (type === 'financial') {
            addSummarySection('Financial Details', [
                'total_fund_amount',
                'fund_source',
                'trust_account_code',
                'fund_release_reference'
            ], 'bi-cash-stack', 'success');

            addSummarySection('Legal & Compliance', [
                'legal_basis_type',
                'legal_basis_reference_no',
                'legal_basis_date',
                'legal_basis_remarks',
                'liquidation_status',
                'liquidation_due_date',
                'liquidation_submitted_at',
                'liquidation_reference_no',
                'requires_farmc_endorsement',
                'farmc_reference_no',
                'farmc_endorsed_at',
                'compliance_overall_status',
                'compliance_overall_reason'
            ], 'bi-shield-check', 'success');
        }

        html += '</div>';
        summaryContent.innerHTML = html;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        clearNotice();
        clearFieldErrors();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        updateSummary();
        confirmationModal.show();
    });

    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function() {
            confirmationModal.hide();
            submitCreateRequest();
        });
    }
});
</script>
@endpush
@endsection
