@extends('layouts.app')

@section('title', 'Distribution Events')

@section('breadcrumb')
    <li class="breadcrumb-item active">Distribution Events</li>
@endsection

@section('content')
<div class="container-fluid module-page">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-1">
        <div>
            <h1 class="h3 mb-0">Distribution Events</h1>
            <p class="text-muted mb-0">Manage distribution schedules and release progress by barangay</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff']))
            <a href="{{ route('distribution-events.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create New Event
            </a>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="row g-2 mb-3 mt-1">
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-2 me-2">
                        <i class="bi bi-calendar-event text-primary fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Total Events</div>
                        <div class="fw-bold summary-stat-value">{{ number_format($total) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 me-2">
                        <i class="bi bi-hourglass-split text-info fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Pending</div>
                        <div class="fw-bold summary-stat-value">{{ number_format($pending) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-2 me-2">
                        <i class="bi bi-arrow-repeat text-warning fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Ongoing</div>
                        <div class="fw-bold summary-stat-value">{{ number_format($ongoing) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 bg-success bg-opacity-10 p-2 me-2">
                        <i class="bi bi-check-circle text-success fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Completed</div>
                        <div class="fw-bold summary-stat-value">{{ number_format($completed) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 bg-success bg-opacity-10 p-2 me-2">
                        <i class="bi bi-cash-stack text-success fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Financial Events</div>
                        <div class="fw-bold summary-stat-value">{{ number_format($totalFinancialEvents) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center summary-stat-card-body">
                    <div class="rounded-3 p-2 me-2" style="background-color: rgba(25, 135, 84, 0.1);">
                        <i class="bi bi-currency-exchange text-success fs-5"></i>
                    </div>
                    <div class="summary-stat-text">
                        <div class="text-muted summary-stat-label">Cash Disbursed</div>
                        <div class="fw-bold summary-stat-value summary-stat-value-cash">&#8369;{{ number_format($totalCashDisbursed, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    {{-- Enhanced Filter Section --}}
    <form id="filterForm" method="GET" action="{{ route('distribution-events.index') }}" class="mb-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden filter-bar-card">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">
                    <!-- Search -->
                    <div class="col-12 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Search</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-light border-end-0 px-2">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 ajax-filter ps-0" 
                                   placeholder="Event/Brgy..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <!-- Agency -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Agency</label>
                        <select name="agency_id" class="form-select bg-light border-0 ajax-filter">
                            <option value="">All Agencies</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ (string) request('agency_id') === (string) $agency->id ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Program -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Program</label>
                        <select name="program_name_id" class="form-select bg-light border-0 ajax-filter">
                            <option value="">All Programs</option>
                            @foreach($programNames as $program)
                                <option value="{{ $program->id }}" {{ request('program_name_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Status -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Status</label>
                        <select name="status" class="form-select bg-light border-0 ajax-filter">
                            <option value="">All Statuses</option>
                            @foreach(['Pending', 'Ongoing', 'Completed'] as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Sort -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1 text-uppercase tracking-wider">Sort</label>
                        <select name="sort" class="form-select bg-light border-0 ajax-filter">
                            <option value="created_desc" {{ request('sort', 'created_desc') === 'created_desc' ? 'selected' : '' }}>Newest</option>
                            <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Date</option>
                        </select>
                    </div>
                    <!-- Reset -->
                    <div class="col-auto ms-auto">
                        <a href="{{ route('distribution-events.index') }}" class="btn btn-light btn-icon-only rounded-circle" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Results Table --}}
    <div id="events-table-container">
        <div class="card border-0 shadow-sm">
        <div class="card-body pb-0">
            <p class="text-muted mb-2">{{ $events->total() }} {{ Str::plural('event', $events->total()) }} found</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Event Name</th>
                            <th>Barangay</th>
                            <th>Resource Type</th>
                            <th>Source Agency</th>
                            <th>Type</th>
                            <th>Distribution Date</th>
                            <th>Status</th>
                            <th class="text-center">Beneficiaries</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td class="text-muted" data-label="#">{{ $events->firstItem() + $loop->index }}</td>
                                <td data-label="Event Name" class="fw-semibold">{{ $event->name ?: 'N/A' }}</td>
                                <td data-label="Barangay">{{ $event->barangay->name }}</td>
                                <td data-label="Resource Type">{{ $event->resourceType->name }}</td>
                                <td data-label="Source Agency">
                                    @php
                                        $agencyName = $event->resourceType->agency->name ?? 'N/A';
                                        $agencyBadge = match($agencyName) {
                                            'DA'   => 'bg-success',
                                            'BFAR' => 'bg-primary',
                                            'DAR'  => 'bg-warning text-dark',
                                            'LGU'  => 'bg-secondary',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ str_replace('bg-', 'badge-soft-', $agencyBadge) }}">{{ $agencyName }}</span>
                                </td>
                                <td data-label="Type">
                                    @if($event->type === 'financial')
                                        <span class="badge badge-soft-success">Financial</span>
                                    @else
                                        <span class="badge badge-soft-secondary">Physical</span>
                                    @endif
                                </td>
                                <td data-label="Distribution Date">{{ $event->distribution_date->format('M d, Y') }}</td>
                                <td data-label="Status">
                                    @php
                                        $statusBadge = match($event->status) {
                                            'Pending'   => 'bg-info',
                                            'Ongoing'   => 'bg-warning text-dark',
                                            'Completed' => 'bg-success',
                                            default     => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ str_replace('bg-', 'badge-soft-', $statusBadge) }}">{{ $event->status }}</span>
                                </td>
                                <td class="text-center" data-label="Beneficiaries">{{ $event->allocations_count }}</td>
                                <td data-label="Created By">{{ $event->createdBy->name }}</td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="bi bi-eye"></i> <span class="btn-action-label">View</span>
                                    </a>
                                    @if($event->status === 'Pending')
                                        <a href="{{ route('distribution-events.edit', $event) }}" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                        </a>
                                        @if(Auth::user()->role === 'admin')
                                            @php
                                                $deleteMessage = "Are you sure you want to delete event in {$event->barangay->name} on {$event->distribution_date->format('M d, Y')}? This action cannot be undone.";
                                            @endphp
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger" title="Delete"
                                                    data-confirm-message="{{ $deleteMessage }}"
                                                    data-delete-url="{{ route('distribution-events.destroy', $event) }}"
                                                    onclick="confirmAction('Confirm Deletion', this.dataset.confirmMessage, this.dataset.deleteUrl, 'DELETE')">
                                                <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distribution events found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-muted small order-2 order-md-1">
                    @if($events->total() > 0)
                        Showing {{ number_format($events->firstItem()) }} to {{ number_format($events->lastItem()) }} of {{ number_format($events->total()) }} events
                    @endif
                </div>
                @if($events->hasPages())
                    <div class="pagination-container order-1 order-md-2">
                        {{ $events->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // AJAX Filtering Logic
    const filterContainer = document.getElementById('events-table-container');
    const filterForm = document.getElementById('filterForm');

    const updateEventsTable = async () => {
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
            const newContent = doc.getElementById('events-table-container');

            if (newContent) {
                filterContainer.innerHTML = newContent.innerHTML;
                history.pushState({}, '', url);
            }
        } catch (error) {
            console.error('Error filtering events:', error);
        } finally {
            filterContainer.style.opacity = '1';
            filterContainer.style.pointerEvents = 'auto';
        }
    };

    // Auto-trigger filters
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('ajax-filter') && e.target.tagName !== 'INPUT') {
            updateEventsTable();
        }
    });

    let filterTimeout;
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('ajax-filter') && e.target.tagName === 'INPUT') {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(updateEventsTable, 300);
        }
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            updateEventsTable();
        });
    }

    // Delegate pagination link clicks to AJAX
    document.addEventListener('click', (e) => {
        const link = e.target.closest('#events-table-container .pagination a');
        if (link) {
            e.preventDefault();
            const url = link.href;
            
            filterContainer.style.opacity = '0.5';
            filterContainer.style.pointerEvents = 'none';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('events-table-container');
                    if (newContent) {
                        filterContainer.innerHTML = newContent.innerHTML;
                        history.pushState({}, '', url);
                    }
                })
                .finally(() => {
                    filterContainer.style.opacity = '1';
                    filterContainer.style.pointerEvents = 'auto';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
        }
    });
</script>
@endpush
@endsection

@push('styles')
<style>
.module-page .h3 {
    font-size: 1.5rem;
    font-weight: 650;
}

.module-page .card {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.module-page .card.shadow-sm {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
}

.module-page .form-control,
.module-page .form-select,
.module-page .btn {
    border-radius: 10px;
}

.module-page .btn {
    font-size: 0.875rem;
}

.module-page .table thead th {
    font-size: 0.78rem;
    letter-spacing: 0.2px;
    color: #4b5563;
}

.module-page .table tbody td {
    font-size: 0.9rem;
}

.module-page .summary-stat-card-body {
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0.6rem 0.75rem;
}

.module-page .summary-stat-text {
    flex: 1 1 auto;
    min-width: 0;
}

.module-page .summary-stat-label {
    font-size: 0.7rem;
    line-height: 1.2;
}

.module-page .summary-stat-value {
    font-size: 0.95rem;
    line-height: 1.2;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.module-page .summary-stat-value-cash {
    display: block;
    max-width: 100%;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-all;
    font-size: clamp(0.68rem, 0.55rem + 0.4vw, 0.88rem);
    line-height: 1.2;
}

@media (min-width: 576px) {
    .module-page .summary-stat-card-body {
        flex-wrap: nowrap;
        gap: 0;
    }
}
</style>
@endpush
