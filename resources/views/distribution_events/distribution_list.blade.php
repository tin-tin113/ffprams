@extends('layouts.app')

@section('title', 'Distribution List')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.show', $event) }}">Event #{{ $event->id }}</a></li>
    <li class="breadcrumb-item active">Distribution List</li>
@endsection

@push('styles')
<style>
    @media print {
        .top-navbar,
        .sidebar,
        .no-print,
        .btn,
        .alert {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h1 class="h4 mb-1">Printable Distribution List</h1>
            <p class="text-muted mb-0">Use this as release acknowledgment sheet (FAR-style local form).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-md-4"><strong>Agency:</strong> {{ $event->resourceType->agency->name ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Program:</strong> {{ $event->programName->name ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Barangay:</strong> {{ $event->barangay->name }}</div>
                <div class="col-md-4"><strong>Date:</strong> {{ $event->distribution_date->format('M d, Y') }}</div>
                <div class="col-md-4"><strong>Resource:</strong> {{ $event->resourceType->name }}</div>
                <div class="col-md-4"><strong>Type:</strong> {{ $event->isFinancial() ? 'Financial' : 'Physical' }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Distribution List</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Beneficiary Name</th>
                            <th>Barangay</th>
                            <th>{{ $event->isFinancial() ? 'Amount (PHP)' : 'Quantity' }}</th>
                            <th style="width: 160px;">Signature</th>
                            <th style="width: 100px;">Outcome</th>
                            <th style="width: 180px;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($event->allocations as $allocation)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $allocation->beneficiary->full_name }}</td>
                                <td>{{ $event->barangay->name }}</td>
                                <td class="text-center">
                                    @if($event->isFinancial())
                                        {{ number_format((float) $allocation->amount, 2) }}
                                    @else
                                        {{ number_format((float) $allocation->quantity, 2) }} {{ $event->resourceType->unit }}
                                    @endif
                                </td>
                                <td></td>
                                <td class="text-center">
                                    @if($allocation->release_outcome === 'not_received')
                                        Not Received
                                    @elseif($allocation->distributed_at)
                                        Received
                                    @else
                                        ______
                                    @endif
                                </td>
                                <td>{{ $allocation->remarks ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No allocations recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
