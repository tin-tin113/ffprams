@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-file-text"></i> {{ $programName->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <small>
                            <span class="badge bg-secondary">{{ $programName->agency->name }}</span>
                            <span class="badge bg-info">{{ $programName->classification }}</span>
                        </small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.settings.program-names.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            @if($programName->description)
            <p class="text-muted mb-0">{{ $programName->description }}</p>
            @endif
        </div>
    </div>

    {{-- Legal Requirements Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Legal Requirements / Supporting Documents
                    </h5>
                    @if(Auth::user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="bi bi-plus-circle"></i> Upload Document
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($programName->legalRequirements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Size</th>
                                    <th>Uploaded Date</th>
                                    <th>Remarks</th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programName->legalRequirements as $req)
                                <tr>
                                    <td>
                                        @if($req->document_type)
                                        <span class="badge bg-light text-dark">{{ $req->document_type }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($req->original_name, 40) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $req->uploader?->name ?? 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ number_format($req->size_bytes / 1024, 1) }} KB</small>
                                    </td>
                                    <td>
                                        <small>{{ $req->created_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $req->remarks ? Str::limit($req->remarks, 30) : '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.view', [$programName, $req]) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               target="_blank"
                                               title="View document">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.download', [$programName, $req]) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="Download document">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger delete-req"
                                                    data-id="{{ $req->id }}"
                                                    data-program-id="{{ $programName->id }}"
                                                    title="Delete document">
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
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> No legal requirement documents uploaded yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Counters --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-primary">{{ $totalEvents }}</div>
                    <p class="text-muted mb-0">Total Distribution Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-success">₱{{ number_format($totalAllocatedAmount, 2) }}</div>
                    <p class="text-muted mb-0">Total Allocated Amount</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-info">{{ $totalBeneficiaries }}</div>
                    <p class="text-muted mb-0">Total Beneficiaries</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribution Events Accordion --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event"></i> Distribution Events ({{ $events->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($events->count() > 0)
                    <div class="accordion" id="eventsAccordion">
                        @foreach($events as $index => $event)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#event{{ $event->id }}" aria-expanded="false" aria-controls="event{{ $event->id }}">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <span class="badge bg-secondary">{{ $event->distribution_date?->format('Y-m-d') ?? 'N/A' }}</span>
                                        <span><strong>{{ $event->barangay?->name ?? 'N/A' }}</strong></span>
                                        <span class="text-muted">{{ $event->resourceType?->name ?? 'N/A' }}</span>
                                        <span class="ms-auto badge bg-light text-dark">{{ $event->allocations->count() }} allocations</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="event{{ $event->id }}" class="accordion-collapse collapse" data-bs-parent="#eventsAccordion">
                                <div class="accordion-body">
                                    {{-- Event Metadata --}}
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <small class="text-muted">Event Date</small>
                                            <p class="mb-0"><strong>{{ $event->distribution_date?->format('Y-m-d') ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Barangay</small>
                                            <p class="mb-0"><strong>{{ $event->barangay?->name ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Resource Type</small>
                                            <p class="mb-0"><strong>{{ $event->resourceType?->name ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Event Status</small>
                                            <p class="mb-0"><strong>{{ $event->status ?? '-' }}</strong></p>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    {{-- Associated Allocations --}}
                                    @if($event->allocations->count() > 0)
                                    <h6 class="mb-3">
                                        <i class="bi bi-box-seam"></i> Associated Allocations ({{ $event->allocations->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Beneficiary</th>
                                                    <th>Resource Type</th>
                                                    <th class="text-end">Quantity</th>
                                                    <th class="text-end">Amount (₱)</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($event->allocations as $allocation)
                                                <tr>
                                                    <td>
                                                        <small>{{ $allocation->beneficiary?->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $allocation->resourceType?->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        <small>{{ $allocation->quantity }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        <small>{{ number_format($allocation->amount, 2) }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $allocation->created_at?->format('Y-m-d') ?? 'N/A' }}</small>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">No allocations for this event.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No distribution events found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Complete Allocations List for this Program --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> All Allocations Under This Program ({{ $allocations->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($allocations->count() > 0)
                    <div class="table-tools mb-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label for="allocationsSearch" class="small text-muted mb-0">Search</label>
                            <input id="allocationsSearch" type="search" class="form-control form-control-sm" placeholder="Beneficiary, barangay, resource, status..." style="min-width: 260px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="allocationsPageSize" class="small text-muted mb-0">Rows</label>
                            <select id="allocationsPageSize" class="form-select form-select-sm" style="width: 90px;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="allocationsTable" class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Created</th>
                                    <th>Beneficiary</th>
                                    <th>Barangay</th>
                                    <th>Resource Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Amount (₱)</th>
                                    <th>Release Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="allocationsTableBody">
                                @foreach($allocations as $allocation)
                                <tr>
                                    <td><small>{{ $allocation->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</small></td>
                                    <td><small>{{ $allocation->beneficiary?->full_name ?? $allocation->beneficiary?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $allocation->distributionEvent?->barangay?->name ?? $allocation->beneficiary?->barangay?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $allocation->resourceType?->name ?? $allocation->distributionEvent?->resourceType?->name ?? 'N/A' }}</small></td>
                                    <td class="text-end"><small>{{ $allocation->quantity !== null ? number_format((float) $allocation->quantity, 2) : '-' }}</small></td>
                                    <td class="text-end"><small>{{ $allocation->amount !== null ? number_format((float) $allocation->amount, 2) : '-' }}</small></td>
                                    <td><small>{{ $allocation->release_method ? ucfirst($allocation->release_method) : 'N/A' }}</small></td>
                                    <td><small><span class="badge bg-light text-dark">{{ $allocation->release_status_label ?? 'Planned' }}</span></small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-tools mt-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <small id="allocationsInfo" class="text-muted"></small>
                        <div class="d-flex align-items-center gap-1">
                            <button id="allocationsPrev" type="button" class="btn btn-sm btn-outline-secondary">Prev</button>
                            <small id="allocationsPage" class="text-muted px-2"></small>
                            <button id="allocationsNext" type="button" class="btn btn-sm btn-outline-secondary">Next</button>
                        </div>
                    </div>
                    @else
                    <p class="text-muted mb-0">No allocations found for this program.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Complete Direct Assistance List for this Program --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-heart-pulse"></i> All Direct Assistance Under This Program ({{ $directAssistanceRecords->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($directAssistanceRecords->count() > 0)
                    <div class="table-tools mb-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label for="directAssistanceSearch" class="small text-muted mb-0">Search</label>
                            <input id="directAssistanceSearch" type="search" class="form-control form-control-sm" placeholder="Beneficiary, resource, status..." style="min-width: 260px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="directAssistancePageSize" class="small text-muted mb-0">Rows</label>
                            <select id="directAssistancePageSize" class="form-select form-select-sm" style="width: 90px;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="directAssistanceTable" class="table table-sm table-hover mb-0">
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
                            <tbody id="directAssistanceTableBody">
                                @foreach($directAssistanceRecords as $record)
                                <tr>
                                    <td><small>{{ $record->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</small></td>
                                    <td><small>{{ $record->beneficiary?->full_name ?? $record->beneficiary?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $record->resourceType?->name ?? 'N/A' }}</small></td>
                                    <td class="text-end"><small>{{ $record->quantity !== null ? number_format((float) $record->quantity, 2) : '-' }}</small></td>
                                    <td class="text-end"><small>{{ $record->amount !== null ? number_format((float) $record->amount, 2) : '-' }}</small></td>
                                    <td><small><span class="badge bg-light text-dark">{{ $record->status_label ?? 'Planned' }}</span></small></td>
                                    <td><small>{{ $record->distributed_at?->format('Y-m-d H:i') ?? 'N/A' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-tools mt-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <small id="directAssistanceInfo" class="text-muted"></small>
                        <div class="d-flex align-items-center gap-1">
                            <button id="directAssistancePrev" type="button" class="btn btn-sm btn-outline-secondary">Prev</button>
                            <small id="directAssistancePage" class="text-muted px-2"></small>
                            <button id="directAssistanceNext" type="button" class="btn btn-sm btn-outline-secondary">Next</button>
                        </div>
                    </div>
                    @else
                    <p class="text-muted mb-0">No direct assistance records found for this program.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Beneficiaries Table --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people"></i> Beneficiaries ({{ $beneficiaries->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($beneficiaries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Classification</th>
                                    <th class="text-end">Allocations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($beneficiaries as $beneficiary)
                                <tr>
                                    <td>
                                        <small>{{ $beneficiary->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            @if(isset($beneficiary->classification))
                                            <span class="badge bg-light text-dark">{{ $beneficiary->classification }}</span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <small>
                                            {{ $allocations->where('beneficiary_id', $beneficiary->id)->count() }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No beneficiaries found.</p>
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
        <div class="modal-content">
            <form id="uploadDocForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocModalLabel">Upload Supporting Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="docFile" class="form-label fw-semibold">Select File <span class="text-danger">*</span></label>
                        <input type="file" id="docFile" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Max size: 5MB. PDF, JPG, PNG allowed.</small>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="bi bi-cloud-arrow-up"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


<style>
    .display-4 {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .card {
        border-radius: 0.5rem;
    }

    .table-tools .form-control,
    .table-tools .form-select {
        max-width: 300px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const setupTable = ({
        bodyId,
        searchId,
        pageSizeId,
        prevId,
        nextId,
        pageId,
        infoId,
    }) => {
        const tbody = document.getElementById(bodyId);
        const searchInput = document.getElementById(searchId);
        const pageSizeSelect = document.getElementById(pageSizeId);
        const prevBtn = document.getElementById(prevId);
        const nextBtn = document.getElementById(nextId);
        const pageText = document.getElementById(pageId);
        const infoText = document.getElementById(infoId);

        if (!tbody || !searchInput || !pageSizeSelect || !prevBtn || !nextBtn || !pageText || !infoText) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        let currentPage = 1;

        const refresh = () => {
            const query = searchInput.value.trim().toLowerCase();
            const pageSize = parseInt(pageSizeSelect.value, 10) || 25;

            const filteredRows = rows.filter((row) => row.textContent.toLowerCase().includes(query));
            const totalRows = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            rows.forEach((row) => {
                row.style.display = 'none';
            });

            filteredRows.slice(start, end).forEach((row) => {
                row.style.display = '';
            });

            if (totalRows === 0) {
                pageText.textContent = 'No results';
                infoText.textContent = 'Showing 0 results';
            } else {
                pageText.textContent = `Page ${currentPage} of ${totalPages}`;
                infoText.textContent = `Showing ${start + 1}-${Math.min(end, totalRows)} of ${totalRows}`;
            }

            prevBtn.disabled = currentPage <= 1 || totalRows === 0;
            nextBtn.disabled = currentPage >= totalPages || totalRows === 0;
        };

        searchInput.addEventListener('input', () => {
            currentPage = 1;
            refresh();
        });

        pageSizeSelect.addEventListener('change', () => {
            currentPage = 1;
            refresh();
        });

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage -= 1;
                refresh();
            }
        });

        nextBtn.addEventListener('click', () => {
            currentPage += 1;
            refresh();
        });

        refresh();
    };

    setupTable({
        bodyId: 'allocationsTableBody',
        searchId: 'allocationsSearch',
        pageSizeId: 'allocationsPageSize',
        prevId: 'allocationsPrev',
        nextId: 'allocationsNext',
        pageId: 'allocationsPage',
        infoId: 'allocationsInfo',
    });

    setupTable({
        bodyId: 'directAssistanceTableBody',
        searchId: 'directAssistanceSearch',
        pageSizeId: 'directAssistancePageSize',
        prevId: 'directAssistancePrev',
        nextId: 'directAssistanceNext',
        pageId: 'directAssistancePage',
        infoId: 'directAssistanceInfo',
    });

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
                            location.reload();
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
            const icon = submitBtn.querySelector('.bi-cloud-arrow-up');
            
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
                    location.reload();
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
