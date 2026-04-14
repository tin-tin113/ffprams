@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-file-text"></i> {{ $programName->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <small>
                            <span class="badge bg-secondary">{{ $programName->agency->name }}</span>
                            <span class="badge bg-info">{{ $programName->classification }}</span>
                        </small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.settings.program-names.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            @if($programName->description)
            <p class="text-muted mb-0">{{ $programName->description }}</p>
            @endif
        </div>
    </div>

    {{-- Legal Requirements Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Legal Requirements / Supporting Documents
                    </h5>
                </div>
                <div class="card-body">
                    @if($programName->legalRequirements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Size</th>
                                    <th>Uploaded Date</th>
                                    <th>Remarks</th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programName->legalRequirements as $req)
                                <tr>
                                    <td>
                                        @if($req->document_type)
                                        <span class="badge bg-light text-dark">{{ $req->document_type }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($req->original_name, 40) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $req->uploader?->name ?? 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ number_format($req->size_bytes / 1024, 1) }} KB</small>
                                    </td>
                                    <td>
                                        <small>{{ $req->created_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $req->remarks ? Str::limit($req->remarks, 30) : '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.settings.program-names.legal-requirements.download', [$programName, $req]) }}"
                                           class="btn btn-sm btn-outline-info"
                                           title="Download document">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger delete-req"
                                                data-id="{{ $req->id }}"
                                                data-program-id="{{ $programName->id }}"
                                                title="Delete document">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> No legal requirement documents uploaded yet.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Counters --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-primary">{{ $totalEvents }}</div>
                    <p class="text-muted mb-0">Total Distribution Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-success">₱{{ number_format($totalAllocatedAmount, 2) }}</div>
                    <p class="text-muted mb-0">Total Allocated Amount</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="display-4 text-info">{{ $totalBeneficiaries }}</div>
                    <p class="text-muted mb-0">Total Beneficiaries</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribution Events Accordion --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event"></i> Distribution Events ({{ $events->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($events->count() > 0)
                    <div class="accordion" id="eventsAccordion">
                        @foreach($events as $index => $event)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#event{{ $event->id }}" aria-expanded="false" aria-controls="event{{ $event->id }}">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <span class="badge bg-secondary">{{ $event->event_date?->format('Y-m-d') ?? 'N/A' }}</span>
                                        <span><strong>{{ $event->barangay?->name ?? 'N/A' }}</strong></span>
                                        <span class="text-muted">{{ $event->resourceType?->name ?? 'N/A' }}</span>
                                        <span class="ms-auto badge bg-light text-dark">{{ $event->allocations->count() }} allocations</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="event{{ $event->id }}" class="accordion-collapse collapse" data-bs-parent="#eventsAccordion">
                                <div class="accordion-body">
                                    {{-- Event Metadata --}}
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <small class="text-muted">Event Date</small>
                                            <p class="mb-0"><strong>{{ $event->event_date?->format('Y-m-d') ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Barangay</small>
                                            <p class="mb-0"><strong>{{ $event->barangay?->name ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Resource Type</small>
                                            <p class="mb-0"><strong>{{ $event->resourceType?->name ?? 'N/A' }}</strong></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Quantity</small>
                                            <p class="mb-0"><strong>{{ $event->total_quantity ?? '-' }}</strong></p>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    {{-- Associated Allocations --}}
                                    @if($event->allocations->count() > 0)
                                    <h6 class="mb-3">
                                        <i class="bi bi-box-seam"></i> Associated Allocations ({{ $event->allocations->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Beneficiary</th>
                                                    <th>Resource Type</th>
                                                    <th class="text-end">Quantity</th>
                                                    <th class="text-end">Amount (₱)</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($event->allocations as $allocation)
                                                <tr>
                                                    <td>
                                                        <small>{{ $allocation->beneficiary?->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $allocation->resourceType?->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        <small>{{ $allocation->quantity }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        <small>{{ number_format($allocation->amount, 2) }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ $allocation->created_at?->format('Y-m-d') ?? 'N/A' }}</small>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">No allocations for this event.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No distribution events found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Beneficiaries Table --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people"></i> Beneficiaries ({{ $beneficiaries->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($beneficiaries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Classification</th>
                                    <th class="text-end">Allocations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($beneficiaries->take(10) as $beneficiary)
                                <tr>
                                    <td>
                                        <small>{{ $beneficiary->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            @if(isset($beneficiary->classification))
                                            <span class="badge bg-light text-dark">{{ $beneficiary->classification }}</span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <small>
                                            {{ $allocations->where('beneficiary_id', $beneficiary->id)->count() }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($beneficiaries->count() > 10)
                    <small class="text-muted d-block mt-2">Showing 10 of {{ $beneficiaries->count() }} beneficiaries</small>
                    @endif
                    @else
                    <p class="text-muted mb-0">No beneficiaries found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .display-4 {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .card {
        border-radius: 0.5rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Delete legal requirement
    document.querySelectorAll('.delete-req').forEach(btn => {
        btn.addEventListener('click', function() {
            const reqId = this.dataset.id;
            const programId = this.dataset.programId;

            if (!confirm('Delete this legal requirement document? This action cannot be undone.')) {
                return;
            }

            fetch(`/admin/settings/program-names/${programId}/legal-requirements/${reqId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete document');
                }
            })
            .catch(error => {
                alert('An error occurred');
            });
        });
    });
});
</script>

@endsection
