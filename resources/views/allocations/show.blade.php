@extends('layouts.app')

@section('title', 'Allocation Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('allocations.index') }}">Assistance Allocations</a></li>
    <li class="breadcrumb-item active">#{{ $allocation->id }}</li>
@endsection

@push('styles')
<style>
    .nav-tabs-custom {
        border-bottom: 2px solid #f1f5f9;
        gap: 2rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        padding: 1rem 0;
        color: #64748b;
        font-weight: 600;
        position: relative;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link.active {
        color: var(--sidebar-bg);
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--sidebar-bg);
        border-radius: 2px;
    }
    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        color: #1e293b;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @php
        $event = $allocation->distributionEvent;
        $isDirect = $allocation->isDirect();
        $releaseStatus = $allocation->release_status;
        $isEventPending = (bool) ($event && $event->status === 'Pending');
        $isFinalOutcome = in_array($releaseStatus, ['released', 'not_received'], true);
        $directWorkflowBlockedByPending = ! $isDirect && $isEventPending;

        $canSetReadyForRelease = $isDirect
            && in_array($releaseStatus, ['planned', 'not_received'], true);

        $canMarkOutcome = $isDirect
            ? ($releaseStatus === 'ready_for_release')
            : (! $isEventPending && ! $isFinalOutcome);

        $statusBadgeClass = match ($releaseStatus) {
            'ready_for_release' => 'badge-soft-primary',
            'released' => 'badge-soft-success',
            'not_received' => 'badge-soft-danger',
            default => 'badge-soft-warning',
        };

        $statusText = match ($releaseStatus) {
            'ready_for_release' => 'Ready for Release',
            'released' => 'Released',
            'not_received' => 'Not Received',
            default => 'Planned',
        };
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 mb-0 fw-bold">Allocation #{{ $allocation->id }}</h1>
                <span class="badge {{ $statusBadgeClass }} px-2 py-1">{{ $statusText }}</span>
            </div>
            <p class="text-muted mb-0">
                <i class="bi bi-tag-fill me-1"></i> {{ $isDirect ? 'Direct Assistance' : 'Event Allocation' }}
                <span class="mx-2 text-slate-300">|</span>
                <i class="bi bi-calendar-check me-1"></i> Recorded {{ $allocation->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($event && ! $isDirect)
                <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary shadow-sm bg-white">
                    <i class="bi bi-calendar-event me-1"></i> View Event
                </a>
            @endif
            <a href="{{ route('allocations.index') }}" class="btn btn-outline-secondary shadow-sm bg-white">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Tabbed Interface -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="allocationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-info-circle me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attachments-tab" data-bs-toggle="tab" data-bs-target="#attachments" type="button" role="tab">
                <i class="bi bi-paperclip me-1"></i> Documents
                @if($allocation->attachments->count() > 0)
                    <span class="badge rounded-pill bg-secondary ms-1">{{ $allocation->attachments->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
        </li>
    </ul>

    <div class="tab-content" id="allocationTabsContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Beneficiary Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Beneficiary Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="detail-label">Full Name</div>
                                    <div class="detail-value fs-5">{{ $allocation->beneficiary->full_name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Classification</div>
                                    <div class="detail-value">{{ $allocation->beneficiary->classification ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Barangay</div>
                                    <div class="detail-value">{{ $allocation->beneficiary->barangay->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Contact Number</div>
                                    <div class="detail-value">{{ $allocation->beneficiary->contact_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Allocation Details -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-success"></i>Assistance Specifications</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="detail-label">Program</div>
                                    <div class="detail-value">{{ $allocation->programName->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Source Agency</div>
                                    <div class="detail-value">{{ $allocation->resourceType->agency->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Resource Type</div>
                                    <div class="detail-value">{{ $allocation->resourceType->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Allocated Value</div>
                                    <div class="detail-value text-primary fs-5">{{ $allocation->getDisplayValue() }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Assistance Purpose</div>
                                    <div class="detail-value">{{ $allocation->assistancePurpose->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Distribution Strategy</div>
                                    <div class="detail-value">
                                        @if($event)
                                            <span class="text-info"><i class="bi bi-calendar-event me-1"></i> Event #{{ $event->id }}</span>
                                        @else
                                            <span class="text-success"><i class="bi bi-lightning-charge me-1"></i> Direct Release</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="detail-label">Administrative Remarks</div>
                                    <div class="detail-value p-3 bg-light rounded-3 fw-normal italic">
                                        {{ $allocation->remarks ?: 'No additional remarks provided for this record.' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Release Actions -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-fill me-2 text-warning"></i>Release Actions</h6>
                        </div>
                        <div class="card-body">
                            @if($canSetReadyForRelease)
                                <form method="POST" action="{{ route('allocations.mark-ready-for-release', $allocation) }}"
                                      data-confirm-title="Set Ready for Release"
                                      data-confirm-message="Set this allocation to Ready for Release? This will enable distribution confirmation."
                                      class="mb-3">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-bell-fill me-2"></i> Ready for Release
                                    </button>
                                </form>
                            @endif

                            @if($canMarkOutcome)
                                <form method="POST" action="{{ route('allocations.markDistributed', $allocation) }}"
                                      data-confirm-title="Confirm Release"
                                      data-confirm-message="Mark this allocation as successfully released to the beneficiary?"
                                      class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-check-circle-fill me-2"></i> Confirm Release
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('allocations.markNotReceived', $allocation) }}"
                                      data-confirm-title="Confirm Not Received"
                                      data-confirm-message="Mark this allocation as Not Received for this schedule?"
                                      class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-bold">
                                        <i class="bi bi-x-circle me-2"></i> Not Received
                                    </button>
                                </form>
                            @endif

                            @if(! $canSetReadyForRelease && ! $canMarkOutcome)
                                <div class="text-center py-3">
                                    <div class="stats-icon bg-light text-muted rounded-circle mx-auto mb-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-shield-lock fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Finalized State</h6>
                                    <p class="text-muted small mb-0">
                                        @if($directWorkflowBlockedByPending)
                                            Updates restricted while event is Pending.
                                        @else
                                            No further administrative actions required.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="opacity-75 small text-uppercase tracking-wider fw-bold">Release Timestamp</div>
                                    <h5 class="mb-0 mt-1 fw-bold">
                                        {{ $allocation->distributed_at ? $allocation->distributed_at->format('M d, Y') : 'Pending' }}
                                    </h5>
                                    @if($allocation->distributed_at)
                                        <div class="small opacity-75">{{ $allocation->distributed_at->format('h:i A') }}</div>
                                    @endif
                                </div>
                                <div class="bg-white bg-opacity-25 rounded p-2">
                                    <i class="bi bi-clock-history fs-4"></i>
                                </div>
                            </div>
                            <div class="pt-3 border-top border-white border-opacity-25">
                                <div class="small opacity-75">Processed By</div>
                                <div class="fw-bold">Administrative Team</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachments Tab -->
        <div class="tab-pane fade" id="attachments" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip me-2 text-primary"></i>Allocation Documents</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('allocations.attachments.store', $allocation) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end mb-4 p-3 bg-light rounded-3" data-submit-spinner>
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Document Type</label>
                            <input type="text" class="form-control shadow-sm" name="document_type" placeholder="e.g. Receipt, ID Photo">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Select File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control shadow-sm" name="attachment" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="bi bi-cloud-upload me-1"></i> Upload
                            </button>
                        </div>
                    </form>

                    @if($allocation->attachments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-uppercase tracking-wider">
                                        <th class="ps-3">Type</th>
                                        <th>File Details</th>
                                        <th>Uploaded By</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allocation->attachments as $attachment)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-dark border">{{ $attachment->document_type ?: 'General' }}</span></td>
                                            <td>
                                                <div class="fw-bold text-truncate" style="max-width: 250px;">{{ $attachment->original_name }}</div>
                                                <div class="text-muted small">{{ number_format($attachment->size_bytes / 1024, 1) }} KB &bull; {{ $attachment->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="small">{{ $attachment->uploader?->name ?? 'System' }}</td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group shadow-sm">
                                                    <a href="{{ route('allocations.attachments.view', [$allocation, $attachment]) }}" class="btn btn-sm btn-white border" target="_blank"><i class="bi bi-eye"></i></a>
                                                    <a href="{{ route('allocations.attachments.download', [$allocation, $attachment]) }}" class="btn btn-sm btn-white border"><i class="bi bi-download"></i></a>
                                                    <form action="{{ route('allocations.attachments.destroy', [$allocation, $attachment]) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-white border text-danger" data-confirm-title="Delete File" data-confirm-message="Permanent delete this document?"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>
                            <p>No documents associated with this allocation.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Audit Trail</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold">Allocation Recorded</h6>
                                <small class="text-muted">{{ $allocation->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <p class="mb-1 small text-muted">Initial assistance record created in the system.</p>
                            <small class="text-primary fw-semibold"><i class="bi bi-person-circle me-1"></i> System Administrator</small>
                        </div>

                        @if($allocation->distributed_at)
                        <div class="list-group-item py-3 bg-light-subtle">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold text-success">Assistance Released</h6>
                                <small class="text-muted">{{ $allocation->distributed_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <p class="mb-1 small text-muted">Beneficiary successfully received the allocated resource.</p>
                            <small class="text-primary fw-semibold"><i class="bi bi-person-circle me-1"></i> Release Officer</small>
                        </div>
                        @endif

                        @if($allocation->release_status === 'not_received')
                        <div class="list-group-item py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold text-danger">Marked as Not Received</h6>
                                <small class="text-muted">{{ $allocation->updated_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <p class="mb-1 small text-muted">Record updated to reflect beneficiary did not receive the items.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
