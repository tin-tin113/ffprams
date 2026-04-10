@extends('layouts.app')

@section('title', 'Audit Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Audit Log</li>
@endsection

@section('content')
<div class="container-fluid">
    @php
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

        $formatValue = function ($value): string {
            if ($value === null) {
                return 'None';
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
            <h1 class="h3 mb-0">Audit Log</h1>
            <p class="text-muted mb-0">See who did what, when it happened, and what changed.</p>
        </div>
    </div>

    <div class="alert alert-info" role="alert">
        <strong>Quick guide:</strong> This page is your system timeline. Use filters to find activities faster.
        <div class="small mt-1">Examples: login, logout, profile updated, beneficiary created, event status updated.</div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-funnel me-1"></i> Find Activity
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
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

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-journal-text me-1"></i> Activity List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
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
                                <td class="text-muted small" data-label="Date and Time">{{ $log->created_at->format('M d, Y h:i:s A') }}</td>
                                <td data-label="Person">
                                    <div class="fw-semibold">{{ $log->user->name ?? 'Unknown User' }}</div>
                                    <div class="small text-muted">{{ $log->user->email ?? 'N/A' }}</div>
                                </td>
                                <td data-label="Activity"><span class="badge bg-secondary">{{ $actionLabelMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action)) }}</span></td>
                                <td data-label="Section">{{ $tableLabelMap[$log->table_name] ?? ucwords(str_replace('_', ' ', $log->table_name)) }}</td>
                                <td data-label="Reference ID">{{ $log->record_id }}</td>
                                <td data-label="Details">
                                    @php
                                        $oldValues = is_array($log->old_values) ? $log->old_values : [];
                                        $newValues = is_array($log->new_values) ? $log->new_values : [];

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
                                    <details>
                                        <summary class="small text-primary" style="cursor: pointer;">Show Details</summary>
                                        <div class="mt-2">
                                            @if($changedKeys->count())
                                                <div class="small fw-semibold mb-2">What Changed</div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Before</th>
                                                                <th>After</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($changedKeys as $key)
                                                                <tr>
                                                                    <td>{{ $fieldLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}</td>
                                                                    <td>{{ $formatValue($oldValues[$key] ?? null) }}</td>
                                                                    <td>{{ $formatValue($newValues[$key] ?? null) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="small text-muted">No user-visible field changes were found for this item.</div>
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
