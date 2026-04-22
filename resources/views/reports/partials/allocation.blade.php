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
