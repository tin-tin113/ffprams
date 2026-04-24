@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="container-fluid">
    @php
        $activeFilters = collect([
            'user_id' => request('user_id'),
            'action' => request('action'),
            'table_name' => request('table_name'),
            'record_id' => request('record_id'),
            'from' => request('from'),
            'to' => request('to'),
        ])->filter(fn ($value) => filled($value));

        $activeFilterCount = $activeFilters->count();

        $actionLabelMap = [
            'created' => 'Added',
            'updated' => 'Edited',
            'deleted' => 'Deleted',
            'login' => 'Signed In',
            'logout' => 'Signed Out',
            'profile_updated' => 'Profile Updated',
            'password_updated' => 'Password Changed',
            'account_deleted' => 'Account Deleted',
        ];

        $actionBadgeClassMap = [
            'created' => 'text-bg-success',
            'updated' => 'text-bg-primary',
            'deleted' => 'text-bg-danger',
            'login' => 'text-bg-info',
            'logout' => 'text-bg-secondary',
            'profile_updated' => 'text-bg-primary',
            'password_updated' => 'text-bg-warning',
            'account_deleted' => 'text-bg-danger',
        ];

        $tableLabelMap = [
            'users' => 'Users',
            'beneficiaries' => 'Beneficiaries',
            'allocations' => 'Assistance Allocations',
            'distribution_events' => 'Distribution Events',
            'resource_types' => 'Resource Types',
            'program_names' => 'Program Names',
            'agencies' => 'Agencies',
            'assistance_purposes' => 'Assistance Purposes',
            'form_field_options' => 'Form Field Options',
            'sms_broadcast' => 'SMS Broadcast',
            'auth' => 'Sign In and Sign Out',
            'field_assessments' => 'Field Assessments',
        ];

        $fieldLabelMap = [
            'name' => 'Name',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'role' => 'Role',
            'status' => 'Status',
            'remarks' => 'Remarks',
            'quantity' => 'Quantity',
            'amount' => 'Amount',
            'release_method' => 'Release Method',
            'release_outcome' => 'Release Outcome',
            'distributed_at' => 'Release Date and Time',
            'program_name_id' => 'Program',
            'resource_type_id' => 'Resource Type',
            'beneficiary_id' => 'Beneficiary',
            'assistance_purpose_id' => 'Assistance Purpose',
            'distribution_event_id' => 'Event',
            'is_active' => 'Active Status',
            'classification' => 'Classification',
            'contact_number' => 'Contact Number',
            'barangay_id' => 'Barangay',
            'distribution_date' => 'Distribution Date',
            'type' => 'Distribution Type',
            'total_fund_amount' => 'Total Fund Amount',
            'legal_basis_type' => 'Legal Basis Type',
            'legal_basis_reference_no' => 'Legal Basis Reference Number',
            'legal_basis_date' => 'Legal Basis Date',
            'legal_basis_remarks' => 'Legal Remarks',
            'fund_source' => 'Fund Source',
            'liquidation_status' => 'Liquidation Status',
            'liquidation_due_date' => 'Liquidation Due Date',
            'liquidation_submitted_at' => 'Liquidation Submitted Date',
            'liquidation_reference_no' => 'Liquidation Reference Number',
            'requires_farmc_endorsement' => 'FARMC Endorsement Required',
            'farmc_endorsed_at' => 'FARMC Endorsed Date',
            'farmc_reference_no' => 'FARMC Reference Number',
            'beneficiary_list_override_reason' => 'Override Reason',
        ];

        $ignoredFields = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'active_beneficiary_id',
            'distribution_event',
            '_ip',
            '_method',
            '_url',
            '_route',
            '_user_agent',
        ];

        $booleanFields = [
            'is_active',
            'requires_farmc_endorsement',
            'is_required',
            'is_financial',
        ];

        $enumValueMap = [
            'release_method' => [
                'direct' => 'Direct Assistance',
                'event' => 'Event Allocation',
            ],
            'release_status' => [
                'planned' => 'Planned',
                'ready_for_release' => 'Ready for Release',
                'released' => 'Released',
                'not_received' => 'Not Received',
            ],
            'status' => [
                'pending' => 'Pending',
                'ongoing' => 'Ongoing',
                'completed' => 'Completed',
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'type' => [
                'financial' => 'Financial',
                'physical' => 'Physical',
            ],
            'liquidation_status' => [
                'pending' => 'Pending',
                'submitted' => 'Submitted',
                'verified' => 'Verified',
            ],
            'fund_source' => [
                'lgu_trust_fund' => 'LGU Trust Fund',
                'nga_transfer' => 'NGA Transfer',
                'local_program' => 'Local Program',
                'other' => 'Other',
            ],
            'legal_basis_type' => [
                'resolution' => 'Resolution',
                'ordinance' => 'Ordinance',
                'memo' => 'Memo',
                'special_order' => 'Special Order',
                'other' => 'Other',
            ],
        ];

        $formatValue = function (string $fieldKey, $value) use ($booleanFields, $enumValueMap, $referenceMaps): string {
            if ($value === null) {
                return 'None';
            }

            if (in_array($fieldKey, $booleanFields, true)) {
                if ($value === true || $value === 1 || $value === '1') {
                    return 'Yes';
                }

                if ($value === false || $value === 0 || $value === '0') {
                    return 'No';
                }
            }

            if (isset($referenceMaps[$fieldKey]) && is_numeric($value) && (int) $value > 0) {
                $id = (int) $value;
                $label = $referenceMaps[$fieldKey][$id] ?? null;
                if ($label !== null && $label !== '') {
                    return $label . ' (#' . $id . ')';
                }
            }

            if (isset($enumValueMap[$fieldKey])) {
                $normalized = strtolower((string) $value);
                if (isset($enumValueMap[$fieldKey][$normalized])) {
                    return $enumValueMap[$fieldKey][$normalized];
                }
            }

            if ($value === true || $value === 1 || $value === '1') {
                return 'Yes';
            }
            if ($value === false || $value === 0 || $value === '0') {
                return 'No';
            }
            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            return (string) $value;
        };

        // Group logs by date for the timeline view
        $groupedLogs = $logs->groupBy(fn($log) => $log->created_at->format('Y-m-d'));
        
        $actionIconMap = [
            'created' => 'bi-plus-circle-fill',
            'updated' => 'bi-pencil-square',
            'deleted' => 'bi-trash3-fill',
            'login' => 'bi-door-open-fill',
            'logout' => 'bi-door-closed-fill',
            'profile_updated' => 'bi-person-badge',
            'password_updated' => 'bi-shield-lock-fill',
            'account_deleted' => 'bi-person-x-fill',
        ];
    @endphp

    <div class="audit-header mb-4 mt-2">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-dark mb-1">System Audit Log</h1>
                <p class="text-muted small mb-0">Transparency and accountability through comprehensive activity tracking.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex flex-wrap gap-2">
                    <div class="stat-pill">
                        <span class="label">Total Records</span>
                        <span class="value">{{ number_format($logs->total()) }}</span>
                    </div>
                    <div class="stat-pill {{ $activeFilterCount ? 'active' : '' }}">
                        <span class="label">Active Filters</span>
                        <span class="value">{{ $activeFilterCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 audit-filter-wrapper">
        <div class="card-body p-0">
            <div class="filter-toggle-header d-flex justify-content-between align-items-center p-3 border-bottom d-md-none" data-bs-toggle="collapse" data-bs-target="#auditFilterCollapse">
                <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div id="auditFilterCollapse" class="collapse show d-md-block">
                <div class="p-3 p-lg-4">
                    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-medium small text-muted text-uppercase">Person</label>
                            <select name="user_id" class="form-select border-0 bg-light shadow-none">
                                <option value="">All Personnel</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium small text-muted text-uppercase">Activity Type</label>
                            <select name="action" class="form-select border-0 bg-light shadow-none">
                                <option value="">All Types</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                        {{ $actionLabelMap[$action] ?? ucwords(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium small text-muted text-uppercase">Section</label>
                            <select name="table_name" class="form-select border-0 bg-light shadow-none">
                                <option value="">All Sections</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table }}" {{ request('table_name') === $table ? 'selected' : '' }}>
                                        {{ $tableLabelMap[$table] ?? ucwords(str_replace('_', ' ', $table)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium small text-muted text-uppercase">From Date</label>
                            <input type="date" class="form-control border-0 bg-light shadow-none" name="from" value="{{ request('from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium small text-muted text-uppercase">To Date</label>
                            <input type="date" class="form-control border-0 bg-light shadow-none" name="to" value="{{ request('to') }}">
                        </div>
                        
                        <div class="col-md-1">
                            <label class="form-label fw-medium small text-muted text-uppercase">Rows</label>
                            <select name="per_page" class="form-select border-0 bg-light shadow-none">
                                <option value="30" {{ request('per_page') == '30' ? 'selected' : '' }}>30</option>
                                <option value="60" {{ request('per_page') == '60' ? 'selected' : '' }}>60</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                            <div class="active-filter-badges d-none d-lg-flex gap-2">
                                @if($activeFilterCount)
                                    @foreach($activeFilters as $key => $value)
                                        @if($key !== 'record_id')
                                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border-0 px-3 py-2">
                                                {{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}: 
                                                <span class="fw-bold">{{ $value }}</span>
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="d-flex gap-2 ms-auto">
                                @if($activeFilterCount)
                                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-link text-muted text-decoration-none px-3">
                                        Clear All
                                    </a>
                                @endif
                                <button type="submit" class="btn btn-dark px-4 rounded-pill">
                                    <i class="bi bi-search me-2"></i>Search Activity
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="timeline-container">
        @forelse($groupedLogs as $date => $dayLogs)
            <div class="timeline-date-group mb-4">
                <div class="date-header sticky-top bg-white py-2 mb-3 z-3">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $displayText = $carbonDate->isToday() ? 'Today' : ($carbonDate->isYesterday() ? 'Yesterday' : $carbonDate->format('F d, Y'));
                        @endphp
                        {{ $displayText }}
                        <span class="badge rounded-pill bg-light text-muted fw-medium ms-2 border" style="font-size: 0.7rem;">{{ $dayLogs->count() }} activities</span>
                    </h5>
                </div>

                <div class="audit-card-list">
                    @foreach($dayLogs as $log)
                        @php
                            $actionColor = match($log->action) {
                                'created' => '#10b981',
                                'updated' => '#3b82f6',
                                'deleted' => '#ef4444',
                                'login' => '#6366f1',
                                'logout' => '#64748b',
                                default => '#94a3b8'
                            };

                            $oldValues = is_array($log->old_values) ? $log->old_values : [];
                            $newValues = is_array($log->new_values) ? $log->new_values : [];

                            $requestMeta = [
                                'IP' => $newValues['_ip'] ?? null,
                                'Method' => $newValues['_method'] ?? null,
                                'Route' => $newValues['_route'] ?? null,
                            ];

                            $specialFields = ['beneficiary_list_override_reason'];
                            $hasSpecialField = false;
                            $specialFieldValue = null;

                            foreach ($specialFields as $field) {
                                if (isset($newValues[$field]) && !empty($newValues[$field])) {
                                    $hasSpecialField = true;
                                    $specialFieldValue = $newValues[$field];
                                    break;
                                }
                            }

                            foreach ($ignoredFields as $ignoredField) {
                                unset($oldValues[$ignoredField], $newValues[$ignoredField]);
                            }

                            $changedKeys = collect(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))))
                                ->filter(function ($key) use ($oldValues, $newValues) {
                                    $old = $oldValues[$key] ?? null;
                                    $new = $newValues[$key] ?? null;
                                    return $old !== $new;
                                })
                                ->values();
                        @endphp
                        <div class="card border-0 shadow-sm mb-2 audit-item-card overflow-hidden">
                            <div class="card-body p-0">
                                <div class="d-flex flex-column flex-lg-row">
                                    <div class="audit-time-stripe d-none d-lg-flex flex-column justify-content-center align-items-center px-2 py-2 bg-light border-end" style="min-width: 70px;">
                                        <span class="time fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $log->created_at->format('h:i') }}</span>
                                        <span class="period text-muted small text-uppercase" style="font-size: 0.6rem;">{{ $log->created_at->format('A') }}</span>
                                    </div>
                                    <div class="flex-grow-1 py-2 px-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar-mini bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                                    {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div class="d-flex align-items-center flex-wrap gap-1">
                                                    <span class="fw-bold text-dark small">{{ $log->user->name ?? 'Unknown User' }}</span>
                                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $actionColor ?? '#94a3b8' }}15; color: {{ $actionColor ?? '#94a3b8' }}; border: 1px solid {{ $actionColor ?? '#94a3b8' }}30; font-size: 0.65rem;">
                                                        <i class="bi {{ $actionIconMap[$log->action] ?? 'bi-dot' }} me-1"></i>
                                                        {{ $actionLabelMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action)) }}
                                                    </span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">
                                                        @if($log->action === 'created')
                                                            added record to
                                                        @elseif($log->action === 'updated')
                                                            modified
                                                        @elseif($log->action === 'deleted')
                                                            removed
                                                        @else
                                                            activity in
                                                        @endif
                                                        <span class="fw-semibold text-dark">{{ $tableLabelMap[$log->table_name] ?? $log->table_name }}</span>
                                                        @if($log->record_id)
                                                            @php
                                                                $recordRoute = match($log->table_name) {
                                                                    'beneficiaries' => route('beneficiaries.show', $log->record_id),
                                                                    'distribution_events' => route('distribution-events.show', $log->record_id),
                                                                    'users' => route('admin.users.edit', $log->record_id),
                                                                    default => null
                                                                };
                                                            @endphp
                                                            @if($recordRoute)
                                                                <a href="{{ $recordRoute }}" class="fw-bold text-primary text-decoration-none ms-1">#{{ $log->record_id }}</a>
                                                            @else
                                                                <span class="fw-bold text-dark ms-1">#{{ $log->record_id }}</span>
                                                            @endif
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                @if($changedKeys->count() > 0 || collect($requestMeta)->filter()->isNotEmpty())
                                                    <button class="btn btn-link btn-sm text-primary fw-bold text-decoration-none p-0 expansion-trigger" style="font-size: 0.75rem;" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}">
                                                        {{ $changedKeys->count() ?: '' }} Changes <i class="bi bi-chevron-down ms-1 transition-icon"></i>
                                                    </button>
                                                @endif
                                                <span class="text-muted d-lg-none" style="font-size: 0.7rem;">{{ $log->created_at->format('h:i A') }}</span>
                                            </div>
                                        </div>

                                        @if($hasSpecialField)
                                            <div class="mt-2 p-2 rounded bg-warning bg-opacity-10 border-start border-warning border-3">
                                                <div class="text-dark small" style="font-size: 0.75rem;">
                                                    <span class="fw-bold text-warning-emphasis text-uppercase me-1" style="font-size: 0.6rem;">Override:</span> {{ $specialFieldValue }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="collapse" id="details-{{ $log->id }}">
                                            <div class="pt-2 border-top mt-2">
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    @foreach($requestMeta as $mLabel => $mValue)
                                                        @if(filled($mValue))
                                                            <span class="text-muted" style="font-size: 0.65rem;">
                                                                {{ $mLabel }}: <span class="text-dark fw-medium">{{ $mValue }}</span>
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                @if($changedKeys->count())
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless audit-diff-table align-middle mb-0">
                                                            <thead>
                                                                <tr class="text-muted small text-uppercase">
                                                                    <th class="ps-0" style="width: 25%;">Field</th>
                                                                    <th style="width: 37.5%;">Previous Value</th>
                                                                    <th class="pe-0" style="width: 37.5%;">New Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($changedKeys as $key)
                                                                    <tr class="border-bottom-faint">
                                                                        <td class="ps-0 fw-semibold text-dark small py-2">
                                                                            {{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                                        </td>
                                                                        <td class="py-2">
                                                                            <div class="diff-value old px-2 py-1 rounded bg-danger bg-opacity-10 text-danger-emphasis small border border-danger-subtle">
                                                                                {{ $formatValue((string) $key, $oldValues[$key] ?? null) }}
                                                                            </div>
                                                                        </td>
                                                                        <td class="pe-0 py-2">
                                                                            <div class="diff-value new px-2 py-1 rounded bg-success bg-opacity-10 text-success-emphasis small border border-success-subtle fw-medium">
                                                                                {{ $formatValue((string) $key, $newValues[$key] ?? null) }}
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="text-muted small py-2">
                                                        <i class="bi bi-info-circle me-1"></i> No field-level changes were captured for this activity.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-5 my-5 bg-white rounded shadow-sm border">
                <div class="mb-3">
                    <i class="bi bi-journal-x text-light display-1"></i>
                </div>
                <h4 class="fw-bold text-dark">No Activity Found</h4>
                <p class="text-muted">Adjust your filters or search criteria to find what you're looking for.</p>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-primary px-4 rounded-pill mt-2">
                    Clear All Filters
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-muted small order-2 order-md-1">
                @if($logs->total() > 0)
                    Showing {{ number_format($logs->firstItem()) }} to {{ number_format($logs->lastItem()) }} of {{ number_format($logs->total()) }} activities
                @endif
            </div>
            @if($logs->hasPages())
                <div class="pagination-container order-1 order-md-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-pill {
        display: inline-flex;
        flex-direction: column;
        background: white;
        border: 1px solid #edf2f7;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        min-width: 120px;
        text-align: left;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .stat-pill.active {
        border-color: #0d6efd;
        background: #f0f7ff;
    }

    .stat-pill .label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #718096;
        font-weight: 600;
        margin-bottom: 0.15rem;
    }

    .stat-pill .value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1a202c;
    }

    .audit-filter-wrapper {
        border-radius: 1.25rem;
        overflow: hidden;
    }

    .audit-item-card {
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        border: 1px solid #edf2f7 !important;
    }

    .audit-item-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        border-color: #cbd5e0 !important;
    }

    .audit-time-stripe {
        min-width: 70px;
    }

    .date-header {
        top: 60px; /* Adjust based on navbar height if needed */
    }

    .transition-icon {
        transition: transform 0.3s ease;
        display: inline-block;
    }

    .expansion-trigger[aria-expanded="true"] .transition-icon {
        transform: rotate(90deg);
    }

    .audit-diff-table th {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
    }

    .border-bottom-faint {
        border-bottom: 1px solid #f7fafc;
    }

    .diff-value {
        word-break: break-all;
        line-height: 1.4;
    }

    .hover-underline:hover {
        text-decoration: underline !important;
    }

    .copy-id-btn:hover {
        color: #0d6efd !important;
    }

    .audit-diff-table tr:hover {
        background-color: #f8fafc;
    }

    @media (max-width: 991.98px) {
        .date-header {
            top: 0;
        }
        
        .audit-time-stripe {
            border-end: 0 !important;
            border-bottom: 1px solid #edf2f7;
            flex-direction: row !important;
            justify-content: flex-start !important;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Copy ID functionality
        document.querySelectorAll('.copy-id-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                navigator.clipboard.writeText(id).then(() => {
                    const icon = this.querySelector('i');
                    icon.classList.replace('bi-clipboard', 'bi-check-lg');
                    icon.classList.add('text-success');
                    
                    setTimeout(() => {
                        icon.classList.replace('bi-check-lg', 'bi-clipboard');
                        icon.classList.remove('text-success');
                    }, 2000);
                });
            });
        });
    });
</script>
@endpush
