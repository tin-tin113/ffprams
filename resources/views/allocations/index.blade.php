@extends('layouts.app')

@section('title', 'Assistance Allocations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Assistance Allocations</li>
@endsection

@section('content')
<div class="container-fluid module-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Assistance Allocations</h1>
            <p class="text-muted mb-0">Record and monitor direct assistance allocations (Standalone Distributions)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('allocations.create') }}" class="btn btn-success btn-primary-action shadow-sm rounded-pill px-4">
                <i class="bi bi-plus-circle me-1"></i>
                <span>Add Direct Allocation</span>
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
                            <i class="bi bi-calendar-event fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Planned</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($summary['planned']) }}</h2>
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
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Ready for Release</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($summary['ready']) }}</h2>
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
                        <h2 class="mb-0 fw-bold">{{ number_format($summary['released']) }}</h2>
                        <span class="text-success small"><i class="bi bi-graph-up"></i> Success</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-info-subtle text-info rounded-circle p-2 me-2">
                            <i class="bi bi-folder2-open fs-4"></i>
                        </div>
                        <span class="text-muted small fw-medium text-uppercase tracking-wider">Total records</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h2 class="mb-0 fw-bold">{{ number_format($summary['total']) }}</h2>
                        <span class="text-info small fw-bold">Grand Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Section -->
    <form id="filterForm" method="GET" action="{{ route('allocations.index') }}" class="mb-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden filter-bar-card">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">
                    <!-- Search -->
                    <div class="col-12 col-lg-3">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Search</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 ajax-filter" 
                                   placeholder="Beneficiary name or remarks..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <!-- Program -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Program</label>
                        <select name="program_name_id" class="form-select bg-light border-0 ajax-filter">
                            <option value="">All Programs</option>
                            @foreach($programNames as $program)
                                <option value="{{ $program->id }}" {{ (string) request('program_name_id') === (string) $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} ({{ $program->agency->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Status -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Status</label>
                        <select name="status" class="form-select bg-light border-0 ajax-filter">
                            <option value="">All Statuses</option>
                            <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="ready_for_release" {{ request('status') === 'ready_for_release' ? 'selected' : '' }}>Ready</option>
                            <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                            <option value="not_received" {{ request('status') === 'not_received' ? 'selected' : '' }}>Not Received</option>
                        </select>
                    </div>
                    <!-- Sorting -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Sort</label>
                        <select name="sort" class="form-select bg-light border-0 ajax-filter">
                            <option value="date_desc" {{ request('sort', 'date_desc') === 'date_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="program_asc" {{ request('sort') === 'program_asc' ? 'selected' : '' }}>Program A-Z</option>
                        </select>
                    </div>
                    <!-- Rows -->
                    <div class="col-12 col-sm-6 col-md-1">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Rows</label>
                        <select name="per_page" class="form-select bg-light border-0 ajax-filter">
                            <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', '25') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', '25') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <!-- Reset -->
                    <div class="col-auto ms-auto">
                        <a href="{{ route('allocations.index') }}" class="btn btn-light btn-icon-only rounded-circle" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="allocation-table-container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
            <span class="fw-semibold"><i class="bi bi-list-check me-1"></i> Direct Allocations</span>
            <span class="text-muted small">
                @if($directAllocations->total() > 0)
                    Showing {{ number_format($directAllocations->firstItem()) }}&ndash;{{ number_format($directAllocations->lastItem()) }}
                    of {{ number_format($directAllocations->total()) }} records
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
                            <th>Program</th>
                            <th>Resource</th>
                            <th>Value</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th class="text-end text-nowrap" style="min-width: 340px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directAllocations as $allocation)
                            <tr>
                                <td class="text-muted small" data-label="Date">{{ $allocation->created_at->format('M d, Y') }}</td>
                                <td data-label="Beneficiary">{{ $allocation->beneficiary->full_name ?? 'N/A' }}</td>
                                <td data-label="Program">{{ $allocation->programName->name ?? 'N/A' }}</td>
                                <td data-label="Resource">{{ $allocation->resourceType->name ?? 'N/A' }}</td>
                                <td data-label="Value">{{ $allocation->getDisplayValue() }}</td>
                                <td data-label="Purpose">{{ $allocation->assistancePurpose->name ?? 'N/A' }}</td>
                                @php($releaseStatus = $allocation->release_status)
                                <td data-label="Status">
                                    @switch($releaseStatus)
                                        @case('planned')
                                            <span class="badge bg-warning text-dark">Planned</span>
                                            @break
                                        @case('ready_for_release')
                                            <span class="badge bg-primary">Ready for Release</span>
                                            @break
                                        @case('released')
                                            <span class="badge bg-success">Released</span>
                                            @break
                                        @case('not_received')
                                            <span class="badge bg-danger">Not Received</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $allocation->release_status_label }}</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap justify-content-end">
                                        <a href="{{ route('allocations.show', $allocation) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>

                                        @if(in_array($releaseStatus, ['planned', 'not_received'], true))
                                            <form method="POST"
                                                  action="{{ route('allocations.mark-ready-for-release', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Set Ready for Release"
                                                  data-confirm-message="Set this direct allocation to Ready for Release? This will mark the record as staged for distribution.">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-bell"></i> Ready for Release
                                                </button>
                                            </form>
                                        @elseif($releaseStatus === 'ready_for_release')
                                            <form method="POST"
                                                  action="{{ route('allocations.markDistributed', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Confirm Release"
                                                  data-confirm-message="Mark this direct allocation as released? This will timestamp the release transaction.">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check2"></i> Mark Released
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('allocations.markNotReceived', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Confirm Not Received"
                                                  data-confirm-message="Mark this direct allocation as Not Received?">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-lg"></i> Not Received
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small ms-1">Finalized</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No direct allocations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-muted small order-2 order-md-1">
                    @if($directAllocations->total() > 0)
                        Showing {{ number_format($directAllocations->firstItem()) }} to {{ number_format($directAllocations->lastItem()) }} of {{ number_format($directAllocations->total()) }} records
                    @endif
                </div>
                @if($directAllocations->hasPages())
                    <div class="pagination-container order-1 order-md-2">
                        {{ $directAllocations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
</div>
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
        background-color: rgba(25, 135, 84, 0.02) !important;
    }
    .badge-soft-primary { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .badge-soft-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-soft-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-soft-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // AJAX Filtering Logic
    const filterContainer = document.getElementById('allocation-table-container');
    const filterForm = document.getElementById('filterForm');

    const updateAllocationTable = async () => {
        if (!filterContainer || !filterForm) return;

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.pathname}?${params}`;

        filterContainer.style.opacity = '0.5';
        filterContainer.style.pointerEvents = 'none';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('allocation-table-container');

            if (newContent) {
                filterContainer.innerHTML = newContent.innerHTML;
                history.pushState({}, '', url);
            }
        } catch (error) {
            console.error('Error filtering allocations:', error);
        } finally {
            filterContainer.style.opacity = '1';
            filterContainer.style.pointerEvents = 'auto';
        }
    };

    // Auto-trigger filters
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('ajax-filter') && e.target.tagName !== 'INPUT') {
            updateAllocationTable();
        }
    });

    let filterTimeout;
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('ajax-filter') && e.target.tagName === 'INPUT') {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(updateAllocationTable, 300);
        }
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            updateAllocationTable();
        });
    }

    // Delegate pagination link clicks to AJAX
    document.addEventListener('click', (e) => {
        const link = e.target.closest('#allocation-table-container .pagination a');
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
                    const newContent = doc.getElementById('allocation-table-container');
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
});
</script>
@endpush
