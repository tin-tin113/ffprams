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
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
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
        background: linear-gradient(135deg, #0f9f46 0%, #138a3e 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 20px rgba(15, 159, 70, 0.3);
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
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
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
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
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #dbe3ef;
        border-radius: 0.9rem;
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.05);
        padding: 0.82rem 0.9rem;
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
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
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

    $highestFinancialBarangay = $financialPerBarangay->sortByDesc('total_amount')->first();
    $highestFinancialBarangayName = $highestFinancialBarangay->name ?? 'N/A';
    $highestFinancialBarangayAmount = (float) ($highestFinancialBarangay->total_amount ?? 0);

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
@endphp

<div class="container-fluid reports-shell">
    <div class="card reports-toolbar border-0 no-print">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div>
                    <h1 class="reports-title">Reports & Analytics</h1>
                    <p class="reports-subtitle">Municipality of Enrique B. Magalona - Farmer-Fisherfolk Resource Allocation</p>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                    <form method="GET" action="{{ route('reports.index') }}" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <input type="hidden" name="tab" value="{{ $activeTab }}" id="reportTabInput">
                        <select class="form-select reports-filter" aria-label="Period" disabled>
                            <option selected>Full Year</option>
                        </select>
                        <select class="form-select reports-year" name="year" aria-label="Year" onchange="this.form.submit()">
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
            <div class="insight-grid mb-4">
                <div class="insight-card">
                    <div class="insight-label">Peak Delivery Month</div>
                    <div class="insight-value">{{ $topMonthLabel }}</div>
                    <div class="insight-note">{{ number_format($topMonthTotal) }} beneficiaries reached</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Distribution Mix</div>
                    <div class="insight-value">{{ number_format($directSharePct, 1) }}% Direct</div>
                    <div class="insight-note">{{ number_format($directQtyTotal, 2) }} direct vs {{ number_format($eventQtyTotal, 2) }} event quantity</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Compliance Readiness</div>
                    <div class="insight-value">{{ number_format($legalBasisCoveragePct, 1) }}%</div>
                    <div class="insight-note">Legal basis coverage with {{ number_format($complianceOverview->liquidation_pending) }} pending liquidation cases</div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-green h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Total Beneficiaries</div>
                                <div class="kpi-value">{{ number_format($kpiTotalBeneficiaries) }}</div>
                                <div class="kpi-meta">{{ number_format($farmersTotal) }} Farmers - {{ number_format($fisherfolkTotal) }} Fisherfolk</div>
                            </div>
                            <i class="bi bi-people fs-5 text-success"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-blue h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Resources Distributed</div>
                                <div class="kpi-value">{{ number_format($kpiResourcesDistributed, 2) }}</div>
                                <div class="kpi-meta">{{ number_format($kpiPendingEvents) }} pending events</div>
                            </div>
                            <i class="bi bi-truck fs-5 text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-amber h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Financial Assistance</div>
                                <div class="kpi-value">&#8369;{{ number_format($kpiFinancialReleased, 2) }}</div>
                                <div class="kpi-meta">Total released</div>
                            </div>
                            <i class="bi bi-cash-coin fs-5 text-warning"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-violet h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Active Programs</div>
                                <div class="kpi-value">{{ number_format($kpiActivePrograms) }}</div>
                                <div class="kpi-meta">By assistance purpose</div>
                            </div>
                            <i class="bi bi-journal-check fs-5" style="color:#7c3aed"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-navy h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Completed Events</div>
                                <div class="kpi-value">{{ number_format($kpiCompletedEvents) }}</div>
                                <div class="kpi-meta">Across all barangays</div>
                            </div>
                            <i class="bi bi-check-circle fs-5 text-dark"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-cyan h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Barangays Covered</div>
                                <div class="kpi-value">{{ number_format($kpiBarangaysCovered) }}</div>
                                <div class="kpi-meta">With recorded beneficiaries</div>
                            </div>
                            <i class="bi bi-geo-alt fs-5 text-info"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-orange h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Source Agencies</div>
                                <div class="kpi-value">{{ number_format($kpiSourceAgencies) }}</div>
                                <div class="kpi-meta">With active contributions</div>
                            </div>
                            <i class="bi bi-building fs-5" style="color:#ea580c"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card kpi-emerald h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-label">Dual Classification</div>
                                <div class="kpi-value">{{ number_format($bothTotal) }}</div>
                                <div class="kpi-meta">Tagged as Farmer and Fisherfolk</div>
                            </div>
                            <i class="bi bi-person-badge fs-5" style="color:#059669"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Financial Events</div>
                            <div class="fs-4 fw-bold">{{ number_format($complianceOverview->financial_events_total) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Missing Legal Basis</div>
                            <div class="fs-4 fw-bold text-danger">{{ number_format($complianceOverview->missing_legal_basis) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Pending Liquidation</div>
                            <div class="fs-4 fw-bold text-warning">{{ number_format($complianceOverview->liquidation_pending) }}</div>
                            <div class="small text-muted">Overdue: {{ number_format($complianceOverview->liquidation_overdue) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">FARMC Pending</div>
                            <div class="fs-4 fw-bold text-primary">{{ number_format($complianceOverview->farmc_required_pending) }}</div>
                        </div>
                    </div>
                </div>
            </div>

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
            <div class="beneficiary-kpi-grid mb-4">
                <div class="beneficiary-kpi-card">
                    <div class="beneficiary-kpi-label">Coverage Rate</div>
                    <div class="beneficiary-kpi-value">{{ number_format($coverageRate, 1) }}%</div>
                    <div class="beneficiary-kpi-meta">{{ number_format($reachedCount) }} reached out of {{ number_format($totalBeneficiaries) }}</div>
                </div>
                <div class="beneficiary-kpi-card">
                    <div class="beneficiary-kpi-label">Top 3 Concentration</div>
                    <div class="beneficiary-kpi-value">{{ number_format($topThreeBarangayConcentrationPct, 1) }}%</div>
                    <div class="beneficiary-kpi-meta">Share of beneficiaries in the 3 most served barangays</div>
                </div>
                <div class="beneficiary-kpi-card">
                    <div class="beneficiary-kpi-label">Average Per Covered Barangay</div>
                    <div class="beneficiary-kpi-value">{{ number_format($avgBeneficiariesPerCoveredBarangay, 1) }}</div>
                    <div class="beneficiary-kpi-meta">Across {{ number_format($kpiBarangaysCovered) }} barangays with beneficiaries</div>
                </div>
                <div class="beneficiary-kpi-card">
                    <div class="beneficiary-kpi-label">Dominant Classification</div>
                    <div class="beneficiary-kpi-value">{{ $dominantBeneficiaryMixLabel }}</div>
                    <div class="beneficiary-kpi-meta">{{ number_format($dominantBeneficiaryMixPercent, 1) }}% of all registered beneficiaries</div>
                </div>
            </div>

            <div class="beneficiary-analytics-grid mb-4">
                <div class="card report-card border-0 beneficiary-analytics-card">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-pie-chart me-1"></i> Classification Mix</span>
                    </div>
                    <div class="card-body">
                        @if($beneficiaryMixTotal > 0)
                            <div class="beneficiary-mix-layout">
                                <div class="report-chart-wrap beneficiary-mix-chart-wrap">
                                    <canvas id="beneficiaryMixChart"></canvas>
                                </div>
                                <div class="beneficiary-mix-legend">
                                    @foreach($beneficiaryMixRows as $mixRow)
                                        @php
                                            $mixColorClass = match($mixRow['label']) {
                                                'Farmers' => 'mix-farmers',
                                                'Fisherfolk' => 'mix-fisherfolk',
                                                'Both' => 'mix-both',
                                                default => 'mix-default',
                                            };
                                        @endphp
                                        <div class="beneficiary-mix-item">
                                            <span class="beneficiary-mix-dot {{ $mixColorClass }}"></span>
                                            <div>
                                                <div class="fw-semibold text-dark small">{{ $mixRow['label'] }}</div>
                                                <div class="text-muted small">{{ number_format($mixRow['value']) }} beneficiaries · {{ number_format($mixRow['percent'], 1) }}%</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="beneficiary-empty">No beneficiary classification data available.</div>
                        @endif
                    </div>
                </div>

                <div class="card report-card border-0 beneficiary-analytics-card">
                    <div class="card-header report-card-header">
                        <span class="report-card-title"><i class="bi bi-flag me-1"></i> Outreach Priority by Barangay</span>
                    </div>
                    <div class="card-body">
                        <p class="beneficiary-priority-note mb-2">
                            Highest priority: <strong>{{ $topPriorityOutreachBarangay }}</strong>
                            <span class="text-muted">({{ number_format($topPriorityOutreachCount) }} unreached · {{ number_format($topPriorityOutreachShare, 1) }}%)</span>
                        </p>

                        @if($priorityOutreachBarangays->count())
                            @php
                                $maxPriorityCount = max(1, (int) $priorityOutreachBarangays->max('count'));
                            @endphp
                            <div class="priority-list mb-3">
                                @foreach($priorityOutreachBarangays as $priorityRow)
                                    <div class="priority-item">
                                        <div class="priority-meta">
                                            <span class="priority-rank">{{ $loop->iteration }}</span>
                                            <span class="priority-name">{{ $priorityRow->barangay_name }}</span>
                                            <span class="priority-stat">{{ number_format($priorityRow->count) }} · {{ number_format($priorityRow->share, 1) }}%</span>
                                        </div>
                                        <progress class="priority-progress" value="{{ $priorityRow->count }}" max="{{ $maxPriorityCount }}"></progress>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="beneficiary-empty mb-3">No unreached beneficiary records by barangay.</div>
                        @endif

                        <div class="report-chart-wrap beneficiary-priority-chart-wrap">
                            <canvas id="beneficiaryPriorityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-people me-1"></i> Beneficiaries per Barangay</span>
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
                        <table class="table table-hover align-middle mb-0 report-data-table beneficiary-clean-table">
                            <thead>
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
                                        <td colspan="6" class="empty-state">
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

            <div class="card report-card border-0 mb-4">
                <div class="card-header report-card-header">
                    <span class="report-card-title"><i class="bi bi-person-x me-1"></i> Beneficiaries Not Yet Reached</span>
                </div>

                <div class="card-body border-bottom pb-3">
                    <div class="report-chart-wrap compact-donut">
                        <canvas id="unreachedBeneficiariesChart"></canvas>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-data-table beneficiary-clean-table">
                            <thead>
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
                                                    'Farmer' => 'bg-success',
                                                    'Fisherfolk' => 'bg-primary',
                                                    'Both' => 'bg-info',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $classBadge }}">{{ $beneficiary->classification }}</span>
                                        </td>
                                        <td>{{ $beneficiary->contact_number ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
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
                <div class="insight-card">
                    <div class="insight-label">Most Active Barangay</div>
                    <div class="insight-value">{{ $topBarangayByEventsName }}</div>
                    <div class="insight-note">{{ number_format($topBarangayByEventsTotal) }} recorded events</div>
                </div>
            </div>

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
                    <div class="insight-label">Highest Release Barangay</div>
                    <div class="insight-value">{{ $highestFinancialBarangayName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($highestFinancialBarangayAmount, 2) }} total assistance</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Average Support per Reached Beneficiary</div>
                    <div class="insight-value">&#8369;{{ number_format($avgFinancialPerReached, 2) }}</div>
                    <div class="insight-note">Based on {{ number_format($financialReachedTotal) }} reached beneficiaries</div>
                </div>
            </div>

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
                <div class="insight-card">
                    <div class="insight-label">Highest Completed Events</div>
                    <div class="insight-value">{{ $topBarangayByCompletedEventsName }}</div>
                    <div class="insight-note">{{ number_format($topBarangayByCompletedEventsTotal) }} completed events</div>
                </div>
            </div>

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
                <div class="insight-card">
                    <div class="insight-label">Average Agency Funding</div>
                    <div class="insight-value">&#8369;{{ number_format($avgFinancialPerAgency, 2) }}</div>
                    <div class="insight-note">Average disbursement across active agencies</div>
                </div>
            </div>

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
                    <div class="insight-label">Leading Program Category</div>
                    <div class="insight-value">{{ $topProgramCategoryName }}</div>
                    <div class="insight-note">&#8369;{{ number_format($topProgramCategoryAmount, 2) }} total disbursed</div>
                </div>
                <div class="insight-card">
                    <div class="insight-label">Average Support per Beneficiary</div>
                    <div class="insight-value">&#8369;{{ number_format($avgProgramSupport, 2) }}</div>
                    <div class="insight-note">Across {{ number_format($programBeneficiaryTotal) }} assisted beneficiaries</div>
                </div>
            </div>

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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
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

    const chartInstances = {};

    function createChartIfNeeded(canvasId, createFn) {
        if (typeof Chart === 'undefined') {
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

    function initializeBeneficiariesChart() {
        if (!beneficiariesByBarangayData.length) {
            return;
        }

        createChartIfNeeded('barangayBeneficiariesChart', function (canvas) {
            const labels = beneficiariesByBarangayData
                .map(function (row) {
                    return row.barangay && row.barangay.name ? row.barangay.name : 'Unknown';
                })
                .slice(0, 10);

            const values = beneficiariesByBarangayData
                .map(function (row) {
                    return toNumber(row.grand_total);
                })
                .slice(0, 10);

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

    function initializeResourceDistributionChart() {
        if (!resourceDistributionData.length) {
            return;
        }

        createChartIfNeeded('resourceDistributionChart', function (canvas) {
            const labels = resourceDistributionData.map(function (row) { return row.name; }).slice(0, 10);
            const eventQty = resourceDistributionData.map(function (row) { return toNumber(row.event_quantity_distributed); }).slice(0, 10);
            const directQty = resourceDistributionData.map(function (row) { return toNumber(row.direct_quantity_distributed); }).slice(0, 10);

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

    function initializeStatusPerBarangayChart() {
        if (!statusPerBarangayData.length) {
            return;
        }

        createChartIfNeeded('statusPerBarangayChart', function (canvas) {
            const labels = statusPerBarangayData
                .map(function (row) {
                    return row.barangay && row.barangay.name ? row.barangay.name : 'Unknown';
                })
                .slice(0, 10);

            const pending = statusPerBarangayData.map(function (row) { return toNumber(row.pending_events); }).slice(0, 10);
            const ongoing = statusPerBarangayData.map(function (row) { return toNumber(row.ongoing_events); }).slice(0, 10);
            const completed = statusPerBarangayData.map(function (row) { return toNumber(row.completed_events); }).slice(0, 10);

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
            const labels = financialSummaryData.map(function (row) { return row.name; }).slice(0, 10);
            const values = financialSummaryData.map(function (row) { return toNumber(row.total_amount_disbursed); }).slice(0, 10);

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

    function initializeFinancialPerBarangayChart() {
        if (!financialPerBarangayData.length) {
            return;
        }

        createChartIfNeeded('financialPerBarangayChart', function (canvas) {
            const labels = financialPerBarangayData.map(function (row) { return row.name; });
            const eventAmounts = financialPerBarangayData.map(function (row) { return toNumber(row.event_amount); });
            const directAmounts = financialPerBarangayData.map(function (row) { return toNumber(row.direct_amount); });

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

    function initializePurposeChart() {
        if (!assistanceByPurposeData.length) {
            return;
        }

        createChartIfNeeded('assistanceByPurposeChart', function (canvas) {
            const labels = assistanceByPurposeData.map(function (row) { return row.name; });
            const amounts = assistanceByPurposeData.map(function (row) { return toNumber(row.total_amount); });

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
        overview: [initializeMonthlyChart],
        beneficiary: [initializeBeneficiariesChart, initializeUnreachedChart, initializeBeneficiaryMixChart, initializeBeneficiaryPriorityChart],
        allocation: [initializeResourceDistributionChart, initializeStatusPerBarangayChart],
        financial: [initializeFinancialSummaryChart, initializeFinancialPerBarangayChart],
        barangay: [],
        agency: [],
        program: [initializePurposeChart]
    };

    function initializeChartsForTab(tabKey) {
        const initializers = tabChartInitializers[tabKey] || [];
        initializers.forEach(function (fn) {
            fn();
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

    tabButtons.forEach(function (button) {
        button.addEventListener('shown.bs.tab', function (event) {
            const tabKey = event.target.getAttribute('data-report-tab') || 'overview';

            if (reportTabInput) {
                reportTabInput.value = tabKey;
            }

            initializeChartsForTab(tabKey);
            resizeCharts();

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

    initializeChartsForTab(initialTabKey);

    window.addEventListener('resize', function () {
        resizeCharts();
    });

    const pdfButton = document.getElementById('reportsPdfBtn');
    if (pdfButton) {
        pdfButton.addEventListener('click', function () {
            window.print();
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
