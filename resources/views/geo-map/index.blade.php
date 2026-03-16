@extends('layouts.app')

@section('title', 'Geo-Map')

@section('breadcrumb')
    <li class="breadcrumb-item active">Geo-Map</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ---- Map ---- */
    #map {
        height: 70vh;
        min-height: 500px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 1;
    }

    /* ---- Stat strip ---- */
    .stat-strip {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .stat-chip {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.82rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .stat-chip .stat-value {
        font-weight: 700;
        font-size: 0.95rem;
    }
    .stat-chip i {
        font-size: 1rem;
    }

    /* ---- Legend (on-map) ---- */
    .map-legend {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(4px);
        border-radius: 0.5rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1.75;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }
    .legend-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 2px rgba(0,0,0,.25);
        vertical-align: middle;
        margin-right: 5px;
    }

    /* ---- Pin markers ---- */
    .pin-marker {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .pin-icon {
        width: 24px;
        height: 32px;
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }
    .pin-icon svg {
        width: 24px;
        height: 32px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,.35));
        transition: transform .15s ease;
    }
    .pin-marker:hover .pin-icon svg {
        transform: scale(1.15);
    }
    .pin-count {
        position: absolute;
        top: 4px;
        left: 50%;
        transform: translateX(-50%);
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0,0,0,.4);
    }

    /* ---- Pin tooltip (barangay name) ---- */
    .pin-label.leaflet-tooltip {
        background: rgba(255,255,255,.85);
        backdrop-filter: blur(2px);
        border: none;
        border-radius: 3px;
        box-shadow: 0 1px 4px rgba(0,0,0,.12);
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 600;
        color: #333;
    }
    .pin-label.leaflet-tooltip::before {
        display: none;
    }

    /* ---- Side panel ---- */
    .side-panel {
        position: fixed;
        top: 0;
        right: -420px;
        width: 400px;
        max-width: 90vw;
        height: 100vh;
        background: #fff;
        box-shadow: -4px 0 20px rgba(0,0,0,.12);
        z-index: 1050;
        transition: right .3s ease;
        overflow-y: auto;
    }
    .side-panel.open {
        right: 0;
    }
    .side-panel-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.25);
        z-index: 1049;
    }
    .side-panel-overlay.open {
        display: block;
    }
    .panel-stat-card {
        text-align: center;
        padding: 0.6rem 0.4rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }
    .panel-stat-card .panel-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
    }
    .panel-stat-card .panel-stat-label {
        font-size: 0.7rem;
        color: #6c757d;
    }

    /* ---- Panel sections ---- */
    .panel-section {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .panel-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .panel-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    .panel-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.25rem 0;
        font-size: 0.82rem;
    }
    .panel-info-row .label {
        color: #6c757d;
    }
    .panel-info-row .value {
        font-weight: 600;
    }
    .panel-progress-bar {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }
    .panel-progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.4s ease;
    }
    .panel-resource-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }
    .panel-resource-tag {
        display: inline-block;
        background: #e8f5e9;
        color: #2e7d32;
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.2rem 0.5rem;
        border-radius: 0.3rem;
    }

    /* ---- Search box on map ---- */
    .map-search {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(4px);
        border-radius: 0.5rem;
        padding: 0.4rem;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }
    .map-search input {
        border: 1px solid #dee2e6;
        border-radius: 0.4rem;
        padding: 0.35rem 0.6rem;
        font-size: 0.8rem;
        width: 200px;
        outline: none;
    }
    .map-search input:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 2px rgba(40,167,69,.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-geo-alt me-1"></i> Farmer-Fisherfolk Precision Resource Allocation Management System</h1>
            <p class="text-muted mb-0">Geo-Map Resource Targeting</p>
        </div>
        <button class="btn btn-sm btn-outline-secondary" onclick="resetMapView()" title="Reset view">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset View
        </button>
    </div>

    {{-- Stat Strip --}}
    <div class="stat-strip mb-3">
        <div class="stat-chip">
            <i class="bi bi-geo-alt-fill text-success"></i>
            <span>Barangays</span>
            <span class="stat-value" id="stat-total-barangays">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-people-fill text-primary"></i>
            <span>Beneficiaries</span>
            <span class="stat-value" id="stat-beneficiaries">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span>Completed</span>
            <span class="stat-value text-success" id="stat-completed">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-clock-fill text-warning"></i>
            <span>Ongoing</span>
            <span class="stat-value text-warning" id="stat-ongoing">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-hourglass-split text-primary"></i>
            <span>Pending</span>
            <span class="stat-value text-primary" id="stat-pending">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-exclamation-circle-fill text-danger"></i>
            <span>No Distribution</span>
            <span class="stat-value text-danger" id="stat-none">--</span>
        </div>
        <div class="stat-chip">
            <i class="bi bi-cash-stack text-success"></i>
            <span>Cash Disbursed</span>
            <span class="stat-value text-success" id="stat-cash-disbursed">--</span>
        </div>
    </div>

    {{-- Map --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div id="map"></div>
        </div>
    </div>
</div>

{{-- Side Panel Overlay --}}
<div class="side-panel-overlay" id="panelOverlay"></div>

{{-- Side Panel --}}
<div class="side-panel" id="sidePanel">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0" id="panel-name">
            <i class="bi bi-pin-map-fill text-success me-1"></i>
            <span id="panel-name-text"></span>
        </h5>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" id="panel-close" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="p-3">

        {{-- Distribution Status --}}
        <div class="panel-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="panel-section-title mb-1">Distribution Status</div>
                    <div id="panel-status-badge"></div>
                </div>
                <div class="text-end">
                    <div class="panel-section-title mb-1">Coverage</div>
                    <span class="fw-bold" id="panel-coverage-rate">0%</span>
                </div>
            </div>
            <div class="panel-progress-bar mt-2">
                <div class="panel-progress-fill bg-success" id="panel-coverage-bar" style="width: 0%"></div>
            </div>
        </div>

        {{-- Beneficiary Summary --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-people me-1"></i>Beneficiaries</div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-primary" id="panel-beneficiaries">0</div>
                        <div class="panel-stat-label">Total Registered</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value" style="color: #6f42c1;" id="panel-household">0</div>
                        <div class="panel-stat-label">Household Members</div>
                    </div>
                </div>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-flower1 me-1"></i>Farmers</span>
                <span class="value text-success" id="panel-farmers">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-water me-1"></i>Fisherfolk</span>
                <span class="value" style="color: #0dcaf0;" id="panel-fisherfolk">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-intersect me-1"></i>Both (Farmer & Fisherfolk)</span>
                <span class="value text-info" id="panel-both">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label">Avg. Household Size</span>
                <span class="value text-muted" id="panel-avg-household">0</span>
            </div>
        </div>

        {{-- Distribution Events --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-calendar-event me-1"></i>Distribution Events</div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-dark" id="panel-total-events">0</div>
                        <div class="panel-stat-label">Total</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-success" id="panel-physical-events">0</div>
                        <div class="panel-stat-label">Physical</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-warning" id="panel-financial-events">0</div>
                        <div class="panel-stat-label">Financial</div>
                    </div>
                </div>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-check-circle text-success me-1"></i>Completed</span>
                <span class="value text-success" id="panel-events-completed">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-clock text-warning me-1"></i>Ongoing</span>
                <span class="value text-warning" id="panel-events-ongoing">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-hourglass-split text-primary me-1"></i>Pending</span>
                <span class="value text-primary" id="panel-events-pending">0</span>
            </div>
        </div>

        {{-- Allocation & Distribution --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-box-seam me-1"></i>Allocations</div>
            <div class="panel-info-row">
                <span class="label">Total Allocations</span>
                <span class="value" id="panel-total-allocations">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-check2-all text-success me-1"></i>Distributed</span>
                <span class="value text-success" id="panel-distributed">0</span>
            </div>
            <div class="panel-info-row">
                <span class="label"><i class="bi bi-clock-history text-muted me-1"></i>Pending Release</span>
                <span class="value text-muted" id="panel-pending-allocations">0</span>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-cash-stack me-1"></i>Financial Summary</div>
            <div class="row g-2">
                <div class="col-6">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-primary" id="panel-fund-allocated" style="font-size: 0.9rem;">&#8369;0</div>
                        <div class="panel-stat-label">Fund Allocated</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="panel-stat-card">
                        <div class="panel-stat-value text-success" id="panel-cash-disbursed" style="font-size: 0.9rem;">&#8369;0</div>
                        <div class="panel-stat-label">Cash Disbursed</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resources Distributed --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-archive me-1"></i>Resources Distributed</div>
            <div class="panel-resource-tags" id="panel-resources">
                <span class="text-muted small">None</span>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-calendar3 me-1"></i>Timeline</div>
            <div class="panel-info-row">
                <span class="label">First Distribution</span>
                <span class="value small" id="panel-first-date">--</span>
            </div>
            <div class="panel-info-row">
                <span class="label">Last Distribution</span>
                <span class="value small" id="panel-last-date">--</span>
            </div>
        </div>

        {{-- Coordinates --}}
        <div class="panel-section">
            <div class="panel-section-title"><i class="bi bi-geo me-1"></i>Coordinates</div>
            <div class="panel-info-row">
                <span class="label">Latitude</span>
                <span class="value small text-muted" id="panel-lat">--</span>
            </div>
            <div class="panel-info-row">
                <span class="label">Longitude</span>
                <span class="value small text-muted" id="panel-lng">--</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-grid gap-2 mt-2">
            <a href="#" class="btn btn-success" id="panel-view-events">
                <i class="bi bi-calendar-event me-1"></i> View Distribution Events
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Map setup ----
    var ebmCenter = [10.8600, 123.0400];
    var ebmBounds = L.latLngBounds(
        [10.730, 122.920],
        [10.940, 123.200]
    );

    var map = L.map('map', {
        center: ebmCenter,
        zoom: 12,
        minZoom: 10,
        maxBounds: ebmBounds.pad(0.2),
        maxBoundsViscosity: 1.0
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);

    // Make reset available globally
    window.resetMapView = function () {
        map.flyTo(ebmCenter, 12, { duration: 0.8 });
        closePanel();
    };

    // ---- Legend control (bottom-right) ----
    var legendControl = L.control({ position: 'bottomright' });
    legendControl.onAdd = function () {
        var div = L.DomUtil.create('div', 'map-legend');
        div.innerHTML =
            '<b style="font-size:0.8rem;">Legend</b><br>' +
            '<span class="legend-dot" style="background:#28a745;"></span> Completed<br>' +
            '<span class="legend-dot" style="background:#ffc107;"></span> Ongoing<br>' +
            '<span class="legend-dot" style="background:#0d6efd;"></span> Pending<br>' +
            '<span class="legend-dot" style="background:#dc3545;"></span> No Distribution';
        return div;
    };
    legendControl.addTo(map);

    // ---- Search control (top-right) ----
    var searchControl = L.control({ position: 'topright' });
    searchControl.onAdd = function () {
        var div = L.DomUtil.create('div', 'map-search');
        div.innerHTML = '<input type="text" id="mapSearch" placeholder="Search barangay..." />';
        L.DomEvent.disableClickPropagation(div);
        return div;
    };
    searchControl.addTo(map);

    // ---- Side panel ----
    var sidePanel = document.getElementById('sidePanel');
    var panelOverlay = document.getElementById('panelOverlay');

    function openPanel() {
        sidePanel.classList.add('open');
        panelOverlay.classList.add('open');
    }
    function closePanel() {
        sidePanel.classList.remove('open');
        panelOverlay.classList.remove('open');
    }

    document.getElementById('panel-close').addEventListener('click', closePanel);
    panelOverlay.addEventListener('click', closePanel);

    // ---- Helpers ----
    function statusBadge(status) {
        var statusMap = {
            completed: { label: 'Completed', css: 'bg-success' },
            ongoing:   { label: 'Ongoing',   css: 'bg-warning text-dark' },
            pending:   { label: 'Pending',   css: 'bg-primary' },
            none:      { label: 'No Distribution', css: 'bg-danger' }
        };
        var info = statusMap[status] || statusMap['none'];
        return '<span class="badge ' + info.css + '">' + info.label + '</span>';
    }

    function formatPeso(amount) {
        return '\u20B1' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function createPinIcon(color, count) {
        var html =
            '<div class="pin-marker">' +
                '<div class="pin-icon">' +
                    '<svg viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">' +
                        '<path d="M16 0 C7.2 0 0 7.2 0 16 C0 28 16 42 16 42 C16 42 32 28 32 16 C32 7.2 24.8 0 16 0 Z" fill="' + color + '" stroke="#fff" stroke-width="1.5"/>' +
                    '</svg>' +
                    '<span class="pin-count">' + count + '</span>' +
                '</div>' +
            '</div>';
        return L.divIcon({
            html: html,
            className: '',
            iconSize: [24, 32],
            iconAnchor: [12, 32],
            popupAnchor: [0, -28]
        });
    }

    // ---- Fetch & render ----
    var allMarkers = [];

    fetch("{{ route('geo-map.data') }}")
        .then(function (r) { return r.json(); })
        .then(function (data) {

            // Stats
            document.getElementById('stat-total-barangays').textContent = data.length;
            document.getElementById('stat-beneficiaries').textContent =
                data.reduce(function (s, b) { return s + b.total_beneficiaries; }, 0).toLocaleString();
            document.getElementById('stat-completed').textContent =
                data.filter(function (b) { return b.distribution_status === 'completed'; }).length;
            document.getElementById('stat-ongoing').textContent =
                data.filter(function (b) { return b.distribution_status === 'ongoing'; }).length;
            document.getElementById('stat-pending').textContent =
                data.filter(function (b) { return b.distribution_status === 'pending'; }).length;
            document.getElementById('stat-none').textContent =
                data.filter(function (b) { return b.distribution_status === 'none'; }).length;

            var totalCash = data.reduce(function (s, b) { return s + b.total_cash_disbursed; }, 0);
            document.getElementById('stat-cash-disbursed').textContent = formatPeso(totalCash);

            // Markers
            var markerBounds = [];

            data.forEach(function (b) {
                if (!b.latitude || !b.longitude) return;

                var latlng = [parseFloat(b.latitude), parseFloat(b.longitude)];
                markerBounds.push(latlng);

                var marker = L.marker(latlng, {
                    icon: createPinIcon(b.pin_color, b.total_beneficiaries)
                }).addTo(map);

                marker.barangayName = b.name.toLowerCase();

                // Tooltip label
                marker.bindTooltip(b.name, {
                    permanent: true,
                    direction: 'bottom',
                    offset: [0, 2],
                    className: 'pin-label'
                });

                // Side panel on click
                marker.on('click', function () {
                    // Header
                    document.getElementById('panel-name-text').textContent = b.name;
                    document.getElementById('panel-status-badge').innerHTML = statusBadge(b.distribution_status);

                    // Coverage
                    document.getElementById('panel-coverage-rate').textContent = b.coverage_rate + '%';
                    var coverageBar = document.getElementById('panel-coverage-bar');
                    coverageBar.style.width = Math.min(b.coverage_rate, 100) + '%';
                    coverageBar.className = 'panel-progress-fill ' + (b.coverage_rate >= 75 ? 'bg-success' : b.coverage_rate >= 40 ? 'bg-warning' : 'bg-danger');

                    // Beneficiaries
                    document.getElementById('panel-beneficiaries').textContent = b.total_beneficiaries.toLocaleString();
                    document.getElementById('panel-household').textContent = b.total_household_members.toLocaleString();
                    document.getElementById('panel-farmers').textContent = b.total_farmers_only;
                    document.getElementById('panel-fisherfolk').textContent = b.total_fisherfolk_only;
                    document.getElementById('panel-both').textContent = b.total_both;
                    document.getElementById('panel-avg-household').textContent = b.avg_household_size;

                    // Distribution events
                    document.getElementById('panel-total-events').textContent = b.total_events;
                    document.getElementById('panel-physical-events').textContent = b.total_physical_events;
                    document.getElementById('panel-financial-events').textContent = b.total_financial_events;
                    document.getElementById('panel-events-completed').textContent = b.events_completed;
                    document.getElementById('panel-events-ongoing').textContent = b.events_ongoing;
                    document.getElementById('panel-events-pending').textContent = b.events_pending;

                    // Allocations
                    document.getElementById('panel-total-allocations').textContent = b.total_allocations;
                    document.getElementById('panel-distributed').textContent = b.total_distributed;
                    document.getElementById('panel-pending-allocations').textContent = b.total_pending_allocations;

                    // Financial
                    document.getElementById('panel-fund-allocated').textContent = formatPeso(b.total_fund_allocated);
                    document.getElementById('panel-cash-disbursed').textContent = formatPeso(b.total_cash_disbursed);

                    // Resources
                    var resourcesEl = document.getElementById('panel-resources');
                    if (b.resources_distributed && b.resources_distributed !== 'None') {
                        var tags = b.resources_distributed.split(', ').map(function (r) {
                            return '<span class="panel-resource-tag">' + r + '</span>';
                        }).join('');
                        resourcesEl.innerHTML = tags;
                    } else {
                        resourcesEl.innerHTML = '<span class="text-muted small">No resources distributed yet</span>';
                    }

                    // Timeline
                    document.getElementById('panel-first-date').textContent = b.first_distribution_date || 'None yet';
                    document.getElementById('panel-last-date').textContent = b.last_distribution_date || 'None yet';

                    // Coordinates
                    document.getElementById('panel-lat').textContent = parseFloat(b.latitude).toFixed(6);
                    document.getElementById('panel-lng').textContent = parseFloat(b.longitude).toFixed(6);

                    // Action link
                    document.getElementById('panel-view-events').href =
                        "{{ route('distribution-events.index') }}" + '?barangay_id=' + b.id;
                    openPanel();
                });

                allMarkers.push(marker);
            });

            // Fit to markers
            if (markerBounds.length > 0) {
                map.fitBounds(markerBounds, { padding: [40, 40], maxZoom: 13 });
            }

            // Search
            document.getElementById('mapSearch').addEventListener('input', function () {
                var query = this.value.toLowerCase().trim();
                allMarkers.forEach(function (m) {
                    if (!query || m.barangayName.indexOf(query) !== -1) {
                        m.setOpacity(1);
                        m.getTooltip().getElement().style.opacity = '1';
                    } else {
                        m.setOpacity(0.2);
                        m.getTooltip().getElement().style.opacity = '0.2';
                    }
                });
            });
        });
});
</script>
@endpush
