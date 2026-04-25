@extends('layouts.app')

@section('title', 'Beneficiary Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Beneficiaries</li>
@endsection

@section('content')
    <div class="module-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Beneficiary Registry</h1>
            <p class="text-muted mb-0 fs-6">Manage and monitor community members with precision</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff']))
            <a href="{{ route('beneficiaries.create') }}" class="btn btn-success btn-primary-action">
                <i class="bi bi-person-plus-fill"></i>
                <span>Register Beneficiary</span>
            </a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bene-stat-card">
                <div class="card-body bene-stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted bene-stat-label mb-1">Total Registry</div>
                            <div class="bene-stat-value">{{ number_format($summary['total_all']) }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-normal">Overall Members</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bene-stat-card">
                <div class="card-body bene-stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted bene-stat-label mb-1">Active Status</div>
                            <div class="bene-stat-value text-primary">{{ number_format($summary['total_active']) }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-normal">Current Verified</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bene-stat-card">
                <div class="card-body bene-stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted bene-stat-label mb-1">Documented</div>
                            <div class="bene-stat-value text-info">{{ number_format($summary['with_documents']) }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill fw-normal">With Attachments</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bene-stat-card">
                <div class="card-body bene-stat-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted bene-stat-label mb-1">New this Month</div>
                            <div class="bene-stat-value text-warning">{{ number_format($summary['new_this_month']) }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill fw-normal">Recent Growth</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 modern-filter-card">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('beneficiaries.index') }}" id="beneficiaryFiltersForm">
                <div class="row g-3 align-items-center">
                    <!-- Primary Filters -->
                    <div class="col-lg-4 col-md-12">
                        <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-white">
                            <span class="input-group-text border-0 bg-white ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   class="form-control border-0 py-2 fs-7"
                                   placeholder="Search Registry..."
                                   value="{{ request('search') }}">
                            <button class="btn btn-success px-4" type="submit">Search</button>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-white">
                            <span class="input-group-text border-0 bg-white ps-3">
                                <i class="bi bi-geo-alt text-muted"></i>
                            </span>
                            <select name="barangay_id" id="barangay_id" class="form-select border-0 py-2 fs-7" onchange="this.form.submit()">
                                <option value="">All Barangays</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                        {{ $barangay->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-6 d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm shadow-sm border rounded-pill overflow-hidden bg-white flex-shrink-0" style="width: 110px;">
                            <span class="input-group-text border-0 bg-white ps-2 pe-1">
                                <i class="bi bi-list-ol text-muted small"></i>
                            </span>
                            <select name="per_page" id="per_page" class="form-select border-0 py-1 fs-7" onchange="this.form.submit()">
                                <option value="25" {{ $perPage === 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage === 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <button class="btn btn-light border shadow-sm rounded-pill px-3 text-nowrap fs-7 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false">
                            <i class="bi bi-sliders2 me-1"></i> Filters
                        </button>
                        <a href="{{ route('beneficiaries.index') }}" class="btn btn-link text-muted text-decoration-none small">Clear</a>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div class="collapse {{ $activeFilterCount > 2 || request('agency_id') || request('classification') || request('status') || $documentFilter ? 'show' : '' }}" id="advancedFilters">
                    <div class="pt-4 mt-3 border-top">
                        <div class="row g-3">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label for="agency_id" class="form-label small fw-semibold text-muted">Agency</label>
                                <select name="agency_id" id="agency_id" class="form-select shadow-sm">
                                    <option value="">All Agencies</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                            {{ $agency->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label for="classification" class="form-label small fw-semibold text-muted">Classification</label>
                                <select name="classification" id="classification" class="form-select shadow-sm">
                                    <option value="">All Types</option>
                                    <option value="Farmer" {{ request('classification') === 'Farmer' ? 'selected' : '' }}>Farmer</option>
                                    <option value="Fisherfolk" {{ request('classification') === 'Fisherfolk' ? 'selected' : '' }}>Fisherfolk</option>
                                    <option value="Both" {{ request('classification') === 'Both' ? 'selected' : '' }}>Both</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label for="status" class="form-label small fw-semibold text-muted">Status</label>
                                <select name="status" id="status" class="form-select shadow-sm">
                                    <option value="">All Status</option>
                                    <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label for="documents" class="form-label small fw-semibold text-muted">Documents</label>
                                <select name="documents" id="documents" class="form-select shadow-sm">
                                    <option value="">All Registry</option>
                                    <option value="with" {{ $documentFilter === 'with' ? 'selected' : '' }}>Complete (With Docs)</option>
                                    <option value="without" {{ $documentFilter === 'without' ? 'selected' : '' }}>Incomplete (No Docs)</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    Update Results
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            @if($activeFilterCount > 0)
                <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-medium">Filtered by:</span>
                    @if(request('search'))
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                            "{{ request('search') }}" <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ms-1 text-muted"><i class="bi bi-x-lg small"></i></a>
                        </span>
                    @endif
                    @if(request('barangay_id'))
                        @php $barangay = $barangays->firstWhere('id', (int) request('barangay_id')); @endphp
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                            Barangay: {{ $barangay?->name ?? 'Unknown' }} <a href="{{ request()->fullUrlWithQuery(['barangay_id' => null]) }}" class="ms-1 text-muted"><i class="bi bi-x-lg small"></i></a>
                        </span>
                    @endif
                    @if(request('agency_id'))
                        @php $agency = $agencies->firstWhere('id', (int) request('agency_id')); @endphp
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                            Agency: {{ $agency?->name ?? 'Unknown' }} <a href="{{ request()->fullUrlWithQuery(['agency_id' => null]) }}" class="ms-1 text-muted"><i class="bi bi-x-lg small"></i></a>
                        </span>
                    @endif
                    @if(request('classification'))
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                            {{ request('classification') }} <a href="{{ request()->fullUrlWithQuery(['classification' => null]) }}" class="ms-1 text-muted"><i class="bi bi-x-lg small"></i></a>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                            Status: {{ request('status') }} <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="ms-1 text-muted"><i class="bi bi-x-lg small"></i></a>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>


    <div class="card border-0 shadow-sm">
        <div class="card-body pb-0 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <p class="text-muted mb-0">
                {{ number_format($beneficiaries->total()) }} {{ Str::plural('beneficiary', $beneficiaries->total()) }} found
                <span class="small">(showing {{ $beneficiaries->firstItem() ?? 0 }}-{{ $beneficiaries->lastItem() ?? 0 }})</span>
            </p>
            <p class="text-muted small mb-0">Tip: Press <kbd>/</kbd> to focus search</p>
        </div>

        <div class="card-body pt-2">
            <div id="bulkActionBar" class="alert alert-light border d-none mb-3 py-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <div>
                        <span class="fw-semibold" id="selectedCountText">0 selected</span>
                        <span class="text-muted small">on this page</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="submitBulkStatus('Active')">
                            <i class="bi bi-person-check me-1"></i> Set Active
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="submitBulkStatus('Inactive')">
                            <i class="bi bi-person-x me-1"></i> Set Inactive
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="clearSelectionBtn">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            <form id="bulkStatusForm" method="POST" action="{{ route('beneficiaries.bulkStatus') }}" class="d-none" data-submit-spinner>
                @csrf
                <input type="hidden" name="status" id="bulkStatusInput">
                <div id="bulkStatusIds"></div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive beneficiary-table-wrapper">
                <table class="table table-hover align-middle mb-0 table-responsive-cards border-top">
                    <thead class="bg-light">
                        <tr>
                            <th class="sticky-select" style="width: 42px; min-width: 42px;">
                                <input class="form-check-input" type="checkbox" id="selectAllBeneficiaries">
                            </th>
                            <th class="sticky-index text-center" style="width: 50px;">#</th>
                            <th class="sticky-name" style="min-width: 250px;">Beneficiary Details</th>
                            <th>Barangay</th>
                            <th>Agency / Class</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Docs</th>
                            <th class="text-end pe-4" style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiaries as $beneficiary)
                            <tr class="beneficiary-row">
                                <td class="sticky-select" data-label="Select">
                                    <input class="form-check-input beneficiary-select"
                                           type="checkbox"
                                           value="{{ $beneficiary->id }}">
                                </td>
                                <td class="text-muted sticky-index text-center" data-label="#">{{ $beneficiaries->firstItem() + $loop->index }}</td>
                                <td class="sticky-name" data-label="Beneficiary Details">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][($beneficiary->id % 5)] }} bg-opacity-10 text-{{ ['primary', 'success', 'info', 'warning', 'danger'][($beneficiary->id % 5)] }}">
                                            {{ strtoupper(substr($beneficiary->first_name, 0, 1) . substr($beneficiary->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="fw-bold text-dark text-decoration-none d-block mb-0 h6 mb-1">{{ $beneficiary->full_name }}</a>
                                            <span class="text-muted small d-block"><i class="bi bi-telephone me-1"></i>{{ $beneficiary->contact_number ?: 'No Contact' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Barangay">
                                    <span class="fw-medium text-dark">{{ $beneficiary->barangay->name ?? '—' }}</span>
                                </td>
                                <td data-label="Agency / Class">
                                    <div class="small fw-semibold text-dark mb-1">{{ $beneficiary->agency->name ?? '—' }}</div>
                                    @php
                                        $classificationBadge = match($beneficiary->classification) {
                                            'Farmer' => 'badge-soft-primary',
                                            'Fisherfolk' => 'badge-soft-info',
                                            'Both' => 'badge-soft-purple',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $classificationBadge }} rounded-pill">{{ $beneficiary->classification }}</span>
                                </td>
                                <td class="text-center" data-label="Status">
                                    @if($beneficiary->status === 'Active')
                                        <span class="status-indicator status-active" title="Active"></span>
                                        <span class="small fw-medium text-success">Active</span>
                                    @else
                                        <span class="status-indicator status-inactive" title="Inactive"></span>
                                        <span class="small fw-medium text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Documents">
                                    @if($beneficiary->attachments_count > 0)
                                        <div class="badge-doc-count" title="{{ $beneficiary->attachments_count }} documents">
                                            <i class="bi bi-file-earmark-check-fill text-success"></i>
                                            <span class="count">{{ $beneficiary->attachments_count }}</span>
                                        </div>
                                    @else
                                        <i class="bi bi-file-earmark-x text-muted opacity-50" title="No documents"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-4" data-label="Actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('beneficiaries.show', $beneficiary) }}"
                                           class="btn btn-icon btn-light-info" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-icon btn-light-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('beneficiaries.edit', $beneficiary) }}">
                                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Edit Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('beneficiaries.attachments.create', $beneficiary) }}">
                                                        <i class="bi bi-paperclip me-2 text-primary"></i> Manage Documents
                                                    </a>
                                                </li>
                                                @if(Auth::user()->isAdmin())
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-danger"
                                                                data-confirm-message="Delete {{ $beneficiary->full_name }}?"
                                                                data-delete-url="{{ route('beneficiaries.destroy', $beneficiary) }}"
                                                                onclick="confirmAction('Confirm Deletion', this.dataset.confirmMessage, this.dataset.deleteUrl, 'DELETE')">
                                                            <i class="bi bi-trash me-2"></i> Delete Beneficiary
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state-container">
                                        <img src="{{ asset('images/empty-search.svg') }}" alt="No results" class="mb-3" style="width: 120px; opacity: 0.5;">
                                        <h5 class="text-muted fw-normal">No beneficiaries match your criteria</h5>
                                        <p class="text-muted small">Try adjusting your filters or search terms</p>
                                        <a href="{{ route('beneficiaries.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">Clear all filters</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-muted small order-2 order-md-1">
                    @if($beneficiaries->total() > 0)
                        Showing {{ number_format($beneficiaries->firstItem()) }} to {{ number_format($beneficiaries->lastItem()) }} of {{ number_format($beneficiaries->total()) }} beneficiaries
                    @endif
                </div>
                @if($beneficiaries->hasPages())
                    <div class="pagination-container order-1 order-md-2">
                        {{ $beneficiaries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Overhaul Styles */
.module-page .h3 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.5px;
}

.bene-stat-card {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    border: 1px solid #edf2f7 !important;
}
.bene-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Modern Filter Card */
.modern-filter-card {
    border-radius: 20px !important;
    background: #fdfdfd !important;
    border: 1px solid #edf2f7 !important;
}
.modern-filter-card .form-control:focus, 
.modern-filter-card .form-select:focus {
    box-shadow: none;
    border-color: #22c55e;
}

/* Table Refinements */
.beneficiary-table-wrapper {
    max-height: 70vh;
    border-radius: 0 0 12px 12px;
}
.beneficiary-row {
    transition: background-color 0.2s ease;
}
.beneficiary-row:hover {
    background-color: #f9fafb !important;
}

/* Avatar Circle */
.avatar-circle {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}

/* Status Indicators */
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.status-active { background-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
.status-inactive { background-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2); }

/* Document Badge */
.badge-doc-count {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-radius: 8px;
    color: #166534;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Custom Buttons */
.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    transition: all 0.2s;
}
.btn-light-info { background: #ecfdf5; color: #059669; border: none; }
.btn-light-info:hover { background: #059669; color: #fff; }
.btn-light-secondary { background: #f3f4f6; color: #4b5563; border: none; }
.btn-light-secondary:hover { background: #e5e7eb; color: #111827; }

/* Sticky behavior refinement */
@media (min-width: 992px) {
    .beneficiary-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f9fafb !important;
        border-bottom: 2px solid #edf2f7;
    }
    .sticky-select { left: 0; z-index: 11 !important; background: #fff !important; }
    .sticky-index { left: 42px; z-index: 11 !important; background: #fff !important; }
    .sticky-name { left: 92px; z-index: 11 !important; background: #fff !important; }
    
    thead .sticky-select { background: #f9fafb !important; }
    thead .sticky-index { background: #f9fafb !important; }
    thead .sticky-name { background: #f9fafb !important; }
}

/* Empty State */
.empty-state-container {
    padding: 40px 20px;
}

/* Small adjustments */
.fs-7 { font-size: 0.85rem; }
</style>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search');
    const selectAll = document.getElementById('selectAllBeneficiaries');
    const rowChecks = Array.from(document.querySelectorAll('.beneficiary-select'));
    const bulkBar = document.getElementById('bulkActionBar');
    const selectedCountText = document.getElementById('selectedCountText');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');

    function selectedIds() {
        return rowChecks.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    }

    function refreshBulkState() {
        const ids = selectedIds();
        const total = ids.length;

        bulkBar.classList.toggle('d-none', total === 0);
        selectedCountText.textContent = total + ' selected';

        if (selectAll) {
            if (total === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (total === rowChecks.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            refreshBulkState();
        });
    }

    rowChecks.forEach((checkbox) => {
        checkbox.addEventListener('change', refreshBulkState);
    });

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function () {
            rowChecks.forEach((checkbox) => {
                checkbox.checked = false;
            });
            refreshBulkState();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== '/' || !searchInput) {
            return;
        }

        const activeTag = (document.activeElement?.tagName || '').toLowerCase();
        if (activeTag === 'input' || activeTag === 'textarea' || document.activeElement?.isContentEditable) {
            return;
        }

        event.preventDefault();
        searchInput.focus();
        searchInput.select();
    });

    refreshBulkState();
});

function submitBulkStatus(statusValue) {
    const selected = Array.from(document.querySelectorAll('.beneficiary-select:checked')).map((checkbox) => checkbox.value);

    if (selected.length === 0) {
        showToast('Please select at least one beneficiary first.', 'warning');
        return;
    }

    const statusInput = document.getElementById('bulkStatusInput');
    const idsContainer = document.getElementById('bulkStatusIds');
    const form = document.getElementById('bulkStatusForm');

    statusInput.value = statusValue;
    idsContainer.innerHTML = '';

    selected.forEach((idValue) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_ids[]';
        input.value = idValue;
        idsContainer.appendChild(input);
    });

    form.submit();
}
</script>
@endpush
