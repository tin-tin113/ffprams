@extends('layouts.app')

@section('title', 'Barangay Direct Assistance Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">Barangay Analytics</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Barangay Direct Assistance Analytics</h1>
            <p class="text-muted mb-0">Summary of direct assistance by barangay</p>
        </div>
        <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Summary Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-bar-chart me-1"></i> Direct Assistance Summary by Barangay
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Barangay</th>
                            <th class="text-center">Total Records</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Distributed</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Distributed Today</th>
                            <th class="text-center">Distribution Rate</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analytics as $barangayAnalytic)
                            @php
                                $distributionRate = $barangayAnalytic['total'] > 0
                                    ? ($barangayAnalytic['distributed'] / $barangayAnalytic['total'] * 100)
                                    : 0;
                            @endphp
                            <tr>
                                <td data-label="Barangay">
                                    <strong>{{ $barangayAnalytic['barangay']->name }}</strong>
                                </td>
                                <td class="text-center" data-label="Total Records">
                                    <span class="badge bg-light text-dark">{{ $barangayAnalytic['total'] }}</span>
                                </td>
                                <td class="text-center" data-label="Pending">
                                    @if($barangayAnalytic['pending'] > 0)
                                        <span class="badge bg-warning text-dark">{{ $barangayAnalytic['pending'] }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Distributed">
                                    @if($barangayAnalytic['distributed'] > 0)
                                        <span class="badge bg-success">{{ $barangayAnalytic['distributed'] }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Completed">
                                    @if($barangayAnalytic['completed'] > 0)
                                        <span class="badge bg-info">{{ $barangayAnalytic['completed'] }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Distributed Today">
                                    @if($barangayAnalytic['distributed_today'] > 0)
                                        <span class="badge bg-primary">{{ $barangayAnalytic['distributed_today'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center" data-label="Distribution Rate">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ $distributionRate }}%;"
                                             aria-valuenow="{{ round($distributionRate) }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <small>{{ round($distributionRate) }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end" data-label="Actions">
                                    <a href="{{ route('direct-assistance.index', ['barangay_id' => $barangayAnalytic['barangay']->id]) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i> View Records
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 24px;"></i>
                                    <p class="text-muted mt-2">No direct assistance records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mt-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title text-muted">Total Barangays</h5>
                    <h2 class="mb-0">{{ count($analytics) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title text-muted">Total Records</h5>
                    <h2 class="mb-0">{{ array_sum(array_column($analytics, 'total')) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title text-muted">Total Distributed</h5>
                    <h2 class="mb-0">{{ array_sum(array_column($analytics, 'distributed')) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title text-muted">Overall Rate</h5>
                    @php
                        $totalRecords = array_sum(array_column($analytics, 'total'));
                        $totalDistributed = array_sum(array_column($analytics, 'distributed'));
                        $overallRate = $totalRecords > 0 ? ($totalDistributed / $totalRecords * 100) : 0;
                    @endphp
                    <h2 class="mb-0">{{ round($overallRate) }}%</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .progress {
        background-color: #e9ecef;
    }
</style>
@endsection
