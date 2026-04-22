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
            <div class="beneficiary-kpi-label">Unreached Total</div>
            <div class="beneficiary-kpi-value">{{ number_format($unreachedTotal) }}</div>
            <div class="beneficiary-kpi-meta">Focus area for {{ $selectedYear }} outreach</div>
        </div>
        <div class="beneficiary-kpi-card">
            <div class="beneficiary-kpi-label">Top Priority Barangay</div>
            <div class="beneficiary-kpi-value text-truncate" title="{{ $topPriorityOutreachBarangay }}">{{ $topPriorityOutreachBarangay }}</div>
            <div class="beneficiary-kpi-meta">{{ number_format($topPriorityOutreachCount) }} target records</div>
        </div>
        <div class="beneficiary-kpi-card">
            <div class="beneficiary-kpi-label">Dominant Group</div>
            <div class="beneficiary-kpi-value">{{ $dominantBeneficiaryMixLabel }}</div>
            <div class="beneficiary-kpi-meta">{{ number_format($dominantBeneficiaryMixPercent, 1) }}% of registry</div>
        </div>
    </div>

    <div class="beneficiary-analytics-grid mb-4">
        <div class="card report-card border-0 beneficiary-analytics-card">
            <div class="card-header report-card-header">
                <span class="report-card-title"><i class="bi bi-pie-chart me-1"></i> Registry Composition</span>
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
                                    $mixLabel = $mixRow->label ?? 'Unknown';
                                    $mixValue = $mixRow->value ?? 0;
                                    $mixPercent = $mixRow->percent ?? 0;
                                    $mixColor = $mixRow->color ?? '#6b7280';
                                @endphp
                                <div class="beneficiary-mix-item">
                                    <span class="beneficiary-mix-dot" style="background-color: {{ $mixColor }}"></span>
                                    <div>
                                        <div class="fw-semibold text-dark small">{{ $mixLabel }}</div>
                                        <div class="text-muted small">{{ number_format($mixValue) }} records · {{ number_format($mixPercent, 1) }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="beneficiary-empty">No registry data.</div>
                @endif
            </div>
        </div>

        <div class="card report-card border-0 beneficiary-analytics-card">
            <div class="card-header report-card-header">
                <span class="report-card-title"><i class="bi bi-check-circle me-1"></i> Reach Sensitivity</span>
                <span class="badge bg-light text-dark fw-normal border ms-2">Reached vs Unreached</span>
            </div>
            <div class="card-body">
                <div class="report-chart-wrap">
                    <canvas id="classificationReachChart"></canvas>
                </div>
                <div class="mt-3 small text-muted">
                    This shows how effectively each primary classification groups are being reached in {{ $selectedYear }}.
                </div>
            </div>
        </div>
    </div>

    <div class="card report-card border-0 mb-4">
        <div class="card-header report-card-header">
            <span class="report-card-title"><i class="bi bi-geo me-1"></i> Outreach Priority by Barangay</span>
        </div>
        <div class="card-body">
            @if($priorityOutreachBarangays->count())
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <p class="beneficiary-priority-note mb-4">
                            Highest priority: <strong>{{ $topPriorityOutreachBarangay }}</strong>
                            <span class="text-muted">({{ number_format($topPriorityOutreachCount) }} unreached)</span>
                        </p>
                        @php
                            $maxPriorityCount = max(1, (int) $priorityOutreachBarangays->max('count'));
                        @endphp
                        <div class="priority-list">
                            @foreach($priorityOutreachBarangays as $priorityRow)
                                <div class="priority-item mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-bold">{{ $priorityRow->barangay_name }}</span>
                                        <span class="small text-muted">{{ number_format($priorityRow->count) }} unreached</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" style="width: {{ ($priorityRow->count / $maxPriorityCount) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="report-chart-wrap" style="height: 300px;">
                            <canvas id="beneficiaryPriorityChart"></canvas>
                        </div>
                    </div>
                </div>
            @else
                <div class="beneficiary-empty">All registered beneficiaries in the priority list have been reached for {{ $selectedYear }}.</div>
            @endif
        </div>
    </div>

    @if($beneficiariesPerBarangay->count())
        <div class="card report-card border-0 mb-4">
            <div class="card-header report-card-header">
                <span class="report-card-title"><i class="bi bi-bar-chart-stacked me-1"></i> Beneficiary Composition by Barangay (Top 10)</span>
            </div>
            <div class="card-body">
                <div class="report-chart-wrap">
                    <canvas id="beneficiaryCompositionByBarangayChart"></canvas>
                </div>
            </div>
        </div>
    @endif

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
                                <td class="text-center">{{ number_format($beneficiariesPerBarangay->sum('grand_total')) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="card report-card border-0 mb-4 bg-light shadow-none">
        <div class="card-body p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-person-x fs-1 text-muted"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Priority Outreach Opportunity</h4>
            <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                We have identified <strong>{{ number_format($unreachedTotal) }} beneficiaries</strong> who have not received any assistance this year. Targeting these individuals can improve your overall coverage rate.
            </p>
            <button type="button" class="btn btn-dark px-4 py-2 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#unreachedBeneficiariesModal">
                <i class="bi bi-eye me-2"></i> View Full Unreached List
            </button>
        </div>
    </div>
</div>
