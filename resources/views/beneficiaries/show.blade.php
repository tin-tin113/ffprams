@extends('layouts.app')

@section('title', $beneficiary->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">{{ $beneficiary->full_name }}</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">{{ $beneficiary->full_name }}</h1>
                <div class="d-flex gap-2">
                    @php
                        $classBadge = match($beneficiary->classification) {
                            'Farmer'     => 'bg-primary',
                            'Fisherfolk' => 'bg-info text-dark',
                            'Both'       => '',
                            default      => 'bg-secondary',
                        };
                    @endphp
                    @if($beneficiary->classification === 'Both')
                        <span class="badge" style="background-color: #6f42c1;">{{ $beneficiary->classification }}</span>
                    @else
                        <span class="badge {{ $classBadge }}">{{ $beneficiary->classification }}</span>
                    @endif
                    <span class="badge {{ $beneficiary->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                        {{ $beneficiary->status }}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('beneficiaries.edit', $beneficiary) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="card border-0 shadow-sm mb-4">
        {{-- Personal Information --}}
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person me-1"></i> Personal Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Civil Status</div>
                    <div class="fw-semibold">{{ $beneficiary->civil_status }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Highest Education</div>
                    <div class="fw-semibold">{{ $beneficiary->highest_education }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Number of Dependents</div>
                    <div class="fw-semibold">{{ $beneficiary->number_of_dependents }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Contact Number</div>
                    <div class="fw-semibold">{{ $beneficiary->contact_number }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Main Source of Income</div>
                    <div class="fw-semibold">{{ $beneficiary->main_income_source }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Household Size</div>
                    <div class="fw-semibold">{{ $beneficiary->household_size }}</div>
                </div>
            </div>
        </div>

        {{-- Registration Details --}}
        <div class="card-header bg-white fw-semibold border-top">
            <i class="bi bi-geo-alt me-1"></i> Registration Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Barangay</div>
                    <div class="fw-semibold">{{ $beneficiary->barangay->name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Government ID Type</div>
                    <div class="fw-semibold">{{ $beneficiary->id_type }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Government ID Number</div>
                    <div class="fw-semibold">{{ $beneficiary->government_id }}</div>
                </div>
                @if($beneficiary->isFarmer())
                    <div class="col-md-4">
                        <div class="text-muted small">RSBSA Number</div>
                        <div class="fw-semibold">{{ $beneficiary->rsbsa_number ?? '—' }}</div>
                    </div>
                @endif
                @if($beneficiary->isFisherfolk())
                    <div class="col-md-4">
                        <div class="text-muted small">FishR Number</div>
                        <div class="fw-semibold">{{ $beneficiary->fishr_number ?? '—' }}</div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="text-muted small">Registered Date</div>
                    <div class="fw-semibold">{{ $beneficiary->registered_at->format('M d, Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div>
                        <span class="badge {{ $beneficiary->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                            {{ $beneficiary->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Farmer Details --}}
        @if($beneficiary->isFarmer())
            <div class="card-header bg-white fw-semibold border-top">
                <i class="bi bi-tree me-1"></i> Farmer Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Farm Ownership</div>
                        <div class="fw-semibold">{{ $beneficiary->farm_ownership ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Farm Size</div>
                        <div class="fw-semibold">{{ $beneficiary->farm_size_hectares ? $beneficiary->farm_size_hectares . ' hectares' : '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Primary Commodity</div>
                        <div class="fw-semibold">{{ $beneficiary->primary_commodity ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Farm Type</div>
                        <div class="fw-semibold">{{ $beneficiary->farm_type ?? '—' }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Fisherfolk Details --}}
        @if($beneficiary->isFisherfolk())
            <div class="card-header bg-white fw-semibold border-top">
                <i class="bi bi-water me-1"></i> Fisherfolk Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Fisherfolk Type</div>
                        <div class="fw-semibold">{{ $beneficiary->fisherfolk_type ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Main Fishing Gear</div>
                        <div class="fw-semibold">{{ $beneficiary->main_fishing_gear ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Has Fishing Vessel</div>
                        <div class="fw-semibold">
                            @if($beneficiary->has_fishing_vessel)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Association & Emergency Contact --}}
        <div class="card-header bg-white fw-semibold border-top">
            <i class="bi bi-shield-check me-1"></i> Association &amp; Emergency Contact
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Association Member</div>
                    <div class="fw-semibold">
                        @if($beneficiary->association_member)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>
                @if($beneficiary->association_member && $beneficiary->association_name)
                    <div class="col-md-8">
                        <div class="text-muted small">Association Name</div>
                        <div class="fw-semibold">{{ $beneficiary->association_name }}</div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="text-muted small">Emergency Contact Name</div>
                    <div class="fw-semibold">{{ $beneficiary->emergency_contact_name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Emergency Contact Number</div>
                    <div class="fw-semibold">{{ $beneficiary->emergency_contact_number }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribution History --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-box-seam me-1"></i> Distribution History (Event-Based & Direct Assistance)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Method</th>
                            <th>Program</th>
                            <th>Resource Type</th>
                            <th>Source Agency</th>
                            <th>Value</th>
                            <th>Distribution Date</th>
                            <th>Event Status</th>
                            <th>Distributed At</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiary->allocations as $allocation)
                            <tr>
                                <td>
                                    @if($allocation->isDirect())
                                        <span class="badge bg-info text-dark">Direct</span>
                                    @else
                                        <span class="badge bg-secondary">Event</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $allocation->programName->name ?? $allocation->distributionEvent->programName->name ?? '—' }}</td>
                                <td>{{ $allocation->resourceType->name ?? $allocation->distributionEvent->resourceType->name ?? '—' }}</td>
                                <td>{{ $allocation->resourceType->agency->name ?? $allocation->distributionEvent->resourceType->agency->name ?? '—' }}</td>
                                <td>{{ $allocation->getDisplayValue() }}</td>
                                <td class="text-muted small">{{ $allocation->distributionEvent?->distribution_date?->format('M d, Y') ?? $allocation->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    @php
                                        $eventStatus = $allocation->distributionEvent?->status ?? ($allocation->distributed_at ? 'Released' : 'Planned');
                                        $statusBadge = match($eventStatus) {
                                            'Pending'   => 'bg-primary',
                                            'Ongoing'   => 'bg-warning text-dark',
                                            'Completed' => 'bg-success',
                                            'Released'  => 'bg-success',
                                            'Planned'   => 'bg-secondary',
                                            default     => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ $eventStatus ?: '—' }}</span>
                                </td>
                                <td class="text-muted small">{{ $allocation->distributed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                <td>{{ $allocation->remarks ?? '—' }}</td>
                            </tr>
                        @endforelse

                        {{-- Direct Assistance Records --}}
                        @forelse($beneficiary->directAssistance as $assistance)
                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark">Direct Assistance</span>
                                </td>
                                <td class="fw-semibold">{{ $assistance->programName->name ?? '—' }}</td>
                                <td>{{ $assistance->resourceType->name ?? '—' }}</td>
                                <td>{{ $assistance->programName->agency->name ?? '—' }}</td>
                                <td>{{ $assistance->getDisplayValue() }}</td>
                                <td class="text-muted small">{{ $assistance->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
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
                                <td class="text-muted small">{{ $assistance->distributed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                <td>
                                    {{ $assistance->remarks ?? '—' }}
                                    @if($assistance->distributionEvent)
                                        <br><small class="text-muted">Linked to event</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        {{-- Empty State --}}
                        @if($beneficiary->allocations->isEmpty() && $beneficiary->directAssistance->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distributions recorded yet.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @if($beneficiary->directAssistance->isNotEmpty())
            <div class="card-footer bg-white">
                <a href="{{ route('direct-assistance.index', ['beneficiary_search' => $beneficiary->full_name]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right me-1"></i> View All Direct Assistance
                </a>
            </div>
        @endif
    </div>

    {{-- Send SMS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-send me-1"></i> Send SMS
        </div>
        <div class="card-body">
            @if($beneficiary->contact_number)
                <form action="{{ route('beneficiaries.sendSms', $beneficiary) }}" method="POST" data-submit-spinner>
                    @csrf
                    <div class="mb-3">
                        <label for="sms-message" class="form-label">Message</label>
                        <textarea name="message" id="sms-message" class="form-control @error('message') is-invalid @enderror"
                                  rows="3" maxlength="300" placeholder="Type your message here..." data-char-counter>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max 300 characters. Will be sent to {{ $beneficiary->contact_number }}</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send SMS
                    </button>
                </form>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    No contact number on file. Please update the beneficiary profile to send SMS.
                </p>
            @endif
        </div>
    </div>

    {{-- SMS History --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-chat-dots me-1"></i> SMS History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiary->smsLogs as $sms)
                            <tr>
                                <td class="small" style="max-width: 500px;">{{ $sms->message }}</td>
                                <td>
                                    <span class="badge {{ $sms->status === 'sent' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($sms->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $sms->sent_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No SMS logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
