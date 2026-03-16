@extends('layouts.app')

@section('title', 'Field Assessments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Field Assessments</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h1 class="h3 mb-0">Field Assessments</h1>
            <p class="text-muted mb-0">Beneficiary eligibility assessments &amp; approval tracking</p>
        </div>
        <a href="{{ route('field-assessments.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> New Field Assessment
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4 mt-2">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-clipboard-check text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Assessments</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Approval</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['pending_approval']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Approved</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['approved']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-x-circle text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Not Eligible</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary['not_eligible']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('field-assessments.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Name or Government ID"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="barangay_id" class="form-label">Barangay</label>
                        <select name="barangay_id" id="barangay_id" class="form-select">
                            <option value="">All Barangays</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}"
                                    {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="eligibility_status" class="form-label">Eligibility</label>
                        <select name="eligibility_status" id="eligibility_status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('eligibility_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="eligible" {{ request('eligibility_status') === 'eligible' ? 'selected' : '' }}>Eligible</option>
                            <option value="not_eligible" {{ request('eligibility_status') === 'not_eligible' ? 'selected' : '' }}>Not Eligible</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="approval_status" class="form-label">Approval</label>
                        <select name="approval_status" id="approval_status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="assessed_by" class="form-label">Assessed By</label>
                        <select name="assessed_by" id="assessed_by" class="form-select">
                            <option value="">All Assessors</option>
                            @foreach($assessors as $assessor)
                                <option value="{{ $assessor->id }}"
                                    {{ request('assessed_by') == $assessor->id ? 'selected' : '' }}>
                                    {{ $assessor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <a href="{{ route('field-assessments.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body pb-0">
            <p class="text-muted mb-2">{{ $assessments->total() }} {{ Str::plural('assessment', $assessments->total()) }} found</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Beneficiary Name</th>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Visit Date</th>
                            <th>Assessed By</th>
                            <th>Eligibility</th>
                            <th>Approval</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessments as $assessment)
                            <tr>
                                <td class="text-muted">{{ $assessments->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $assessment->beneficiary->full_name }}</td>
                                <td>{{ $assessment->beneficiary->barangay->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $classBadge = match($assessment->beneficiary->classification) {
                                            'Farmer'     => 'bg-primary',
                                            'Fisherfolk' => 'bg-info text-dark',
                                            'Both'       => '',
                                            default      => 'bg-secondary',
                                        };
                                    @endphp
                                    @if($assessment->beneficiary->classification === 'Both')
                                        <span class="badge" style="background-color: #6f42c1;">{{ $assessment->beneficiary->classification }}</span>
                                    @else
                                        <span class="badge {{ $classBadge }}">{{ $assessment->beneficiary->classification }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $assessment->visit_date->format('M d, Y') }}</td>
                                <td>{{ $assessment->assessedBy->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $eligBadge = match($assessment->eligibility_status) {
                                            'eligible'     => 'bg-success',
                                            'not_eligible' => 'bg-danger',
                                            default        => 'bg-secondary',
                                        };
                                        $eligLabel = match($assessment->eligibility_status) {
                                            'eligible'     => 'Eligible',
                                            'not_eligible' => 'Not Eligible',
                                            default        => 'Pending',
                                        };
                                    @endphp
                                    <span class="badge {{ $eligBadge }}">{{ $eligLabel }}</span>
                                </td>
                                <td>
                                    @if($assessment->eligibility_status !== 'not_eligible')
                                        @php
                                            $apprBadge = match($assessment->approval_status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default    => 'bg-secondary',
                                            };
                                            $apprLabel = ucfirst($assessment->approval_status);
                                        @endphp
                                        <span class="badge {{ $apprBadge }}">{{ $apprLabel }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('field-assessments.show', $assessment) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="bi bi-eye"></i> <span class="btn-action-label">View</span>
                                    </a>
                                    @if($assessment->approval_status === 'pending')
                                        <a href="{{ route('field-assessments.edit', $assessment) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No field assessments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($assessments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $assessments->links() }}
        </div>
    @endif
@endsection
