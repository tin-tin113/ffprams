@extends('layouts.app')

@section('title', 'Edit Direct Assistance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.show', $directAssistance) }}">{{ $directAssistance->beneficiary->full_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">Update direct assistance record for {{ $directAssistance->beneficiary->full_name }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @include('direct_assistance.partials.form')
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-header bg-white fw-semibold">
                    Record Details
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6 text-muted small">Created On</dt>
                        <dd class="col-sm-6">{{ $directAssistance->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-6 text-muted small">Created By</dt>
                        <dd class="col-sm-6">{{ $directAssistance->createdBy->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-6 text-muted small">Status</dt>
                        <dd class="col-sm-6">
                            @php($normalizedStatus = $directAssistance->normalized_status)
                            @switch($normalizedStatus)
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
                                    <span class="badge bg-secondary">{{ $directAssistance->status_label }}</span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-6 text-muted small">Value</dt>
                        <dd class="col-sm-6">{{ $directAssistance->getDisplayValue() }}</dd>

                        @if($directAssistance->distributionEvent)
                            <dt class="col-sm-6 text-muted small">Linked Event</dt>
                            <dd class="col-sm-6">
                                <a href="{{ route('distribution-events.show', $directAssistance->distributionEvent) }}" class="text-decoration-none">
                                    {{ $directAssistance->distributionEvent->resourceType->name ?? 'N/A' }}
                                </a>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($normalizedStatus !== 'released')
                <div class="card border-0 shadow-sm bg-light mt-3">
                    <div class="card-header bg-white fw-semibold text-danger">
                        Danger Zone
                    </div>
                    <div class="card-body">
                        <form method="POST"
                              action="{{ route('direct-assistance.destroy', $directAssistance) }}"
                              data-confirm-title="Confirm Delete"
                              data-confirm-message="Delete this direct assistance record? This action cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash me-1"></i> Delete Record
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
