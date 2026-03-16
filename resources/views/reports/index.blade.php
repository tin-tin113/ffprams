@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@push('styles')
<style>
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
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0">Reports</h1>
            <p class="text-muted mb-0">Summary reports and analytics for resource distribution</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 1 — Beneficiaries per Barangay --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-people me-1"></i> Report 1: Beneficiaries per Barangay</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
    {{-- REPORT 2 — Resource Distribution Summary --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-box-seam me-1"></i> Report 2: Resource Distribution Summary (Completed Events)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Resource Type</th>
                            <th>Unit</th>
                            <th>Source Agency</th>
                            <th class="text-center">Total Qty Distributed</th>
                            <th class="text-center">Beneficiaries Reached</th>
                            <th class="text-center">Events</th>
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
                                <td class="text-center">{{ number_format($row->total_quantity_distributed, 2) }}</td>
                                <td class="text-center">{{ number_format($row->total_beneficiaries_reached) }}</td>
                                <td class="text-center">{{ number_format($row->total_events) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
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
    {{-- REPORT 3 — Distribution Status per Barangay --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-bar-chart me-1"></i> Report 3: Distribution Status per Barangay</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Ongoing</th>
                            <th class="text-center">Completed</th>
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
                                <td class="text-center fw-bold">{{ number_format($row->total_events) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
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
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-person-x me-1"></i> Report 4: Beneficiaries Not Yet Reached</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
    {{-- REPORT 5 — Monthly Distribution Summary --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-calendar3 me-1"></i> Report 5: Monthly Distribution Summary ({{ $currentYear }})</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Total Events</th>
                            <th class="text-center">Beneficiaries Served</th>
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
                                <td class="text-center">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-center">{{ number_format($row->total_quantity, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
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
                                <td class="text-center">{{ number_format($monthlyDistribution->sum('total_beneficiaries')) }}</td>
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
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 6 — Financial Assistance Summary --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-cash-stack me-1"></i> Report 6: Financial Assistance Summary (Completed Events)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Assistance Type</th>
                            <th>Source Agency</th>
                            <th class="text-center">Total Events</th>
                            <th class="text-center">Beneficiaries Reached</th>
                            <th class="text-end">Total Amount Disbursed (PHP)</th>
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
                                <td class="text-center">{{ number_format($row->total_beneficiaries_reached) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->total_amount_disbursed, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No completed financial assistance events yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($financialSummary->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3">Grand Total</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('total_events')) }}</td>
                                <td class="text-center">{{ number_format($financialSummary->sum('total_beneficiaries_reached')) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialSummary->sum('total_amount_disbursed'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 7 — Financial Assistance per Barangay --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-geo-alt me-1"></i> Report 7: Financial Assistance per Barangay (Completed Events)</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th class="text-center">Total Financial Events</th>
                            <th class="text-center">Total Beneficiaries</th>
                            <th class="text-end">Total Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financialPerBarangay as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="text-center">{{ number_format($row->total_financial_events) }}</td>
                                <td class="text-center">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No completed financial assistance events yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($financialPerBarangay->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Grand Total</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('total_financial_events')) }}</td>
                                <td class="text-center">{{ number_format($financialPerBarangay->sum('total_beneficiaries')) }}</td>
                                <td class="text-end">&#8369;{{ number_format($financialPerBarangay->sum('total_amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 8 — Field Assessment Activity by Staff --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-clipboard-check me-1"></i> Report 8: Field Assessment Activity by Staff</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th class="text-center">Total Visits</th>
                            <th class="text-center">Eligible</th>
                            <th class="text-center">Not Eligible</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Approved</th>
                            <th class="text-center">Rejected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessmentByStaff as $row)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="text-center fw-bold">{{ number_format($row->total_visits) }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $row->eligible_count }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $row->not_eligible_count }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $row->pending_count }}</span></td>
                                <td class="text-center"><span class="badge bg-primary">{{ $row->approved_count }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $row->rejected_count }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No field assessment data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($assessmentByStaff->count())
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Total</td>
                                <td class="text-center">{{ number_format($assessmentByStaff->sum('total_visits')) }}</td>
                                <td class="text-center">{{ $assessmentByStaff->sum('eligible_count') }}</td>
                                <td class="text-center">{{ $assessmentByStaff->sum('not_eligible_count') }}</td>
                                <td class="text-center">{{ $assessmentByStaff->sum('pending_count') }}</td>
                                <td class="text-center">{{ $assessmentByStaff->sum('approved_count') }}</td>
                                <td class="text-center">{{ $assessmentByStaff->sum('rejected_count') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- REPORT 9 — Financial Assistance Distribution by Purpose --}}
    {{-- ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-cash-coin me-1"></i> Report 9: Financial Assistance Distribution by Purpose</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Purpose</th>
                            <th>Category</th>
                            <th class="text-center">Beneficiaries</th>
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
                                <td class="text-center">{{ number_format($row->total_beneficiaries) }}</td>
                                <td class="text-end">&#8369;{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
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
                                <td class="text-center">{{ number_format($assistanceByPurpose->sum('total_beneficiaries')) }}</td>
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
    const beneficiaries = data.map(row => row.total_beneficiaries);
    const events = data.map(row => row.total_events);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Beneficiaries Served',
                    data: beneficiaries,
                    backgroundColor: 'rgba(46, 125, 50, 0.7)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Events',
                    data: events,
                    backgroundColor: 'rgba(21, 101, 192, 0.7)',
                    borderColor: 'rgba(21, 101, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
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
</script>
@endif
@endpush
