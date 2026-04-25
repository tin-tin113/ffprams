@extends('layouts.app')

@section('title', $beneficiary->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">{{ $beneficiary->full_name }}</li>
@endsection

@php
    $darAgency = $beneficiary->agencies->first(fn ($a) => strtoupper((string) $a->name) === 'DAR');
    $hasDarAgency = !is_null($darAgency);
    $darDynamicAll = (array) data_get($beneficiary->custom_fields, 'agency_dynamic', []);
    $darDynamicAgencyKey = (string) ($darAgency->id ?? (array_key_first($darDynamicAll) ?? ''));
    $darDynamicData = $darDynamicAgencyKey !== ''
        ? data_get($beneficiary->custom_fields, 'agency_dynamic.' . $darDynamicAgencyKey, [])
        : [];

    $hasDarDetails = $hasDarAgency
        || filled(data_get($darDynamicData, 'cloa_ep_number'))
        || filled(data_get($darDynamicData, 'arb_classification'))
        || filled(data_get($darDynamicData, 'landholding_description'))
        || filled(data_get($darDynamicData, 'land_area_awarded_hectares'))
        || filled(data_get($darDynamicData, 'ownership_scheme'))
        || filled(data_get($darDynamicData, 'barc_membership_status'));

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
        'CLOA/EP DAR Fields' => $darDynamicAgencyKey !== ''
            ? data_get($beneficiary->custom_field_unavailability_reasons, 'agency_dynamic.' . $darDynamicAgencyKey . '.cloa_ep_number')
            : null,
    ])->filter(fn ($reason) => filled($reason));
@endphp

@push('styles')
<style>
    /* Premium Dashboard Styles */
    .beneficiary-header {
        background: #fff;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    .stat-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .nav-pills.program-tabs {
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f1f5f9;
        border-radius: 0.75rem;
        display: inline-flex;
    }
    .nav-pills.program-tabs .nav-link {
        border: none;
        border-radius: 0.5rem;
        padding: 0.6rem 1.25rem;
        color: #64748b;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: transparent;
    }
    .nav-pills.program-tabs .nav-link:hover {
        background: rgba(255,255,255,0.5);
        color: #0d6efd;
    }
    .nav-pills.program-tabs .nav-link.active {
        background: #fff;
        color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .tab-content {
        padding-top: 1.5rem;
    }
    .card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    .badge-soft-primary { background: #e0e7ff; color: #4338ca; }
    .badge-soft-success { background: #dcfce7; color: #15803d; }
    .badge-soft-info { background: #e0f2fe; color: #0369a1; }
    .badge-soft-warning { background: #fef3c7; color: #92400e; }
    .badge-soft-danger { background: #fee2e2; color: #b91c1c; }
    .badge-soft-purple { background: #f3e8ff; color: #7e22ce; }

    /* SMS Thread Styles */
    .sms-thread {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 1rem;
    }
    .sms-message {
        max-width: 85%;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
        position: relative;
        font-size: 0.9rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .sms-message.sent {
        align-self: flex-end;
        background: #0d6efd;
        color: white;
        border-bottom-right-radius: 0.25rem;
    }
    .sms-message.failed {
        align-self: flex-end;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-bottom-right-radius: 0.25rem;
    }
    .sms-meta {
        font-size: 0.7rem;
        margin-top: 0.4rem;
        opacity: 0.8;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .sent .sms-meta { color: rgba(255,255,255,0.9); }

    .form-control, .form-select {
        color: #1e293b !important;
    }

    .avatar-circle {
        width: 80px;
        height: 80px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        font-size: 2rem;
        font-weight: 700;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        text-transform: uppercase;
    }
    .header-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .beneficiary-header {
        background: linear-gradient(to right, #ffffff, #f8fafc);
        border: 1px solid #edf2f7;
    }

    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    @php
        $initials = collect(explode(' ', $beneficiary->full_name))->map(fn($n) => $n[0] ?? '')->take(2)->join('');
        $bgColors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-purple'];
        $bgColor = $bgColors[$beneficiary->id % count($bgColors)];
    @endphp
    <div class="beneficiary-header shadow-sm rounded-4 p-4 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <div class="header-content">
                    <div class="avatar-circle {{ $bgColor }} flex-shrink-0">
                        {{ $initials }}
                    </div>
                    <div>
                        <h2 class="mb-2 fw-bold text-dark">{{ $beneficiary->full_name }}</h2>
                        <div class="d-flex flex-wrap gap-2">
                            @php
                                $classBadge = match($beneficiary->classification) {
                                    'Farmer'     => 'badge-soft-primary',
                                    'Fisherfolk' => 'badge-soft-info',
                                    'Farmer & Fisherfolk' => 'badge-soft-purple',
                                    default      => 'bg-soft-secondary',
                                };
                            @endphp
                            <span class="badge {{ $classBadge }} px-3 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-person-badge me-1"></i> {{ $beneficiary->classification }}
                            </span>
                            <span class="badge {{ $beneficiary->status === 'Active' ? 'badge-soft-success' : 'badge-soft-danger' }} px-3 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ $beneficiary->status }}
                            </span>
                            @if($beneficiary->barangay)
                                <span class="badge bg-white text-muted border px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-geo-alt me-1"></i> Barangay {{ $beneficiary->barangay->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="d-flex justify-content-md-end gap-3 no-print">
                    <a href="{{ route('beneficiaries.index') }}" class="btn btn-white border rounded-pill px-4 shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Registry
                    </a>
                    <a href="{{ route('beneficiaries.edit', $beneficiary) }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="d-flex justify-content-center justify-content-md-start">
        <ul class="nav nav-pills program-tabs mb-4 p-1 no-print" id="beneficiaryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-person-lines-fill me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="distributions-tab" data-bs-toggle="tab" data-bs-target="#distributions" type="button" role="tab">
                    <i class="bi bi-box-seam me-2"></i>Distributions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                    <i class="bi bi-file-earmark-text me-2"></i>Documents
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2" id="communications-tab" data-bs-toggle="tab" data-bs-target="#communications" type="button" role="tab">
                    <i class="bi bi-chat-left-dots me-2"></i>Communications
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="beneficiaryTabsContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
                {{-- Main Info Column --}}
                <div class="col-lg-8">
                    {{-- Personal Info Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded text-primary">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Personal & Contact Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <div class="info-label">First Name</div>
                                    <div class="info-value">{{ $beneficiary->first_name }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Middle Name</div>
                                    <div class="info-value">{{ $beneficiary->middle_name ?: '—' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Last Name</div>
                                    <div class="info-value">{{ $beneficiary->last_name }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Extension</div>
                                    <div class="info-value">{{ $beneficiary->name_suffix ?: '—' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Sex</div>
                                    <div class="info-value">{{ $beneficiary->sex }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value">{{ $beneficiary->date_of_birth ? $beneficiary->date_of_birth->format('M d, Y') : '—' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Civil Status</div>
                                    <div class="info-value">{{ $beneficiary->civil_status }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Education</div>
                                    <div class="info-value">{{ $beneficiary->highest_education ?: '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Contact Number</div>
                                    <div class="info-value text-primary">
                                        @if($beneficiary->contact_number)
                                            <i class="bi bi-telephone-fill me-1"></i> {{ $beneficiary->contact_number }}
                                        @else
                                            <span class="text-muted small">No contact recorded</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Primary ID ({{ $beneficiary->id_type ?: 'None' }})</div>
                                    <div class="info-value">{{ $beneficiary->id_number ?: '—' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="info-label">Home Address</div>
                                    <div class="info-value text-muted"><i class="bi bi-house-door me-1"></i> {{ $beneficiary->home_address ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Agency Registrations Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-2">
                            <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0">Registered Agencies</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($beneficiary->agencies->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted">
                                            <tr>
                                                <th class="ps-4">Agency</th>
                                                <th>Identifier</th>
                                                <th class="text-end pe-4">Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($beneficiary->agencies as $agency)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="bg-soft-primary text-primary p-1 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="bi bi-check-circle-fill" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <span class="fw-bold text-dark">{{ $agency->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($agency->pivot->identifier)
                                                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ $agency->pivot->identifier }}</span>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span class="text-muted small">{{ $agency->pivot->registered_at }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">No agency registrations found.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Classification Details Card --}}
                    @if($beneficiary->isFarmer() || $beneficiary->isFisherfolk() || $hasDarDetails)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-2">
                                <div class="bg-info bg-opacity-10 p-2 rounded text-info">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Classification Specifics</h5>
                            </div>
                            <div class="card-body p-4">
                                {{-- Farmer Section --}}
                                @if($beneficiary->isFarmer())
                                    <div class="mb-4">
                                        <h6 class="text-primary fw-bold mb-3 small text-uppercase"><i class="bi bi-tree me-2"></i>Farmer Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="info-label">Ownership</div>
                                                <div class="info-value">{{ $beneficiary->farm_ownership ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Farm Size</div>
                                                <div class="info-value">{{ $beneficiary->farm_size_hectares ? $beneficiary->farm_size_hectares . ' ha' : '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Commodity</div>
                                                <div class="info-value">{{ $beneficiary->primary_commodity ?: '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Fisherfolk Section --}}
                                @if($beneficiary->isFisherfolk())
                                    <div class="mb-4 @if($beneficiary->isFarmer()) border-top pt-4 @endif">
                                        <h6 class="text-info fw-bold mb-3 small text-uppercase"><i class="bi bi-water me-2"></i>Fisherfolk Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="info-label">Fisherfolk Type</div>
                                                <div class="info-value">{{ $beneficiary->fisherfolk_type ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Main Gear</div>
                                                <div class="info-value">{{ $beneficiary->main_fishing_gear ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Vessel</div>
                                                <div class="info-value">
                                                    @if($beneficiary->has_fishing_vessel)
                                                        <span class="badge badge-soft-success">Yes</span>
                                                    @else
                                                        <span class="badge badge-soft-secondary">No</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- DAR Details --}}
                                @if($hasDarDetails)
                                    <div class="mb-0 @if($beneficiary->isFarmer() || $beneficiary->isFisherfolk()) border-top pt-4 @endif">
                                        <h6 class="text-success fw-bold mb-3 small text-uppercase"><i class="bi bi-journal-check me-2"></i>DAR / ARB Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="info-label">CLOA/EP #</div>
                                                <div class="info-value font-monospace">{{ $darAgency?->pivot->identifier ?: data_get($darDynamicData, 'cloa_ep_number', '—') }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Classification</div>
                                                <div class="info-value">{{ data_get($darDynamicData, 'arb_classification', '—') }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-label">Ownership Scheme</div>
                                                <div class="info-value">{{ data_get($darDynamicData, 'ownership_scheme', '—') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Custom Fields Card --}}
                    @if($customFieldValues->isNotEmpty() || $agencyDynamicCustomFieldValues->isNotEmpty())
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-2">
                                <div class="bg-warning bg-opacity-10 p-2 rounded text-warning">
                                    <i class="bi bi-sliders"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Additional Attributes</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    @foreach($customFieldValues as $fieldGroup => $fieldValue)
                                        <div class="col-md-4">
                                            <div class="info-label">{{ Str::title(str_replace('_', ' ', $fieldGroup)) }}</div>
                                            <div class="info-value">
                                                {{ is_array($fieldValue) ? collect($fieldValue)->filter(fn ($item) => filled($item))->implode(', ') : $fieldValue }}
                                            </div>
                                        </div>
                                    @endforeach

                                    @foreach($agencyDynamicCustomFieldValues as $entry)
                                        <div class="col-md-4">
                                            <div class="info-label">{{ $entry['agency_name'] }} &middot; {{ $entry['field_label'] }}</div>
                                            <div class="info-value">{{ $entry['value'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar Column --}}
                <div class="col-lg-4">
                    {{-- Status Summary Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Registration Context</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small fw-bold text-uppercase">RSBSA #</span>
                                    <span class="info-value font-monospace">{{ $beneficiary->rsbsa_number ?: '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <span class="text-muted small fw-bold text-uppercase">FishR #</span>
                                    <span class="info-value font-monospace">{{ $beneficiary->fishr_number ?: '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <span class="text-muted small fw-bold text-uppercase">Member Date</span>
                                    <span class="info-value">{{ $beneficiary->registered_at ? $beneficiary->registered_at->format('M d, Y') : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Association Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Association</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            @if($beneficiary->association_member)
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-inline-flex mb-3">
                                    <i class="bi bi-shield-check fs-2"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Active Member</h6>
                                <p class="text-muted small mb-0">{{ $beneficiary->association_name ?: 'Association name not specified' }}</p>
                            @else
                                <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle d-inline-flex mb-3">
                                    <i class="bi bi-shield-x fs-2"></i>
                                </div>
                                <h6 class="fw-bold mb-1 text-muted">Non-Member</h6>
                                <p class="text-muted small mb-0">No organizational link recorded</p>
                            @endif
                        </div>
                    </div>

                    {{-- Missing Data Reasons Card --}}
                    @if($coreUnavailabilityReasons->isNotEmpty() || $customFieldUnavailabilityReasons->isNotEmpty() || $agencyDynamicUnavailabilityReasons->isNotEmpty())
                        <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="card-title fw-bold mb-0 text-warning">Data Availability Notes</h5>
                            </div>
                            <div class="card-body p-4 pt-2">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($coreUnavailabilityReasons as $label => $reason)
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.6rem;">{{ $label }}</div>
                                            <div class="small fw-semibold">{{ $reason }}</div>
                                        </div>
                                    @endforeach
                                    @foreach($agencyDynamicUnavailabilityReasons as $entry)
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.6rem;">{{ $entry['agency_name'] }} &middot; {{ $entry['field_label'] }}</div>
                                            <div class="small fw-semibold">{{ $entry['reason'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Distributions Tab -->
        <div class="tab-pane fade" id="distributions" role="tabpanel">
            <div class="card border-0 shadow-sm">
                @php
                    $allDistributions = $beneficiary->allocations->map(function($allocation) {
                        $isDirect = $allocation->release_method === 'direct';
                        return (object)[
                            'type' => 'allocation',
                            'methodBadge' => $isDirect ? 'Standalone' : 'Event-Based',
                            'badgeClass' => $isDirect ? 'badge-soft-info' : 'badge-soft-primary',
                            'data' => $allocation,
                            'sortDate' => $allocation->distributed_at ?? $allocation->created_at,
                        ];
                    })->sortByDesc('sortDate')->values();
                @endphp

                <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">Consolidated Distribution Ledger</h5>
                    @if($allDistributions->isNotEmpty())
                        <span class="badge bg-light text-muted border rounded-pill">{{ $allDistributions->count() }} Records</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4">Method</th>
                                    <th>Program & Resource</th>
                                    <th>Agency</th>
                                    <th class="text-end">Value</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allDistributions as $distribution)
                                    @php $allocation = $distribution->data; @endphp
                                    <tr>
                                        <td class="ps-4"><span class="badge {{ $distribution->badgeClass }} px-2 py-1 rounded-pill">{{ $distribution->methodBadge }}</span></td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $allocation->programName->name ?? $allocation->distributionEvent->programName->name ?? '—' }}</div>
                                            <small class="text-muted">{{ $allocation->resourceType->name ?? $allocation->distributionEvent->resourceType->name ?? '—' }}</small>
                                        </td>
                                        <td><small class="text-muted">{{ $allocation->resourceType->agency->name ?? '—' }}</small></td>
                                        <td class="text-end fw-bold text-primary">{{ $allocation->getDisplayValue() }}</td>
                                        <td>
                                            @php
                                                $displayStatus = $allocation->release_status_label;
                                                    
                                                $sBadge = match($displayStatus) {
                                                    'Completed', 'Released' => 'badge-soft-success',
                                                    'Ongoing', 'Pending'   => 'badge-soft-warning',
                                                    'Ready for Release'     => 'badge-soft-info',
                                                    'Not Received'          => 'badge-soft-danger',
                                                    default                 => 'badge-soft-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $sBadge }} px-2 py-1 rounded-pill">{{ $displayStatus }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="small text-dark fw-semibold">{{ $allocation->distributed_at ? $allocation->distributed_at->format('M d, Y') : '—' }}</div>
                                            @if($allocation->distributionEvent?->distribution_date)
                                                <small class="text-muted" style="font-size: 0.7rem;">Sched: {{ $allocation->distributionEvent->distribution_date->format('M d, Y') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No distribution history found for this beneficiary.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Upload Document</h5>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <form action="{{ route('beneficiaries.attachments.store', $beneficiary) }}" method="POST" enctype="multipart/form-data" data-submit-spinner>
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label info-label">Document Category</label>
                                    <input type="text" name="document_type" class="form-control bg-light border-0 @error('document_type') is-invalid @enderror" placeholder="e.g. Govt ID, Barangay Cert" value="{{ old('document_type') }}">
                                    @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-4">
                                    <label class="form-label info-label">Select File</label>
                                    <div class="p-3 bg-light rounded-3 border-dashed border-primary text-center">
                                        <input type="file" name="attachment" class="form-control border-0 bg-transparent @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted mt-2 d-block">PDF, JPG, PNG (Max 5MB)</small>
                                    </div>
                                    @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                                    <i class="bi bi-cloud-upload me-2"></i>Upload Support File
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold mb-0">Supporting Documents Library</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($beneficiary->attachments->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted">
                                            <tr>
                                                <th class="ps-4">Type</th>
                                                <th>File Name</th>
                                                <th class="text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($beneficiary->attachments as $attachment)
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="badge badge-soft-primary rounded-pill">{{ $attachment->document_type ?: 'Other' }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-file-earmark-text fs-5 text-muted"></i>
                                                            <div>
                                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 300px;">{{ $attachment->original_name }}</div>
                                                                <small class="text-muted">{{ number_format($attachment->size_bytes / 1024, 1) }} KB &bull; {{ $attachment->created_at->format('M d, Y') }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                            <a href="{{ route('beneficiaries.attachments.view', [$beneficiary, $attachment]) }}" class="btn btn-white btn-sm px-3 border-end" target="_blank" title="View">
                                                                <i class="bi bi-eye text-primary"></i>
                                                            </a>
                                                            <a href="{{ route('beneficiaries.attachments.download', [$beneficiary, $attachment]) }}" class="btn btn-white btn-sm px-3 border-end" title="Download">
                                                                <i class="bi bi-download text-info"></i>
                                                            </a>
                                                            <form action="{{ route('beneficiaries.attachments.destroy', [$beneficiary, $attachment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this attachment?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-white btn-sm px-3" title="Delete">
                                                                    <i class="bi bi-trash text-danger"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-4 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">No documents in the library.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communications Tab -->
        <div class="tab-pane fade" id="communications" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Broadcast SMS</h5>
                        </div>
                        <div class="card-body p-4 pt-2">
                            @if($beneficiary->contact_number)
                                <form action="{{ route('beneficiaries.sendSms', $beneficiary) }}" method="POST" data-submit-spinner>
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label info-label">Message Template / Content</label>
                                        <textarea name="message" class="form-control bg-light border-0 @error('message') is-invalid @enderror" rows="5" maxlength="300" placeholder="Type your message for this beneficiary..." required>{{ old('message') }}</textarea>
                                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text small mt-2">Recipient: <span class="fw-bold text-primary">{{ $beneficiary->contact_number }}</span></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                </form>
                            @else
                                <div class="alert badge-soft-warning border-0 p-3 rounded-3 d-flex align-items-center gap-3">
                                    <i class="bi bi-exclamation-triangle fs-4"></i>
                                    <div class="small">No valid contact number on file. SMS functionality is disabled for this profile.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="card-title fw-bold mb-0">Message Thread</h5>
                        </div>
                        <div class="card-body p-4 pt-2">
                            @if($beneficiary->smsLogs->isNotEmpty())
                                <div class="sms-thread">
                                    @foreach($beneficiary->smsLogs->sortByDesc('sent_at') as $sms)
                                        <div class="sms-message {{ $sms->status === 'sent' ? 'sent shadow-sm' : 'failed' }}">
                                            <div class="message-text">{{ $sms->message }}</div>
                                            <div class="sms-meta">
                                                <span>{{ $sms->sent_at->format('M d, h:i A') }}</span>
                                                @if($sms->status === 'sent')
                                                    <i class="bi bi-check2-all"></i>
                                                @else
                                                    <i class="bi bi-exclamation-circle-fill" title="Delivery Failed"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-text display-4 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">No SMS history recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restore active tab based on URL param
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'overview';
    const tabTrigger = document.querySelector(`#${activeTab}-tab`);
    if (tabTrigger) {
        const tab = new bootstrap.Tab(tabTrigger);
        tab.show();
    }

    // Update URL when switching tabs
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const targetId = event.target.id.replace('-tab', '');
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('tab', targetId);
            window.history.replaceState({}, '', currentUrl);
        });
    });
});
</script>
@endpush
@endsection
