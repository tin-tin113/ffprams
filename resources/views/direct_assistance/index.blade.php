@extends('layouts.app')

@section('title', 'Direct Assistance Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Direct Assistance</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Direct Assistance Management</h1>
            <p class="text-muted mb-0">Manage direct assistance records to beneficiaries</p>
        </div>
        <a href="{{ route('direct-assistance.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Direct Assistance
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Pending</p>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <i class="bi bi-clock-history text-warning" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Distributed Today</p>
                            <h3 class="mb-0">{{ $stats['distributed_today'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">This Month</p>
                            <h3 class="mb-0">{{ $stats['this_month'] }}</h3>
                        </div>
                        <i class="bi bi-calendar-check text-info" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <a href="{{ route('direct-assistance.barangay-analytics') }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Barangay Analytics</p>
                                <p class="text-primary mb-0">View Report</p>
                            </div>
                            <i class="bi bi-bar-chart text-primary" style="font-size: 24px;"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-funnel me-1"></i> Filters
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('direct-assistance.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Barangay</label>
                    <select class="form-select" name="barangay_id">
                        <option value="">All Barangays</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                {{ $barangay->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Agency</label>
                    <select class="form-select" name="agency_id">
                        <option value="">All Agencies</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                {{ $agency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Program</label>
                    <select class="form-select" name="program_id">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} ({{ $program->agency->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="recorded" {{ request('status') == 'recorded' ? 'selected' : '' }}>Recorded (Pending)</option>
                        <option value="distributed" {{ request('status') == 'distributed' ? 'selected' : '' }}>Distributed</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Beneficiary Search</label>
                    <input type="text" class="form-control" name="beneficiary_search" placeholder="Name or contact..." value="{{ request('beneficiary_search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Direct Assistance List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Direct Assistance Records
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
                            <th>Distributed</th>
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
                                <td data-label="Status">
                                    @switch($assistance->status)
                                        @case('recorded')
                                            <span class="badge bg-warning text-dark">Recorded</span>
                                            @break
                                        @case('distributed')
                                            <span class="badge bg-success">Distributed</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-info">Completed</span>
                                            @break
                                    @endswitch
                                </td>
                                <td data-label="Distributed">
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
                                    @if($assistance->status === 'recorded')
                                        <a href="{{ route('direct-assistance.edit', $assistance) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('direct-assistance.mark-distributed', $assistance) }}"
                                                class="direct-assistance-action-form"
                                              data-confirm-title="Mark as Distributed"
                                              data-confirm-message="Mark this assistance as distributed?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check2"></i> Distribute
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
        @if($directAssistance->hasPages())
            <div class="card-footer bg-white">
                {{ $directAssistance->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
