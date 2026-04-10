@extends('layouts.app')

@section('title', 'Distribution List')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.show', $event) }}">Event #{{ $event->id }}</a></li>
    <li class="breadcrumb-item active">Distribution List</li>
@endsection

@push('styles')
<style>
    .distribution-sheet .doc-title {
        letter-spacing: 0.04em;
    }

    .distribution-sheet .meta-label {
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .distribution-sheet .meta-value {
        font-weight: 600;
        color: #0f172a;
    }

    .distribution-table thead th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .distribution-table td {
        vertical-align: middle;
    }

    .signature-cell {
        min-width: 150px;
        height: 34px;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        .top-navbar,
        .sidebar,
        .no-print,
        .btn,
        .alert,
        .header-right,
        .header-left {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }

        .distribution-table {
            font-size: 11px;
        }

        .distribution-table thead {
            display: table-header-group;
        }

        .distribution-table tr {
            page-break-inside: avoid;
        }

        .distribution-table th,
        .distribution-table td {
            padding: 0.35rem 0.4rem !important;
        }

        .signature-cell {
            min-width: 120px;
            height: 28px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid distribution-sheet">
    @php
        $totalAllocations = $event->allocations->count();
        $totalReceived = $event->allocations->whereNotNull('distributed_at')->count();
        $totalNotReceived = $event->allocations->where('release_outcome', 'not_received')->count();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h1 class="h4 mb-1">Printable Distribution List</h1>
            <p class="text-muted mb-0">Formatted release sheet with signatures and release outcome tracking.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('distribution-events.distributionListPdf', $event) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('distribution-events.distributionListCsv', $event) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="small text-uppercase text-muted">Municipality of Enrique B. Magalona</div>
                <h2 class="h5 mb-0 doc-title">Distribution List</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="meta-label">Agency</div>
                    <div class="meta-value">{{ $event->resourceType->agency->name ?? 'N/A' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="meta-label">Program</div>
                    <div class="meta-value">{{ $event->programName->name ?? 'N/A' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="meta-label">Barangay</div>
                    <div class="meta-value">{{ $event->barangay->name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="meta-label">Distribution Date</div>
                    <div class="meta-value">{{ $event->distribution_date->format('M d, Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="meta-label">Resource</div>
                    <div class="meta-value">{{ $event->resourceType->name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="meta-label">Distribution Type</div>
                    <div class="meta-value">{{ $event->isFinancial() ? 'Financial Assistance' : 'Physical Resource' }}</div>
                </div>
                <div class="col-md-2">
                    <div class="meta-label">Total Rows</div>
                    <div class="meta-value">{{ number_format($totalAllocations) }}</div>
                </div>
                <div class="col-md-2">
                    <div class="meta-label">Received</div>
                    <div class="meta-value">{{ number_format($totalReceived) }}</div>
                </div>
                <div class="col-md-2">
                    <div class="meta-label">Not Received</div>
                    <div class="meta-value">{{ number_format($totalNotReceived) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Beneficiary Distribution Table</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 distribution-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 42px;">#</th>
                            <th>Beneficiary Name</th>
                            <th style="width: 100px;">Class</th>
                            <th style="width: 130px;">Contact No.</th>
                            <th style="width: 150px;">Barangay</th>
                            <th style="width: 130px;">{{ $event->isFinancial() ? 'Amount (PHP)' : 'Quantity' }}</th>
                            <th style="width: 150px;">Signature</th>
                            <th style="width: 110px;">Outcome</th>
                            <th style="width: 220px;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($event->allocations as $allocation)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $allocation->beneficiary->full_name }}</td>
                                <td class="text-center">{{ $allocation->beneficiary->classification }}</td>
                                <td class="text-center">{{ $allocation->beneficiary->contact_number ?? '—' }}</td>
                                <td>{{ $event->barangay->name }}</td>
                                <td class="text-center fw-semibold">
                                    @if($event->isFinancial())
                                        {{ number_format((float) $allocation->amount, 2) }}
                                    @else
                                        {{ number_format((float) $allocation->quantity, 2) }} {{ $event->resourceType->unit }}
                                    @endif
                                </td>
                                <td class="signature-cell"></td>
                                <td class="text-center">
                                    @if($allocation->release_outcome === 'not_received')
                                        Not Received
                                    @elseif($allocation->distributed_at)
                                        Received
                                    @else
                                        Pending
                                    @endif
                                </td>
                                <td>{{ $allocation->remarks ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No allocations recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white small text-muted d-flex justify-content-between flex-wrap gap-2">
            <span>Prepared by: ______________________</span>
            <span>Verified by: ______________________</span>
            <span>Date Printed: {{ now()->format('M d, Y h:i A') }}</span>
        </div>
    </div>
</div>
@endsection
