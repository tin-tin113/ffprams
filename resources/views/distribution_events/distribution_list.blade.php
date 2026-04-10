@extends('layouts.app')

@section('title', 'Distribution List')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.show', $event) }}">Event #{{ $event->id }}</a></li>
    <li class="breadcrumb-item active">Distribution List</li>
@endsection

@push('styles')
<style>
    .distribution-sheet {
        max-width: 1320px;
        margin: 0 auto;
    }

    .list-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .print-sheet {
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        padding: 18px;
    }

    .title-wrap {
        text-align: center;
        margin-bottom: 10px;
    }

    .title-wrap .municipality {
        font-size: 10px;
        text-transform: uppercase;
        color: #4b5563;
        letter-spacing: 0.06em;
    }

    .title-wrap h2 {
        margin: 4px 0 0;
        font-size: 16px;
        letter-spacing: 0.04em;
    }

    table.meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    table.meta td {
        width: 25%;
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        vertical-align: top;
    }

    table.meta .label {
        display: block;
        font-size: 9px;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 2px;
        letter-spacing: 0.04em;
    }

    table.meta .value {
        font-weight: 600;
        color: #111827;
    }

    table.list {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        min-width: 900px;
    }

    table.list th,
    table.list td {
        border: 1px solid #cbd5e1;
        padding: 5px 6px;
        vertical-align: middle;
    }

    table.list th {
        background: #f1f5f9;
        text-transform: uppercase;
        font-size: 9px;
        letter-spacing: 0.04em;
        text-align: center;
        white-space: normal;
        line-height: 1.2;
    }

    table.list td {
        font-size: 11px;
    }

    .col-num {
        width: 30px;
        text-align: center;
    }

    .col-class {
        width: 90px;
        text-align: center;
    }

    .col-contact {
        width: 125px;
        text-align: center;
        white-space: nowrap !important;
    }

    .col-barangay {
        width: 130px;
    }

    .col-amount {
        width: 130px;
        text-align: center;
        white-space: nowrap !important;
        font-weight: 600;
    }

    .col-signature {
        width: 120px;
    }

    .col-remarks {
        width: 270px;
    }

    .signature {
        min-height: 22px;
    }

    .text-center {
        text-align: center;
    }

    .footer {
        margin-top: 10px;
        font-size: 10px;
    }

    .footer .line {
        margin-top: 14px;
        display: inline-block;
        min-width: 220px;
        border-top: 1px solid #6b7280;
        padding-top: 2px;
        text-align: center;
        color: #374151;
    }

    .footer .line + .line {
        margin-left: 20px;
    }

    @media print {
        @page {
            size: A4 landscape !important;
            margin: 10mm;
        }

        .top-navbar,
        .sidebar,
        .no-print,
        .alert,
        .header-right,
        .header-left {
            display: none !important;
        }

        html,
        body {
            width: 297mm;
        }

        body {
            background: #fff !important;
            font-size: 11px;
            margin: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .main-content {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }

        .container-fluid.distribution-sheet {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-sheet {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }

        table.list {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
        }

        .list-wrap {
            overflow: visible !important;
        }

        table.list thead {
            display: table-header-group;
        }

        table.list tr {
            page-break-inside: avoid;
        }

        table.list th,
        table.list td {
            padding: 5px 6px !important;
        }
    }

    @media (max-width: 991.98px) {
        .distribution-sheet {
            max-width: 100%;
        }

        .print-sheet {
            padding: 12px;
        }

        table.meta td {
            width: 50%;
        }

        table.list {
            min-width: 860px;
        }
    }

    @media (max-width: 575.98px) {
        .print-sheet {
            padding: 10px;
        }

        .title-wrap h2 {
            font-size: 14px;
        }

        table.meta td {
            width: 100%;
            display: block;
        }

        table.list {
            min-width: 820px;
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 no-print">
        <div>
            <h1 class="h4 mb-1">Printable Distribution List</h1>
            <p class="text-muted mb-0">Formatted release sheet with signatures and remarks tracking.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="{{ route('distribution-events.show', $event) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('distribution-events.distributionListPdf', ['event' => $event, 'inline' => 1]) }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            <a href="{{ route('distribution-events.distributionListPdf', $event) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('distribution-events.distributionListCsv', $event) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
            </a>
        </div>
    </div>

    <div class="print-sheet">
        <div class="title-wrap">
            <div class="municipality">Municipality of Enrique B. Magalona</div>
            <h2>Distribution List</h2>
        </div>

        <table class="meta">
            <tr>
                <td>
                    <span class="label">Agency</span>
                    <span class="value">{{ $event->resourceType->agency->name ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="label">Program</span>
                    <span class="value">{{ $event->programName->name ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="label">Barangay</span>
                    <span class="value">{{ $event->barangay->name }}</span>
                </td>
                <td>
                    <span class="label">Distribution Date</span>
                    <span class="value">{{ $event->distribution_date->format('M d, Y') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Resource</span>
                    <span class="value">{{ $event->resourceType->name }}</span>
                </td>
                <td>
                    <span class="label">Type</span>
                    <span class="value">{{ $event->isFinancial() ? 'Financial Assistance' : 'Physical Resource' }}</span>
                </td>
                <td>
                    <span class="label">Total Rows</span>
                    <span class="value">{{ number_format($totalAllocations) }}</span>
                </td>
                <td>
                    <span class="label">Received / Not Received</span>
                    <span class="value">{{ number_format($totalReceived) }} / {{ number_format($totalNotReceived) }}</span>
                </td>
            </tr>
        </table>

        <div class="list-wrap">
            <table class="list">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th>Beneficiary Name</th>
                        <th class="col-class">Class</th>
                        <th class="col-contact">Contact</th>
                        <th class="col-barangay">Barangay</th>
                        <th class="col-amount">{{ $event->isFinancial() ? 'Amount (PHP)' : 'Quantity' }}</th>
                        <th class="col-signature">Signature</th>
                        <th class="col-remarks">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($event->allocations as $allocation)
                        <tr>
                            <td class="col-num">{{ $loop->iteration }}</td>
                            <td>{{ $allocation->beneficiary->full_name }}</td>
                            <td class="col-class">{{ $allocation->beneficiary->classification }}</td>
                            <td class="col-contact">{{ $allocation->beneficiary->contact_number ?? '—' }}</td>
                            <td>{{ $event->barangay->name }}</td>
                            <td class="col-amount">
                                @if($event->isFinancial())
                                    {{ number_format((float) $allocation->amount, 2) }}
                                @else
                                    {{ number_format((float) $allocation->quantity, 2) }} {{ $event->resourceType->unit }}
                                @endif
                            </td>
                            <td class="col-signature"><div class="signature"></div></td>
                            <td>{{ $allocation->remarks ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No allocations recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <span class="line">Prepared by</span>
            <span class="line">Verified by</span>
        </div>
    </div>
</div>
@endsection
