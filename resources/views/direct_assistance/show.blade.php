@extends('layouts.app')

@section('title', 'Direct Assistance Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direct-assistance.index') }}">Direct Assistance</a></li>
    <li class="breadcrumb-item active">{{ $directAssistance->beneficiary->full_name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
            <p class="text-muted mb-0">Direct Assistance Record</p>
        </div>
        <div>
            @php($normalizedStatus = $directAssistance->normalized_status)
            @if(in_array($normalizedStatus, ['planned', 'not_received'], true))
                <form method="POST"
                      action="{{ route('direct-assistance.mark-ready-for-release', $directAssistance) }}"
                      class="d-inline"
                      data-confirm-title="Set Ready for Release"
                      data-confirm-message="Set this assistance to Ready for Release? If SMS automation is enabled, this will send an automatic SMS to the beneficiary.">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-bell me-1"></i> Ready for Release
                    </button>
                </form>
            @endif
            @if($normalizedStatus === 'ready_for_release')
                <form method="POST"
                      action="{{ route('direct-assistance.mark-released', $directAssistance) }}"
                      class="d-inline"
                      data-confirm-title="Mark as Released"
                      data-confirm-message="Mark this assistance as Released now?">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Mark Released
                    </button>
                </form>
                <form method="POST"
                      action="{{ route('direct-assistance.mark-not-received', $directAssistance) }}"
                      class="d-inline"
                      data-confirm-title="Mark as Not Received"
                      data-confirm-message="Mark this assistance as Not Received?">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i> Not Received
                    </button>
                </form>
            @endif
            <a href="{{ route('direct-assistance.edit', $directAssistance) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-info-circle me-1"></i> Assistance Details
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Program</dt>
                        <dd class="col-sm-9">
                            {{ $directAssistance->programName->name ?? 'N/A' }}
                            <small class="text-muted">({{ $directAssistance->programName->agency->name ?? 'N/A' }})</small>
                        </dd>

                        <dt class="col-sm-3">Resource Type</dt>
                        <dd class="col-sm-9">
                            {{ $directAssistance->resourceType->name ?? 'N/A' }}
                            <small class="text-muted">({{ $directAssistance->resourceType->unit ?? 'N/A' }})</small>
                        </dd>

                        <dt class="col-sm-3">Amount/Quantity</dt>
                        <dd class="col-sm-9">
                            <strong>{{ $directAssistance->getDisplayValue() }}</strong>
                        </dd>

                        <dt class="col-sm-3">Assistance Purpose</dt>
                        <dd class="col-sm-9">{{ $directAssistance->assistancePurpose->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Remarks</dt>
                        <dd class="col-sm-9">{{ $directAssistance->remarks ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Beneficiary Profile -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person me-1"></i> Beneficiary Information
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Full Name</dt>
                        <dd class="col-sm-9">{{ $directAssistance->beneficiary->full_name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Barangay</dt>
                        <dd class="col-sm-9">{{ $directAssistance->beneficiary->barangay->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Agency</dt>
                        <dd class="col-sm-9">{{ $directAssistance->beneficiary->agency->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Classification</dt>
                        <dd class="col-sm-9">{{ $directAssistance->beneficiary->classification ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Contact Number</dt>
                        <dd class="col-sm-9">{{ $directAssistance->beneficiary->contact_number ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if($directAssistance->beneficiary->status === 'Active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Distribution Tracking -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-truck me-1"></i> Distribution Information
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
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

                        <dt class="col-sm-3">Released At</dt>
                        <dd class="col-sm-9">
                            @if($directAssistance->distributed_at)
                                {{ $directAssistance->distributed_at->format('M d, Y H:i:s') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Released By</dt>
                        <dd class="col-sm-9">
                            @if($directAssistance->distributedBy)
                                {{ $directAssistance->distributedBy->name }}
                                <small class="text-muted">({{ $directAssistance->distributedBy->email }})</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Release Outcome</dt>
                        <dd class="col-sm-9">
                            @if($directAssistance->release_outcome)
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $directAssistance->release_outcome)) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-paperclip me-1"></i> Direct Assistance Documents
                </div>
                <div class="card-body">
                    <form action="{{ route('direct-assistance.attachments.store', $directAssistance) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="row g-3 align-items-end mb-3"
                          data-submit-spinner>
                        @csrf
                        <div class="col-md-4">
                            <label for="direct_assistance_document_type" class="form-label">Document Type</label>
                            <input type="text"
                                   class="form-control"
                                   id="direct_assistance_document_type"
                                   name="document_type"
                                   maxlength="100"
                                   placeholder="e.g. Receipt, Acknowledgment Slip">
                        </div>
                        <div class="col-md-5">
                            <label for="direct_assistance_attachment" class="form-label">Attachment File <span class="text-danger">*</span></label>
                            <input type="file"
                                   class="form-control"
                                   id="direct_assistance_attachment"
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

                    @if($directAssistance->attachments->isNotEmpty())
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
                                    @foreach($directAssistance->attachments as $attachment)
                                        <tr>
                                            <td data-label="Type">{{ $attachment->document_type ?: 'Uncategorized' }}</td>
                                            <td class="text-break" data-label="File Name">{{ $attachment->original_name }}</td>
                                            <td data-label="Size">{{ number_format($attachment->size_bytes / 1024, 2) }} KB</td>
                                            <td data-label="Uploaded By">{{ $attachment->uploader?->name ?? 'System' }}</td>
                                            <td data-label="Uploaded At">{{ $attachment->created_at->format('M d, Y h:i A') }}</td>
                                            <td class="text-end text-nowrap" data-label="Actions">
                                                <a href="{{ route('direct-assistance.attachments.view', [$directAssistance, $attachment]) }}"
                                                   class="btn btn-sm btn-outline-secondary me-1"
                                                   target="_blank"
                                                   rel="noopener">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('direct-assistance.attachments.download', [$directAssistance, $attachment]) }}"
                                                   class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                                <form action="{{ route('direct-assistance.attachments.destroy', [$directAssistance, $attachment]) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      data-confirm-title="Delete Attachment"
                                                      data-confirm-message="Delete {{ $attachment->original_name }} from this direct assistance record?">
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
                            No direct assistance documents uploaded yet.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Record Metadata -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Record Timeline
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6 text-muted small">Created On</dt>
                        <dd class="col-sm-6 small">{{ $directAssistance->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-6 text-muted small">Created By</dt>
                        <dd class="col-sm-6 small">{{ $directAssistance->createdBy->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-6 text-muted small">Last Updated</dt>
                        <dd class="col-sm-6 small">{{ $directAssistance->updated_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Distribution Event Link -->
            @if($directAssistance->distributionEvent)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-link-45deg me-1"></i> Linked Event
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Resource:</strong> {{ $directAssistance->distributionEvent->resourceType->name ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <strong>Barangay:</strong> {{ $directAssistance->distributionEvent->barangay->name ?? 'N/A' }}
                        </p>
                        <p class="mb-3">
                            <strong>Date:</strong> {{ $directAssistance->distributionEvent->distribution_date->format('M d, Y') }}
                        </p>
                        <a href="{{ route('distribution-events.show', $directAssistance->distributionEvent) }}" class="btn btn-sm btn-outline-primary w-100">
                            View Distribution Event
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('beneficiaries.show', $directAssistance->beneficiary) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-person-check me-1"></i> View Beneficiary Profile
                        </a>
                        <a href="{{ route('direct-assistance.edit', $directAssistance) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit Record
                        </a>
                        <a href="{{ route('direct-assistance.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
