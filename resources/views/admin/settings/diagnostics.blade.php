@extends('layouts.app')

@section('title', 'Settings Diagnostics')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">System Settings</a></li>
    <li class="breadcrumb-item active">Diagnostics</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.settings.index', ['tab' => 'agencies']) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Settings
        </a>
    </div>

    @if (($repairedFieldCount ?? 0) > 0)
        <div class="alert alert-success">
            Auto-repair applied: {{ $repairedFieldCount }} missing core agency field(s) were created for DA/BFAR/DAR.
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-1"></i> Route Health</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Capability</th>
                                    <th>Route Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($routeDiagnostics as $routeItem)
                                    <tr>
                                        <td>{{ $routeItem['label'] }}</td>
                                        <td><code>{{ $routeItem['name'] }}</code></td>
                                        <td>
                                            @if ($routeItem['exists'])
                                                <span class="badge bg-success">OK</span>
                                            @else
                                                <span class="badge bg-danger">Missing</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-list-check me-1"></i> Global Core Groups (Legacy Check)</h5>
                </div>
                <div class="card-body">
                    @if ($globalCoreGroupCounts->isEmpty())
                        <div class="alert alert-success mb-0">No legacy global entries found for agency-specific core field groups.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Core Group</th>
                                        <th>Global Entries</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($globalCoreGroupCounts as $group => $count)
                                        <tr>
                                            <td><code>{{ $group }}</code></td>
                                            <td><span class="badge bg-warning text-dark">{{ $count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-building me-1"></i> Agency Mapping Coverage</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Agency</th>
                                    <th>Classifications</th>
                                    <th>Status</th>
                                    <th>Total Fields</th>
                                    <th>Core Coverage</th>
                                    <th>Missing Core Fields</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($agencyDiagnostics as $agency)
                                    <tr>
                                        <td>
                                            <strong>{{ $agency['name'] }}</strong>
                                            <div class="text-muted small">{{ $agency['full_name'] }}</div>
                                        </td>
                                        <td>
                                            @if (empty($agency['classifications']))
                                                <span class="text-muted small">—</span>
                                            @else
                                                @foreach ($agency['classifications'] as $classification)
                                                    <span class="badge bg-info me-1">{{ $classification }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if ($agency['is_active'])
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $agency['total_form_fields'] }}</td>
                                        <td>
                                            <span class="badge {{ $agency['expected_core_count'] === $agency['existing_core_count'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $agency['existing_core_count'] }}/{{ $agency['expected_core_count'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if (empty($agency['missing_core_fields']))
                                                <span class="text-success small">None</span>
                                            @else
                                                @foreach ($agency['missing_core_fields'] as $field)
                                                    <span class="badge bg-danger me-1">{{ $field }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
