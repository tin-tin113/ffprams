@extends('layouts.app')

@section('title', 'Field Assessment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('field-assessments.index') }}">Field Assessments</a></li>
    <li class="breadcrumb-item active">Assessment #{{ $fieldAssessment->id }}</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('field-assessments.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">Field Assessment #{{ $fieldAssessment->id }}</h1>
                <p class="text-muted mb-0">{{ $fieldAssessment->beneficiary->full_name }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($fieldAssessment->approval_status === 'pending')
                <a href="{{ route('field-assessments.edit', $fieldAssessment) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            @endif
            <a href="{{ route('field-assessments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Back to List
            </a>
        </div>
    </div>

    {{-- CARD 1: Beneficiary Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person me-1"></i> Beneficiary Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fs-5 fw-bold">{{ $fieldAssessment->beneficiary->full_name }}</div>
                    <div class="text-muted">{{ $fieldAssessment->beneficiary->barangay->name ?? '—' }}</div>
                    <div class="mt-1">
                        @php
                            $classBadge = match($fieldAssessment->beneficiary->classification) {
                                'Farmer'     => 'bg-primary',
                                'Fisherfolk' => 'bg-info text-dark',
                                'Both'       => '',
                                default      => 'bg-secondary',
                            };
                        @endphp
                        @if($fieldAssessment->beneficiary->classification === 'Both')
                            <span class="badge" style="background-color: #6f42c1;">{{ $fieldAssessment->beneficiary->classification }}</span>
                        @else
                            <span class="badge {{ $classBadge }}">{{ $fieldAssessment->beneficiary->classification }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">Contact Number</div>
                            <div class="fw-semibold">{{ $fieldAssessment->beneficiary->contact_number ?? '—' }}</div>
                        </div>
                        @if($fieldAssessment->beneficiary->isFarmer() && $fieldAssessment->beneficiary->rsbsa_number)
                            <div class="col-6">
                                <div class="text-muted small">RSBSA Number</div>
                                <div class="fw-semibold">{{ $fieldAssessment->beneficiary->rsbsa_number }}</div>
                            </div>
                        @endif
                        @if($fieldAssessment->beneficiary->isFisherfolk() && $fieldAssessment->beneficiary->fishr_number)
                            <div class="col-6">
                                <div class="text-muted small">FishR Number</div>
                                <div class="fw-semibold">{{ $fieldAssessment->beneficiary->fishr_number }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('beneficiaries.show', $fieldAssessment->beneficiary) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person-lines-fill me-1"></i> View Full Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: Assessment Details --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-clipboard-data me-1"></i> Assessment Details
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="text-muted small">Visit Date</div>
                    <div class="fw-semibold">
                        {{ $fieldAssessment->visit_date->format('M d, Y') }}
                        @if($fieldAssessment->visit_time)
                            at {{ \Carbon\Carbon::parse($fieldAssessment->visit_time)->format('h:i A') }}
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Assessed By</div>
                    <div class="fw-semibold">{{ $fieldAssessment->assessedBy->name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Eligibility Status</div>
                    <div>
                        @php
                            $eligBadge = match($fieldAssessment->eligibility_status) {
                                'eligible'     => 'bg-success',
                                'not_eligible' => 'bg-danger',
                                default        => 'bg-secondary',
                            };
                            $eligLabel = match($fieldAssessment->eligibility_status) {
                                'eligible'     => 'Eligible',
                                'not_eligible' => 'Not Eligible',
                                default        => 'Pending',
                            };
                        @endphp
                        <span class="badge {{ $eligBadge }} fs-6">{{ $eligLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small mb-1">Findings</div>
                <blockquote class="blockquote bg-light rounded p-3 mb-0" style="font-size: 0.95rem;">
                    {{ $fieldAssessment->findings }}
                </blockquote>
            </div>

            @if($fieldAssessment->eligibility_notes)
                <div>
                    <div class="text-muted small mb-1">Eligibility Notes</div>
                    <p class="mb-0">{{ $fieldAssessment->eligibility_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- CARD 3: Recommendation (only if eligible) --}}
    @if($fieldAssessment->eligibility_status === 'eligible')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-hand-thumbs-up me-1"></i> Recommendation
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Recommended Purpose</div>
                        <div class="fw-bold">{{ $fieldAssessment->recommendedPurpose->name ?? '—' }}</div>
                        @if($fieldAssessment->recommendedPurpose)
                            <span class="badge bg-info text-dark mt-1">{{ ucfirst($fieldAssessment->recommendedPurpose->category) }}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Recommended Amount</div>
                        <div class="fs-4 fw-bold text-success">
                            &#8369;{{ number_format($fieldAssessment->recommended_amount, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- CARD 4: Approval --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Approval
        </div>
        <div class="card-body">
            {{-- Current Status Badge --}}
            <div class="mb-3">
                @php
                    $apprBadge = match($fieldAssessment->approval_status) {
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        default    => 'bg-secondary',
                    };
                @endphp
                <span class="badge {{ $apprBadge }} fs-6">{{ ucfirst($fieldAssessment->approval_status) }}</span>
            </div>

            @if($fieldAssessment->approval_status === 'pending' && auth()->user()->isAdmin())
                {{-- Admin Approve / Reject Buttons --}}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                </div>
            @elseif($fieldAssessment->approval_status === 'approved')
                <div class="alert alert-success d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>This assessment has been approved.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Approved By</div>
                        <div class="fw-semibold">{{ $fieldAssessment->approvedBy->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Approved At</div>
                        <div class="fw-semibold">{{ $fieldAssessment->approved_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($fieldAssessment->approval_notes)
                        <div class="col-md-12">
                            <div class="text-muted small">Approval Notes</div>
                            <p class="mb-0">{{ $fieldAssessment->approval_notes }}</p>
                        </div>
                    @endif
                </div>
            @elseif($fieldAssessment->approval_status === 'rejected')
                <div class="alert alert-danger d-flex align-items-center mb-3">
                    <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                    <div>This assessment has been rejected.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Rejected By</div>
                        <div class="fw-semibold">{{ $fieldAssessment->approvedBy->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Rejected At</div>
                        <div class="fw-semibold">{{ $fieldAssessment->approved_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($fieldAssessment->approval_notes)
                        <div class="col-md-12">
                            <div class="text-muted small">Rejection Reason</div>
                            <div class="alert alert-danger mb-0">{{ $fieldAssessment->approval_notes }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- CARD 5: Disbursement History --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-cash-stack me-1"></i> Disbursement History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Amount</th>
                            <th>Purpose</th>
                            <th>Distribution Event</th>
                            <th>Disbursed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fieldAssessment->allocations as $allocation)
                            <tr>
                                <td class="fw-semibold">&#8369;{{ number_format($allocation->amount ?? $allocation->quantity, 2) }}</td>
                                <td>{{ $allocation->assistancePurpose->name ?? '—' }}</td>
                                <td>
                                    @if($allocation->distributionEvent)
                                        {{ $allocation->distributionEvent->resourceType->name ?? '' }}
                                        &mdash; {{ $allocation->distributionEvent->distribution_date?->format('M d, Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $allocation->distributed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No disbursements recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    @if($fieldAssessment->approval_status === 'pending' && auth()->user()->isAdmin())
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('field-assessments.approve', $fieldAssessment) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Approve Financial Assistance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You are approving assistance for <strong>{{ $fieldAssessment->beneficiary->full_name }}</strong>.</p>
                            <p class="text-muted small">An SMS will be sent to notify them.</p>
                            <div class="mb-3">
                                <label for="approve_notes" class="form-label">Approval Notes (optional)</label>
                                <textarea class="form-control" id="approve_notes" name="approval_notes"
                                          rows="3" maxlength="500" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Confirm Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('field-assessments.reject', $fieldAssessment) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Assessment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Please provide a reason for rejecting this assessment.</p>
                            <div class="mb-3">
                                <label for="reject_notes" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reject_notes" name="approval_notes"
                                          rows="3" minlength="5" maxlength="500" required
                                          placeholder="Explain why this assessment is being rejected..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-lg me-1"></i> Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
