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
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">See who did what, when it happened, and what changed.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border px-3 py-2">Total Records: {{ number_format($logs->total()) }}</span>
            <span class="badge rounded-pill text-bg-light border px-3 py-2">Showing: {{ number_format($logs->count()) }}</span>
            <span class="badge rounded-pill {{ $activeFilterCount ? 'text-bg-primary' : 'text-bg-light border' }} px-3 py-2">
                Filters Applied: {{ $activeFilterCount }}
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 modern-filter-card audit-filter-card">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-funnel me-1"></i> Find Activity
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3 modern-filter-grid">
                <div class="col-md-3">
                    <label class="form-label">Person</label>
                    <select name="user_id" class="form-select">
                        <option value="">All People</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Activity Type</label>
                    <select name="action" class="form-select">
                        <option value="">All Activity Types</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $actionLabelMap[$action] ?? ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="table_name" class="form-select">
                        <option value="">All Sections</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}" {{ request('table_name') === $table ? 'selected' : '' }}>
                                {{ $tableLabelMap[$table] ?? ucwords(str_replace('_', ' ', $table)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Reference ID</label>
                    <input type="number" min="1" class="form-control" name="record_id" value="{{ request('record_id') }}" placeholder="e.g. 60">
                </div>

                <div class="col-12 col-md-3 modern-filter-actions d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>

            @if($activeFilterCount)
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @foreach($activeFilters as $key => $value)
                        <span class="badge rounded-pill text-bg-light border px-3 py-2">
                            {{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><i class="bi bi-journal-text me-1"></i> Activity List</div>
            <div class="small text-muted">Newest first</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards audit-log-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date and Time</th>
                            <th>Person</th>
                            <th>Activity</th>
                            <th>Section</th>
                            <th>Reference ID</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted small" data-label="Date and Time">
                                    <div class="fw-semibold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div>{{ $log->created_at->format('h:i:s A') }}</div>
                                </td>
                                <td data-label="Person">
                                    <div class="fw-semibold">{{ $log->user->name ?? 'Unknown User' }}</div>
                                    <div class="small text-muted">{{ $log->user->email ?? 'N/A' }}</div>
                                </td>
                                <td data-label="Activity">
                                    <span class="badge {{ $actionBadgeClassMap[$log->action] ?? 'text-bg-secondary' }}">
                                        {{ $actionLabelMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td data-label="Section">{{ $tableLabelMap[$log->table_name] ?? ucwords(str_replace('_', ' ', $log->table_name)) }}</td>
                                <td data-label="Reference ID">{{ $log->record_id ?: 'N/A' }}</td>
                                <td data-label="Details">
                                    @php
                                        $oldValues = is_array($log->old_values) ? $log->old_values : [];
                                        $newValues = is_array($log->new_values) ? $log->new_values : [];

                                        $requestMeta = [
                                            'IP Address' => $newValues['_ip'] ?? null,
                                            'HTTP Method' => $newValues['_method'] ?? null,
                                            'Route' => $newValues['_route'] ?? null,
                                        ];

                                        // Check for special fields that should be highlighted
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

                                    @if($hasSpecialField)
                                        <div class="mb-2 p-2 rounded bg-warning bg-opacity-10 border border-warning-subtle">
                                            <div class="small text-dark" style="max-width: 300px; word-wrap: break-word;">
                                                {{ $specialFieldValue }}
                                            </div>
                                        </div>
                                    @endif

                                    <details class="audit-details">
                                        <summary class="small text-primary" style="cursor: pointer;">
                                            <i class="bi bi-chevron-right me-1"></i>View Details
                                            @if($changedKeys->count())
                                                <span class="badge text-bg-light border ms-1">{{ $changedKeys->count() }} change(s)</span>
                                            @endif
                                        </summary>
                                        <div class="mt-3 pt-2 border-top">
                                            @if($changedKeys->count())
                                                <div class="small fw-semibold mb-3">
                                                    <i class="bi bi-arrow-left-right me-1"></i> Changes Summary
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0 audit-changes-table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="col-3">Field</th>
                                                                <th class="col-3">Before</th>
                                                                <th class="col-3">After</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($changedKeys as $key)
                                                                <tr>
                                                                    <td class="fw-semibold text-dark">
                                                                        {{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                                    </td>
                                                                    <td class="text-muted small">
                                                                        {{ $formatValue((string) $key, $oldValues[$key] ?? null) }}
                                                                    </td>
                                                                    <td class="fw-semibold">
                                                                        {{ $formatValue((string) $key, $newValues[$key] ?? null) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="small text-muted py-2">
                                                    <i class="bi bi-info-circle me-1"></i> No user-visible changes recorded.
                                                </div>
                                            @endif

                                            @if(collect($requestMeta)->filter()->isNotEmpty())
                                                <div class="small fw-semibold mb-2 mt-3 pt-2 border-top">
                                                    <i class="bi bi-server me-1"></i> Request Context
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($requestMeta as $metaLabel => $metaValue)
                                                        @if(filled($metaValue))
                                                            <span class="badge rounded-pill text-bg-light border small">
                                                                {{ $metaLabel }}: <code class="text-dark">{{ $metaValue }}</code>
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No activity found for your current search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .audit-filter-card {
        border-left: 4px solid #0d6efd;
    }

    .audit-log-table thead th {
        white-space: nowrap;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .audit-log-table tbody td {
        vertical-align: top;
    }

    .audit-details summary {
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
    }

    .audit-details summary::-webkit-details-marker {
        display: none;
    }

    .audit-details summary::before {
        content: '\25B8';
        font-size: 0.7rem;
        color: #64748b;
        transition: transform 0.15s ease;
    }

    .audit-details[open] summary::before {
        transform: rotate(90deg);
    }

    .audit-changes-table td:nth-child(2) {
        background-color: #fff5f5;
    }

    .audit-changes-table td:nth-child(3) {
        background-color: #f0fff4;
    }

    @media (max-width: 991.98px) {
        .audit-log-table thead {
            display: none;
        }

        .audit-log-table tr {
            display: block;
            border-bottom: 1px solid #e9ecef;
            padding: 0.65rem 0;
        }

        .audit-log-table td {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 0.5rem;
            border: 0;
            padding: 0.35rem 0.75rem;
        }

        .audit-log-table td::before {
            content: attr(data-label);
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.01em;
        }
    }
</style>
@endpush
