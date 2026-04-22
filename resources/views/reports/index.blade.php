@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports & Analytics</li>
@endsection

@push('styles')
<style>
    .reports-shell {
        padding-bottom: 1.5rem;
    }

    .reports-toolbar {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        position: sticky;
        top: 0.75rem;
        z-index: 20;
    }

    .reports-title {
        font-size: clamp(1.25rem, 2vw, 1.75rem);
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0;
    }

    .reports-subtitle {
        color: #475569;
        margin: 0;
        font-size: 0.92rem;
    }

    .reports-filter,
    .reports-year {
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        min-width: 120px;
        font-size: 0.9rem;
    }

    .reports-tab-scroll {
        overflow: visible;
        padding-bottom: 0;
        margin-top: 1rem;
    }

    .reports-tabs {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.5rem;
        min-width: 0;
        width: 100%;
        margin-top: 0;
    }

    .reports-tabs .nav-item {
        width: 100%;
    }

    .reports-tabs .nav-link {
        border: 1px solid #dbe3ef;
        border-radius: 0.8rem;
        background: #fff;
        color: #334155;
        font-weight: 600;
        padding: 0.62rem 0.65rem;
        white-space: nowrap;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        text-align: center;
    }

    .reports-tabs .nav-link span {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reports-tabs .nav-link:hover {
        border-color: #34a853;
        color: #166534;
    }

    .reports-tabs .nav-link.active {
        background: #0f9f46;
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(15, 159, 70, 0.22);
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        padding: 1rem;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: #94a3b8;
    }

    .kpi-green::before { background: #16a34a; }
    .kpi-blue::before { background: #2563eb; }
    .kpi-amber::before { background: #d97706; }
    .kpi-violet::before { background: #7c3aed; }
    .kpi-navy::before { background: #1e293b; }
    .kpi-cyan::before { background: #0891b2; }
    .kpi-orange::before { background: #ea580c; }
    .kpi-emerald::before { background: #059669; }

    .kpi-label {
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 0.4rem;
    }

    .kpi-value {
        font-size: clamp(1.2rem, 2.4vw, 1.85rem);
        font-weight: 700;
        color: #0f172a;
        line-height: 1.15;
    }

    .kpi-meta {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 0.35rem;
    }

    .summary-card {
        border-radius: 0.95rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .summary-card .card-body {
        padding: 0.95rem;
    }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .insight-card {
        background: #ffffff;
        border: 1px solid #dbe3ef;
        border-radius: 0.9rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        padding: 0.82rem 0.9rem;
    }

    .overview-insights-toggle {
        border: 1px solid #dbe3ef;
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .overview-insights-toggle > summary {
        list-style: none;
        cursor: pointer;
        padding: 0.8rem 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        color: #1e293b;
    }

    .overview-insights-toggle > summary::-webkit-details-marker {
        display: none;
    }

    .overview-insights-toggle > summary::after {
        content: 'Show';
        font-size: 0.78rem;
        color: #64748b;
    }

    .overview-insights-toggle[open] > summary::after {
        content: 'Hide';
    }

    .overview-insights-body {
        border-top: 1px solid #e2e8f0;
        padding: 0.9rem;
    }

    .tab-insights-toggle {
        border: 1px solid #dbe3ef;
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .tab-insights-toggle > summary {
        list-style: none;
        cursor: pointer;
        padding: 0.72rem 0.9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        color: #1e293b;
    }

    .tab-insights-toggle > summary::-webkit-details-marker {
        display: none;
    }

    .tab-insights-toggle > summary::after {
        content: 'Show';
        font-size: 0.78rem;
        color: #64748b;
    }

    .tab-insights-toggle[open] > summary::after {
        content: 'Hide';
    }

    .tab-insights-body {
        border-top: 1px solid #e2e8f0;
        padding: 0.85rem;
    }

    .insight-label {
        font-size: 0.76rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.35rem;
    }

    .insight-value {
        font-size: clamp(1.05rem, 2vw, 1.4rem);
        font-weight: 700;
        color: #0f172a;
        line-height: 1.15;
    }

    .insight-note {
        font-size: 0.82rem;
        color: #475569;
        margin-top: 0.28rem;
    }

    .beneficiary-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .beneficiary-kpi-card {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #dbe3ef;
        border-radius: 0.9rem;
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.06);
        padding: 0.9rem 1rem;
    }

    .beneficiary-kpi-meta { font-size: 0.75rem; color: #64748b; }

    /* Print Optimizations for Unreached List */
    @media print {
        html, body {
            height: auto !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        body > *:not(#unreachedBeneficiariesModal) { 
            display: none !important; 
        }

        #unreachedBeneficiariesModal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 9999 !important;
        }

        .modal {
            position: relative !important;
            overflow: visible !important;
            display: block !important;
            height: auto !important;
        }

        .modal-dialog {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            transform: none !important;
            height: auto !important;
        }

        .modal-content {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            height: auto !important;
            background: #fff !important;
            display: block !important;
        }

        .modal-body {
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
            height: auto !important;
            display: block !important;
        }

        .table-responsive {
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
            height: auto !important;
        }

        .table {
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            margin: 0 !important;
        }

        body.modal-open {
            overflow: visible !important;
            height: auto !important;
        }

        .table th, .table td {
            border: 1px solid #dee2e6 !important;
            padding: 8px !important;
            background: #fff !important;
            color: #000 !important;
        }

        .table thead {
            display: table-header-group !important;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        .bg-light { background-color: #fff !important; border-bottom: 1px solid #000 !important; }
        .text-muted { color: #000 !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; background: none !important; }
        
        @page {
            size: auto;
            margin: 15mm;
        }
    }

    /* Missing Overview & Chart Styles */
    .command-center {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .command-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .command-icon {
        width: 42px;
        height: 42px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .command-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .command-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .command-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
    }

    .command-yoy {
        font-weight: 700;
        padding: 0.15rem 0.4rem;
        border-radius: 0.35rem;
    }

    .yoy-up { background: #dcfce7; color: #166534; }
    .yoy-down { background: #fee2e2; color: #991b1b; }
    .yoy-neutral { background: #f1f5f9; color: #475569; }

    .command-meta { color: #94a3b8; }

    .overview-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
        .overview-chart-grid { grid-template-columns: 1fr; }
    }

    .report-card {
        background: #fff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .report-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }

    .report-card-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .report-chart-wrap {
        position: relative;
        height: 320px;
        width: 100%;
    }

    .compact-donut { height: 260px; }

    .report-data-table th {
        background: #f8fafc;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 0.75rem 1rem;
    }

    .report-data-table td {
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
@php
    $validTabIds = collect($reportTabs)->pluck('id');
    $activeTab = request('tab', 'overview');
    if (! $validTabIds->contains($activeTab)) {
        $activeTab = 'overview';
    }
    $activeTabLabel = collect($reportTabs)->firstWhere('id', $activeTab)['label'] ?? ucfirst($activeTab);
@endphp

<div class="container-fluid reports-shell">
    <div class="print-only mb-2">
        <h2 class="mb-1" style="font-size:16px; font-weight:700;">FFPRAMS Reports and Analytics</h2>
        <div style="font-size:11px; color:#475569;">
            Year: {{ $currentYear }} | Tab: <span id="printTabLabel">{{ $activeTabLabel }}</span> | Mode: <span id="printModeLabel">Standard</span> | Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <div class="card reports-toolbar border-0 no-print modern-filter-card">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div>
                    <h1 class="reports-title">Reports & Analytics</h1>
                    <p class="reports-subtitle">Municipality of Enrique B. Magalona - Farmer-Fisherfolk Resource Allocation</p>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                    <form method="GET" action="{{ route('reports.index') }}" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 modern-filter-grid">
                        <input type="hidden" name="tab" value="{{ $activeTab }}" id="reportTabInput">
                        <select class="form-select reports-filter modern-filter-select" aria-label="Period" disabled>
                            <option selected>Full Year</option>
                        </select>
                        <select class="form-select reports-year modern-filter-select" name="year" aria-label="Year" onchange="this.form.submit()">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" @selected((int) $year === (int) $currentYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </form>

                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                    <button type="button" class="btn btn-danger" id="reportsPdfBtn">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </button>
                    <button type="button" class="btn btn-success" id="reportsExcelBtn">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                    </button>
                    <div class="form-check form-switch ms-md-2 d-flex align-items-center">
                        <input class="form-check-input" type="checkbox" role="switch" id="compactPrintModeToggle">
                        <label class="form-check-label small text-muted ms-2" for="compactPrintModeToggle">Compact Print</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="reports-tab-scroll no-print">
        <ul class="nav nav-pills reports-tabs" id="reportsTabNav" role="tablist">
            @foreach($reportTabs as $tab)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link @if($activeTab === $tab['id']) active @endif"
                        id="reports-tab-{{ $tab['id'] }}"
                        data-bs-toggle="pill"
                        data-bs-target="#reports-pane-{{ $tab['id'] }}"
                        data-report-tab="{{ $tab['id'] }}"
                        type="button"
                        role="tab"
                        aria-controls="reports-pane-{{ $tab['id'] }}"
                        aria-selected="{{ $activeTab === $tab['id'] ? 'true' : 'false' }}"
                    >
                        <i class="bi {{ $tab['icon'] }}"></i>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content pt-3" id="reportsTabContent">
        @include('reports.partials.overview')
        @include('reports.partials.beneficiary')
        @include('reports.partials.allocation')
        @include('reports.partials.financial')
        @include('reports.partials.barangay')
        @include('reports.partials.agency')
        @include('reports.partials.program')
    <!-- Modal: Unreached Beneficiaries -->
    <div class="modal fade" id="unreachedBeneficiariesModal" tabindex="-1" aria-labelledby="unreachedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title d-flex align-items-center" id="unreachedModalLabel">
                        <i class="bi bi-person-x me-2 fs-4"></i> 
                        Priority Outreach List ({{ number_format($unreachedTotal) }})
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 bg-light border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <p class="mb-0 text-muted">
                                    Below are the registered beneficiaries who have <strong>not yet received</strong> any resources for the year {{ $selectedYear }}. 
                                    This list is sorted by barangay to help you plan group distributions.
                                </p>
                            </div>
                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-danger p-2 px-3 rounded-pill">Priority Outreach Required</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-white sticky-top shadow-sm" style="z-index: 10;">
                                <tr>
                                    <th class="ps-4">Beneficiary</th>
                                    <th>Classification</th>
                                    <th>Barangay</th>
                                    <th>Contact Info</th>
                                    <th>RSBSA/Control #</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unreachedBeneficiaries as $beneficiary)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $beneficiary->full_name }}</div>
                                            <div class="small text-muted">Registered: {{ $beneficiary->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $classBadge = match($beneficiary->classification) {
                                                    'Farmer' => 'bg-success',
                                                    'Fisherfolk' => 'bg-primary',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $classBadge }}">{{ $beneficiary->classification }}</span>
                                        </td>
                                        <td>{{ $beneficiary->barangay->name }}</td>
                                        <td>
                                            <i class="bi bi-telephone text-primary me-1"></i>
                                            {{ $beneficiary->contact_number ?? 'No contact info' }}
                                        </td>
                                        <td class="font-monospace small">
                                            {{ $beneficiary->id_number ?? 'N/A' }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('beneficiaries.show', $beneficiary) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Profile
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-check2-circle fs-1 text-success d-block mb-3"></i>
                                            All registered beneficiaries have been reached successfully!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    @if($unreachedBeneficiaries->count())
                        <button type="button" class="btn btn-success rounded-pill px-4" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Strategy List
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartRuntimeUrl = "{{ asset('vendor/chartjs/chart.umd.js') }}";

    function ensureChartJs() {
        if (typeof window.Chart !== 'undefined') {
            return Promise.resolve(true);
        }

        return new Promise(function (resolve) {
            const existing = document.querySelector('script[data-chartjs-runtime="1"]');
            if (existing) {
                existing.addEventListener('load', function () {
                    resolve(typeof window.Chart !== 'undefined');
                }, { once: true });
                existing.addEventListener('error', function () {
                    resolve(false);
                }, { once: true });
                return;
            }

            const runtimeScript = document.createElement('script');
            runtimeScript.src = chartRuntimeUrl;
            runtimeScript.async = true;
            runtimeScript.setAttribute('data-chartjs-runtime', '1');
            runtimeScript.onload = function () {
                resolve(typeof window.Chart !== 'undefined');
            };
            runtimeScript.onerror = function () {
                resolve(false);
            };
            document.head.appendChild(runtimeScript);
        });
    }

    const toNumber = function (value) {
        return Number(value || 0);
    };

    const monthlyData = @json($monthlyDistribution->values());
    const beneficiariesByBarangayData = @json($beneficiariesPerBarangay->values());
    const resourceDistributionData = @json($resourceDistribution->values());
    const statusPerBarangayData = @json($statusPerBarangay->values());
    const totalBeneficiaries = {{ $totalBeneficiaries ?? 0 }};
    const unreachedCount = @json($unreachedBeneficiaries->count());
    const financialSummaryData = @json($financialSummary->values());
    const financialPerBarangayData = @json($financialPerBarangay->values());
    const assistanceByPurposeData = @json($assistanceByPurpose->values());
    const beneficiaryMixData = @json($beneficiaryMixRows->values());
    const beneficiaryPriorityData = @json($unreachedByBarangay->take(10)->values());
    const barangayInsightsData = @json($barangayInsights->values());
    const agencySummaryData = @json($agencySummary->values());
    const programCategoryData = @json($programCategorySummary->values());
    const complianceOverviewData = @json($complianceOverview);
    const classificationReachData = @json($beneficiaryClassificationReach->values());

    const chartInstances = {};

    function createChartIfNeeded(canvasId, createFn) {
        if (typeof Chart === 'undefined') {
            const canvasMissingLib = document.getElementById(canvasId);
            if (canvasMissingLib && canvasMissingLib.parentElement) {
                canvasMissingLib.parentElement.insertAdjacentHTML(
                    'beforeend',
                    '<div class="text-muted small mt-2">Chart library failed to load.</div>'
                );
            }
            return;
        }

        if (chartInstances[canvasId]) {
            return;
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            return;
        }

        chartInstances[canvasId] = createFn(canvas);

        if (!chartInstances[canvasId] && canvas.parentElement) {
            canvas.parentElement.insertAdjacentHTML(
                'beforeend',
                '<div class="text-muted small mt-2">No data available for this chart in the selected year.</div>'
            );
        }
    }

    function topRowsBy(rows, field, limit) {
        return rows
            .slice()
            .sort(function (a, b) {
                return toNumber(b[field]) - toNumber(a[field]);
            })
            .slice(0, limit);
    }

    function initializeMonthlyChart() {
        if (!monthlyData.length) {
            return;
        }

        createChartIfNeeded('monthlyChart', function (canvas) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const labels = monthlyData.map(function (row) {
                return monthNames[(row.month_number || 1) - 1];
            });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Event Beneficiaries',
                            data: monthlyData.map(function (row) { return toNumber(row.event_beneficiaries); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Beneficiaries',
                            data: monthlyData.map(function (row) { return toNumber(row.direct_beneficiaries); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.7)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Total Events',
                            data: monthlyData.map(function (row) { return toNumber(row.total_events); }),
                            backgroundColor: 'rgba(15, 23, 42, 0.65)',
                            borderColor: 'rgba(15, 23, 42, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Releases',
                            data: monthlyData.map(function (row) { return toNumber(row.direct_releases); }),
                            backgroundColor: 'rgba(217, 119, 6, 0.7)',
                            borderColor: 'rgba(217, 119, 6, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: window.matchMedia('(max-width: 575.98px)').matches ? 'bottom' : 'top'
                        }
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
    }

    function initializeOverviewComplianceRiskChart() {
        const totalFinancialEvents = toNumber(complianceOverviewData.financial_events_total);
        const missingLegalBasis = toNumber(complianceOverviewData.missing_legal_basis);
        const liquidationPending = toNumber(complianceOverviewData.liquidation_pending);
        const liquidationOverdue = toNumber(complianceOverviewData.liquidation_overdue);
        const farmcPending = toNumber(complianceOverviewData.farmc_required_pending);

        if (!totalFinancialEvents && !missingLegalBasis && !liquidationPending && !liquidationOverdue && !farmcPending) {
            return;
        }

        createChartIfNeeded('overviewComplianceRiskChart', function (canvas) {
            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: ['Missing Legal Basis', 'Pending Liquidation', 'Overdue Liquidation', 'FARMC Pending'],
                    datasets: [
                        {
                            label: 'Events',
                            data: [missingLegalBasis, liquidationPending, liquidationOverdue, farmcPending],
                            backgroundColor: [
                                'rgba(220, 53, 69, 0.72)',
                                'rgba(217, 119, 6, 0.72)',
                                'rgba(153, 27, 27, 0.72)',
                                'rgba(37, 99, 235, 0.72)'
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Total financial events: ' + totalFinancialEvents
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeOverviewChannelSplitChart() {
        const eventQty = resourceDistributionData.reduce(function (sum, row) {
            return sum + toNumber(row.event_quantity_distributed);
        }, 0);
        const directQty = resourceDistributionData.reduce(function (sum, row) {
            return sum + toNumber(row.direct_quantity_distributed);
        }, 0);

        if (eventQty <= 0 && directQty <= 0) {
            return;
        }

        createChartIfNeeded('overviewChannelSplitChart', function (canvas) {
            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Event Channel', 'Direct Channel'],
                    datasets: [
                        {
                            data: [eventQty, directQty],
                            backgroundColor: ['rgba(22, 163, 74, 0.74)', 'rgba(37, 99, 235, 0.74)'],
                            borderColor: ['rgba(22, 163, 74, 1)', 'rgba(37, 99, 235, 1)'],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    }

    function initializeOverviewReachTrendChart() {
        if (!monthlyData.length) {
            return;
        }

        createChartIfNeeded('overviewReachTrendChart', function (canvas) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const labels = monthlyData.map(function (row) {
                return monthNames[(row.month_number || 1) - 1];
            });

            let cumulative = 0;
            const monthlyReach = monthlyData.map(function (row) {
                return toNumber(row.total_beneficiaries);
            });
            const cumulativeReach = monthlyReach.map(function (value) {
                cumulative += value;
                return cumulative;
            });

            return new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Monthly Reach',
                            data: monthlyReach,
                            borderColor: 'rgba(37, 99, 235, 1)',
                            backgroundColor: 'rgba(37, 99, 235, 0.15)',
                            fill: true,
                            tension: 0.35,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Cumulative Reach',
                            data: cumulativeReach,
                            borderColor: 'rgba(217, 119, 6, 1)',
                            backgroundColor: 'rgba(217, 119, 6, 0.12)',
                            tension: 0.35,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });
    }

    function initializeOverviewTopResourcesChart() {
        if (!resourceDistributionData.length) {
            return;
        }

        createChartIfNeeded('overviewTopResourcesChart', function (canvas) {
            const rows = topRowsBy(resourceDistributionData, 'total_quantity_distributed', 8);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.name; }),
                    datasets: [
                        {
                            label: 'Total Quantity',
                            data: rows.map(function (row) { return toNumber(row.total_quantity_distributed); }),
                            backgroundColor: 'rgba(14, 165, 233, 0.72)',
                            borderColor: 'rgba(14, 165, 233, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }

    function initializeOverviewFinancialFlowChart() {
        if (!financialSummaryData.length) {
            return;
        }

        createChartIfNeeded('overviewFinancialFlowChart', function (canvas) {
            const rows = topRowsBy(financialSummaryData, 'total_amount_disbursed', 8);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.name; }),
                    datasets: [
                        {
                            label: 'Event Amount',
                            data: rows.map(function (row) { return toNumber(row.event_amount_disbursed); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.74)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Amount',
                            data: rows.map(function (row) { return toNumber(row.direct_amount_disbursed); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.74)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }

    function initializeBeneficiariesChart() {
        if (!beneficiariesByBarangayData.length) {
            return;
        }

        createChartIfNeeded('barangayBeneficiariesChart', function (canvas) {
            const rows = beneficiariesByBarangayData
                .slice()
                .sort(function (a, b) {
                    return toNumber(b.grand_total) - toNumber(a.grand_total);
                })
                .slice(0, 10);

            const labels = rows
                .map(function (row) {
                    return row.barangay && row.barangay.name ? row.barangay.name : 'Unknown';
                });

            const values = rows
                .map(function (row) {
                    return toNumber(row.grand_total);
                });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Beneficiaries',
                            data: values,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeBeneficiaryCompositionByBarangayChart() {
        if (!beneficiariesByBarangayData.length) {
            return;
        }

        createChartIfNeeded('beneficiaryCompositionByBarangayChart', function (canvas) {
            const rows = topRowsBy(beneficiariesByBarangayData, 'grand_total', 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) {
                        return row.barangay && row.barangay.name ? row.barangay.name : 'Unknown';
                    }),
                    datasets: [
                        {
                            label: 'Farmers',
                            data: rows.map(function (row) { return toNumber(row.total_farmers); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.72)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Fisherfolk',
                            data: rows.map(function (row) { return toNumber(row.total_fisherfolk); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Both',
                            data: rows.map(function (row) { return toNumber(row.total_both); }),
                            backgroundColor: 'rgba(14, 165, 233, 0.72)',
                            borderColor: 'rgba(14, 165, 233, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        });
    }

    function initializeClassificationReachChart() {
        if (!classificationReachData.length) {
            return;
        }

        createChartIfNeeded('classificationReachChart', function (canvas) {
            const labels = classificationReachData.map(function(row) { return row.label; });
            const reachedData = classificationReachData.map(function(row) { return toNumber(row.reached); });
            const unreachedData = classificationReachData.map(function(row) { return toNumber(row.unreached); });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Reached',
                            data: reachedData,
                            backgroundColor: 'rgba(22, 163, 74, 0.75)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Unreached',
                            data: unreachedData,
                            backgroundColor: 'rgba(220, 38, 38, 0.15)',
                            borderColor: 'rgba(220, 38, 38, 0.5)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                footer: (tooltipItems) => {
                                    const index = tooltipItems[0].dataIndex;
                                    const row = classificationReachData[index];
                                    return `Reach Rate: ${toNumber(row.reach_rate).toFixed(1)}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { 
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeResourceDistributionChart() {
        if (!resourceDistributionData.length) {
            return;
        }

        createChartIfNeeded('resourceDistributionChart', function (canvas) {
            const rows = topRowsBy(resourceDistributionData, 'total_quantity_distributed', 10);
            const labels = rows.map(function (row) { return row.name; });
            const eventQty = rows.map(function (row) { return toNumber(row.event_quantity_distributed); });
            const directQty = rows.map(function (row) { return toNumber(row.direct_quantity_distributed); });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Event Qty',
                            data: eventQty,
                            backgroundColor: 'rgba(22, 163, 74, 0.65)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Qty',
                            data: directQty,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
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
    }

    function initializeAllocationReachByResourceChart() {
        if (!resourceDistributionData.length) {
            return;
        }

        createChartIfNeeded('allocationReachByResourceChart', function (canvas) {
            const rows = topRowsBy(resourceDistributionData, 'total_beneficiaries_reached', 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.name; }),
                    datasets: [
                        {
                            label: 'Event Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.event_beneficiaries_reached); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.72)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.direct_beneficiaries_reached); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeAllocationMonthlyReachChart() {
        if (!monthlyData.length) {
            return;
        }

        createChartIfNeeded('allocationMonthlyReachChart', function (canvas) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const labels = monthlyData.map(function (row) {
                return monthNames[(row.month_number || 1) - 1];
            });

            return new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Event Beneficiaries',
                            data: monthlyData.map(function (row) { return toNumber(row.event_beneficiaries); }),
                            borderColor: 'rgba(22, 163, 74, 1)',
                            backgroundColor: 'rgba(22, 163, 74, 0.18)',
                            pointBackgroundColor: 'rgba(22, 163, 74, 1)',
                            fill: true,
                            tension: 0.35
                        },
                        {
                            label: 'Direct Beneficiaries',
                            data: monthlyData.map(function (row) { return toNumber(row.direct_beneficiaries); }),
                            borderColor: 'rgba(37, 99, 235, 1)',
                            backgroundColor: 'rgba(37, 99, 235, 0.16)',
                            pointBackgroundColor: 'rgba(37, 99, 235, 1)',
                            fill: true,
                            tension: 0.35
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Beneficiary Reach by Channel'
                        }
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
    }

    function initializeAllocationMonthlyQuantityChart() {
        if (!monthlyData.length) {
            return;
        }

        createChartIfNeeded('allocationMonthlyQuantityChart', function (canvas) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const labels = monthlyData.map(function (row) {
                return monthNames[(row.month_number || 1) - 1];
            });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Event Quantity',
                            data: monthlyData.map(function (row) { return toNumber(row.event_quantity); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.72)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Quantity',
                            data: monthlyData.map(function (row) { return toNumber(row.direct_quantity); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Allocation Quantity by Channel'
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }

    function initializeStatusPerBarangayChart() {
        if (!statusPerBarangayData.length) {
            return;
        }

        createChartIfNeeded('statusPerBarangayChart', function (canvas) {
            const rows = topRowsBy(statusPerBarangayData, 'total_events', 10);

            const labels = rows
                .map(function (row) {
                    return row.barangay && row.barangay.name ? row.barangay.name : 'Unknown';
                });

            const pending = rows.map(function (row) { return toNumber(row.pending_events); });
            const ongoing = rows.map(function (row) { return toNumber(row.ongoing_events); });
            const completed = rows.map(function (row) { return toNumber(row.completed_events); });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pending',
                            data: pending,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Ongoing',
                            data: ongoing,
                            backgroundColor: 'rgba(217, 119, 6, 0.65)',
                            borderColor: 'rgba(217, 119, 6, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Completed',
                            data: completed,
                            backgroundColor: 'rgba(22, 163, 74, 0.65)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeUnreachedChart() {
        createChartIfNeeded('unreachedBeneficiariesChart', function (canvas) {
            const reachedCount = totalBeneficiaries ? (totalBeneficiaries - unreachedCount) : 0;

            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Reached', 'Unreached'],
                    datasets: [
                        {
                            data: [reachedCount, unreachedCount],
                            backgroundColor: ['rgba(22, 163, 74, 0.65)', 'rgba(220, 53, 69, 0.65)'],
                            borderColor: ['rgba(22, 163, 74, 1)', 'rgba(220, 53, 69, 1)'],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    }

    function initializeBeneficiaryMixChart() {
        if (!beneficiaryMixData.length) {
            return;
        }

        const totalMix = beneficiaryMixData.reduce(function (sum, row) {
            return sum + toNumber(row.value);
        }, 0);

        if (!totalMix) {
            return;
        }

        createChartIfNeeded('beneficiaryMixChart', function (canvas) {
            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: beneficiaryMixData.map(function (row) { return row.label; }),
                    datasets: [
                        {
                            data: beneficiaryMixData.map(function (row) { return toNumber(row.value); }),
                            backgroundColor: beneficiaryMixData.map(function (row) { return row.color || 'rgba(148,163,184,0.75)'; }),
                            borderColor: beneficiaryMixData.map(function (row) { return row.color || 'rgba(148,163,184,1)'; }),
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    }

    function initializeBeneficiaryPriorityChart() {
        if (!beneficiaryPriorityData.length) {
            return;
        }

        createChartIfNeeded('beneficiaryPriorityChart', function (canvas) {
            const rows = beneficiaryPriorityData.slice(0, 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.barangay_name; }),
                    datasets: [
                        {
                            label: 'Unreached Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.count); }),
                            backgroundColor: 'rgba(220, 53, 69, 0.65)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    }

    function initializeFinancialSummaryChart() {
        if (!financialSummaryData.length) {
            return;
        }

        createChartIfNeeded('financialSummaryChart', function (canvas) {
            const rows = topRowsBy(financialSummaryData, 'total_amount_disbursed', 10);
            const labels = rows.map(function (row) { return row.name; });
            const values = rows.map(function (row) { return toNumber(row.total_amount_disbursed); });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Amount (PHP)',
                            data: values,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        });
    }

    function initializeFinancialChannelMixChart() {
        if (!financialSummaryData.length) {
            return;
        }

        createChartIfNeeded('financialChannelMixChart', function (canvas) {
            const eventAmount = financialSummaryData.reduce(function (sum, row) {
                return sum + toNumber(row.event_amount_disbursed);
            }, 0);
            const directAmount = financialSummaryData.reduce(function (sum, row) {
                return sum + toNumber(row.direct_amount_disbursed);
            }, 0);

            if (eventAmount <= 0 && directAmount <= 0) {
                return null;
            }

            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Event', 'Direct'],
                    datasets: [
                        {
                            data: [eventAmount, directAmount],
                            backgroundColor: ['rgba(22, 163, 74, 0.72)', 'rgba(37, 99, 235, 0.72)'],
                            borderColor: ['rgba(22, 163, 74, 1)', 'rgba(37, 99, 235, 1)'],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    }

    function initializeFinancialPerBarangayChart() {
        if (!financialPerBarangayData.length) {
            return;
        }

        createChartIfNeeded('financialPerBarangayChart', function (canvas) {
            const rows = topRowsBy(financialPerBarangayData, 'total_amount', 10);
            const labels = rows.map(function (row) { return row.name; });
            const eventAmounts = rows.map(function (row) { return toNumber(row.event_amount); });
            const directAmounts = rows.map(function (row) { return toNumber(row.direct_amount); });

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Event Amount',
                            data: eventAmounts,
                            backgroundColor: 'rgba(22, 163, 74, 0.65)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Amount',
                            data: directAmounts,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        });
    }

    function initializeBarangayPerformanceChart() {
        if (!barangayInsightsData.length) {
            return;
        }

        createChartIfNeeded('barangayPerformanceChart', function (canvas) {
            const rows = topRowsBy(barangayInsightsData, 'financial_amount', 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.barangay_name; }),
                    datasets: [
                        {
                            label: 'Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.beneficiaries_total); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Financial Amount (PHP)',
                            data: rows.map(function (row) { return toNumber(row.financial_amount); }),
                            type: 'line',
                            tension: 0.35,
                            borderColor: 'rgba(22, 163, 74, 1)',
                            backgroundColor: 'rgba(22, 163, 74, 0.2)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });
    }

    function initializeBarangayEventMixChart() {
        if (!barangayInsightsData.length) {
            return;
        }

        createChartIfNeeded('barangayEventMixChart', function (canvas) {
            const rows = topRowsBy(barangayInsightsData, 'total_events', 10)
                .filter(function (row) {
                    return toNumber(row.total_events) > 0;
                });

            if (!rows.length) {
                return null;
            }

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.barangay_name; }),
                    datasets: [
                        {
                            label: 'Pending',
                            data: rows.map(function (row) { return toNumber(row.pending_events); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Ongoing',
                            data: rows.map(function (row) { return toNumber(row.ongoing_events); }),
                            backgroundColor: 'rgba(217, 119, 6, 0.72)',
                            borderColor: 'rgba(217, 119, 6, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Completed',
                            data: rows.map(function (row) { return toNumber(row.completed_events); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.72)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        });
    }

    function initializeAgencyContributionChart() {
        if (!agencySummaryData.length) {
            return;
        }

        createChartIfNeeded('agencyContributionChart', function (canvas) {
            const rows = topRowsBy(agencySummaryData, 'financial_amount', 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.agency_name; }),
                    datasets: [
                        {
                            label: 'Beneficiaries Reached',
                            data: rows.map(function (row) { return toNumber(row.beneficiaries_reached); }),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Financial Amount (PHP)',
                            data: rows.map(function (row) { return toNumber(row.financial_amount); }),
                            type: 'line',
                            tension: 0.35,
                            borderColor: 'rgba(217, 119, 6, 1)',
                            backgroundColor: 'rgba(217, 119, 6, 0.2)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });
    }

    function initializeAgencyOperationsMixChart() {
        if (!agencySummaryData.length) {
            return;
        }

        createChartIfNeeded('agencyOperationsMixChart', function (canvas) {
            const rows = topRowsBy(agencySummaryData, 'completed_events', 10);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.agency_name; }),
                    datasets: [
                        {
                            label: 'Completed Events',
                            data: rows.map(function (row) { return toNumber(row.completed_events); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Resource Quantity',
                            data: rows.map(function (row) { return toNumber(row.resource_quantity); }),
                            type: 'line',
                            tension: 0.35,
                            borderColor: 'rgba(22, 163, 74, 1)',
                            backgroundColor: 'rgba(22, 163, 74, 0.2)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });
    }

    function initializeAgencyFinancialShareChart() {
        if (!agencySummaryData.length) {
            return;
        }

        createChartIfNeeded('agencyFinancialShareChart', function (canvas) {
            const rows = topRowsBy(agencySummaryData, 'financial_amount', 8)
                .filter(function (row) {
                    return toNumber(row.financial_amount) > 0;
                });

            if (!rows.length) {
                return null;
            }

            const labels = rows.map(function (row) { return row.agency_name || 'N/A'; });
            const values = rows.map(function (row) { return toNumber(row.financial_amount); });
            const palette = [
                'rgba(37, 99, 235, 0.7)',
                'rgba(22, 163, 74, 0.7)',
                'rgba(217, 119, 6, 0.7)',
                'rgba(220, 53, 69, 0.7)',
                'rgba(14, 165, 233, 0.7)',
                'rgba(124, 58, 237, 0.7)',
                'rgba(245, 158, 11, 0.7)',
                'rgba(100, 116, 139, 0.7)'
            ];

            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: values,
                            backgroundColor: labels.map(function (_, index) { return palette[index % palette.length]; }),
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    }

    function initializeProgramCategoryChart() {
        if (!programCategoryData.length) {
            return;
        }

        createChartIfNeeded('programCategoryChart', function (canvas) {
            const rows = topRowsBy(programCategoryData, 'amount', 8);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) {
                        if (!row.category) {
                            return 'Uncategorized';
                        }

                        return String(row.category).charAt(0).toUpperCase() + String(row.category).slice(1);
                    }),
                    datasets: [
                        {
                            label: 'Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.beneficiaries); }),
                            backgroundColor: 'rgba(14, 165, 233, 0.7)',
                            borderColor: 'rgba(14, 165, 233, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Amount (PHP)',
                            data: rows.map(function (row) { return toNumber(row.amount); }),
                            type: 'line',
                            tension: 0.35,
                            borderColor: 'rgba(22, 163, 74, 1)',
                            backgroundColor: 'rgba(22, 163, 74, 0.25)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        });
    }

    function initializeProgramEventDirectChart() {
        if (!assistanceByPurposeData.length) {
            return;
        }

        createChartIfNeeded('programEventDirectChart', function (canvas) {
            const rows = topRowsBy(assistanceByPurposeData, 'total_amount', 8);

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.name; }),
                    datasets: [
                        {
                            label: 'Event Amount (PHP)',
                            data: rows.map(function (row) { return toNumber(row.event_amount); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Amount (PHP)',
                            data: rows.map(function (row) { return toNumber(row.direct_amount); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.7)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }

    function initializeProgramBeneficiaryReachChart() {
        if (!assistanceByPurposeData.length) {
            return;
        }

        createChartIfNeeded('programBeneficiaryReachChart', function (canvas) {
            const rows = topRowsBy(assistanceByPurposeData, 'total_beneficiaries', 10)
                .filter(function (row) {
                    return toNumber(row.total_beneficiaries) > 0;
                });

            if (!rows.length) {
                return null;
            }

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: rows.map(function (row) { return row.name; }),
                    datasets: [
                        {
                            label: 'Event Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.event_beneficiaries); }),
                            backgroundColor: 'rgba(22, 163, 74, 0.72)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Direct Beneficiaries',
                            data: rows.map(function (row) { return toNumber(row.direct_beneficiaries); }),
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        });
    }

    function initializePurposeChart() {
        if (!assistanceByPurposeData.length) {
            return;
        }

        createChartIfNeeded('assistanceByPurposeChart', function (canvas) {
            const rankedRows = topRowsBy(assistanceByPurposeData, 'total_amount', 8);
            const remainingTotal = assistanceByPurposeData
                .slice()
                .sort(function (a, b) {
                    return toNumber(b.total_amount) - toNumber(a.total_amount);
                })
                .slice(8)
                .reduce(function (sum, row) {
                    return sum + toNumber(row.total_amount);
                }, 0);

            const labels = rankedRows.map(function (row) { return row.name; });
            const amounts = rankedRows.map(function (row) { return toNumber(row.total_amount); });

            if (remainingTotal > 0) {
                labels.push('Others');
                amounts.push(remainingTotal);
            }

            const palette = [
                ['rgba(37, 99, 235, 0.65)', 'rgba(37, 99, 235, 1)'],
                ['rgba(22, 163, 74, 0.65)', 'rgba(22, 163, 74, 1)'],
                ['rgba(220, 53, 69, 0.65)', 'rgba(220, 53, 69, 1)'],
                ['rgba(217, 119, 6, 0.65)', 'rgba(217, 119, 6, 1)'],
                ['rgba(124, 58, 237, 0.65)', 'rgba(124, 58, 237, 1)']
            ];

            const backgroundColor = labels.map(function (_, index) {
                return palette[index % palette.length][0];
            });

            const borderColor = labels.map(function (_, index) {
                return palette[index % palette.length][1];
            });

            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: amounts,
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    }

    const tabChartInitializers = {
        overview: [
            initializeOverviewChannelSplitChart,
            initializeOverviewReachTrendChart,
            initializeOverviewTopResourcesChart,
            initializeOverviewFinancialFlowChart,
            initializeOverviewComplianceRiskChart,
            initializeMonthlyChart
        ],
        beneficiary: [initializeBeneficiariesChart, initializeBeneficiaryCompositionByBarangayChart, initializeUnreachedChart, initializeBeneficiaryMixChart, initializeBeneficiaryPriorityChart],
        allocation: [initializeResourceDistributionChart, initializeAllocationReachByResourceChart, initializeAllocationMonthlyReachChart, initializeAllocationMonthlyQuantityChart, initializeStatusPerBarangayChart],
        financial: [initializeFinancialSummaryChart, initializeFinancialChannelMixChart, initializeFinancialPerBarangayChart],
        barangay: [initializeBarangayPerformanceChart, initializeBarangayEventMixChart],
        agency: [initializeAgencyContributionChart, initializeAgencyFinancialShareChart, initializeAgencyOperationsMixChart],
        program: [initializePurposeChart, initializeProgramCategoryChart, initializeProgramEventDirectChart, initializeProgramBeneficiaryReachChart]
    };

    function initializeChartsForTab(tabKey) {
        const initializers = tabChartInitializers[tabKey] || [];
        initializers.forEach(function (fn) {
            try {
                fn();
            } catch (error) {
                console.error('Chart initialization failed for tab:', tabKey, error);
            }
        });
    }

    function resizeCharts() {
        Object.keys(chartInstances).forEach(function (chartId) {
            if (chartInstances[chartId] && typeof chartInstances[chartId].resize === 'function') {
                chartInstances[chartId].resize();
            }
        });
    }

    const reportTabInput = document.getElementById('reportTabInput');
    const tabButtons = document.querySelectorAll('#reportsTabNav [data-bs-toggle="pill"]');
    const printTabLabel = document.getElementById('printTabLabel');
    const printModeLabel = document.getElementById('printModeLabel');
    const compactPrintModeToggle = document.getElementById('compactPrintModeToggle');
    const compactPrintStorageKey = 'reportsCompactPrintMode';

    function applyCompactPrintMode(isEnabled) {
        document.body.classList.toggle('print-compact-mode', Boolean(isEnabled));

        if (compactPrintModeToggle) {
            compactPrintModeToggle.checked = Boolean(isEnabled);
        }

        if (printModeLabel) {
            printModeLabel.textContent = isEnabled ? 'Compact' : 'Standard';
        }
    }

    let compactPrintModeEnabled = false;
    try {
        compactPrintModeEnabled = window.localStorage.getItem(compactPrintStorageKey) === '1';
    } catch (error) {
        compactPrintModeEnabled = false;
    }

    applyCompactPrintMode(compactPrintModeEnabled);

    if (compactPrintModeToggle) {
        compactPrintModeToggle.addEventListener('change', function () {
            const isEnabled = compactPrintModeToggle.checked;
            applyCompactPrintMode(isEnabled);

            try {
                window.localStorage.setItem(compactPrintStorageKey, isEnabled ? '1' : '0');
            } catch (error) {
                // Ignore storage errors in restricted browsing contexts.
            }
        });
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('shown.bs.tab', function (event) {
            const tabKey = event.target.getAttribute('data-report-tab') || 'overview';

            if (reportTabInput) {
                reportTabInput.value = tabKey;
            }

            if (printTabLabel) {
                const tabText = event.target.querySelector('span');
                printTabLabel.textContent = tabText ? tabText.textContent.trim() : tabKey;
            }

            ensureChartJs().then(function () {
                initializeChartsForTab(tabKey);
                resizeCharts();
            });

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabKey);
            history.replaceState({}, '', url.toString());
        });
    });

    const initiallyActiveButton = document.querySelector('#reportsTabNav .nav-link.active');
    const initialTabKey = initiallyActiveButton
        ? initiallyActiveButton.getAttribute('data-report-tab')
        : 'overview';

    if (reportTabInput) {
        reportTabInput.value = initialTabKey;
    }

    if (printTabLabel && initiallyActiveButton) {
        const activeText = initiallyActiveButton.querySelector('span');
        printTabLabel.textContent = activeText ? activeText.textContent.trim() : initialTabKey;
    }

    ensureChartJs().then(function () {
        initializeChartsForTab(initialTabKey);
    });

    window.addEventListener('resize', function () {
        resizeCharts();
    });

    window.addEventListener('beforeprint', function () {
        applyCompactPrintMode(compactPrintModeToggle ? compactPrintModeToggle.checked : compactPrintModeEnabled);
        resizeCharts();
    });

    window.addEventListener('afterprint', function () {
        setTimeout(function () {
            resizeCharts();
        }, 50);
    });

    const pdfButton = document.getElementById('reportsPdfBtn');
    if (pdfButton) {
        pdfButton.addEventListener('click', async function () {
            const activePane = document.querySelector('#reportsTabContent .tab-pane.active');

            if (!activePane) {
                window.alert('No active report tab available for PDF export.');
                return;
            }

            if (typeof html2pdf === 'undefined') {
                window.alert('PDF export library failed to load. Please refresh the page and try again.');
                return;
            }

            const tabKey = activePane.getAttribute('data-report-pane') || 'report';
            const now = new Date();
            const generatedAt = now.toLocaleString();

            const exportWrapper = document.createElement('div');
            exportWrapper.style.position = 'absolute';
            exportWrapper.style.left = '0';
            exportWrapper.style.top = '0';
            exportWrapper.style.zIndex = '-1';
            exportWrapper.style.pointerEvents = 'none';
            exportWrapper.style.width = '1120px';
            exportWrapper.style.background = '#ffffff';
            exportWrapper.style.color = '#111827';
            exportWrapper.style.padding = '20px';
            exportWrapper.style.boxSizing = 'border-box';
            exportWrapper.setAttribute('aria-hidden', 'true');

            const header = document.createElement('div');
            header.style.marginBottom = '12px';
            header.innerHTML =
                '<h2 style="margin:0 0 4px 0;font-size:20px;font-weight:700;">FFPRAMS Reports and Analytics</h2>' +
                '<div style="font-size:12px;color:#475569;">Tab: ' + tabKey + ' | Year: {{ $currentYear }} | Generated: ' + generatedAt + '</div>';

            const exportPane = activePane.cloneNode(true);
            exportPane.classList.remove('tab-pane', 'fade');
            exportPane.style.display = 'block';
            exportPane.style.opacity = '1';

            exportPane.querySelectorAll('.table-responsive').forEach(function (el) {
                el.style.overflow = 'visible';
            });

            exportPane.querySelectorAll('.report-data-table, .table').forEach(function (el) {
                el.style.minWidth = '0';
                el.style.width = '100%';
                el.style.tableLayout = 'fixed';
            });

            exportPane.querySelectorAll('.no-print').forEach(function (el) {
                el.remove();
            });

            // Copy canvas drawing data so charts appear in the generated PDF.
            const sourceCanvases = activePane.querySelectorAll('canvas');
            const clonedCanvases = exportPane.querySelectorAll('canvas');

            clonedCanvases.forEach(function (canvas, index) {
                const sourceCanvas = sourceCanvases[index];
                if (!sourceCanvas) {
                    return;
                }

                canvas.width = sourceCanvas.width;
                canvas.height = sourceCanvas.height;
                const ctx = canvas.getContext('2d');
                if (ctx) {
                    ctx.drawImage(sourceCanvas, 0, 0);
                }
            });

            exportWrapper.appendChild(header);
            exportWrapper.appendChild(exportPane);
            document.body.appendChild(exportWrapper);

            // Ensure the temporary export DOM is fully laid out before html2canvas snapshots it.
            await new Promise(function (resolve) {
                requestAnimationFrame(function () {
                    requestAnimationFrame(resolve);
                });
            });

            const previousButtonHtml = pdfButton.innerHTML;
            pdfButton.disabled = true;
            pdfButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Generating PDF...';

            const pdfOptions = {
                margin: [10, 10, 10, 10],
                filename: 'reports-' + tabKey + '-{{ $currentYear }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    scrollY: 0,
                    windowWidth: 1120
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['css', 'legacy']
                }
            };

            try {
                await html2pdf().set(pdfOptions).from(exportWrapper).save();
            } catch (error) {
                console.error('PDF export failed on cloned container, trying active pane fallback:', error);

                try {
                    await html2pdf().set(pdfOptions).from(activePane).save();
                } catch (fallbackError) {
                    console.error('PDF fallback export failed:', fallbackError);
                    window.alert('Failed to generate PDF. Please try again.');
                }
            } finally {
                pdfButton.disabled = false;
                pdfButton.innerHTML = previousButtonHtml;
                if (exportWrapper.parentNode) {
                    exportWrapper.parentNode.removeChild(exportWrapper);
                }
            }
        });
    }

    const excelButton = document.getElementById('reportsExcelBtn');
    if (excelButton) {
        excelButton.addEventListener('click', function () {
            const activePane = document.querySelector('#reportsTabContent .tab-pane.active');
            const table = activePane ? activePane.querySelector('table') : null;

            if (!table) {
                window.alert('No table is available in the active tab for export.');
                return;
            }

            const csvRows = [];
            const rows = table.querySelectorAll('tr');

            rows.forEach(function (row) {
                const cols = row.querySelectorAll('th, td');
                const values = [];

                cols.forEach(function (col) {
                    const text = (col.innerText || '')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .replace(/"/g, '""');
                    values.push('"' + text + '"');
                });

                csvRows.push(values.join(','));
            });

            const tabKey = activePane.getAttribute('data-report-pane') || 'report';
            const blob = new Blob(['\ufeff' + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');

            link.href = URL.createObjectURL(blob);
            link.download = 'reports-' + tabKey + '-{{ $currentYear }}.csv';
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }
});
</script>
@endpush
