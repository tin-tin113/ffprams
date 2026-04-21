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

    .beneficiary-kpi-label {
        font-size: 0.72rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }

    .beneficiary-kpi-value {
        font-size: clamp(1.05rem, 2vw, 1.4rem);
        font-weight: 700;
        color: #0f172a;
        line-height: 1.15;
    }

    .beneficiary-kpi-meta {
        font-size: 0.8rem;
        color: #475569;
        margin-top: 0.25rem;
    }

    .beneficiary-analytics-grid {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 0.9rem;
    }

    .beneficiary-analytics-card .card-body {
        padding: 1rem 1.1rem;
    }

    .beneficiary-mix-layout {
        display: grid;
        grid-template-columns: minmax(210px, 285px) minmax(0, 1fr);
        gap: 1rem;
        align-items: center;
    }

    .beneficiary-mix-chart-wrap {
        height: clamp(210px, 30vw, 280px);
    }

    .beneficiary-mix-legend {
        display: grid;
        gap: 0.55rem;
    }

    .beneficiary-mix-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.48rem 0.6rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.7rem;
        background: #fff;
    }

    .beneficiary-mix-dot {
        width: 0.7rem;
        height: 0.7rem;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .mix-farmers { background-color: #16a34a; }
    .mix-fisherfolk { background-color: #2563eb; }
    .mix-both { background-color: #0ea5e9; }
    .mix-default { background-color: #94a3b8; }

    .priority-list {
        display: grid;
        gap: 0.5rem;
    }

    .priority-item {
        border: 1px solid #e2e8f0;
        border-radius: 0.7rem;
        padding: 0.48rem 0.62rem;
        background: #fff;
    }

    .priority-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.32rem;
    }

    .priority-rank {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .priority-name {
        font-size: 0.83rem;
        color: #0f172a;
        font-weight: 600;
        flex: 1;
        margin-left: 0.4rem;
    }

    .priority-stat {
        font-size: 0.78rem;
        color: #475569;
        white-space: nowrap;
    }

    .priority-bar-track {
        width: 100%;
        height: 0.34rem;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .priority-bar-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f9f46 0%, #2563eb 100%);
    }

    .priority-progress {
        width: 100%;
        height: 0.42rem;
        border: 0;
        border-radius: 999px;
        overflow: hidden;
        background-color: #e2e8f0;
    }

    .priority-progress::-webkit-progress-bar {
        background-color: #e2e8f0;
        border-radius: 999px;
    }

    .priority-progress::-webkit-progress-value {
        background: linear-gradient(90deg, #0f9f46 0%, #2563eb 100%);
        border-radius: 999px;
    }

    .priority-progress::-moz-progress-bar {
        background: linear-gradient(90deg, #0f9f46 0%, #2563eb 100%);
        border-radius: 999px;
    }

    .beneficiary-priority-note {
        font-size: 0.84rem;
        color: #475569;
        margin-bottom: 0.75rem;
    }

    .beneficiary-priority-chart-wrap {
        height: clamp(220px, 28vw, 300px);
        margin-top: 0.9rem;
    }

    .beneficiary-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.9rem;
        text-align: center;
        color: #64748b;
        font-size: 0.82rem;
    }

    .beneficiary-clean-table thead th {
        background: #f1f5f9;
        color: #334155;
    }

    .beneficiary-clean-table tbody tr:nth-child(even) {
        background: #fafcff;
    }

    .report-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .overview-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        margin-bottom: 1rem;
    }

    .report-card-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.95rem 1.2rem;
    }

    .report-card-title {
        font-weight: 700;
        color: #1e293b;
    }

    .report-chart-wrap {
        position: relative;
        height: clamp(220px, 42vw, 360px);
    }

    .report-chart-wrap.compact-donut {
        max-width: 360px;
        margin: 0 auto;
        height: clamp(220px, 36vw, 300px);
    }

    .report-chart-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .report-data-table {
        min-width: 860px;
    }

    .table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        background: #f8fafc;
    }

    .empty-state {
        color: #64748b;
        text-align: center;
        padding: 1.75rem 1rem;
    }

    @media (max-width: 767.98px) {
        .reports-toolbar .btn {
            width: 100%;
        }

        .reports-filter,
        .reports-year {
            min-width: 100%;
        }

        .reports-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reports-tabs .nav-link {
            padding: 0.56rem 0.5rem;
            font-size: 0.83rem;
            gap: 0.35rem;
        }

        .reports-tabs .nav-link i {
            font-size: 0.93rem;
        }

        .insight-grid {
            grid-template-columns: 1fr;
        }

        .beneficiary-kpi-grid {
            grid-template-columns: 1fr;
        }

        .beneficiary-analytics-grid {
            grid-template-columns: 1fr;
        }

        .beneficiary-mix-layout {
            grid-template-columns: 1fr;
        }

        .priority-meta {
            flex-wrap: wrap;
        }

        .priority-stat {
            white-space: normal;
        }
    }

    @media (max-width: 1199.98px) {
        .reports-tabs {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .insight-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .beneficiary-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .beneficiary-analytics-grid {
            grid-template-columns: 1fr;
        }

        .overview-chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .report-data-table {
            min-width: 760px;
        }

        .beneficiary-mix-chart-wrap,
        .beneficiary-priority-chart-wrap {
            height: 220px;
        }
    }

    .print-only {
        display: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        .top-header,
        .top-navbar,
        .sidebar,
        .sidebar-overlay,
        .no-print,
        .btn,
        .alert {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        html,
        body {
            background: #fff !important;
            color: #111827 !important;
            font-size: 11px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            height: auto !important;
            overflow: visible !important;
        }

        body {
            display: block !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
            width: 100% !important;
            min-height: auto !important;
            height: auto !important;
            display: block !important;
            overflow: visible !important;
        }

        .main-content .container-fluid {
            max-width: none !important;
            width: 100% !important;
            overflow: visible !important;
        }

        .reports-shell {
            padding: 0 !important;
        }

        .tab-content {
            padding-top: 0 !important;
        }

        #reportsTabContent .tab-pane {
            display: none !important;
            opacity: 1 !important;
        }

        #reportsTabContent .tab-pane.show.active {
            display: block !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: auto;
            page-break-inside: auto;
            overflow: visible !important;
        }

        .report-card,
        .report-card .card-body,
        .report-card .table-responsive {
            break-inside: auto !important;
            page-break-inside: auto !important;
            overflow: visible !important;
        }

        .report-card,
        .summary-card,
        .insight-card,
        .beneficiary-kpi-card,
        .kpi-card {
            margin-bottom: 8px !important;
        }

        .summary-card,
        .insight-card,
        .beneficiary-kpi-card,
        .kpi-card {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .report-chart-wrap {
            height: 70mm !important;
            max-height: 70mm !important;
            page-break-inside: avoid;
        }

        .report-chart-wrap.compact-donut {
            height: 62mm !important;
            max-width: 100% !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .report-data-table,
        .table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
            font-size: 10px !important;
        }

        .table th,
        .table td {
            white-space: normal !important;
            word-break: break-word;
            padding: 4px 6px !important;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tr,
        img,
        canvas,
        progress {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .badge {
            border: 1px solid #cbd5e1 !important;
        }

        body.print-compact-mode .insight-grid,
        body.print-compact-mode .beneficiary-kpi-grid,
        body.print-compact-mode .beneficiary-analytics-grid,
        body.print-compact-mode .kpi-card,
        body.print-compact-mode .summary-card,
        body.print-compact-mode .insight-card,
        body.print-compact-mode .beneficiary-kpi-card {
            display: none !important;
        }

        body.print-compact-mode .report-chart-wrap,
        body.print-compact-mode .beneficiary-mix-chart-wrap,
        body.print-compact-mode .beneficiary-priority-chart-wrap,
        body.print-compact-mode canvas,
        body.print-compact-mode progress {
            display: none !important;
            height: 0 !important;
            max-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
        }

        body.print-compact-mode .card-body.border-top,
        body.print-compact-mode .card-body.border-bottom.pb-3,
        body.print-compact-mode .beneficiary-analytics-card {
            display: none !important;
        }

        body.print-compact-mode .report-card:not(:has(table)) {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $reportTabs = [
        ['id' => 'overview', 'label' => 'Overview', 'icon' => 'bi-grid-1x2'],
        ['id' => 'beneficiary', 'label' => 'Beneficiaries', 'icon' => 'bi-people'],
        ['id' => 'allocation', 'label' => 'Allocation', 'icon' => 'bi-box-seam'],
        ['id' => 'financial', 'label' => 'Financial', 'icon' => 'bi-graph-up-arrow'],
        ['id' => 'barangay', 'label' => 'Barangays', 'icon' => 'bi-geo-alt'],
        ['id' => 'agency', 'label' => 'Agencies', 'icon' => 'bi-building'],
        ['id' => 'program', 'label' => 'Programs', 'icon' => 'bi-journal-text'],
    ];

    $validTabIds = collect($reportTabs)->pluck('id');
    $activeTab = request('tab', 'overview');
    if (! $validTabIds->contains($activeTab)) {
        $activeTab = 'overview';
    }
    $activeTabLabel = collect($reportTabs)->firstWhere('id', $activeTab)['label'] ?? ucfirst($activeTab);

    $farmersTotal = (int) $beneficiariesPerBarangay->sum('total_farmers');
    $fisherfolkTotal = (int) $beneficiariesPerBarangay->sum('total_fisherfolk');
    $bothTotal = (int) $beneficiariesPerBarangay->sum('total_both');
    $kpiTotalBeneficiaries = (int) $beneficiariesPerBarangay->sum('grand_total');
    $kpiResourcesDistributed = (float) $resourceDistribution->sum('total_quantity_distributed');
    $kpiFinancialReleased = (float) $financialSummary->sum('total_amount_disbursed');
    $kpiActivePrograms = (int) $assistanceByPurpose->count();
    $kpiCompletedEvents = (int) $statusPerBarangay->sum('completed_events');
    $kpiPendingEvents = (int) $statusPerBarangay->sum('pending_events');
    $kpiBarangaysCovered = (int) $beneficiariesPerBarangay->where('grand_total', '>', 0)->count();
    $kpiSourceAgencies = (int) $resourceDistribution
        ->pluck('agency_name')
        ->merge($financialSummary->pluck('agency_name'))
        ->filter(fn ($agency) => filled($agency))
        ->unique()
        ->count();

    $statusByBarangay = $statusPerBarangay
        ->filter(fn ($row) => $row->barangay)
        ->keyBy(fn ($row) => $row->barangay->name);

    $financialByBarangay = $financialPerBarangay->keyBy('name');

    $barangayInsights = $beneficiariesPerBarangay
        ->filter(fn ($row) => $row->barangay)
        ->map(function ($row) use ($statusByBarangay, $financialByBarangay) {
            $barangayName = $row->barangay->name;
            $status = $statusByBarangay->get($barangayName);
            $financial = $financialByBarangay->get($barangayName);

            return (object) [
                'barangay_name' => $barangayName,
                'beneficiaries_total' => (int) $row->grand_total,
                'completed_events' => (int) ($status->completed_events ?? 0),
                'pending_events' => (int) ($status->pending_events ?? 0),
                'ongoing_events' => (int) ($status->ongoing_events ?? 0),
                'total_events' => (int) ($status->total_events ?? 0),
                'financial_amount' => (float) ($financial->total_amount ?? 0),
            ];
        })
        ->sortByDesc('financial_amount')
        ->values();

    $resourceByAgency = $resourceDistribution->groupBy(fn ($row) => $row->agency_name ?: 'N/A');
    $financialByAgency = $financialSummary->groupBy(fn ($row) => $row->agency_name ?: 'N/A');

    $agencySummary = $resourceByAgency
        ->keys()
        ->merge($financialByAgency->keys())
        ->unique()
        ->map(function ($agencyName) use ($resourceByAgency, $financialByAgency) {
            $resourceRows = $resourceByAgency->get($agencyName, collect());
            $financialRows = $financialByAgency->get($agencyName, collect());

            return (object) [
                'agency_name' => $agencyName,
                'resource_types' => (int) $resourceRows->count(),
                'resource_quantity' => (float) $resourceRows->sum('total_quantity_distributed'),
                'completed_events' => (int) ($resourceRows->sum('total_events') + $financialRows->sum('total_events')),
                'beneficiaries_reached' => (int) ($resourceRows->sum('total_beneficiaries_reached') + $financialRows->sum('total_beneficiaries_reached')),
                'financial_amount' => (float) $financialRows->sum('total_amount_disbursed'),
            ];
        })
        ->sortByDesc('financial_amount')
        ->values();

    $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March',
        4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September',
        10 => 'October', 11 => 'November', 12 => 'December',
    ];

    $topMonthRecord = $monthlyDistribution->sortByDesc('total_beneficiaries')->first();
    $topMonthLabel = $topMonthRecord ? ($monthNames[$topMonthRecord->month_number] ?? 'N/A') : 'N/A';
    $topMonthTotal = (int) ($topMonthRecord->total_beneficiaries ?? 0);

    $topBarangayByBeneficiaries = $beneficiariesPerBarangay->sortByDesc('grand_total')->first();
    $topBarangayByBeneficiariesName = ($topBarangayByBeneficiaries && $topBarangayByBeneficiaries->barangay)
        ? $topBarangayByBeneficiaries->barangay->name
        : 'N/A';
    $topBarangayByBeneficiariesTotal = (int) ($topBarangayByBeneficiaries->grand_total ?? 0);

    $topBarangayByEvents = $statusPerBarangay->sortByDesc('total_events')->first();
    $topBarangayByEventsName = ($topBarangayByEvents && $topBarangayByEvents->barangay)
        ? $topBarangayByEvents->barangay->name
        : 'N/A';
    $topBarangayByEventsTotal = (int) ($topBarangayByEvents->total_events ?? 0);

    $topBarangayByCompletedEvents = $barangayInsights->sortByDesc('completed_events')->first();
    $topBarangayByCompletedEventsName = $topBarangayByCompletedEvents->barangay_name ?? 'N/A';
    $topBarangayByCompletedEventsTotal = (int) ($topBarangayByCompletedEvents->completed_events ?? 0);

    $directQtyTotal = (float) $resourceDistribution->sum('direct_quantity_distributed');
    $eventQtyTotal = (float) $resourceDistribution->sum('event_quantity_distributed');
    $totalQtyMix = $directQtyTotal + $eventQtyTotal;
    $directSharePct = $totalQtyMix > 0 ? ($directQtyTotal / $totalQtyMix) * 100 : 0;

    $topResource = $resourceDistribution->sortByDesc('total_quantity_distributed')->first();
    $topResourceName = $topResource->name ?? 'N/A';
    $topResourceQty = (float) ($topResource->total_quantity_distributed ?? 0);
    $allocationResourceTypesCount = (int) $resourceDistribution->count();
    $allocationEventBeneficiariesTotal = (int) $resourceDistribution->sum('event_beneficiaries_reached');
    $allocationDirectBeneficiariesTotal = (int) $resourceDistribution->sum('direct_beneficiaries_reached');
    $allocationBeneficiaryMixTotal = $allocationEventBeneficiariesTotal + $allocationDirectBeneficiariesTotal;
    $allocationEventReachSharePct = $allocationBeneficiaryMixTotal > 0
        ? ((float) $allocationEventBeneficiariesTotal / $allocationBeneficiaryMixTotal) * 100
        : 0;

    $unreachedTotal = (int) $unreachedBeneficiaries->count();
    $reachedCount = max(0, (int) $totalBeneficiaries - $unreachedTotal);
    $coverageRate = $totalBeneficiaries > 0 ? ($reachedCount / $totalBeneficiaries) * 100 : 0;

    $beneficiaryMixRows = collect([
        ['label' => 'Farmers', 'value' => $farmersTotal, 'color' => '#16a34a'],
        ['label' => 'Fisherfolk', 'value' => $fisherfolkTotal, 'color' => '#2563eb'],
        ['label' => 'Both', 'value' => $bothTotal, 'color' => '#0ea5e9'],
    ])->map(function ($row) use ($kpiTotalBeneficiaries) {
        $row['percent'] = $kpiTotalBeneficiaries > 0
            ? ((float) $row['value'] / $kpiTotalBeneficiaries) * 100
            : 0;

        return $row;
    })->values();

    $beneficiaryMixTotal = (int) $beneficiaryMixRows->sum('value');
    $dominantBeneficiaryMix = $beneficiaryMixRows->sortByDesc('value')->first();
    $dominantBeneficiaryMixLabel = $dominantBeneficiaryMix['label'] ?? 'N/A';
    $dominantBeneficiaryMixPercent = (float) ($dominantBeneficiaryMix['percent'] ?? 0);

    $topBeneficiaryBarangays = $beneficiariesPerBarangay
        ->filter(fn ($row) => $row->barangay)
        ->sortByDesc('grand_total')
        ->take(5)
        ->values();

    $topThreeBarangayConcentrationPct = $kpiTotalBeneficiaries > 0
        ? ((float) $topBeneficiaryBarangays->take(3)->sum('grand_total') / $kpiTotalBeneficiaries) * 100
        : 0;

    $unreachedByBarangay = $unreachedBeneficiaries
        ->groupBy(function ($beneficiary) {
            return $beneficiary->barangay->name ?? 'Unassigned';
        })
        ->map(function ($rows, $barangayName) use ($unreachedTotal) {
            $count = (int) $rows->count();

            return (object) [
                'barangay_name' => $barangayName,
                'count' => $count,
                'share' => $unreachedTotal > 0 ? ($count / $unreachedTotal) * 100 : 0,
            ];
        })
        ->sortByDesc('count')
        ->values();

    $priorityOutreachBarangays = $unreachedByBarangay->take(5)->values();
    $topPriorityOutreach = $unreachedByBarangay->first();
    $topPriorityOutreachBarangay = $topPriorityOutreach->barangay_name ?? 'N/A';
    $topPriorityOutreachCount = (int) ($topPriorityOutreach->count ?? 0);
    $topPriorityOutreachShare = (float) ($topPriorityOutreach->share ?? 0);
    $avgBeneficiariesPerCoveredBarangay = $kpiBarangaysCovered > 0
        ? ((float) $kpiTotalBeneficiaries / $kpiBarangaysCovered)
        : 0;

    $legalBasisCoveragePct = $complianceOverview->financial_events_total > 0
        ? (($complianceOverview->financial_events_total - $complianceOverview->missing_legal_basis) / $complianceOverview->financial_events_total) * 100
        : 100;

    $topAssistance = $financialSummary->sortByDesc('total_amount_disbursed')->first();
    $topAssistanceName = $topAssistance->name ?? 'N/A';
    $topAssistanceAmount = (float) ($topAssistance->total_amount_disbursed ?? 0);
    $financialEventAmountTotal = (float) $financialSummary->sum('event_amount_disbursed');
    $financialDirectAmountTotal = (float) $financialSummary->sum('direct_amount_disbursed');
    $financialAmountMixTotal = $financialEventAmountTotal + $financialDirectAmountTotal;
    $financialDirectSharePct = $financialAmountMixTotal > 0
        ? ($financialDirectAmountTotal / $financialAmountMixTotal) * 100
        : 0;
    $topDirectFinancialType = $financialSummary->sortByDesc('direct_amount_disbursed')->first();
    $topDirectFinancialTypeName = $topDirectFinancialType->name ?? 'N/A';
    $topDirectFinancialTypeAmount = (float) ($topDirectFinancialType->direct_amount_disbursed ?? 0);

    $highestFinancialBarangay = $financialPerBarangay->sortByDesc('total_amount')->first();
    $highestFinancialBarangayName = $highestFinancialBarangay->name ?? 'N/A';
    $highestFinancialBarangayAmount = (float) ($highestFinancialBarangay->total_amount ?? 0);
    $barangayActiveCount = (int) $barangayInsights
        ->filter(fn ($row) => ((int) $row->total_events > 0) || ((float) $row->financial_amount > 0))
        ->count();
    $avgFinancialPerActiveBarangay = $barangayActiveCount > 0
        ? ((float) $barangayInsights->sum('financial_amount') / $barangayActiveCount)
        : 0;
    $topPendingBarangay = $barangayInsights->sortByDesc('pending_events')->first();
    $topPendingBarangayName = $topPendingBarangay->barangay_name ?? 'N/A';
    $topPendingBarangayCount = (int) ($topPendingBarangay->pending_events ?? 0);

    $financialReachedTotal = (int) $financialSummary->sum('total_beneficiaries_reached');
    $avgFinancialPerReached = $financialReachedTotal > 0 ? ($kpiFinancialReleased / $financialReachedTotal) : 0;

    $topAgencyByFinancial = $agencySummary->sortByDesc('financial_amount')->first();
    $topAgencyByFinancialName = $topAgencyByFinancial->agency_name ?? 'N/A';
    $topAgencyByFinancialAmount = (float) ($topAgencyByFinancial->financial_amount ?? 0);

    $topAgencyByReach = $agencySummary->sortByDesc('beneficiaries_reached')->first();
    $topAgencyByReachName = $topAgencyByReach->agency_name ?? 'N/A';
    $topAgencyByReachTotal = (int) ($topAgencyByReach->beneficiaries_reached ?? 0);

    $avgFinancialPerAgency = $agencySummary->count() > 0
        ? ((float) $agencySummary->sum('financial_amount') / (int) $agencySummary->count())
        : 0;
    $agencyActiveCount = (int) $agencySummary->count();
    $topAgencyByEvents = $agencySummary->sortByDesc('completed_events')->first();
    $topAgencyByEventsName = $topAgencyByEvents->agency_name ?? 'N/A';
    $topAgencyByEventsTotal = (int) ($topAgencyByEvents->completed_events ?? 0);
    $avgReachPerAgency = $agencyActiveCount > 0
        ? ((float) $agencySummary->sum('beneficiaries_reached') / $agencyActiveCount)
        : 0;

    $topPurpose = $assistanceByPurpose->sortByDesc('total_amount')->first();
    $topPurposeName = $topPurpose->name ?? 'N/A';
    $topPurposeAmount = (float) ($topPurpose->total_amount ?? 0);
    $topPurposeBeneficiaries = (int) ($topPurpose->total_beneficiaries ?? 0);

    $programCategorySummary = $assistanceByPurpose
        ->groupBy('category')
        ->map(function ($rows, $category) {
            return (object) [
                'category' => $category,
                'amount' => (float) $rows->sum('total_amount'),
                'beneficiaries' => (int) $rows->sum('total_beneficiaries'),
            ];
        })
        ->sortByDesc('amount')
        ->values();

    $topProgramCategory = $programCategorySummary->first();
    $topProgramCategoryName = ($topProgramCategory && filled($topProgramCategory->category))
        ? ucfirst($topProgramCategory->category)
        : 'N/A';
    $topProgramCategoryAmount = (float) ($topProgramCategory->amount ?? 0);

    $programBeneficiaryTotal = (int) $assistanceByPurpose->sum('total_beneficiaries');
    $avgProgramSupport = $programBeneficiaryTotal > 0
        ? ((float) $assistanceByPurpose->sum('total_amount') / $programBeneficiaryTotal)
        : 0;
    $programEventAmountTotal = (float) $assistanceByPurpose->sum('event_amount');
    $programDirectAmountTotal = (float) $assistanceByPurpose->sum('direct_amount');
    $programAmountMixTotal = $programEventAmountTotal + $programDirectAmountTotal;
    $programDirectSharePct = $programAmountMixTotal > 0
        ? ($programDirectAmountTotal / $programAmountMixTotal) * 100
        : 0;
    $topProgramByReach = $assistanceByPurpose->sortByDesc('total_beneficiaries')->first();
    $topProgramByReachName = $topProgramByReach->name ?? 'N/A';
    $topProgramByReachTotal = (int) ($topProgramByReach->total_beneficiaries ?? 0);
    $programCategoriesCount = (int) $programCategorySummary->count();
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
        <div
            class="tab-pane fade @if($activeTab === 'overview') show active @endif"
            id="reports-pane-overview"
            role="tabpanel"
            aria-labelledby="reports-tab-overview"
            data-report-pane="overview"
        >
            <div class="overview-chart-grid">
                <div class="card report-card border-0">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-pie-chart me-1"></i> Delivery Channel Mix</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap compact-donut">
                            <canvas id="overviewChannelSplitChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card report-card border-0">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-graph-up me-1"></i> Reach and Momentum Trend</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="overviewReachTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card report-card border-0">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-boxes me-1"></i> Top Distributed Resources</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="overviewTopResourcesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card report-card border-0">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-cash-stack me-1"></i> Financial Flow by Assistance Type</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="overviewFinancialFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @if($complianceOverview->financial_events_total > 0 || $complianceOverview->missing_legal_basis > 0 || $complianceOverview->liquidation_pending > 0 || $complianceOverview->farmc_required_pending > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-shield-check me-1"></i> Compliance Risk Snapshot</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="overviewComplianceRiskChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-calendar3 me-1"></i> Monthly Summary (Event vs Direct, {{ $currentYear }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-data-table">
                            <thead>
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
                                        <td colspan="8" class="empty-state">
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

                @if($monthlyDistribution->count())
                    <div class="card-body border-top">
                        <div class="report-chart-wrap">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div
            class="tab-pane fade @if($activeTab === 'beneficiary') show active @endif"
            id="reports-pane-beneficiary"
            role="tabpanel"
            aria-labelledby="reports-tab-beneficiary"
            data-report-pane="beneficiary"
        >
            <!-- Slim KPI Overview -->
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Coverage Rate</div>
                    <div class="insight-value">{{ number_format($coverageRate, 1) }}%</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Total Beneficiaries</div>
                    <div class="insight-value">{{ number_format($totalBeneficiaries) }}</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Reached</div>
                    <div class="insight-value">{{ number_format($reachedCount) }}</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Unreached</div>
                    <div class="insight-value">{{ number_format($unreachedTotal) }}</div>
                </div>
            </div>

            <!-- Chart Grid - Focus on Visualizations -->
            <div class="beneficiary-analytics-grid mb-4">
                <!-- Classification Mix Chart -->
                <div class="card report-card border-0 beneficiary-analytics-card">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-pie-chart me-1"></i> Beneficiary Classification Mix</span>
                    </div>
                    <div class="card-body">
                        @if($beneficiaryMixTotal > 0)
                            <div class="report-chart-wrap" style="height: 300px;">
                                <canvas id="beneficiaryMixChart"></canvas>
                            </div>
                        @else
                            <div class="beneficiary-empty">No classification data available.</div>
                        @endif
                    </div>
                </div>

                <!-- Geographic Distribution Chart -->
                <div class="card report-card border-0 beneficiary-analytics-card">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-geo-alt me-1"></i> Beneficiaries by Barangay (Top 10)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap" style="height: 300px;">
                            <canvas id="barangayBeneficiariesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row of Charts -->
            <div class="beneficiary-analytics-grid mb-4">
                <!-- Composition by Barangay -->
                @if($beneficiariesPerBarangay->count())
                    <div class="card report-card border-0 beneficiary-analytics-card">
                        <div class="card-header report-card-header">
                            <span class="report-card-title"><i class="bi bi-bar-chart-stacked me-1"></i> Classification by Barangay</span>
                        </div>
                        <div class="card-body">
                            <div class="report-chart-wrap" style="height: 350px;">
                                <canvas id="beneficiaryCompositionByBarangayChart"></canvas>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Priority Outreach Chart -->
                <div class="card report-card border-0 beneficiary-analytics-card">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-exclamation-triangle me-1"></i> Unreached by Barangay (Top 10)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap" style="height: 300px;">
                            <canvas id="beneficiaryPriorityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div
            class="tab-pane fade @if($activeTab === 'allocation') show active @endif"
            id="reports-pane-allocation"
            role="tabpanel"
            aria-labelledby="reports-tab-allocation"
            data-report-pane="allocation"
        >
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Top Distributed Resource</div>
                    <div class="insight-value">{{ $topResourceName }}</div>
                    <div class="insight-note">{{ number_format($topResourceQty, 2) }} total quantity released</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Direct Release Share</div>
                    <div class="insight-value">{{ number_format($directSharePct, 1) }}%</div>
                    <div class="insight-note">Share of quantity distributed through direct releases</div>
                </div>
            </div>

            <details class="tab-insights-toggle mb-4">
                <summary>
                    <span><i class="bi bi-lightbulb me-1"></i> More Allocation Insights</span>
                </summary>
                <div class="tab-insights-body">
                    <div class="insight-grid mb-0">
                        <div class="insight-card">
                            <div class="insight-label">Most Active Barangay</div>
                            <div class="insight-value">{{ $topBarangayByEventsName }}</div>
                            <div class="insight-note">{{ number_format($topBarangayByEventsTotal) }} recorded events</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Total Completed Events</div>
                            <div class="insight-value">{{ number_format($kpiCompletedEvents) }}</div>
                            <div class="insight-note">Completed within selected year context</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Active Resource Types</div>
                            <div class="insight-value">{{ number_format($allocationResourceTypesCount) }}</div>
                            <div class="insight-note">Resource types with event/direct movement</div>
                        </div>
                    </div>
                </div>
            </details>

            @if($monthlyDistribution->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-graph-up me-1"></i> Monthly Allocation Analytics ({{ $currentYear }})</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-xl-6">
                                <div class="report-chart-wrap">
                                    <canvas id="allocationMonthlyReachChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="report-chart-wrap">
                                    <canvas id="allocationMonthlyQuantityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($resourceDistribution->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-people-fill me-1"></i> Resource Reach by Type (Event vs Direct)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="allocationReachByResourceChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-box-seam me-1"></i> Resource Distribution Summary (Event vs Direct)</span>
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
                            <thead>
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
                                                    'DA' => 'bg-success',
                                                    'BFAR' => 'bg-primary',
                                                    'DAR' => 'bg-warning text-dark',
                                                    'LGU' => 'bg-secondary',
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
                                        <td colspan="11" class="empty-state">
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

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-bar-chart me-1"></i> Distribution Status and Direct Releases per Barangay</span>
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
                            <thead>
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
                                        <td colspan="8" class="empty-state">
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
        </div>

        <div
            class="tab-pane fade @if($activeTab === 'financial') show active @endif"
            id="reports-pane-financial"
            role="tabpanel"
            aria-labelledby="reports-tab-financial"
            data-report-pane="financial"
        >
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Highest Assistance Type</div>
                    <div class="insight-value">{{ $topAssistanceName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($topAssistanceAmount, 2) }} total disbursed</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Average Support per Reached Beneficiary</div>
                    <div class="insight-value">&#8369;{{ number_format($avgFinancialPerReached, 2) }}</div>
                    <div class="insight-note">Based on {{ number_format($financialReachedTotal) }} reached beneficiaries</div>
                </div>
            </div>

            <details class="tab-insights-toggle mb-4">
                <summary>
                    <span><i class="bi bi-lightbulb me-1"></i> More Financial Insights</span>
                </summary>
                <div class="tab-insights-body">
                    <div class="insight-grid mb-0">
                        <div class="insight-card">
                            <div class="insight-label">Highest Release Barangay</div>
                            <div class="insight-value">{{ $highestFinancialBarangayName }}</div>
                            <div class="insight-note">&#8369;{{ number_format($highestFinancialBarangayAmount, 2) }} total assistance</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Channel Disbursement Split</div>
                            <div class="insight-value">&#8369;{{ number_format($financialSummary->sum('event_amount_disbursed'), 2) }}</div>
                            <div class="insight-note">Event vs &#8369;{{ number_format($financialSummary->sum('direct_amount_disbursed'), 2) }} Direct</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Top Direct Assistance Type</div>
                            <div class="insight-value">{{ $topDirectFinancialTypeName }}</div>
                            <div class="insight-note">&#8369;{{ number_format($topDirectFinancialTypeAmount, 2) }} direct disbursed</div>
                        </div>
                    </div>
                </div>
            </details>

            @if($financialSummary->sum('total_amount_disbursed') > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-pie-chart-fill me-1"></i> Financial Channel Mix (Event vs Direct)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap compact-donut">
                            <canvas id="financialChannelMixChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-cash-stack me-1"></i> Financial Assistance Summary (Event vs Direct)</span>
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
                            <thead>
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
                                                    'DA' => 'bg-success',
                                                    'BFAR' => 'bg-primary',
                                                    'DAR' => 'bg-warning text-dark',
                                                    'LGU' => 'bg-secondary',
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
                                        <td colspan="10" class="empty-state">
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

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-geo-alt me-1"></i> Financial Assistance per Barangay (Event vs Direct)</span>
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
                            <thead>
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
                                        <td colspan="9" class="empty-state">
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
        </div>

        <div
            class="tab-pane fade @if($activeTab === 'barangay') show active @endif"
            id="reports-pane-barangay"
            role="tabpanel"
            aria-labelledby="reports-tab-barangay"
            data-report-pane="barangay"
        >
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Top Funding Barangay</div>
                    <div class="insight-value">{{ $highestFinancialBarangayName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($highestFinancialBarangayAmount, 2) }} total assistance</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Top Beneficiary Barangay</div>
                    <div class="insight-value">{{ $topBarangayByBeneficiariesName }}</div>
                    <div class="insight-note">{{ number_format($topBarangayByBeneficiariesTotal) }} beneficiaries registered</div>
                </div>
            </div>

            <details class="tab-insights-toggle mb-4">
                <summary>
                    <span><i class="bi bi-lightbulb me-1"></i> More Barangay Insights</span>
                </summary>
                <div class="tab-insights-body">
                    <div class="insight-grid mb-0">
                        <div class="insight-card">
                            <div class="insight-label">Highest Completed Events</div>
                            <div class="insight-value">{{ $topBarangayByCompletedEventsName }}</div>
                            <div class="insight-note">{{ number_format($topBarangayByCompletedEventsTotal) }} completed events</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Barangays in Snapshot</div>
                            <div class="insight-value">{{ number_format($barangayInsights->count()) }}</div>
                            <div class="insight-note">With beneficiary and/or event activity</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Top Pending Barangay</div>
                            <div class="insight-value">{{ $topPendingBarangayName }}</div>
                            <div class="insight-note">{{ number_format($topPendingBarangayCount) }} pending events</div>
                        </div>
                    </div>
                </div>
            </details>

            @if($barangayInsights->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-bar-chart-line me-1"></i> Barangay Beneficiaries vs Financial Assistance</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="barangayPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            @if($barangayInsights->sum('total_events') > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-diagram-2 me-1"></i> Barangay Event Status Mix</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="barangayEventMixChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-pin-map me-1"></i> Barangay Performance Snapshot</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barangay</th>
                                    <th class="text-center">Total Beneficiaries</th>
                                    <th class="text-center">Completed Events</th>
                                    <th class="text-center">Pending Events</th>
                                    <th class="text-center">Ongoing Events</th>
                                    <th class="text-center">Total Events</th>
                                    <th class="text-end">Financial Amount (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangayInsights as $row)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>{{ $row->barangay_name }}</td>
                                        <td class="text-center fw-semibold">{{ number_format($row->beneficiaries_total) }}</td>
                                        <td class="text-center">{{ number_format($row->completed_events) }}</td>
                                        <td class="text-center">{{ number_format($row->pending_events) }}</td>
                                        <td class="text-center">{{ number_format($row->ongoing_events) }}</td>
                                        <td class="text-center">{{ number_format($row->total_events) }}</td>
                                        <td class="text-end fw-semibold">&#8369;{{ number_format($row->financial_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No barangay performance data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade @if($activeTab === 'agency') show active @endif"
            id="reports-pane-agency"
            role="tabpanel"
            aria-labelledby="reports-tab-agency"
            data-report-pane="agency"
        >
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Leading Agency by Funding</div>
                    <div class="insight-value">{{ $topAgencyByFinancialName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($topAgencyByFinancialAmount, 2) }} distributed</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Leading Agency by Reach</div>
                    <div class="insight-value">{{ $topAgencyByReachName }}</div>
                    <div class="insight-note">{{ number_format($topAgencyByReachTotal) }} beneficiaries reached</div>
                </div>
            </div>

            <details class="tab-insights-toggle mb-4">
                <summary>
                    <span><i class="bi bi-lightbulb me-1"></i> More Agency Insights</span>
                </summary>
                <div class="tab-insights-body">
                    <div class="insight-grid mb-0">
                        <div class="insight-card">
                            <div class="insight-label">Average Agency Funding</div>
                            <div class="insight-value">&#8369;{{ number_format($avgFinancialPerAgency, 2) }}</div>
                            <div class="insight-note">Average disbursement across active agencies</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Total Agency Disbursement</div>
                            <div class="insight-value">&#8369;{{ number_format($agencySummary->sum('financial_amount'), 2) }}</div>
                            <div class="insight-note">Combined contributions from all active agencies</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Most Active by Events</div>
                            <div class="insight-value">{{ $topAgencyByEventsName }}</div>
                            <div class="insight-note">{{ number_format($topAgencyByEventsTotal) }} completed events</div>
                        </div>
                    </div>
                </div>
            </details>

            @if($agencySummary->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-graph-up me-1"></i> Agency Reach vs Financial Contribution</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="agencyContributionChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            @if($agencySummary->sum('financial_amount') > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-pie-chart me-1"></i> Agency Financial Share</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap compact-donut">
                            <canvas id="agencyFinancialShareChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            @if($agencySummary->sum('completed_events') > 0 || $agencySummary->sum('resource_quantity') > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-activity me-1"></i> Agency Operations Mix</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="agencyOperationsMixChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-building me-1"></i> Agency Contribution Summary</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Agency</th>
                                    <th class="text-center">Resource Types</th>
                                    <th class="text-center">Completed Events</th>
                                    <th class="text-center">Beneficiaries Reached</th>
                                    <th class="text-center">Resource Qty</th>
                                    <th class="text-end">Financial Amount (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agencySummary as $row)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            @php
                                                $agencyBadge = match($row->agency_name) {
                                                    'DA' => 'bg-success',
                                                    'BFAR' => 'bg-primary',
                                                    'DAR' => 'bg-warning text-dark',
                                                    'LGU' => 'bg-secondary',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $agencyBadge }}">{{ $row->agency_name }}</span>
                                        </td>
                                        <td class="text-center">{{ number_format($row->resource_types) }}</td>
                                        <td class="text-center">{{ number_format($row->completed_events) }}</td>
                                        <td class="text-center fw-semibold">{{ number_format($row->beneficiaries_reached) }}</td>
                                        <td class="text-center">{{ number_format($row->resource_quantity, 2) }}</td>
                                        <td class="text-end fw-semibold">&#8369;{{ number_format($row->financial_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No agency summary data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($agencySummary->count())
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2">Grand Total</td>
                                        <td class="text-center">{{ number_format($agencySummary->sum('resource_types')) }}</td>
                                        <td class="text-center">{{ number_format($agencySummary->sum('completed_events')) }}</td>
                                        <td class="text-center">{{ number_format($agencySummary->sum('beneficiaries_reached')) }}</td>
                                        <td class="text-center">{{ number_format($agencySummary->sum('resource_quantity'), 2) }}</td>
                                        <td class="text-end">&#8369;{{ number_format($agencySummary->sum('financial_amount'), 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade @if($activeTab === 'program') show active @endif"
            id="reports-pane-program"
            role="tabpanel"
            aria-labelledby="reports-tab-program"
            data-report-pane="program"
        >
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Top Funded Program</div>
                    <div class="insight-value">{{ $topPurposeName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($topPurposeAmount, 2) }} for {{ number_format($topPurposeBeneficiaries) }} beneficiaries</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Average Support per Beneficiary</div>
                    <div class="insight-value">&#8369;{{ number_format($avgProgramSupport, 2) }}</div>
                    <div class="insight-note">Across {{ number_format($programBeneficiaryTotal) }} assisted beneficiaries</div>
                </div>
            </div>

            <details class="tab-insights-toggle mb-4">
                <summary>
                    <span><i class="bi bi-lightbulb me-1"></i> More Program Insights</span>
                </summary>
                <div class="tab-insights-body">
                    <div class="insight-grid mb-0">
                        <div class="insight-card">
                            <div class="insight-label">Leading Program Category</div>
                            <div class="insight-value">{{ $topProgramCategoryName }}</div>
                            <div class="insight-note">&#8369;{{ number_format($topProgramCategoryAmount, 2) }} total disbursed</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Active Program Categories</div>
                            <div class="insight-value">{{ number_format($programCategoriesCount) }}</div>
                            <div class="insight-note">Categories with recorded event/direct support</div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-label">Top Program by Reach</div>
                            <div class="insight-value">{{ $topProgramByReachName }}</div>
                            <div class="insight-note">{{ number_format($topProgramByReachTotal) }} beneficiaries assisted</div>
                        </div>
                    </div>
                </div>
            </details>

            @if($programCategorySummary->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-diagram-3 me-1"></i> Program Category Reach and Funding</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="programCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            @if($assistanceByPurpose->count())
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-bar-chart-steps me-1"></i> Program Event vs Direct Amount (Top Purposes)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="programEventDirectChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            @if($assistanceByPurpose->sum('total_beneficiaries') > 0)
                <div class="card report-card border-0 mb-4">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-people me-1"></i> Program Beneficiary Reach (Top Purposes)</span>
                    </div>
                    <div class="card-body">
                        <div class="report-chart-wrap">
                            <canvas id="programBeneficiaryReachChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-cash-coin me-1"></i> Financial Assistance Distribution by Purpose (Event vs Direct)</span>
                </div>
                @if($assistanceByPurpose->count())
                    <div class="card-body border-bottom pb-3">
                        <div class="report-chart-wrap compact-donut">
                            <canvas id="assistanceByPurposeChart"></canvas>
                        </div>
                    </div>
                @endif
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-data-table">
                            <thead>
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
                                                    'fishery' => 'bg-primary',
                                                    'livelihood' => 'bg-info',
                                                    'medical' => 'bg-danger',
                                                    'emergency' => 'bg-warning text-dark',
                                                    default => 'bg-secondary',
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
                                        <td colspan="9" class="empty-state">
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
