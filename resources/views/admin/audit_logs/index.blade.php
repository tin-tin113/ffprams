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

    <div class="audit-header mb-3 mt-2">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h4 fw-bold text-dark mb-0">System Audit Log</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-inline-flex gap-2">
                    <span class="text-muted small">Total: <strong>{{ number_format($logs->total()) }}</strong> records</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3 audit-filter-wrapper">
        <div class="card-body p-2 px-3">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">Person</label>
                    <select name="user_id" class="form-select form-select-sm border-0 bg-light shadow-none">
                        <option value="">All Personnel</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">Activity</label>
                    <select name="action" class="form-select form-select-sm border-0 bg-light shadow-none">
                        <option value="">All Types</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $actionLabelMap[$action] ?? ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">Section</label>
                    <select name="table_name" class="form-select form-select-sm border-0 bg-light shadow-none">
                        <option value="">All Sections</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}" {{ request('table_name') === $table ? 'selected' : '' }}>
                                {{ $tableLabelMap[$table] ?? ucwords(str_replace('_', ' ', $table)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">From</label>
                    <input type="date" class="form-control form-control-sm border-0 bg-light shadow-none" name="from" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">To</label>
                    <input type="date" class="form-control form-control-sm border-0 bg-light shadow-none" name="to" value="{{ request('to') }}">
                </div>
                
                <div class="col-md-1">
                    <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">Rows</label>
                    <select name="per_page" class="form-select form-select-sm border-0 bg-light shadow-none">
                        <option value="30" {{ request('per_page') == '30' ? 'selected' : '' }}>30</option>
                        <option value="60" {{ request('per_page') == '60' ? 'selected' : '' }}>60</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100 rounded-pill">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            @if($activeFilterCount)
                <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                    <span class="extra-small text-muted fw-bold text-uppercase">Filters:</span>
                    @foreach($activeFilters as $key => $value)
                        <span class="badge bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-2 py-1 extra-small">
                            {{ $fieldLabelMap[$key] ?? $key }}: {{ $value }}
                        </span>
                    @endforeach
                    <a href="{{ route('admin.audit-logs.index') }}" class="ms-auto extra-small text-decoration-none text-muted">Clear All</a>
                </div>
            @endif
        </div>
    </div>

    <div class="timeline-container">
        @forelse($groupedLogs as $date => $dayLogs)
            <div class="timeline-date-group mb-3">
                <div class="date-header sticky-top bg-white py-2 mb-2 z-3 border-bottom-0">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $displayText = $carbonDate->isToday() ? 'Today' : ($carbonDate->isYesterday() ? 'Yesterday' : $carbonDate->format('F d, Y'));
                        @endphp
                        {{ $displayText }}
                        <span class="badge rounded-pill bg-light text-muted fw-medium ms-2 border" style="font-size: 0.65rem;">{{ $dayLogs->count() }}</span>
                    </h6>
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
                        <div class="card border-0 shadow-sm mb-1 audit-item-card overflow-hidden" id="card-{{ $log->id }}">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-mini text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center flex-wrap gap-2 min-width-0">
                                                <span class="fw-bold text-dark small text-truncate" style="max-width: 150px;">{{ $log->user->name ?? 'Unknown User' }}</span>
                                                <span class="action-badge px-2 py-0" style="background-color: {{ $actionColor ?? '#94a3b8' }}15; color: {{ $actionColor ?? '#94a3b8' }}; border: 1px solid {{ $actionColor ?? '#94a3b8' }}30; height: 20px;">
                                                    <i class="bi {{ $actionIconMap[$log->action] ?? 'bi-dot' }} extra-small"></i>
                                                    <span class="extra-small fw-bold">{{ $actionLabelMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                                </span>
                                                <span class="text-muted extra-small d-none d-md-inline">
                                                    @if($log->action === 'created')
                                                        added record to
                                                    @elseif($log->action === 'updated')
                                                        modified
                                                    @elseif($log->action === 'deleted')
                                                        removed
                                                    @else
                                                        activity in
                                                    @endif
                                                    <span class="fw-bold">{{ $tableLabelMap[$log->table_name] ?? $log->table_name }}</span>
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
                                                            <a href="{{ $recordRoute }}" class="text-primary text-decoration-none fw-bold">#{{ $log->record_id }}</a>
                                                        @else
                                                            <span class="fw-bold">#{{ $log->record_id }}</span>
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                                <span class="text-muted extra-small">{{ $log->created_at->format('h:i A') }}</span>
                                                @if($changedKeys->count() > 0 || collect($requestMeta)->filter()->isNotEmpty() || $hasSpecialField)
                                                    <button class="expansion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}" aria-expanded="false" onclick="document.getElementById('card-{{ $log->id }}').classList.toggle('expanded')">
                                                        <i class="bi bi-chevron-down"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="collapse" id="details-{{ $log->id }}">
                                    <div class="pt-2 mt-2 border-top">
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach($requestMeta as $mLabel => $mValue)
                                                @if(filled($mValue))
                                                    <span class="meta-item py-0 px-2" style="height: 18px; line-height: 18px;">
                                                        <span class="text-uppercase fw-bold opacity-50 extra-small">{{ $mLabel }}:</span> <span class="extra-small">{{ $mValue }}</span>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>

                                        @if($hasSpecialField)
                                            <div class="special-field-badge p-2 mb-3">
                                                <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                                                <div>
                                                    <div class="fw-bold extra-small text-uppercase opacity-75">Override Reason</div>
                                                    <div class="small">{{ $specialFieldValue }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($changedKeys->count())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless audit-diff-table align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="extra-small">FIELD</th>
                                                            <th class="extra-small">CHANGES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($changedKeys as $key)
                                                            <tr>
                                                                <td class="field-name-cell extra-small" style="width: 150px;">
                                                                    {{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                                </td>
                                                                <td>
                                                                    <div class="row g-1 align-items-center diff-grid">
                                                                        <div class="col">
                                                                            <div class="diff-value-container">
                                                                                <div class="diff-value old py-1 px-2 extra-small">
                                                                                    {{ $formatValue((string) $key, $oldValues[$key] ?? null) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-auto d-none d-md-flex">
                                                                            <i class="bi bi-arrow-right text-muted extra-small"></i>
                                                                        </div>
                                                                        <div class="col">
                                                                            <div class="diff-value-container">
                                                                                <div class="diff-value new py-1 px-2 extra-small">
                                                                                    {{ $formatValue((string) $key, $newValues[$key] ?? null) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
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

    <div class="mt-3 mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-muted extra-small order-2 order-md-1">
                @if($logs->total() > 0)
                    Showing {{ number_format($logs->firstItem()) }} to {{ number_format($logs->lastItem()) }} of {{ number_format($logs->total()) }}
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
    .extra-small { font-size: 0.65rem; }

    .stat-pill {
        display: inline-flex;
        flex-direction: column;
        background: white;
        border: 1px solid #edf2f7;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        min-width: 120px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .audit-filter-wrapper {
        border-radius: 0.75rem;
    }

    .audit-item-card {
        border-radius: 0.5rem;
        transition: all 0.15s ease;
        border: 1px solid #f1f5f9 !important;
    }

    .audit-item-card:hover {
        border-color: #e2e8f0 !important;
        background: #fcfdfe;
    }

    .audit-item-card.expanded {
        border-color: #3b82f6 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }

    .user-avatar-mini {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .action-badge {
        font-weight: 700;
        border-radius: 0.35rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .meta-item {
        color: #64748b;
        background: #f8fafc;
        border-radius: 0.25rem;
        border: 1px solid #f1f5f9;
    }

    .expansion-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #f1f5f9;
    }

    .expansion-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .expansion-btn[aria-expanded="true"] {
        background: #3b82f6;
        color: white;
        transform: rotate(180deg);
    }

    .audit-diff-table thead th {
        background: #f8fafc;
        padding: 0.4rem 0.5rem;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }

    .audit-diff-table tbody td {
        padding: 0.4rem 0.5rem;
    }

    .diff-value {
        border-radius: 0.35rem;
        min-height: 1.5rem;
        display: flex;
        align-items: center;
        border: 1px solid transparent;
    }

    .diff-value.old {
        background-color: #fef2f2;
        color: #991b1b;
        border-color: #fee2e2;
    }

    .diff-value.new {
        background-color: #f0fdf4;
        color: #166534;
        border-color: #dcfce7;
    }

    .date-header {
        top: 0;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(4px);
    }

    .special-field-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #fffbeb;
        border: 1px solid #fef3c7;
        color: #92400e;
        border-radius: 0.5rem;
    }

    @media (max-width: 767.98px) {
        .diff-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Any specific JS logic for compact UI if needed
    });
</script>
@endpush
