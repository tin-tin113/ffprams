<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distribution List - Event #{{ $event->id }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
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

        .title-wrap h1 {
            margin: 4px 0 0;
            font-size: 16px;
            letter-spacing: 0.04em;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .meta td {
            width: 25%;
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .meta .label {
            display: block;
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
            letter-spacing: 0.04em;
        }

        .meta .value {
            font-weight: 600;
        }

        table.list {
            width: 100%;
            border-collapse: collapse;
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
        }

        .text-center {
            text-align: center;
        }

        .signature {
            min-height: 22px;
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
    </style>
</head>
<body>
    @php
        $totalAllocations = $event->allocations->count();
        $totalReceived = $event->allocations->whereNotNull('distributed_at')->count();
        $totalNotReceived = $event->allocations->where('release_outcome', 'not_received')->count();
    @endphp

    <div class="title-wrap">
        <div class="municipality">Municipality of Enrique B. Magalona</div>
        <h1>Distribution List</h1>
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

    <table class="list">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Beneficiary Name</th>
                <th style="width: 85px;">Class</th>
                <th style="width: 120px;">Contact</th>
                <th style="width: 130px;">Barangay</th>
                <th style="width: 120px;">{{ $event->isFinancial() ? 'Amount (PHP)' : 'Quantity' }}</th>
                <th style="width: 120px;">Signature</th>
                <th style="width: 270px;">Remarks</th>
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
                    <td class="text-center">
                        @if($event->isFinancial())
                            {{ number_format((float) $allocation->amount, 2) }}
                        @else
                            {{ number_format((float) $allocation->quantity, 2) }} {{ $event->resourceType->unit }}
                        @endif
                    </td>
                    <td class="signature"></td>
                    <td>{{ $allocation->remarks ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No allocations recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="line">Prepared by</span>
        <span class="line" style="margin-left: 20px;">Verified by</span>
    </div>
</body>
</html>
