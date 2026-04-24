@extends('layouts.app')

@section('title', 'Direct Assistance Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">#{{ $directAssistance->id }}</li>
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
        $normalizedStatus = $directAssistance->normalized_status;
        $statusBadgeClass = match($normalizedStatus) {
            'planned'           => 'badge-soft-info',
            'ready_for_release' => 'badge-soft-warning',
            'released'          => 'badge-soft-success',
            'not_received'      => 'badge-soft-danger',
            default             => 'badge-soft-secondary',
        };
        $statusText = ucfirst(str_replace('_', ' ', $directAssistance->status));
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 mb-0 fw-bold">Record #{{ $directAssistance->id }}</h1>
                <span class="badge {{ $statusBadgeClass }} px-2 py-1">{{ $statusText }}</span>
            </div>
            <p class="text-muted mb-0">
                <i class="bi bi-person-fill me-1"></i> {{ $directAssistance->beneficiary->full_name }}
                <span class="mx-2 text-slate-300">|</span>
                <i class="bi bi-calendar-check me-1"></i> Created {{ $directAssistance->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn btn-primary shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-lightning-charge me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    @if(in_array($normalizedStatus, ['planned', 'not_received'], true))
                        <li>
                            <form action="{{ route('direct-assistance.mark-ready-for-release', $directAssistance) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-bell me-2 text-primary"></i> Ready for Release
                                </button>
                            </form>
                        </li>
                    @endif
                    @if($normalizedStatus === 'ready_for_release')
                        <li>
                            <form action="{{ route('direct-assistance.mark-released', $directAssistance) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-check2-circle me-2 text-success"></i> Mark Released
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('direct-assistance.mark-not-received', $directAssistance) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-x-circle me-2 text-danger"></i> Not Received
                                </button>
                            </form>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('direct-assistance.edit', $directAssistance) }}">
                            <i class="bi bi-pencil me-2 text-info"></i> Edit Details
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li>
                            <form action="{{ route('direct-assistance.destroy', $directAssistance) }}" method="POST"
                                  data-confirm-title="Delete Record" data-confirm-message="Permanent delete this assistance record?">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i> Delete Record
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
            <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary shadow-sm bg-white">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Tabbed Interface -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="assistanceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-info-circle me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="beneficiary-tab" data-bs-toggle="tab" data-bs-target="#beneficiary" type="button" role="tab">
                <i class="bi bi-person me-1"></i> Beneficiary Profile
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                <i class="bi bi-paperclip me-1"></i> Documents
                @if($directAssistance->attachments->count() > 0)
                    <span class="badge rounded-pill bg-secondary ms-1">{{ $directAssistance->attachments->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
        </li>
    </ul>

    <div class="tab-content" id="assistanceTabsContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Assistance Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Assistance Specifications</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="detail-label">Program</div>
                                    <div class="detail-value fs-6">{{ $directAssistance->programName->name }}</div>
                                    <div class="small text-muted">{{ $directAssistance->programName->agency->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Resource Type</div>
                                    <div class="detail-value">{{ $directAssistance->resourceType->name }}</div>
                                    <div class="small text-muted">Unit: {{ $directAssistance->resourceType->unit }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Allocated Value</div>
                                    <div class="detail-value text-primary fs-5">{{ $directAssistance->getDisplayValue() }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Assistance Purpose</div>
                                    <div class="detail-value text-info">{{ $directAssistance->assistancePurpose->name }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="detail-label">Administrative Remarks</div>
                                    <div class="detail-value p-3 bg-light rounded-3 fw-normal italic">
                                        {{ $directAssistance->remarks ?: 'No additional remarks provided for this record.' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Info -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2 text-success"></i>Tracking & Logistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="detail-label">Current Status</div>
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusText }}</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-label">Released At</div>
                                    <div class="detail-value">{{ $directAssistance->distributed_at ? $directAssistance->distributed_at->format('M d, Y h:i A') : 'Pending Release' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-label">Released By</div>
                                    <div class="detail-value">{{ $directAssistance->distributedBy->name ?? '—' }}</div>
                                </div>
                                @if($directAssistance->distributionEvent)
                                    <div class="col-12">
                                        <hr class="my-1">
                                        <div class="d-flex align-items-center justify-content-between p-3 bg-light-subtle rounded-3 mt-3">
                                            <div>
                                                <div class="detail-label">Linked Distribution Event</div>
                                                <div class="fw-bold">{{ $directAssistance->distributionEvent->name ?: 'Event #' . $directAssistance->distributionEvent->id }}</div>
                                                <div class="small text-muted">{{ $directAssistance->distributionEvent->distribution_date->format('M d, Y') }}</div>
                                            </div>
                                            <a href="{{ route('distribution-events.show', $directAssistance->distributionEvent) }}" class="btn btn-sm btn-primary shadow-sm">
                                                View Event <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Summary Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-warning"></i>Record Info</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Created By</span>
                                    <span class="fw-semibold">{{ $directAssistance->createdBy->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Recorded On</span>
                                    <span class="fw-semibold">{{ $directAssistance->created_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Last Modified</span>
                                    <span class="fw-semibold">{{ $directAssistance->updated_at->format('M d, Y') }}</span>
                                </li>
                            </ul>
                            <div class="p-3">
                                <button class="btn btn-outline-secondary w-100 btn-sm shadow-sm" onclick="window.print()">
                                    <i class="bi bi-printer me-1"></i> Print Acknowledgment
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Status Summary Card -->
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="opacity-75 small text-uppercase tracking-wider fw-bold">Current State</div>
                                    <h4 class="mb-0 mt-1 fw-bold">{{ $statusText }}</h4>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded p-2">
                                    <i class="bi bi-shield-check fs-4"></i>
                                </div>
                            </div>
                            <div class="pt-3 border-top border-white border-opacity-25">
                                <p class="small opacity-75 mb-0">Record verified for direct assistance distribution.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beneficiary Profile Tab -->
        <div class="tab-pane fade" id="beneficiary" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Beneficiary Details</h6>
                    <a href="{{ route('beneficiaries.show', $directAssistance->beneficiary) }}" class="btn btn-sm btn-link text-primary p-0">
                        Full Profile <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value fs-4">{{ $directAssistance->beneficiary->full_name }}</div>
                            <div class="text-muted">{{ $directAssistance->beneficiary->gender }} &bull; {{ $directAssistance->beneficiary->age ?: 'Age N/A' }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="detail-label">Classification</div>
                            <span class="badge badge-soft-primary fs-6 px-3 py-2">{{ $directAssistance->beneficiary->classification }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Barangay</div>
                            <div class="detail-value">{{ $directAssistance->beneficiary->barangay->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Contact Number</div>
                            <div class="detail-value">{{ $directAssistance->beneficiary->contact_number ?: 'Not provided' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Primary Agency</div>
                            <div class="detail-value">{{ $directAssistance->beneficiary->agency->name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip me-2 text-primary"></i>Record Documents</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('direct-assistance.attachments.store', $directAssistance) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end mb-4 p-3 bg-light rounded-3" data-submit-spinner>
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Document Type</label>
                            <input type="text" class="form-control shadow-sm" name="document_type" placeholder="e.g. Receipt, Photo">
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

                    @if($directAssistance->attachments->isNotEmpty())
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
                                    @foreach($directAssistance->attachments as $attachment)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-dark border">{{ $attachment->document_type ?: 'General' }}</span></td>
                                            <td>
                                                <div class="fw-bold text-truncate" style="max-width: 250px;">{{ $attachment->original_name }}</div>
                                                <div class="text-muted small">{{ number_format($attachment->size_bytes / 1024, 1) }} KB &bull; {{ $attachment->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="small">{{ $attachment->uploader->name ?? 'System' }}</td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group shadow-sm">
                                                    <a href="{{ route('direct-assistance.attachments.view', [$directAssistance, $attachment]) }}" class="btn btn-sm btn-white border" target="_blank"><i class="bi bi-eye"></i></a>
                                                    <a href="{{ route('direct-assistance.attachments.download', [$directAssistance, $attachment]) }}" class="btn btn-sm btn-white border"><i class="bi bi-download"></i></a>
                                                    @if(auth()->user()->role === 'admin')
                                                        <form action="{{ route('direct-assistance.attachments.destroy', [$directAssistance, $attachment]) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-white border text-danger" data-confirm-title="Delete File" data-confirm-message="Permanent delete this document?"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    @endif
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
                            <p>No documents associated with this record.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Assistance History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-uppercase tracking-wider">
                                    <th class="ps-3">Program / Strategy</th>
                                    <th>Resource</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Direct Assistance Records --}}
                                @foreach($directAssistance->beneficiary->directAssistanceRecords as $history)
                                    <tr class="{{ $history->id === $directAssistance->id ? 'bg-light-subtle' : '' }}">
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $history->programName->name }}</div>
                                            <span class="badge badge-soft-primary small">Direct Assistance</span>
                                        </td>
                                        <td>{{ $history->resourceType->name }}</td>
                                        <td class="fw-bold text-primary">{{ $history->getDisplayValue() }}</td>
                                        <td>
                                            @php
                                                $hStatusClass = match($history->normalized_status) {
                                                    'released' => 'badge-soft-success',
                                                    'not_received' => 'badge-soft-danger',
                                                    default => 'badge-soft-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $hStatusClass }}">{{ ucfirst($history->status) }}</span>
                                        </td>
                                        <td class="text-end pe-3 text-muted small">{{ $history->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                                
                                {{-- Event Allocations --}}
                                @foreach($directAssistance->beneficiary->allocations as $alloc)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $alloc->distributionEvent->programName->name }}</div>
                                            <span class="badge badge-soft-purple small">Event Distribution</span>
                                        </td>
                                        <td>{{ $alloc->distributionEvent->resourceType->name }}</td>
                                        <td class="fw-bold text-primary">{{ $alloc->distributionEvent->isFinancial() ? '₱' . number_format($alloc->amount, 2) : number_format($alloc->quantity, 1) }}</td>
                                        <td>
                                            @php
                                                $aStatusClass = match($alloc->release_outcome) {
                                                    'received' => 'badge-soft-success',
                                                    'not_received' => 'badge-soft-danger',
                                                    default => 'badge-soft-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $aStatusClass }}">{{ $alloc->release_outcome ? ucfirst(str_replace('_', ' ', $alloc->release_outcome)) : 'Pending' }}</span>
                                        </td>
                                        <td class="text-end pe-3 text-muted small">{{ $alloc->distributionEvent->distribution_date->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
