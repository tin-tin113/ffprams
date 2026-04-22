<div
    class="tab-pane fade @if($activeTab === 'overview') show active @endif"
    id="reports-pane-overview"
    role="tabpanel"
    aria-labelledby="reports-tab-overview"
    data-report-pane="overview"
>
    <!-- Command Center: High-Signal Metrics -->
    <div class="command-center no-print">
        <div class="command-card">
            <div class="command-icon shadow-sm" style="background: #ecfdf5; color: #059669;">
                <i class="bi bi-people-fill"></i>
            </div>
            <span class="command-label">Annual Reach</span>
            <span class="command-value">{{ number_format($currYearTotalReach) }}</span>
            <div class="command-footer">
                @if($yoyGrowth > 0)
                    <span class="command-yoy yoy-up"><i class="bi bi-graph-up-arrow"></i> {{ number_format($yoyGrowth, 1) }}%</span>
                @elseif($yoyGrowth < 0)
                    <span class="command-yoy yoy-down"><i class="bi bi-graph-down-arrow"></i> {{ number_format(abs($yoyGrowth), 1) }}%</span>
                @else
                    <span class="command-yoy yoy-neutral">--%</span>
                @endif
                <span class="command-meta">YoY Growth</span>
            </div>
        </div>

        <div class="command-card">
            <div class="command-icon shadow-sm" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <span class="command-label">Outreach Velocity</span>
            <span class="command-value">{{ number_format($avgOutreachDays, 1) }}</span>
            <div class="command-footer">
                <span class="command-meta">Avg days from plan to distribution</span>
            </div>
        </div>

        <div class="command-card">
            <div class="command-icon shadow-sm" style="background: #fff7ed; color: #ea580c;">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <span class="command-label">Liquidation Risk</span>
            <span class="command-value">{{ number_format($complianceOverview->liquidation_pending) }}</span>
            <div class="command-footer">
                <span class="command-yoy {{ $complianceOverview->liquidation_pending > 5 ? 'yoy-down' : 'yoy-neutral' }}">
                    {{ $complianceOverview->liquidation_pending > 0 ? 'Action Reqd' : 'Healthy' }}
                </span>
                <span class="command-meta">Pending Liquidations</span>
            </div>
        </div>

        <div class="command-card">
            <div class="command-icon shadow-sm" style="background: #fdf2f8; color: #db2777;">
                <i class="bi bi-check2-all"></i>
            </div>
            <span class="command-label">Delivery Completion</span>
            <span class="command-value">{{ number_format($kpiCompletedEvents) }}</span>
            <div class="command-footer">
                <span class="command-meta">Events closed in {{ $currentYear }}</span>
            </div>
        </div>
    </div>

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
