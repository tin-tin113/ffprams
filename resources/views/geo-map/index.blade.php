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
        height: clamp(340px, 68vh, 760px);
        min-height: 340px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 1;
    }

    /* ---- Leaflet Controls Stay Below Modal ---- */
    .leaflet-control {
        z-index: 400 !important;
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

    /* ---- Modal styling ---- */
    .barangay-modal .modal-dialog {
        max-width: min(95vw, 640px);
    }
    .barangay-modal .modal-body {
        max-height: calc(100dvh - 170px);
        overflow-y: auto;
    }
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

    /* ---- Layer control polish ---- */
    .map-layer-control.leaflet-control-layers {
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.16);
    }

    .map-layer-control .leaflet-control-layers-list label {
        margin-bottom: 0.22rem;
    }

    /* ---- Mobile map ergonomics ---- */
    @media (max-width: 991.98px) {
        #map {
            height: clamp(300px, 62vh, 620px);
            min-height: 300px;
        }

        .map-search {
            padding: 0.3rem;
        }

        .map-search input {
            width: 150px;
            font-size: 0.76rem;
            padding: 0.3rem 0.5rem;
        }

        .map-legend {
            font-size: 0.68rem;
            line-height: 1.6;
            max-width: 170px;
            padding: 0.42rem 0.55rem;
        }

        .leaflet-bottom .leaflet-control {
            margin-bottom: 0.55rem;
        }

        .leaflet-left .leaflet-control {
            margin-left: 0.55rem;
        }

        .leaflet-right .leaflet-control {
            margin-right: 0.55rem;
        }

        .map-layer-control.leaflet-control-layers-expanded {
            max-width: 230px;
            max-height: 45vh;
            overflow-y: auto;
        }

        .map-layer-control .leaflet-control-layers-list label {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
        }
    }

    @media (max-width: 575.98px) {
        #map {
            height: clamp(260px, 56vh, 480px);
            min-height: 260px;
        }

        .barangay-modal .modal-dialog {
            max-width: calc(100vw - 1rem);
            margin: 0.5rem auto;
        }

        .barangay-modal .modal-body {
            max-height: calc(100dvh - 150px);
        }

        .map-search input {
            width: 125px;
        }

        .map-legend {
            max-width: 150px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-geo-alt me-1"></i> Farmer-Fisherfolk Precision Resource Allocation Management System</h1>
            <p class="text-muted mb-0">Geo-Map Resource Targeting</p>
        </div>
        <button class="btn btn-sm btn-outline-secondary align-self-start" onclick="resetMapView()" title="Reset view">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset View
        </button>
    </div>

    {{-- Map Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="agencyFilter" class="form-label small fw-semibold mb-1">Agency</label>
                    <select id="agencyFilter" class="form-select form-select-sm">
                        <option value="">All Agencies</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label for="programFilter" class="form-label small fw-semibold mb-1">Program</label>
                    <select id="programFilter" class="form-select form-select-sm">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" data-agency-id="{{ $program->agency_id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label for="statusFilter" class="form-label small fw-semibold mb-1">Status</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="pending">Pending</option>
                        <option value="none">No Distribution</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label for="sectorFilter" class="form-label small fw-semibold mb-1">Beneficiary Type</label>
                    <select id="sectorFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="farmer">Farmer-related</option>
                        <option value="fisherfolk">Fisherfolk-related</option>
                        <option value="both_classified">Both-classified</option>
                        <option value="with_direct">With Direct Assistance</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearMapFilters">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between gap-1 mt-2">
                <div class="small text-muted" id="mapFilterSummary">Showing: --</div>
                <div class="small text-muted" id="mapLastUpdated">Last updated: --</div>
            </div>
        </div>
    </div>

    <div class="alert alert-danger d-none py-2 mb-3" id="mapErrorBanner" role="alert"></div>

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
        <div class="stat-chip">
            <i class="bi bi-person-check-fill text-info"></i>
            <span>Direct Assistance</span>
            <span class="stat-value text-info" id="stat-direct-assistance">--</span>
        </div>
    </div>

    {{-- Map --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div id="map"></div>
        </div>
    </div>
</div>

{{-- Barangay Details Modal --}}
<div class="modal fade barangay-modal" id="barangayModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modal-title">
                    <i class="bi bi-pin-map-fill text-success me-2"></i>
                    <span id="modal-name-text"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Distribution Status --}}
                <div class="panel-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="panel-section-title mb-1">Distribution Status</div>
                            <div id="modal-status-badge"></div>
                        </div>
                        <div class="text-end">
                            <div class="panel-section-title mb-1">Coverage</div>
                            <span class="fw-bold" id="modal-coverage-rate">0%</span>
                        </div>
                    </div>
                    <div class="panel-progress-bar mt-2">
                        <div class="panel-progress-fill bg-success" id="modal-coverage-bar" style="width: 0%"></div>
                    </div>
                </div>

                {{-- Beneficiary Summary --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-people me-1"></i>Beneficiaries</div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-primary" id="modal-beneficiaries">0</div>
                                <div class="panel-stat-label">Total Registered</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value" style="color: var(--color-purple);" id="modal-household">0</div>
                                <div class="panel-stat-label">Household Members</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-flower1 me-1"></i>Farmers</span>
                        <span class="value text-success" id="modal-farmers">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-water me-1"></i>Fisherfolk</span>
                        <span class="value" style="color: var(--color-cyan);" id="modal-fisherfolk">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-intersect me-1"></i>Both (Farmer & Fisherfolk)</span>
                        <span class="value text-info" id="modal-both">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label">Avg. Household Size</span>
                        <span class="value text-muted" id="modal-avg-household">0</span>
                    </div>
                </div>

                {{-- Distribution Events --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-calendar-event me-1"></i>Distribution Events</div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-dark" id="modal-total-events">0</div>
                                <div class="panel-stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-success" id="modal-physical-events">0</div>
                                <div class="panel-stat-label">Physical</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-warning" id="modal-financial-events">0</div>
                                <div class="panel-stat-label">Financial</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-check-circle text-success me-1"></i>Completed</span>
                        <span class="value text-success" id="modal-events-completed">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-clock text-warning me-1"></i>Ongoing</span>
                        <span class="value text-warning" id="modal-events-ongoing">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-hourglass-split text-primary me-1"></i>Pending</span>
                        <span class="value text-primary" id="modal-events-pending">0</span>
                    </div>
                </div>

                {{-- Allocation & Distribution --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-box-seam me-1"></i>Allocations</div>
                    <div class="panel-info-row">
                        <span class="label">Total Allocations</span>
                        <span class="value" id="modal-total-allocations">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-check2-all text-success me-1"></i>Distributed</span>
                        <span class="value text-success" id="modal-distributed">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-clock-history text-muted me-1"></i>Pending Release</span>
                        <span class="value text-muted" id="modal-pending-allocations">0</span>
                    </div>
                </div>

                {{-- Direct Assistance --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-person-check me-1"></i>Direct Assistance</div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-info" id="modal-direct-total">0</div>
                                <div class="panel-stat-label">Total Records</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-success" id="modal-direct-released">0</div>
                                <div class="panel-stat-label">Released</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-clock-history text-muted me-1"></i>Planned</span>
                        <span class="value text-muted" id="modal-direct-planned">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-bell text-primary me-1"></i>Ready for Release</span>
                        <span class="value text-primary" id="modal-direct-ready">0</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label"><i class="bi bi-x-circle text-danger me-1"></i>Not Received</span>
                        <span class="value text-danger" id="modal-direct-not-received">0</span>
                    </div>
                </div>

                {{-- Financial Summary --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-cash-stack me-1"></i>Financial Summary</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-primary" id="modal-fund-allocated" style="font-size: 0.9rem;">&#8369;0</div>
                                <div class="panel-stat-label">Fund Allocated</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="panel-stat-card">
                                <div class="panel-stat-value text-success" id="modal-cash-disbursed" style="font-size: 0.9rem;">&#8369;0</div>
                                <div class="panel-stat-label">Cash Disbursed</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resources Distributed --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-archive me-1"></i>Resources Distributed</div>
                    <div class="panel-resource-tags" id="modal-resources">
                        <span class="text-muted small">None</span>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-calendar3 me-1"></i>Timeline</div>
                    <div class="panel-info-row">
                        <span class="label">First Distribution</span>
                        <span class="value small" id="modal-first-date">--</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label">Last Distribution</span>
                        <span class="value small" id="modal-last-date">--</span>
                    </div>
                </div>

                {{-- Coordinates --}}
                <div class="panel-section">
                    <div class="panel-section-title"><i class="bi bi-geo me-1"></i>Coordinates</div>
                    <div class="panel-info-row">
                        <span class="label">Latitude</span>
                        <span class="value small text-muted" id="modal-lat">--</span>
                    </div>
                    <div class="panel-info-row">
                        <span class="label">Longitude</span>
                        <span class="value small text-muted" id="modal-lng">--</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-success" id="modal-view-events">
                    <i class="bi bi-calendar-event me-1"></i> View Distribution Events
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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

    var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    });

    var satelliteLayer = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
            maxZoom: 19
        }
    );

    var markerLayerGroup = L.layerGroup().addTo(map);
    var coverageLayerGroup = L.layerGroup();
    var directAssistanceLayerGroup = L.layerGroup();
    var isCompactViewport = window.matchMedia('(max-width: 991.98px)').matches;

    streetLayer.addTo(map);

    var layerControl = L.control.layers(
        {
            'Street Map': streetLayer,
            'Satellite': satelliteLayer
        },
        {
            'Barangay Pins': markerLayerGroup,
            'Coverage Rings': coverageLayerGroup,
            'Direct Assistance Intensity': directAssistanceLayerGroup
        },
        {
            position: 'bottomleft',
            collapsed: isCompactViewport
        }
    ).addTo(map);

    if (layerControl && layerControl.getContainer) {
        L.DomUtil.addClass(layerControl.getContainer(), 'map-layer-control');
    }

    // Make reset available globally
    window.resetMapView = function () {
        map.flyTo(ebmCenter, 12, { duration: 0.8 });
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
            '<span class="legend-dot" style="background:#dc3545;"></span> No Distribution<br>' +
            '<small class="text-muted">Use bottom-left layer toggle for advanced overlays.</small>';
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

    // ---- Modal handling ----
    var barangayModalEl = document.getElementById('barangayModal');

    // Move modal to <body> so it is not trapped inside page stacking contexts.
    if (barangayModalEl && barangayModalEl.parentElement !== document.body) {
        document.body.appendChild(barangayModalEl);
    }

    var barangayModal = new bootstrap.Modal(barangayModalEl, { keyboard: false });

    function refreshMapSize() {
        window.requestAnimationFrame(function () {
            map.invalidateSize();
        });
    }

    // If mobile sidebar overlay is still open, ensure it never blocks modal interaction.
    barangayModalEl.addEventListener('show.bs.modal', function () {
        var sidebarOverlay = document.getElementById('sidebarOverlay');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('show');
        }
    });

    barangayModalEl.addEventListener('shown.bs.modal', refreshMapSize);
    barangayModalEl.addEventListener('hidden.bs.modal', refreshMapSize);

    window.addEventListener('resize', refreshMapSize);

    var sidebarToggleBtn = document.getElementById('sidebarToggle');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function () {
            window.setTimeout(refreshMapSize, 320);
        });
    }

    function openBarangayModal(b) {
        // Header
        document.getElementById('modal-name-text').textContent = b.name;
        document.getElementById('modal-status-badge').innerHTML = statusBadge(b.distribution_status);

        // Coverage
        document.getElementById('modal-coverage-rate').textContent = b.coverage_rate + '%';
        var coverageBar = document.getElementById('modal-coverage-bar');
        coverageBar.style.width = Math.min(b.coverage_rate, 100) + '%';
        coverageBar.className = 'panel-progress-fill ' + (b.coverage_rate >= 75 ? 'bg-success' : b.coverage_rate >= 40 ? 'bg-warning' : 'bg-danger');

        // Beneficiaries
        document.getElementById('modal-beneficiaries').textContent = b.total_beneficiaries.toLocaleString();
        document.getElementById('modal-household').textContent = b.total_household_members.toLocaleString();
        document.getElementById('modal-farmers').textContent = b.total_farmers_only;
        document.getElementById('modal-fisherfolk').textContent = b.total_fisherfolk_only;
        document.getElementById('modal-both').textContent = b.total_both;
        var avgHousehold = (b.avg_household_size === null || b.avg_household_size === undefined || b.avg_household_size === '')
            ? 'N/A'
            : b.avg_household_size;
        document.getElementById('modal-avg-household').textContent = avgHousehold;

        // Distribution events
        document.getElementById('modal-total-events').textContent = b.total_events;
        document.getElementById('modal-physical-events').textContent = b.total_physical_events;
        document.getElementById('modal-financial-events').textContent = b.total_financial_events;
        document.getElementById('modal-events-completed').textContent = b.events_completed;
        document.getElementById('modal-events-ongoing').textContent = b.events_ongoing;
        document.getElementById('modal-events-pending').textContent = b.events_pending;

        // Allocations
        document.getElementById('modal-total-allocations').textContent = b.total_allocations;
        document.getElementById('modal-distributed').textContent = b.total_distributed;
        document.getElementById('modal-pending-allocations').textContent = b.total_pending_allocations;

        // Direct assistance
        document.getElementById('modal-direct-total').textContent = b.total_direct_assistance;
        document.getElementById('modal-direct-planned').textContent = b.direct_assistance_planned;
        document.getElementById('modal-direct-ready').textContent = b.direct_assistance_ready_for_release;
        document.getElementById('modal-direct-released').textContent = b.direct_assistance_released;
        document.getElementById('modal-direct-not-received').textContent = b.direct_assistance_not_received;

        // Financial
        document.getElementById('modal-fund-allocated').textContent = formatPeso(b.total_fund_allocated);
        document.getElementById('modal-cash-disbursed').textContent = formatPeso(b.total_cash_disbursed);

        // Resources
        var resourcesEl = document.getElementById('modal-resources');
        if (b.resources_distributed && b.resources_distributed !== 'None') {
            var tags = b.resources_distributed.split(', ').map(function (r) {
                return '<span class="panel-resource-tag">' + r + '</span>';
            }).join('');
            resourcesEl.innerHTML = tags;
        } else {
            resourcesEl.innerHTML = '<span class="text-muted small">No resources distributed yet</span>';
        }

        // Timeline
        document.getElementById('modal-first-date').textContent = b.first_distribution_date || 'None yet';
        document.getElementById('modal-last-date').textContent = b.last_distribution_date || 'None yet';

        // Coordinates
        document.getElementById('modal-lat').textContent = parseFloat(b.latitude).toFixed(6);
        document.getElementById('modal-lng').textContent = parseFloat(b.longitude).toFixed(6);

        // Action link
        document.getElementById('modal-view-events').href =
            "{{ route('distribution-events.index') }}" + '?barangay_id=' + b.id;

        barangayModal.show();
    }

    // ---- Fetch & render (with filters) ----
    var allMarkers = [];
    var overlayItems = [];
    var agencyFilterEl = document.getElementById('agencyFilter');
    var programFilterEl = document.getElementById('programFilter');
    var statusFilterEl = document.getElementById('statusFilter');
    var sectorFilterEl = document.getElementById('sectorFilter');
    var clearMapFiltersBtn = document.getElementById('clearMapFilters');
    var mapSearchEl = document.getElementById('mapSearch');
    var mapErrorBanner = document.getElementById('mapErrorBanner');
    var mapLastUpdatedEl = document.getElementById('mapLastUpdated');
    var mapFilterSummaryEl = document.getElementById('mapFilterSummary');
    var programOptions = Array.prototype.slice.call(programFilterEl.querySelectorAll('option'))
        .filter(function (option) {
            return option.value !== '';
        })
        .map(function (option) {
            return {
                id: option.value,
                name: option.textContent,
                agency_id: option.getAttribute('data-agency-id')
            };
        });

    function populateProgramFilter() {
        var selectedAgency = agencyFilterEl.value;
        var previousProgram = programFilterEl.value;

        programFilterEl.innerHTML = '<option value="">All Programs</option>';

        programOptions.forEach(function (program) {
            if (!selectedAgency || String(program.agency_id) === String(selectedAgency)) {
                var option = document.createElement('option');
                option.value = String(program.id);
                option.textContent = program.name;
                programFilterEl.appendChild(option);
            }
        });

        if (previousProgram && programFilterEl.querySelector('option[value="' + previousProgram + '"]')) {
            programFilterEl.value = previousProgram;
        }
    }

    function clearMarkers() {
        markerLayerGroup.clearLayers();
        coverageLayerGroup.clearLayers();
        directAssistanceLayerGroup.clearLayers();
        allMarkers = [];
        overlayItems = [];
    }

    function showMapError(message) {
        if (!mapErrorBanner) return;
        mapErrorBanner.textContent = message;
        mapErrorBanner.classList.remove('d-none');
    }

    function clearMapError() {
        if (!mapErrorBanner) return;
        mapErrorBanner.textContent = '';
        mapErrorBanner.classList.add('d-none');
    }

    function updateLastUpdated(meta) {
        if (!mapLastUpdatedEl) return;

        if (!meta || !meta.generated_at) {
            mapLastUpdatedEl.textContent = 'Last updated: --';
            return;
        }

        var generatedAt = new Date(meta.generated_at);
        if (Number.isNaN(generatedAt.getTime())) {
            mapLastUpdatedEl.textContent = 'Last updated: --';
            return;
        }

        mapLastUpdatedEl.textContent = 'Last updated: ' + generatedAt.toLocaleString();
    }

    function updateFilterSummary(visibleCount, totalCount) {
        if (!mapFilterSummaryEl) return;

        mapFilterSummaryEl.textContent = 'Showing: ' + visibleCount + ' of ' + totalCount + ' barangays';
    }

    function passesClientFilters(barangay) {
        var statusFilter = statusFilterEl.value;
        var sectorFilter = sectorFilterEl.value;

        if (statusFilter && barangay.distribution_status !== statusFilter) {
            return false;
        }

        if (!sectorFilter) {
            return true;
        }

        if (sectorFilter === 'farmer') {
            return Number(barangay.total_farmers || 0) > 0;
        }

        if (sectorFilter === 'fisherfolk') {
            return Number(barangay.total_fisherfolk || 0) > 0;
        }

        if (sectorFilter === 'both_classified') {
            return Number(barangay.total_both || 0) > 0;
        }

        if (sectorFilter === 'with_direct') {
            return Number(barangay.total_direct_assistance || 0) > 0;
        }

        return true;
    }

    function applySearchToMarkers() {
        var query = mapSearchEl.value.toLowerCase().trim();

        allMarkers.forEach(function (marker) {
            var matched = !query || marker.barangayName.indexOf(query) !== -1;
            marker.setOpacity(matched ? 1 : 0.2);
        });

        overlayItems.forEach(function (item) {
            var matched = !query || item.barangayName.indexOf(query) !== -1;

            if (item.coverageCircle) {
                item.coverageCircle.setStyle({
                    opacity: matched ? 0.55 : 0.12,
                    fillOpacity: matched ? 0.08 : 0.02,
                });
            }

            if (item.directCircle) {
                item.directCircle.setStyle({
                    opacity: matched ? 0.65 : 0.12,
                    fillOpacity: matched ? 0.12 : 0.03,
                });
            }
        });
    }

    function buildGeoMapDataUrl() {
        var params = new URLSearchParams();

        if (agencyFilterEl.value) {
            params.append('agency_id', agencyFilterEl.value);
        }

        if (programFilterEl.value) {
            params.append('program_name_id', programFilterEl.value);
        }

        var baseUrl = "{{ route('geo-map.data') }}";
        var query = params.toString();

        return query ? (baseUrl + '?' + query) : baseUrl;
    }

    function loadGeoMapData() {
        var dataUrl = buildGeoMapDataUrl();

        clearMapError();
        clearMarkers();

        fetch(dataUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load map data (' + response.status + ')');
                }
                return response.json();
            })
            .then(function (payload) {
                clearMapError();

                var data = Array.isArray(payload) ? payload : (payload.data || []);
                var meta = Array.isArray(payload) ? null : (payload.meta || null);
                var filteredData = data.filter(passesClientFilters);
                updateLastUpdated(meta);
                updateFilterSummary(filteredData.length, data.length);

                // Stats
                document.getElementById('stat-total-barangays').textContent = filteredData.length;
                document.getElementById('stat-beneficiaries').textContent =
                    filteredData.reduce(function (sum, b) { return sum + b.total_beneficiaries; }, 0).toLocaleString();
                document.getElementById('stat-completed').textContent =
                    filteredData.filter(function (b) { return b.distribution_status === 'completed'; }).length;
                document.getElementById('stat-ongoing').textContent =
                    filteredData.filter(function (b) { return b.distribution_status === 'ongoing'; }).length;
                document.getElementById('stat-pending').textContent =
                    filteredData.filter(function (b) { return b.distribution_status === 'pending'; }).length;
                document.getElementById('stat-none').textContent =
                    filteredData.filter(function (b) { return b.distribution_status === 'none'; }).length;

                var totalCash = filteredData.reduce(function (sum, b) { return sum + b.total_cash_disbursed; }, 0);
                document.getElementById('stat-cash-disbursed').textContent = formatPeso(totalCash);

                var totalDirectAssistance = filteredData.reduce(function (sum, b) {
                    return sum + (b.total_direct_assistance || 0);
                }, 0);
                document.getElementById('stat-direct-assistance').textContent = totalDirectAssistance.toLocaleString();

                // Markers
                var markerBounds = [];

                filteredData.forEach(function (b) {
                    if (!b.latitude || !b.longitude) return;

                    var latlng = [parseFloat(b.latitude), parseFloat(b.longitude)];
                    markerBounds.push(latlng);

                    var marker = L.marker(latlng, {
                        icon: createPinIcon(b.pin_color, b.total_beneficiaries)
                    }).addTo(markerLayerGroup);

                    marker.barangayName = b.name.toLowerCase();

                    marker.bindTooltip(b.name, {
                        permanent: false,
                        direction: 'top',
                        offset: [0, -10],
                        className: 'pin-label'
                    });

                    marker.on('click', function () {
                        openBarangayModal(b);
                    });

                    allMarkers.push(marker);

                    var coverageColor = b.coverage_rate >= 75 ? '#198754' : (b.coverage_rate >= 40 ? '#ffc107' : '#dc3545');
                    var coverageRadius = Math.max(120, Math.sqrt(Math.max(Number(b.total_beneficiaries || 0), 1)) * 90);

                    var coverageCircle = L.circle(latlng, {
                        radius: coverageRadius,
                        color: coverageColor,
                        weight: 1.5,
                        opacity: 0.55,
                        fillColor: coverageColor,
                        fillOpacity: 0.08,
                    }).addTo(coverageLayerGroup);

                    coverageCircle.bindTooltip(
                        '<strong>' + b.name + '</strong><br>Coverage: ' + b.coverage_rate + '%',
                        { direction: 'top', offset: [0, -4] }
                    );

                    var directCircle = null;
                    var directTotal = Number(b.total_direct_assistance || 0);
                    if (directTotal > 0) {
                        var directRadius = Math.max(80, Math.sqrt(directTotal) * 75);
                        directCircle = L.circle(latlng, {
                            radius: directRadius,
                            color: '#0d6efd',
                            weight: 1.2,
                            opacity: 0.65,
                            fillColor: '#0d6efd',
                            fillOpacity: 0.12,
                        }).addTo(directAssistanceLayerGroup);

                        directCircle.bindTooltip(
                            '<strong>' + b.name + '</strong><br>Direct Assistance: ' + directTotal,
                            { direction: 'top', offset: [0, -4] }
                        );
                    }

                    overlayItems.push({
                        barangayName: marker.barangayName,
                        coverageCircle: coverageCircle,
                        directCircle: directCircle,
                    });
                });

                if (markerBounds.length > 0) {
                    map.fitBounds(markerBounds, { padding: [40, 40], maxZoom: 13 });
                } else {
                    map.flyTo(ebmCenter, 12, { duration: 0.6 });
                }

                applySearchToMarkers();
            })
            .catch(function (err) {
                showMapError(err && err.message ? err.message : 'Failed to load geo-map data. Please try again.');
            });
    }

    mapSearchEl.addEventListener('input', applySearchToMarkers);

    agencyFilterEl.addEventListener('change', function () {
        populateProgramFilter();
        loadGeoMapData();
    });

    programFilterEl.addEventListener('change', function () {
        loadGeoMapData();
    });

    statusFilterEl.addEventListener('change', function () {
        loadGeoMapData();
    });

    sectorFilterEl.addEventListener('change', function () {
        loadGeoMapData();
    });

    clearMapFiltersBtn.addEventListener('click', function () {
        agencyFilterEl.value = '';
        populateProgramFilter();
        programFilterEl.value = '';
        statusFilterEl.value = '';
        sectorFilterEl.value = '';
        mapSearchEl.value = '';
        loadGeoMapData();
    });

    populateProgramFilter();
    loadGeoMapData();
    refreshMapSize();
});
</script>
@endpush
