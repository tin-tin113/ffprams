@extends('layouts.app')

@section('title', 'Geo-Map')

@section('breadcrumb')
    <li class="breadcrumb-item active">Geo-Map</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 550px;
        width: 100%;
        border-radius: 0.375rem;
        z-index: 1;
    }
    .legend-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 2px rgba(0,0,0,.3);
        vertical-align: middle;
        margin-right: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-1"><i class="bi bi-geo-alt me-1"></i> Geo-Map &mdash; Resource Targeting</h1>
        <p class="text-muted mb-0">Municipality of Enrique B. Magalona, Negros Occidental</p>
    </div>

    {{-- Legend Row --}}
    <div class="d-flex flex-wrap gap-3 mb-3">
        <span class="badge bg-light text-dark border px-3 py-2">
            <span class="legend-dot" style="background-color: #28a745;"></span> Completed Distribution
        </span>
        <span class="badge bg-light text-dark border px-3 py-2">
            <span class="legend-dot" style="background-color: #ffc107;"></span> Ongoing Distribution
        </span>
        <span class="badge bg-light text-dark border px-3 py-2">
            <span class="legend-dot" style="background-color: #0d6efd;"></span> Pending Distribution
        </span>
        <span class="badge bg-light text-dark border px-3 py-2">
            <span class="legend-dot" style="background-color: #dc3545;"></span> No Distribution Yet
        </span>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-geo-alt-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Barangays</div>
                        <div class="fs-4 fw-bold" id="stat-total-barangays">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Completed Distribution</div>
                        <div class="fs-4 fw-bold" id="stat-completed">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3" style="background-color: rgba(220, 53, 69, 0.1);">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">No Distribution Yet</div>
                        <div class="fs-4 fw-bold" id="stat-none">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Beneficiaries Mapped</div>
                        <div class="fs-4 fw-bold" id="stat-beneficiaries">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Map Container --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div id="map"></div>
        </div>
    </div>

    {{-- Barangay Info Panel (hidden by default) --}}
    <div class="card border-0 shadow-sm mb-4 d-none" id="barangay-panel">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Barangay Details
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="panel-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1" id="panel-name"></h4>
                    <span class="badge mb-3" id="panel-status-badge"></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="btn btn-sm btn-outline-success" id="panel-view-events">
                        <i class="bi bi-calendar-event me-1"></i> View Distribution Events
                    </a>
                </div>
            </div>
            <hr>
            <div class="row text-center g-3">
                <div class="col-6 col-md">
                    <div class="text-muted small">Total Beneficiaries</div>
                    <div class="fs-5 fw-bold" id="panel-beneficiaries"></div>
                </div>
                <div class="col-6 col-md">
                    <div class="text-muted small">Farmers</div>
                    <div class="fs-5 fw-bold text-success" id="panel-farmers"></div>
                </div>
                <div class="col-6 col-md">
                    <div class="text-muted small">Fisherfolk</div>
                    <div class="fs-5 fw-bold text-primary" id="panel-fisherfolk"></div>
                </div>
                <div class="col-6 col-md">
                    <div class="text-muted small">Total Distributed</div>
                    <div class="fs-5 fw-bold text-info" id="panel-distributed"></div>
                </div>
                <div class="col-6 col-md">
                    <div class="text-muted small">Last Distribution</div>
                    <div class="fs-5 fw-bold" id="panel-last-date"></div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize map centered on Enrique B. Magalona
    const map = L.map('map').setView([10.5167, 123.0167], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    const panel            = document.getElementById('barangay-panel');
    const panelName        = document.getElementById('panel-name');
    const panelStatusBadge = document.getElementById('panel-status-badge');
    const panelBeneficiaries = document.getElementById('panel-beneficiaries');
    const panelFarmers     = document.getElementById('panel-farmers');
    const panelFisherfolk  = document.getElementById('panel-fisherfolk');
    const panelDistributed = document.getElementById('panel-distributed');
    const panelLastDate    = document.getElementById('panel-last-date');
    const panelViewEvents  = document.getElementById('panel-view-events');

    // Close panel button
    document.getElementById('panel-close').addEventListener('click', function () {
        panel.classList.add('d-none');
    });

    // Status badge helper
    function statusBadge(status) {
        const map = {
            completed: { label: 'Completed', css: 'bg-success' },
            ongoing:   { label: 'Ongoing',   css: 'bg-warning text-dark' },
            pending:   { label: 'Pending',   css: 'bg-primary' },
            none:      { label: 'None',      css: 'bg-danger' }
        };
        const info = map[status] || map['none'];
        return '<span class="badge ' + info.css + '">' + info.label + '</span>';
    }

    // Fetch map data
    fetch("{{ route('geo-map.data') }}")
        .then(function (response) { return response.json(); })
        .then(function (data) {
            // Update summary cards
            document.getElementById('stat-total-barangays').textContent = data.length;
            document.getElementById('stat-completed').textContent =
                data.filter(function (b) { return b.distribution_status === 'completed'; }).length;
            document.getElementById('stat-none').textContent =
                data.filter(function (b) { return b.distribution_status === 'none'; }).length;
            document.getElementById('stat-beneficiaries').textContent =
                data.reduce(function (sum, b) { return sum + b.total_beneficiaries; }, 0);

            // Place markers
            data.forEach(function (b) {
                if (!b.latitude || !b.longitude) return;

                var marker = L.circleMarker([parseFloat(b.latitude), parseFloat(b.longitude)], {
                    radius: 10,
                    fillColor: b.pin_color,
                    color: '#ffffff',
                    weight: 2,
                    fillOpacity: 0.85
                }).addTo(map);

                // Popup content
                var popupHtml =
                    '<b>' + b.name + '</b><br>' +
                    'Distribution Status: ' + statusBadge(b.distribution_status) + '<br>' +
                    'Total Beneficiaries: <b>' + b.total_beneficiaries + '</b><br>' +
                    'Farmers: <b>' + b.total_farmers + '</b> | Fisherfolk: <b>' + b.total_fisherfolk + '</b><br>' +
                    'Total Distributed: <b>' + b.total_distributed + '</b><br>' +
                    'Last Distribution: <b>' + (b.last_distribution_date || 'None yet') + '</b>';

                marker.bindPopup(popupHtml);

                // Show info panel on click
                marker.on('click', function () {
                    panelName.textContent = b.name;

                    var badge = statusBadge(b.distribution_status);
                    panelStatusBadge.innerHTML          = badge;
                    panelBeneficiaries.textContent       = b.total_beneficiaries;
                    panelFarmers.textContent              = b.total_farmers;
                    panelFisherfolk.textContent           = b.total_fisherfolk;
                    panelDistributed.textContent          = b.total_distributed;
                    panelLastDate.textContent             = b.last_distribution_date || 'None yet';
                    panelViewEvents.href                 = "{{ route('distribution-events.index') }}" + '?barangay_id=' + b.id;

                    panel.classList.remove('d-none');
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });
        });
});
</script>
@endpush
