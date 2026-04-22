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
