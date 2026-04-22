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
