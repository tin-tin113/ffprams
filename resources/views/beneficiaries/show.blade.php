@extends('layouts.app')

@section('title', $beneficiary->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">{{ $beneficiary->full_name }}</li>
@endsection

@php
    $hasDarAgency = $beneficiary->agencies->contains(fn ($agency) => strtoupper((string) $agency->name) === 'DAR');
    $hasDarDetails = $hasDarAgency
        || filled($beneficiary->cloa_ep_number)
        || filled($beneficiary->arb_classification)
        || filled($beneficiary->landholding_description)
        || filled($beneficiary->land_area_awarded_hectares)
        || filled($beneficiary->ownership_scheme)
        || filled($beneficiary->barc_membership_status);

    $agencyFieldLabels = $agencyFieldLabels ?? [];
    $agencyNameById = $beneficiary->agencies
        ->mapWithKeys(fn ($agency) => [(string) $agency->id => (string) $agency->name])
        ->toArray();

    $rawCustomFieldValues = collect((array) ($beneficiary->custom_fields ?? []));
    $customFieldValues = $rawCustomFieldValues
        ->except('agency_dynamic')
        ->filter(fn ($value) => filled($value));

    $agencyDynamicCustomFieldValues = collect((array) ($rawCustomFieldValues->get('agency_dynamic', [])))
        ->filter(fn ($fields) => is_array($fields))
        ->flatMap(function ($fields, $agencyId) use ($agencyFieldLabels, $agencyNameById) {
            if (! is_array($fields)) {
                return collect();
            }

            return collect($fields)
                ->filter(fn ($value) => filled($value))
                ->map(function ($value, $fieldName) use ($agencyFieldLabels, $agencyNameById, $agencyId) {
                    $agencyIdString = (string) $agencyId;
                    $displayValue = is_array($value)
                        ? implode(', ', array_map('strval', array_values($value)))
                        : (string) $value;

                    return [
                        'agency_name' => $agencyNameById[$agencyIdString] ?? ('Agency #' . $agencyIdString),
                        'field_label' => $agencyFieldLabels[$agencyIdString][$fieldName] ?? Str::title(str_replace('_', ' ', (string) $fieldName)),
                        'value' => $displayValue,
                    ];
                });
        })
        ->values();

    $rawCustomFieldUnavailabilityReasons = collect((array) ($beneficiary->custom_field_unavailability_reasons ?? []));
    $customFieldUnavailabilityReasons = $rawCustomFieldUnavailabilityReasons
        ->except('agency_dynamic')
        ->filter(fn ($value) => filled($value));

    $agencyDynamicUnavailabilityReasons = collect((array) ($rawCustomFieldUnavailabilityReasons->get('agency_dynamic', [])))
        ->filter(fn ($fields) => is_array($fields))
        ->flatMap(function ($fields, $agencyId) use ($agencyFieldLabels, $agencyNameById) {
            if (! is_array($fields)) {
                return collect();
            }

            return collect($fields)
                ->filter(fn ($reason) => filled($reason))
                ->map(function ($reason, $fieldName) use ($agencyFieldLabels, $agencyNameById, $agencyId) {
                    $agencyIdString = (string) $agencyId;

                    return [
                        'agency_name' => $agencyNameById[$agencyIdString] ?? ('Agency #' . $agencyIdString),
                        'field_label' => $agencyFieldLabels[$agencyIdString][$fieldName] ?? Str::title(str_replace('_', ' ', (string) $fieldName)),
                        'reason' => (string) $reason,
                    ];
                });
        })
        ->values();

    $coreUnavailabilityReasons = collect([
        'RSBSA/DA Fields' => $beneficiary->rsbsa_unavailability_reason,
        'FishR/BFAR Fields' => $beneficiary->fishr_unavailability_reason,
        'CLOA/EP DAR Fields' => $beneficiary->cloa_ep_unavailability_reason,
    ])->filter(fn ($reason) => filled($reason));
@endphp

@section('content')
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2 gap-sm-0">
            <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">{{ $beneficiary->full_name }}</h1>
                <div class="d-flex flex-wrap gap-2">
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
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('beneficiaries.edit', $beneficiary) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <a href="#beneficiary-profile" class="btn btn-sm btn-outline-secondary">Profile</a>
                <a href="#supporting-documents" class="btn btn-sm btn-outline-secondary">Documents</a>
                <a href="#distribution-history" class="btn btn-sm btn-outline-secondary">Distribution History</a>
                <a href="#send-sms" class="btn btn-sm btn-outline-secondary">Send SMS</a>
                <a href="#sms-history" class="btn btn-sm btn-outline-secondary">SMS History</a>
            </div>
        </div>
    </div>

    {{-- Profile Card --}}
    <div id="beneficiary-profile" class="card border-0 shadow-sm mb-4" style="scroll-margin-top: 90px;">
        {{-- Personal Information --}}
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person me-1"></i> Personal Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">First Name</div>
                    <div class="fw-semibold">{{ $beneficiary->first_name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Middle Name</div>
                    <div class="fw-semibold">{{ $beneficiary->middle_name ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Last Name</div>
                    <div class="fw-semibold">{{ $beneficiary->last_name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Name Extension</div>
                    <div class="fw-semibold">{{ $beneficiary->name_suffix ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Sex</div>
                    <div class="fw-semibold">{{ $beneficiary->sex }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Date of Birth</div>
                    <div class="fw-semibold">{{ $beneficiary->date_of_birth ? $beneficiary->date_of_birth->format('M d, Y') : '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Civil Status</div>
                    <div class="fw-semibold">{{ $beneficiary->civil_status }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Highest Education</div>
                    <div class="fw-semibold">{{ $beneficiary->highest_education ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">ID Type</div>
                    <div class="fw-semibold">{{ $beneficiary->id_type ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Contact Number</div>
                    <div class="fw-semibold">{{ $beneficiary->contact_number }}</div>
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

        {{-- Address Information --}}
        <div class="card-header bg-white fw-semibold border-top">
            <i class="bi bi-geo-alt-fill me-1"></i> Address Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="text-muted small">Home Address</div>
                    <div class="fw-semibold">{{ $beneficiary->home_address ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="card-header bg-white fw-semibold border-top">
            <i class="bi bi-building me-1"></i> Registered Agencies
        </div>
        <div class="card-body">
            @if($beneficiary->agencies->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Agency</th>
                                <th>Identifier</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($beneficiary->agencies as $agency)
                                <tr>
                                    <td class="fw-semibold">{{ $agency->name }}</td>
                                    <td>
                                        @if($agency->pivot->identifier)
                                            <code class="small">{{ $agency->pivot->identifier }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $agency->pivot->registered_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-inbox me-1"></i>
                    No registered agencies.
                </p>
            @endif
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
                    <div class="col-md-4">
                        <div class="text-muted small">Organization / Cooperative Membership</div>
                        <div class="fw-semibold">{{ $beneficiary->organization_membership ?? '—' }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- DAR Details --}}
        @if($hasDarDetails)
            <div class="card-header bg-white fw-semibold border-top">
                <i class="bi bi-file-earmark-text me-1"></i> DAR/ARB Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">CLOA/EP Number</div>
                        <div class="fw-semibold">{{ $beneficiary->cloa_ep_number ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">ARB Classification</div>
                        <div class="fw-semibold">{{ $beneficiary->arb_classification ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Ownership Scheme</div>
                        <div class="fw-semibold">{{ $beneficiary->ownership_scheme ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Land Area Awarded</div>
                        <div class="fw-semibold">{{ $beneficiary->land_area_awarded_hectares ? $beneficiary->land_area_awarded_hectares . ' hectares' : '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">BARC Membership Status</div>
                        <div class="fw-semibold">{{ $beneficiary->barc_membership_status ?? '—' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="text-muted small">Landholding Description</div>
                        <div class="fw-semibold">{{ $beneficiary->landholding_description ?? '—' }}</div>
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
                    <div class="col-md-4">
                        <div class="text-muted small">Length of Residency (Months)</div>
                        <div class="fw-semibold">{{ $beneficiary->length_of_residency_months ?? '—' }}</div>
                    </div>
                    @if($beneficiary->has_fishing_vessel)
                        <div class="col-md-4">
                            <div class="text-muted small">Fishing Vessel Type</div>
                            <div class="fw-semibold">{{ $beneficiary->fishing_vessel_type ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Fishing Vessel Tonnage</div>
                            <div class="fw-semibold">{{ $beneficiary->fishing_vessel_tonnage ? $beneficiary->fishing_vessel_tonnage . ' tons' : '—' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Association --}}
        <div class="card-header bg-white fw-semibold border-top">
            <i class="bi bi-shield-check me-1"></i> Association Membership
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
            </div>
        </div>

        @if($customFieldValues->isNotEmpty() || $agencyDynamicCustomFieldValues->isNotEmpty())
            <div class="card-header bg-white fw-semibold border-top">
                <i class="bi bi-sliders me-1"></i> Additional Configured Fields
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($customFieldValues as $fieldGroup => $fieldValue)
                        <div class="col-md-4">
                            <div class="text-muted small">{{ Str::title(str_replace('_', ' ', $fieldGroup)) }}</div>
                            <div class="fw-semibold">
                                {{ is_array($fieldValue) ? collect($fieldValue)->filter(fn ($item) => filled($item))->implode(', ') : $fieldValue }}
                            </div>
                        </div>
                    @endforeach

                    @foreach($agencyDynamicCustomFieldValues as $entry)
                        <div class="col-md-4">
                            <div class="text-muted small">{{ $entry['agency_name'] }} &middot; {{ $entry['field_label'] }}</div>
                            <div class="fw-semibold">{{ $entry['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($coreUnavailabilityReasons->isNotEmpty() || $customFieldUnavailabilityReasons->isNotEmpty() || $agencyDynamicUnavailabilityReasons->isNotEmpty())
            <div class="card-header bg-white fw-semibold border-top">
                <i class="bi bi-info-circle me-1"></i> Field Unavailability Reasons
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($coreUnavailabilityReasons as $fieldLabel => $reason)
                        <div class="col-md-6">
                            <div class="text-muted small">{{ $fieldLabel }}</div>
                            <div class="fw-semibold">{{ $reason }}</div>
                        </div>
                    @endforeach

                    @foreach($customFieldUnavailabilityReasons as $fieldName => $reason)
                        <div class="col-md-6">
                            <div class="text-muted small">{{ Str::title(str_replace('_', ' ', $fieldName)) }}</div>
                            <div class="fw-semibold">{{ $reason }}</div>
                        </div>
                    @endforeach

                    @foreach($agencyDynamicUnavailabilityReasons as $entry)
                        <div class="col-md-6">
                            <div class="text-muted small">{{ $entry['agency_name'] }} &middot; {{ $entry['field_label'] }}</div>
                            <div class="fw-semibold">{{ $entry['reason'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Supporting Documents --}}
    <div id="supporting-documents" class="card border-0 shadow-sm mb-4" style="scroll-margin-top: 90px;">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-paperclip me-1"></i> Supporting Documents</span>
            <a href="{{ route('beneficiaries.attachments.create', $beneficiary) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Open Upload Page
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('beneficiaries.attachments.store', $beneficiary) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="row g-3 align-items-end mb-3"
                  data-submit-spinner>
                @csrf
                <div class="col-md-4">
                    <label for="document_type" class="form-label">Document Type</label>
                    <input type="text"
                           class="form-control @error('document_type') is-invalid @enderror"
                           id="document_type"
                           name="document_type"
                           maxlength="100"
                           placeholder="e.g. Valid ID, Barangay Certificate"
                           value="{{ old('document_type') }}">
                    @error('document_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label for="attachment" class="form-label">Attachment File <span class="text-danger">*</span></label>
                    <input type="file"
                           class="form-control @error('attachment') is-invalid @enderror"
                           id="attachment"
                           name="attachment"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required>
                    @error('attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Accepted: PDF, JPG, JPEG, PNG. Maximum: 5 MB.</div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload Document
                    </button>
                </div>
            </form>

            @if($beneficiary->attachments->isNotEmpty())
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
                            @foreach($beneficiary->attachments as $attachment)
                                <tr>
                                    <td data-label="Type">{{ $attachment->document_type ?: 'Uncategorized' }}</td>
                                    <td class="text-break" data-label="File Name">{{ $attachment->original_name }}</td>
                                    <td data-label="Size">{{ number_format($attachment->size_bytes / 1024, 2) }} KB</td>
                                    <td data-label="Uploaded By">{{ $attachment->uploader?->name ?? 'System' }}</td>
                                    <td data-label="Uploaded At">{{ $attachment->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="text-end text-nowrap" data-label="Actions">
                                        <a href="{{ route('beneficiaries.attachments.view', [$beneficiary, $attachment]) }}"
                                           class="btn btn-sm btn-outline-secondary me-1"
                                           target="_blank"
                                           rel="noopener">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('beneficiaries.attachments.download', [$beneficiary, $attachment]) }}"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <form action="{{ route('beneficiaries.attachments.destroy', [$beneficiary, $attachment]) }}"
                                              method="POST"
                                              class="d-inline"
                                              data-confirm-title="Delete Attachment"
                                              data-confirm-message="Delete {{ $attachment->original_name }} from this beneficiary record?">
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
                    No supporting documents uploaded yet.
                </p>
            @endif
        </div>
    </div>

    {{-- Distribution History --}}
    <div id="distribution-history" class="card border-0 shadow-sm mb-4" style="scroll-margin-top: 90px;">
        @php
            // Merge and sort allocations and direct assistance by most recent date first
            $allDistributions = collect();

            // Add allocations with method type indicator
            foreach($beneficiary->allocations as $allocation) {
                $allDistributions->push((object)[
                    'type' => 'allocation',
                    'method' => $allocation->isDirect() ? 'Direct' : 'Event-Based',
                    'methodBadge' => $allocation->isDirect() ? 'Direct' : 'Event-Based',
                    'badgeClass' => $allocation->isDirect() ? 'bg-info text-dark' : 'bg-secondary',
                    'data' => $allocation,
                    'sortDate' => $allocation->distributionEvent?->distribution_date ?? $allocation->created_at,
                ]);
            }

            // Add direct assistance with method type indicator
            foreach($beneficiary->directAssistance as $assistance) {
                $allDistributions->push((object)[
                    'type' => 'directAssistance',
                    'method' => 'Direct Assistance',
                    'methodBadge' => 'Direct Assistance',
                    'badgeClass' => 'bg-warning text-dark',
                    'data' => $assistance,
                    'sortDate' => $assistance->distributed_at ?? $assistance->created_at,
                ]);
            }

            // Sort by most recent date first (descending)
            $allDistributions = $allDistributions->sortByDesc('sortDate')->values();
        @endphp

        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-semibold">
                    <i class="bi bi-box-seam me-1"></i> Distribution History
                    @if($allDistributions->isNotEmpty())
                        <span class="badge bg-light text-dark ms-2">{{ $allDistributions->count() }} Record{{ $allDistributions->count() !== 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <small class="text-muted">Showing all allocations, events, and direct assistance</small>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Method</th>
                            <th>Program</th>
                            <th>Resource Type</th>
                            <th>Source Agency</th>
                            <th>Value</th>
                            <th>Distribution Date</th>
                            <th>Status</th>
                            <th>Distributed At</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allDistributions as $distribution)
                            <tr>
                                <td data-label="Method">
                                    <span class="badge {{ $distribution->badgeClass }}">{{ $distribution->methodBadge }}</span>
                                </td>

                                @if($distribution->type === 'allocation')
                                    @php
                                        $allocation = $distribution->data;
                                    @endphp
                                    <td class="fw-semibold" data-label="Program">{{ $allocation->programName->name ?? $allocation->distributionEvent->programName->name ?? '—' }}</td>
                                    <td data-label="Resource Type">{{ $allocation->resourceType->name ?? $allocation->distributionEvent->resourceType->name ?? '—' }}</td>
                                    <td data-label="Source Agency">{{ $allocation->resourceType->agency->name ?? $allocation->distributionEvent->resourceType->agency->name ?? '—' }}</td>
                                    <td data-label="Value">{{ $allocation->getDisplayValue() }}</td>
                                    <td class="text-muted small" data-label="Distribution Date">{{ $allocation->distributionEvent?->distribution_date?->format('M d, Y') ?? $allocation->created_at?->format('M d, Y') ?? '—' }}</td>
                                    <td data-label="Status">
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
                                        <span class="badge {{ $statusBadge ?? 'bg-secondary' }}">{{ $eventStatus ?? '—' }}</span>
                                    </td>
                                    <td class="text-muted small" data-label="Distributed At">{{ $allocation->distributed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td data-label="Remarks">{{ $allocation->remarks ?? '—' }}</td>
                                @else
                                    @php
                                        $assistance = $distribution->data;
                                    @endphp
                                    <td class="fw-semibold" data-label="Program">{{ $assistance->programName->name ?? '—' }}</td>
                                    <td data-label="Resource Type">{{ $assistance->resourceType->name ?? '—' }}</td>
                                    <td data-label="Source Agency">{{ $assistance->programName->agency->name ?? '—' }}</td>
                                    <td data-label="Value">{{ $assistance->getDisplayValue() }}</td>
                                    <td class="text-muted small" data-label="Distribution Date">{{ $assistance->created_at?->format('M d, Y') ?? '—' }}</td>
                                    <td data-label="Status">
                                        @php
                                            $normalizedStatus = $assistance->normalized_status;
                                        @endphp
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
                                                <span class="badge bg-secondary">{{ $assistance->status_label }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-muted small" data-label="Distributed At">{{ $assistance->distributed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td data-label="Remarks">
                                        {{ $assistance->remarks ?? '—' }}
                                        @if($assistance->distributionEvent)
                                            <br><small class="text-muted">Linked to event</small>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distributions recorded yet.
                                </td>
                            </tr>
                        @endforelse
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
    <div id="send-sms" class="card border-0 shadow-sm mb-4" style="scroll-margin-top: 90px;">
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
    <div id="sms-history" class="card border-0 shadow-sm mb-4" style="scroll-margin-top: 90px;">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-chat-dots me-1"></i> SMS History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
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
                                <td class="small text-break" data-label="Message">{{ $sms->message }}</td>
                                <td data-label="Status">
                                    <span class="badge {{ $sms->status === 'sent' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($sms->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small" data-label="Sent At">{{ $sms->sent_at->format('M d, Y h:i A') }}</td>
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
