@extends('layouts.app')

@section('title', 'Edit Distribution Event')

@section('content')
<div class="container-fluid py-4">
    @php
        $complianceStatusLabels = [
            'provided' => 'Provided',
            'not_available_yet' => 'Not available yet',
            'not_applicable' => 'Not applicable',
            'to_be_verified' => 'To be verified',
        ];
        $complianceStates = $event->complianceStates();
    @endphp

    {{-- Page Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-light border shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </div>
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item text-muted small"><a href="{{ route('distribution-events.index') }}" class="text-decoration-none">Distribution Events</a></li>
                    <li class="breadcrumb-item text-muted small"><a href="{{ route('distribution-events.show', $event) }}" class="text-decoration-none">{{ Str::limit($event->name, 40) }}</a></li>
                    <li class="breadcrumb-item active small" aria-current="page">Edit</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">Edit Distribution Event</h4>
        </div>
    </div>

    <div id="distributionEventEditAjaxNotice" class="alert d-none shadow-sm border-0 mb-4" role="alert"></div>

    <form id="distributionEventEditForm"
          action="{{ route('distribution-events.update', $event) }}"
          method="POST">
        @csrf
        @method('PUT')

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
                                       {{ old('type', $event->type) === 'physical' ? 'checked' : '' }}>
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
                                       {{ old('type', $event->type) === 'financial' ? 'checked' : '' }}>
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

                {{-- Event Configuration Card --}}
                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="bi bi-gear-fill me-2 text-primary"></i>
                            Event Configuration
                        </h6>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control border-0 bg-light @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $event->name) }}"
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
                                        <option value="" disabled>Select Barangay</option>
                                        @foreach($barangays as $barangay)
                                            <option value="{{ $barangay->id }}" {{ old('barangay_id', $event->barangay_id) == $barangay->id ? 'selected' : '' }}>
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
                                           value="{{ old('distribution_date', $event->distribution_date->format('Y-m-d')) }}" required>
                                    <label for="distribution_date">Distribution Date <span class="text-danger">*</span></label>
                                </div>
                                @error('distribution_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="border-light mb-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-0 bg-light @error('program_name_id') is-invalid @enderror"
                                            id="program_name_id" name="program_name_id" required>
                                        <option value="" disabled>Select Program</option>
                                        @foreach($programNames as $program)
                                            <option value="{{ $program->id }}"
                                                    data-agency-id="{{ $program->agency_id }}"
                                                    {{ old('program_name_id', $event->program_name_id) == $program->id ? 'selected' : '' }}>
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
                                <div class="form-floating position-relative">
                                    <select class="form-select border-0 bg-light @error('resource_type_id') is-invalid @enderror"
                                            id="resource_type_id" name="resource_type_id" required>
                                        <option value="" disabled>Select Resource Type</option>
                                        @foreach($resourceTypes as $type)
                                            <option value="{{ $type->id }}"
                                                    data-unit="{{ $type->unit }}"
                                                    data-agency-id="{{ $type->agency_id }}"
                                                    {{ old('resource_type_id', $event->resource_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="resource_type_id">Resource Type <span class="text-danger">*</span></label>
                                    <span id="unitDisplay" class="position-absolute end-0 top-50 translate-middle-y me-4 badge bg-primary bg-opacity-10 text-primary d-none fw-bold" style="z-index: 5;"></span>
                                </div>
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
                                               value="{{ old('total_fund_amount', $event->total_fund_amount) }}"
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

                {{-- Financial Compliance Section --}}
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
                                                <select class="form-select border-0 bg-light @error('legal_basis_type') is-invalid @enderror" id="legal_basis_type" name="legal_basis_type">
                                                    <option value="" disabled>Select type...</option>
                                                    <option value="resolution" {{ old('legal_basis_type', $event->legal_basis_type) === 'resolution' ? 'selected' : '' }}>Resolution</option>
                                                    <option value="ordinance" {{ old('legal_basis_type', $event->legal_basis_type) === 'ordinance' ? 'selected' : '' }}>Ordinance</option>
                                                    <option value="memo" {{ old('legal_basis_type', $event->legal_basis_type) === 'memo' ? 'selected' : '' }}>Memo</option>
                                                    <option value="special_order" {{ old('legal_basis_type', $event->legal_basis_type) === 'special_order' ? 'selected' : '' }}>Special Order</option>
                                                    <option value="other" {{ old('legal_basis_type', $event->legal_basis_type) === 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                <label for="legal_basis_type">Legal Basis Type</label>
                                            </div>
                                            @error('legal_basis_type')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-7">
                                            <div class="form-floating">
                                                <input type="text" maxlength="150" class="form-control border-0 bg-light @error('legal_basis_reference_no') is-invalid @enderror" id="legal_basis_reference_no" name="legal_basis_reference_no" value="{{ old('legal_basis_reference_no', $event->legal_basis_reference_no) }}" placeholder="Ref No.">
                                                <label for="legal_basis_reference_no">Reference No.</label>
                                            </div>
                                            @error('legal_basis_reference_no')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-5">
                                            <div class="form-floating">
                                                <input type="date" class="form-control border-0 bg-light @error('legal_basis_date') is-invalid @enderror" id="legal_basis_date" name="legal_basis_date" value="{{ old('legal_basis_date', optional($event->legal_basis_date)->format('Y-m-d')) }}">
                                                <label for="legal_basis_date">Basis Date</label>
                                            </div>
                                            @error('legal_basis_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div id="legalRemarksGroup" class="col-12 d-none">
                                            <div class="form-floating">
                                                <textarea class="form-control border-0 bg-light @error('legal_basis_remarks') is-invalid @enderror" id="legal_basis_remarks" name="legal_basis_remarks" style="height: 100px" placeholder="Remarks">{{ old('legal_basis_remarks', $event->legal_basis_remarks) }}</textarea>
                                                <label for="legal_basis_remarks">Legal Basis Remarks</label>
                                            </div>
                                            @error('legal_basis_remarks')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Fund Control Group --}}
                                <div class="col-md-6 border-start border-light ps-md-4">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Fund Control</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light @error('fund_source') is-invalid @enderror" id="fund_source" name="fund_source">
                                                    <option value="" disabled>Select source...</option>
                                                    <option value="lgu_trust_fund" {{ old('fund_source', $event->fund_source) === 'lgu_trust_fund' ? 'selected' : '' }}>LGU Trust Fund</option>
                                                    <option value="nga_transfer" {{ old('fund_source', $event->fund_source) === 'nga_transfer' ? 'selected' : '' }}>NGA Transfer</option>
                                                    <option value="local_program" {{ old('fund_source', $event->fund_source) === 'local_program' ? 'selected' : '' }}>Local Program</option>
                                                    <option value="other" {{ old('fund_source', $event->fund_source) === 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                <label for="fund_source">Fund Source</label>
                                            </div>
                                            @error('fund_source')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 d-none" id="trustAccountGroup">
                                            <div class="form-floating">
                                                <input type="text" maxlength="100" class="form-control border-0 bg-light @error('trust_account_code') is-invalid @enderror" id="trust_account_code" name="trust_account_code" value="{{ old('trust_account_code', $event->trust_account_code) }}" placeholder="Code">
                                                <label for="trust_account_code">Trust Account Code</label>
                                            </div>
                                            @error('trust_account_code')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="text" maxlength="150" class="form-control border-0 bg-light @error('fund_release_reference') is-invalid @enderror" id="fund_release_reference" name="fund_release_reference" value="{{ old('fund_release_reference', $event->fund_release_reference) }}" placeholder="Ref">
                                                <label for="fund_release_reference">Release Reference (DV/Check No.)</label>
                                            </div>
                                            @error('fund_release_reference')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
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
                                                <select class="form-select border-0 bg-light @error('liquidation_status') is-invalid @enderror" id="liquidation_status" name="liquidation_status">
                                                    <option value="not_required" {{ old('liquidation_status', $event->liquidation_status ?? 'not_required') === 'not_required' ? 'selected' : '' }}>Not Required</option>
                                                    <option value="pending" {{ old('liquidation_status', $event->liquidation_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="submitted" {{ old('liquidation_status', $event->liquidation_status) === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                    <option value="verified" {{ old('liquidation_status', $event->liquidation_status) === 'verified' ? 'selected' : '' }}>Verified</option>
                                                </select>
                                                <label for="liquidation_status">Liquidation Status</label>
                                            </div>
                                            @error('liquidation_status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 d-none" id="liquidationDueDateGroup">
                                            <div class="form-floating mb-2">
                                                <input type="date" class="form-control border-0 bg-light @error('liquidation_due_date') is-invalid @enderror" id="liquidation_due_date" name="liquidation_due_date" value="{{ old('liquidation_due_date', optional($event->liquidation_due_date)->format('Y-m-d')) }}">
                                                <label for="liquidation_due_date">Due Date</label>
                                            </div>
                                            @error('liquidation_due_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 d-none" id="liquidationSubmittedAtGroup">
                                            <div class="form-floating mb-2">
                                                <input type="datetime-local" class="form-control border-0 bg-light @error('liquidation_submitted_at') is-invalid @enderror" id="liquidation_submitted_at" name="liquidation_submitted_at" value="{{ old('liquidation_submitted_at', optional($event->liquidation_submitted_at)->format('Y-m-d\TH:i')) }}">
                                                <label for="liquidation_submitted_at">Date Submitted</label>
                                            </div>
                                            @error('liquidation_submitted_at')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 d-none" id="liquidationReferenceGroup">
                                            <div class="form-floating">
                                                <input type="text" maxlength="150" class="form-control border-0 bg-light @error('liquidation_reference_no') is-invalid @enderror" id="liquidation_reference_no" name="liquidation_reference_no" value="{{ old('liquidation_reference_no', $event->liquidation_reference_no) }}" placeholder="Ref">
                                                <label for="liquidation_reference_no">Liquidation Ref No.</label>
                                            </div>
                                            @error('liquidation_reference_no')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Special Endorsements --}}
                                <div class="col-md-6 border-start border-light ps-md-4">
                                    <h6 class="small fw-bold text-uppercase text-muted mb-3">Special Endorsements</h6>
                                    <div class="row g-2">
                                        <div class="col-12 mb-2">
                                            <div class="form-check form-switch p-3 bg-light rounded-3 shadow-none border-0">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <label class="form-check-label small fw-bold text-muted mb-0" for="requires_farmc_endorsement">
                                                        <i class="bi bi-person-check me-2"></i>Requires FARMC Endorsement
                                                    </label>
                                                    <input class="form-check-input ms-0" type="checkbox" role="switch" id="requires_farmc_endorsement" name="requires_farmc_endorsement" value="1" {{ old('requires_farmc_endorsement', $event->requires_farmc_endorsement) ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="farmcReferenceGroup" class="col-12 d-none mb-2">
                                            <div class="form-floating">
                                                <input type="text" maxlength="150" class="form-control border-0 bg-light @error('farmc_reference_no') is-invalid @enderror" id="farmc_reference_no" name="farmc_reference_no" value="{{ old('farmc_reference_no', $event->farmc_reference_no) }}" placeholder="Ref">
                                                <label for="farmc_reference_no">FARMC Reference No.</label>
                                            </div>
                                            @error('farmc_reference_no')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div id="farmcEndorsedAtGroup" class="col-12 d-none">
                                            <div class="form-floating">
                                                <input type="datetime-local" class="form-control border-0 bg-light @error('farmc_endorsed_at') is-invalid @enderror" id="farmc_endorsed_at" name="farmc_endorsed_at" value="{{ old('farmc_endorsed_at', optional($event->farmc_endorsed_at)->format('Y-m-d\TH:i')) }}">
                                                <label for="farmc_endorsed_at">Endorsement Date</label>
                                            </div>
                                            @error('farmc_endorsed_at')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 border-light"></div>

                                {{-- General Audit Status --}}
                                @php
                                    $defaultOverallStatus = data_get($complianceStates, 'legal_basis_type.status', 'not_available_yet');
                                    $defaultOverallReason = data_get($complianceStates, 'legal_basis_type.reason');
                                    $overallStatus = old('compliance_overall_status', $defaultOverallStatus);
                                    $overallReason = old('compliance_overall_reason', $defaultOverallReason);
                                    $overallReasonHidden = $overallStatus === 'provided';
                                @endphp
                                <div class="col-12">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-4">
                                            <div class="form-floating">
                                                <select class="form-select border-0 bg-light fw-bold @error('compliance_overall_status') is-invalid @enderror" id="compliance_overall_status" name="compliance_overall_status">
                                                    @foreach($complianceStatusLabels as $statusValue => $statusLabel)
                                                        <option value="{{ $statusValue }}" {{ $overallStatus === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="compliance_overall_status">Verification Status</label>
                                            </div>
                                            @error('compliance_overall_status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-8 {{ $overallReasonHidden ? 'd-none' : '' }}" id="compliance_overall_reason_group">
                                            <div class="form-floating">
                                                <textarea class="form-control border-0 bg-light @error('compliance_overall_reason') is-invalid @enderror" id="compliance_overall_reason" name="compliance_overall_reason" placeholder="Reason" style="height: 60px">{{ $overallReason }}</textarea>
                                                <label for="compliance_overall_reason">Pending Reason / Missing Documents</label>
                                            </div>
                                            @error('compliance_overall_reason')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sticky Actions --}}
            <div class="col-12 col-xl-4">
                <div class="sticky-top" style="top: 1.5rem; z-index: 10;">
                    {{-- Action Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm py-3 fw-bold">
                                    <i class="bi bi-check-lg me-2"></i> Save Changes
                                </button>
                                <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary py-2">
                                    Cancel
                                </a>
                            </div>
                            <hr class="my-4">
                            <div class="text-start">
                                <h6 class="small fw-bold text-uppercase text-muted mb-3">Event Info</h6>
                                <div class="d-flex align-items-center mb-2 small text-muted">
                                    <i class="bi bi-calendar3 text-primary me-2"></i>
                                    Created {{ $event->created_at->format('M d, Y') }}
                                </div>
                                <div class="d-flex align-items-center mb-2 small text-muted">
                                    <i class="bi bi-info-circle text-warning me-2"></i>
                                    Only Pending events can be edited
                                </div>
                                <div class="d-flex align-items-center small">
                                    <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem; color: #198754;"></i>
                                    <span class="badge bg-warning text-dark">{{ $event->status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title d-flex align-items-center" id="confirmationModalLabel">
                        <i class="bi bi-check2-circle me-2 fs-4"></i>
                        Confirm Distribution Event Changes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted mb-4">Please review the changes below before updating.</p>
                    <div id="summaryContent" class="mb-0" style="max-height: 60vh; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-pencil me-1"></i> Make Changes
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 shadow-sm" id="confirmSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Confirm & Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="distributionEventEditToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex p-2">
                <div class="toast-body fw-medium" id="distributionEventEditToastMessage"></div>
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

    .form-control:focus, .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .transition-all { transition: all 0.3s ease-in-out; }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        line-height: 1;
        vertical-align: middle;
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

    .card { border-radius: 16px !important; }
    .btn-lg { border-radius: 12px !important; }
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
    const allOptions = Array.from(resourceSelect.options);

    function setGroupState(groupEl, inputEl, show, required = false) {
        if (!groupEl || !inputEl) return;
        groupEl.classList.toggle('d-none', !show);
        inputEl.disabled = !show;
        inputEl.required = false;
        if (!show && inputEl.type !== 'checkbox') inputEl.value = '';
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
        allOptions.forEach(function (opt) {
            if (opt.value === '') { resourceSelect.appendChild(opt.cloneNode(true)); return; }
            const matchesType = isFinancial ? (opt.dataset.unit === 'PHP') : (opt.dataset.unit !== 'PHP');
            const matchesAgency = !agencyId || opt.dataset.agencyId === agencyId;
            if (matchesType && matchesAgency) resourceSelect.appendChild(opt.cloneNode(true));
        });

        const exists = Array.from(resourceSelect.options).some(o => o.value === currentValue);
        if (exists) resourceSelect.value = currentValue;
        else resourceSelect.selectedIndex = 0;
        updateUnit();
    }

    function toggleType() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';
        if (isFinancial) {
            totalFundGroup.classList.remove('d-none');
            totalFundInput.required = true;
            financialComplianceFields.classList.remove('d-none');
        } else {
            totalFundGroup.classList.add('d-none');
            totalFundInput.required = false;
            financialComplianceFields.classList.add('d-none');
        }
        updateComplianceDependencies();
        updateResourceTypeOptions();
    }

    typeRadios.forEach(r => r.addEventListener('change', toggleType));
    resourceSelect.addEventListener('change', updateUnit);
    programSelect?.addEventListener('change', updateResourceTypeOptions);
    legalBasisType?.addEventListener('change', updateComplianceDependencies);
    fundSource?.addEventListener('change', updateComplianceDependencies);
    liquidationStatus?.addEventListener('change', updateComplianceDependencies);
    requiresFarmc?.addEventListener('change', updateComplianceDependencies);

    const overallComplianceStatus = document.getElementById('compliance_overall_status');
    const overallComplianceReasonGroup = document.getElementById('compliance_overall_reason_group');
    const overallComplianceReason = document.getElementById('compliance_overall_reason');

    function updateOverallComplianceReasonState() {
        if (!overallComplianceStatus || !overallComplianceReasonGroup) return;
        const showReason = overallComplianceStatus.value !== 'provided';
        overallComplianceReasonGroup.classList.toggle('d-none', !showReason);
        if (overallComplianceReason) overallComplianceReason.required = showReason;
    }

    overallComplianceStatus?.addEventListener('change', updateOverallComplianceReasonState);
    updateOverallComplianceReasonState();
    toggleType();
    updateComplianceDependencies();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('distributionEventEditForm');
    if (!form) return;

    var submitButton = form.querySelector('button[type="submit"]');
    var ajaxNotice = document.getElementById('distributionEventEditAjaxNotice');
    var toastEl = document.getElementById('distributionEventEditToast');
    var toastMessageEl = document.getElementById('distributionEventEditToastMessage');
    var toast = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }) : null;

    function showToast(type, message) {
        if (!toast || !toastEl || !toastMessageEl) return;
        var bgClass = type === 'success' ? 'text-bg-success' : type === 'error' ? 'text-bg-danger' : 'text-bg-primary';
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
        var cssClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-warning';
        ajaxNotice.className = 'alert shadow-sm border-0 ' + cssClass;
        ajaxNotice.textContent = message;
        if (linkUrl && linkText) {
            var link = document.createElement('a');
            link.href = linkUrl;
            link.className = 'alert-link ms-1';
            link.textContent = linkText;
            ajaxNotice.appendChild(link);
        }
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback.js-invalid-feedback, .text-danger.js-inline-error').forEach(el => el.remove());
    }

    function setFieldError(fieldName, message) {
        var escapedFieldName = fieldName.replace(/"/g, '\\"');
        var selectors = ['[name="' + escapedFieldName + '"]'];
        if (fieldName.indexOf('.') !== -1) {
            var parts = fieldName.split('.');
            var bracketName = parts[0];
            for (var i = 1; i < parts.length; i++) bracketName += '[' + parts[i] + ']';
            selectors.push('[name="' + bracketName.replace(/"/g, '\\"') + '"]');
        }
        var elements = [];
        selectors.some(function (selector) {
            elements = Array.from(form.querySelectorAll(selector));
            return elements.length > 0;
        });
        if (!elements.length) return;
        var target = elements.find(el => el.type !== 'hidden') || elements[0];
        var isChoiceGroup = target.type === 'radio' || (target.type === 'checkbox' && elements.length > 1);
        elements.forEach(el => { if (el.type !== 'hidden') el.classList.add('is-invalid'); });
        var feedbackParent = target.closest('.col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-12') || target.parentElement;
        if (!feedbackParent) return;
        if (isChoiceGroup) {
            var existingInline = feedbackParent.querySelector('.text-danger.small.js-inline-error');
            if (existingInline) { existingInline.textContent = message; return; }
            var inlineError = document.createElement('div');
            inlineError.className = 'text-danger small mt-1 js-inline-error';
            inlineError.textContent = message;
            feedbackParent.appendChild(inlineError);
            return;
        }
        var existingFeedback = feedbackParent.querySelector('.invalid-feedback.js-invalid-feedback');
        if (existingFeedback) { existingFeedback.textContent = message; return; }
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
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
        } else {
            submitButton.disabled = false;
            if (submitButton.dataset.originalHtml) submitButton.innerHTML = submitButton.dataset.originalHtml;
        }
    }

    function parseResponse(response) {
        return response.text().then(function (raw) {
            var data = {};
            try { data = raw ? JSON.parse(raw) : {}; } catch (e) {}
            return { ok: response.ok, status: response.status, data: data };
        });
    }

    function submitUpdateRequest() {
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
                showToast('success', result.data.message || 'Distribution event updated successfully.');
                if (result.data.redirect_url) {
                    setTimeout(() => { window.location.href = result.data.redirect_url; }, 1000);
                }
                return;
            }
            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function (field) {
                    var messages = result.data.errors[field] || [];
                    if (messages.length > 0) setFieldError(field, messages.join(' '));
                });
                showToast('error', 'Please fix the highlighted fields and try again.');
                showNotice('error', 'Some required fields are missing or invalid. Please review the highlighted fields.');
                return;
            }
            showToast('error', result.data.message || 'Unable to update distribution event.');
            showNotice('error', result.data.message || 'Unable to update distribution event.');
        })
        .catch(function () {
            showToast('error', 'Network error. Please check your connection and try again.');
            showNotice('error', 'Network error. Please check your connection and try again.');
        })
        .finally(function () { setSubmittingState(false); });
    }

    var confirmationModalEl = document.getElementById('confirmationModal');
    var confirmationModal = confirmationModalEl ? new bootstrap.Modal(confirmationModalEl) : null;
    var confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    var summaryContent = document.getElementById('summaryContent');

    function getLabelFor(name) {
        var input = form.querySelector('[name="' + name + '"]');
        if (!input) return name;
        if (input.id) {
            var label = form.querySelector('label[for="' + input.id + '"]');
            if (label) return label.textContent.replace('*', '').trim();
        }
        var parentLabel = input.closest('.form-check')?.querySelector('.form-check-label');
        if (parentLabel) return parentLabel.textContent.trim();
        return name;
    }

    function updateSummary() {
        if (!summaryContent) return;
        var formData = new FormData(form);
        var html = '<div class="row g-3">';

        function addSummarySection(title, fields, icon, colorClass) {
            colorClass = colorClass || 'primary';
            var sectionHtml = '<div class="col-12 mb-2">' +
                '<div class="d-flex align-items-center mb-2">' +
                    '<div class="bg-' + colorClass + ' bg-opacity-10 text-' + colorClass + ' rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;">' +
                        '<i class="bi ' + icon + '"></i></div>' +
                    '<h6 class="mb-0 fw-bold text-uppercase small">' + title + '</h6>' +
                '</div>' +
                '<div class="card border-0 shadow-sm bg-white overflow-hidden"><div class="card-body p-3"><div class="row g-3">';

            var hasFields = false;
            fields.forEach(function (field) {
                var value = '', label = '';
                if (typeof field === 'string') {
                    label = getLabelFor(field);
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input && input.tagName === 'SELECT') {
                        value = input.options[input.selectedIndex]?.text || 'N/A';
                    } else if (input && input.type === 'radio') {
                        var checked = form.querySelector('input[name="' + field + '"]:checked');
                        if (checked) {
                            var lbl = form.querySelector('label[for="' + checked.id + '"]');
                            value = lbl ? lbl.textContent.trim() : checked.value;
                        }
                    } else if (input && input.type === 'checkbox') {
                        value = input.checked ? 'Yes' : 'No';
                    } else {
                        value = formData.get(field) || 'N/A';
                    }
                } else {
                    label = field.label; value = field.value;
                }
                if (value && value !== 'N/A' && !value.includes('Select')) {
                    hasFields = true;
                    sectionHtml += '<div class="col-md-6"><div class="small text-muted" style="font-size:.75rem;">' + label + '</div><div class="fw-bold text-dark">' + value + '</div></div>';
                }
            });

            sectionHtml += '</div></div></div></div>';
            if (hasFields) html += sectionHtml;
        }

        addSummarySection('Event Details', ['type', 'name', 'barangay_id', 'distribution_date'], 'bi-calendar-event', 'primary');
        addSummarySection('Resource Details', ['program_name_id', 'resource_type_id'], 'bi-box-seam', 'primary');

        var type = form.querySelector('input[name="type"]:checked')?.value;
        if (type === 'financial') {
            addSummarySection('Financial Details', ['total_fund_amount', 'fund_source', 'trust_account_code', 'fund_release_reference'], 'bi-cash-stack', 'success');
            addSummarySection('Legal & Compliance', ['legal_basis_type', 'legal_basis_reference_no', 'legal_basis_date', 'liquidation_status', 'liquidation_due_date', 'compliance_overall_status', 'compliance_overall_reason'], 'bi-shield-check', 'success');
        }

        html += '</div>';
        summaryContent.innerHTML = html;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearNotice();
        clearFieldErrors();
        if (!form.checkValidity()) { form.reportValidity(); return; }
        updateSummary();
        confirmationModal.show();
    });

    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function () {
            confirmationModal.hide();
            submitUpdateRequest();
        });
    }
});
</script>
@endpush
@endsection
