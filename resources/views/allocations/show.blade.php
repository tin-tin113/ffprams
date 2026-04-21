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
        $isDirect = $allocation->isDirect();
        $releaseStatus = $allocation->release_status;
        $isEventPending = (bool) ($event && $event->status === 'Pending');
        $isFinalOutcome = in_array($releaseStatus, ['released', 'not_received'], true);

        $canSetReadyForRelease = $isDirect
            && ! $isEventPending
            && in_array($releaseStatus, ['planned', 'not_received'], true);

        $canMarkOutcome = $isDirect
            ? (! $isEventPending && $releaseStatus === 'ready_for_release')
            : (! $isEventPending && ! $isFinalOutcome);

        $statusBadge = match ($releaseStatus) {
            'ready_for_release' => 'bg-primary',
            'released' => 'bg-success',
            'not_received' => 'bg-danger',
            default => 'bg-warning text-dark',
        };

        $statusText = match ($releaseStatus) {
            'ready_for_release' => 'Ready for Release',
            'released' => 'Released',
            'not_received' => 'Not Received',
            default => 'Planned',
        };
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
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
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold mt-1">
                        <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Type</div>
                    <div class="fw-semibold mt-1">{{ $allocation->release_method === 'direct' ? 'Direct Assistance' : 'Event Allocation' }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Created At</div>
                    <div class="fw-semibold mt-1">{{ $allocation->created_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Released At</div>
                    <div class="fw-semibold mt-1">{{ $allocation->distributed_at ? $allocation->distributed_at->format('M d, Y h:i A') : ($releaseStatus === 'ready_for_release' ? 'Awaiting release confirmation' : 'Not yet released') }}</div>
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
                    @if($canSetReadyForRelease)
                        <form method="POST"
                              action="{{ route('allocations.mark-ready-for-release', $allocation) }}"
                              data-confirm-title="Set Ready for Release"
                              data-confirm-message="Set this allocation to Ready for Release? If SMS automation is enabled, this will send an automatic SMS to the beneficiary.">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-bell me-1"></i> Ready for Release
                            </button>
                        </form>
                    @endif

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
                    @endif

                    @if(! $canSetReadyForRelease && ! $canMarkOutcome)
                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-1">No Available Actions</div>
                            <div class="text-muted small">This allocation is finalized or cannot be updated while the event is Pending.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-paperclip me-1"></i> Allocation Documents
        </div>
        <div class="card-body">
            <form action="{{ route('allocations.attachments.store', $allocation) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="row g-3 align-items-end mb-3"
                  data-submit-spinner>
                @csrf
                <div class="col-md-4">
                    <label for="allocation_document_type" class="form-label">Document Type</label>
                    <input type="text"
                           class="form-control"
                           id="allocation_document_type"
                           name="document_type"
                           maxlength="100"
                           placeholder="e.g. Claim Stub, Receipt, Acknowledgment">
                </div>
                <div class="col-md-5">
                    <label for="allocation_attachment" class="form-label">Attachment File <span class="text-danger">*</span></label>
                    <input type="file"
                           class="form-control"
                           id="allocation_attachment"
                           name="attachment"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt"
                           required>
                    <div class="form-text">Supported files: PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, CSV, TXT. Maximum: 10 MB.</div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload Document
                    </button>
                </div>
            </form>

            @if($allocation->attachments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-responsive-cards">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Uploaded At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allocation->attachments as $attachment)
                                <tr>
                                    <td data-label="Type">{{ $attachment->document_type ?: 'Uncategorized' }}</td>
                                    <td class="text-break" data-label="File Name">{{ $attachment->original_name }}</td>
                                    <td data-label="Size">{{ number_format($attachment->size_bytes / 1024, 2) }} KB</td>
                                    <td data-label="Uploaded By">{{ $attachment->uploader?->name ?? 'System' }}</td>
                                    <td data-label="Uploaded At">{{ $attachment->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="text-end text-nowrap" data-label="Actions">
                                        <a href="{{ route('allocations.attachments.view', [$allocation, $attachment]) }}"
                                           class="btn btn-sm btn-outline-secondary me-1"
                                           target="_blank"
                                           rel="noopener">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('allocations.attachments.download', [$allocation, $attachment]) }}"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <form action="{{ route('allocations.attachments.destroy', [$allocation, $attachment]) }}"
                                              method="POST"
                                              class="d-inline"
                                              data-confirm-title="Delete Attachment"
                                              data-confirm-message="Delete {{ $attachment->original_name }} from this allocation record?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-inbox me-1"></i>
                    No allocation documents uploaded yet.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
