@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .dashboard-header h1 {
            font-size: 28px;
            font-weight: 650;
            margin: 0;
            color: #1a1d29;
        }

        .dashboard-date {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin: 24px 0 14px 0;
            gap: 8px;
        }

        .section-header h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.2px;
            color: #4b5563;
        }

        .section-header i {
            color: #6b7280;
            font-size: 14px;
        }

        /* Main KPI Cards */
        .kpi-master {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .kpi-master:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .kpi-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: #f3f4f6;
            color: #374151;
            flex-shrink: 0;
        }

        .kpi-text h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1a1d29;
            line-height: 1;
        }

        .kpi-text p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Progress Bar Cards */
        .progress-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .progress-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .progress-card-label {
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--color, #0d6efd);
        }

        .progress-bar-custom {
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--color, #0d6efd);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .progress-sub {
            font-size: 12px;
            color: #6c757d;
        }

        /* Clickable metric cards */
        .progress-card.clickable {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .progress-card.clickable:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            border-color: #c5cad0;
        }

        .progress-card.clickable:active {
            transform: translateY(0);
        }

        .progress-card.clickable .progress-card-header::after {
            content: '\F282';
            font-family: 'bootstrap-icons';
            font-size: 14px;
            color: #9ca3af;
            margin-left: auto;
        }

        /* Drill-down modal styles */
        .drill-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .drill-summary-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .drill-summary-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #1a1d29;
            line-height: 1.2;
        }

        .drill-summary-card .label {
            font-size: 11px;
            color: #6c757d;
            font-weight: 500;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .drill-filter-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 14px;
        }

        .drill-filter-tabs .btn {
            font-size: 12px;
            padding: 5px 14px;
            border-radius: 20px;
            font-weight: 500;
        }

        .drill-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .drill-table-wrapper table {
            font-size: 13px;
            margin: 0;
        }

        .drill-table-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .drill-table-wrapper tbody tr:hover {
            background: #f0f4ff;
        }

        .drill-empty {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .drill-empty i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }

        .drill-loading {
            text-align: center;
            padding: 50px 20px;
        }

        .drill-loading .spinner-border {
            width: 2rem;
            height: 2rem;
            color: #0d6efd;
        }

        /* Chart Cards */
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .chart-card-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1d29;
            margin-bottom: 12px;
            letter-spacing: 0.2px;
        }

        /* Mini Cards */
        .mini-stat {
            background: white;
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .mini-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #1a1d29;
            line-height: 1;
            margin-bottom: 6px;
        }

        .mini-stat-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Alert Cards */
        .alert-card {
            background: linear-gradient(135deg, var(--bg-from), var(--bg-to));
            border-radius: 14px;
            padding: 20px;
            color: white;
            border: none;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .alert-icon {
            font-size: 28px;
            flex-shrink: 0;
        }

        .alert-content h5 {
            margin: 0 0 6px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-content p {
            margin: 0;
            font-size: 13px;
            opacity: 0.95;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .section-header {
                margin-top: 24px;
            }
        }
    </style>

    {{-- HEADER --}}
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <span class="dashboard-date">{{ now()->format('F d, Y • l') }}</span>
    </div>

    {{-- SECTION 1: CRITICAL METRICS --}}
    <div class="section-header">
        <i class="bi bi-speedometer2"></i>
        <h6>Critical Metrics</h6>
    </div>
    <div class="row g-3 mb-4">
        {{-- Completion Rate --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-master">
                <div class="kpi-content">
                    <div class="kpi-icon" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="kpi-text">
                        <h3>{{ number_format($completionRate, 1) }}%</h3>
                        <p>Completion Rate</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coverage Reached --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-master">
                <div class="kpi-content">
                    <div class="kpi-icon" style="background: #e3f2fd; color: #1565c0;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="kpi-text">
                        <h3>{{ number_format($reachedBeneficiaries) }}</h3>
                        <p>Beneficiaries Reached</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Disbursed --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-master">
                <div class="kpi-content">
                    <div class="kpi-icon" style="background: #fff3e0; color: #e65100;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="kpi-text">
                        <h3>₱{{ number_format($totalFinancialDisbursed / 1000, 0) }}K</h3>
                        <p>Total Disbursed</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Events Completed --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-master">
                <div class="kpi-content">
                    <div class="kpi-icon" style="background: #f3e5f5; color: #7b1fa2;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="kpi-text">
                        <h3>{{ $completedEvents }}/{{ $totalDistributionEvents }}</h3>
                        <p>Events Completed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: KEY PERFORMANCE INDICATORS WITH PROGRESS --}}
    <div class="section-header">
        <i class="bi bi-graph-up"></i>
        <h6>Performance Overview</h6>
    </div>
    <div class="row g-3 mb-4">
        {{-- Financial Utilization --}}
        <div class="col-lg-4">
            <div class="progress-card clickable" id="metric-financial-utilization" data-bs-toggle="modal" data-bs-target="#financialUtilizationModal">
                <div class="progress-card-header">
                    <span class="progress-card-label">Financial Utilization</span>
                    <span class="progress-value" style="--color: #0d6efd;">{{ number_format($financialUtilizationRate, 1) }}%</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: {{ min($financialUtilizationRate, 100) }}%; --color: #0d6efd;"></div>
                </div>
                <div class="progress-sub">Budget Efficiency</div>
            </div>
        </div>

        {{-- Coverage Gap --}}
        <div class="col-lg-4">
            <div class="progress-card clickable" id="metric-coverage-gap" data-bs-toggle="modal" data-bs-target="#coverageGapModal">
                <div class="progress-card-header">
                    <span class="progress-card-label">Coverage Gap</span>
                    <span class="progress-value" style="--color: #dc3545;">{{ number_format($coverageGap['percentage'], 1) }}%</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: {{ min($coverageGap['percentage'], 100) }}%; background: #dc3545;"></div>
                </div>
                <div class="progress-sub">{{ number_format($coverageGap['unreached_count']) }} unreached beneficiaries</div>
            </div>
        </div>

        {{-- Allocation Rate --}}
        <div class="col-lg-4">
            <div class="progress-card clickable" id="metric-allocation-rate" data-bs-toggle="modal" data-bs-target="#allocationRateModal">
                <div class="progress-card-header">
                    <span class="progress-card-label">Allocation Rate</span>
                    <span class="progress-value" style="--color: #198754;">{{ number_format(($totalEventAllocations + $totalDirectAllocations) > 0 ? (($eventDistributed + $directReleased) / ($totalEventAllocations + $totalDirectAllocations)) * 100 : 0, 1) }}%</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: {{ number_format(($totalEventAllocations + $totalDirectAllocations) > 0 ? (($eventDistributed + $directReleased) / ($totalEventAllocations + $totalDirectAllocations)) * 100 : 0, 1) }}%; background: #198754;"></div>
                </div>
                <div class="progress-sub">{{ number_format($eventDistributed + $directReleased) }} of {{ number_format($totalEventAllocations + $totalDirectAllocations) }} allocations</div>
            </div>
        </div>
    </div>

    {{-- SECTION 3: VISUAL CHARTS ROW 1 --}}
    <div class="section-header">
        <i class="bi bi-pie-chart"></i>
        <h6>Distribution Breakdown</h6>
    </div>
    <div class="row g-3 mb-4">
        {{-- Beneficiary Breakdown --}}
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-card-title">Beneficiary Classification</div>
                <div style="position: relative; height: 180px;">
                    <canvas id="beneficiaryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Allocation Method --}}
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-card-title">Allocation Method</div>
                <div style="position: relative; height: 180px;">
                    <canvas id="allocationMethodChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Event Status --}}
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <div class="chart-card-title">Event Status</div>
                <div style="position: relative; height: 180px;">
                    <canvas id="eventStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 4: QUICK STATS --}}
    <div class="section-header">
        <i class="bi bi-info-circle"></i>
        <h6>System Overview</h6>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="mini-stat">
                <div class="mini-stat-value">{{ number_format($totalBeneficiaries) }}</div>
                <div class="mini-stat-label">Total Beneficiaries</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="mini-stat">
                <div class="mini-stat-value">₱{{ number_format($averageAllocationPerBeneficiary, 0) }}</div>
                <div class="mini-stat-label">Avg. per Beneficiary</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="mini-stat">
                <div class="mini-stat-value">{{ $totalDistributionEvents }}</div>
                <div class="mini-stat-label">Distribution Events</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="mini-stat">
                <div class="mini-stat-value">{{ number_format($totalEventAllocations + $totalDirectAllocations) }}</div>
                <div class="mini-stat-label">Total Allocations</div>
            </div>
        </div>
    </div>

    {{-- SECTION 5: RESOURCE DISTRIBUTION & ASSISTANCE PURPOSES --}}
    <div class="section-header">
        <i class="bi bi-layers"></i>
        <h6>Distribution Insights</h6>
    </div>
    <div class="row g-3 mb-4">
        {{-- Resource Type Distribution --}}
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Resource Type Distribution</div>
                <div style="position: relative; height: 240px;">
                    <canvas id="resourceTypeChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Assistance Purpose Distribution --}}
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Assistance Purpose Breakdown</div>
                <div style="position: relative; height: 240px;">
                    <canvas id="assistancePurposeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 5B: GEOGRAPHIC & TEMPORAL INSIGHTS --}}
    <div class="row g-3 mb-4">
        {{-- Geographic Coverage --}}
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Coverage by Barangay</div>
                <div style="position: relative; height: 240px;">
                    <canvas id="barangayChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Distribution Trend (Last 6 Months)</div>
                <div style="position: relative; height: 240px;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 6: TOP PROGRAMS CHART --}}
    <div class="section-header">
        <i class="bi bi-bar-chart-line"></i>
        <h6>Top Programs by Reach</h6>
    </div>
    <div class="chart-card mb-4">
        <div style="position: relative; height: 300px;">
            <canvas id="topProgramsChart"></canvas>
        </div>
    </div>

    {{-- SECTION 7: ADDITIONAL IMPORTANT VISUALIZATIONS --}}
    <div class="section-header">
        <i class="bi bi-graph-up-arrow"></i>
        <h6>Additional Important Visualizations</h6>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Program Disbursement Amount (Top 7)</div>
                <div style="position: relative; height: 260px;">
                    <canvas id="programDisbursementChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-card-title">Monthly Release Method Trend</div>
                <div style="position: relative; height: 260px;">
                    <canvas id="methodTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- DRILL-DOWN MODALS             --}}
    {{-- ============================= --}}

    {{-- Financial Utilization Modal --}}
    <div class="modal fade" id="financialUtilizationModal" tabindex="-1" aria-labelledby="financialUtilizationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid #0d6efd;">
                    <div>
                        <h5 class="modal-title" id="financialUtilizationModalLabel">
                            <i class="bi bi-cash-stack text-primary me-2"></i>Financial Utilization Details
                        </h5>
                        <small class="text-muted">Budget allocation and disbursement breakdown</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="financialUtilizationBody">
                    <div class="drill-loading">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        <p class="mt-2 text-muted">Loading financial data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Coverage Gap Modal --}}
    <div class="modal fade" id="coverageGapModal" tabindex="-1" aria-labelledby="coverageGapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid #dc3545;">
                    <div>
                        <h5 class="modal-title" id="coverageGapModalLabel">
                            <i class="bi bi-people text-danger me-2"></i>Coverage Gap Details
                        </h5>
                        <small class="text-muted">Beneficiaries without distributed allocations</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="coverageGapBody">
                    <div class="drill-loading">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        <p class="mt-2 text-muted">Loading beneficiary data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Allocation Rate Modal --}}
    <div class="modal fade" id="allocationRateModal" tabindex="-1" aria-labelledby="allocationRateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid #198754;">
                    <div>
                        <h5 class="modal-title" id="allocationRateModalLabel">
                            <i class="bi bi-clipboard-data text-success me-2"></i>Allocation Rate Details
                        </h5>
                        <small class="text-muted">Distribution progress across all allocations</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="allocationRateBody">
                    <div class="drill-loading">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        <p class="mt-2 text-muted">Loading allocation data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    font: { family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" }
};

Chart.defaults.font = chartDefaults.font;

// Global Tooltip Styling
const tooltipConfig = {
    enabled: true,
    mode: 'index',
    intersect: false,
    backgroundColor: 'rgba(0, 0, 0, 0.9)',
    titleColor: '#ffffff',
    bodyColor: '#ffffff',
    borderColor: 'rgba(255, 255, 255, 0.3)',
    borderWidth: 1.5,
    cornerRadius: 10,
    padding: 14,
    displayColors: true,
    titleFont: { size: 13, weight: '700' },
    bodyFont: { size: 12, weight: '500' },
    boxPadding: 8,
    usePointStyle: true,
    titleMarginBottom: 8,
    bodySpacing: 6,
    caretPadding: 10
};

// 1. BENEFICIARY BREAKDOWN PIE CHART
document.addEventListener('DOMContentLoaded', function () {
    const beneficiaryData = @json($beneficiaryBreakdown);
    if (beneficiaryData.data && beneficiaryData.data.length > 0) {
        new Chart(document.getElementById('beneficiaryChart'), {
            type: 'doughnut',
            data: {
                labels: beneficiaryData.labels,
                datasets: [{
                    data: beneficiaryData.data,
                    backgroundColor: ['#198754', '#0dcaf0', '#6f42c1'],
                    borderColor: 'white',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, weight: '500' }, padding: 15, color: '#6c757d' }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0].label || 'Category';
                            },
                            label: function(context) {
                                const total = beneficiaryData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    'Count: ' + value.toLocaleString(),
                                    'Percentage: ' + percentage + '%'
                                ];
                            },
                            afterLabel: function(context) {
                                const total = beneficiaryData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return 'Share: ' + percentage + '% of ' + total.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});

// 2. ALLOCATION METHOD PIE CHART
document.addEventListener('DOMContentLoaded', function () {
    const allocationData = @json($allocationMethodChart);
    if (allocationData.data && allocationData.data.length > 0) {
        new Chart(document.getElementById('allocationMethodChart'), {
            type: 'doughnut',
            data: {
                labels: allocationData.labels,
                datasets: [{
                    data: allocationData.data,
                    backgroundColor: ['#0d6efd', '#198754'],
                    borderColor: 'white',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, weight: '500' }, padding: 15, color: '#6c757d' }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0].label || 'Method';
                            },
                            label: function(context) {
                                const total = allocationData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    'Count: ' + value.toLocaleString(),
                                    'Percentage: ' + percentage + '%'
                                ];
                            },
                            afterLabel: function(context) {
                                const total = allocationData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                return 'Of Total ' + total.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});

// 3. EVENT STATUS PIE CHART
document.addEventListener('DOMContentLoaded', function () {
    const eventData = @json($eventStatusChart);
    if (eventData.data && eventData.data.length > 0) {
        new Chart(document.getElementById('eventStatusChart'), {
            type: 'doughnut',
            data: {
                labels: eventData.labels,
                datasets: [{
                    data: eventData.data,
                    backgroundColor: ['#ffc107', '#fd7e14', '#198754'],
                    borderColor: 'white',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, weight: '500' }, padding: 15, color: '#6c757d' }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0].label || 'Status';
                            },
                            label: function(context) {
                                const total = eventData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    'Events: ' + value.toLocaleString(),
                                    'Percentage: ' + percentage + '%'
                                ];
                            },
                            afterLabel: function(context) {
                                const total = eventData.data.reduce((a, b) => a + b, 0);
                                return 'Total Events: ' + total.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});

// 4. TOP PROGRAMS BAR CHART
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($topProgramsChart);
    if (chartData.labels && chartData.labels.length > 0) {
        const ctx = document.getElementById('topProgramsChart');
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.8)');
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Beneficiaries Reached',
                    data: chartData.data,
                    backgroundColor: gradient,
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { size: 12, weight: '500' }, padding: 20, color: '#6c757d' }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function(context) {
                                return context[0]?.label || 'Program';
                            },
                            label: function(context) {
                                const value = context.parsed.x;
                                const total = chartData.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    'Beneficiaries: ' + value.toLocaleString(),
                                    'Share: ' + percentage + '% of total'
                                ];
                            },
                            afterLabel: function(context) {
                                const total = chartData.data.reduce((a, b) => a + b, 0);
                                return 'Total: ' + total.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { font: { size: 12 }, color: '#6c757d', callback: function(value) { return value.toLocaleString(); } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    y: {
                        ticks: { font: { size: 12, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// 5. RESOURCE TYPE DISTRIBUTION
document.addEventListener('DOMContentLoaded', function () {
    const resourceData = @json($resourceTypeDistribution ?? ['labels' => [], 'data' => []]);
    if (resourceData.data && resourceData.data.length > 0) {
        const colors = ['#0d6efd', '#198754', '#fd7e14', '#20c997', '#6f42c1', '#dc3545', '#00bcd4', '#e91e63'];
        new Chart(document.getElementById('resourceTypeChart'), {
            type: 'bar',
            data: {
                labels: resourceData.labels,
                datasets: [{
                    label: 'Distribution Count',
                    data: resourceData.data,
                    backgroundColor: colors.slice(0, resourceData.data.length),
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'x',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0]?.label || 'Resource Type';
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const total = resourceData.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return ['Count: ' + value.toLocaleString(), 'Share: ' + percentage + '%'];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { size: 11 }, color: '#6c757d', callback: function(value) { return value.toLocaleString(); } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { size: 11, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// 6. ASSISTANCE PURPOSE DISTRIBUTION
document.addEventListener('DOMContentLoaded', function () {
    const purposeData = @json($assistancePurposeDistribution ?? ['labels' => [], 'data' => []]);
    if (purposeData.data && purposeData.data.length > 0) {
        new Chart(document.getElementById('assistancePurposeChart'), {
            type: 'doughnut',
            data: {
                labels: purposeData.labels,
                datasets: [{
                    data: purposeData.data,
                    backgroundColor: ['#6f42c1', '#20c997', '#fd7e14', '#0d6efd', '#198754', '#dc3545', '#00bcd4', '#e91e63'],
                    borderColor: 'white',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { size: 11, weight: '500' }, padding: 10, color: '#6c757d', usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0].label || 'Purpose';
                            },
                            label: function(context) {
                                const total = purposeData.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return [
                                    'Count: ' + value.toLocaleString(),
                                    'Percentage: ' + percentage + '%'
                                ];
                            },
                            afterLabel: function(context) {
                                const total = purposeData.data.reduce((a, b) => a + b, 0);
                                return 'Of Total ' + total.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});

// 7. GEOGRAPHIC COVERAGE BY BARANGAY
document.addEventListener('DOMContentLoaded', function () {
    const barangayData = @json($barangayDistribution ?? ['labels' => [], 'data' => []]);
    if (barangayData.data && barangayData.data.length > 0) {
        const ctx = document.getElementById('barangayChart');
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(32, 201, 151, 0.8)');
        gradient.addColorStop(1, 'rgba(32, 201, 151, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: barangayData.labels,
                datasets: [{
                    label: 'Beneficiaries',
                    data: barangayData.data,
                    backgroundColor: gradient,
                    borderColor: 'rgba(32, 201, 151, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0]?.label || 'Barangay';
                            },
                            label: function(context) {
                                const value = context.parsed.x;
                                const total = barangayData.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return ['Beneficiaries: ' + value.toLocaleString(), 'Coverage: ' + percentage + '%'];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { font: { size: 11 }, color: '#6c757d', callback: function(value) { return value.toLocaleString(); } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    y: {
                        ticks: { font: { size: 11, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// 8. MONTHLY TREND
document.addEventListener('DOMContentLoaded', function () {
    const trendData = @json($monthlyTrendData ?? ['labels' => [], 'data' => []]);
    if (trendData.data && trendData.data.length > 0) {
        const ctx = document.getElementById('monthlyTrendChart');
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(255, 99, 132, 0.8)');
        gradient.addColorStop(1, 'rgba(255, 99, 132, 0.2)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: 'Allocations',
                    data: trendData.data,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    borderRadius: 8,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return context[0]?.label || 'Month';
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                return 'Allocations: ' + value.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { size: 11 }, color: '#6c757d', callback: function(value) { return value.toLocaleString(); } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { size: 11, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// 9. PROGRAM DISBURSEMENT AMOUNT
document.addEventListener('DOMContentLoaded', function () {
    const disbursementData = @json($programDisbursementChart ?? ['labels' => [], 'data' => []]);
    if (disbursementData.data && disbursementData.data.length > 0) {
        const ctx = document.getElementById('programDisbursementChart');
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(111, 66, 193, 0.85)');
        gradient.addColorStop(1, 'rgba(111, 66, 193, 0.25)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: disbursementData.labels,
                datasets: [{
                    label: 'Disbursed Amount',
                    data: disbursementData.data,
                    backgroundColor: gradient,
                    borderColor: 'rgba(111, 66, 193, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return 'Program: ' + (context[0]?.label || 'N/A');
                            },
                            label: function(context) {
                                return 'Amount: ₱' + Number(context.parsed.x || 0).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            color: '#6c757d',
                            callback: function(value) { return '₱' + Number(value).toLocaleString(); }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    y: {
                        ticks: { font: { size: 11, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// 10. MONTHLY RELEASE METHOD TREND
document.addEventListener('DOMContentLoaded', function () {
    const methodTrend = @json($monthlyReleaseMethodTrend ?? ['labels' => [], 'event' => [], 'direct' => []]);
    if (methodTrend.labels && methodTrend.labels.length > 0) {
        new Chart(document.getElementById('methodTrendChart'), {
            type: 'line',
            data: {
                labels: methodTrend.labels,
                datasets: [
                    {
                        label: 'Event-Based',
                        data: methodTrend.event,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        borderWidth: 3,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                    },
                    {
                        label: 'Direct',
                        data: methodTrend.direct,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.15)',
                        borderWidth: 3,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, weight: '500' }, padding: 14, color: '#6c757d' }
                    },
                    tooltip: {
                        ...tooltipConfig,
                        callbacks: {
                            title: function(context) {
                                return 'Month: ' + (context[0]?.label || 'N/A');
                            },
                            label: function(context) {
                                return (context.dataset.label || 'Series') + ': ' + Number(context.parsed.y || 0).toLocaleString() + ' allocations';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { size: 11 }, color: '#6c757d', callback: function(value) { return Number(value).toLocaleString(); } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { size: 11, weight: '500' }, color: '#1a1d29' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    }
});

// ===================================
// DRILL-DOWN MODAL LOGIC
// ===================================

function formatCurrency(value) {
    return '₱' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '<span class="text-muted">—</span>';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function escapeHtml(str) {
    if (!str) return '<span class="text-muted">—</span>';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function statusBadge(status, color) {
    const colorMap = {
        'Disbursed': 'badge-soft-success',
        'Pending': 'badge-soft-warning',
        'released': 'badge-soft-success',
        'planned': 'badge-soft-warning',
        'ready_for_release': 'badge-soft-info',
        'not_received': 'badge-soft-danger',
    };
    const cls = colorMap[status] || 'badge-soft-secondary';
    const label = status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    return `<span class="badge ${cls}">${label}</span>`;
}

// === FINANCIAL UTILIZATION ===
let financialLoaded = false;
document.getElementById('financialUtilizationModal').addEventListener('show.bs.modal', function () {
    if (!financialLoaded) {
        loadFinancialUtilization();
        financialLoaded = true;
    }
});

function loadFinancialUtilization(filter) {
    const body = document.getElementById('financialUtilizationBody');
    body.innerHTML = '<div class="drill-loading"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading financial data...</p></div>';

    const url = new URL('/api/dashboard/financial-utilization', window.location.origin);
    if (filter) url.searchParams.set('filter', filter);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const s = data.summary;
            let html = `
                <div class="drill-summary-grid">
                    <div class="drill-summary-card">
                        <div class="value">${formatCurrency(s.total_budget)}</div>
                        <div class="label">Total Budget</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #198754;">
                        <div class="value" style="color: #198754;">${formatCurrency(s.disbursed)}</div>
                        <div class="label">Disbursed</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #ffc107;">
                        <div class="value" style="color: #b45309;">${formatCurrency(s.pending)}</div>
                        <div class="label">Pending</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #0d6efd;">
                        <div class="value" style="color: #0d6efd;">${s.utilization_rate}%</div>
                        <div class="label">Utilization Rate</div>
                    </div>
                </div>

                <div class="drill-filter-tabs">
                    <button class="btn ${!filter || filter === '' ? 'btn-primary' : 'btn-outline-secondary'}" onclick="loadFinancialUtilization()">All</button>
                    <button class="btn ${filter === 'disbursed' ? 'btn-success' : 'btn-outline-secondary'}" onclick="loadFinancialUtilization('disbursed')">Disbursed</button>
                    <button class="btn ${filter === 'pending' ? 'btn-warning' : 'btn-outline-secondary'}" onclick="loadFinancialUtilization('pending')">Pending</button>
                </div>

                <div class="mb-2 text-muted" style="font-size:12px;"><i class="bi bi-info-circle me-1"></i>Showing ${data.records.length.toLocaleString()} allocation(s)</div>
            `;

            if (data.records.length === 0) {
                html += '<div class="drill-empty"><i class="bi bi-inbox"></i><p>No allocations found</p></div>';
            } else {
                html += `<div class="drill-table-wrapper"><table class="table table-hover table-sm">
                    <thead><tr>
                        <th>#</th><th>Beneficiary</th><th>Program</th><th>Resource</th><th>Amount</th><th>Method</th><th>Status</th><th>Distributed</th>
                    </tr></thead><tbody>`;
                data.records.forEach((r, i) => {
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td>${escapeHtml(r.beneficiary_name)}</td>
                        <td>${escapeHtml(r.program_name)}</td>
                        <td>${escapeHtml(r.resource_type)}</td>
                        <td class="fw-semibold">${formatCurrency(r.amount)}</td>
                        <td><span class="badge ${r.release_method === 'event' ? 'badge-soft-primary' : 'badge-soft-info'}">${r.release_method === 'event' ? 'Event' : 'Direct'}</span></td>
                        <td>${statusBadge(r.status)}</td>
                        <td>${formatDate(r.distributed_at)}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
            }
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="drill-empty"><i class="bi bi-exclamation-triangle text-danger"></i><p>Failed to load data. Please try again.</p></div>';
            console.error('Financial drill-down error:', err);
        });
}

// === COVERAGE GAP ===
let coverageLoaded = false;
document.getElementById('coverageGapModal').addEventListener('show.bs.modal', function () {
    if (!coverageLoaded) {
        loadCoverageGap('unreached');
        coverageLoaded = true;
    }
});

function loadCoverageGap(filter) {
    const body = document.getElementById('coverageGapBody');
    body.innerHTML = '<div class="drill-loading"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading beneficiary data...</p></div>';

    const url = new URL('/api/dashboard/coverage-gap', window.location.origin);
    if (filter) url.searchParams.set('filter', filter);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const s = data.summary;
            let html = `
                <div class="drill-summary-grid">
                    <div class="drill-summary-card">
                        <div class="value">${s.total_beneficiaries.toLocaleString()}</div>
                        <div class="label">Total Beneficiaries</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #198754;">
                        <div class="value" style="color: #198754;">${s.reached.toLocaleString()}</div>
                        <div class="label">Reached</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #dc3545;">
                        <div class="value" style="color: #dc3545;">${s.unreached.toLocaleString()}</div>
                        <div class="label">Unreached</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #dc3545;">
                        <div class="value" style="color: #dc3545;">${s.gap_percentage}%</div>
                        <div class="label">Gap Rate</div>
                    </div>
                </div>

                <div class="drill-filter-tabs">
                    <button class="btn ${data.filter === 'unreached' ? 'btn-danger' : 'btn-outline-secondary'}" onclick="loadCoverageGap('unreached')">
                        <i class="bi bi-person-x me-1"></i>Unreached (${s.unreached.toLocaleString()})
                    </button>
                    <button class="btn ${data.filter === 'reached' ? 'btn-success' : 'btn-outline-secondary'}" onclick="loadCoverageGap('reached')">
                        <i class="bi bi-person-check me-1"></i>Reached (${s.reached.toLocaleString()})
                    </button>
                </div>

                <div class="mb-2 text-muted" style="font-size:12px;"><i class="bi bi-info-circle me-1"></i>Showing ${data.records.length.toLocaleString()} beneficiar${data.records.length === 1 ? 'y' : 'ies'}</div>
            `;

            if (data.records.length === 0) {
                html += '<div class="drill-empty"><i class="bi bi-inbox"></i><p>No beneficiaries found</p></div>';
            } else {
                html += `<div class="drill-table-wrapper"><table class="table table-hover table-sm">
                    <thead><tr>
                        <th>#</th><th>Name</th><th>Classification</th><th>Barangay</th><th>Agency</th><th>Contact</th><th>Status</th>
                    </tr></thead><tbody>`;
                data.records.forEach((r, i) => {
                    const classBadge = r.classification === 'Farmer' ? 'badge-soft-success' :
                                       r.classification === 'Fisherfolk' ? 'badge-soft-info' : 'badge-soft-purple';
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td class="fw-semibold">${escapeHtml(r.full_name)}</td>
                        <td><span class="badge ${classBadge}">${escapeHtml(r.classification)}</span></td>
                        <td>${escapeHtml(r.barangay_name)}</td>
                        <td>${escapeHtml(r.agency_name)}</td>
                        <td>${escapeHtml(r.contact_number)}</td>
                        <td>${r.status === 'active' ? '<span class="badge badge-soft-success">Active</span>' : '<span class="badge badge-soft-secondary">' + escapeHtml(r.status) + '</span>'}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
            }
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="drill-empty"><i class="bi bi-exclamation-triangle text-danger"></i><p>Failed to load data. Please try again.</p></div>';
            console.error('Coverage gap drill-down error:', err);
        });
}

// === ALLOCATION RATE ===
let allocationLoaded = false;
document.getElementById('allocationRateModal').addEventListener('show.bs.modal', function () {
    if (!allocationLoaded) {
        loadAllocationRate('all');
        allocationLoaded = true;
    }
});

function loadAllocationRate(filter) {
    const body = document.getElementById('allocationRateBody');
    body.innerHTML = '<div class="drill-loading"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading allocation data...</p></div>';

    const url = new URL('/api/dashboard/allocation-rate', window.location.origin);
    if (filter && filter !== 'all') url.searchParams.set('filter', filter);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const s = data.summary;
            let html = `
                <div class="drill-summary-grid">
                    <div class="drill-summary-card">
                        <div class="value">${s.total_allocations.toLocaleString()}</div>
                        <div class="label">Total Allocations</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #198754;">
                        <div class="value" style="color: #198754;">${s.distributed.toLocaleString()}</div>
                        <div class="label">Distributed</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #ffc107;">
                        <div class="value" style="color: #b45309;">${s.pending.toLocaleString()}</div>
                        <div class="label">Pending</div>
                    </div>
                    <div class="drill-summary-card" style="border-color: #198754;">
                        <div class="value" style="color: #198754;">${s.rate}%</div>
                        <div class="label">Overall Rate</div>
                    </div>
                </div>

                <div class="drill-summary-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="drill-summary-card" style="border-left: 3px solid #0d6efd;">
                        <div class="value" style="font-size:16px;">${s.event_distributed}/${s.event_total}</div>
                        <div class="label">Event-Based</div>
                    </div>
                    <div class="drill-summary-card" style="border-left: 3px solid #198754;">
                        <div class="value" style="font-size:16px;">${s.direct_distributed}/${s.direct_total}</div>
                        <div class="label">Direct Release</div>
                    </div>
                </div>

                <div class="drill-filter-tabs">
                    <button class="btn ${!filter || filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'}" onclick="loadAllocationRate('all')">All</button>
                    <button class="btn ${filter === 'distributed' ? 'btn-success' : 'btn-outline-secondary'}" onclick="loadAllocationRate('distributed')">Distributed</button>
                    <button class="btn ${filter === 'pending' ? 'btn-warning' : 'btn-outline-secondary'}" onclick="loadAllocationRate('pending')">Pending</button>
                </div>

                <div class="mb-2 text-muted" style="font-size:12px;"><i class="bi bi-info-circle me-1"></i>Showing ${data.records.length.toLocaleString()} allocation(s)</div>
            `;

            if (data.records.length === 0) {
                html += '<div class="drill-empty"><i class="bi bi-inbox"></i><p>No allocations found</p></div>';
            } else {
                html += `<div class="drill-table-wrapper"><table class="table table-hover table-sm">
                    <thead><tr>
                        <th>#</th><th>Beneficiary</th><th>Program</th><th>Resource</th><th>Method</th><th>Amount</th><th>Status</th><th>Distributed</th>
                    </tr></thead><tbody>`;
                data.records.forEach((r, i) => {
                    let rstatus = 'Planned';
                    if (r.distributed_at) rstatus = 'Released';
                    else if (r.release_outcome === 'not_received') rstatus = 'Not Received';
                    else if (r.is_ready_for_release) rstatus = 'Ready';

                    const statusCls = {
                        'Released': 'badge-soft-success',
                        'Ready': 'badge-soft-info',
                        'Not Received': 'badge-soft-danger',
                        'Planned': 'badge-soft-warning'
                    }[rstatus] || 'badge-soft-secondary';

                    html += `<tr>
                        <td>${i + 1}</td>
                        <td class="fw-semibold">${escapeHtml(r.beneficiary_name)}</td>
                        <td>${escapeHtml(r.program_name)}</td>
                        <td>${escapeHtml(r.resource_type)}</td>
                        <td><span class="badge ${r.release_method === 'event' ? 'badge-soft-primary' : 'badge-soft-info'}">${r.release_method === 'event' ? 'Event' : 'Direct'}</span></td>
                        <td>${formatCurrency(r.amount)}</td>
                        <td><span class="badge ${statusCls}">${rstatus}</span></td>
                        <td>${formatDate(r.distributed_at)}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
            }
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="drill-empty"><i class="bi bi-exclamation-triangle text-danger"></i><p>Failed to load data. Please try again.</p></div>';
            console.error('Allocation rate drill-down error:', err);
        });
}

// Reset loaded flags when modals are hidden so data refreshes on next open
document.getElementById('financialUtilizationModal').addEventListener('hidden.bs.modal', () => { financialLoaded = false; });
document.getElementById('coverageGapModal').addEventListener('hidden.bs.modal', () => { coverageLoaded = false; });
document.getElementById('allocationRateModal').addEventListener('hidden.bs.modal', () => { allocationLoaded = false; });

</script>
@endpush
