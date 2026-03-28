@extends('layouts.app')

@section('title', 'Distribution Event Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item active">Event #{{ $event->id }}</li>
@endsection

@section('content')
<div class="container-fluid">

    @php
        $availableBeneficiaries = \App\Models\Beneficiary::where('barangay_id', $event->barangay_id)
            ->where('status', 'Active')
            ->whereNotIn('id', $allocatedBeneficiaryIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'classification']);
    @endphp

    {{-- ============================================================ --}}
    {{-- 1. EVENT HEADER CARD                                         --}}
    {{-- ============================================================ --}}
    @php
        $statusBadge = match($event->status) {
            'Pending'   => 'bg-info',
            'Ongoing'   => 'bg-warning text-dark',
            'Completed' => 'bg-success',
            default     => 'bg-secondary',
        };
        $agencyName = $event->resourceType->agency->name ?? 'N/A';
        $agencyBadge = match($agencyName) {
            'DA'   => 'bg-success',
            'BFAR' => 'bg-primary',
            'DAR'  => 'bg-warning text-dark',
            'LGU'  => 'bg-secondary',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">{{ $event->barangay->name }}</h1>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge {{ $statusBadge }}">{{ $event->status }}</span>
                    <span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span>
                    @if($event->isFinancial())
                        <span class="badge bg-success">Financial</span>
                    @else
                        <span class="badge bg-secondary">Physical</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            @if($event->status === 'Pending')
                <a href="{{ route('distribution-events.edit', $event) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            @endif

            {{-- Status transition buttons --}}
            @if($event->status === 'Pending')
                <button type="button" class="btn btn-warning btn-status-change"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-status="Ongoing"
                        data-title="Mark as Ongoing"
                        data-message="Are you sure you want to start this distribution event? The status will change from Pending to Ongoing.">
                    <i class="bi bi-play-fill me-1"></i> Mark as Ongoing
                </button>
            @endif

            @if($event->status === 'Ongoing' && Auth::user()->role === 'admin')
                <button type="button" class="btn btn-success btn-status-change"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        data-status="Completed"
                        data-title="Mark as Completed"
                        data-message="Are you sure you want to mark this event as Completed? This action cannot be reversed.">
                    <i class="bi bi-check-circle me-1"></i> Mark as Completed
                </button>
            @endif

            <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Event Details Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Event Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Barangay</div>
                    <div class="fw-semibold">{{ $event->barangay->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Resource Type</div>
                    <div class="fw-semibold">{{ $event->resourceType->name }} ({{ $event->resourceType->unit }})</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Source Agency</div>
                    <div class="fw-semibold"><span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Distribution Type</div>
                    <div class="fw-semibold">
                        @if($event->isFinancial())
                            <span class="badge bg-success">Financial Assistance</span>
                        @else
                            <span class="badge bg-secondary">Physical Resources</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Distribution Date</div>
                    <div class="fw-semibold">{{ $event->distribution_date->format('M d, Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold"><span class="badge {{ $statusBadge }}">{{ $event->status }}</span></div>
                </div>
                @if($event->isFinancial())
                    <div class="col-md-4">
                        <div class="text-muted small">Total Fund Budget</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->total_fund_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Total Disbursed</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->allocations->sum('amount'), 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Remaining Budget</div>
                        <div class="fw-semibold">&#8369;{{ number_format($event->total_fund_amount - $event->allocations->sum('amount'), 2) }}</div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="text-muted small">Created By</div>
                    <div class="fw-semibold">{{ $event->createdBy->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Created At</div>
                    <div class="fw-semibold">{{ $event->created_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 2. ALLOCATION SUMMARY CARDS                                  --}}
    {{-- ============================================================ --}}
    @php
        $totalAllocated  = $event->allocations->count();
        $totalDistributed = $event->allocations->whereNotNull('distributed_at')->count();
    @endphp

    @if($event->isFinancial())
        @php
            $totalAmountAllocated = $event->allocations->sum('amount');
            $totalClaimed = $event->allocations->whereNotNull('distributed_at')->count();
            $remainingBudget = $event->total_fund_amount - $totalAmountAllocated;
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Beneficiaries Allocated</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-cash-stack text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Amount Allocated</div>
                            <div class="fs-4 fw-bold">&#8369;{{ number_format($totalAmountAllocated, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Claimed</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalClaimed) }} / {{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-wallet2 text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Remaining Budget</div>
                            <div class="fs-4 fw-bold">&#8369;{{ number_format($remainingBudget, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $totalQuantity = $event->allocations->sum('quantity');
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Beneficiaries Allocated</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-box-seam text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Quantity to Distribute</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalQuantity, 2) }} {{ $event->resourceType->unit }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Distributed</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalDistributed) }} / {{ number_format($totalAllocated) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- 3. ADD BENEFICIARIES SECTION                                 --}}
    {{-- ============================================================ --}}
    @if($event->status !== 'Completed')
        <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                <i class="bi bi-plus-lg me-1"></i> Add Beneficiary
            </button>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addAllModal">
                <i class="bi bi-people me-1"></i> Add All Barangay Beneficiaries
            </button>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- 4. ALLOCATIONS TABLE                                         --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Allocations
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Beneficiary Name</th>
                            <th>Classification</th>
                            <th>Contact Number</th>
                            @if($event->isFinancial())
                                <th>Amount (PHP)</th>
                            @else
                                <th>Quantity</th>
                            @endif
                            <th>Distributed At</th>
                            <th>Remarks</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($event->allocations as $allocation)
                            <tr class="{{ $allocation->distributed_at ? 'table-success' : '' }}">
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $allocation->beneficiary->full_name }}</td>
                                <td>
                                    @php
                                        $classificationBadge = match($allocation->beneficiary->classification) {
                                            'Farmer'     => 'bg-primary',
                                            'Fisherfolk' => 'bg-info text-dark',
                                            'Both'       => '',
                                            default      => 'bg-secondary',
                                        };
                                    @endphp
                                    @if($allocation->beneficiary->classification === 'Both')
                                        <span class="badge" style="background-color: #6f42c1;">Both</span>
                                    @else
                                        <span class="badge {{ $classificationBadge }}">{{ $allocation->beneficiary->classification }}</span>
                                    @endif
                                </td>
                                <td>{{ $allocation->beneficiary->contact_number ?? '—' }}</td>
                                @if($event->isFinancial())
                                    <td>&#8369;{{ number_format($allocation->amount, 2) }}</td>
                                @else
                                    <td>{{ number_format($allocation->quantity, 2) }} {{ $event->resourceType->unit }}</td>
                                @endif
                                <td>
                                    @if($allocation->distributed_at)
                                        <span class="text-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            {{ $allocation->distributed_at->format('M d, Y h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not yet {{ $event->isFinancial() ? 'claimed' : 'distributed' }}</span>
                                    @endif
                                </td>
                                <td>{{ $allocation->remarks ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @if(!$allocation->distributed_at && $event->status !== 'Pending')
                                        <form method="POST"
                                              action="{{ route('allocations.markDistributed', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Distribution"
                                              data-confirm-message="Mark this allocation as {{ $event->isFinancial() ? 'claimed' : 'distributed' }}? This will timestamp the transaction.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                                <i class="bi bi-check2"></i> {{ $event->isFinancial() ? 'Mark as Claimed' : 'Distribute' }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($event->status !== 'Completed' && Auth::user()->role === 'admin')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmAction('Confirm Removal', 'Are you sure you want to remove {{ e($allocation->beneficiary->full_name) }} from this distribution event?', '{{ route('allocations.destroy', $allocation) }}', 'DELETE')">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No beneficiaries allocated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODALS                                                       --}}
{{-- ============================================================ --}}

{{-- 5. Status Change Confirmation Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('distribution-events.updateStatus', $event) }}" id="statusForm">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="statusMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="statusConfirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Single Beneficiary Modal --}}
@if($event->status !== 'Completed')
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1" aria-labelledby="addBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('allocations.store') }}">
                @csrf
                <input type="hidden" name="release_method" value="event">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeneficiaryModalLabel">Add Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="beneficiary_id" class="form-label">Beneficiary <span class="text-danger">*</span></label>
                        <select class="form-select @error('beneficiary_id') is-invalid @enderror"
                                id="beneficiary_id" name="beneficiary_id" required>
                            <option value="" disabled selected>Select Beneficiary</option>
                            {{-- Populated via JS --}}
                        </select>
                        @error('beneficiary_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($event->isFinancial())
                        <div class="mb-3">
                            <label for="amount" class="form-label">
                                Amount (PHP) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="1"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   id="amount" name="amount" value="{{ old('amount') }}"
                                   placeholder="e.g. 1000.00" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="quantity" class="form-label">
                                Quantity <span class="text-danger">*</span>
                                <span class="badge bg-secondary ms-1">{{ $event->resourceType->unit }}</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" max="9999.99"
                                   class="form-control @error('quantity') is-invalid @enderror"
                                   id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" class="form-control" id="remarks" name="remarks"
                               value="{{ old('remarks') }}" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add All Barangay Beneficiaries Modal --}}
<div class="modal fade" id="addAllModal" tabindex="-1" aria-labelledby="addAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST"
                action="{{ route('allocations.storeBulk') }}"
                id="bulkForm"
                data-confirm-title="Confirm Bulk Allocation"
                data-confirm-message="Add allocations for all selected beneficiaries in this barangay? This is a bulk transaction.">
                @csrf
                <input type="hidden" name="release_method" value="event">
                <input type="hidden" name="distribution_event_id" value="{{ $event->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAllModalLabel">Add All Barangay Beneficiaries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Beneficiary Name</th>
                                    <th>Classification</th>
                                    @if($event->isFinancial())
                                        <th style="width: 150px;">Amount (PHP) <span class="text-danger">*</span></th>
                                    @else
                                        <th style="width: 150px;">Quantity ({{ $event->resourceType->unit }}) <span class="text-danger">*</span></th>
                                    @endif
                                    <th style="width: 200px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="bulkTableBody">
                                {{-- Populated via JS --}}
                            </tbody>
                        </table>
                    </div>
                    <div id="bulkEmpty" class="text-center text-muted py-4 d-none">
                        <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                        All beneficiaries in this barangay are already allocated.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="bulkSubmitBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Status change modal ----
    const statusModal = document.getElementById('statusModal');
    if (statusModal) {
        statusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('statusInput').value = button.getAttribute('data-status');
            document.getElementById('statusModalLabel').textContent = button.getAttribute('data-title');
            document.getElementById('statusMessage').textContent = button.getAttribute('data-message');
        });
    }

    // ---- Beneficiary data for modals ----
    const beneficiaries = @json($availableBeneficiaries);

    const isFinancial = @json($event->isFinancial());

    // Populate single-add dropdown
    (function populateSingleSelect() {
        const select = document.getElementById('beneficiary_id');
        if (!select) return;
        beneficiaries.forEach(b => {
            const option = document.createElement('option');
            option.value = b.id;
            option.textContent = `${b.full_name} (${b.classification})`;
            select.appendChild(option);
        });
    })();

    // Populate bulk-add table
    (function populateBulkTable() {
        const tbody = document.getElementById('bulkTableBody');
        const emptyMsg = document.getElementById('bulkEmpty');
        const submitBtn = document.getElementById('bulkSubmitBtn');
        if (!tbody) return;

        if (beneficiaries.length === 0) {
            emptyMsg.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        emptyMsg.classList.add('d-none');

        beneficiaries.forEach((b, i) => {
            const classificationBadge = {
                'Farmer': 'bg-primary',
                'Fisherfolk': 'bg-info text-dark',
                'Both': '',
            }[b.classification] || 'bg-secondary';

            const badgeHtml = b.classification === 'Both'
                ? `<span class="badge" style="background-color: #6f42c1;">Both</span>`
                : `<span class="badge ${classificationBadge}">${b.classification}</span>`;

            const valueInput = isFinancial
                ? `<input type="number" step="0.01" min="1"
                          class="form-control form-control-sm"
                          name="allocations[${i}][amount]" placeholder="e.g. 1000.00" required>`
                : `<input type="number" step="0.01" min="0.01" max="9999.99"
                          class="form-control form-control-sm"
                          name="allocations[${i}][quantity]" required>`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    ${b.full_name}
                    <input type="hidden" name="allocations[${i}][beneficiary_id]" value="${b.id}">
                </td>
                <td>${badgeHtml}</td>
                <td>${valueInput}</td>
                <td>
                    <input type="text" class="form-control form-control-sm"
                           name="allocations[${i}][remarks]" maxlength="500">
                </td>
            `;
            tbody.appendChild(row);
        });
    })();

    // Re-open add modal on validation errors
    @if($errors->any() && old('_token') && !old('_method'))
        new bootstrap.Modal(document.getElementById('addBeneficiaryModal')).show();
    @endif
});
</script>
@endpush
