@extends('layouts.app')

@section('title', 'Direct Assistance Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">{{ $directAssistance->beneficiary->full_name }}</li>
@endsection

@push('styles')
<style>
    /* Premium Dashboard Styles */
    .record-header {
        background: #fff;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    .nav-pills-custom .nav-link {
        color: #64748b;
        font-weight: 500;
        padding: 0.8rem 1.5rem;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        margin-right: 0.5rem;
    }
    .nav-pills-custom .nav-link.active {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #e2e8f0;
    }
    .badge-soft-success { background-color: #dcfce7; color: #15803d; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-info { background-color: #e0f2fe; color: #075985; }
    .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-primary { background-color: #e0e7ff; color: #3730a3; }
    
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
    
    /* Ensure form text is dark */
    .form-control, .form-select {
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="record-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-auto">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-hand-thumbs-up text-primary fs-3"></i>
                </div>
            </div>
            <div class="col">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 class="h4 fw-bold mb-0">Record #{{ $directAssistance->id }}</h2>
                    @php
                        $statusClass = match($directAssistance->normalized_status) {
                            'planned'           => 'badge-soft-info',
                            'ready_for_release' => 'badge-soft-warning',
                            'released'          => 'badge-soft-success',
                            'not_received'      => 'badge-soft-danger',
                            default             => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ ucfirst(str_replace('_', ' ', $directAssistance->status)) }}</span>
                </div>
                <p class="text-muted mb-0">
                    <i class="bi bi-person me-1"></i> {{ $directAssistance->beneficiary->full_name }}
                    <span class="mx-2 text-silver">|</span>
                    <i class="bi bi-tag me-1"></i> {{ $directAssistance->programName->name }}
                    <span class="mx-2 text-silver">|</span>
                    <i class="bi bi-clock me-1"></i> Created {{ $directAssistance->created_at->diffForHumans() }}
                </p>
            </div>
            <div class="col-md-auto">
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle px-4 shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            @php($normalizedStatus = $directAssistance->normalized_status)
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
                    <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills nav-pills-custom mb-4" id="assistanceTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview">
                <i class="bi bi-info-circle me-2"></i> Overview
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-beneficiary">
                <i class="bi bi-person me-2"></i> Beneficiary Profile
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-history">
                <i class="bi bi-clock-history me-2"></i> History
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-documents">
                <i class="bi bi-files me-2"></i> Documents
            </button>
        </li>
    </ul>

    <div class="tab-content" id="assistanceTabsContent">
        {{-- Overview Tab --}}
        <div class="tab-pane fade show active" id="tab-overview">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-dashboard">
                        <div class="card-header">Assistance Details</div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Program</label>
                                    <div class="fw-semibold">{{ $directAssistance->programName->name }}</div>
                                    <div class="small text-muted">{{ $directAssistance->programName->agency->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Resource Type</label>
                                    <div class="fw-semibold">{{ $directAssistance->resourceType->name }}</div>
                                    <div class="small text-muted">Unit: {{ $directAssistance->resourceType->unit }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Amount / Quantity</label>
                                    <div class="h5 fw-bold text-primary mb-0">{{ $directAssistance->getDisplayValue() }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Assistance Purpose</label>
                                    <div class="fw-semibold text-info">{{ $directAssistance->assistancePurpose->name }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small d-block mb-1">Remarks</label>
                                    <div class="p-3 bg-light rounded-3 text-muted">{{ $directAssistance->remarks ?: 'No remarks provided.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-dashboard">
                        <div class="card-header">Tracking Information</div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="text-muted small d-block mb-1">Current Status</label>
                                    <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $directAssistance->status)) }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small d-block mb-1">Released At</label>
                                    <div class="fw-semibold">{{ $directAssistance->distributed_at ? $directAssistance->distributed_at->format('M d, Y h:i A') : '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small d-block mb-1">Released By</label>
                                    <div class="fw-semibold">{{ $directAssistance->distributedBy->name ?? '—' }}</div>
                                </div>
                                @if($directAssistance->distributionEvent)
                                    <div class="col-12">
                                        <hr class="my-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <label class="text-muted small d-block mb-1">Linked Distribution Event</label>
                                                <div class="fw-semibold">{{ $directAssistance->distributionEvent->name ?: 'Event #' . $directAssistance->distributionEvent->id }}</div>
                                                <div class="small text-muted">{{ $directAssistance->distributionEvent->distribution_date->format('M d, Y') }}</div>
                                            </div>
                                            <a href="{{ route('distribution-events.show', $directAssistance->distributionEvent) }}" class="btn btn-sm btn-outline-primary">
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
                    <div class="card card-dashboard h-100">
                        <div class="card-header">Record Timeline</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Created By</span>
                                    <span class="fw-semibold text-end">{{ $directAssistance->createdBy->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Created At</span>
                                    <span class="fw-semibold text-end">{{ $directAssistance->created_at->format('M d, Y h:i A') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">Last Updated</span>
                                    <span class="fw-semibold text-end">{{ $directAssistance->updated_at->format('M d, Y h:i A') }}</span>
                                </li>
                            </ul>
                            <div class="p-4 text-center">
                                <div class="d-grid">
                                    <button class="btn btn-sm btn-outline-info" onclick="window.print()">
                                        <i class="bi bi-printer me-1"></i> Print Acknowledgment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Beneficiary Tab --}}
        <div class="tab-pane fade" id="tab-beneficiary">
            <div class="card card-dashboard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Beneficiary Profile</span>
                    <a href="{{ route('beneficiaries.show', $directAssistance->beneficiary) }}" class="btn btn-sm btn-link text-primary p-0">
                        View Full Profile <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-muted small d-block mb-1">Full Name</label>
                            <div class="fw-bold fs-5 text-dark">{{ $directAssistance->beneficiary->full_name }}</div>
                            <div class="small text-muted">{{ $directAssistance->beneficiary->gender }} | {{ $directAssistance->beneficiary->age ?: 'Age N/A' }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <label class="text-muted small d-block mb-1">Classification</label>
                            <span class="badge badge-soft-primary fs-6 px-3 py-2">{{ $directAssistance->beneficiary->classification }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">Address / Barangay</label>
                            <div class="fw-semibold text-dark">{{ $directAssistance->beneficiary->barangay->name }}</div>
                            <div class="small text-muted">FFPRAMS Area 1</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">Contact Number</label>
                            <div class="fw-semibold text-dark">{{ $directAssistance->beneficiary->contact_number ?: 'Not provided' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block mb-1">Primary Agency</label>
                            <div class="fw-semibold text-dark">{{ $directAssistance->beneficiary->agency->name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- History Tab --}}
        <div class="tab-pane fade" id="tab-history">
            <div class="card card-dashboard">
                <div class="card-header">Beneficiary Assistance History</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Program / Type</th>
                                    <th>Resource</th>
                                    <th>Amount/Qty</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Direct Assistance Records --}}
                                @foreach($directAssistance->beneficiary->directAssistanceRecords as $history)
                                    <tr class="{{ $history->id === $directAssistance->id ? 'table-info' : '' }}">
                                        <td>
                                            <div class="fw-semibold">{{ $history->programName->name }}</div>
                                            <span class="badge badge-soft-primary small">Direct Assistance</span>
                                        </td>
                                        <td>{{ $history->resourceType->name }}</td>
                                        <td>{{ $history->getDisplayValue() }}</td>
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
                                        <td>{{ $history->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                                
                                {{-- Event Allocations --}}
                                @foreach($directAssistance->beneficiary->allocations as $alloc)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $alloc->distributionEvent->programName->name }}</div>
                                            <span class="badge badge-soft-purple small">Event Distribution</span>
                                        </td>
                                        <td>{{ $alloc->distributionEvent->resourceType->name }}</td>
                                        <td>{{ $alloc->distributionEvent->isFinancial() ? '₱' . number_format($alloc->amount, 2) : number_format($alloc->quantity, 1) }}</td>
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
                                        <td>{{ $alloc->distributionEvent->distribution_date->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents Tab --}}
        <div class="tab-pane fade" id="tab-documents">
            <div class="card card-dashboard">
                <div class="card-header">Record Attachments</div>
                <div class="card-body">
                    <form action="{{ route('direct-assistance.attachments.store', $directAssistance) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="row g-3 align-items-end mb-4 p-3 bg-light rounded-3"
                          data-submit-spinner>
                        @csrf
                        <div class="col-md-4">
                            <label for="direct_assistance_document_type" class="form-label small fw-bold">Document Type</label>
                            <input type="text" class="form-control form-control-sm" id="direct_assistance_document_type" name="document_type" placeholder="e.g. Receipt">
                        </div>
                        <div class="col-md-5">
                            <label for="direct_assistance_attachment" class="form-label small fw-bold">Attachment File</label>
                            <input type="file" class="form-control form-control-sm" id="direct_assistance_attachment" name="attachment" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100 py-2">
                                <i class="bi bi-upload me-1"></i> Upload File
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($directAssistance->attachments as $attachment)
                                    <tr>
                                        <td><span class="badge badge-soft-info">{{ $attachment->document_type ?: 'General' }}</span></td>
                                        <td>
                                            <div class="fw-semibold text-break">{{ $attachment->original_name }}</div>
                                            <div class="small text-muted">{{ $attachment->uploader->name }} | {{ $attachment->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td>{{ number_format($attachment->size_bytes / 1024, 1) }} KB</td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('direct-assistance.attachments.view', [$directAssistance, $attachment]) }}" class="btn btn-outline-secondary" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('direct-assistance.attachments.download', [$directAssistance, $attachment]) }}" class="btn btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @if(auth()->user()->role === 'admin')
                                                    <form action="{{ route('direct-assistance.attachments.destroy', [$directAssistance, $attachment]) }}" method="POST" class="d-inline"
                                                          data-confirm-title="Delete Document" data-confirm-message="Delete this document?">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-file-earmark-text fs-1 d-block mb-3 opacity-25"></i>
                                            No attachments yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
