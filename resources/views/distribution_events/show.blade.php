@extends('layouts.app')

@section('title', 'Distribution Event Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item active">Event #{{ $event->id }}</li>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('import_error_report_file'))
        <div class="alert alert-warning d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2" role="alert">
            <div>
                <strong>CSV Import Notice:</strong>
                {{ session('import_error_report_count', 0) }} row(s) were skipped during import.
                Download the detailed error report for row-level issues.
            </div>
            <a href="{{ route('allocations.importCsvErrorsReport', ['report' => session('import_error_report_file')]) }}"
               class="btn btn-sm btn-outline-dark">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Error Report
            </a>
        </div>
    @endif

    @php
        $availableBeneficiaries = \App\Models\Beneficiary::where('barangay_id', $event->barangay_id)
            ->where('status', 'Active')
            ->whereNotIn('id', $allocatedBeneficiaryIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'classification']);
    @endphp

    {{-- ============================================================ --}}
    {{-- 1. EVENT HEADER CARD                                         --}}
    {{-- ============================================================ --}}
    @php
        $statusBadge = match($event->status) {
            'Pending'   => 'bg-info',
            'Ongoing'   => 'bg-warning text-dark',
            'Completed' => 'bg-success',
            default     => 'bg-secondary',
        };
        $legalBasisLabels = [
            'resolution' => 'Resolution',
            'ordinance' => 'Ordinance',
            'memo' => 'Memo',
            'special_order' => 'Special Order',
            'other' => 'Other',
        ];
        $fundSourceLabels = [
            'lgu_trust_fund' => 'LGU Trust Fund',
            'nga_transfer' => 'NGA Transfer',
            'local_program' => 'Local Program',
            'other' => 'Other',
        ];
        $agencyName = $event->resourceType->agency->name ?? 'N/A';
        $agencyBadge = match($agencyName) {
            'DA'   => 'bg-success',
            'BFAR' => 'bg-primary',
            'DAR'  => 'bg-warning text-dark',
            'LGU'  => 'bg-secondary',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2 gap-sm-0">
            <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">{{ $event->barangay->name }}</h1>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge {{ $statusBadge }}">{{ $event->status }}</span>
                    <span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span>
                    @if($event->isFinancial())
                        <span class="badge bg-success">Financial</span>
                    @else
                        <span class="badge bg-secondary">Physical</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            @if($event->status === 'Pending')
                <a href="{{ route('distribution-events.edit', $event) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            @endif

            {{-- Status transition buttons --}}
            @if($event->status === 'Pending')
                @if(!$event->beneficiary_list_approved_at && Auth::user()->role === 'admin')
                    <form method="POST"
                          action="{{ route('distribution-events.approveBeneficiaryList', $event) }}"
                          class="d-inline"
                          data-confirm-title="Approve Beneficiary List"
                          data-confirm-message="Confirm beneficiary list review and approval for this event?">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-check2-square me-1"></i> Approve Beneficiary List
                        </button>
                    </form>
                @endif

                <button type="button" class="btn btn-warning btn-status-change"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-status="Ongoing"
                        data-title="Mark as Ongoing"
                        data-message="Are you sure you want to start this distribution event? The status will change from Pending to Ongoing."
                        {{ $event->beneficiary_list_approved_at ? '' : 'disabled' }}>
                    <i class="bi bi-play-fill me-1"></i> Mark as Ongoing
                </button>
            @endif

            @if($event->status === 'Ongoing' && Auth::user()->role === 'admin')
                <button type="button" class="btn btn-success btn-status-change"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-status="Completed"
                        data-title="Mark as Completed"
                        data-message="Are you sure you want to mark this event as Completed? This action cannot be reversed.">
                    <i class="bi bi-check-circle me-1"></i> Mark as Completed
                </button>
            @endif

            <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Back to List
            </a>
            <a href="{{ route('distribution-events.distributionList', $event) }}" class="btn btn-outline-dark">
                <i class="bi bi-printer me-1"></i> Print Distribution List
            </a>
        </div>
    </div>

    @if($event->status === 'Completed')
        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4" role="alert">
            <div>
                <strong>Next Step:</strong> Generate and review the distribution summary report for agency submission.
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-bar-chart-line me-1"></i> Open Reports
            </a>
        </div>
    @endif

    {{-- Event Details Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Event Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Barangay</div>
                    <div class="fw-semibold">{{ $event->barangay->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Resource Type</div>
                    <div class="fw-semibold">{{ $event->resourceType->name }} ({{ $event->resourceType->unit }})</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Source Agency</div>
                    <div class="fw-semibold"><span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Distribution Type</div>
                    <div class="fw-semibold">
                        @if($event->isFinancial())
                            <span class="badge bg-success">Financial Assistance</span>
                        @else
                            <span class="badge bg-secondary">Physical Resources</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Distribution Date</div>
                    <div class="fw-semibold">{{ $event->distribution_date->format('M d, Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold"><span class="badge {{ $statusBadge }}">{{ $event->status }}</span></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Beneficiary List Approval</div>
                    <div class="fw-semibold">
                        @if($event->beneficiary_list_approved_at)
                            <span class="badge bg-success">Approved</span>
                            <div class="small text-muted mt-1">
                                {{ $event->beneficiary_list_approved_at->format('M d, Y h:i A') }}
                                @if($event->beneficiaryListApprovedBy)
                                    by {{ $event->beneficiaryListApprovedBy->name }}
                                @endif
                            </div>
                        @else
                            <span class="badge bg-secondary">Pending Approval</span>
                        @endif
                    </div>
                </div>
                @if($event->isFinancial())
                    <div class="col-md-4">
                        <div class="text-muted small">Total Fund Budget</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->total_fund_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Total Disbursed</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->allocations->sum('amount'), 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Remaining Budget</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->total_fund_amount - $event->allocations->sum('amount'), 2) }}</div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="text-muted small">Created By</div>
                    <div class="fw-semibold">{{ $event->createdBy->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Created At</div>
                    <div class="fw-semibold">{{ $event->created_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($event->isFinancial())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Legal and Compliance
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Legal Basis</div>
                        <div class="fw-semibold">{{ $legalBasisLabels[$event->legal_basis_type] ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Reference No.</div>
                        <div class="fw-semibold">{{ $event->legal_basis_reference_no ?: 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Fund Source</div>
                        <div class="fw-semibold">{{ $fundSourceLabels[$event->fund_source] ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Liquidation Status</div>
                        <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $event->liquidation_status ?? 'not_required')) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Liquidation Due Date</div>
                        <div class="fw-semibold">{{ $event->liquidation_due_date?->format('M d, Y') ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">FARMC Endorsement</div>
                        <div class="fw-semibold">
                            @if($event->requires_farmc_endorsement)
                                {{ $event->farmc_endorsed_at ? 'Endorsed' : 'Required (Pending)' }}
                            @else
                                Not Required
                            @endif
                        </div>
                    </div>
                </div>

                @if($event->status !== 'Completed')
                    <form method="POST"
                          action="{{ route('distribution-events.updateCompliance', $event) }}"
                          data-confirm-title="Update Compliance Details"
                          data-confirm-message="Save legal/compliance updates for this event?">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="legal_basis_type" class="form-label">Legal Basis Type</label>
                                <select class="form-select @error('legal_basis_type') is-invalid @enderror" id="legal_basis_type" name="legal_basis_type">
                                    <option value="">Select legal basis type</option>
                                    <option value="resolution" {{ old('legal_basis_type', $event->legal_basis_type) === 'resolution' ? 'selected' : '' }}>Resolution</option>
                                    <option value="ordinance" {{ old('legal_basis_type', $event->legal_basis_type) === 'ordinance' ? 'selected' : '' }}>Ordinance</option>
                                    <option value="memo" {{ old('legal_basis_type', $event->legal_basis_type) === 'memo' ? 'selected' : '' }}>Memo</option>
                                    <option value="special_order" {{ old('legal_basis_type', $event->legal_basis_type) === 'special_order' ? 'selected' : '' }}>Special Order</option>
                                    <option value="other" {{ old('legal_basis_type', $event->legal_basis_type) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="legal_basis_reference_no" class="form-label">Legal Basis Reference No.</label>
                                <input type="text" class="form-control @error('legal_basis_reference_no') is-invalid @enderror" maxlength="150" id="legal_basis_reference_no" name="legal_basis_reference_no" value="{{ old('legal_basis_reference_no', $event->legal_basis_reference_no) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="legal_basis_date" class="form-label">Legal Basis Date</label>
                                <input type="date" class="form-control @error('legal_basis_date') is-invalid @enderror" id="legal_basis_date" name="legal_basis_date" value="{{ old('legal_basis_date', $event->legal_basis_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6" id="showTrustAccountGroup">
                                <label for="fund_source" class="form-label">Fund Source</label>
                                <select class="form-select @error('fund_source') is-invalid @enderror" id="fund_source" name="fund_source">
                                    <option value="">Select fund source</option>
                                    <option value="lgu_trust_fund" {{ old('fund_source', $event->fund_source) === 'lgu_trust_fund' ? 'selected' : '' }}>LGU Trust Fund</option>
                                    <option value="nga_transfer" {{ old('fund_source', $event->fund_source) === 'nga_transfer' ? 'selected' : '' }}>NGA Transfer</option>
                                    <option value="local_program" {{ old('fund_source', $event->fund_source) === 'local_program' ? 'selected' : '' }}>Local Program</option>
                                    <option value="other" {{ old('fund_source', $event->fund_source) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="showTrustAccountInputGroup">
                                <label for="trust_account_code" class="form-label">Trust Account Code</label>
                                <input type="text" class="form-control @error('trust_account_code') is-invalid @enderror" maxlength="100" id="trust_account_code" name="trust_account_code" value="{{ old('trust_account_code', $event->trust_account_code) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="fund_release_reference" class="form-label">Fund Release Reference</label>
                                <input type="text" class="form-control @error('fund_release_reference') is-invalid @enderror" maxlength="150" id="fund_release_reference" name="fund_release_reference" value="{{ old('fund_release_reference', $event->fund_release_reference) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="liquidation_status" class="form-label">Liquidation Status</label>
                                <select class="form-select @error('liquidation_status') is-invalid @enderror" id="liquidation_status" name="liquidation_status">
                                    <option value="not_required" {{ old('liquidation_status', $event->liquidation_status ?? 'not_required') === 'not_required' ? 'selected' : '' }}>Not Required</option>
                                    <option value="pending" {{ old('liquidation_status', $event->liquidation_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="submitted" {{ old('liquidation_status', $event->liquidation_status) === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="verified" {{ old('liquidation_status', $event->liquidation_status) === 'verified' ? 'selected' : '' }}>Verified</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="showLiqDueGroup">
                                <label for="liquidation_due_date" class="form-label">Liquidation Due Date</label>
                                <input type="date" class="form-control @error('liquidation_due_date') is-invalid @enderror" id="liquidation_due_date" name="liquidation_due_date" value="{{ old('liquidation_due_date', $event->liquidation_due_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4" id="showLiqSubmittedGroup">
                                <label for="liquidation_submitted_at" class="form-label">Liquidation Submitted At</label>
                                <input type="datetime-local" class="form-control @error('liquidation_submitted_at') is-invalid @enderror" id="liquidation_submitted_at" name="liquidation_submitted_at" value="{{ old('liquidation_submitted_at', $event->liquidation_submitted_at?->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-4" id="showLiqReferenceGroup">
                                <label for="liquidation_reference_no" class="form-label">Liquidation Reference No.</label>
                                <input type="text" class="form-control @error('liquidation_reference_no') is-invalid @enderror" maxlength="150" id="liquidation_reference_no" name="liquidation_reference_no" value="{{ old('liquidation_reference_no', $event->liquidation_reference_no) }}">
                            </div>
                            <div class="col-md-4" id="showFarmcReferenceGroup">
                                <label for="farmc_reference_no" class="form-label">FARMC Reference No.</label>
                                <input type="text" class="form-control @error('farmc_reference_no') is-invalid @enderror" maxlength="150" id="farmc_reference_no" name="farmc_reference_no" value="{{ old('farmc_reference_no', $event->farmc_reference_no) }}">
                            </div>
                            <div class="col-md-4" id="showFarmcEndorsedGroup">
                                <label for="farmc_endorsed_at" class="form-label">FARMC Endorsed At</label>
                                <input type="datetime-local" class="form-control @error('farmc_endorsed_at') is-invalid @enderror" id="farmc_endorsed_at" name="farmc_endorsed_at" value="{{ old('farmc_endorsed_at', $event->farmc_endorsed_at?->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="requires_farmc_endorsement" name="requires_farmc_endorsement" {{ old('requires_farmc_endorsement', $event->requires_farmc_endorsement) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_farmc_endorsement">Requires FARMC endorsement</label>
                                </div>
                            </div>
                            <div class="col-md-12" id="showLegalRemarksGroup">
                                <label for="legal_basis_remarks" class="form-label">Legal/Compliance Remarks</label>
                                <textarea class="form-control @error('legal_basis_remarks') is-invalid @enderror" id="legal_basis_remarks" name="legal_basis_remarks" rows="2" maxlength="1000">{{ old('legal_basis_remarks', $event->legal_basis_remarks) }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-save me-1"></i> Save Compliance Details
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- 2. ALLOCATION SUMMARY CARDS                                  --}}
    {{-- ============================================================ --}}
    @php
        $totalAllocated  = $event->allocations->count();
        $totalDistributed = $event->allocations->whereNotNull('distributed_at')->count();
    @endphp

    @if($event->isFinancial())
        @php
            $totalAmountAllocated = $event->allocations->sum('amount');
            $totalClaimed = $event->allocations->whereNotNull('distributed_at')->count();
            $remainingBudget = $event->total_fund_amount - $totalAmountAllocated;
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Beneficiaries Allocated</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-cash-stack text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Amount Allocated</div>
                            <div class="fs-4 fw-bold">&#8369;{{ number_format($totalAmountAllocated, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Claimed</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalClaimed) }} / {{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-wallet2 text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Remaining Budget</div>
                            <div class="fs-4 fw-bold">&#8369;{{ number_format($remainingBudget, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $totalQuantity = $event->allocations->sum('quantity');
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-4 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Beneficiaries Allocated</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-box-seam text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Quantity to Distribute</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalQuantity, 2) }} {{ $event->resourceType->unit }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Distributed</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalDistributed) }} / {{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $bulkEligibleCount = $event->allocations->filter(function ($allocation) {
            return ! $allocation->distributed_at && $allocation->release_outcome !== 'not_received';
        })->count();
    @endphp

    {{-- ============================================================ --}}
    {{-- 3. ADD BENEFICIARIES SECTION                                 --}}
    {{-- ============================================================ --}}
    @if($event->status !== 'Completed')
        <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                <i class="bi bi-plus-lg me-1"></i> Add Beneficiary
            </button>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addAllModal">
                <i class="bi bi-people me-1"></i> Add All Barangay Beneficiaries
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
            </button>
        </div>
    @endif

    @if($event->status !== 'Pending' && $bulkEligibleCount > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">Bulk Release Update</div>
                    <div class="text-muted small">
                        Select multiple eligible rows and update release outcome in one action.
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-success btn-sm" data-bulk-release-action="distributed">
                        <i class="bi bi-check2-all me-1"></i> Mark Selected as {{ $event->isFinancial() ? 'Claimed' : 'Distributed' }}
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bulk-release-action="not_received">
                        <i class="bi bi-x-circle me-1"></i> Mark Selected as Not Received
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('allocations.bulkReleaseOutcome') }}" id="bulkReleaseForm" class="d-none">
            @csrf
            <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
            <input type="hidden" name="action" id="bulkReleaseAction" value="">
            <div id="bulkReleaseIds"></div>
        </form>
    @endif

    {{-- ============================================================ --}}
    {{-- 4. ALLOCATIONS TABLE                                         --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Allocations
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            @if($event->status !== 'Pending' && $bulkEligibleCount > 0)
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input" id="bulkSelectAll" aria-label="Select all eligible allocations">
                                </th>
                            @endif
                            <th>#</th>
                            <th>Beneficiary Name</th>
                            <th>Classification</th>
                            <th>Contact Number</th>
                            @if($event->isFinancial())
                                <th>Amount (PHP)</th>
                            @else
                                <th>Quantity</th>
                            @endif
                            <th>Distributed At</th>
                            <th>Remarks</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($event->allocations as $allocation)
                            <tr class="{{ $allocation->distributed_at ? 'table-success' : '' }}">
                                @if($event->status !== 'Pending' && $bulkEligibleCount > 0)
                                    <td class="text-center" data-label="Select">
                                        @if(!$allocation->distributed_at && $allocation->release_outcome !== 'not_received')
                                            <input type="checkbox"
                                                   class="form-check-input bulk-allocation-checkbox"
                                                   value="{{ $allocation->id }}"
                                                   aria-label="Select allocation for {{ $allocation->beneficiary->full_name }}">
                                        @endif
                                    </td>
                                @endif
                                <td class="text-muted" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Beneficiary Name">{{ $allocation->beneficiary->full_name }}</td>
                                <td data-label="Classification">
                                    @php
                                        $classificationBadge = match($allocation->beneficiary->classification) {
                                            'Farmer'     => 'bg-primary',
                                            'Fisherfolk' => 'bg-info text-dark',
                                            'Both'       => '',
                                            default      => 'bg-secondary',
                                        };
                                    @endphp
                                    @if($allocation->beneficiary->classification === 'Both')
                                        <span class="badge" style="background-color: var(--color-purple);">Both</span>
                                    @else
                                        <span class="badge {{ $classificationBadge }}">{{ $allocation->beneficiary->classification }}</span>
                                    @endif
                                </td>
                                <td data-label="Contact Number">{{ $allocation->beneficiary->contact_number ?? '—' }}</td>
                                @if($event->isFinancial())
                                    <td data-label="Amount (PHP)">&#8369;{{ number_format($allocation->amount, 2) }}</td>
                                @else
                                    <td data-label="Quantity">{{ number_format($allocation->quantity, 2) }} {{ $event->resourceType->unit }}</td>
                                @endif
                                <td data-label="Distributed At">
                                    @if($allocation->distributed_at)
                                        <span class="text-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            {{ $allocation->distributed_at->format('M d, Y h:i A') }}
                                        </span>
                                    @elseif($allocation->release_outcome === 'not_received')
                                        <span class="text-danger">
                                            <i class="bi bi-x-circle-fill me-1"></i>
                                            Not Received
                                        </span>
                                    @else
                                        <span class="text-muted">Not yet {{ $event->isFinancial() ? 'claimed' : 'distributed' }}</span>
                                    @endif
                                </td>
                                <td data-label="Remarks">{{ $allocation->remarks ?? '—' }}</td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    @if($event->status !== 'Completed' && in_array(Auth::user()->role, ['admin', 'staff'], true))
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editAllocationModal"
                                                data-update-url="{{ route('allocations.update', $allocation) }}"
                                                data-beneficiary-name="{{ $allocation->beneficiary->full_name }}"
                                                data-assistance-purpose-id="{{ $allocation->assistance_purpose_id ?? '' }}"
                                                data-remarks="{{ $allocation->remarks ?? '' }}"
                                                data-amount="{{ $allocation->amount ?? '' }}"
                                                data-quantity="{{ $allocation->quantity ?? '' }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                    @endif

                                    @if(!$allocation->distributed_at && $allocation->release_outcome !== 'not_received' && $event->status !== 'Pending')
                                        <form method="POST"
                                              action="{{ route('allocations.markDistributed', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Distribution"
                                              data-confirm-message="Mark this allocation as {{ $event->isFinancial() ? 'claimed' : 'distributed' }}? This will timestamp the transaction.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                                <i class="bi bi-check2"></i> {{ $event->isFinancial() ? 'Mark as Claimed' : 'Distribute' }}
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('allocations.markNotReceived', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Not Received"
                                              data-confirm-message="Mark this allocation as Not Received for this release schedule?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger me-1">
                                                <i class="bi bi-x-lg"></i> Not Received
                                            </button>
                                        </form>
                                    @endif

                                    @if($event->status !== 'Completed' && Auth::user()->role === 'admin')
                                        <form method="POST"
                                              action="{{ route('allocations.destroy', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Removal"
                                              data-confirm-message="Are you sure you want to remove {{ $allocation->beneficiary->full_name }} from this distribution event?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($event->status !== 'Pending' && $bulkEligibleCount > 0) ? 9 : 8 }}" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No beneficiaries allocated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODALS                                                       --}}
{{-- ============================================================ --}}

{{-- 5. Status Change Confirmation Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('distribution-events.updateStatus', $event) }}" id="statusForm">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="statusMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="statusConfirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Single Beneficiary Modal --}}
@if($event->status !== 'Completed')
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1" aria-labelledby="addBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('allocations.store') }}">
                @csrf
                <input type="hidden" name="form_context" value="add_single">
                <input type="hidden" name="release_method" value="event">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeneficiaryModalLabel">Add Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="beneficiary_id" class="form-label">Beneficiary <span class="text-danger">*</span></label>
                        <select class="form-select @error('beneficiary_id') is-invalid @enderror"
                                id="beneficiary_id" name="beneficiary_id" required>
                            <option value="" disabled selected>Select Beneficiary</option>
                            {{-- Populated via JS --}}
                        </select>
                        @error('beneficiary_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($event->isFinancial())
                        <div class="mb-3">
                            <label for="amount" class="form-label">
                                Amount (PHP) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="1"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   id="amount" name="amount" value="{{ old('amount') }}"
                                   placeholder="e.g. 1000.00" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="quantity" class="form-label">
                                Quantity <span class="text-danger">*</span>
                                <span class="badge bg-secondary ms-1">{{ $event->resourceType->unit }}</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" max="9999.99"
                                   class="form-control @error('quantity') is-invalid @enderror"
                                   id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" class="form-control" id="remarks" name="remarks"
                               value="{{ old('remarks') }}" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add All Barangay Beneficiaries Modal --}}
<div class="modal fade" id="addAllModal" tabindex="-1" aria-labelledby="addAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST"
                action="{{ route('allocations.storeBulk') }}"
                id="bulkForm"
                data-confirm-title="Confirm Bulk Allocation"
                data-confirm-message="Add allocations for all selected beneficiaries in this barangay? This is a bulk transaction.">
                @csrf
                <input type="hidden" name="release_method" value="event">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAllModalLabel">Add All Barangay Beneficiaries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-responsive-cards">
                            <thead class="table-light">
                                <tr>
                                    <th>Beneficiary Name</th>
                                    <th>Classification</th>
                                    @if($event->isFinancial())
                                        <th>Amount (PHP) <span class="text-danger">*</span></th>
                                    @else
                                        <th>Quantity ({{ $event->resourceType->unit }}) <span class="text-danger">*</span></th>
                                    @endif
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="bulkTableBody">
                                {{-- Populated via JS --}}
                            </tbody>
                        </table>
                    </div>
                    <div id="bulkEmpty" class="text-center text-muted py-4 d-none">
                        <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                        All beneficiaries in this barangay are already allocated.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="bulkSubmitBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Allocation CSV Modal --}}
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('allocations.importCsv') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_context" value="import_csv">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="importCsvModalLabel">Import Beneficiary Allocations (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control @error('csv_file') is-invalid @enderror"
                               id="csv_file"
                               name="csv_file"
                               accept=".csv,text/csv"
                               required>
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="small text-muted">
                        Required CSV header:
                        <code>beneficiary_id</code>,
                        <code>{{ $event->isFinancial() ? 'amount' : 'quantity' }}</code>.
                        Optional headers:
                        <code>assistance_purpose_id</code>, <code>remarks</code>.
                    </div>
                    <div class="small text-muted mt-2">
                        Rules: only active beneficiaries from {{ $event->barangay->name }}, no duplicate allocations, and financial rows must fit remaining budget.
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('allocations.importCsvTemplate', ['distribution_event_id' => $event->id]) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Download CSV Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Allocation Modal --}}
<div class="modal fade" id="editAllocationModal" tabindex="-1" aria-labelledby="editAllocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="#" id="editAllocationForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editAllocationModalLabel">Edit Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_allocation_beneficiary" class="form-label">Beneficiary</label>
                        <input type="text" id="edit_allocation_beneficiary" class="form-control" readonly>
                    </div>

                    @if($event->isFinancial())
                        <div class="mb-3">
                            <label for="edit_allocation_amount" class="form-label">Amount (PHP) <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="edit_allocation_amount"
                                   name="amount"
                                   class="form-control"
                                   min="1"
                                   step="0.01"
                                   required>
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="edit_allocation_quantity" class="form-label">Quantity ({{ $event->resourceType->unit }}) <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="edit_allocation_quantity"
                                   name="quantity"
                                   class="form-control"
                                   min="0.01"
                                   max="9999.99"
                                   step="0.01"
                                   required>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="edit_assistance_purpose_id" class="form-label">Assistance Purpose</label>
                        <select class="form-select" id="edit_assistance_purpose_id" name="assistance_purpose_id">
                            <option value="">Select Purpose (Optional)</option>
                            @foreach($assistancePurposes as $purpose)
                                <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_allocation_remarks" class="form-label">Remarks</label>
                        <textarea id="edit_allocation_remarks"
                                  name="remarks"
                                  class="form-control"
                                  maxlength="500"
                                  rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Status change modal ----
    const statusModal = document.getElementById('statusModal');
    if (statusModal) {
        statusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('statusInput').value = button.getAttribute('data-status');
            document.getElementById('statusModalLabel').textContent = button.getAttribute('data-title');
            document.getElementById('statusMessage').textContent = button.getAttribute('data-message');
        });
    }

    // ---- Beneficiary data for modals ----
    const beneficiaries = @json($availableBeneficiaries);

    const isFinancial = @json($event->isFinancial());

    // Populate single-add dropdown
    (function populateSingleSelect() {
        const select = document.getElementById('beneficiary_id');
        if (!select) return;
        beneficiaries.forEach(b => {
            const option = document.createElement('option');
            option.value = b.id;
            option.textContent = `${b.full_name} (${b.classification})`;
            select.appendChild(option);
        });
    })();

    // Populate bulk-add table
    (function populateBulkTable() {
        const tbody = document.getElementById('bulkTableBody');
        const emptyMsg = document.getElementById('bulkEmpty');
        const submitBtn = document.getElementById('bulkSubmitBtn');
        if (!tbody) return;

        if (beneficiaries.length === 0) {
            emptyMsg.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        emptyMsg.classList.add('d-none');

        beneficiaries.forEach((b, i) => {
            const classificationBadge = {
                'Farmer': 'bg-primary',
                'Fisherfolk': 'bg-info text-dark',
                'Both': '',
            }[b.classification] || 'bg-secondary';
            const valueLabel = isFinancial ? 'Amount (PHP)' : 'Quantity';

            const badgeHtml = b.classification === 'Both'
                ? `<span class="badge" style="background-color: #6f42c1;">Both</span>`
                : `<span class="badge ${classificationBadge}">${b.classification}</span>`;

            const valueInput = isFinancial
                ? `<input type="number" step="0.01" min="1"
                          class="form-control form-control-sm"
                          name="allocations[${i}][amount]" placeholder="e.g. 1000.00" required>`
                : `<input type="number" step="0.01" min="0.01" max="9999.99"
                          class="form-control form-control-sm"
                          name="allocations[${i}][quantity]" required>`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td data-label="Beneficiary Name">
                    ${b.full_name}
                    <input type="hidden" name="allocations[${i}][beneficiary_id]" value="${b.id}">
                </td>
                <td data-label="Classification">${badgeHtml}</td>
                <td data-label="${valueLabel}">${valueInput}</td>
                <td data-label="Remarks">
                    <input type="text" class="form-control form-control-sm"
                           name="allocations[${i}][remarks]" maxlength="500">
                </td>
            `;
            tbody.appendChild(row);
        });
    })();

    // Re-open add modal on validation errors
    @if($errors->any() && old('form_context') === 'add_single')
        new bootstrap.Modal(document.getElementById('addBeneficiaryModal')).show();
    @endif

    @if($errors->any() && old('form_context') === 'import_csv')
        new bootstrap.Modal(document.getElementById('importCsvModal')).show();
    @endif

    // Compliance form dependencies (financial events)
    const showLegalBasisType = document.getElementById('legal_basis_type');
    const showLegalRemarksGroup = document.getElementById('showLegalRemarksGroup');
    const showLegalRemarks = document.getElementById('legal_basis_remarks');
    const showFundSource = document.getElementById('fund_source');
    const showTrustAccountInputGroup = document.getElementById('showTrustAccountInputGroup');
    const showTrustAccount = document.getElementById('trust_account_code');
    const showLiquidationStatus = document.getElementById('liquidation_status');
    const showLiqDueGroup = document.getElementById('showLiqDueGroup');
    const showLiqDue = document.getElementById('liquidation_due_date');
    const showLiqSubmittedGroup = document.getElementById('showLiqSubmittedGroup');
    const showLiqSubmitted = document.getElementById('liquidation_submitted_at');
    const showLiqReferenceGroup = document.getElementById('showLiqReferenceGroup');
    const showLiqReference = document.getElementById('liquidation_reference_no');
    const showRequiresFarmc = document.getElementById('requires_farmc_endorsement');
    const showFarmcReferenceGroup = document.getElementById('showFarmcReferenceGroup');
    const showFarmcReference = document.getElementById('farmc_reference_no');
    const showFarmcEndorsedGroup = document.getElementById('showFarmcEndorsedGroup');
    const showFarmcEndorsed = document.getElementById('farmc_endorsed_at');

    function setShowGroupState(groupEl, inputEl, show, required = false) {
        if (!groupEl || !inputEl) return;
        groupEl.classList.toggle('d-none', !show);
        inputEl.disabled = !show;
        inputEl.required = show && required;
    }

    function updateShowComplianceDependencies() {
        if (!showLegalBasisType) return;

        const legalType = showLegalBasisType.value;
        setShowGroupState(showLegalRemarksGroup, showLegalRemarks, true, legalType === 'other');

        const source = showFundSource?.value ?? '';
        setShowGroupState(showTrustAccountInputGroup, showTrustAccount, source === 'lgu_trust_fund', source === 'lgu_trust_fund');

        const liq = showLiquidationStatus?.value ?? 'not_required';
        const dueRequired = ['pending', 'submitted', 'verified'].includes(liq);
        const submittedRequired = ['submitted', 'verified'].includes(liq);
        setShowGroupState(showLiqDueGroup, showLiqDue, dueRequired, dueRequired);
        setShowGroupState(showLiqSubmittedGroup, showLiqSubmitted, submittedRequired, submittedRequired);
        setShowGroupState(showLiqReferenceGroup, showLiqReference, submittedRequired, submittedRequired);

        const farmcRequired = !!showRequiresFarmc?.checked;
        setShowGroupState(showFarmcReferenceGroup, showFarmcReference, farmcRequired, farmcRequired);
        setShowGroupState(showFarmcEndorsedGroup, showFarmcEndorsed, farmcRequired, false);
    }

    showLegalBasisType?.addEventListener('change', updateShowComplianceDependencies);
    showFundSource?.addEventListener('change', updateShowComplianceDependencies);
    showLiquidationStatus?.addEventListener('change', updateShowComplianceDependencies);
    showRequiresFarmc?.addEventListener('change', updateShowComplianceDependencies);
    updateShowComplianceDependencies();

    const bulkSelectAll = document.getElementById('bulkSelectAll');
    const bulkReleaseForm = document.getElementById('bulkReleaseForm');
    const bulkReleaseAction = document.getElementById('bulkReleaseAction');
    const bulkReleaseIds = document.getElementById('bulkReleaseIds');
    const bulkButtons = document.querySelectorAll('[data-bulk-release-action]');
    const bulkCheckboxes = Array.from(document.querySelectorAll('.bulk-allocation-checkbox'));
    const bulkDistributedLabel = @json($event->isFinancial() ? 'claimed' : 'distributed');

    function syncBulkSelectAllState() {
        if (!bulkSelectAll || bulkCheckboxes.length === 0) {
            return;
        }

        const selectedCount = bulkCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        bulkSelectAll.checked = selectedCount > 0 && selectedCount === bulkCheckboxes.length;
        bulkSelectAll.indeterminate = selectedCount > 0 && selectedCount < bulkCheckboxes.length;
    }

    if (bulkSelectAll) {
        bulkSelectAll.addEventListener('change', function () {
            bulkCheckboxes.forEach(function (checkbox) {
                checkbox.checked = bulkSelectAll.checked;
            });
            syncBulkSelectAllState();
        });
    }

    bulkCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', syncBulkSelectAllState);
    });

    bulkButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!bulkReleaseForm || !bulkReleaseAction || !bulkReleaseIds) {
                return;
            }

            const selectedIds = bulkCheckboxes
                .filter(function (checkbox) { return checkbox.checked; })
                .map(function (checkbox) { return checkbox.value; });

            if (selectedIds.length === 0) {
                window.alert('Select at least one allocation first.');
                return;
            }

            const action = button.getAttribute('data-bulk-release-action');
            const isDistributedAction = action === 'distributed';

            bulkReleaseAction.value = action;
            bulkReleaseIds.innerHTML = '';

            selectedIds.forEach(function (id) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'allocation_ids[]';
                hidden.value = id;
                bulkReleaseIds.appendChild(hidden);
            });

            const title = isDistributedAction
                ? 'Confirm Bulk Distribution'
                : 'Confirm Bulk Not Received';

            const message = isDistributedAction
                ? 'Mark ' + selectedIds.length + ' selected allocation(s) as ' + bulkDistributedLabel + '? This will set release outcome to received.'
                : 'Mark ' + selectedIds.length + ' selected allocation(s) as Not Received for this release schedule?';

            const submitBulk = function () {
                bulkReleaseForm.submit();
            };

            if (typeof confirmThenRun === 'function') {
                confirmThenRun(title, message, submitBulk);
                return;
            }

            submitBulk();
        });
    });

    const editAllocationModal = document.getElementById('editAllocationModal');
    if (editAllocationModal) {
        editAllocationModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }

            const form = document.getElementById('editAllocationForm');
            const beneficiaryInput = document.getElementById('edit_allocation_beneficiary');
            const purposeSelect = document.getElementById('edit_assistance_purpose_id');
            const remarksInput = document.getElementById('edit_allocation_remarks');
            const amountInput = document.getElementById('edit_allocation_amount');
            const quantityInput = document.getElementById('edit_allocation_quantity');

            form.setAttribute('action', button.getAttribute('data-update-url') || '#');
            beneficiaryInput.value = button.getAttribute('data-beneficiary-name') || '';

            if (purposeSelect) {
                purposeSelect.value = button.getAttribute('data-assistance-purpose-id') || '';
            }

            if (remarksInput) {
                remarksInput.value = button.getAttribute('data-remarks') || '';
            }

            if (amountInput) {
                amountInput.value = button.getAttribute('data-amount') || '';
            }

            if (quantityInput) {
                quantityInput.value = button.getAttribute('data-quantity') || '';
            }
        });
    }

    syncBulkSelectAllState();
});
</script>
@endpush
