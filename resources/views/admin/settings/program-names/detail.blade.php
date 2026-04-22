@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-file-text text-primary"></i> {{ $programName->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <small>
                            <span class="badge bg-secondary"><i class="bi bi-building"></i> {{ $programName->agency->name }}</span>
                            <span class="badge bg-info text-dark"><i class="bi bi-tags"></i> {{ $programName->classification }}</span>
                        </small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.settings.program-names.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Programs
                    </a>
                </div>
            </div>
            @if($programName->description)
            <div class="card bg-light border-0">
                <div class="card-body">
                    <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> {{ $programName->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs mb-4 px-2" id="programTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                <i class="bi bi-speedometer2"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                <i class="bi bi-folder2-open"></i> Documents <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark ms-1">{{ $programName->legalRequirements->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
                <i class="bi bi-calendar-event"></i> Events <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark ms-1">{{ $events->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="allocations-tab" data-bs-toggle="tab" data-bs-target="#allocations" type="button" role="tab" aria-controls="allocations" aria-selected="false">
                <i class="bi bi-box-seam"></i> Allocations <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark ms-1">{{ $allocations->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="assistance-tab" data-bs-toggle="tab" data-bs-target="#assistance" type="button" role="tab" aria-controls="assistance" aria-selected="false">
                <i class="bi bi-heart-pulse"></i> Direct Assistance <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark ms-1">{{ $directAssistanceRecords->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="beneficiaries-tab" data-bs-toggle="tab" data-bs-target="#beneficiaries" type="button" role="tab" aria-controls="beneficiaries" aria-selected="false">
                <i class="bi bi-people"></i> Beneficiaries <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark ms-1">{{ $totalBeneficiaries }}</span>
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="programTabsContent">
        
        {{-- Overview TAB --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="text-muted fw-bold text-uppercase mb-0">Total Distribution Events</h6>
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded">
                                    <i class="bi bi-calendar-check fs-4"></i>
                                </div>
                            </div>
                            <div class="display-5 fw-bold text-dark">{{ number_format($totalEvents) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="text-muted fw-bold text-uppercase mb-0">Total Allocated Amount</h6>
                                <div class="bg-success bg-opacity-10 text-success p-2 rounded">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                            </div>
                            <div class="display-5 fw-bold text-dark">₱{{ number_format($totalAllocatedAmount, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="text-muted fw-bold text-uppercase mb-0">Total Beneficiaries</h6>
                                <div class="bg-info bg-opacity-10 text-info p-2 rounded">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                            </div>
                            <div class="display-5 fw-bold text-dark">{{ number_format($totalBeneficiaries) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents TAB --}}
        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-pdf text-primary gap-2"></i> Legal Requirements & Documents
                    </h5>
                    @if(Auth::user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="bi bi-cloud-upload"></i> Upload Document
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($programName->legalRequirements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Size</th>
                                    <th>Uploaded Date</th>
                                    <th>Remarks</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programName->legalRequirements as $req)
                                <tr>
                                    <td>
                                        @if($req->document_type)
                                        <span class="badge bg-light border text-dark">{{ $req->document_type }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-text text-secondary me-2"></i>
                                            <small class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $req->original_name }}">{{ $req->original_name }}</small>
                                        </div>
                                    </td>
                                    <td><small>{{ $req->uploader?->name ?? 'Unknown' }}</small></td>
                                    <td><small>{{ number_format($req->size_bytes / 1024, 1) }} KB</small></td>
                                    <td><small>{{ $req->created_at->format('M d, Y H:i') }}</small></td>
                                    <td><small class="text-muted text-truncate d-inline-block" style="max-width: 150px;" title="{{ $req->remarks }}">{{ $req->remarks ?: '-' }}</small></td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.view', [$programName, $req]) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               target="_blank" title="View document">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.download', [$programName, $req]) }}"
                                               class="btn btn-sm btn-outline-info" title="Download document">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger delete-req"
                                                    data-id="{{ $req->id }}"
                                                    data-program-id="{{ $programName->id }}" title="Delete document">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-folder-x border border-2 border-light rounded-circle p-4 bg-light"></i></div>
                        <h5>No Documents Mapped</h5>
                        <p class="text-muted">There are no legal requirement or supporting documents for this program yet.</p>
                        @if(Auth::user()->isAdmin())
                        <button class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <i class="bi bi-plus-circle"></i> Upload First Document
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Events TAB --}}
        <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event text-primary"></i> Distribution Events
                    </h5>
                </div>
                <div class="card-body">
                    @if($events->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Barangay</th>
                                    <th>Resource Type</th>
                                    <th>Status</th>
                                    <th class="text-end">Allocations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                <tr>
                                    <td><small class="fw-medium">{{ $event->distribution_date?->format('F d, Y') ?? 'N/A' }}</small></td>
                                    <td><small>{{ $event->barangay?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $event->resourceType?->name ?? 'N/A' }}</small></td>
                                    <td>
                                        @if($event->status === 'Completed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
                                        @elseif($event->status === 'Planned')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Planned</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $event->status ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end"><span class="badge bg-secondary rounded-pill">{{ $event->allocations_count }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $events->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-calendar-x border border-2 border-light rounded-circle p-4 bg-light"></i></div>
                        <h5>No Distribution Events Found</h5>
                        <p class="text-muted">No distribution events have been created under this program yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Allocations TAB --}}
        <div class="tab-pane fade" id="allocations" role="tabpanel" aria-labelledby="allocations-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box-seam text-primary"></i> Event Allocations
                    </h5>
                </div>
                <div class="card-body">
                    @if($allocations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Beneficiary</th>
                                    <th>Barangay</th>
                                    <th>Resource Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Amount (₱)</th>
                                    <th>Release Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allocations as $allocation)
                                <tr>
                                    <td><small class="text-muted">{{ $allocation->created_at?->format('M d, Y') ?? 'N/A' }}</small></td>
                                    <td><small class="fw-medium">{{ $allocation->beneficiary?->full_name ?? $allocation->beneficiary?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $allocation->distributionEvent?->barangay?->name ?? $allocation->beneficiary?->barangay?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $allocation->resourceType?->name ?? $allocation->distributionEvent?->resourceType?->name ?? 'N/A' }}</small></td>
                                    <td class="text-end"><small>{{ $allocation->quantity !== null ? number_format((float) $allocation->quantity, 2) : '-' }}</small></td>
                                    <td class="text-end"><small>{{ $allocation->amount !== null ? number_format((float) $allocation->amount, 2) : '-' }}</small></td>
                                    <td>
                                        @if($allocation->release_method === 'cash')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-cash"></i> Cash</span>
                                        @elseif($allocation->release_method === 'voucher')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-ticket-detailed"></i> Voucher</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $allocation->release_method ? ucfirst($allocation->release_method) : 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($allocation->release_status_label ?? 'Planned') === 'Released')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Released</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $allocation->release_status_label ?? 'Planned' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $allocations->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-inbox border border-2 border-light rounded-circle p-4 bg-light"></i></div>
                        <h5>No Allocations Found</h5>
                        <p class="text-muted">There are no individual allocations tied to events for this program.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Direct Assistance TAB --}}
        <div class="tab-pane fade" id="assistance" role="tabpanel" aria-labelledby="assistance-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-heart-pulse text-primary"></i> Direct Assistance Records
                    </h5>
                </div>
                <div class="card-body">
                    @if($directAssistanceRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Recorded</th>
                                    <th>Beneficiary</th>
                                    <th>Resource Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Amount (₱)</th>
                                    <th>Status</th>
                                    <th>Distributed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($directAssistanceRecords as $record)
                                <tr>
                                    <td><small class="text-muted">{{ $record->created_at?->format('M d, Y H:i') ?? 'N/A' }}</small></td>
                                    <td><small class="fw-medium">{{ $record->beneficiary?->full_name ?? $record->beneficiary?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $record->resourceType?->name ?? 'N/A' }}</small></td>
                                    <td class="text-end"><small>{{ $record->quantity !== null ? number_format((float) $record->quantity, 2) : '-' }}</small></td>
                                    <td class="text-end"><small>{{ $record->amount !== null ? number_format((float) $record->amount, 2) : '-' }}</small></td>
                                    <td>
                                        @if(($record->status_label ?? 'Planned') === 'Completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $record->status_label ?? 'Planned' }}</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $record->distributed_at?->format('M d, Y') ?? 'N/A' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $directAssistanceRecords->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-clipboard-x border border-2 border-light rounded-circle p-4 bg-light"></i></div>
                        <h5>No Direct Assistance Records</h5>
                        <p class="text-muted">No direct assistance has been recorded under this program.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Beneficiaries TAB --}}
        <div class="tab-pane fade" id="beneficiaries" role="tabpanel" aria-labelledby="beneficiaries-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people text-primary"></i> Unique Beneficiaries
                    </h5>
                </div>
                <div class="card-body">
                    @if($beneficiaries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Classification</th>
                                    <th class="text-end">Program Allocations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($beneficiaries as $beneficiary)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="fw-medium">{{ $beneficiary->full_name ?? $beneficiary->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if(isset($beneficiary->classification))
                                        <span class="badge bg-light border text-dark">{{ $beneficiary->classification }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary rounded-pill">{{ $beneficiaryAllocationCounts[$beneficiary->id] ?? 0 }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $beneficiaries->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-people border border-2 border-light rounded-circle p-4 bg-light opacity-50"></i></div>
                        <h5>No Beneficiaries Reached</h5>
                        <p class="text-muted">No beneficiaries have received allocations or assistance under this program yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Upload Document Modal --}}
@if(Auth::user()->isAdmin())
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="uploadDocForm" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="uploadDocModalLabel"><i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Supporting Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="docFile" class="form-label fw-semibold">Select File <span class="text-danger">*</span></label>
                        <input type="file" id="docFile" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Max size: 5MB. PDF, JPG, PNG allowed.</small>
                    </div>

                    <div class="mb-3">
                        <label for="docType" class="form-label fw-semibold">Document Type</label>
                        <select id="docType" name="document_type" class="form-select">
                            <option value="">Select type...</option>
                            <option value="Executive Order">Executive Order</option>
                            <option value="DAO">DAO (Department Administrative Order)</option>
                            <option value="Memorandum">Memorandum</option>
                            <option value="Policy">Policy</option>
                            <option value="Contract">Contract / Agreement</option>
                            <option value="Legal Basis">Legal Basis</option>
                            <option value="Other">Other Document</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="docRemarks" class="form-label fw-semibold">Remarks</label>
                        <textarea id="docRemarks" name="remarks" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Restore active tab based on URL param
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const tabTrigger = document.querySelector(`#${activeTab}-tab`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    // Update URL when switching tabs
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const targetId = event.target.id.replace('-tab', '');
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('tab', targetId);
            window.history.replaceState({}, '', currentUrl);
        });
    });
    
    // Auto-select tab if pagination is clicked inside it
    // Note: this logic runs gracefully when page restarts, setting the active tab based on ?tab=xxx in the URL that pagination append.

    // Delete legal requirement
    document.querySelectorAll('.delete-req').forEach(btn => {
        btn.addEventListener('click', function() {
            const reqId = this.dataset.id;
            const programId = this.dataset.programId;

            confirmThenRun(
                'Confirm Deletion',
                'Delete this legal requirement document? This action cannot be undone.',
                function () {
                    fetch(`/admin/settings/program-names/${programId}/legal-requirements/${reqId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrftoken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // Ensure we stay on the documents tab on reload
                            const currentUrl = new URL(window.location.href);
                            currentUrl.searchParams.set('tab', 'documents');
                            window.location.href = currentUrl.href;
                        } else {
                            alert(data.message || 'Failed to delete document');
                        }
                    })
                    .catch(function () {
                        alert('An error occurred');
                    });
                }
            );
        });
    });

    // Upload document
    const uploadForm = document.getElementById('uploadDocForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('uploadSubmitBtn');
            const spinner = submitBtn.querySelector('.spinner-border');
            const icon = submitBtn.querySelector('.bi-upload');
            
            const formData = new FormData(this);
            const programId = '{{ $programName->id }}';

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');

            fetch(`/admin/settings/program-names/${programId}/legal-requirements`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('tab', 'documents');
                    window.location.href = currentUrl.href;
                } else {
                    alert(data.message || 'Failed to upload document');
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    icon.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred during upload');
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                icon.classList.remove('d-none');
            });
        });
    }
});
</script>

@endsection
