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
    <!-- NEW: Liquidation Health Gauge -->
    @php
        $hasPendingReports = isset($liquidationAging) && (($liquidationAging->bucket_30 ?? 0) + ($liquidationAging->bucket_60 ?? 0) + ($liquidationAging->bucket_90 ?? 0) + ($liquidationAging->bucket_over_90 ?? 0)) > 0;
    @endphp
    @if($hasPendingReports)
    <div class="card report-card border-0 mb-4 bg-light shadow-none">
        <div class="card-header report-card-header bg-transparent">
            <span class="report-card-title"><i class="bi bi-activity me-1"></i> Liquidation Health (Aging of Pending Reports)</span>
        </div>
        <div class="card-body">
            <div class="health-gauge-container">
                <div class="health-bucket border-success bg-white">
                    <span class="bucket-label text-success">Healthy (< 30 Days)</span>
                    <span class="bucket-value">{{ number_format($liquidationAging->bucket_30 ?? 0) }}</span>
                </div>
                <div class="health-bucket border-warning bg-white">
                    <span class="bucket-label text-warning">Warning (31-60 Days)</span>
                    <span class="bucket-value">{{ number_format($liquidationAging->bucket_60 ?? 0) }}</span>
                </div>
                <div class="health-bucket border-danger bg-white">
                    <span class="bucket-label text-danger">At Risk (61-90 Days)</span>
                    <span class="bucket-value">{{ number_format($liquidationAging->bucket_90 ?? 0) }}</span>
                </div>
                <div class="health-bucket border-dark bg-white" style="border-width: 2px;">
                    <span class="bucket-label text-dark">Overdue (> 90 Days)</span>
                    <span class="bucket-value">{{ number_format($liquidationAging->bucket_over_90 ?? 0) }}</span>
                </div>
            </div>
            <div class="mt-3 small text-muted text-center italic">
                <i class="bi bi-info-circle me-1"></i> Aging is calculated from the distribution date or liquidation due date for financial events.
            </div>
        </div>
    </div>
    @endif

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
