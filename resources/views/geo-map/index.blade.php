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

    .tooltip-zone {
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
    .modal-stat-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
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
        .modal-stat-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 575.98px) { 
        #map { height: 45vh; min-height: 350px; }
        .filters-bar { padding: 0.6rem; }
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

    /* Enhanced stat card icon boxes */
    .modal-stat-card { display: flex; align-items: center; gap: 0.85rem; text-align: left; }
    .modal-stat-card .stat-icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; }
    .modal-stat-card .stat-icon-box.icon-dark    { background: #f1f5f9; color: #1e293b; }
    .modal-stat-card .stat-icon-box.icon-primary { background: #dbeafe; color: #2563eb; }
    .modal-stat-card .stat-icon-box.icon-success { background: #dcfce7; color: #15803d; }
    .modal-stat-card .stat-icon-box.icon-info    { background: #cffafe; color: #0e7490; }
    .modal-stat-card .stat-text { display: flex; flex-direction: column; min-width: 0; }

    /* Right-column tab pills */
    .modal-right-tabs .nav-link { font-size: 0.8rem; font-weight: 700; color: #64748b; border-radius: 10px; padding: 0.4rem 1rem; }
    .modal-right-tabs .nav-link.active { background: #10b981; color: #fff; }
    .modal-right-tabs .nav-link:hover:not(.active) { background: #f0fdf4; color: #059669; }

    /* Event card colored left border */
    .event-card { border-left: 4px solid transparent !important; border-radius: 12px; transition: box-shadow 0.2s ease; }
    .event-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
    .event-card.status-completed { border-left-color: #10b981 !important; }
    .event-card.status-ongoing   { border-left-color: #f59e0b !important; }
    .event-card.status-pending   { border-left-color: #3b82f6 !important; }
    .event-card.status-default   { border-left-color: #94a3b8 !important; }

    /* Skeleton loading animation */
    @keyframes skeleton-shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .skeleton-line {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 800px 100%;
        animation: skeleton-shimmer 1.4s infinite linear;
        border-radius: 6px;
    }
    .skeleton-card {
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.6rem;
    }

    /* Beneficiary filter bar */
    .benef-filter-bar { padding: 0.5rem 0.25rem 0.75rem; display: flex; gap: 0.5rem; align-items: center; }
    .benef-filter-bar input { flex: 1; font-size: 0.82rem; border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.35rem 0.75rem 0.35rem 2rem; background: #f8fafc; }
    .benef-filter-bar input:focus { outline: none; border-color: #10b981; background: #fff; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
    .benef-filter-bar .search-icon { position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; pointer-events: none; }
    .benef-filter-bar .filter-search-wrap { position: relative; flex: 1; }
    .benef-filter-bar select { font-size: 0.78rem; border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.35rem 0.6rem; background: #f8fafc; color: #475569; font-weight: 600; cursor: pointer; }
    .benef-filter-bar select:focus { outline: none; border-color: #10b981; }

    /* Distribution summary 2x2 mini grid */
    .dist-summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .dist-summary-cell { background: #fff; border: 1.5px dashed #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; }
    .dist-summary-cell .ds-label { font-size: 0.68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.2rem; }
    .dist-summary-cell .ds-value { font-size: 1.15rem; font-weight: 800; line-height: 1.1; }

    /* No-results overlay */
    .map-no-results {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 500;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 1rem;
        padding: 2rem 3rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.05);
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        pointer-events: none;
    }
    /* Hide tooltips while search is active — prevents auto-show on hover */
    #map.searching .leaflet-tooltip { display: none !important; }

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
                            <i class="bi bi-geo-alt"></i>
                            <select id="quadrantFilter" class="form-select">
                                <option value="">Zones</option>
                                <option value="Urban Center">Urban Center</option>
                                <option value="Coastal Area">Coastal Area</option>
                                <option value="Upland & Inland">Upland & Inland</option>
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
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary px-3 rounded-3 d-flex align-items-center gap-2" id="statsDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="View Summary Stats" style="height:40px;">
                                <i class="bi bi-bar-chart-line"></i>
                                <span class="small fw-semibold d-none d-sm-inline">Stats</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow-sm border-0 rounded-4" style="min-width:300px;" aria-labelledby="statsDropdownBtn">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-light text-center">
                                            <div class="fw-bold text-success fs-5 mb-0" id="stat-barangays">--</div>
                                            <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Barangays</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-light text-center">
                                            <div class="fw-bold text-primary fs-5 mb-0" id="stat-beneficiaries">--</div>
                                            <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Beneficiaries</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-light text-center">
                                            <div class="fw-bold text-warning fs-5 mb-0" id="stat-events">--</div>
                                            <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Total Events</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded-3 bg-light text-center">
                                            <div class="fw-bold text-info fs-5 mb-0" id="stat-reach">--%</div>
                                            <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Municipality Reach</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-dark px-3 rounded-3" id="clearFilters" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="map"></div>
        <div id="mapNoResults" class="map-no-results">
            <i class="bi bi-search fs-2 text-muted"></i>
            <div class="fw-bold text-dark">No barangays found</div>
            <div class="text-muted small">Try a different search term or zone name</div>
        </div>
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
                            <span class="badge bg-white text-dark opacity-75" id="panel-quadrant-badge" style="font-size: 0.7rem;">Zone</span>
                        </div>
                        <p class="mb-0 small text-white-50">E.B. Magalona, Negros Occidental</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary Stats Row -->
                <div class="modal-stat-grid mb-4">
                    <div class="modal-stat-card">
                        <div class="stat-icon-box icon-dark"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-text">
                            <span class="value" id="panel-total-benef">0</span>
                            <span class="label">Total Beneficiaries</span>
                        </div>
                    </div>
                    <div class="modal-stat-card">
                        <div class="stat-icon-box icon-primary"><i class="bi bi-person-check-fill"></i></div>
                        <div class="stat-text">
                            <span class="value text-primary" id="panel-reached">0</span>
                            <span class="label">Reached / Served</span>
                        </div>
                    </div>
                    <div class="modal-stat-card">
                        <div class="stat-icon-box icon-success"><i class="bi bi-tree-fill"></i></div>
                        <div class="stat-text">
                            <span class="value text-success" id="panel-farmers">0</span>
                            <span class="label">Farmers</span>
                        </div>
                    </div>
                    <div class="modal-stat-card">
                        <div class="stat-icon-box icon-info"><i class="bi bi-water"></i></div>
                        <div class="stat-text">
                            <span class="value text-info" id="panel-fisherfolk">0</span>
                            <span class="label">Fisherfolk</span>
                        </div>
                    </div>
                    <div class="modal-stat-card">
                        <div class="stat-icon-box icon-warning"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-text">
                            <span class="value text-warning" id="panel-both">0</span>
                            <span class="label">Both</span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-lg-4">
                        <!-- Section 1: Status & Reach -->
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
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="text-muted small fw-bold">First Activity</span>
                                        <span id="panel-first-dist" class="text-dark fw-bold small">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Distribution Summary (merged) -->
                        <div class="info-section mb-0">
                            <div class="info-section-title"><i class="bi bi-cash-stack"></i>Distribution Summary</div>
                            <div class="dist-summary-grid">
                                <div class="dist-summary-cell">
                                    <div class="ds-label">Fund Allocated</div>
                                    <div class="ds-value text-dark" id="panel-fund-allocated">₱0</div>
                                </div>
                                <div class="dist-summary-cell">
                                    <div class="ds-label">Total Events</div>
                                    <div class="ds-value text-dark" id="panel-total-events">0</div>
                                </div>
                                <div class="dist-summary-cell">
                                    <div class="ds-label">Event-Based</div>
                                    <div class="ds-value text-primary" id="panel-event-allocations">—</div>
                                </div>
                                <div class="dist-summary-cell">
                                    <div class="ds-label">Direct</div>
                                    <div class="ds-value text-warning" id="panel-direct-allocations">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Tabbed (Events / Beneficiaries) -->
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-3">
                            <ul class="nav nav-pills modal-right-tabs gap-1" id="barangayTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-events-btn"
                                        data-bs-toggle="pill" data-bs-target="#tab-events"
                                        type="button" role="tab" aria-selected="true">
                                        <i class="bi bi-calendar2-event me-1"></i>Events
                                        <span class="badge bg-warning-subtle text-warning ms-1 rounded-pill" style="font-size:0.65rem;" id="panel-events-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-beneficiaries-btn"
                                        data-bs-toggle="pill" data-bs-target="#tab-beneficiaries"
                                        type="button" role="tab" aria-selected="false">
                                        <i class="bi bi-list-stars me-1"></i>Beneficiaries
                                        <span class="badge bg-success-subtle text-success ms-1 rounded-pill" style="font-size:0.65rem;" id="panel-beneficiary-count">0</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="barangayTabContent">
                            <!-- Events Tab -->
                            <div class="tab-pane fade show active" id="tab-events" role="tabpanel">
                                <div id="panel-events-list" class="beneficiary-list" style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-3 text-muted small">Loading...</div>
                                </div>
                            </div>

                            <!-- Beneficiaries Tab -->
                            <div class="tab-pane fade" id="tab-beneficiaries" role="tabpanel">
                                <div class="benef-filter-bar" id="benef-filter-bar" style="display:none;">
                                    <div class="filter-search-wrap">
                                        <i class="bi bi-search search-icon"></i>
                                        <input type="text" id="benef-search" placeholder="Search by name...">
                                    </div>
                                    <select id="benef-class-filter">
                                        <option value="">All</option>
                                        <option value="Farmer">Farmer</option>
                                        <option value="Fisherfolk">Fisherfolk</option>
                                        <option value="Farmer & Fisherfolk">Both</option>
                                    </select>
                                </div>
                                <div class="bg-white rounded-4 border p-2" style="background: #f8fafc !important;">
                                    <div id="panel-beneficiary-list" class="beneficiary-list p-2" style="max-height: 480px; overflow-y: auto;">
                                        <div class="text-center py-4 text-muted small">Switch to this tab to load records.</div>
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
    let beneficiaryTabLoaded = false;
    let currentBeneficiaryBarangayId = null;
    const EVENT_BASE_URL = '{{ url("distribution-events") }}';

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
                            <span class="tooltip-zone">${barangay.quadrant || 'N/A'}</span>
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
        const search = document.getElementById('mapSearch').value.toLowerCase().trim();
        const visibleData = [];

        // Fix #2: O(n) iteration using Object.entries instead of O(n²) reverse lookup
        Object.entries(markers).forEach(([bId, marker]) => {
            const b = barangayDataMap[bId];
            if (!b) return;

            let visible = true;
            if (search) {
                // Fix #6: Match both barangay name AND zone
                const nameMatch = b.name.toLowerCase().includes(search);
                const zoneMatch = (b.quadrant || '').toLowerCase().includes(search);
                if (!nameMatch && !zoneMatch) visible = false;
            }

            if (visible) {
                marker.addTo(map);
                visibleData.push(b);
            } else {
                map.removeLayer(marker);
            }
        });

        // Fix #1: Update stats to reflect the filtered results
        updateGlobalStats(visibleData);

        // Fix #3: Show/hide empty-state overlay
        const noResults = document.getElementById('mapNoResults');
        noResults.style.display = (visibleData.length === 0 && search) ? 'flex' : 'none';

        // Suppress tooltips while search is active so pin content doesn't auto-show
        if (search) {
            map.getContainer().classList.add('searching');
        } else {
            map.getContainer().classList.remove('searching');
        }

        // Zoom to visible markers — tooltips are suppressed by CSS while searching
        if (search && visibleData.length > 0) {
            const visibleMarkers = visibleData.map(b => markers[b.id]).filter(Boolean);
            if (visibleMarkers.length > 0) {
                const group = new L.featureGroup(visibleMarkers);
                map.flyToBounds(group.getBounds().pad(0.2), { maxZoom: 14, duration: 0.8 });
            }
        } else if (!search && Object.keys(markers).length > 0) {
            // Reset to default view when search is cleared
            const group = new L.featureGroup(Object.values(markers));
            map.fitBounds(group.getBounds().pad(0.1), { maxZoom: INITIAL_ZOOM, animate: true });
        }
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
        const rate = barangay.coverage_rate || 0;
        document.getElementById('panel-coverage').textContent = `${rate}%`;
        const bar = document.getElementById('panel-coverage-bar');
        bar.style.width = `${rate}%`;
        bar.style.background = rate >= 70
            ? 'linear-gradient(90deg, #10b981, #34d399)'
            : rate >= 30
                ? 'linear-gradient(90deg, #f59e0b, #fbbf24)'
                : 'linear-gradient(90deg, #ef4444, #f87171)';
        document.getElementById('panel-coverage').className = rate >= 70
            ? 'fw-bold text-success fs-5'
            : rate >= 30
                ? 'fw-bold text-warning fs-5'
                : 'fw-bold text-danger fs-5';
        
        const status = barangay.distribution_status || 'none';
        const sb = document.getElementById('panel-status-badge');
        sb.textContent = (status === 'none' ? 'No Distribution' : status).toUpperCase();
        sb.className = `badge-info ${status}`;
        
        document.getElementById('panel-last-dist').textContent = barangay.last_distribution_date ? new Date(barangay.last_distribution_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        document.getElementById('panel-first-dist').textContent = barangay.first_distribution_date ? new Date(barangay.first_distribution_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';

        document.getElementById('panel-total-benef').textContent = (barangay.total_beneficiaries || 0).toLocaleString();
        document.getElementById('panel-reached').textContent = (barangay.beneficiaries_reached || 0).toLocaleString();
        document.getElementById('panel-farmers').textContent = (barangay.total_farmers_only || 0).toLocaleString();
        document.getElementById('panel-fisherfolk').textContent = (barangay.total_fisherfolk_only || 0).toLocaleString();
        document.getElementById('panel-both').textContent = (barangay.total_both || 0).toLocaleString();
        document.getElementById('panel-total-events').textContent = (barangay.total_events || 0).toLocaleString();

        const fa = Number(barangay.total_fund_allocated || 0);
        document.getElementById('panel-fund-allocated').textContent = '₱' + fa.toLocaleString('en-PH', {minimumFractionDigits: 0});

        // Reset lazy-loaded tabs
        document.getElementById('panel-event-allocations').textContent = '—';
        document.getElementById('panel-direct-allocations').textContent = '—';
        document.getElementById('panel-events-count').textContent = '0';
        document.getElementById('panel-events-list').innerHTML = eventSkeleton(3);
        document.getElementById('panel-beneficiary-list').innerHTML = '<div class="text-center py-4 text-muted small">Switch to this tab to load records.</div>';
        document.getElementById('panel-beneficiary-count').textContent = (barangay.total_beneficiaries || 0).toLocaleString();

        beneficiaryTabLoaded = false;
        currentBeneficiaryBarangayId = barangay.id;
        document.getElementById('benef-filter-bar').style.display = 'none';
        document.getElementById('benef-search').value = '';
        document.getElementById('benef-class-filter').value = '';
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-events-btn')).show();

        loadEvents(barangay.id);
        barangayModal.show();
    }

    // Lazy-load beneficiaries only when tab is clicked
    document.getElementById('tab-beneficiaries-btn').addEventListener('shown.bs.tab', function () {
        if (!beneficiaryTabLoaded && currentBeneficiaryBarangayId !== null) {
            loadBeneficiaries(currentBeneficiaryBarangayId);
            beneficiaryTabLoaded = true;
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

    function eventSkeleton(count = 3) {
        return Array.from({ length: count }, () => `
            <div class="skeleton-card">
                <div class="d-flex gap-2 mb-2 align-items-center">
                    <div class="skeleton-line" style="height:13px; width:55%;"></div>
                    <div class="skeleton-line" style="height:13px; width:18%;"></div>
                    <div class="skeleton-line" style="height:13px; width:15%;"></div>
                </div>
                <div class="skeleton-line" style="height:11px; width:70%;"></div>
            </div>
        `).join('');
    }

    function beneficiarySkeleton(count = 4) {
        return Array.from({ length: count }, () => `
            <div class="skeleton-card">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <div class="skeleton-line mb-1" style="height:13px; width:140px;"></div>
                        <div class="skeleton-line" style="height:10px; width:80px;"></div>
                    </div>
                    <div class="skeleton-line" style="height:20px; width:60px; border-radius:8px;"></div>
                </div>
                <div class="skeleton-line mb-3" style="height:10px; width:110px;"></div>
                <div class="d-flex gap-2">
                    <div class="skeleton-line flex-grow-1" style="height:30px; border-radius:8px;"></div>
                    <div class="skeleton-line" style="height:30px; width:36px; border-radius:8px;"></div>
                </div>
            </div>
        `).join('');
    }

    function loadEvents(barangayId) {
        fetch(`/api/barangay/${barangayId}/events`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('panel-event-allocations').textContent = (data.event_allocations || 0).toLocaleString();
            document.getElementById('panel-direct-allocations').textContent = (data.direct_allocations || 0).toLocaleString();
            document.getElementById('panel-events-count').textContent = (data.events || []).length;

            const container = document.getElementById('panel-events-list');
            if (!data.events || data.events.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted small">No distribution events found</div>';
                return;
            }

            const statusClass = { 'Completed': 'success', 'Ongoing': 'warning', 'Pending': 'primary' };
            const statusBorder = { 'Completed': 'status-completed', 'Ongoing': 'status-ongoing', 'Pending': 'status-pending' };
            const typeLabel = { 'physical': 'Physical', 'financial': 'Financial' };

            let html = '';
            data.events.forEach(ev => {
                const sc = statusClass[ev.status] || 'secondary';
                const bc = statusBorder[ev.status] || 'status-default';
                const tl = typeLabel[ev.type] || ev.type;
                const date = ev.distribution_date ? new Date(ev.distribution_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
                html += `
                    <div class="p-3 mb-2 bg-white border event-card ${bc}" style="font-size: 0.82rem;">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold text-dark">${escapeHtml(ev.program_name)}</span>
                                <span class="badge bg-${sc}-subtle text-${sc} border border-${sc}" style="font-size:0.63rem;">${ev.status}</span>
                                <span class="badge bg-light text-muted border" style="font-size:0.63rem;">${tl}</span>
                            </div>
                            <a href="${EVENT_BASE_URL}/${ev.id}"
                               class="btn btn-sm btn-light border flex-shrink-0"
                               style="font-size:0.7rem; padding: 2px 8px; white-space:nowrap;"
                               title="View event details">
                                <i class="bi bi-arrow-right-circle"></i>
                            </a>
                        </div>
                        <div class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>${date}
                            &nbsp;·&nbsp;<i class="bi bi-box me-1"></i>${escapeHtml(ev.resource_type)}
                            &nbsp;·&nbsp;<span style="font-size:0.72rem;">${ev.total_distributed}/${ev.total_allocated} served</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('panel-events-list').innerHTML = '<div class="text-center py-3 text-danger small">Failed to load events</div>';
        });
    }

    let allBeneficiaries = [];

    function renderBeneficiaryList(items) {
        const container = document.getElementById('panel-beneficiary-list');
        if (items.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted small">No matching beneficiaries</div>';
            return;
        }
        let html = '';
        items.forEach(benef => {
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
    }

    function applyBeneficiaryFilter() {
        const search = document.getElementById('benef-search').value.toLowerCase().trim();
        const cls = document.getElementById('benef-class-filter').value;
        const filtered = allBeneficiaries.filter(b => {
            const name = (b.full_name || b.name || '').toLowerCase();
            const matchName = !search || name.includes(search);
            const matchClass = !cls || b.classification === cls;
            return matchName && matchClass;
        });
        renderBeneficiaryList(filtered);
    }

    function loadBeneficiaries(barangayId) {
        const container = document.getElementById('panel-beneficiary-list');
        const countBadge = document.getElementById('panel-beneficiary-count');

        container.innerHTML = beneficiarySkeleton(4);

        fetch(`/api/barangay/${barangayId}/beneficiaries`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            allBeneficiaries = data.beneficiaries || [];
            countBadge.textContent = allBeneficiaries.length;

            if (allBeneficiaries.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted small">No beneficiaries found</div>';
                document.getElementById('benef-filter-bar').style.display = 'none';
                return;
            }

            document.getElementById('benef-filter-bar').style.display = 'flex';
            renderBeneficiaryList(allBeneficiaries);
        });
    }
    // Beneficiary filter listeners
    document.getElementById('benef-search').addEventListener('input', applyBeneficiaryFilter);
    document.getElementById('benef-class-filter').addEventListener('change', applyBeneficiaryFilter);

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
    // Fix #5: Debounce search input (200ms) to avoid excessive re-filtering
    let searchDebounceTimer;
    document.getElementById('mapSearch').addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(applyClientFilters, 200);
    });

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
