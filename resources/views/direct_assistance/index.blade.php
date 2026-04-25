@extends('layouts.app')

@section('title', 'Direct Assistance Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Direct Assistance</li>
@endsection

@push('styles')
<style>
    .stats-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }
    .stats-card:hover .stats-icon {
        transform: scale(1.1) rotate(5deg);
    }
    .tracking-wider {
        letter-spacing: 0.05em;
    }
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.02) !important;
    }
    .badge-soft-primary { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .badge-soft-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-soft-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-soft-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Direct Assistance Management</h1>
            <p class="text-muted mb-0">Manage direct assistance records to beneficiaries</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#batchModeModal">
                <i class="bi bi-layers me-1"></i> Batch Mode
            </button>
            <a href="{{ route('direct-assistance.create') }}" class="btn btn-success btn-primary-action">
                <i class="bi bi-plus-circle"></i>
                <span>Add Direct Assistance</span>
            </a>
        </div>
    </div>

    <!-- Summary Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card status-planned">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-warning-subtle text-warning rounded-circle p-2 me-2">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Planned</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['planned']) }}</h2>
                        <span class="text-warning small"><i class="bi bi-arrow-up-right"></i> Staged</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card status-ready">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-primary-subtle text-primary rounded-circle p-2 me-2">
                            <i class="bi bi-bell fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Ready for Release</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['ready_for_release']) }}</h2>
                        <span class="text-primary small"><i class="bi bi-broadcast"></i> Active</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card status-released">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-success-subtle text-success rounded-circle p-2 me-2">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Released Today</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['released_today']) }}</h2>
                        <span class="text-success small"><i class="bi bi-graph-up"></i> Performance</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card status-monthly border-start border-4 border-info">
                <div class="card-body p-3">
                    <a href="{{ route('direct-assistance.barangay-analytics') }}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-info-subtle text-info rounded-circle p-2 me-2">
                                <i class="bi bi-bar-chart fs-4"></i>
                            </div>
                            <span class="text-muted small fw-medium text-uppercase tracking-wider">Total This Month</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['this_month']) }}</h2>
                            <span class="text-info small fw-bold">View Analytics <i class="bi bi-chevron-right ms-1"></i></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4 modern-filter-card overflow-hidden">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary-subtle text-primary rounded p-2 me-2">
                    <i class="bi bi-funnel-fill fs-5"></i>
                </div>
                <h5 class="mb-0 fw-bold">Filter Records</h5>
            </div>
        </div>
        <div class="card-body bg-light-subtle">
            <form method="GET" action="{{ route('direct-assistance.index') }}" class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase">Program Name</label>
                    <select class="form-select border-0 shadow-sm ajax-filter" name="program_id">
                        <option value="">All Active Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase">Assistance Status</label>
                    <select class="form-select border-0 shadow-sm ajax-filter" name="status">
                        <option value="">Any Status</option>
                        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="ready_for_release" {{ request('status') == 'ready_for_release' ? 'selected' : '' }}>Ready for Release</option>
                        <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                        <option value="not_received" {{ request('status') == 'not_received' ? 'selected' : '' }}>Not Received</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase">Sorting</label>
                    <select class="form-select border-0 shadow-sm ajax-filter" name="sort">
                        <option value="created_desc" {{ request('sort', 'created_desc') === 'created_desc' ? 'selected' : '' }}>Date: Newest</option>
                        <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>Date: Oldest</option>
                        <option value="program_asc" {{ request('sort') === 'program_asc' ? 'selected' : '' }}>Program: A-Z</option>
                        <option value="program_desc" {{ request('sort') === 'program_desc' ? 'selected' : '' }}>Program: Z-A</option>
                        <option value="status_asc" {{ request('sort') === 'status_asc' ? 'selected' : '' }}>Status: A-Z</option>
                        <option value="status_desc" {{ request('sort') === 'status_desc' ? 'selected' : '' }}>Status: Z-A</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-2 col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase">Rows</label>
                    <select class="form-select border-0 shadow-sm ajax-filter" name="per_page">
                        <option value="10"  {{ request('per_page', '25') == '10'  ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ request('per_page', '25') == '25'  ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ request('per_page', '25') == '50'  ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', '25') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-xl-4 col-lg-10 col-md-12 d-flex gap-2 justify-content-xl-end">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm d-none d-xl-inline-block">
                        <i class="bi bi-filter me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary px-4 shadow-sm bg-white" id="reset-filters">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div id="direct-assistance-table-container">
    <!-- Direct Assistance List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
            <span class="fw-semibold"><i class="bi bi-list-check me-1"></i> Direct Assistance Records</span>
            <span class="text-muted small">
                @if($directAssistance->total() > 0)
                    Showing {{ number_format($directAssistance->firstItem()) }}–{{ number_format($directAssistance->lastItem()) }}
                    of {{ number_format($directAssistance->total()) }} records
                @else
                    No records found
                @endif
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Beneficiary</th>
                            <th>Barangay</th>
                            <th>Program</th>
                            <th>Resource</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Released At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directAssistance as $assistance)
                            <tr>
                                <td class="text-muted small" data-label="Date">{{ $assistance->created_at->format('M d, Y') }}</td>
                                <td data-label="Beneficiary">
                                    <a href="{{ route('beneficiaries.show', $assistance->beneficiary) }}" class="text-decoration-none">
                                        {{ $assistance->beneficiary->full_name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td data-label="Barangay">{{ $assistance->beneficiary->barangay->name ?? 'N/A' }}</td>
                                <td data-label="Program">{{ $assistance->programName->name ?? 'N/A' }}</td>
                                <td data-label="Resource">{{ $assistance->resourceType->name ?? 'N/A' }}</td>
                                <td data-label="Value">{{ $assistance->getDisplayValue() }}</td>
                                @php($normalizedStatus = $assistance->normalized_status)
                                <td data-label="Status">
                                @switch($normalizedStatus)
                                    @case('planned')
                                        <span class="badge badge-soft-warning">Planned</span>
                                        @break
                                    @case('ready_for_release')
                                        <span class="badge badge-soft-primary">Ready for Release</span>
                                        @break
                                    @case('released')
                                        <span class="badge badge-soft-success">Released</span>
                                        @break
                                    @case('not_received')
                                        <span class="badge badge-soft-danger">Not Received</span>
                                        @break
                                    @default
                                        <span class="badge badge-soft-secondary">{{ $assistance->status_label }}</span>
                                        @break
                                @endswitch
                                </td>
                                <td data-label="Released At">
                                    @if($assistance->distributed_at)
                                        <small class="text-muted">{{ $assistance->distributed_at->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end" data-label="Actions">
                                    <a href="{{ route('direct-assistance.show', $assistance) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($normalizedStatus !== 'released')
                                        <a href="{{ route('direct-assistance.edit', $assistance) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    @endif

                                    @if(in_array($normalizedStatus, ['planned', 'not_received'], true))
                                        <form method="POST"
                                              action="{{ route('direct-assistance.mark-ready-for-release', $assistance) }}"
                                                class="direct-assistance-action-form"
                                              data-confirm-title="Set Ready for Release"
                                              data-confirm-message="Set this assistance to Ready for Release? This will mark the record as staged for distribution.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-bell"></i> Ready for Release
                                            </button>
                                        </form>
                                    @endif

                                    @if($normalizedStatus === 'ready_for_release')
                                        <form method="POST"
                                              action="{{ route('direct-assistance.mark-released', $assistance) }}"
                                              class="direct-assistance-action-form"
                                              data-confirm-title="Mark as Released"
                                              data-confirm-message="Mark this assistance as Released now?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check2"></i> Mark Released
                                            </button>
                                        </form>
                                        <form method="POST"
                                              action="{{ route('direct-assistance.mark-not-received', $assistance) }}"
                                              class="direct-assistance-action-form"
                                              data-confirm-title="Mark as Not Received"
                                              data-confirm-message="Mark this assistance as Not Received?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i> Not Received
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 24px;"></i>
                                    <p class="text-muted mt-2">No direct assistance records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-muted small order-2 order-md-1">
                    @if($directAssistance->total() > 0)
                        Showing {{ number_format($directAssistance->firstItem()) }} to {{ number_format($directAssistance->lastItem()) }} of {{ number_format($directAssistance->total()) }} records
                    @endif
                </div>
                @if($directAssistance->hasPages())
                    <div class="pagination-container order-1 order-md-2">
                        {{ $directAssistance->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Batch Mode Modal -->
<div class="modal fade" id="batchModeModal" tabindex="-1" aria-labelledby="batchModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="batchModeModalLabel">
                    <i class="bi bi-layers-fill me-2"></i>Batch Direct Assistance Mode
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="batchModeForm" action="{{ route('direct-assistance.storeBulk') }}" method="POST">
                    @csrf
                    <div class="bg-white p-3 border-bottom shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0 text-primary small fw-bold">
                                <i class="bi bi-search me-1"></i> Find Beneficiary (Batch)
                            </h6>
                        </div>
                        <div class="row g-2 mb-2 modern-filter-grid">
                            <div class="col-md-2">
                                <select id="batch_beneficiary_agency" class="form-select form-select-sm">
                                    <option value="">All Agencies</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="batch_beneficiary_barangay" class="form-select form-select-sm">
                                    <option value="">All Barangays</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="batch_beneficiary_classification" class="form-select form-select-sm">
                                    <option value="">All Sectors</option>
                                    <option value="Farmer">Farmer</option>
                                    <option value="Fisherfolk">Fisherfolk</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="batch_beneficiary_search" class="form-control form-control-sm" placeholder="Search name/contact...">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" id="batch_beneficiary_search_btn" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search"></i> Find
                                </button>
                            </div>
                        </div>
                        <div id="batch_beneficiary_results" class="list-group list-group-flush border rounded overflow-auto" style="max-height: 200px; display: none; background: white; position: absolute; z-index: 1050; width: calc(100% - 2rem); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <!-- Results will be injected here -->
                        </div>
                        <div id="batch_beneficiary_hint" class="small text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i> Search and click "Add" to include beneficiaries in the batch table.
                        </div>
                    </div>
                    <div class="bg-light p-3 border-bottom shadow-sm" style="background-color: #f8fafc !important;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-primary mb-1">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> Quick Set: Program
                                </label>
                                <select id="quickSetProgram" class="form-select form-select-sm border-primary-subtle">
                                    <option value="">Select Program</option>
                                    @foreach($programs as $prog)
                                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-primary mb-1">Resource Type</label>
                                <select id="quickSetResource" class="form-select form-select-sm border-primary-subtle" disabled>
                                    <option value="">Select Program First</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label id="quickSetValueLabel" class="form-label small fw-bold text-primary mb-1">Qty/Amt</label>
                                <input type="number" id="quickSetValue" class="form-control form-control-sm border-primary-subtle" step="0.01" placeholder="Value">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-primary mb-1">Purpose</label>
                                <select id="quickSetPurpose" class="form-select form-select-sm border-primary-subtle">
                                    <option value="">Select Purpose</option>
                                    @foreach($assistancePurposes as $purpose)
                                        <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" id="btnQuickSetApply" class="btn btn-success btn-sm fw-bold">
                                    <i class="bi bi-check-all me-1"></i> Apply
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">
                            <i class="bi bi-info-circle me-1"></i> This will update all <strong>selected</strong> rows below.
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="batchTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="batchSelectAll" checked>
                                    </th>
                                    <th style="width: 250px;">Beneficiary</th>
                                    <th style="width: 200px;">Program</th>
                                    <th style="width: 200px;">Resource Type</th>
                                    <th style="width: 150px;">Value (Qty/Amt)</th>
                                    <th style="width: 180px;">Assistance Purpose</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="batchTbody">
                                <!-- Dynamic rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                </form>

                <div id="batchEmptyState" class="text-center py-5 d-none">
                    <i class="bi bi-plus-circle text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-muted">No rows added. Click "Add Row" to start.</p>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-primary" id="batchAddRow">
                        <i class="bi bi-plus-lg me-1"></i> Add Row
                    </button>
                    <span class="ms-3 text-muted small" id="batchSummaryText">0 rows added</span>
                    <span class="ms-3 small" id="batchValidationStatus"></span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="batchModeForm" class="btn btn-primary" id="batchSubmitBtn">
                        <i class="bi bi-save me-1"></i> Save All Records
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const batchModal = document.getElementById('batchModeModal');
    const batchTbody = document.getElementById('batchTbody');
    const batchAddRowBtn = document.getElementById('batchAddRow');
    const batchSelectAll = document.getElementById('batchSelectAll');
    const batchSummaryText = document.getElementById('batchSummaryText');
    const batchEmptyState = document.getElementById('batchEmptyState');
    const batchForm = document.getElementById('batchModeForm');

    // AJAX Filtering Logic
    const filterContainer = document.getElementById('direct-assistance-table-container');
    const ajaxFilters = document.querySelectorAll('.ajax-filter');
    const filterForm = document.querySelector('.modern-filter-card form');

    const updateDirectAssistanceTable = async () => {
        if (!filterContainer || !filterForm) return;

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.pathname}?${params}`;

        // Add loading state
        filterContainer.style.opacity = '0.5';
        filterContainer.style.pointerEvents = 'none';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('direct-assistance-table-container');

            if (newContent) {
                filterContainer.innerHTML = newContent.innerHTML;
                history.pushState({}, '', url);
            }
        } catch (error) {
            console.error('Error filtering direct assistance:', error);
        } finally {
            filterContainer.style.opacity = '1';
            filterContainer.style.pointerEvents = 'auto';
        }
    };

    ajaxFilters.forEach(filter => {
        filter.addEventListener('change', updateDirectAssistanceTable);
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            updateDirectAssistanceTable();
        });
    }

    // Handle Reset Button
    const resetBtn = document.getElementById('reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterForm.reset();
            ajaxFilters.forEach(f => f.value = '');
            updateDirectAssistanceTable();
        });
    }

    // Delegate pagination link clicks to AJAX
    document.addEventListener('click', (e) => {
        const link = e.target.closest('#direct-assistance-table-container .pagination a');
        if (link) {
            e.preventDefault();
            const url = link.href;
            
            // Add loading state
            filterContainer.style.opacity = '0.5';
            filterContainer.style.pointerEvents = 'none';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('direct-assistance-table-container');
                    if (newContent) {
                        filterContainer.innerHTML = newContent.innerHTML;
                        history.pushState({}, '', url);
                        window.scrollTo({ top: filterContainer.offsetTop - 100, behavior: 'smooth' });
                    }
                })
                .finally(() => {
                    filterContainer.style.opacity = '1';
                    filterContainer.style.pointerEvents = 'auto';
                });
        }
    });
    const batchSubmitBtn = document.getElementById('batchSubmitBtn');
    const batchValidationStatus = document.getElementById('batchValidationStatus');

    let batchRowIndex = 0;
    let searchTimeout;

    // Helper functions (copied from allocations/index.blade.php for consistency)
    async function fetchEligiblePrograms(beneficiaryId) {
        if (!beneficiaryId) return [];
        const response = await fetch(`/api/eligible-programs/${beneficiaryId}`);
        if (!response.ok) throw new Error('Failed to fetch eligible programs');
        return await response.json();
    }

    async function loadResourceTypesByProgram(programSelect, resourceTypeSelect) {
        const programId = programSelect.value;
        if (!programId) {
            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Select Program First</option>';
            resourceTypeSelect.disabled = true;
            return;
        }

        try {
            resourceTypeSelect.disabled = true;
            const response = await fetch(`/api/programs/${programId}/resource-types`);
            if (!response.ok) throw new Error('Failed to fetch resource types');
            const resources = await response.json();

            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Select Resource</option>';
            resources.forEach(res => {
                const option = document.createElement('option');
                option.value = res.id;
                option.textContent = res.name;
                option.dataset.unit = res.unit;
                resourceTypeSelect.appendChild(option);
            });

            resourceTypeSelect.disabled = false;
            
            // Trigger change to update quantity/amount input name for batch row
            resourceTypeSelect.dispatchEvent(new Event('change'));
        } catch (error) {
            console.error('Error loading resource types:', error);
            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Error loading resources</option>';
        }
    }

    function createBatchRow(index, preset = null) {
        const row = document.createElement('tr');
        row.dataset.rowIndex = index;
        const suggestionsId = `batch_beneficiary_suggestions_${index}`;
        row.innerHTML = `
            <td class="text-center">
                <input type="checkbox" name="records[${index}][selected]" value="1" class="form-check-input batch-row-checkbox" checked>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm batch-beneficiary-search"
                       placeholder="Type beneficiary name..." data-row-index="${index}" list="${suggestionsId}" autocomplete="off" required>
                <datalist id="${suggestionsId}"></datalist>
                <input type="hidden" name="records[${index}][beneficiary_id]" class="batch-beneficiary-id">
            </td>
            <td>
                <select name="records[${index}][program_name_id]" class="form-select form-select-sm batch-program" required disabled>
                    <option value="">Select Beneficiary First</option>
                </select>
            </td>
            <td>
                <select name="records[${index}][resource_type_id]" class="form-select form-select-sm batch-resource" required disabled>
                    <option value="">Select Program First</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="records[${index}][quantity]"
                       class="form-control form-control-sm batch-quantity" placeholder="Qty" required>
            </td>
            <td>
                <select name="records[${index}][assistance_purpose_id]" class="form-select form-select-sm batch-purpose">
                    <option value="">-</option>
                    @foreach($assistancePurposes as $purpose)
                        <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger batch-remove-row" title="Remove row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        const beneficiarySearch = row.querySelector('.batch-beneficiary-search');
        const programSelect = row.querySelector('.batch-program');
        const resourceSelect = row.querySelector('.batch-resource');
        const beneficiaryIdInput = row.querySelector('.batch-beneficiary-id');
        const suggestionList = row.querySelector(`#${suggestionsId}`);
        const beneficiaryMap = new Map();
        let beneficiarySearchTimeout;

        async function loadBatchProgramsForBeneficiary(beneficiaryId) {
            if (!beneficiaryId) {
                programSelect.innerHTML = '<option value="" selected disabled>Select Beneficiary First</option>';
                programSelect.disabled = true;
                resourceSelect.innerHTML = '<option value="" selected disabled>Select Program First</option>';
                resourceSelect.disabled = true;
                return;
            }

            try {
                const programs = await fetchEligiblePrograms(beneficiaryId);
                programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';
                resourceSelect.innerHTML = '<option value="" selected disabled>Select Program First</option>';

                if (programs.length === 0) {
                    programSelect.innerHTML = '<option value="" selected disabled>No eligible programs</option>';
                    programSelect.disabled = true;
                    return;
                }

                programs.forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.id;
                    option.textContent = prog.formatted;
                    programSelect.appendChild(option);
                });

                programSelect.disabled = false;

                if (programs.length === 1) {
                    programSelect.value = String(programs[0].id);
                    await loadResourceTypesByProgram(programSelect, resourceSelect);
                }
            } catch (error) {
                console.error('Error loading batch eligible programs:', error);
                programSelect.innerHTML = '<option value="" selected disabled>Error</option>';
            }
        }

        function applyBatchBeneficiarySelection(displayValue) {
            const selectedBeneficiary = beneficiaryMap.get(displayValue);
            if (!selectedBeneficiary) {
                beneficiaryIdInput.value = '';
                loadBatchProgramsForBeneficiary('');
                return;
            }

            beneficiaryIdInput.value = String(selectedBeneficiary.id);
            beneficiarySearch.value = selectedBeneficiary.display;
            loadBatchProgramsForBeneficiary(selectedBeneficiary.id);
            validateBatchRows();
        }

        if (preset && preset.beneficiary) {
            const b = preset.beneficiary;
            const display = b.display || `${b.name} (${b.classification}) - ${b.barangay}`;
            beneficiaryMap.set(display, b);
            beneficiarySearch.value = display;
            beneficiaryIdInput.value = b.id;
            loadBatchProgramsForBeneficiary(b.id);
        }

        beneficiarySearch.addEventListener('input', () => {
            const query = beneficiarySearch.value.trim();
            beneficiaryIdInput.value = '';
            beneficiaryMap.clear();
            suggestionList.innerHTML = '';
            clearTimeout(beneficiarySearchTimeout);

            if (query.length < 2) {
                loadBatchProgramsForBeneficiary('');
                return;
            }

            beneficiarySearchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/beneficiaries/search?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    const results = data.results || [];

                    suggestionList.innerHTML = '';
                    beneficiaryMap.clear();

                    results.forEach((beneficiary) => {
                        beneficiaryMap.set(beneficiary.display, beneficiary);
                        const option = document.createElement('option');
                        option.value = beneficiary.display;
                        suggestionList.appendChild(option);
                    });

                    if (results.length === 1) {
                        applyBatchBeneficiarySelection(results[0].display);
                    }
                } catch (e) {
                    console.error('Error searching beneficiary:', e);
                }
            }, 250);
        });

        beneficiarySearch.addEventListener('change', () => {
            applyBatchBeneficiarySelection(beneficiarySearch.value.trim());
        });

        programSelect.addEventListener('change', () => {
            loadResourceTypesByProgram(programSelect, resourceSelect);
        });

        resourceSelect.addEventListener('change', () => {
            const selected = resourceSelect.options[resourceSelect.selectedIndex];
            const unit = selected ? selected.dataset.unit : '';
            const isFinancial = unit === 'PHP';
            const quantityInput = row.querySelector('.batch-quantity');

            if (isFinancial) {
                quantityInput.name = `records[${index}][amount]`;
                quantityInput.placeholder = 'Amount (PHP)';
                quantityInput.title = 'Enter amount in PHP';
            } else {
                quantityInput.name = `records[${index}][quantity]`;
                quantityInput.placeholder = 'Qty';
                quantityInput.title = 'Enter quantity';
                if (unit) quantityInput.placeholder += ` (${unit})`;
            }
        });

        row.querySelector('.batch-remove-row').addEventListener('click', () => {
            row.remove();
            updateBatchSummary();
        });

        row.querySelector('.batch-row-checkbox').addEventListener('change', () => {
            updateBatchSelectAll();
        });

        // Validation triggers
        [programSelect, resourceSelect, beneficiaryIdInput].forEach(el => {
            el.addEventListener('change', validateBatchRows);
        });
        row.querySelector('.batch-quantity').addEventListener('input', validateBatchRows);

        batchTbody.appendChild(row);
        updateBatchSummary();
        return row;
    }

    function updateBatchSummary() {
        const rows = batchTbody.querySelectorAll('tr');
        const rowCount = rows.length;
        batchSummaryText.textContent = `${rowCount} row${rowCount === 1 ? '' : 's'} added`;
        batchEmptyState.classList.toggle('d-none', rowCount > 0);
        batchSubmitBtn.disabled = rowCount === 0;

        if (rowCount > 0) {
            validateBatchRows();
        } else {
            batchValidationStatus.innerHTML = '';
        }
    }

    function validateBatchRows() {
        if (!batchTbody || !batchValidationStatus) return;
        
        const rows = batchTbody.querySelectorAll('tr');
        let isValid = true;
        let incompleteCount = 0;

        rows.forEach(row => {
            const bId = row.querySelector('.batch-beneficiary-id')?.value;
            const pId = row.querySelector('.batch-program')?.value;
            const rId = row.querySelector('.batch-resource')?.value;
            const qty = row.querySelector('.batch-quantity')?.value;

            // All fields must be present and quantity must be > 0
            const rowIsComplete = bId && pId && rId && qty && parseFloat(qty) > 0;

            if (!rowIsComplete) {
                isValid = false;
                incompleteCount++;
                row.classList.add('table-warning');
            } else {
                row.classList.remove('table-warning');
            }
        });

        if (isValid && rows.length > 0) {
            batchValidationStatus.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Ready</span>';
            batchSubmitBtn.disabled = false;
        } else if (rows.length > 0) {
            batchValidationStatus.innerHTML = `<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i> ${incompleteCount} Incomplete</span>`;
        } else {
            batchValidationStatus.innerHTML = '';
        }
    }

    function updateBatchSelectAll() {
        const checkboxes = batchTbody.querySelectorAll('.batch-row-checkbox');
        const checkedCount = batchTbody.querySelectorAll('.batch-row-checkbox:checked').length;
        batchSelectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        batchSelectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }

    batchAddRowBtn.addEventListener('click', () => {
        createBatchRow(batchRowIndex++);
    });

    batchSelectAll.addEventListener('change', () => {
        const checked = batchSelectAll.checked;
        batchTbody.querySelectorAll('.batch-row-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    });

    // Initialize with one row when modal opens if empty
    batchModal.addEventListener('shown.bs.modal', function () {
        if (batchTbody.querySelectorAll('tr').length === 0) {
            createBatchRow(batchRowIndex++);
        }
    });

    // Form submission
    batchForm.addEventListener('submit', function() {
        batchSubmitBtn.disabled = true;
        batchSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    });

    // ---- Batch Finder Logic ----
    const batchFinderSearchInput = document.getElementById('batch_beneficiary_search');
    const batchFinderBarangay = document.getElementById('batch_beneficiary_barangay');
    const batchFinderAgency = document.getElementById('batch_beneficiary_agency');
    const batchFinderClassification = document.getElementById('batch_beneficiary_classification');
    const batchFinderSearchBtn = document.getElementById('batch_beneficiary_search_btn');
    const batchFinderResults = document.getElementById('batch_beneficiary_results');

    function renderBatchFinderResults(results) {
        if (!batchFinderResults) return;
        batchFinderResults.innerHTML = '';

        if (results.length === 0) {
            batchFinderResults.innerHTML = '<div class="p-3 text-center text-muted small">No beneficiaries found.</div>';
            batchFinderResults.style.display = 'block';
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.batch-beneficiary-id'))
            .map(input => input.value)
            .filter(val => val !== '');

        results.forEach(beneficiary => {
            const isAlreadyAdded = selectedIds.includes(String(beneficiary.id));
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-sm d-flex justify-content-between align-items-center py-2';
            item.innerHTML = `
                <div class="text-start">
                    <div class="fw-bold small">${beneficiary.name}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <span class="badge bg-light text-dark border me-1">${beneficiary.classification}</span>
                        ${beneficiary.barangay}
                    </div>
                </div>
            `;

            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = isAlreadyAdded ? 'btn btn-sm btn-secondary' : 'btn btn-sm btn-outline-primary';
            addBtn.innerHTML = isAlreadyAdded ? 'Added' : '<i class="bi bi-plus-lg"></i> Add';
            addBtn.disabled = isAlreadyAdded;

            addBtn.addEventListener('click', () => {
                createBatchRow(batchRowIndex++, {
                    beneficiary,
                });
                addBtn.textContent = 'Added';
                addBtn.className = 'btn btn-sm btn-secondary';
                addBtn.disabled = true;
                updateBatchSummary();
            });

            item.appendChild(addBtn);
            batchFinderResults.appendChild(item);
        });

        batchFinderResults.style.display = 'block';
    }

    async function searchBatchBeneficiaries() {
        if (!batchFinderResults || !batchFinderSearchInput) return;

        const query = batchFinderSearchInput.value.trim();
        const barangayId = batchFinderBarangay ? batchFinderBarangay.value : '';
        const agencyId = batchFinderAgency ? batchFinderAgency.value : '';
        const classification = batchFinderClassification ? batchFinderClassification.value : '';

        try {
            const params = new URLSearchParams();
            if (query) params.append('q', query);
            if (barangayId) params.append('barangay_id', barangayId);
            if (agencyId) params.append('agency_id', agencyId);
            if (classification) params.append('classification', classification);

            const response = await fetch(`/api/beneficiaries/search?${params.toString()}`);
            const data = await response.json();
            renderBatchFinderResults(Array.isArray(data.results) ? data.results : []);
        } catch (error) {
            console.error('Batch search error:', error);
        }
    }

    if (batchFinderSearchInput) {
        batchFinderSearchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchBatchBeneficiaries();
            } else {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(searchBatchBeneficiaries, 300);
            }
        });
    }

    if (batchFinderSearchBtn) {
        batchFinderSearchBtn.addEventListener('click', searchBatchBeneficiaries);
    }

    [batchFinderBarangay, batchFinderAgency, batchFinderClassification].forEach(el => {
        if (el) el.addEventListener('change', searchBatchBeneficiaries);
    });

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        if (batchFinderResults && !batchFinderResults.contains(e.target) && e.target !== batchFinderSearchInput) {
            batchFinderResults.style.display = 'none';
        }
    });

    // ---- Quick Set Logic ----
    const quickSetProgram = document.getElementById('quickSetProgram');
    const quickSetResource = document.getElementById('quickSetResource');
    const quickSetValue = document.getElementById('quickSetValue');
    const quickSetValueLabel = document.getElementById('quickSetValueLabel');
    const quickSetPurpose = document.getElementById('quickSetPurpose');
    const btnQuickSetApply = document.getElementById('btnQuickSetApply');

    if (quickSetProgram) {
        quickSetProgram.addEventListener('change', async function() {
            await loadResourceTypesByProgram(quickSetProgram, quickSetResource);
        });
    }

    if (quickSetResource) {
        quickSetResource.addEventListener('change', function() {
            const selected = quickSetResource.options[quickSetResource.selectedIndex];
            const unit = selected ? selected.dataset.unit : '';
            if (unit === 'PHP') {
                quickSetValueLabel.textContent = 'Amount (PHP)';
                quickSetValue.placeholder = 'e.g. 1000.00';
            } else {
                quickSetValueLabel.textContent = 'Quantity' + (unit ? ` (${unit})` : '');
                quickSetValue.placeholder = 'Qty';
            }
        });
    }

    if (btnQuickSetApply) {
        btnQuickSetApply.addEventListener('click', async function() {
            const pId = quickSetProgram.value;
            const rId = quickSetResource.value;
            const val = quickSetValue.value;
            const purpId = quickSetPurpose.value;

            if (!pId && !rId && !val && !purpId) {
                alert('Please set at least one value in the Quick Set bar.');
                return;
            }

            const selectedRows = batchTbody.querySelectorAll('tr');
            let appliedCount = 0;
            const resourceCache = new Map(); // Cache to avoid redundant API calls

            for (const row of selectedRows) {
                const checkbox = row.querySelector('.batch-row-checkbox');
                if (!checkbox || !checkbox.checked) continue;

                const programSelect = row.querySelector('.batch-program');
                const resourceSelect = row.querySelector('.batch-resource');
                const quantityInput = row.querySelector('.batch-quantity');
                const purposeSelect = row.querySelector('.batch-purpose');

                // 1. Apply Program if provided and available for this row
                if (pId && !programSelect.disabled) {
                    const hasOption = Array.from(programSelect.options).some(opt => opt.value === pId);
                    if (hasOption) {
                        programSelect.value = pId;
                        
                        // 2. Load Resources for this row (with caching)
                        if (!resourceCache.has(pId)) {
                            await loadResourceTypesByProgram(programSelect, resourceSelect);
                            // Store the HTML options of the resource select to reuse
                            resourceCache.set(pId, resourceSelect.innerHTML);
                        } else {
                            resourceSelect.innerHTML = resourceCache.get(pId);
                            resourceSelect.disabled = false;
                        }
                    }
                }

                // 3. Apply Resource if provided and available
                if (rId && !resourceSelect.disabled) {
                    const hasOption = Array.from(resourceSelect.options).some(opt => opt.value === rId);
                    if (hasOption) {
                        resourceSelect.value = rId;
                        // Trigger change to update input names (quantity/amount)
                        resourceSelect.dispatchEvent(new Event('change'));
                    }
                }

                // 4. Apply Value
                if (val && !quantityInput.disabled) {
                    quantityInput.value = val;
                    quantityInput.dispatchEvent(new Event('input')); // Trigger validation
                }

                // 5. Apply Purpose
                if (purpId && !purposeSelect.disabled) {
                    purposeSelect.value = purpId;
                    purposeSelect.dispatchEvent(new Event('change')); // Trigger validation
                }

                appliedCount++;
                
                // Flash the row to show it was updated
                row.style.backgroundColor = '#f0f9ff';
                setTimeout(() => row.style.backgroundColor = '', 500);
            }

            if (appliedCount === 0) {
                alert('No selected/eligible rows found to apply values to.');
            } else {
                validateBatchRows();
            }
        });
    }
});
</script>
@endpush
@endsection
