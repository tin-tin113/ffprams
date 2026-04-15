# MODULE 3: GEO-MAPPING & BENEFICIARY VISUALIZATION
## System Architecture & Data Flow Diagrams

**Current Date**: 2026-04-15
**Status**: Based on actual implemented features
**Geographic Scope**: E.B. Magalona, Negros Occidental

---

## EXECUTIVE SUMMARY

The Geo-Mapping & Beneficiary Visualization module provides an interactive map-based interface for visualizing beneficiary distribution and resource allocation across barangays in E.B. Magalona. The system displays real-time data about beneficiaries, distribution events, allocations, and direct assistance aggregated by barangay location.

**Key Capabilities:**
- Interactive Leaflet map with Street/Satellite layer support
- Barangay-level data visualization with customizable filters
- Detailed beneficiary information and distribution statistics
- Real-time aggregated metrics and coverage analysis

---

## LEVEL 0: SYSTEM CONTEXT DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                    EXTERNAL ENTITIES                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  [User/Admin]          [Database]         [Map Providers]   │
│       │                    │                    │            │
│       │ 1. View map        │ 2. Query data      │            │
│       ├──────────────────→ │ ← Query results    │            │
│       │                    │                    │            │
│       │ 3. Filter/Search   │ 5. Tile/Layer data │◄──────┐   │
│       ├──────────────────→ │ ────────────────→  │        │   │
│       │                    │                    │ (OSM,   │   │
│       │ 4. View Details    │ 6. Barangay list   │ Esri)   │   │
│       │ & Beneficiaries    │ & aggregates       │        │   │
│       ↓                    ↓                    ↓        │   │
│  ┌─────────────────────────────────────┐              │   │
│  │  GEO-MAPPING SYSTEM                 │──────────────┘   │
│  │  (Interactive Map Visualization)    │                   │
│  └─────────────────────────────────────┘                   │
│       ↑                                                      │
│       └──────────── Display Results ─────────────────────→  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

**System Boundary**: Geo-Mapping & Visualization System
- Maps beneficiary and resource data to geographic locations
- Filters and aggregates data by agency, distribution status, beneficiary type
- Displays interactive visualization with drill-down capabilities

---

## LEVEL 1: MAIN PROCESSES

```
┌──────────────────────────────────────────────────────────────────────┐
│                    GEO-MAPPING SYSTEM                                │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────┐  │
│  │ 1.0                │  │ 2.0                │  │ 3.0            │  │
│  │ Load & Display     │  │ Process Filters    │  │ Fetch Detail   │  │
│  │ Interactive Map    │  │ & Aggregation      │  │ Beneficiary    │  │
│  │                    │  │                    │  │ Information    │  │
│  │ - Initialize Leaflet│  │ - Agency filter   │  │                │  │
│  │ - Add base layers   │  │ - Status filter    │  │ - Query DB     │  │
│  │ - Add markers       │  │ - Type filter      │  │ - Format list  │  │
│  │ - Add legend        │  │ - Cache data       │  │ - Return data  │  │
│  │                    │  │                    │  │                │  │
│  └────────────────────┘  └────────────────────┘  └────────────────┘  │
│         ↑                         ↑                      │            │
│         │                         │                      │            │
│         │ D2: Barangays           │ D1: Beneficiaries    │            │
│         │ D3: Distribution Events │ D4: Allocations      │            │
│         │ D6: Agencies            │ D9: Direct Assist.   │            │
│         │                         │                      │            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## LEVEL 2: PROCESS DECOMPOSITION

### 1.0 - Load & Display Interactive Map

```
┌─────────────────────────────────────────────────┐
│ 1.0 Load & Display Interactive Map              │
├─────────────────────────────────────────────────┤
│                                                 │
│ 1.1 Initialize Map Environment                 │
│  ├─ Set geographic bounds (E.B. Magalona)      │
│  ├─ Configure zoom levels (11-16)              │
│  └─ Set map center [10.8300, 123.0550]         │
│                                                 │
│ 1.2 Add Base Map Layers                        │
│  ├─ Load Street Map (OpenStreetMap)            │
│  └─ Load Satellite Map (Esri World Imagery)    │
│                                                 │
│ 1.3 Fetch Barangay Data                        │
│  ├─ Query: barangays (filtered by municipality)│
│  ├─ Fetch: name, latitude, longitude           │
│  └─ Join: Join with beneficiaries, events      │
│                                                 │
│ 1.4 Aggregate Metrics per Barangay             │
│  ├─ Count total beneficiaries                  │
│  ├─ Count farmers, fisherfolk, both            │
│  ├─ Count distribution events (by status)      │
│  ├─ Sum fund allocated & cash disbursed        │
│  └─ Calculate coverage rate (%)                │
│                                                 │
│ 1.5 Create Pin Markers                         │
│  ├─ Color-coded by distribution status:        │
│  │  • Green (#28a745) = Completed              │
│  │  • Yellow (#ffc107) = Ongoing               │
│  │  • Blue (#0d6efd) = Pending                 │
│  │  • Red (#dc3545) = No Distribution          │
│  ├─ Display beneficiary count on pin           │
│  └─ Attach tooltip with barangay name          │
│                                                 │
│ 1.6 Add Legend & Controls                      │
│  ├─ Add status legend (bottom-right)           │
│  └─ Add layer switcher (Street/Satellite)      │
│                                                 │
│ 1.7 Render Map View                            │
│  └─ Display interactive map with all layers    │
│                                                 │
└─────────────────────────────────────────────────┘
```

### 2.0 - Process Filters & Aggregation

```
┌─────────────────────────────────────────────────┐
│ 2.0 Process Filters & Data Aggregation          │
├─────────────────────────────────────────────────┤
│                                                 │
│ 2.1 Read User Filters                          │
│  ├─ agency_id (optional)                       │
│  ├─ status: completed, ongoing, pending, none  │
│  └─ sector: farmer, fisherfolk, both           │
│                                                 │
│ 2.2 Generate Cache Key                         │
│  ├─ Key = agency_id + filters                  │
│  └─ Check cache (TTL: based on system config)  │
│                                                 │
│ 2.3 Query Beneficiaries (if not cached)       │
│  ├─ Filter by agency (if selected)             │
│  ├─ Join beneficiary_agencies (multi-agency)   │
│  ├─ Exclude deleted & inactive beneficiaries   │
│  └─ Group by barangay                          │
│                                                 │
│ 2.4 Query Distribution Events                  │
│  ├─ Join with program_names                    │
│  ├─ Filter by agency & program (if selected)   │
│  ├─ Count by status: completed, ongoing,       │
│  │  pending, physical, financial               │
│  └─ Group by barangay                          │
│                                                 │
│ 2.5 Query Allocations                          │
│  ├─ Count total allocations                    │
│  ├─ Count distributed vs pending               │
│  ├─ Count beneficiaries reached                │
│  └─ Group by barangay                          │
│                                                 │
│ 2.6 Query Direct Assistance (D9)               │
│  ├─ Count by status: planned, ready for        │
│  │  release, released, not_received            │
│  └─ Group by barangay                          │
│                                                 │
│ 2.7 Query Financial Summaries                  │
│  ├─ Total fund allocated (financial events)    │
│  ├─ Total cash disbursed (allocations)         │
│  └─ Group by barangay                          │
│                                                 │
│ 2.8 Query Resource Types Distributed           │
│  ├─ GROUP_CONCAT resource names                │
│  └─ Group by barangay                          │
│                                                 │
│ 2.9 Calculate Composite Metrics                │
│  ├─ Coverage rate = (beneficiaries_reached /   │
│  │   total_beneficiaries) * 100                │
│  ├─ Determine pin color by status priority     │
│  └─ Format currency & date values              │
│                                                 │
│ 2.10 Cache Results                             │
│  ├─ Store aggregated data in cache             │
│  └─ Set TTL for cache expiration               │
│                                                 │
└─────────────────────────────────────────────────┘
```

### 3.0 - Fetch Detailed Beneficiary Information

```
┌─────────────────────────────────────────────────┐
│ 3.0 Fetch Detailed Beneficiary Info             │
├─────────────────────────────────────────────────┤
│                                                 │
│ 3.1 Receive Barangay ID from UI                │
│  └─ User clicked on map marker                 │
│                                                 │
│ 3.2 Query Beneficiaries                        │
│  ├─ Filter: barangay_id = requested ID         │
│  ├─ Filter: status = 'Active'                  │
│  ├─ Exclude: deleted_at IS NOT NULL            │
│  ├─ Select: id, full_name, classification,     │
│  │           contact_number, agency_id         │
│  └─ Order by: full_name ASC                    │
│                                                 │
│ 3.3 Format Response                            │
│  ├─ Map to standard format:                    │
│  │  • id, name, full_name                      │
│  │  • classification (Farmer/Fisherfolk/Both)  │
│  │  • contact_number                           │
│  │  • agency_id                                │
│  └─ Return count + beneficiary list            │
│                                                 │
│ 3.4 Return JSON Response                       │
│  ├─ beneficiaries: array of beneficiary objs   │
│  └─ count: total beneficiaries                 │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## LEVEL 3: DETAILED PROCESS SPECIFICATIONS

### Summary Statistics Calculation

```
┌───────────────────────────────────┐
│ Calculate Summary Metrics          │
├───────────────────────────────────┤
│                                   │
│ Iteration over result.data:       │
│                                   │
│ 1. Count Barangays                │
│    Result = result.data.length    │
│                                   │
│ 2. Sum Beneficiaries              │
│    Sum all: total_beneficiaries   │
│                                   │
│ 3. Sum Distribution Events        │
│    Sum all: total_events          │
│                                   │
│ 4. Calculate Avg Coverage         │
│    FOR each barangay:             │
│      total_coverage += coverage%  │
│    END FOR                        │
│    avg_coverage = total_coverage/ │
│                  barangay_count   │
│    avg_coverage = ROUND(%)        │
│                                   │
│ 5. Update UI Stat Cards           │
│    - Barangays count              │
│    - Beneficiaries count          │
│    - Events count                 │
│    - Avg Coverage %               │
│                                   │
└───────────────────────────────────┘
```

### Pin Color Logic

```
┌─────────────────────────────────────┐
│ Determine Pin Color & Status         │
├─────────────────────────────────────┤
│                                     │
│ IF has_completed = 1                │
│   status = 'completed'              │
│   pin_color = '#28a745' (Green)     │
│                                     │
│ ELSE IF has_ongoing = 1             │
│   status = 'ongoing'                │
│   pin_color = '#ffc107' (Yellow)    │
│                                     │
│ ELSE IF has_pending = 1             │
│   status = 'pending'                │
│   pin_color = '#0d6efd' (Blue)      │
│                                     │
│ ELSE                                │
│   status = 'none'                   │
│   pin_color = '#dc3545' (Red)       │
│                                     │
│ END IF                              │
│                                     │
└─────────────────────────────────────┘
```

---

## DATA STORES

| Store | Purpose | Key Fields |
|-------|---------|-----------|
| **D1: Beneficiaries** | Main beneficiary records | id, full_name, classification, barangay_id, agency_id, status, contact_number |
| **D2: Barangays** | Geographic location data | id, name, municipality, province, latitude, longitude |
| **D3: Distribution Events** | Resource distribution records | id, barangay_id, program_name_id, status (Completed/Ongoing/Pending), type (physical/financial), total_fund_amount, resource_type_id, distribution_date |
| **D4: Allocations** | Individual allocation records | id, distribution_event_id, beneficiary_id, amount, distributed_at |
| **D6: Agencies** | Agency information | id, name, acronym, active |
| **D8: Program Names** | Program information | id, agency_id, name, active |
| **D9: Direct Assistance** | Direct assistance records | id, beneficiary_id, program_name_id, status (planned/recorded/ready_for_release/distributed/released/completed/not_received) |

---

## SYSTEM FEATURES & ROUTES

| Feature | Route | Method | Parameters | Purpose |
|---------|-------|--------|-----------|---------|
| Display Map | `/geo-map` | GET | None | Render interactive map page with filters |
| Load Map Data | `/geo-map/data` | GET | agency_id, program_name_id | Fetch aggregated barangay data |
| Get Beneficiaries | `/api/barangay/{id}/beneficiaries` | GET | barangay_id | Fetch beneficiary list for modal |

---

## USER INTERACTIONS

### 1. Load Geo-Map Page
- User navigates to `/geo-map`
- System fetches agencies and programs
- Page renders with Leaflet map
- Initial data loads with all filters blank

### 2. Filter by Agency
- User selects agency from dropdown
- System queries data for that agency only
- Markers update to show filtered results
- Statistics recalculate

### 3. Filter by Distribution Status
- User selects status (Completed/Ongoing/Pending/None)
- Markers refresh to show only barangays with that status
- Multi-status filters possible with reset

### 4. Filter by Beneficiary Type
- User selects sector: Farmer, Fisherfolk, or Both
- Shows beneficiaries matching that classification
- Events involving those beneficiary types displayed

### 5. View Barangay Details
- User clicks on map marker
- Modal opens showing:
  - Barangay name & distribution status
  - Beneficiary breakdown (Total, Farmers, Fisherfolk, Both)
  - Distribution event stats (counts by status & type)
  - Allocations & direct assistance counts
  - Financial summaries (allocated funds, cash disbursed)
  - List of all beneficiaries in that barangay

### 6. Toggle Map Layers
- User clicks layer control (top-left)
- Can switch between Street Map and Satellite
- Maintains zoom level and filters

### 7. Reset Filters
- User clicks "Reset" button
- All filters cleared (shows all agencies, all statuses, all types)
- Map markers reload with full dataset

---

## TECHNICAL SPECIFICATIONS

### Map Configuration
- **Library**: Leaflet.js
- **Base Layers**:
  - OpenStreetMap (street view)
  - Esri World Imagery (satellite)
- **Geographic Bounds**: E.B. Magalona, Negros Occidental
  - SW: [10.740, 122.935]
  - NE: [10.920, 123.175]
- **Map Center**: [10.8300, 123.0550]
- **Zoom Levels**:
  - Initial: 12
  - Min: 11
  - Max: 16

### Marker Display
- **Type**: Custom SVG pin icons
- **Size**: 28x36 pixels
- **Color-Coded** by distribution status
- **Badge**: Shows total beneficiary count

### Modal Information Display
- **Sections**:
  1. Status & Coverage
  2. Beneficiary breakdown
  3. Distribution events
  4. Allocations & assistance
  5. Financial summary
  6. Beneficiary list

---

## CACHING STRATEGY

- **Cache Key**: Built from agency_id + filter parameters
- **TTL**: Configured in GeoMapCache system
- **Purpose**: Reduce database queries, improve map load performance
- **Invalidation**: Manual refresh reloads fresh data

---

## GEOGRAPHIC SCOPE

**Fixed Municipality**: E.B. Magalona, Negros Occidental
- All data queries scoped to this municipality
- Barangays: All barangays in E.B. Magalona municipality
- Beneficiaries: Registered in barangays within this municipality
- Events: Distribution events in this municipality

---

## PERFORMANCE CONSIDERATIONS

1. **Query Optimization**: Uses eager loading with appropriate JOINs
2. **Aggregation**: SQL-level GROUP BY and SUM for efficiency
3. **Caching**: Map data cached based on filters
4. **Pagination**: Not required (barangay-level data only)
5. **Responsive**: Mobile-friendly map with adaptive heights

---

## ERROR HANDLING

| Scenario | Response |
|----------|----------|
| Invalid agency_id | Returns empty results or error |
| Database connection failure | JSON error: "Failed to load geo-map data" |
| Beneficiary query timeout | Modal shows loading state, then error |
| Missing barangay coordinates | Marker still created, may display off-map |

---

## SYSTEM NOTES

- **Real-Time**: Data reflects current database state
- **Static Scope**: Geographic focus limited to E.B. Magalona
- **Non-Hierarchical**: No multi-level regional views
- **No Heatmaps**: System displays markers, not heat/density visualizations
- **No Advanced Analytics**: Provides basic aggregation and filtering
- **UI-Driven Filtering**: All filtering controlled by user dropdown/button selections

---

**Status**: Reflects actual implemented features in FFPRAMS as of 2026-04-15
