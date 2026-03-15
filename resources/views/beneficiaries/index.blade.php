@extends('layouts.app')

@section('title', 'Beneficiary Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Beneficiaries</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h1 class="h3 mb-0">Beneficiary Management</h1>
            <p class="text-muted mb-0">Enrique B. Magalona &mdash; RSBSA &amp; FishR Registry</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff']))
            <a href="{{ route('beneficiaries.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i> Add New Beneficiary
            </a>
        @endif
    </div>

    {{-- Summary Cards --}}
    @php
        $totalAll       = \App\Models\Beneficiary::count();
        $totalFarmers   = \App\Models\Beneficiary::where('classification', 'Farmer')->count();
        $totalFisherfolk = \App\Models\Beneficiary::where('classification', 'Fisherfolk')->count();
        $totalBoth      = \App\Models\Beneficiary::where('classification', 'Both')->count();
    @endphp

    <div class="row g-3 mb-4 mt-2">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Beneficiaries</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalAll) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-tree-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Farmers</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalFarmers) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-water text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Fisherfolk</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalFisherfolk) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-purple bg-opacity-10 p-3 me-3" style="background-color: rgba(111, 66, 193, 0.1) !important;">
                        <i class="bi bi-intersect fs-4" style="color: #6f42c1;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Both</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalBoth) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('beneficiaries.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Name or Government ID"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                        <label for="classification" class="form-label">Classification</label>
                        <select name="classification" id="classification" class="form-select">
                            <option value="">All</option>
                            <option value="Farmer" {{ request('classification') === 'Farmer' ? 'selected' : '' }}>Farmer</option>
                            <option value="Fisherfolk" {{ request('classification') === 'Fisherfolk' ? 'selected' : '' }}>Fisherfolk</option>
                            <option value="Both" {{ request('classification') === 'Both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body pb-0">
            <p class="text-muted mb-2">{{ $beneficiaries->total() }} {{ Str::plural('beneficiary', $beneficiaries->total()) }} found</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Contact Number</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiaries as $beneficiary)
                            <tr>
                                <td class="text-muted">{{ $beneficiaries->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $beneficiary->full_name }}</td>
                                <td>{{ $beneficiary->barangay->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $classificationBadge = match($beneficiary->classification) {
                                            'Farmer'     => 'bg-primary',
                                            'Fisherfolk' => 'bg-info text-dark',
                                            'Both'       => '',
                                            default      => 'bg-secondary',
                                        };
                                    @endphp
                                    @if($beneficiary->classification === 'Both')
                                        <span class="badge" style="background-color: #6f42c1;">{{ $beneficiary->classification }}</span>
                                    @else
                                        <span class="badge {{ $classificationBadge }}">{{ $beneficiary->classification }}</span>
                                    @endif
                                </td>
                                <td>{{ $beneficiary->contact_number }}</td>
                                <td>
                                    <span class="badge {{ $beneficiary->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $beneficiary->status }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $beneficiary->registered_at->format('M d, Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('beneficiaries.show', $beneficiary) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="bi bi-eye"></i> <span class="btn-action-label">View</span>
                                    </a>
                                    <a href="{{ route('beneficiaries.edit', $beneficiary) }}"
                                       class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger" title="Delete"
                                            onclick="confirmAction('Confirm Deletion', 'Are you sure you want to delete {{ addslashes($beneficiary->full_name) }}? This action cannot be undone.', '{{ route('beneficiaries.destroy', $beneficiary) }}', 'DELETE')">
                                        <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
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

    {{-- Pagination --}}
    @if($beneficiaries->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $beneficiaries->links() }}
        </div>
    @endif

@endsection
