@extends('layouts.app')

@section('title', 'Geo-Map - E.B. Magalona')

@section('breadcrumb')
    <li class="breadcrumb-item active">Geo-Map</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-layers-tree/L.Control.Layers.Tree.css" />
<style>
    .geom-container { display: flex; flex-direction: column; gap: 1rem; }
    #map { height: 600px; width: 100%; border-radius: 0.5rem; z-index: 1; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    @media (min-height: 900px) { #map { height: 65vh; } }
    .leaflet-control { z-index: 400 !important; }
    .map-legend { background: rgba(255,255,255,0.95); backdrop-filter: blur(4px); border-radius: 0.4rem; padding: 0.6rem 0.8rem; font-size: 0.78rem; line-height: 1.8; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
    .legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; }
    .pin-marker { display: flex; flex-direction: column; align-items: center; cursor: pointer; }
    .pin-icon svg { width: 28px; height: 36px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); transition: transform 0.15s ease; }
    .pin-marker:hover .pin-icon svg { transform: scale(1.2); }
    .pin-count { position: absolute; top: 5px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 10px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.4); }
    .pin-label.leaflet-tooltip { background: rgba(255,255,255,0.9); border: none; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); padding: 4px 8px; font-size: 11px; font-weight: 600; color: #333; }
    .pin-label.leaflet-tooltip::before { display: none; }
    .filters-bar { background: #fff; border-radius: 0.5rem; padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
    .filters-bar .filter-group { display: flex; gap: 0.6rem; align-items: flex-end; flex-wrap: wrap; }
    .filters-bar .filter-item { display: flex; flex-direction: column; gap: 0.3rem; }
    .filters-bar label { font-size: 0.8rem; font-weight: 600; color: #495057; margin: 0; }
    .filters-bar select { font-size: 0.85rem; border-radius: 0.35rem; border: 1px solid #dee2e6; padding: 0.4rem 0.6rem; }
    .summary-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.8rem; }
    .stat-card { background: #fff; border-radius: 0.4rem; padding: 0.8rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
    .stat-card .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.3rem; }
    .stat-card .stat-label { font-size: 0.75rem; color: #6c757d; font-weight: 500; }
    .barangay-info-modal .modal-dialog { max-width: 700px; }
    .barangay-info-modal .modal-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 1rem; }
    .barangay-info-modal .modal-title { font-size: 1.1rem; font-weight: 600; }
    .barangay-info-modal .btn-close { filter: brightness(0) invert(1); }
    .barangay-info-modal .modal-body { max-height: 75vh; overflow-y: auto; padding: 1.2rem; }
    .info-section { margin-bottom: 1.2rem; }
    .info-section:last-child { margin-bottom: 0; }
    .info-section-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #28a745; margin-bottom: 0.7rem; display: flex; align-items: center; gap: 0.4rem; }
    .info-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #6c757d; font-weight: 500; }
    .info-row .value { font-weight: 600; color: #333; }
    .info-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; margin-bottom: 0.8rem; }
    .info-stat-box { background: #f8f9fa; border-radius: 0.35rem; padding: 0.6rem; text-align: center; border: 1px solid #e9ecef; }
    .info-stat-box .value { font-size: 1.3rem; font-weight: 700; color: #28a745; }
    .info-stat-box .label { font-size: 0.7rem; color: #6c757d; margin-top: 0.2rem; }
    .badge-info { display: inline-block; padding: 0.4rem 0.8rem; border-radius: 0.3rem; font-size: 0.8rem; font-weight: 600; }
    .badge-info.completed { background: #d4edda; color: #28a745; }
    .badge-info.ongoing { background: #fff3cd; color: #856404; }
    .badge-info.pending { background: #d1ecf1; color: #0c5460; }
    .badge-info.none { background: #f8d7da; color: #721c24; }
    .beneficiary-list { margin-top: 0.8rem; }
    .beneficiary-item { background: #f8f9fa; border-left: 3px solid #28a745; padding: 0.6rem; margin-bottom: 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; }
    .beneficiary-item .name { font-weight: 600; color: #333; }
    .beneficiary-item .details { color: #6c757d; font-size: 0.8rem; margin-top: 0.2rem; }
    .beneficiary-badge { display: inline-block; background: #e7f5e9; color: #2e7d32; padding: 0.2rem 0.4rem; border-radius: 0.2rem; font-size: 0.7rem; font-weight: 600; margin-top: 0.3rem; }
    .no-data { text-align: center; padding: 1rem; color: #999; font-style: italic; }
    @media (max-width: 991.98px) { #map { height: 50vh; min-height: 400px; } .filters-bar { padding: 0.8rem; } .stat-card { padding: 0.6rem; } }
    @media (max-width: 575.98px) { #map { height: 45vh; min-height: 350px; } .filters-bar { padding: 0.6rem; } .summary-stats { grid-template-columns: repeat(2, 1fr); } .barangay-info-modal .modal-dialog { max-width: calc(100vw - 1rem); } }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="geom-container">
        <div>
            <h2 class="h4 mb-1"><i class="bi bi-map me-2 text-success"></i>Beneficiary Distribution Map</h2>
            <p class="text-muted small mb-0">E.B. Magalona, Negros Occidental - Interactive Geo-Located Resource Allocation Overview</p>
        </div>
        <div class="filters-bar">
            <div class="filter-group">
                <div class="filter-item" style="flex: 1; min-width: 150px;">
                    <label for="agencyFilter">Agency</label>
                    <select id="agencyFilter" class="form-select form-select-sm">
                        <option value="">All Agencies</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item" style="flex: 1; min-width: 150px;">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="pending">Pending</option>
                        <option value="none">No Distribution</option>
                    </select>
                </div>
                <div class="filter-item" style="flex: 1; min-width: 150px;">
                    <label for="sectorFilter">Beneficiary Type</label>
                    <select id="sectorFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="farmer">Farmer</option>
                        <option value="fisherfolk">Fisherfolk</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="clearFilters"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            </div>
        </div>
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-value text-success" id="stat-barangays">--</div>
                <div class="stat-label"><i class="bi bi-geo-alt me-1"></i>Barangays</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-primary" id="stat-beneficiaries">--</div>
                <div class="stat-label"><i class="bi bi-people me-1"></i>Beneficiaries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-success" id="stat-events">--</div>
                <div class="stat-label"><i class="bi bi-calendar me-1"></i>Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-value text-info" id="stat-reach">--</div>
                <div class="stat-label"><i class="bi bi-percent me-1"></i>Avg Coverage</div>
            </div>
        </div>
        <div id="map"></div>
    </div>
</div>

<div class="modal fade barangay-info-modal" id="barangayInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pin-map-fill me-2"></i><span id="modal-barangay-name">--</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-info-circle"></i>Status</div>
                    <span class="badge-info" id="modal-status-badge" style="display: inline-block; margin-bottom: 0.8rem;">--</span>
                    <div class="info-row"><span class="label">Coverage Rate</span><span class="value" id="modal-coverage">0%</span></div>
                    <div class="info-row"><span class="label">Last Distribution</span><span class="value" id="modal-last-dist">N/A</span></div>
                </div>
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-people"></i>Beneficiaries</div>
                    <div class="info-stat-grid">
                        <div class="info-stat-box"><div class="value" id="modal-total-benef">0</div><div class="label">Total Registered</div></div>
                        <div class="info-stat-box"><div class="value" id="modal-reached">0</div><div class="label">Reached</div></div>
                    </div>
                    <div class="info-row"><span class="label"><i class="bi bi-flower1 me-1"></i>Farmers</span><span class="value" id="modal-farmers">0</span></div>
                    <div class="info-row"><span class="label"><i class="bi bi-water me-1"></i>Fisherfolk</span><span class="value" id="modal-fisherfolk">0</span></div>
                    <div class="info-row"><span class="label"><i class="bi bi-shuffle me-1"></i>Both</span><span class="value" id="modal-both">0</span></div>
                </div>
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-calendar-event"></i>Distribution Events</div>
                    <div class="info-stat-grid">
                        <div class="info-stat-box"><div class="value" id="modal-total-events">0</div><div class="label">Total Events</div></div>
                        <div class="info-stat-box"><div class="value" id="modal-completed-events">0</div><div class="label">Completed</div></div>
                    </div>
                    <div class="info-row"><span class="label">Physical Events</span><span class="value" id="modal-physical">0</span></div>
                    <div class="info-row"><span class="label">Financial Events</span><span class="value" id="modal-financial">0</span></div>
                </div>
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-box-seam"></i>Allocations & Assistance</div>
                    <div class="info-stat-grid">
                        <div class="info-stat-box"><div class="value" id="modal-allocations">0</div><div class="label">Allocations</div></div>
                        <div class="info-stat-box"><div class="value" id="modal-direct-assist">0</div><div class="label">Direct Assistance</div></div>
                    </div>
                </div>
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-cash-stack"></i>Financial</div>
                    <div class="info-row"><span class="label">Fund Allocated</span><span class="value text-success" id="modal-fund-allocated">₱0</span></div>
                    <div class="info-row"><span class="label">Cash Disbursed</span><span class="value text-success" id="modal-cash-disbursed">₱0</span></div>
                </div>
                <div class="info-section">
                    <div class="info-section-title"><i class="bi bi-people-fill"></i>Registered Beneficiaries<span class="badge bg-success ms-2" id="beneficiary-count">0</span></div>
                    <div id="beneficiary-list-container" class="beneficiary-list"></div>
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

    // Street Map Layer
    const streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 16,
        name: 'Street Map'
    });

    // Satellite Map Layer
    const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri',
        maxZoom: 16,
        name: 'Satellite'
    });

    // Add Street Map by default
    streetMap.addTo(map);

    // Layer control for street/satellite toggle
    const baseLayers = {
        'Street Map': streetMap,
        'Satellite': satelliteMap
    };
    L.control.layers(baseLayers, null, { position: 'topleft', collapsed: true }).addTo(map);

    // Legend
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'map-legend');
        div.innerHTML = '<strong>Distribution Status</strong><br><span class="legend-dot" style="background: #28a745;"></span>Completed<br><span class="legend-dot" style="background: #ffc107;"></span>Ongoing<br><span class="legend-dot" style="background: #0d6efd;"></span>Pending<br><span class="legend-dot" style="background: #dc3545;"></span>No Distribution';
        return div;
    };
    legend.addTo(map);

    let markers = {};
    let barangayDataMap = {};

    async function loadMapData() {
        const params = new URLSearchParams();
        if (document.getElementById('agencyFilter').value) params.append('agency_id', document.getElementById('agencyFilter').value);
        if (document.getElementById('statusFilter').value) params.append('status', document.getElementById('statusFilter').value);
        if (document.getElementById('sectorFilter').value) params.append('sector', document.getElementById('sectorFilter').value);

        try {
            const response = await fetch(`{{ route('geo-map.data') }}?${params}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Failed to load map data');
            const result = await response.json();
            Object.values(markers).forEach(m => map.removeLayer(m));
            markers = {};
            barangayDataMap = {};

            result.data.forEach(barangay => {
                barangayDataMap[barangay.id] = barangay;
                const color = barangay.pin_color;
                const svg = `<svg viewBox="0 0 24 32" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C7.03 0 3 4.03 3 9c0 5.25 9 23 9 23s9-17.75 9-23c0-4.97-4.03-9-9-9z" fill="${color}" stroke="white" stroke-width="2"/></svg>`;
                const html = `<div class="pin-marker">${svg}<div class="pin-count">${barangay.total_beneficiaries}</div></div>`;

                const marker = L.marker([barangay.latitude, barangay.longitude], {
                    icon: L.divIcon({ html: html, iconSize: [28, 36], className: 'pin-marker-icon' })
                }).bindTooltip(barangay.name, { direction: 'top', className: 'pin-label' })
                  .on('click', () => showBarangayInfo(barangay))
                  .addTo(map);
                markers[barangay.id] = marker;
            });

            let tb = 0, te = 0, tc = 0;
            result.data.forEach(b => { tb += b.total_beneficiaries; te += b.total_events; tc += b.coverage_rate; });
            const ac = result.data.length > 0 ? Math.round(tc / result.data.length) : 0;
            document.getElementById('stat-barangays').textContent = result.data.length;
            document.getElementById('stat-beneficiaries').textContent = tb.toLocaleString();
            document.getElementById('stat-events').textContent = te;
            document.getElementById('stat-reach').textContent = ac + '%';

            if (Object.keys(markers).length > 0) {
                const group = new L.featureGroup(Object.values(markers));
                map.fitBounds(group.getBounds().pad(0.1), { maxZoom: INITIAL_ZOOM, animate: true });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function showBarangayInfo(barangay) {
        const modal = new bootstrap.Modal(document.getElementById('barangayInfoModal'));
        document.getElementById('modal-barangay-name').textContent = barangay.name;
        const sb = document.getElementById('modal-status-badge');
        sb.textContent = barangay.distribution_status.toUpperCase();
        sb.className = `badge-info ${barangay.distribution_status}`;
        document.getElementById('modal-coverage').textContent = `${barangay.coverage_rate}%`;
        document.getElementById('modal-last-dist').textContent = barangay.last_distribution_date ? new Date(barangay.last_distribution_date).toLocaleDateString('en-PH') : 'N/A';
        document.getElementById('modal-total-benef').textContent = barangay.total_beneficiaries;
        document.getElementById('modal-reached').textContent = barangay.beneficiaries_reached;
        document.getElementById('modal-farmers').textContent = barangay.total_farmers;
        document.getElementById('modal-fisherfolk').textContent = barangay.total_fisherfolk;
        document.getElementById('modal-both').textContent = barangay.total_both;
        document.getElementById('modal-total-events').textContent = barangay.total_events;
        document.getElementById('modal-completed-events').textContent = barangay.events_completed;
        document.getElementById('modal-physical').textContent = barangay.total_physical_events;
        document.getElementById('modal-financial').textContent = barangay.total_financial_events;
        document.getElementById('modal-allocations').textContent = barangay.total_allocations;
        document.getElementById('modal-direct-assist').textContent = barangay.total_direct_assistance;
        const fa = Number(barangay.total_fund_allocated || 0);
        const cd = Number(barangay.total_cash_disbursed || 0);
        document.getElementById('modal-fund-allocated').textContent = '₱' + fa.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('modal-cash-disbursed').textContent = '₱' + cd.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Load beneficiaries for this barangay
        loadBeneficiaries(barangay.id);

        modal.show();
    }

    function loadBeneficiaries(barangayId) {
        const container = document.getElementById('beneficiary-list-container');
        const countBadge = document.getElementById('beneficiary-count');

        // Show loading state
        container.innerHTML = '<div class="no-data">Loading beneficiaries...</div>';

        fetch(`/api/barangay/${barangayId}/beneficiaries`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load beneficiaries');
            return response.json();
        })
        .then(data => {
            countBadge.textContent = data.beneficiaries.length;

            if (data.beneficiaries.length === 0) {
                container.innerHTML = '<div class="no-data">No beneficiaries registered in this barangay</div>';
                return;
            }

            let html = '';
            data.beneficiaries.forEach(benef => {
                const classification = benef.classification || 'Unknown';
                const classColor = classification === 'Farmer' ? 'success' : classification === 'Fisherfolk' ? 'info' : 'warning';
                html += `
                    <div class="beneficiary-item">
                        <div class="name">${benef.full_name || benef.name || 'N/A'}</div>
                        <div class="details">
                            <span class="badge bg-${classColor}" style="font-size: 0.7rem;">${classification}</span>
                            ${benef.contact_number ? '<span class="ms-2 text-secondary">' + benef.contact_number + '</span>' : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading beneficiaries:', error);
            container.innerHTML = '<div class="no-data">Error loading beneficiaries</div>';
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
