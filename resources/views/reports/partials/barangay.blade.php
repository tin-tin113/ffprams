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
