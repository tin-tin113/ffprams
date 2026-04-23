@extends('layouts.app')

@push('styles')
<style>
    .program-header {
        background: #fff;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    .stat-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .nav-tabs {
        border-bottom: none;
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f1f5f9;
        border-radius: 0.75rem;
        display: inline-flex;
    }
    .nav-tabs .nav-link {
        border: none;
        border-radius: 0.5rem;
        padding: 0.6rem 1.25rem;
        color: #64748b;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nav-tabs .nav-link:hover {
        background: rgba(255,255,255,0.5);
        color: #0d6efd;
    }
    .nav-tabs .nav-link.active {
        background: #fff;
        color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .tab-content {
        padding-top: 1.5rem;
    }
    .card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .table thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        color: #64748b;
        border-top: none;
    }
    .badge-soft-primary { background: #e0e7ff; color: #4338ca; }
    .badge-soft-success { background: #dcfce7; color: #15803d; }
    .badge-soft-info { background: #e0f2fe; color: #0369a1; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; }
    .badge-soft-danger { background: #fee2e2; color: #b91c1c; }
    .badge-soft-purple { background: #f3e8ff; color: #7e22ce; }
    .form-control, .form-select {
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="program-header">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="bi bi-file-text fs-3"></i>
                </div>
                <div>
                    <h2 class="mb-1 fw-bold text-dark">{{ $programName->name }}</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-soft-primary px-3 py-2 rounded-pill"><i class="bi bi-building me-1"></i> {{ $programName->agency->name }}</span>
                        <span class="badge badge-soft-info px-3 py-2 rounded-pill"><i class="bi bi-tags me-1"></i> {{ $programName->classification }}</span>
                        <span class="badge {{ $programName->is_active ? 'badge-soft-success' : 'badge-soft-danger' }} px-3 py-2 rounded-pill">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ $programName->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.settings.program-names.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        @if($programName->description)
            <div class="mt-4 p-3 bg-light rounded-3 border-start border-primary border-4">
                <p class="text-muted mb-0"><i class="bi bi-info-circle me-2"></i> {{ $programName->description }}</p>
            </div>
        @endif
    </div>

    {{-- Tabs Navigation --}}
    <div class="d-flex justify-content-center justify-content-md-start">
        <ul class="nav nav-pills program-tabs mb-4 p-1 bg-light rounded-pill shadow-sm" id="programTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2" id="insights-tab" data-bs-toggle="tab" data-bs-target="#insights" type="button" role="tab">
                    <i class="bi bi-graph-up-arrow me-2"></i>Insights
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="operations-tab" data-bs-toggle="tab" data-bs-target="#operations" type="button" role="tab">
                    <i class="bi bi-calendar3 me-2"></i>Operations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="distributions-tab" data-bs-toggle="tab" data-bs-target="#distributions" type="button" role="tab">
                    <i class="bi bi-box-seam me-2"></i>Distributions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="reach-tab" data-bs-toggle="tab" data-bs-target="#reach" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Reach
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="legal-tab" data-bs-toggle="tab" data-bs-target="#legal" type="button" role="tab">
                    <i class="bi bi-folder2-open me-2"></i>Legal & Files
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="programTabsContent">
        <!-- Insights Tab -->
        <div class="tab-pane fade show active" id="insights" role="tabpanel">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card shadow-sm border-0 h-100 bg-white p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-soft-primary text-primary p-3 rounded-circle" style="background: rgba(13, 110, 253, 0.1);">
                                <i class="bi bi-calendar-event fs-4"></i>
                            </div>
                            <div>
                                <div class="stat-card-value text-primary fs-3 fw-bold">{{ number_format($totalEvents) }}</div>
                                <div class="stat-card-label fw-bold text-uppercase small text-muted">Total Operations</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card shadow-sm border-0 h-100 bg-white p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-soft-success text-success p-3 rounded-circle" style="background: rgba(25, 135, 84, 0.1);">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                            <div>
                                <div class="stat-card-value text-success fs-3 fw-bold">₱{{ number_format($totalAllocatedAmount, 2) }}</div>
                                <div class="stat-card-label fw-bold text-uppercase small text-muted">Investment Reach</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card shadow-sm border-0 h-100 bg-white p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-card-icon bg-soft-info text-info p-3 rounded-circle" style="background: rgba(13, 202, 240, 0.1);">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="stat-card-value text-info fs-3 fw-bold">{{ number_format($totalBeneficiaries) }}</div>
                                <div class="stat-card-label fw-bold text-uppercase small text-muted">Unique Beneficiaries</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Barangay Reach (Top 10)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div style="height: 350px;">
                                <canvas id="barangayChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Resource Mix</h5>
                        </div>
                        <div class="card-body p-4 d-flex align-items-center justify-content-center">
                            <div style="height: 350px; width: 100%;">
                                <canvas id="resourceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Tab -->
        <div class="tab-pane fade" id="operations" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Barangay</th>
                                    <th>Resource</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Reach</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                <tr>
                                    <td class="ps-4"><span class="fw-semibold text-dark">{{ $event->distribution_date?->format('M d, Y') ?? 'N/A' }}</span></td>
                                    <td>{{ $event->barangay?->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-soft-primary text-primary border-0">{{ $event->resourceType?->name ?? 'N/A' }}</span></td>
                                    <td>
                                        @if($event->status === 'Completed')
                                            <span class="badge bg-soft-success px-3 py-2 rounded-pill">Completed</span>
                                        @elseif($event->status === 'Planned')
                                            <span class="badge bg-soft-warning px-3 py-2 rounded-pill">Planned</span>
                                        @else
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">{{ $event->status ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="fw-bold">{{ $event->allocations_count }}</span>
                                            <small class="text-muted">Allocated</small>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No operations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($events->hasPages())
                <div class="card-footer bg-transparent border-0 px-4 py-3">
                    {{ $events->appends(['tab' => 'operations'])->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Distributions Tab -->
        <div class="tab-pane fade" id="distributions" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 px-4 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0">Distribution Ledger</h5>
                        <ul class="nav nav-pills nav-sm bg-light rounded-pill p-1" id="ledgerSubTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill px-3 py-1 small" id="event-dist-tab" data-bs-toggle="pill" data-bs-target="#event-dist" type="button" role="tab">Event-based</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-3 py-1 small" id="direct-dist-tab" data-bs-toggle="pill" data-bs-target="#direct-dist" type="button" role="tab">Direct Assistance</button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content pt-0">
                        <div class="tab-pane fade show active" id="event-dist" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-4">Beneficiary</th>
                                            <th>Barangay</th>
                                            <th>Resource</th>
                                            <th class="text-end">Value</th>
                                            <th>Method</th>
                                            <th class="pe-4">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allocations as $allocation)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $allocation->beneficiary?->full_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $allocation->created_at?->format('M d, Y') }}</small>
                                            </td>
                                            <td>{{ $allocation->distributionEvent?->barangay?->name ?? $allocation->beneficiary?->barangay?->name ?? 'N/A' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $allocation->resourceType?->name ?? 'N/A' }}</span></td>
                                            <td class="text-end">
                                                @if($allocation->amount) <div class="fw-bold">₱{{ number_format($allocation->amount, 2) }}</div> @endif
                                                @if($allocation->quantity) <small class="text-muted">{{ number_format($allocation->quantity, 1) }} units</small> @endif
                                            </td>
                                            <td>
                                                @if($allocation->release_method === 'cash')
                                                    <span class="badge bg-soft-success px-2 py-1"><i class="bi bi-cash me-1"></i> Cash</span>
                                                @else
                                                    <span class="badge bg-soft-primary px-2 py-1"><i class="bi bi-ticket-detailed me-1"></i> Voucher</span>
                                                @endif
                                            </td>
                                            <td class="pe-4">
                                                <span class="badge {{ ($allocation->release_status_label ?? 'Planned') === 'Released' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill">
                                                    {{ $allocation->release_status_label ?? 'Planned' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No records found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($allocations->hasPages())
                            <div class="px-4 py-3">{{ $allocations->appends(['tab' => 'distributions'])->links() }}</div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="direct-dist" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-4">Beneficiary</th>
                                            <th>Resource</th>
                                            <th class="text-end">Value</th>
                                            <th>Status</th>
                                            <th class="pe-4">Date Distributed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($directAssistanceRecords as $record)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $record->beneficiary?->full_name ?? 'N/A' }}</div>
                                                <small class="text-muted">Recorded: {{ $record->created_at?->format('M d, Y') }}</small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $record->resourceType?->name ?? 'N/A' }}</span></td>
                                            <td class="text-end">
                                                @if($record->amount) <div class="fw-bold">₱{{ number_format($record->amount, 2) }}</div> @endif
                                                @if($record->quantity) <small class="text-muted">{{ number_format($record->quantity, 1) }} units</small> @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ ($record->status_label ?? 'Planned') === 'Released' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} px-3 py-2 rounded-pill">
                                                    {{ $record->status_label ?? 'Planned' }}
                                                </span>
                                            </td>
                                            <td class="pe-4"><small class="text-muted">{{ $record->distributed_at?->format('M d, Y') ?? 'N/A' }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No records found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($directAssistanceRecords->hasPages())
                            <div class="px-4 py-3">{{ $directAssistanceRecords->appends(['tab' => 'distributions'])->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reach Tab -->
        <div class="tab-pane fade" id="reach" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4">Beneficiary Name</th>
                                    <th>Classification</th>
                                    <th class="text-end pe-4">Frequency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beneficiaries as $beneficiary)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark">{{ $beneficiary->full_name ?? 'N/A' }}</span>
                                                <small class="text-muted">{{ $beneficiary->barangay?->name ?? 'No Barangay' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light border text-dark px-3 py-2 rounded-pill">{{ $beneficiary->classification }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $beneficiaryAllocationCounts[$beneficiary->id] ?? 0 }}</span>
                                            <small class="text-muted">Distributions</small>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">No unique beneficiaries recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($beneficiaries->hasPages())
                <div class="card-footer bg-transparent border-0 px-4 py-3">
                    {{ $beneficiaries->appends(['tab' => 'reach'])->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Legal Tab -->
        <div class="tab-pane fade" id="legal" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Program Library</h5>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadRequirementModal">
                        <i class="bi bi-upload me-2"></i>Upload File
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($programName->legalRequirements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4">Document Type</th>
                                    <th>Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programName->legalRequirements as $req)
                                <tr class="border-transparent">
                                    <td class="ps-4">
                                        <span class="badge badge-soft-primary px-3 py-2 rounded-pill">{{ $req->document_type ?: 'Other' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="p-2 bg-light rounded me-2">
                                                <i class="bi bi-file-earmark-text text-primary"></i>
                                            </div>
                                            <div class="text-truncate" style="max-width: 250px;" title="{{ $req->original_name }}">
                                                <span class="d-block fw-medium text-dark">{{ $req->original_name }}</span>
                                                @if($req->remarks)<small class="text-muted d-block">{{ Str::limit($req->remarks, 30) }}</small>@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><small class="text-muted">{{ $req->uploader?->name ?? 'System' }}</small></td>
                                    <td><small class="text-muted">{{ number_format($req->size_bytes / 1024, 1) }} KB</small></td>
                                    <td><small class="text-muted">{{ $req->created_at->format('M d, Y') }}</small></td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.view', [$programName, $req]) }}"
                                               class="btn btn-white btn-sm px-3 border-end" target="_blank" title="View">
                                                <i class="bi bi-eye text-primary"></i>
                                            </a>
                                            <a href="{{ route('admin.settings.program-names.legal-requirements.download', [$programName, $req]) }}"
                                               class="btn btn-white btn-sm px-3 border-end" title="Download">
                                                <i class="bi bi-download text-info"></i>
                                            </a>
                                            <button class="btn btn-white btn-sm px-3 delete-req"
                                                    data-id="{{ $req->id }}"
                                                    data-program-id="{{ $programName->id }}" title="Delete">
                                                <i class="bi bi-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder-x display-4 d-block mb-3 opacity-25"></i>
                        <p class="mb-0">No documents uploaded yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadRequirementModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.settings.program-names.legal-requirements.upload', $programName) }}"
              method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Upload Program File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Document Type</label>
                    <select name="document_type" class="form-select border-0 bg-light" required>
                        <option value="">Select type...</option>
                        <option value="Executive Order">Executive Order</option>
                        <option value="Program Guidelines">Program Guidelines</option>
                        <option value="Legal Basis">Legal Basis</option>
                        <option value="Memorandum">Memorandum</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select File</label>
                    <input type="file" name="requirement_file" class="form-control border-0 bg-light" required>
                    <small class="text-muted">PDF, DOCX, JPG, PNG allowed (max 10MB)</small>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea name="remarks" class="form-control border-0 bg-light" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Start Upload</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Restore active tab based on URL param
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'insights';
    const tabTrigger = document.querySelector(`#${activeTab}-tab`);
    if (tabTrigger) {
        const tab = new bootstrap.Tab(tabTrigger);
        tab.show();
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

    // --- Charts Implementation ---
    const barangayCtx = document.getElementById('barangayChart');
    if (barangayCtx) {
        new Chart(barangayCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($barangayReach->pluck('name')) !!},
                datasets: [{
                    label: 'Beneficiaries',
                    data: {!! json_encode($barangayReach->pluck('total')) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const resourceCtx = document.getElementById('resourceChart');
    if (resourceCtx) {
        new Chart(resourceCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($resourceMix->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($resourceMix->pluck('total')) !!},
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                cutout: '70%'
            }
        });
    }

    // Delete legal requirement
    document.querySelectorAll('.delete-req').forEach(btn => {
        btn.addEventListener('click', function() {
            const reqId = this.dataset.id;
            const programId = this.dataset.programId;

            if (confirm('Delete this legal requirement document? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/settings/program-names/${programId}/legal-requirements/${reqId}`;
                form.innerHTML = `<input type="hidden" name="_token" value="${csrftoken}"><input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
