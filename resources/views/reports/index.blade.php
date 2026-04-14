@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@push('styles')
<style>
    .report-chart-wrap {
        position: relative;
        height: clamp(220px, 42vw, 360px);
    }

    .report-chart-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .report-data-table {
        min-width: 860px;
    }

    @media (max-width: 575.98px) {
        .report-data-table {
            min-width: 760px;
        }
    }

    @media print {
        .top-navbar,
        .sidebar,
        .no-print,
        .btn,
        .alert {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }
        body {
            background: #fff !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4 no-print">
        <div>
            <h1 class="h3 mb-0">Reports</h1>
            <p class="text-muted mb-0">Summary reports and analytics for resource distribution</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Financial Events</div>
                    <div class="fs-4 fw-bold">{{ number_format($complianceOverview->financial_events_total) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Missing Legal Basis</div>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($complianceOverview->missing_legal_basis) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Pending Liquidation</div>
                    <div class="fs-4 fw-bold text-warning">{{ number_format($complianceOverview->liquidation_pending) }}</div>
                    <div class="small text-muted">Overdue: {{ number_format($complianceOverview->liquidation_overdue) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">FARMC Pending</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($complianceOverview->farmc_required_pending) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 1 — Beneficiaries per Barangay --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-people me-1"></i> Report 1: Beneficiaries per Barangay</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        @if($beneficiariesPerBarangay->count())
            <div class="card-body border-bottom pb-3">
                <div class="report-chart-wrap">
                    <canvas id="barangayBeneficiariesChart"></canvas>
                </div>
            </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th class="text-center">Farmers</th>
                            <th class="text-center">Fisherfolk</th>
                            <th class="text-center">Both</th>
                            <th class="text-center">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beneficiariesPerBarangay as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->barangay->name }}</td>
                                <td class="text-center">{{ number_format($row->total_farmers) }}</td>
                                <td class="text-center">{{ number_format($row->total_fisherfolk) }}</td>
                                <td class="text-center">{{ number_format($row->total_both) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->grand_total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No beneficiary data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($beneficiariesPerBarangay->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Total</td>
                                <td class="text-center">{{ number_format($beneficiariesPerBarangay->sum('total_farmers')) }}</td>
                                <td class="text-center">{{ number_format($beneficiariesPerBarangay->sum('total_fisherfolk')) }}</td>
                                <td class="text-center">{{ number_format($beneficiariesPerBarangay->sum('total_both')) }}</td>
                                <td class="text-center">{{ number_format($beneficiariesPerBarangay->sum('grand_total')) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 2 — Resource Distribution Summary (Event vs Direct) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-box-seam me-1"></i> Report 2: Resource Distribution Summary (Event vs Direct)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        @if($resourceDistribution->count())
            <div class="card-body border-bottom pb-3">
                <div class="report-chart-wrap">
                    <canvas id="resourceDistributionChart"></canvas>
                </div>
            </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Resource Type</th>
                            <th>Unit</th>
                            <th>Source Agency</th>
                            <th class="text-center">Event Qty</th>
                            <th class="text-center">Direct Qty</th>
                            <th class="text-center">Total Qty</th>
                            <th class="text-center">Event Beneficiaries</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-center">Completed Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resourceDistribution as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->unit }}</td>
                                <td>
                                    @php
                                        $agencyName = $row->agency_name ?? 'N/A';
                                        $agencyBadge = match($agencyName) {
                                            'DA'   => 'bg-success',
                                            'BFAR' => 'bg-primary',
                                            'DAR'  => 'bg-warning text-dark',
                                            'LGU'  => 'bg-secondary',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span>
                                </td>
                                <td class="text-center">{{ number_format($row->event_quantity_distributed, 2) }}</td>
                                <td class="text-center">{{ number_format($row->direct_quantity_distributed, 2) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_quantity_distributed, 2) }}</td>
                                <td class="text-center">{{ number_format($row->event_beneficiaries_reached) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries_reached) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_beneficiaries_reached) }}</td>
                                <td class="text-center">{{ number_format($row->total_events) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distribution data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 3 — Distribution Status + Direct Releases per Barangay --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-bar-chart me-1"></i> Report 3: Distribution Status + Direct Releases per Barangay</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        @if($statusPerBarangay->count())
            <div class="card-body border-bottom pb-3">
                <div class="report-chart-wrap">
                    <canvas id="statusPerBarangayChart"></canvas>
                </div>
            </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Ongoing</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Direct Releases</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statusPerBarangay as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->barangay->name }}</td>
                                <td class="text-center"><span class="badge bg-info">{{ $row->pending_events }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $row->ongoing_events }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $row->completed_events }}</span></td>
                                <td class="text-center">{{ number_format($row->direct_released_allocations) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries_reached) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_events) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distribution events found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($statusPerBarangay->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Total</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('pending_events') }}</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('ongoing_events') }}</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('completed_events') }}</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('direct_released_allocations') }}</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('direct_beneficiaries_reached') }}</td>
                                <td class="text-center">{{ $statusPerBarangay->sum('total_events') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 4 — Beneficiaries Not Yet Reached --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-person-x me-1"></i> Report 4: Beneficiaries Not Yet Reached</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body border-bottom pb-3">
            <div class="report-chart-wrap" style="max-width: 300px; margin: 0 auto;">
                <canvas id="unreachedBeneficiariesChart"></canvas>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Contact Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unreachedBeneficiaries as $beneficiary)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $beneficiary->full_name }}</td>
                                <td>{{ $beneficiary->barangay->name }}</td>
                                <td>
                                    @php
                                        $classBadge = match($beneficiary->classification) {
                                            'Farmer'     => 'bg-success',
                                            'Fisherfolk' => 'bg-primary',
                                            'Both'       => 'bg-info',
                                            default      => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $classBadge }}">{{ $beneficiary->classification }}</span>
                                </td>
                                <td>{{ $beneficiary->contact_number ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                                    All beneficiaries have been reached.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($unreachedBeneficiaries->count())
            <div class="card-footer bg-white text-muted small">
                {{ $unreachedBeneficiaries->count() }} {{ Str::plural('beneficiary', $unreachedBeneficiaries->count()) }} with no allocations
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 5 — Monthly Summary (Event vs Direct) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-calendar3 me-1"></i> Report 5: Monthly Summary (Event vs Direct, {{ $currentYear }})</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Events</th>
                            <th class="text-center">Event Beneficiaries</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-center">Event Quantity</th>
                            <th class="text-center">Direct Quantity</th>
                            <th class="text-center">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $monthNames = [
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December',
                            ];
                        @endphp
                        @forelse($monthlyDistribution as $row)
                            <tr>
                                <td>{{ $monthNames[$row->month_number] ?? 'Unknown' }}</td>
                                <td class="text-center">{{ number_format($row->total_events) }}</td>
                                <td class="text-center">{{ number_format($row->event_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->event_quantity, 2) }}</td>
                                <td class="text-center">{{ number_format($row->direct_quantity, 2) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_quantity, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distribution data for {{ $currentYear }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($monthlyDistribution->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('total_events')) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('event_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('direct_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('total_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('event_quantity'), 2) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('direct_quantity'), 2) }}</td>
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('total_quantity'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bar Chart --}}
        @if($monthlyDistribution->count())
            <div class="card-body border-top">
                <div class="report-chart-wrap">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 6 — Financial Assistance Summary (Event vs Direct) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-cash-stack me-1"></i> Report 6: Financial Assistance Summary (Event vs Direct)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        @if($financialSummary->count())
            <div class="card-body border-bottom pb-3">
                <div class="report-chart-wrap">
                    <canvas id="financialSummaryChart"></canvas>
                </div>
            </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Assistance Type</th>
                            <th>Source Agency</th>
                            <th class="text-center">Completed Events</th>
                            <th class="text-center">Event Beneficiaries</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-end">Event Amount (PHP)</th>
                            <th class="text-end">Direct Amount (PHP)</th>
                            <th class="text-end">Total Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financialSummary as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td>
                                    @php
                                        $agencyName = $row->agency_name ?? 'N/A';
                                        $agencyBadge = match($agencyName) {
                                            'DA'   => 'bg-success',
                                            'BFAR' => 'bg-primary',
                                            'DAR'  => 'bg-warning text-dark',
                                            'LGU'  => 'bg-secondary',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $agencyBadge }}">{{ $agencyName }}</span>
                                </td>
                                <td class="text-center">{{ number_format($row->total_events) }}</td>
                                <td class="text-center">{{ number_format($row->event_beneficiaries_reached) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries_reached) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_beneficiaries_reached) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->event_amount_disbursed, 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->direct_amount_disbursed, 2) }}</td>
                                <td class="text-end fw-bold">&#8369;{{ number_format($row->total_amount_disbursed, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No financial assistance distribution data yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($financialSummary->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3">Grand Total</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('total_events')) }}</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('event_beneficiaries_reached')) }}</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('direct_beneficiaries_reached')) }}</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('total_beneficiaries_reached')) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialSummary->sum('event_amount_disbursed'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialSummary->sum('direct_amount_disbursed'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialSummary->sum('total_amount_disbursed'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 7 — Financial Assistance per Barangay (Event vs Direct) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-geo-alt me-1"></i> Report 7: Financial Assistance per Barangay (Event vs Direct)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        @if($financialPerBarangay->count())
            <div class="card-body border-bottom pb-3">
                <div class="report-chart-wrap">
                    <canvas id="financialPerBarangayChart"></canvas>
                </div>
            </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th class="text-center">Completed Events</th>
                            <th class="text-center">Event Beneficiaries</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-end">Event Amount (PHP)</th>
                            <th class="text-end">Direct Amount (PHP)</th>
                            <th class="text-end">Total Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financialPerBarangay as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="text-center">{{ number_format($row->total_financial_events) }}</td>
                                <td class="text-center">{{ number_format($row->event_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->event_amount, 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->direct_amount, 2) }}</td>
                                <td class="text-end fw-bold">&#8369;{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No financial assistance distribution data yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($financialPerBarangay->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Grand Total</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('total_financial_events')) }}</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('event_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('direct_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('total_beneficiaries')) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialPerBarangay->sum('event_amount'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialPerBarangay->sum('direct_amount'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialPerBarangay->sum('total_amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 8 — Financial Assistance Distribution by Purpose (Event vs Direct) --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <span class="fw-semibold"><i class="bi bi-cash-coin me-1"></i> Report 8: Financial Assistance Distribution by Purpose (Event vs Direct)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body border-bottom pb-3">
            <div class="report-chart-wrap" style="max-width: 350px; margin: 0 auto;">
                <canvas id="assistanceByPurposeChart"></canvas>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-data-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Purpose</th>
                            <th>Category</th>
                            <th class="text-center">Event Beneficiaries</th>
                            <th class="text-center">Direct Beneficiaries</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-end">Event Amount (PHP)</th>
                            <th class="text-end">Direct Amount (PHP)</th>
                            <th class="text-end">Total Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assistanceByPurpose as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td>
                                    @php
                                        $catBadge = match($row->category) {
                                            'agricultural' => 'bg-success',
                                            'fishery'      => 'bg-primary',
                                            'livelihood'   => 'bg-info',
                                            'medical'      => 'bg-danger',
                                            'emergency'    => 'bg-warning text-dark',
                                            default        => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $catBadge }}">{{ ucfirst($row->category) }}</span>
                                </td>
                                <td class="text-center">{{ number_format($row->event_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->direct_beneficiaries) }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->event_amount, 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->direct_amount, 2) }}</td>
                                <td class="text-end fw-bold">&#8369;{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No distributed assistance data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($assistanceByPurpose->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3">Grand Total</td>
                                <td class="text-center">{{ number_format($assistanceByPurpose->sum('event_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($assistanceByPurpose->sum('direct_beneficiaries')) }}</td>
                                <td class="text-center">{{ number_format($assistanceByPurpose->sum('total_beneficiaries')) }}</td>
                                <td class="text-end">&#8369;{{ number_format($assistanceByPurpose->sum('event_amount'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($assistanceByPurpose->sum('direct_amount'), 2) }}</td>
                                <td class="text-end">&#8369;{{ number_format($assistanceByPurpose->sum('total_amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if($monthlyDistribution->count())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const data = @json($monthlyDistribution);

    const labels = data.map(row => monthNames[row.month_number - 1]);
    const eventBeneficiaries = data.map(row => row.event_beneficiaries);
    const directBeneficiaries = data.map(row => row.direct_beneficiaries);
    const events = data.map(row => row.total_events);
    const directReleases = data.map(row => row.direct_releases);
    const compactViewport = window.matchMedia('(max-width: 575.98px)').matches;

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Event Beneficiaries',
                    data: eventBeneficiaries,
                    backgroundColor: 'rgba(46, 125, 50, 0.7)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Direct Beneficiaries',
                    data: directBeneficiaries,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Events',
                    data: events,
                    backgroundColor: 'rgba(21, 101, 192, 0.7)',
                    borderColor: 'rgba(21, 101, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Direct Releases',
                    data: directReleases,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: compactViewport ? 'bottom' : 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});

// ===== CHART 1: Beneficiaries per Barangay (Horizontal Bar) =====
const barangayCtx = document.getElementById('barangayBeneficiariesChart');
if (barangayCtx) {
    const barangayData = @json($beneficiariesPerBarangay);
    const barangayLabels = barangayData.map(d => d.barangay.name).slice(0, 10);
    const barangayValues = barangayData.map(d => d.grand_total).slice(0, 10);

    new Chart(barangayCtx, {
        type: 'bar',
        data: {
            labels: barangayLabels,
            datasets: [{
                label: 'Total Beneficiaries',
                data: barangayValues,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ===== CHART 2: Resource Distribution (Grouped Bar) =====
const resourceCtx = document.getElementById('resourceDistributionChart');
if (resourceCtx) {
    const resourceData = @json($resourceDistribution);
    const resourceLabels = resourceData.map(d => d.name).slice(0, 10);
    const eventQty = resourceData.map(d => d.event_quantity_distributed).slice(0, 10);
    const directQty = resourceData.map(d => d.direct_quantity_distributed).slice(0, 10);

    new Chart(resourceCtx, {
        type: 'bar',
        data: {
            labels: resourceLabels,
            datasets: [
                {
                    label: 'Event Qty',
                    data: eventQty,
                    backgroundColor: 'rgba(46, 125, 50, 0.6)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Direct Qty',
                    data: directQty,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ===== CHART 3: Distribution Status per Barangay (Stacked Bar) =====
const statusCtx = document.getElementById('statusPerBarangayChart');
if (statusCtx) {
    const statusData = @json($statusPerBarangay);
    const statusLabels = statusData.map(d => d.barangay.name).slice(0, 10);
    const pending = statusData.map(d => d.pending_events).slice(0, 10);
    const ongoing = statusData.map(d => d.ongoing_events).slice(0, 10);
    const completed = statusData.map(d => d.completed_events).slice(0, 10);

    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: statusLabels,
            datasets: [
                {
                    label: 'Pending',
                    data: pending,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Ongoing',
                    data: ongoing,
                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(46, 125, 50, 0.6)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ===== CHART 4: Unreached Beneficiaries (Donut) =====
const unreachedCtx = document.getElementById('unreachedBeneficiariesChart');
if (unreachedCtx) {
    const totalBeneficiaries = {{ $totalBeneficiaries ?? 0 }};
    const unreachedCount = @json($unreachedBeneficiaries->count());
    const reachedCount = totalBeneficiaries ? (totalBeneficiaries - unreachedCount) : 0;

    new Chart(unreachedCtx, {
        type: 'doughnut',
        data: {
            labels: ['Reached', 'Unreached'],
            datasets: [{
                data: [reachedCount, unreachedCount],
                backgroundColor: ['rgba(46, 125, 50, 0.6)', 'rgba(220, 53, 69, 0.6)'],
                borderColor: ['rgba(46, 125, 50, 1)', 'rgba(220, 53, 69, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// ===== CHART 6: Financial Assistance Summary (Horizontal Bar) =====
const financialCtx = document.getElementById('financialSummaryChart');
if (financialCtx) {
    const financialData = @json($financialSummary);
    const financialLabels = financialData.map(d => d.name).slice(0, 10);
    const financialAmounts = financialData.map(d => d.total_amount_disbursed).slice(0, 10);

    new Chart(financialCtx, {
        type: 'bar',
        data: {
            labels: financialLabels,
            datasets: [{
                label: 'Total Amount (PHP)',
                data: financialAmounts,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
}

// ===== CHART 7: Financial per Barangay (Scatter/Bubble) =====
const finBarangayCtx = document.getElementById('financialPerBarangayChart');
if (finBarangayCtx) {
    const finBarangayData = @json($financialPerBarangay);
    const finBarangayLabels = finBarangayData.map(d => d.name);
    const eventAmounts = finBarangayData.map(d => d.event_amount);
    const directAmounts = finBarangayData.map(d => d.direct_amount);

    new Chart(finBarangayCtx, {
        type: 'bar',
        data: {
            labels: finBarangayLabels,
            datasets: [
                {
                    label: 'Event Amount',
                    data: eventAmounts,
                    backgroundColor: 'rgba(46, 125, 50, 0.6)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Direct Amount',
                    data: directAmounts,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { x: { beginAtZero: true } }
        }
    });
}

// ===== CHART 8: Assistance by Purpose (Donut) =====
const purposeCtx = document.getElementById('assistanceByPurposeChart');
if (purposeCtx) {
    const purposeData = @json($assistanceByPurpose);
    const purposeLabels = purposeData.map(d => d.name);
    const purposeAmounts = purposeData.map(d => d.total_amount_disbursed);
    const colors = ['rgba(13, 110, 253, 0.6)', 'rgba(46, 125, 50, 0.6)', 'rgba(220, 53, 69, 0.6)', 'rgba(255, 193, 7, 0.6)', 'rgba(111, 66, 193, 0.6)'];
    const borderColors = ['rgba(13, 110, 253, 1)', 'rgba(46, 125, 50, 1)', 'rgba(220, 53, 69, 1)', 'rgba(255, 193, 7, 1)', 'rgba(111, 66, 193, 1)'];

    new Chart(purposeCtx, {
        type: 'doughnut',
        data: {
            labels: purposeLabels,
            datasets: [{
                data: purposeAmounts,
                backgroundColor: colors.slice(0, purposeLabels.length),
                borderColor: borderColors.slice(0, purposeLabels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}
</script>
@endif
@endpush
