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
        border-radius: 1rem; 
        padding: 1.25rem; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
        border: 1px solid #f0f0f0;
    }
    .summary-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; }
    .stat-card { 
        background: #fff; 
        border-radius: 1rem; 
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

    /* Premium Pin Design - Redesigned for clarity and high counts */
    .pin-marker-container { 
        position: relative; 
        width: 40px; 
        height: 50px; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        justify-content: center;
    }
    .pin-marker-container:hover { transform: translateY(-8px) scale(1.15); z-index: 1000 !important; }
    
    .pin-main {
        position: relative;
        width: 38px;
        height: 38px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.25));
    }

    .pin-droplet {
        fill: currentColor;
        stroke: #fff;
        stroke-width: 2;
    }

    .pin-badge {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 22px;
        height: 22px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .pin-count {
        color: #111827;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
        text-align: center;
    }

    .pin-count.small-text { font-size: 8px; }

    /* Stem of the pin */
    .pin-stem {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 12px solid currentColor;
        filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));
    }
    .pin-stem::after {
        content: '';
        position: absolute;
        top: -14px;
        left: -6px;
        width: 12px;
        height: 12px;
        background: currentColor;
        border-radius: 50%;
    }

    /* Pulsing Animation for Ongoing distribution */
    @keyframes pin-pulse {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    .pin-pulse-effect {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 4px solid rgba(255, 193, 7, 0.8);
        animation: pin-pulse 1.5s infinite;
        z-index: -1;
    }

    .pin-label.leaflet-tooltip { 
        background: #fff; 
        border: none; 
        border-radius: 8px; 
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); 
        padding: 8px 12px; 
        font-size: 13px; 
        font-weight: 700; 
        color: #111827;
        border-bottom: 3px solid #10b981;
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
    
    @media (max-width: 991.98px) { #map { height: 50vh; min-height: 450px; } }
    @media (max-width: 575.98px) { #map { height: 45vh; min-height: 350px; } .filters-bar { padding: 0.6rem; } .summary-stats { grid-template-columns: repeat(2, 1fr); } .barangay-info-modal .modal-dialog { max-width: calc(100vw - 1rem); } }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="geom-container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">Geo-Insights Dashboard</h1>
                <p class="text-muted small mb-0">E.B. Magalona Distribution & Beneficiary Real-time Mapping</p>
            </div>
            <div class="search-container">
                <i class="bi bi-search"></i>
                <input type="text" id="mapSearch" class="form-control" placeholder="Search Barangay...">
            </div>
        </div>

        <div class="filters-bar glass-effect">
            <div class="filter-group d-flex align-items-center gap-3 flex-wrap">
                <div class="filter-item">
                    <label>Line Agency</label>
                    <select id="agencyFilter" class="form-select form-select-sm">
                        <option value="">All Agencies</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label>Distribution Status</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="pending">Pending</option>
                        <option value="none">No Distribution</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Sector</label>
                    <select id="sectorFilter" class="form-select form-select-sm">
                        <option value="">All Sectors</option>
                        <option value="farmer">Farmer</option>
                        <option value="fisherfolk">Fisherfolk</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-sm btn-light text-dark fw-bold border" id="clearFilters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset View
                    </button>
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
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bold mb-0" id="panel-barangay-name">Barangay Name</h4>
                    <p class="mb-0 small text-white-50" id="panel-subtitle">E.B. Magalona, Negros Occidental</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-section">
                            <div class="info-section-title"><i class="bi bi-graph-up-arrow"></i>Distribution Performance</div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold">Coverage Rate</span>
                                <span class="fw-bold text-success" id="panel-coverage">0%</span>
                            </div>
                            <div class="progress-coverage mb-3">
                                <div class="progress-coverage-bar" id="panel-coverage-bar" style="width: 0%"></div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge-info" id="panel-status-badge">STATUS</span>
                                <span class="text-muted small ms-3">Last Activity: <span id="panel-last-dist" class="text-dark fw-bold">N/A</span></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <div class="info-section-title"><i class="bi bi-people-fill"></i>Beneficiary Mix</div>
                            <div class="info-stat-grid">
                                <div class="info-stat-box">
                                    <div class="value" id="panel-total-benef">0</div>
                                    <div class="label">Registered</div>
                                </div>
                                <div class="info-stat-box">
                                    <div class="value" id="panel-reached">0</div>
                                    <div class="label">Served</div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <div class="badge bg-light text-dark border px-3 py-2"><i class="bi bi-flower1 text-success me-1"></i> <span id="panel-farmers">0</span> Farmers</div>
                                <div class="badge bg-light text-dark border px-3 py-2"><i class="bi bi-water text-primary me-1"></i> <span id="panel-fisherfolk">0</span> Fisherfolk</div>
                            </div>
                        </div>

                        <div class="info-section mb-0">
                            <div class="info-section-title"><i class="bi bi-cash-coin"></i>Financial & Resources</div>
                            <div class="p-3 bg-light rounded-3 border mb-2">
                                <div class="text-muted small mb-1">Fund Allocated</div>
                                <div class="fw-bold text-dark h5 mb-0" id="panel-fund-allocated">₱0</div>
                            </div>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small mb-1">Total Events Conducted</div>
                                <div class="fw-bold text-dark h5 mb-0" id="panel-total-events">0</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-section mb-0">
                            <div class="info-section-title d-flex justify-content-between">
                                <span><i class="bi bi-list-stars"></i>Beneficiary Directory</span>
                                <span class="badge bg-success" id="panel-beneficiary-count">0</span>
                            </div>
                            <div id="panel-beneficiary-list" class="beneficiary-list" style="max-height: 500px; overflow-y: auto;">
                                <!-- Loaded via AJAX -->
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

    // Custom Legend
    const legend = L.control({ position: 'bottomleft' });
    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'map-legend glass-effect');
        div.innerHTML = `
            <div class="fw-bold mb-2">Distribution Activity</div>
            <div class="d-flex align-items-center mb-1"><span class="legend-dot" style="background: #10b981;"></span>Completed</div>
            <div class="d-flex align-items-center mb-1"><span class="legend-dot" style="background: #f59e0b;"></span>Ongoing</div>
            <div class="d-flex align-items-center mb-1"><span class="legend-dot" style="background: #3b82f6;"></span>Pending</div>
            <div class="d-flex align-items-center"><span class="legend-dot" style="background: #ef4444;"></span>No Data</div>
        `;
        return div;
    };
    legend.addTo(map);

    let markers = {};
    let barangayDataMap = {};
    const barangayModal = new bootstrap.Modal(document.getElementById('barangayModal'));

    async function loadMapData() {
        const params = new URLSearchParams();
        if (document.getElementById('agencyFilter').value) params.append('agency_id', document.getElementById('agencyFilter').value);
        if (document.getElementById('statusFilter').value) params.append('status', document.getElementById('statusFilter').value);
        if (document.getElementById('sectorFilter').value) params.append('sector', document.getElementById('sectorFilter').value);

        try {
            const response = await fetch(`{{ route('geo-map.data') }}?${params}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Failed to load map data');
            const result = await response.json();
            
            // Clear existing markers
            Object.values(markers).forEach(m => map.removeLayer(m));
            markers = {};
            barangayDataMap = {};

            result.data.forEach(barangay => {
                barangayDataMap[barangay.id] = barangay;
                
                const isOngoing = barangay.distribution_status === 'ongoing';
                const color = barangay.pin_color;
                const count = barangay.total_beneficiaries;
                const displayCount = count > 999 ? (count/1000).toFixed(1) + 'k' : count;
                const isSmall = String(displayCount).length > 2;
                
                // Redesigned Pin HTML - Cleaner Droplet Style
                const pinHtml = `
                    <div class="pin-marker-container" style="color: ${color};">
                        ${isOngoing ? '<div class="pin-pulse-effect"></div>' : ''}
                        <div class="pin-main">
                            <svg viewBox="0 0 38 38" width="38" height="38">
                                <circle cx="19" cy="19" r="17" class="pin-droplet" />
                            </svg>
                            <div class="pin-badge">
                                <span class="pin-count ${isSmall ? 'small-text' : ''}">${displayCount}</span>
                            </div>
                        </div>
                        <div class="pin-stem"></div>
                    </div>
                `;

                const marker = L.marker([barangay.latitude, barangay.longitude], {
                    icon: L.divIcon({ 
                        html: pinHtml, 
                        iconSize: [40, 50], 
                        iconAnchor: [20, 50], 
                        className: 'custom-pin' 
                    })
                }).bindTooltip(`<div class="text-center"><strong>${barangay.name}</strong><br><span class="text-success small">${barangay.coverage_rate}% Reached</span></div>`, { 
                    direction: 'top', 
                    className: 'pin-label',
                    offset: [0, -50]
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
        document.getElementById('panel-coverage').textContent = `${barangay.coverage_rate}%`;
        document.getElementById('panel-coverage-bar').style.width = `${barangay.coverage_rate}%`;
        
        const sb = document.getElementById('panel-status-badge');
        sb.textContent = (barangay.distribution_status || 'Pending').toUpperCase();
        sb.className = `badge-info ${barangay.distribution_status || 'pending'}`;
        
        document.getElementById('panel-last-dist').textContent = barangay.last_distribution_date ? new Date(barangay.last_distribution_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        
        document.getElementById('panel-total-benef').textContent = (barangay.total_beneficiaries || 0).toLocaleString();
        document.getElementById('panel-reached').textContent = (barangay.beneficiaries_reached || 0).toLocaleString();
        document.getElementById('panel-farmers').textContent = (barangay.total_farmers || 0).toLocaleString();
        document.getElementById('panel-fisherfolk').textContent = (barangay.total_fisherfolk || 0).toLocaleString();
        document.getElementById('panel-total-events').textContent = (barangay.total_events || 0).toLocaleString();
        
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

    function loadBeneficiaries(barangayId) {
        const container = document.getElementById('panel-beneficiary-list');
        const countBadge = document.getElementById('panel-beneficiary-count');

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
            if (!text) return false;
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (_) {
                return false;
            }
        };

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

            container.querySelectorAll('.js-copy-contact').forEach((button) => {
                button.addEventListener('click', async () => {
                    const contact = button.getAttribute('data-contact');
                    const copied = await copyToClipboard(contact);
                    const originalIcon = button.innerHTML;
                    button.innerHTML = copied ? '<i class="bi bi-check text-success"></i>' : '<i class="bi bi-x text-danger"></i>';
                    setTimeout(() => { button.innerHTML = originalIcon; }, 1500);
                });
            });
        });
    }

    document.getElementById('agencyFilter').addEventListener('change', loadMapData);
    document.getElementById('statusFilter').addEventListener('change', loadMapData);
    document.getElementById('sectorFilter').addEventListener('change', loadMapData);
    document.getElementById('clearFilters').addEventListener('click', () => {
        document.getElementById('agencyFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('sectorFilter').value = '';
        loadMapData();
    });

    loadMapData();
});
</script>
@endpush
