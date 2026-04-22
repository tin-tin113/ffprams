@extends('layouts.app')

@section('title', 'Beneficiary Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Beneficiaries</li>
@endsection

@section('content')
    <div class="module-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-1">
        <div>
            <h1 class="h3 mb-0">Beneficiary Management</h1>
            <p class="text-muted mb-0">Registry view with barangay-level filters and quick actions</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff']))
            <a href="{{ route('beneficiaries.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i> Add New Beneficiary
            </a>
        @endif
    </div>

    <div class="row g-3 mb-4 mt-2">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Beneficiaries</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['total_all']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-check-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active Beneficiaries</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['total_active']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-paperclip text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">With Documents</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['with_documents']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Without Documents</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['without_documents']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 modern-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('beneficiaries.index') }}" id="beneficiaryFiltersForm">
                <div class="row g-3 align-items-end modern-filter-grid">
                    <div class="col-xl-2 col-lg-6">
                        <label for="search" class="form-label small text-nowrap">Search</label>
                        <input type="text"
                               name="search"
                               id="search"
                               class="form-control"
                               placeholder="Name, contact, RSBSA, FishR, CLOA/EP"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label for="barangay_id" class="form-label small text-nowrap">Barangay</label>
                        <select name="barangay_id" id="barangay_id" class="form-select">
                            <option value="">All Barangays</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label for="agency_id" class="form-label small text-nowrap">Agency</label>
                        <select name="agency_id" id="agency_id" class="form-select">
                            <option value="">All Agencies</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-1 col-lg-3 col-md-4">
                        <label for="classification" class="form-label small text-nowrap">Class</label>
                        <select name="classification" id="classification" class="form-select">
                            <option value="">All</option>
                            <option value="Farmer" {{ request('classification') === 'Farmer' ? 'selected' : '' }}>Farmer</option>
                            <option value="Fisherfolk" {{ request('classification') === 'Fisherfolk' ? 'selected' : '' }}>Fisherfolk</option>
                            <option value="Both" {{ request('classification') === 'Both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>

                    <div class="col-xl-1 col-lg-3 col-md-4">
                        <label for="status" class="form-label small text-nowrap">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-xl-1 col-lg-3 col-md-4">
                        <label for="documents" class="form-label small text-nowrap">Docs</label>
                        <select name="documents" id="documents" class="form-select">
                            <option value="">All</option>
                            <option value="with" {{ $documentFilter === 'with' ? 'selected' : '' }}>With Documents</option>
                            <option value="without" {{ $documentFilter === 'without' ? 'selected' : '' }}>Without Documents</option>
                        </select>
                    </div>
                    <div class="col-xl-1 col-lg-3 col-md-4">
                        <label for="per_page" class="form-label small text-nowrap">Rows</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="25" {{ $perPage === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage === 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-6 modern-filter-actions">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>

            @if($activeFilterCount > 0)
                <hr class="my-3">
                <div class="modern-filter-chip-list">
                    <span class="text-muted small fw-semibold">Active Filters:</span>

                    @if(request('search'))
                        <span class="modern-filter-chip">Search: {{ request('search') }}</span>
                    @endif
                    @if(request('barangay_id'))
                        @php $barangay = $barangays->firstWhere('id', (int) request('barangay_id')); @endphp
                        <span class="modern-filter-chip">Barangay: {{ $barangay?->name ?? request('barangay_id') }}</span>
                    @endif
                    @if(request('agency_id'))
                        @php $agency = $agencies->firstWhere('id', (int) request('agency_id')); @endphp
                        <span class="modern-filter-chip">Agency: {{ $agency?->name ?? request('agency_id') }}</span>
                    @endif
                    @if(request('classification'))
                        <span class="modern-filter-chip">Classification: {{ request('classification') }}</span>
                    @endif
                    @if(request('status'))
                        <span class="modern-filter-chip">Status: {{ request('status') }}</span>
                    @endif
                    @if($documentFilter)
                        <span class="modern-filter-chip">Documents: {{ $documentFilter === 'with' ? 'With Documents' : 'Without Documents' }}</span>
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
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-select" style="width: 42px; min-width: 42px;">
                                <input class="form-check-input" type="checkbox" id="selectAllBeneficiaries" aria-label="Select all beneficiaries on this page">
                            </th>
                            <th class="sticky-index" style="width: 58px; min-width: 58px;">#</th>
                            <th class="sticky-name" style="min-width: 220px;">Full Name</th>
                            <th>Barangay</th>
                            <th>Agency</th>
                            <th>Classification</th>
                            <th>Contact Number</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                            <th class="text-end" style="min-width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiaries as $beneficiary)
                            <tr>
                                <td class="sticky-select" data-label="Select">
                                    <input class="form-check-input beneficiary-select"
                                           type="checkbox"
                                           value="{{ $beneficiary->id }}"
                                           aria-label="Select {{ $beneficiary->full_name }}">
                                </td>
                                <td class="text-muted sticky-index" data-label="#">{{ $beneficiaries->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold sticky-name" data-label="Full Name">
                                    <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="text-decoration-none">{{ $beneficiary->full_name }}</a>
                                </td>
                                <td data-label="Barangay">{{ $beneficiary->barangay->name ?? '—' }}</td>
                                <td data-label="Agency">{{ $beneficiary->agency->name ?? '—' }}</td>
                                <td data-label="Classification">
                                    @php
                                        $classificationBadge = match($beneficiary->classification) {
                                            'Farmer' => 'bg-primary',
                                            'Fisherfolk' => 'bg-info text-dark',
                                            'Both' => '',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    @if($beneficiary->classification === 'Both')
                                        <span class="badge" style="background-color: #6f42c1;">{{ $beneficiary->classification }}</span>
                                    @else
                                        <span class="badge {{ $classificationBadge }}">{{ $beneficiary->classification }}</span>
                                    @endif
                                </td>
                                <td data-label="Contact Number">{{ $beneficiary->contact_number ?: '—' }}</td>
                                <td data-label="Documents">
                                    @if($beneficiary->attachments_count > 0)
                                        <span class="badge bg-success">{{ $beneficiary->attachments_count }}</span>
                                    @else
                                        <span class="badge bg-light text-dark border">0</span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $beneficiary->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $beneficiary->status }}
                                    </span>
                                </td>
                                <td class="text-muted small" data-label="Registered Date">{{ $beneficiary->registered_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    <a href="{{ route('beneficiaries.show', $beneficiary) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="bi bi-eye"></i> <span class="btn-action-label">View</span>
                                    </a>
                                    <a href="{{ route('beneficiaries.edit', $beneficiary) }}"
                                       class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                    </a>
                                    <a href="{{ route('beneficiaries.attachments.create', $beneficiary) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="Upload Documents">
                                        <i class="bi bi-paperclip"></i> <span class="btn-action-label">Docs</span>
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger" title="Delete"
                                                data-confirm-message="Are you sure you want to delete {{ $beneficiary->full_name }}? This action cannot be undone."
                                                data-delete-url="{{ route('beneficiaries.destroy', $beneficiary) }}"
                                                onclick="confirmAction('Confirm Deletion', this.dataset.confirmMessage, this.dataset.deleteUrl, 'DELETE')">
                                            <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No beneficiaries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($beneficiaries->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $beneficiaries->links() }}
        </div>
    @endif
    </div>
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

.module-page .fs-4 {
    font-size: 1.5rem !important;
}

@media (min-width: 992px) {
    #beneficiaryFiltersForm .form-control,
    #beneficiaryFiltersForm .form-select {
        font-size: 0.875rem;
    }

    #beneficiaryFiltersForm .form-label {
        margin-bottom: 0.35rem;
    }

    .beneficiary-table-wrapper {
        max-height: 68vh;
        overflow: auto;
    }

    .beneficiary-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 6;
        background: #f8f9fa;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
    }

    .beneficiary-table-wrapper .sticky-select,
    .beneficiary-table-wrapper .sticky-index,
    .beneficiary-table-wrapper .sticky-name {
        position: sticky;
        z-index: 5;
        background: #fff;
    }

    .beneficiary-table-wrapper .sticky-select {
        left: 0;
    }

    .beneficiary-table-wrapper .sticky-index {
        left: 42px;
    }

    .beneficiary-table-wrapper .sticky-name {
        left: 100px;
    }

    .beneficiary-table-wrapper thead .sticky-select,
    .beneficiary-table-wrapper thead .sticky-index,
    .beneficiary-table-wrapper thead .sticky-name {
        z-index: 8;
        background: #f8f9fa;
    }

    .beneficiary-table-wrapper td[data-label="Actions"] .btn {
        padding: 0.2rem 0.42rem;
        font-size: 0.78rem;
        line-height: 1.2;
    }

    .beneficiary-table-wrapper td[data-label="Actions"] .btn .bi {
        font-size: 0.82rem;
    }
}

@media (min-width: 992px) and (max-width: 1500px) {
    .beneficiary-table-wrapper td[data-label="Actions"] .btn-action-label {
        display: none;
    }

    .beneficiary-table-wrapper td[data-label="Actions"] .btn {
        padding: 0.22rem 0.4rem;
    }
}
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
