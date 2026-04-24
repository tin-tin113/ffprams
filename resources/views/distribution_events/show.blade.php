@extends('layouts.app')

@section('title', 'Distribution Event Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item active">Event #{{ $event->id }}</li>
@endsection

@push('styles')
<style>
    /* Premium Dashboard Styles */
    .event-header {
        background: #fff;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    .stat-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
        border: none;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }
    .nav-pills-custom .nav-link {
        color: #475569;
        font-weight: 600;
        padding: 0.9rem 1.8rem;
        border-radius: 0.75rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        background: #fff;
        margin-right: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .nav-pills-custom .nav-link.active {
        background: #2563eb;
        color: #ffffff !important;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    .nav-pills-custom .nav-link:hover:not(.active) {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .badge-soft-success { background-color: #dcfce7; color: #15803d; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-info { background-color: #e0f2fe; color: #075985; }
    .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-primary { background-color: #e0e7ff; color: #3730a3; }
    .badge-soft-purple { background-color: #f3e8ff; color: #6b21a8; }
    
    .card-dashboard {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        margin-bottom: 1.5rem;
    }
    .card-dashboard .card-header {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: #1e293b;
    }
    .card-dashboard .card-body {
        padding: 1.5rem;
    }
    
    .progress-thin { height: 6px; border-radius: 3px; }
    
    /* Ensure form text is dark */
    .form-control, .form-select {
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- High-Level Header --}}
    <div class="event-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-auto">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-calendar-event text-primary fs-3"></i>
                </div>
            </div>
            <div class="col">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 class="h4 fw-bold mb-0">{{ $event->name ?: 'Distribution Event #' . $event->id }}</h2>
                    @php
                        $statusClass = match($event->status) {
                            'Pending'   => 'badge-soft-info',
                            'Ongoing'   => 'badge-soft-warning',
                            'Completed' => 'badge-soft-success',
                            default     => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $event->status }}</span>
                </div>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-1"></i> {{ $event->barangay->name }} 
                    <span class="mx-2 text-silver">|</span>
                    <i class="bi bi-tag me-1"></i> {{ $event->programName->name ?? 'N/A' }}
                    <span class="mx-2 text-silver">|</span>
                    <i class="bi bi-calendar3 me-1"></i> {{ $event->distribution_date->format('M d, Y') }}
                </p>
            </div>
            <div class="col-md-auto">
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle px-4 shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            @if(auth()->user()->role === 'admin' && $event->status === 'Pending' && !$event->isBeneficiaryListApproved())
                                <li>
                                    <form action="{{ route('distribution-events.approveBeneficiaryList', $event) }}" method="POST"
                                          data-confirm-title="Approve Beneficiary List"
                                          data-confirm-message="Are you sure you want to approve the current beneficiary list? This will track any future changes to the list.">
                                        @csrf
                                        <button type="submit" class="dropdown-item {{ $event->allocations->count() === 0 ? 'disabled opacity-50' : '' }}" 
                                                {{ $event->allocations->count() === 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-person-check me-2 text-success"></i> Approve Beneficiary List
                                            @if($event->allocations->count() === 0)
                                                <span class="small d-block text-muted ps-4">(Add participants first)</span>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif

                            @if($event->status === 'Pending')
                                @if($event->isBeneficiaryListApproved())
                                    <li>
                                        <button class="dropdown-item" type="button" 
                                                data-bs-toggle="modal" data-bs-target="#statusModal"
                                                data-status="Ongoing"
                                                data-title="Start Event"
                                                data-message="Ready to start distribution for this event? Status will be set to Ongoing.">
                                            <i class="bi bi-play-fill me-2 text-warning"></i> Start Event
                                        </button>
                                    </li>
                                @else
                                    <li class="dropdown-header text-muted small">
                                        <i class="bi bi-lock me-1"></i> Approve list to start
                                    </li>
                                @endif
                            @endif

                            @if($event->status === 'Ongoing')
                                @if($allBeneficiariesMarked && $completionComplianceReady)
                                    <li>
                                        <button class="dropdown-item" type="button"
                                                data-bs-toggle="modal" data-bs-target="#statusModal"
                                                data-status="Completed"
                                                data-title="Complete Event"
                                                data-message="Are you sure you want to finalize this event? This will lock all allocations.">
                                            <i class="bi bi-check-circle-fill me-2 text-success"></i> Complete Event
                                        </button>
                                    </li>
                                @else
                                    <li class="dropdown-header text-danger small">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Cannot complete yet
                                    </li>
                                @endif
                            @endif
                            @if($event->status !== 'Completed')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('distribution-events.edit', $event) }}">
                                        <i class="bi bi-pencil me-2 text-info"></i> Edit Details
                                    </a>
                                </li>
                                @if(Auth::user()->role === 'admin' && $event->allocations->count() === 0)
                                    <li>
                                        <form action="{{ route('distribution-events.destroy', $event) }}" method="POST"
                                              data-confirm-title="Delete Event" data-confirm-message="Are you sure you want to delete this event? This action cannot be undone.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i> Delete Event
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            @endif

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('distribution-events.distributionList', $event) }}" target="_blank">
                                    <i class="bi bi-printer me-2 text-primary"></i> Print Distribution List
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('distribution-events.distributionListPdf', $event) }}">
                                    <i class="bi bi-file-pdf me-2 text-danger"></i> Download PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('distribution-events.distributionListCsv', $event) }}">
                                    <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i> Export as CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills nav-pills-custom mb-4" id="eventTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview">
                <i class="bi bi-info-circle me-2"></i> Overview
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-beneficiaries">
                <i class="bi bi-people me-2"></i> Beneficiaries
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-documents">
                <i class="bi bi-files me-2"></i> Documents
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-sms">
                <i class="bi bi-chat-left-text me-2"></i> SMS Broadcast
            </button>
        </li>
    </ul>

    <div class="tab-content" id="eventTabsContent">
        {{-- Overview Tab --}}
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            {{-- Status Guidance Alert --}}
            @if($event->status === 'Pending' && !$event->isBeneficiaryListApproved())
                <div class="alert alert-warning border-warning d-flex align-items-start mb-4 shadow-sm">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Status Locked: Beneficiary List Approval Required</h6>
                        <p class="mb-2 small text-dark">This event cannot proceed to the <strong>Ongoing</strong> phase until the beneficiary list has been formally approved. This safeguard ensures the distribution plan is finalized before any releases begin.</p>
                        <div class="d-flex gap-2 mt-2">
                            <a href="#tab-beneficiaries" class="btn btn-sm btn-warning fw-bold px-3" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('button[data-bs-target=\'#tab-beneficiaries\']')).show()">
                                <i class="bi bi-people-fill me-1"></i> Review & Approve List
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($event->status === 'Ongoing' && (!$allBeneficiariesMarked || !$completionComplianceReady))
                <div class="alert alert-info border-info d-flex align-items-start mb-4 shadow-sm">
                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-info-circle-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Requirements for Completion</h6>
                        <p class="mb-2 small text-dark text-opacity-75">The following criteria must be met before this event can be marked as <strong>Completed</strong>:</p>
                        <ul class="mb-0 small text-dark">
                            @if(!$allBeneficiariesMarked)
                                <li class="mb-1"><span class="fw-bold text-danger">{{ $unmarkedBeneficiariesCount }} participants</span> still have pending release outcomes. Every allocation must be marked as 'Released' or 'Not Received'.</li>
                            @endif
                            @if(!$completionComplianceReady)
                                @foreach($completionComplianceIssues as $issue)
                                    <li class="mb-1"><span class="fw-bold">Missing Documentation:</span> {{ $issue }}</li>
                                @endforeach
                            @endif
                        </ul>
                        <p class="mb-0 mt-2 small text-muted"><i class="bi bi-lightbulb me-1"></i> Tip: Use the 'Bulk Mark Released' tool in the Beneficiaries tab for faster updates.</p>
                    </div>
                </div>
            @endif

            {{-- KPI Stats Row --}}
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <div class="card card-dashboard h-100 border-0 border-start border-4 border-primary shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-people text-primary fs-6"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-muted extra-small fw-bold text-uppercase ls-wide" style="font-size: 0.65rem;">Participants</div>
                                    <div class="h5 fw-bold mb-0 text-primary">{{ number_format($totalAllocated) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard h-100 border-0 border-start border-4 border-info shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-box-seam text-info fs-6"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-muted extra-small fw-bold text-uppercase ls-wide" style="font-size: 0.65rem;">Quantity</div>
                                    <div class="h5 fw-bold mb-0 text-info">
                                        @if($event->isFinancial())
                                            &#8369;{{ number_format($totalQuantity, 0) }}
                                        @else
                                            {{ number_format($totalQuantity, 0) }} <span class="fs-6 text-muted">{{ $event->resourceType->unit }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard h-100 border-0 border-start border-4 border-success shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-check2-circle text-success fs-6"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-muted extra-small fw-bold text-uppercase ls-wide" style="font-size: 0.65rem;">Releases</div>
                                    <div class="h5 fw-bold mb-0 text-success">
                                        {{ number_format($totalDistributed) }} <span class="small text-muted fw-normal">/ {{ number_format($totalAllocated) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="progress mt-1" style="height: 3px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalAllocated > 0 ? ($totalDistributed / $totalAllocated) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        $complianceStatusLabels = [
            'provided' => 'Provided',
            'not_available_yet' => 'Not available yet',
            'not_applicable' => 'Not applicable',
            'to_be_verified' => 'To be verified',
        ];
        $complianceStates = $event->complianceStates();
        $agencyName = $event->resourceType->agency->name ?? 'N/A';
        $agencyBadge = match($agencyName) {
            'DA'   => 'bg-success',
            'BFAR' => 'bg-primary',
            'DAR'  => 'bg-warning text-dark',
            'LGU'  => 'bg-secondary',
            default => 'bg-secondary',
        };
    @endphp

    <div class="card card-dashboard">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Event Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="text-muted small">Event Name</div>
                    <div class="fw-semibold fs-5 text-primary">{{ $event->name ?: 'N/A' }}</div>
                    <hr class="my-2">
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Barangay</div>
                    <div class="fw-semibold">{{ $event->barangay->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Resource Type</div>
                    <div class="fw-semibold">{{ $event->resourceType->name }} ({{ $event->resourceType->unit }})</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Program</div>
                    <div class="fw-semibold">{{ $event->programName->name ?? 'N/A' }}</div>
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

    {{-- Compliance & Legal Basis Form --}}
    <div class="card card-dashboard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-shield-check me-1"></i> Compliance & Legal Basis</span>
            @if(in_array(Auth::user()->role, ['admin', 'staff'], true) && $event->status !== 'Completed')
                <button type="submit" form="complianceForm" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i> Update Compliance
                </button>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('distribution-events.updateCompliance', $event) }}" method="POST" id="complianceForm">
                @csrf
                @method('PATCH')
                
                <div class="row g-4">
                    {{-- Legal Basis Section --}}
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-ruled me-1"></i> Authorization & Legal Basis</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="legal_basis_type" class="form-label small fw-bold">Legal Basis Type</label>
                                <select class="form-select" id="legal_basis_type" name="legal_basis_type">
                                    <option value="">-- Select Type --</option>
                                    @foreach($legalBasisLabels as $val => $label)
                                        <option value="{{ $val }}" {{ $event->legal_basis_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="legal_basis_reference_no" class="form-label small fw-bold">Reference Number</label>
                                <input type="text" class="form-control" id="legal_basis_reference_no" name="legal_basis_reference_no" value="{{ $event->legal_basis_reference_no }}" placeholder="e.g. Res. No. 2024-001">
                            </div>
                            <div class="col-md-4">
                                <label for="legal_basis_date" class="form-label small fw-bold">Date of Issuance</label>
                                <input type="date" class="form-control" id="legal_basis_date" name="legal_basis_date" value="{{ $event->legal_basis_date ? $event->legal_basis_date->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-12 d-none" id="showLegalRemarksGroup">
                                <label for="legal_basis_remarks" class="form-label small fw-bold">Legal Basis Remarks</label>
                                <textarea class="form-control" id="legal_basis_remarks" name="legal_basis_remarks" rows="2">{{ $event->legal_basis_remarks }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Fund Source Section --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-bank me-1"></i> Fund Source & Control</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="fund_source" class="form-label small fw-bold">Primary Fund Source</label>
                                <select class="form-select" id="fund_source" name="fund_source">
                                    <option value="">-- Select Source --</option>
                                    @foreach($fundSourceLabels as $val => $label)
                                        <option value="{{ $val }}" {{ $event->fund_source === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-none" id="showTrustAccountInputGroup">
                                <label for="trust_account_code" class="form-label small fw-bold">Trust Account Code</label>
                                <input type="text" class="form-control" id="trust_account_code" name="trust_account_code" value="{{ $event->trust_account_code }}">
                            </div>
                            <div class="col-12">
                                <label for="fund_release_reference" class="form-label small fw-bold">Fund Release Ref (DV/Check No.)</label>
                                <input type="text" class="form-control" id="fund_release_reference" name="fund_release_reference" value="{{ $event->fund_release_reference }}">
                            </div>
                        </div>
                    </div>

                    {{-- Liquidation Section --}}
                    <div class="col-md-6 border-start ps-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clipboard-check me-1"></i> Liquidation Tracking</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="liquidation_status" class="form-label small fw-bold">Liquidation Status</label>
                                <select class="form-select" id="liquidation_status" name="liquidation_status">
                                    <option value="not_required" {{ $event->liquidation_status === 'not_required' ? 'selected' : '' }}>Not Required</option>
                                    <option value="pending" {{ $event->liquidation_status === 'pending' ? 'selected' : '' }}>Pending Submission</option>
                                    <option value="submitted" {{ $event->liquidation_status === 'submitted' ? 'selected' : '' }}>Submitted for Review</option>
                                    <option value="verified" {{ $event->liquidation_status === 'verified' ? 'selected' : '' }}>Verified & Cleared</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="showLiqDueGroup">
                                <label for="liquidation_due_date" class="form-label small fw-bold">Due Date</label>
                                <input type="date" class="form-control" id="liquidation_due_date" name="liquidation_due_date" value="{{ $event->liquidation_due_date ? $event->liquidation_due_date->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-6 d-none" id="showLiqSubmittedGroup">
                                <label for="liquidation_submitted_at" class="form-label small fw-bold">Submitted At</label>
                                <input type="datetime-local" class="form-control" id="liquidation_submitted_at" name="liquidation_submitted_at" value="{{ $event->liquidation_submitted_at ? $event->liquidation_submitted_at->format('Y-m-d\TH:i') : '' }}">
                            </div>
                            <div class="col-12 d-none" id="showLiqReferenceGroup">
                                <label for="liquidation_reference_no" class="form-label small fw-bold">Liquidation Ref No.</label>
                                <input type="text" class="form-control" id="liquidation_reference_no" name="liquidation_reference_no" value="{{ $event->liquidation_reference_no }}">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Endorsements Section --}}
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-check me-1"></i> Agency & Community Endorsements</h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="requires_farmc_endorsement" name="requires_farmc_endorsement" value="1" {{ $event->requires_farmc_endorsement ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="requires_farmc_endorsement">Requires FARMC Endorsement</label>
                                </div>
                            </div>
                            <div class="col-md-4 d-none" id="showFarmcReferenceGroup">
                                <label for="farmc_reference_no" class="form-label small fw-bold">FARMC Ref No.</label>
                                <input type="text" class="form-control" id="farmc_reference_no" name="farmc_reference_no" value="{{ $event->farmc_reference_no }}">
                            </div>
                            <div class="col-md-4 d-none" id="showFarmcEndorsedGroup">
                                <label for="farmc_endorsed_at" class="form-label small fw-bold">Endorsed At</label>
                                <input type="date" class="form-control" id="farmc_endorsed_at" name="farmc_endorsed_at" value="{{ $event->farmc_endorsed_at ? $event->farmc_endorsed_at->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Overall Compliance Section --}}
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-patch-check me-1"></i> Audit Verification & Overall Compliance</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="compliance_overall_status" class="form-label small fw-bold">Overall Compliance Status</label>
                                <select class="form-select" id="compliance_overall_status" name="compliance_overall_status">
                                    @foreach(App\Models\DistributionEvent::complianceStatuses() as $status)
                                        <option value="{{ $status }}" {{ $event->compliance_overall_status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-none" id="compliance_overall_reason_group">
                                <label for="compliance_overall_reason" class="form-label small fw-bold">Non-Compliance / Partial Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="compliance_overall_reason" name="compliance_overall_reason" rows="2" placeholder="Explain why some compliance items are missing or pending...">{{ $event->compliance_overall_reason }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    </div>{{-- End Overview Tab --}}

    {{-- SMS Broadcast Tab --}}
    <div class="tab-pane fade" id="tab-sms" role="tabpanel">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card card-dashboard shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-broadcast text-primary fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Event SMS Broadcast</h5>
                                <p class="text-muted small mb-0">Message all {{ number_format($totalAllocated) }} participants of this event</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="eventSmsForm">
                            <div class="mb-4">
                                <label class="form-label small fw-bold">1. Message Content</label>
                                <textarea class="form-control shadow-none" id="eventSmsMessage" rows="6" placeholder="Type your message to beneficiaries..."></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="small text-muted">
                                        <span id="eventCharCount">0</span> characters | 
                                        <span id="eventSegmentCount">1</span> segment(s)
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">Cost: <span id="eventCostCount">0</span> segments total</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold mb-0">2. Select Recipients</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none x-small" id="eventSmsSelectAllBtn">Select All</button>
                                        <span class="text-muted x-small">|</span>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none x-small" id="eventSmsDeselectAllBtn">Clear</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control border-start-0 shadow-none" id="eventSmsSearch" placeholder="Search by name...">
                                </div>
                                <div class="border rounded-3 overflow-hidden bg-white" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0" id="eventSmsTable">
                                        <thead class="table-light sticky-top" style="z-index: 10;">
                                            <tr>
                                                <th class="ps-3" style="width: 40px;"></th>
                                                <th class="small fw-bold">Beneficiary Name</th>
                                                <th class="small fw-bold">Contact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($event->allocations as $allocation)
                                                <tr class="event-sms-row">
                                                    <td class="ps-3 text-center">
                                                        <input type="checkbox" class="form-check-input event-sms-checkbox" value="{{ $allocation->beneficiary->id }}" {{ $allocation->beneficiary->contact_number ? 'checked' : 'disabled' }}>
                                                    </td>
                                                    <td>
                                                        <div class="small fw-semibold text-truncate" style="max-width: 250px;">{{ $allocation->beneficiary->full_name }}</div>
                                                    </td>
                                                    <td>
                                                        @if($allocation->beneficiary->contact_number)
                                                            <div class="x-small text-muted">{{ $allocation->beneficiary->contact_number }}</div>
                                                        @else
                                                            <span class="badge bg-danger bg-opacity-10 text-danger x-small" style="font-size: 0.65rem;">No Contact</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 text-end">
                                    <small class="text-muted"><span id="eventSelectedCount">0</span> recipients selected</small>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                                    <span class="fw-bold small">Broadcast Summary</span>
                                </div>
                                <ul class="list-unstyled small mb-0 text-muted">
                                    <li class="mb-1"><i class="bi bi-check2 me-1"></i> Total Selected: <strong id="summarySelectedCount">0</strong></li>
                                    <li class="mb-1"><i class="bi bi-check2 me-1"></i> Estimated Segments: <strong id="summarySegmentCount">0</strong></li>
                                    <li><i class="bi bi-check2 me-1"></i> Total Cost: <strong id="summaryTotalCost">0</strong> segments</li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="sendEventSmsBtn" disabled>
                                <i class="bi bi-send-fill me-2"></i> Send Broadcast
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Beneficiaries Tab --}}
    <div class="tab-pane fade" id="tab-beneficiaries" role="tabpanel">
        @php
            $bulkEligibleCount = $event->allocations->filter(function ($allocation) {
                return ! $allocation->distributed_at && $allocation->release_outcome !== 'not_received';
            })->count();
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h5 mb-0 fw-bold">Beneficiary Management</h3>
            @if($event->status !== 'Completed')
                <div class="d-flex gap-2">
                    @if(auth()->user()->role === 'admin' && !$event->isBeneficiaryListApproved() && $event->status === 'Pending')
                        <form action="{{ route('distribution-events.approveBeneficiaryList', $event) }}" method="POST"
                              data-confirm-title="Approve Beneficiary List"
                              data-confirm-message="Are you sure you want to approve the current beneficiary list? This will track any future changes to the list.">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary {{ $event->allocations->count() === 0 ? 'disabled opacity-50' : '' }}"
                                    {{ $event->allocations->count() === 0 ? 'disabled' : '' }}>
                                <i class="bi bi-person-check me-1"></i> Approve List
                            </button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Beneficiary
                    </button>
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addAllModal">
                        <i class="bi bi-people me-1"></i> Add All Barangay Beneficiaries
                    </button>
                </div>
            @endif
        </div>

        @if($event->status !== 'Completed' && $event->isBeneficiaryListApproved())
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>
                    <strong>Beneficiary list already approved:</strong>
                    Adding new beneficiaries now requires a valid reason.
                </div>
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
                            <i class="bi bi-check2-all me-1"></i> Mark Selected as Released
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

        {{-- Table Filter --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="beneficiarySearch" class="form-control border-start-0" placeholder="Search beneficiaries by name, classification, or status...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="allocationsTable">
                        <thead class="table-light">
                            <tr>
                            @if($event->status !== 'Pending' && $bulkEligibleCount > 0)
                                <th class="text-center" style="width: 36px;">
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
                            <th>Released At</th>
                            <th>Remarks</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($event->allocations as $allocation)
                            <tr class="{{ $allocation->distributed_at ? 'table-success' : '' }}">
                                @if($event->status !== 'Pending' && $bulkEligibleCount > 0)
                                    <td class="text-center">
                                        @if(!$allocation->distributed_at && $allocation->release_outcome !== 'not_received')
                                            <input type="checkbox"
                                                   class="form-check-input bulk-allocation-checkbox"
                                                   value="{{ $allocation->id }}"
                                                   aria-label="Select allocation for {{ $allocation->beneficiary->full_name }}">
                                        @endif
                                    </td>
                                @endif
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $allocation->beneficiary->full_name }}</td>
                                <td>
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
                                <td>{{ $allocation->beneficiary->contact_number ?? '—' }}</td>
                                @if($event->isFinancial())
                                    <td>&#8369;{{ number_format($allocation->amount, 2) }}</td>
                                @else
                                    <td>{{ number_format($allocation->quantity, 2) }} {{ $event->resourceType->unit }}</td>
                                @endif
                                <td>
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
                                    @elseif((bool) $allocation->is_ready_for_release)
                                        <span class="text-primary fw-bold">
                                            <i class="bi bi-bell-fill me-1"></i>
                                            Ready for Release
                                        </span>
                                    @else
                                        <span class="text-muted">Not yet released</span>
                                    @endif
                                </td>
                                <td>{{ $allocation->remarks ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @if($event->status !== 'Completed' && in_array(Auth::user()->role, ['admin', 'staff'], true))
                                        @php($isReleased = (bool)$allocation->distributed_at)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary me-1"
                                                @if($isReleased) disabled title="Released items cannot be edited" @else title="Edit allocation" @endif
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
                                              data-confirm-title="Confirm Release"
                                              data-confirm-message="Mark this allocation as Released? This will timestamp the transaction.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                                <i class="bi bi-check2"></i> Mark Released
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#notReceivedModal"
                                                data-action-url="{{ route('allocations.markNotReceived', $allocation) }}"
                                                data-beneficiary-name="{{ $allocation->beneficiary->full_name }}">
                                            <i class="bi bi-x-lg"></i> Not Received
                                        </button>
                                    @endif

                                    @if($event->status !== 'Completed' && Auth::user()->role === 'admin' && !$event->isBeneficiaryListApproved())
                                        <form method="POST"
                                              action="{{ route('allocations.destroy', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Removal"
                                              data-confirm-message="Are you sure you want to remove {{ $allocation->beneficiary->full_name }} from this distribution event?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove beneficiary">
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
    </div>{{-- End Beneficiaries Tab --}}
    </div>

    {{-- Documents Tab --}}
    <div class="tab-pane fade" id="tab-documents" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-dashboard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Event Attachments</span>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadAttachmentModal">
                            <i class="bi bi-upload me-1"></i> Upload Document
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($event->attachments as $attachment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-text text-primary fs-5 me-2"></i>
                                                    <span class="fw-medium text-dark">{{ $attachment->original_name }}</span>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark">{{ strtoupper($attachment->extension) }}</span></td>
                                            <td>{{ $attachment->uploader->name }}</td>
                                            <td class="text-muted small">{{ $attachment->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="{{ route('distribution-events.attachments.view', [$event, $attachment]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('distribution-events.attachments.download', [$event, $attachment]) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    @if(Auth::user()->role === 'admin')
                                                        <form action="{{ route('distribution-events.attachments.destroy', [$event, $attachment]) }}" method="POST" class="d-inline"
                                                              data-confirm-title="Delete Attachment" data-confirm-message="Are you sure you want to delete this document?">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="bi bi-file-earmark-x fs-3 d-block mb-2"></i>
                                                No documents attached to this event yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-dashboard">
                    <div class="card-header">Document Guidance</div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> Ensure all scanned distribution lists and compliance photos are uploaded here for auditing.
                        </div>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Signed Distribution Lists</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Photo Documentation</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Legal Basis Documents</li>
                            <li><i class="bi bi-check2 text-success me-2"></i> Liquidations Reports</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- end tab-content --}}

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
                <input type="hidden" name="release_method" value="event">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeneficiaryModalLabel">Add Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($event->isBeneficiaryListApproved())
                        <div class="alert alert-warning py-2">
                            <strong>Reason required:</strong>
                            This beneficiary list is already approved. Provide a valid reason before adding a new beneficiary.
                        </div>

                        <div class="mb-3">
                            <label for="approval_override_reason_single" class="form-label">
                                Reason for post-approval add <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('approval_override_reason') is-invalid @enderror"
                                      id="approval_override_reason_single"
                                      name="approval_override_reason"
                                      rows="3"
                                      minlength="10"
                                      maxlength="500"
                                      required>{{ old('approval_override_reason') }}</textarea>
                            @error('approval_override_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

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

                    {{-- Today's Allocation Warning (non-blocking) --}}
                    <div id="today_allocation_warning" class="mb-3" style="display: none;">
                        <div class="alert alert-warning py-2 px-3 mb-0 border-warning">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-triangle-fill mt-1 text-warning"></i>
                                <div>
                                    <strong>Heads up:</strong> This beneficiary already has
                                    <span id="today_allocation_count" class="fw-bold">0</span>
                                    allocation(s) recorded in the last 30 days.
                                    <small class="text-muted d-block mt-1">You may still proceed — this is an informational notice only.</small>
                                    <div id="today_allocation_list" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>
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
    <div class="modal-dialog modal-lg">
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
                    @if($event->isBeneficiaryListApproved())
                        <div class="p-3 border-bottom">
                            <div class="alert alert-warning py-2 mb-3 small">
                                <strong>Reason required:</strong>
                                This beneficiary list is already approved. Provide a valid reason before adding beneficiaries in bulk.
                            </div>
                            <label for="approval_override_reason_bulk" class="form-label small fw-bold">
                                Reason for post-approval add <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-sm @error('approval_override_reason') is-invalid @enderror"
                                      id="approval_override_reason_bulk"
                                      name="approval_override_reason"
                                      rows="2"
                                      minlength="10"
                                      maxlength="500"
                                      required>{{ old('approval_override_reason') }}</textarea>
                        </div>
                    @endif

                    <div class="bg-light p-3 border-bottom shadow-sm" style="background-color: #f8fafc !important;">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-primary mb-1">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> Quick Set: {{ $event->isFinancial() ? 'Amount' : 'Quantity' }}
                                </label>
                                <div class="input-group input-group-sm shadow-sm">
                                    <input type="number" id="bulk_apply_value" class="form-control border-primary-subtle" 
                                           step="0.01" placeholder="Set same {{ $event->isFinancial() ? 'amount' : 'quantity' }} for everyone...">
                                    <button class="btn btn-primary px-3" type="button" id="btn_apply_to_all">
                                        Apply
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-7 border-start-md ps-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-info fs-5"></i>
                                    <div class="text-muted small lh-sm">
                                        This will update the {{ $event->isFinancial() ? 'amount' : 'quantity' }} for all <strong>checked</strong> rows in the table below.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bulk_select_all_modal" checked>
                                        </div>
                                    </th>
                                    <th>Beneficiary Name</th>
                                    <th>Classification</th>
                                    @if($event->isFinancial())
                                        <th style="width: 150px;">Amount (PHP) <span class="text-danger">*</span></th>
                                    @else
                                        <th style="width: 150px;">Quantity ({{ $event->resourceType->unit }}) <span class="text-danger">*</span></th>
                                    @endif
                                    <th style="width: 200px;">Remarks</th>
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

{{-- Bulk Release Confirmation Modal --}}
<div class="modal fade" id="bulkReleaseConfirmModal" tabindex="-1" aria-labelledby="bulkReleaseConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="bulkReleaseConfirmModalLabel">Confirm Bulk Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle rounded-circle p-3 mb-2">
                        <i class="bi bi-question-lg text-primary fs-2"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">
                    You are about to mark <span id="bulk_confirm_count" class="fw-bold text-dark fs-5">0</span> allocation(s) as:
                </p>
                <div class="mt-2 mb-3">
                    <span id="bulk_confirm_action_text" class="badge rounded-pill px-3 py-2 fs-6"></span>
                </div>
                <p class="text-muted small mb-0">This action will be timestamped and recorded in the official audit trail.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light px-4 rounded-pill fw-semibold me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 rounded-pill shadow-sm fw-semibold" id="btn_confirm_bulk_release">Confirm Update</button>
            </div>
        </div>
    </div>
</div>
{{-- Not Received Reason Modal --}}
<div class="modal fade" id="notReceivedModal" tabindex="-1" aria-labelledby="notReceivedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="notReceivedForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="notReceivedModalLabel">Reason for Not Received</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Why was <strong id="nr_beneficiary_name"></strong> unable to receive their allocation?</p>
                    
                    <div class="mb-3">
                        <label for="nr_reason" class="form-label fw-bold">Primary Reason <span class="text-danger">*</span></label>
                        <select class="form-select" name="reason" id="nr_reason" required>
                            <option value="No Show">No Show (Did not arrive)</option>
                            <option value="Ineligible">Ineligible (Incorrect documents/ID)</option>
                            <option value="Refused">Refused (Declined the assistance)</option>
                            <option value="Proxy Issue">Proxy Issue (Invalid representative)</option>
                            <option value="Other">Other (Specify in remarks)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nr_remarks" class="form-label fw-bold">Additional Remarks</label>
                        <textarea class="form-control" name="remarks" id="nr_remarks" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                    
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> This record will be moved to the "Not Received" tab.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Not Received</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- 8. Upload Attachment Modal --}}
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" aria-labelledby="uploadAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('distribution-events.attachments.store', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadAttachmentModalLabel">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="attachment" class="form-label">Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="attachment" name="attachment" required>
                        <div class="form-text">Supported formats: PDF, JPG, PNG, DOCX (Max 10MB)</div>
                    </div>
                    <div class="mb-0">
                        <label for="remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="e.g. Scanned distribution list..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- Tab Persistence ----
    const hash = window.location.hash;
    if (hash) {
        const targetTab = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (targetTab) {
            bootstrap.Tab.getOrCreateInstance(targetTab).show();
        }
    }

    // Update hash on tab change
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="pill"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                history.replaceState(null, null, target);
            }
        });
    });

    // ---- Search Logic ----
    const searchInput = document.getElementById('beneficiarySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#allocationsTable tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

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

        // Soft warning check for recent allocations
        const todayWarningEl = document.getElementById('today_allocation_warning');
        const todayCountEl = document.getElementById('today_allocation_count');
        const todayListEl = document.getElementById('today_allocation_list');

        select.addEventListener('change', async function() {
            if (!todayWarningEl) return;
            todayWarningEl.style.display = 'none';

            if (!this.value) return;

            try {
                const response = await fetch(`/api/beneficiaries/${this.value}/recent-allocations`);
                if (!response.ok) return;
                const data = await response.json();

                if (data.success && data.has_recent) {
                    todayCountEl.textContent = data.count;
                    todayListEl.innerHTML = data.allocations.map(a =>
                        `<div class="d-flex align-items-center gap-2 py-1 border-bottom">
                            <span class="badge bg-secondary">${a.type}</span>
                            <span>${a.program} — ${a.resource}</span>
                            <span class="text-muted">(${a.value})</span>
                            <span class="text-muted small ms-auto">${a.date}</span>
                        </div>`
                    ).join('');
                    todayWarningEl.style.display = 'block';
                }
            } catch (error) {
                console.error('Error checking today allocations:', error);
            }
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
                <td>
                    <div class="form-check">
                        <input class="form-check-input bulk-add-checkbox" type="checkbox" name="allocations[${i}][selected]" value="1" id="bulk_check_${i}" checked>
                    </div>
                </td>
                <td><label for="bulk_check_${i}" class="mb-0 fw-medium">${b.full_name}</label></td>
                <td>${badgeHtml}</td>
                <td>${valueInput}</td>
                <td><input type="text" class="form-control form-control-sm" name="allocations[${i}][remarks]" placeholder="Optional remarks..."></td>
                <input type="hidden" name="allocations[${i}][beneficiary_id]" value="${b.id}">
            `;
            tbody.appendChild(row);
        });
    })();

    // Re-open add modal on validation errors
    @if($errors->any() && old('_token') && !old('_method'))
        @if(is_array(old('allocations')))
            new bootstrap.Modal(document.getElementById('addAllModal')).show();
        @else
            new bootstrap.Modal(document.getElementById('addBeneficiaryModal')).show();
        @endif
    @endif

    // Bulk Release Logic
    const bulkSelectAll = document.getElementById('bulkSelectAll');
    const bulkReleaseForm = document.getElementById('bulkReleaseForm');
    const bulkReleaseAction = document.getElementById('bulkReleaseAction');
    const bulkReleaseIds = document.getElementById('bulkReleaseIds');
    const bulkButtons = document.querySelectorAll('[data-bulk-release-action]');
    const bulkCheckboxes = Array.from(document.querySelectorAll('.bulk-allocation-checkbox'));

    function syncBulkSelectAllState() {
        if (!bulkSelectAll || bulkCheckboxes.length === 0) return;
        const selectedCount = bulkCheckboxes.filter(cb => cb.checked).length;
        bulkSelectAll.checked = selectedCount > 0 && selectedCount === bulkCheckboxes.length;
        bulkSelectAll.indeterminate = selectedCount > 0 && selectedCount < bulkCheckboxes.length;
    }

    if (bulkSelectAll) {
        bulkSelectAll.addEventListener('change', function () {
            bulkCheckboxes.forEach(cb => {
                const row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = bulkSelectAll.checked;
                }
            });
            syncBulkSelectAllState();
        });
    }

    bulkCheckboxes.forEach(cb => cb.addEventListener('change', syncBulkSelectAllState));

    bulkButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const selectedIds = bulkCheckboxes.filter(cb => cb.checked).map(cb => cb.value);
            if (selectedIds.length === 0) {
                window.alert('Select at least one allocation first.');
                return;
            }

            const action = btn.getAttribute('data-bulk-release-action');
            const actionLabel = action === 'distributed' ? 'Released' : 'Not Received';
            const actionClass = action === 'distributed' ? 'bg-success' : 'bg-danger';
            
            // Set modal content
            document.getElementById('bulk_confirm_count').textContent = selectedIds.length;
            const actionTextEl = document.getElementById('bulk_confirm_action_text');
            actionTextEl.textContent = actionLabel;
            actionTextEl.className = 'badge rounded-pill px-3 py-2 fs-6 ' + actionClass;

            // Store action and IDs to form but don't submit yet
            bulkReleaseAction.value = action;
            bulkReleaseIds.innerHTML = '';
            selectedIds.forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'allocation_ids[]';
                hidden.value = id;
                bulkReleaseIds.appendChild(hidden);
            });

            // Show Modal
            const confirmModal = new bootstrap.Modal(document.getElementById('bulkReleaseConfirmModal'));
            confirmModal.show();
        });
    });

    // Handle Confirm button inside the modal
    const btnConfirmBulkRelease = document.getElementById('btn_confirm_bulk_release');
    if (btnConfirmBulkRelease) {
        btnConfirmBulkRelease.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
            bulkReleaseForm.submit();
        });
    }

    // Not Received Modal Logic
    const notReceivedModal = document.getElementById('notReceivedModal');
    if (notReceivedModal) {
        notReceivedModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const beneficiaryName = button.getAttribute('data-beneficiary-name');
            const actionUrl = button.getAttribute('data-action-url');

            document.getElementById('nr_beneficiary_name').textContent = beneficiaryName;
            document.getElementById('notReceivedForm').action = actionUrl;
            document.getElementById('nr_reason').value = 'No Show';
            document.getElementById('nr_remarks').value = '';
        });
    }

    // Edit Allocation Modal
    const editAllocationModal = document.getElementById('editAllocationModal');
    if (editAllocationModal) {
        editAllocationModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = document.getElementById('editAllocationForm');
            form.setAttribute('action', button.getAttribute('data-update-url'));
            document.getElementById('edit_allocation_beneficiary').value = button.getAttribute('data-beneficiary-name');
            const purposeSelect = document.getElementById('edit_assistance_purpose_id');
            if (purposeSelect) purposeSelect.value = button.getAttribute('data-assistance-purpose-id');
            document.getElementById('edit_allocation_remarks').value = button.getAttribute('data-remarks');
            const amt = document.getElementById('edit_allocation_amount');
            if (amt) amt.value = button.getAttribute('data-amount');
            const qty = document.getElementById('edit_allocation_quantity');
            if (qty) qty.value = button.getAttribute('data-quantity');
        });
    }

    // ---- Compliance Dependencies ----
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
        if (required) inputEl.required = show;
    }

    function updateShowComplianceDependencies() {
        if (!showLegalBasisType) return;
        const legalType = showLegalBasisType.value;
        setShowGroupState(showLegalRemarksGroup, showLegalRemarks, legalType === 'other', legalType === 'other');
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

    syncBulkSelectAllState();

    // ---- Add All Modal Bulk Apply ----
    const btnApplyToAll = document.getElementById('btn_apply_to_all');
    const bulkApplyValue = document.getElementById('bulk_apply_value');
    if (btnApplyToAll && bulkApplyValue) {
        btnApplyToAll.addEventListener('click', function() {
            const val = bulkApplyValue.value;
            if (!val || val <= 0) {
                alert('Please enter a valid positive value first.');
                return;
            }

            const checkboxes = document.querySelectorAll('.bulk-add-checkbox');
            let appliedCount = 0;
            
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const row = cb.closest('tr');
                    if (row) {
                        const input = row.querySelector('input[type="number"]:not([disabled])');
                        if (input) {
                            input.value = val;
                            appliedCount++;
                        }
                    }
                }
            });

            if (appliedCount > 0) {
                // Flash the inputs to show success
                const inputs = document.querySelectorAll('#bulkTableBody input[type="number"]:not([disabled])');
                inputs.forEach(input => {
                    input.style.backgroundColor = '#d1e7dd';
                    setTimeout(() => input.style.backgroundColor = '', 500);
                });
            } else {
                alert('No selected beneficiaries to apply value to.');
            }
        });
    }

    // ---- Event SMS Broadcast Logic ----
    const eventSmsMessage = document.getElementById('eventSmsMessage');
    const sendEventSmsBtn = document.getElementById('sendEventSmsBtn');
    const eventCharCount = document.getElementById('eventCharCount');
    const eventSegmentCount = document.getElementById('eventSegmentCount');
    const eventSelectedCount = document.getElementById('eventSelectedCount');
    const summarySelectedCount = document.getElementById('summarySelectedCount');
    const summarySegmentCount = document.getElementById('summarySegmentCount');
    const summaryTotalCost = document.getElementById('summaryTotalCost');
    const eventSmsCheckboxes = document.querySelectorAll('.event-sms-checkbox');
    const eventSmsSearch = document.getElementById('eventSmsSearch');

    function updateEventSmsStats() {
        const charCount = eventSmsMessage?.value.length || 0;
        const segments = charCount <= 160 ? 1 : Math.ceil(charCount / 153);
        const selectedCount = Array.from(eventSmsCheckboxes).filter(cb => cb.checked).length;
        
        if (eventCharCount) eventCharCount.textContent = charCount;
        if (eventSegmentCount) eventSegmentCount.textContent = segments;
        if (eventSelectedCount) eventSelectedCount.textContent = selectedCount;
        if (summarySelectedCount) summarySelectedCount.textContent = selectedCount;
        if (summarySegmentCount) summarySegmentCount.textContent = segments;
        if (summaryTotalCost) summaryTotalCost.textContent = segments * selectedCount;
        
        if (sendEventSmsBtn) {
            sendEventSmsBtn.disabled = charCount < 5 || selectedCount === 0;
        }
    }

    if (eventSmsMessage) {
        eventSmsMessage.addEventListener('input', updateEventSmsStats);
    }

    eventSmsCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateEventSmsStats);
    });

    if (eventSmsSearch) {
        eventSmsSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.event-sms-row').forEach(row => {
                const name = row.querySelector('.fw-semibold').textContent.toLowerCase();
                row.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    document.getElementById('eventSmsSelectAllBtn')?.addEventListener('click', () => {
        eventSmsCheckboxes.forEach(cb => { if(!cb.disabled) cb.checked = true; });
        updateEventSmsStats();
    });

    document.getElementById('eventSmsDeselectAllBtn')?.addEventListener('click', () => {
        eventSmsCheckboxes.forEach(cb => cb.checked = false);
        updateEventSmsStats();
    });

    if (sendEventSmsBtn) {
        sendEventSmsBtn.addEventListener('click', async function() {
            const selectedIds = Array.from(eventSmsCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (!confirm(`Are you sure you want to send this broadcast to ${selectedIds.length} selected participants?`)) return;

            const btn = this;
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            try {
                const response = await fetch('{{ route("sms.send") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        recipient_type: 'selected',
                        beneficiary_ids: selectedIds,
                        message: eventSmsMessage.value
                    })
                });

                const data = await response.json();
                if (data.error) throw new Error(data.error);

                alert('Broadcast started successfully!');
                eventSmsMessage.value = '';
                updateEventSmsStats();
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                btn.innerHTML = originalContent;
                btn.disabled = eventSmsMessage.value.length < 5;
            }
        });
    }
    
    updateEventSmsStats();

    // ---- Add All Modal Select All ----
    const bulkSelectAllModal = document.getElementById('bulk_select_all_modal');
    if (bulkSelectAllModal) {
        bulkSelectAllModal.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.bulk-add-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                // Trigger input toggle
                const row = cb.closest('tr');
                if (row) {
                    const inputs = row.querySelectorAll('input:not(.bulk-add-checkbox)');
                    inputs.forEach(input => input.disabled = !this.checked);
                }
            });
        });
    }

    // Monitor individual checkboxes to update master checkbox state and toggle inputs
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('bulk-add-checkbox')) {
            // Toggle inputs in the same row
            const row = e.target.closest('tr');
            if (row) {
                const inputs = row.querySelectorAll('input:not(.bulk-add-checkbox)');
                inputs.forEach(input => {
                    input.disabled = !e.target.checked;
                    if (input.hasAttribute('required')) {
                        // We keep the required attribute but disabled inputs are not validated
                    }
                });
            }

            if (!bulkSelectAllModal) return;
            const checkboxes = Array.from(document.querySelectorAll('.bulk-add-checkbox'));
            const checkedCount = checkboxes.filter(cb => cb.checked).length;
            bulkSelectAllModal.checked = checkedCount === checkboxes.length;
            bulkSelectAllModal.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    });

    // Also handle initial state of master checkbox when modal opens or rows are populated
    // Since rows are added via JS, we should trigger a check
    if (bulkSelectAllModal) {
        const checkMaster = () => {
            const checkboxes = Array.from(document.querySelectorAll('.bulk-add-checkbox'));
            if (checkboxes.length === 0) return;
            const checkedCount = checkboxes.filter(cb => cb.checked).length;
            bulkSelectAllModal.checked = checkedCount === checkboxes.length;
            bulkSelectAllModal.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        };
        
        // Use a small delay or observer if needed, but since it's populated once:
        setTimeout(checkMaster, 500);
    }
});
</script>
@endpush
