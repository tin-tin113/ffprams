@extends('layouts.app')

@section('title', 'Geo-Map - E.B. Magalona')




@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .geom-container { display: grid; grid-template-columns: 1fr; gap: 1rem; position: relative; overflow: hidden; }
    #map { height: 600px; width: 100%; border-radius: 1rem; z-index: 1; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 4px solid #fff; }
    @media (min-height: 900px) { #map { height: 70vh; } }
    .leaflet-control { z-index: 400 !important; }
    
    /* Glassmorphism Effects */
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .map-legend { 
        background: rgba(255,255,255,0.85); 
        backdrop-filter: blur(8px); 
        border-radius: 0.75rem; 
        padding: 0.75rem 1rem; 
        font-size: 0.8rem; 
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.4);
    }
    .legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; box-shadow: 0 0 0 2px rgba(255,255,255,0.8); }
    
    /* Premium Pin Design */
    .pin-marker-container { position: relative; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .pin-marker-container:hover { transform: translateY(-5px) scale(1.1); z-index: 1000 !important; }
    .pin-shadow { 
        position: absolute; 
        bottom: -2px; 
        left: 50%; 
        transform: translateX(-50%); 
        width: 14px; 
        height: 4px; 
        background: rgba(0,0,0,0.2); 
        border-radius: 50%; 
        filter: blur(2px);
    }
    .pin-main svg { width: 32px; height: 42px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }
    .pin-count { 
        position: absolute; 
        top: 8px; 
        left: 50%; 
        transform: translateX(-50%); 
        color: #fff; 
        font-size: 11px; 
        font-weight: 800; 
        text-shadow: 0 1px 2px rgba(0,0,0,0.5); 
        pointer-events: none;
    }

    /* Pulsing Animation for Ongoing distribution */
    @keyframes pin-pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(255, 193, 7, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
    }
    .pin-pulse-effect {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(255, 193, 7, 0.4);
        animation: pin-pulse 2s infinite;
        z-index: -1;
    }

    .pin-label.leaflet-tooltip { 
        background: rgba(255,255,255,0.95); 
        border: none; 
        border-radius: 20px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        padding: 6px 12px; 
        font-size: 12px; 
        font-weight: 700; 
        color: #2c3e50;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .pin-label.leaflet-tooltip::before { display: none; }

    /* Filters & Stats Layout */
    .filters-bar { 
        background: #fff; 
        border-radius: 1.25rem; 
        padding: 1rem; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
        border: 1px solid #f0f0f0;
        margin-bottom: 1.5rem;
    }
    
    .search-box { position: relative; width: 100%; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-box input { padding-left: 40px; border-radius: 12px; border: 1px solid #e2e8f0; height: 45px; background: #fff; }
    
    .filter-select-wrapper { position: relative; min-width: 140px; }
    .filter-select-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.9rem; }
    .filter-select-wrapper .form-select { padding-left: 35px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem; height: 40px; background-color: #f8fafc; font-weight: 500; }
    .filter-select-wrapper .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }

    .summary-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card { 
        background: #fff; 
        border-radius: 1.25rem; 
        padding: 1.25rem; 
        text-align: center; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        border: 1px solid #f0f0f0;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.2rem; letter-spacing: -0.5px; }
    .stat-card .stat-label { font-size: 0.8rem; color: #8e9aaf; font-weight: 600; text-transform: uppercase; }

    /* Premium Modal Styling */
    .premium-modal .modal-content {
        border: none;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .premium-modal .modal-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1.5rem 2rem;
        border: none;
    }
    .premium-modal .modal-body {
        padding: 2rem;
        background: #fff;
    }
    .premium-modal .btn-close { filter: brightness(0) invert(1); }

    /* Centered Modal Fix */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }

    /* Classic Premium Pin Design - Teardrop shape with clear center */
    .pin-marker-container { 
        position: relative; 
        width: 36px; 
        height: 46px; 
        cursor: pointer; 
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
    }
    .pin-marker-container:hover { transform: translateY(-5px) scale(1.1); z-index: 1000 !important; }
    
    .pin-main {
        position: relative;
        width: 36px;
        height: 46px;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
    }

    .pin-path {
        fill: currentColor;
        stroke: #fff;
        stroke-width: 1.5;
    }

    .pin-center-circle {
        fill: #fff;
    }

    .pin-count-text {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-weight: 800;
        fill: #111827;
        pointer-events: none;
        letter-spacing: -0.5px;
    }
    
    /* Pulsing Animation for Ongoing distribution */
    @keyframes pin-pulse {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    .pin-pulse-effect {
        position: absolute;
        top: 11px; /* Precision centered */
        left: 50%;
        transform: translate(-50%, -50%);
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(255, 193, 7, 0.3);
        border: 2px solid rgba(255, 193, 7, 0.5);
        animation: pin-pulse 2s infinite;
        z-index: -1;
    }

    /* Enhanced Legend */
    .map-legend { 
        background: rgba(255, 255, 255, 0.95); 
        backdrop-filter: blur(10px); 
        border-radius: 12px; 
        padding: 12px 16px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .legend-item { display: flex; align-items: center; margin-bottom: 6px; font-weight: 600; color: #4b5563; }
    .legend-item:last-child { margin-bottom: 0; }
    .legend-dot { width: 12px; height: 12px; border-radius: 3px; margin-right: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

    /* Enhanced Tooltip Styling */
    .pin-label.leaflet-tooltip { 
        background: rgba(255, 255, 255, 0.98); 
        backdrop-filter: blur(8px);
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); 
        padding: 0; 
        color: #1f2937;
        overflow: hidden;
        min-width: 200px;
        transition: all 0.2s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .pin-label.leaflet-tooltip::before { display: none; }

    .tooltip-card { display: flex; flex-direction: column; }
    
    .tooltip-header {
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tooltip-body { padding: 12px 14px; }

    .tooltip-title {
        font-size: 13px;
        font-weight: 800;
        margin: 0;
        color: #111827;
        letter-spacing: -0.2px;
    }

    .tooltip-quadrant {
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tooltip-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
        font-size: 11px;
    }

    .tooltip-row:last-child { margin-bottom: 0; }

    .tooltip-label {
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tooltip-value {
        color: #1e293b;
        font-weight: 700;
    }

    .tooltip-footer {
        padding: 6px 14px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* Progress bar and other styles */
    .progress-coverage { height: 10px; border-radius: 10px; background: #f3f4f6; overflow: hidden; }
    .progress-coverage-bar { height: 100%; background: linear-gradient(90deg, #10b981, #34d399); border-radius: 10px; transition: width 1s ease-out; }
    
    .beneficiary-item { 
        background: #f9fafb; 
        border: 1px solid #f3f4f6;
        padding: 1.25rem; 
        margin-bottom: 1rem; 
        border-radius: 1rem; 
        transition: all 0.3s ease;
    }
    .beneficiary-item:hover { transform: translateY(-2px); border-color: #10b981; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); background: #fff; }

    .stat-card { border: none; border-radius: 1.25rem; padding: 1.5rem; transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

    /* Modal Dashboard Styling */
    .modal-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .modal-stat-card { 
        background: #f8fafc; 
        border-radius: 1rem; 
        padding: 1rem; 
        border: 1px solid #e2e8f0;
        text-align: center;
        transition: all 0.3s ease;
    }
    .modal-stat-card:hover { background: #fff; border-color: #10b981; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transform: translateY(-2px); }
    .modal-stat-card .value { font-size: 1.5rem; font-weight: 800; color: #1e293b; display: block; line-height: 1.2; }
    .modal-stat-card .label { font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    .info-section { margin-bottom: 1.5rem; }
    .info-section-title { 
        font-size: 0.85rem; 
        font-weight: 700; 
        color: #475569; 
        margin-bottom: 1rem; 
        display: flex; 
        align-items: center; 
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-section-title i { font-size: 1.1rem; color: #10b981; }

    .beneficiary-list {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .beneficiary-list::-webkit-scrollbar { width: 6px; }
    .beneficiary-list::-webkit-scrollbar-track { background: #f1f5f9; }
    .beneficiary-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Chips for status tags */
    .chip-row { display: flex; gap: 6px; flex-wrap: wrap; }
    .chip { 
        font-size: 0.65rem; 
        font-weight: 700; 
        padding: 2px 8px; 
        border-radius: 6px; 
        text-transform: uppercase;
    }
    .chip-urgent { background: #fee2e2; color: #ef4444; }
    .chip-unverified { background: #fef3c7; color: #d97706; }
    .chip-duplicate { background: #f1f5f9; color: #475569; }

    /* Badge Overrides */
    .badge-info { padding: 5px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-info.completed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-info.ongoing { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .badge-info.pending { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-info.none { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .border-dashed { border: 1.5px dashed #e2e8f0 !important; }

    @media (max-width: 991.98px) { 
        #map { height: 50vh; min-height: 450px; } 
        .modal-stat-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575.98px) { 
        #map { height: 45vh; min-height: 350px; } 
        .filters-bar { padding: 0.6rem; } 
        .summary-stats { grid-template-columns: repeat(2, 1fr); } 
        .barangay-info-modal .modal-dialog { max-width: calc(100vw - 1rem); } 
        .modal-stat-grid { grid-template-columns: 1fr; }
    }
    .btn-copy-success {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
        transform: scale(1.05);
    }
    .js-copy-contact {
        transition: all 0.2s ease;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="geom-container">
        <div class="filters-bar">
            <div class="row g-3 align-items-center">
                <div class="col-lg-3">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="mapSearch" class="form-control" placeholder="Search Barangay...">
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="d-flex gap-2 flex-wrap justify-content-lg-end align-items-center">
                        <div class="filter-select-wrapper">
                            <i class="bi bi-building"></i>
                            <select id="agencyFilter" class="form-select">
                                <option value="">Agencies</option>
                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-select-wrapper">
                            <i class="bi bi-compass"></i>
                            <select id="quadrantFilter" class="form-select">
                                <option value="">Quadrants</option>
                                <option value="Quadrant 1">Quadrant 1</option>
                                <option value="Quadrant 2">Quadrant 2</option>
                                <option value="Quadrant 3">Quadrant 3</option>
                                <option value="Quadrant 4">Quadrant 4</option>
                            </select>
                        </div>
                        <div class="filter-select-wrapper">
                            <i class="bi bi-funnel"></i>
                            <select id="statusFilter" class="form-select">
                                <option value="">Status</option>
                                <option value="completed">Completed</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="pending">Pending</option>
                                <option value="none">No Data</option>
                            </select>
                        </div>
                        <div class="filter-select-wrapper">
                            <i class="bi bi-people"></i>
                            <select id="sectorFilter" class="form-select">
                                <option value="">Sectors</option>
                                <option value="farmer">Farmers</option>
                                <option value="fisherfolk">Fisherfolk</option>
                                <option value="both">Farmer & Fisherfolk</option>
                            </select>
                        </div>
                        <button class="btn btn-dark px-3 rounded-3" id="clearFilters" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-value text-success" id="stat-barangays">--</div>
                <div class="stat-label">Barangays</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-primary" id="stat-beneficiaries">--</div>
                <div class="stat-label">Total Beneficiaries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-warning" id="stat-events">--</div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-info" id="stat-reach">--%</div>
                <div class="stat-label">Municipality Reach</div>
            </div>
        </div>

        <div id="map"></div>
    </div>
</div>

<!-- Premium Centered Modal -->
<div class="modal fade premium-modal" id="barangayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                        <i class="bi bi-geo-alt-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="modal-title fw-bold mb-0" id="panel-barangay-name">Barangay Name</h4>
                            <span class="badge bg-white text-dark opacity-75" id="panel-quadrant-badge" style="font-size: 0.7rem;">Q1</span>
                        </div>
                        <p class="mb-0 small text-white-50">E.B. Magalona, Negros Occidental</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary Stats Row -->
                <div class="modal-stat-grid">
                    <div class="modal-stat-card">
                        <span class="label">Total Beneficiaries</span>
                        <span class="value" id="panel-total-benef">0</span>
                    </div>
                    <div class="modal-stat-card">
                        <span class="label">Reached / Served</span>
                        <span class="value text-primary" id="panel-reached">0</span>
                    </div>
                    <div class="modal-stat-card">
                        <span class="label">Farmers</span>
                        <span class="value text-success" id="panel-farmers">0</span>
                    </div>
                    <div class="modal-stat-card">
                        <span class="label">Fisherfolk</span>
                        <span class="value text-info" id="panel-fisherfolk">0</span>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Details -->
                    <div class="col-lg-4">
                        <div class="info-section">
                            <div class="info-section-title"><i class="bi bi-activity"></i>Status & Reach</div>
                            <div class="p-4 bg-light rounded-4 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small fw-bold">Coverage Rate</span>
                                    <span class="fw-bold text-success fs-5" id="panel-coverage">0%</span>
                                </div>
                                <div class="progress-coverage mb-4" style="height: 12px;">
                                    <div class="progress-coverage-bar" id="panel-coverage-bar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small fw-bold">Current Status</span>
                                        <span id="panel-status-badge" class="badge-info">STATUS</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="text-muted small fw-bold">Last Activity</span>
                                        <span id="panel-last-dist" class="text-dark fw-bold small">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-section mb-0">
                            <div class="info-section-title"><i class="bi bi-cash-stack"></i>Financial Overview</div>
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 bg-white rounded-4 border-dashed">
                                    <div class="text-muted small fw-bold mb-1">Total Fund Allocated</div>
                                    <div class="fw-bold text-dark h4 mb-0" id="panel-fund-allocated">₱0</div>
                                </div>
                                <div class="p-3 bg-white rounded-4 border-dashed">
                                    <div class="text-muted small fw-bold mb-1">Distribution Events</div>
                                    <div class="fw-bold text-dark h5 mb-0" id="panel-total-events">0 Events</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Directory -->
                    <div class="col-lg-8">
                        <div class="info-section mb-0 h-100 d-flex flex-column">
                            <div class="info-section-title d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-list-stars"></i>Beneficiary Directory</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" style="font-size: 0.7rem;">
                                        <span id="panel-beneficiary-count">0</span> Total Records
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1 bg-white rounded-4 border p-2" style="background: #f8fafc !important;">
                                <div id="panel-beneficiary-list" class="beneficiary-list p-2" style="max-height: 550px; overflow-y: auto;">
                                    <!-- Loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    const EB_MAGALONA_BOUNDS = [[10.740, 122.935], [10.920, 123.175]];
    const EB_MAGALONA_CENTER = [10.8300, 123.0550];
    const INITIAL_ZOOM = 12;

    const map = L.map('map', {
        center: EB_MAGALONA_CENTER,
        zoom: INITIAL_ZOOM,
        maxBounds: EB_MAGALONA_BOUNDS,
        maxBoundsViscosity: 1.0,
        minZoom: 11,
        maxZoom: 16
    });

    // Layers Configuration
    const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    });

    const osmStandard = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    });

    const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',
        maxZoom: 17
    });

    // Set Carto Light as default
    cartoLight.addTo(map);

    const baseLayers = {
        'Modern View': cartoLight,
        'Original View': osmStandard,
        'Satellite': satelliteMap
    };
    L.control.layers(baseLayers, null, { position: 'topleft' }).addTo(map);

    // Custom Legend with clearer indicators
    const legend = L.control({ position: 'bottomleft' });
    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'map-legend');
        div.innerHTML = `
            <div class="fw-bold mb-2 text-dark" style="font-size: 0.85rem;">Status Indicators</div>
            <div class="legend-item"><span class="legend-dot" style="background: #10b981;"></span>Completed</div>
            <div class="legend-item"><span class="legend-dot" style="background: #f59e0b;"></span>Ongoing (Active)</div>
            <div class="legend-item"><span class="legend-dot" style="background: #3b82f6;"></span>Scheduled</div>
            <div class="legend-item"><span class="legend-dot" style="background: #ef4444;"></span>No Distribution</div>
            <div class="mt-2 pt-2 border-top small text-muted">Pins show beneficiary count</div>
        `;
        return div;
    };
    legend.addTo(map);

    let markers = {};
    let barangayDataMap = {};
    const barangayModal = new bootstrap.Modal(document.getElementById('barangayModal'));

    async function loadMapData() {
        const agencyId = document.getElementById('agencyFilter').value;
        const quadrant = document.getElementById('quadrantFilter').value;
        const status = document.getElementById('statusFilter').value;
        const sector = document.getElementById('sectorFilter').value;
        
        const params = new URLSearchParams();
        if (agencyId) params.append('agency_id', agencyId);
        if (quadrant) params.append('quadrant', quadrant);
        if (status) params.append('status', status);
        if (sector) params.append('sector', sector);

        try {
            const response = await fetch(`{{ route('geo-map.data') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Failed to load map data');
            const result = await response.json();
            
            // Update Summary Labels based on sector
            const benefLabel = document.querySelector('#stat-beneficiaries').nextElementSibling;
            if (sector === 'farmer') benefLabel.textContent = 'Total Farmers';
            else if (sector === 'fisherfolk') benefLabel.textContent = 'Total Fisherfolk';
            else if (sector === 'both') benefLabel.textContent = 'Total Farmer & Fisherfolk';
            else benefLabel.textContent = 'Total Beneficiaries';

            // Tracking coordinates to handle overlaps
            const coordCounts = {};
            
            // Clear existing markers
            Object.values(markers).forEach(m => map.removeLayer(m));
            markers = {};
            barangayDataMap = {};

            result.data.forEach(barangay => {
                barangayDataMap[barangay.id] = barangay;
                
                const isOngoing = barangay.distribution_status === 'ongoing';
                const color = barangay.pin_color || '#6b7280';
                const count = barangay.total_beneficiaries || 0;
                const displayCount = count > 999 ? (count/1000).toFixed(1) + 'k' : count;
                const isLarge = String(displayCount).length > 2;

                // More aggressive spreading for overlapping pins (Poblacion)
                let lat = parseFloat(barangay.latitude);
                let lng = parseFloat(barangay.longitude);
                const coordKey = `${lat.toFixed(5)},${lng.toFixed(5)}`;
                
                if (coordCounts[coordKey]) {
                    // Using a much larger spiral to ensure zero overlap in the Town Center
                    const count = coordCounts[coordKey];
                    const angle = count * (Math.PI / 2.5); // Tighter spiral angle
                    const radius = 0.00085 * count; // ~100m incremental radius
                    lat += radius * Math.cos(angle);
                    lng += radius * Math.sin(angle);
                    coordCounts[coordKey]++;
                } else {
                    coordCounts[coordKey] = 1;
                }
                
                // Classic Teardrop Pin Design with Internal Text
                const fontSize = isLarge ? '10px' : '11px';
                const pinHtml = `
                    <div class="pin-marker-container" style="color: ${color};">
                        ${isOngoing ? '<div class="pin-pulse-effect"></div>' : ''}
                        <div class="pin-main">
                            <svg viewBox="0 0 24 32" width="36" height="46" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 0C7.03 0 3 4.03 3 9c0 5.25 9 23 9 23s9-17.75 9-23c0-4.97-4.03-9-9-9z" class="pin-path" />
                                <circle cx="12" cy="11" r="7" class="pin-center-circle" />
                                <text x="12" y="11.5" text-anchor="middle" dominant-baseline="central" class="pin-count-text" style="font-size: ${fontSize};">${displayCount}</text>
                            </svg>
                        </div>
                    </div>
                `;

                // Structured Tooltip Content
                const statusLabel = barangay.distribution_status === 'none' ? 'No Distribution' : (barangay.distribution_status.charAt(0).toUpperCase() + barangay.distribution_status.slice(1));
                const tooltipHtml = `
                    <div class="tooltip-card">
                        <div class="tooltip-header">
                            <span class="tooltip-title">${barangay.name}</span>
                            <span class="tooltip-quadrant">${barangay.quadrant || 'N/A'}</span>
                        </div>
                        <div class="tooltip-body">
                            <div class="tooltip-row">
                                <span class="tooltip-label"><i class="bi bi-people text-primary"></i> Beneficiaries</span>
                                <span class="tooltip-value">${barangay.total_beneficiaries}</span>
                            </div>
                            <div class="tooltip-row">
                                <span class="tooltip-label"><i class="bi bi-bullseye text-success"></i> Coverage Rate</span>
                                <span class="tooltip-value text-success">${barangay.coverage_rate}%</span>
                            </div>
                            <div class="tooltip-row">
                                <span class="tooltip-label"><i class="bi bi-calendar-event text-warning"></i> Total Events</span>
                                <span class="tooltip-value">${barangay.total_events}</span>
                            </div>
                        </div>
                        <div class="tooltip-footer" style="background: ${color}15; color: ${color};">
                            <div class="status-indicator" style="background: ${color}; box-shadow: 0 0 6px ${color}80;"></div>
                            <span>${statusLabel}</span>
                        </div>
                    </div>
                `;

                const marker = L.marker([lat, lng], {
                    icon: L.divIcon({ 
                        html: pinHtml, 
                        iconSize: [36, 46], 
                        iconAnchor: [18, 46], 
                        className: 'custom-pin' 
                    })
                }).bindTooltip(tooltipHtml, { 
                    direction: 'top', 
                    className: 'pin-label',
                    offset: [0, -48],
                    sticky: false,
                    opacity: 1
                }).on('click', () => openBarangayModal(barangay))
                  .addTo(map);
                
                markers[barangay.id] = marker;
            });

            // Update Global Stats
            updateGlobalStats(result.data);

            if (Object.keys(markers).length > 0) {
                const group = new L.featureGroup(Object.values(markers));
                map.fitBounds(group.getBounds().pad(0.1), { maxZoom: INITIAL_ZOOM, animate: true });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function darkenColor(col, amt) {
        let usePound = false;
        if (col[0] == "#") { col = col.slice(1); usePound = true; }
        let num = parseInt(col, 16);
        let r = (num >> 16) - amt;
        if (r > 255) r = 255; else if (r < 0) r = 0;
        let b = ((num >> 8) & 0x00FF) - amt;
        if (b > 255) b = 255; else if (b < 0) b = 0;
        let g = (num & 0x0000FF) - amt;
        if (g > 255) g = 255; else if (g < 0) g = 0;
        return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16).padStart(6, '0');
    }

    function updateGlobalStats(data) {
        let tb = 0, te = 0, tc = 0;
        data.forEach(b => { 
            tb += b.total_beneficiaries; 
            te += b.total_events; 
            tc += b.coverage_rate; 
        });
        const ac = data.length > 0 ? Math.round(tc / data.length) : 0;
        
        animateNumber('stat-barangays', data.length);
        animateNumber('stat-beneficiaries', tb);
        animateNumber('stat-events', te);
        document.getElementById('stat-reach').textContent = ac + '%';
    }

    function applyClientFilters() {
        const search = document.getElementById('mapSearch').value.toLowerCase();

        Object.values(markers).forEach(marker => {
            const bId = Object.keys(markers).find(key => markers[key] === marker);
            const b = barangayDataMap[bId];
            
            let visible = true;
            if (search && !b.name.toLowerCase().includes(search)) visible = false;

            if (visible) marker.addTo(map);
            else map.removeLayer(marker);
        });
    }

    function animateNumber(id, finalValue) {
        const el = document.getElementById(id);
        let startValue = 0;
        const duration = 1000;
        const step = (finalValue - startValue) / (duration / 16);
        
        const update = () => {
            startValue += step;
            if (startValue >= finalValue) {
                el.textContent = finalValue.toLocaleString();
            } else {
                el.textContent = Math.floor(startValue).toLocaleString();
                requestAnimationFrame(update);
            }
        };
        update();
    }

    function openBarangayModal(barangay) {
        document.getElementById('panel-barangay-name').textContent = barangay.name;
        document.getElementById('panel-quadrant-badge').textContent = barangay.quadrant || 'N/A';
        document.getElementById('panel-coverage').textContent = `${barangay.coverage_rate}%`;
        document.getElementById('panel-coverage-bar').style.width = `${barangay.coverage_rate}%`;
        
        const status = barangay.distribution_status || 'none';
        const sb = document.getElementById('panel-status-badge');
        sb.textContent = (status === 'none' ? 'No Distribution' : status).toUpperCase();
        sb.className = `badge-info ${status}`;
        
        document.getElementById('panel-last-dist').textContent = barangay.last_distribution_date ? new Date(barangay.last_distribution_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        
        document.getElementById('panel-total-benef').textContent = (barangay.total_beneficiaries || 0).toLocaleString();
        document.getElementById('panel-reached').textContent = (barangay.beneficiaries_reached || 0).toLocaleString();
        document.getElementById('panel-farmers').textContent = (barangay.total_farmers || 0).toLocaleString();
        document.getElementById('panel-fisherfolk').textContent = (barangay.total_fisherfolk || 0).toLocaleString();
        document.getElementById('panel-total-events').textContent = (barangay.total_events || 0).toLocaleString() + ' Events';
        
        const fa = Number(barangay.total_fund_allocated || 0);
        document.getElementById('panel-fund-allocated').textContent = '₱' + fa.toLocaleString('en-PH', {minimumFractionDigits: 0});

        loadBeneficiaries(barangay.id);
        barangayModal.show();
    }

    // Search Functionality
    document.getElementById('mapSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        for (let id in barangayDataMap) {
            if (barangayDataMap[id].name.toLowerCase().includes(query)) {
                const b = barangayDataMap[id];
                map.flyTo([b.latitude, b.longitude], 15);
                setTimeout(() => openBarangayModal(b), 800);
                break;
            }
        }
    });

    const escapeHtml = (value) => {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[char];
        });
    };

    const formatDate = (value) => {
        if (!value) return 'N/A';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const copyToClipboard = async (text) => {
        if (!text || text === 'N/A') return false;
        
        // Try modern API first
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (err) {
                console.error('Modern copy failed, trying fallback', err);
            }
        }

        // Fallback for non-secure contexts or failed modern API
        try {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            return successful;
        } catch (err) {
            console.error('Fallback copy failed', err);
            return false;
        }
    };

    function loadBeneficiaries(barangayId) {
        const container = document.getElementById('panel-beneficiary-list');
        const countBadge = document.getElementById('panel-beneficiary-count');


        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-success"></div><div class="small text-muted mt-2">Fetching records...</div></div>';

        fetch(`/api/barangay/${barangayId}/beneficiaries`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            countBadge.textContent = data.beneficiaries.length;

            if (data.beneficiaries.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted small">No beneficiaries found</div>';
                return;
            }

            let html = '';
            data.beneficiaries.forEach(benef => {
                const classification = benef.classification || 'Unknown';
                const classColor = classification === 'Farmer' ? 'success' : classification === 'Fisherfolk' ? 'info' : 'warning';
                const safeName = escapeHtml(benef.full_name || benef.name || 'N/A');
                const safeCode = escapeHtml(benef.beneficiary_code || ('BEN-' + benef.id));
                const safeContact = benef.contact_number ? escapeHtml(benef.contact_number) : 'N/A';
                const hasContact = !!(benef.contact_number && String(benef.contact_number).trim() !== '');

                const chips = [];
                if (benef.is_urgent) chips.push('<span class="chip chip-urgent">Urgent</span>');
                if (benef.is_unverified_profile) chips.push('<span class="chip chip-unverified">Unverified</span>');
                if (benef.has_duplicate_risk) chips.push('<span class="chip chip-duplicate">Duplicate Risk</span>');

                html += `
                    <div class="beneficiary-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">${safeName}</div>
                                <div class="text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">${safeCode}</div>
                            </div>
                            <span class="badge bg-${classColor}-subtle text-${classColor} border border-${classColor}" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;">${classification.toUpperCase()}</span>
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-telephone me-1"></i> ${safeContact}
                        </div>
                        ${chips.length ? `<div class="chip-row mb-3">${chips.join('')}</div>` : ''}
                        <div class="d-flex gap-2">
                            <a href="${benef.profile_url}" class="btn btn-sm btn-light border flex-grow-1 fw-bold" style="font-size: 0.75rem;">View</a>
                            <button type="button" class="btn btn-sm btn-light border js-copy-contact" data-contact="${safeContact}" ${hasContact ? '' : 'disabled'}>
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;

        });
    }
    // Delegated Copy Event
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.js-copy-contact');
        if (btn) {
            const contact = btn.getAttribute('data-contact');
            const copied = await copyToClipboard(contact);
            const originalIcon = btn.innerHTML;
            
            btn.innerHTML = copied ? '<i class="bi bi-check-lg text-success"></i>' : '<i class="bi bi-x-lg text-danger"></i>';
            btn.classList.add('btn-copy-success');
            
            setTimeout(() => { 
                btn.innerHTML = originalIcon;
                btn.classList.remove('btn-copy-success');
            }, 2000);
        }
    });

    document.getElementById('agencyFilter').addEventListener('change', loadMapData);
    document.getElementById('quadrantFilter').addEventListener('change', loadMapData);
    document.getElementById('statusFilter').addEventListener('change', loadMapData);
    document.getElementById('sectorFilter').addEventListener('change', loadMapData);
    document.getElementById('mapSearch').addEventListener('input', applyClientFilters);

    document.getElementById('clearFilters').addEventListener('click', () => {
        document.getElementById('agencyFilter').value = '';
        document.getElementById('quadrantFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('sectorFilter').value = '';
        document.getElementById('mapSearch').value = '';
        loadMapData();
    });

    loadMapData();
});
</script>
@endpush
