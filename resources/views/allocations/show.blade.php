@extends('layouts.app')

@section('title', 'Allocation Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('allocations.index') }}">Assistance Allocations</a></li>
    <li class="breadcrumb-item active">Allocation #{{ $allocation->id }}</li>
@endsection

@section('content')
<div class="container-fluid">
    @php
        $event = $allocation->distributionEvent;
        $isFinalOutcome = $allocation->distributed_at || $allocation->release_outcome === 'not_received';
        $canMarkOutcome = ! $isFinalOutcome && (! $event || $event->status !== 'Pending');

        $statusBadge = match (true) {
            (bool) $allocation->distributed_at => 'bg-success',
            $allocation->release_outcome === 'not_received' => 'bg-danger',
            default => 'bg-warning text-dark',
        };

        $statusText = match (true) {
            (bool) $allocation->distributed_at => 'Released',
            $allocation->release_outcome === 'not_received' => 'Not Received',
            default => 'Planned',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">Allocation Details</h1>
            <p class="text-muted mb-0">Review allocation record and release outcome.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            @if($event)
                <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-event me-1"></i> Open Event
                </a>
            @endif
            <a href="{{ route('allocations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Allocations
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold mt-1">
                        <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Type</div>
                    <div class="fw-semibold mt-1">{{ $allocation->release_method === 'direct' ? 'Direct Assistance' : 'Event Allocation' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Created At</div>
                    <div class="fw-semibold mt-1">{{ $allocation->created_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Released At</div>
                    <div class="fw-semibold mt-1">{{ $allocation->distributed_at ? $allocation->distributed_at->format('M d, Y h:i A') : 'Not yet released' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Beneficiary</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Full Name</div>
                            <div class="fw-semibold">{{ $allocation->beneficiary->full_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Classification</div>
                            <div class="fw-semibold">{{ $allocation->beneficiary->classification ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Barangay</div>
                            <div class="fw-semibold">{{ $allocation->beneficiary->barangay->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Contact Number</div>
                            <div class="fw-semibold">{{ $allocation->beneficiary->contact_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Allocation Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Program</div>
                            <div class="fw-semibold">{{ $allocation->programName->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Agency</div>
                            <div class="fw-semibold">{{ $allocation->resourceType->agency->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Resource Type</div>
                            <div class="fw-semibold">{{ $allocation->resourceType->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Allocated Value</div>
                            <div class="fw-semibold">{{ $allocation->getDisplayValue() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Assistance Purpose</div>
                            <div class="fw-semibold">{{ $allocation->assistancePurpose->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Distribution Event</div>
                            <div class="fw-semibold">
                                @if($event)
                                    Event #{{ $event->id }} ({{ $event->status }})
                                @else
                                    Direct Assistance
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Remarks</div>
                            <div class="fw-semibold">{{ $allocation->remarks ?: 'No remarks' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Actions</div>
                <div class="card-body d-grid gap-2">
                    @if($canMarkOutcome)
                        <form method="POST"
                              action="{{ route('allocations.markDistributed', $allocation) }}"
                              data-confirm-title="Confirm Release"
                              data-confirm-message="Mark this allocation as released? This will timestamp the release transaction.">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check2 me-1"></i> Mark Released
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('allocations.markNotReceived', $allocation) }}"
                              data-confirm-title="Confirm Not Received"
                              data-confirm-message="Mark this allocation as Not Received for this release schedule?">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-lg me-1"></i> Not Received
                            </button>
                        </form>
                    @else
                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-1">Finalized Outcome</div>
                            <div class="text-muted small">This allocation already has a release result or cannot be updated while the event is Pending.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
