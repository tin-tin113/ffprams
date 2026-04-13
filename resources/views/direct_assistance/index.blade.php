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
                            <p class="text-muted small mb-1">Planned</p>
                            <h3 class="mb-0">{{ $stats['planned'] }}</h3>
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
                            <p class="text-muted small mb-1">Ready for Release</p>
                            <h3 class="mb-0">{{ $stats['ready_for_release'] }}</h3>
                        </div>
                        <i class="bi bi-bell text-primary" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Released Today</p>
                            <h3 class="mb-0">{{ $stats['released_today'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 24px;"></i>
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
            <form method="GET" action="{{ route('direct-assistance.index') }}" class="row g-2 align-items-end">
                <div class="col-xl-3 col-lg-3 col-md-6">
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
                <div class="col-xl-2 col-lg-2 col-md-6">
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
                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="ready_for_release" {{ request('status') == 'ready_for_release' ? 'selected' : '' }}>Ready for Release</option>
                        <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                        <option value="not_received" {{ request('status') == 'not_received' ? 'selected' : '' }}>Not Received</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label class="form-label">Sort</label>
                    <select class="form-select" name="sort">
                        <option value="created_desc" {{ request('sort', 'created_desc') === 'created_desc' ? 'selected' : '' }}>Date: Newest</option>
                        <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>Date: Oldest</option>
                        <option value="program_asc" {{ request('sort') === 'program_asc' ? 'selected' : '' }}>Program: A-Z</option>
                        <option value="program_desc" {{ request('sort') === 'program_desc' ? 'selected' : '' }}>Program: Z-A</option>
                        <option value="status_asc" {{ request('sort') === 'status_asc' ? 'selected' : '' }}>Status: A-Z</option>
                        <option value="status_desc" {{ request('sort') === 'status_desc' ? 'selected' : '' }}>Status: Z-A</option>
                    </select>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                    <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Clear
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
                                <td data-label="Status">
                                    @switch($assistance->status)
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
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $assistance->status)) }}</span>
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
                                    @if($assistance->status !== 'released')
                                        <a href="{{ route('direct-assistance.edit', $assistance) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    @endif

                                    @if(in_array($assistance->status, ['planned', 'not_received'], true))
                                        <form method="POST"
                                              action="{{ route('direct-assistance.mark-ready-for-release', $assistance) }}"
                                                class="direct-assistance-action-form"
                                              data-confirm-title="Set Ready for Release"
                                              data-confirm-message="Set this assistance to Ready for Release? If SMS automation is enabled, this will send an automatic SMS to the beneficiary.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-bell"></i> Ready for Release
                                            </button>
                                        </form>
                                    @endif

                                    @if($assistance->status === 'ready_for_release')
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
        @if($directAssistance->hasPages())
            <div class="card-footer bg-white">
                {{ $directAssistance->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
