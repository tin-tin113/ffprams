# MODULE 3: GEO-MAPPING & BENEFICIARY VISUALIZATION - DATA FLOW DIAGRAM

**Document**: DFD Specifications (Revised - Core Elements Only)
**Module**: Geo-Mapping & Visualization System
**Date**: 2026-04-15

---

## DFD LEVEL 0 - SYSTEM CONTEXT

### External Entities
- **E1**: User/Admin (system user)
- **E2**: Map Tile Providers (OpenStreetMap, Esri)
- **E3**: Database System

### System Boundary: FFPRAMS Geo-Mapping & Visualization

**Geographic Scope**: E.B. Magalona, Negros Occidental (municipality-limited)
**Coordinate System**: WGS84 (latitude/longitude)

**Main Functions:**
- P0: Display interactive map with beneficiary/resource data
- Filter and aggregate data by criteria
- Fetch detailed beneficiary information by location

### Level 0 Data Flows

| Flow | From | To | Data |
|------|------|-----|------|
| 1 | User | System | Map view request, filter selections |
| 2 | System | Map Providers | Tile requests (Street/Satellite layers) |
| 3 | Map Providers | System | Map tiles and layer data |
| 4 | System | Database | Query for barangay, beneficiary, event, allocation data |
| 5 | Database | System | Aggregated results (counts, metrics) |
| 6 | System | User | Rendered map with markers, popups, detail views |

---

## DFD LEVEL 1 - MAIN PROCESSES

Three main processes:

| Process | Name | Function |
|---------|------|----------|
| **P1** | Load & Display Interactive Map | Initialize Leaflet map with base layers and markers |
| **P2** | Process Filters & Aggregation | Filter data by agency/status/sector and compute metrics |
| **P3** | Fetch Detailed Beneficiary Info | Query beneficiaries for selected barangay |

### Process Specifications - Level 1

#### **P1: LOAD & DISPLAY INTERACTIVE MAP**
- **Input**: Page load request
- **Processing**: Initialize Leaflet map → Load base layers (Street/Satellite) → Fetch barangay data → Aggregate initial metrics → Create pin markers → Render map
- **Output**: Interactive map with color-coded pins showing distribution status
- **Data Stores**: D2 (barangays), D1 (beneficiaries), D3 (distribution events), D6 (agencies)

#### **P2: PROCESS FILTERS & AGGREGATION**
- **Input**: User filter selections (agency_id, status, sector type)
- **Processing**: Generate cache key → Check cache → Query beneficiaries → Query events → Query allocations → Query direct_assistance → Calculate metrics → Cache results
- **Output**: Filtered and aggregated data for all barangays
- **Data Stores**: D1, D3, D4 (allocations), D5 (direct_assistance), D6

#### **P3: FETCH DETAILED BENEFICIARY INFO**
- **Input**: Selected barangay_id (from marker click)
- **Processing**: Query beneficiaries by barangay → Filter active records → Format response
- **Output**: List of beneficiaries with classifications and details
- **Data Stores**: D1 (beneficiaries)

---

## DFD LEVEL 2 - LOAD MAP (P1) DECOMPOSITION

### Sub-processes of P1

```
P1.1 → Initialize Map Environment (set bounds, zoom, center for E.B. Magalona)
P1.2 → Add Base Map Layers (Street Map from OpenStreetMap, Satellite from Esri)
P1.3 → Fetch Barangay Data (query D2, join with beneficiary/event counts)
P1.4 → Aggregate Metrics per Barangay (count beneficiaries, events, calculate coverage %)
P1.5 → Determine Pin Color by Status (completed=green, ongoing=yellow, pending=blue, none=red)
P1.6 → Create Pin Markers (place markers at barangay coordinates, attach tooltip)
P1.7 → Add Legend & Controls (display color legend, add layer switcher)
P1.8 → Render Map View (display final interactive map)
```

### Level 2 Data Flows

| Process | Reads | Writes | Data |
|---------|-------|--------|------|
| P1.1 | - | - | Map configuration |
| P1.2 | - | - | Layer setup |
| P1.3 | D2, D1, D3 | - | Barangay data fetch with counts |
| P1.4 | - | - | Metrics calculation (in-memory) |
| P1.5 | - | - | Status determination |
| P1.6 | - | - | Marker creation |
| P1.7 | - | - | Legend/control setup |
| P1.8 | - | - | Map rendering |

---

## DFD LEVEL 2 - PROCESS FILTERS (P2) DECOMPOSITION

### Sub-processes of P2

```
P2.1 → Read User Filters (agency_id, status, sector)
P2.2 → Generate Cache Key (combine filter values)
P2.3 → Check Cache (TTL-based caching)
P2.4 → Query Beneficiaries (WHERE municipality, agency, sector filters)
P2.5 → Query Distribution Events (count by status: completed, ongoing, pending)
P2.6 → Query Allocations (count distributed vs pending allocations)
P2.7 → Query Direct Assistance (count by status: planned, ready, released, not_received)
P2.8 → Query Financial Summaries (sum allocated funds, sum disbursed cash)
P2.9 → Calculate Composite Metrics (coverage %, determine pin colors, format values)
P2.10 → Cache Results (store aggregated data with TTL)
```

### Level 2 Data Flows

| Process | Reads | Writes | Data |
|---------|-------|--------|------|
| P2.1 | - | - | Filter parsing |
| P2.2 | - | - | Cache key generation |
| P2.3 | Cache | - | Check cache |
| P2.4 | D1 | - | Beneficiary count by barangay |
| P2.5 | D3 | - | Event count by status/barangay |
| P2.6 | D4 | - | Allocation metrics by barangay |
| P2.7 | D5 | - | Direct assistance count by barangay |
| P2.8 | D4 | - | Financial summaries |
| P2.9 | - | - | Metric calculation |
| P2.10 | - | Cache | Store aggregated results |

---

## DFD LEVEL 3 - DETAILED PROCESS SPECIFICATIONS

### P1.4: AGGREGATE METRICS (Terminal Process)

**Input**: Barangay data with associated beneficiaries, events, allocations

**Calculation Steps**:
1. Count total beneficiaries BY barangay
2. Count beneficiaries BY classification (Farmer, Fisherfolk, DAR Beneficiary)
3. Count distribution events BY status (Completed, Ongoing, Pending)
4. Count allocations (total and distributed)
5. Sum allocated funds (financial assistance events)
6. Calculate coverage_rate = (beneficiaries_reached / total_beneficiaries) × 100
7. Determine pin_color based on highest-priority status (Completed > Ongoing > Pending > None)

**Output**: {barangay_id, total_beneficiaries, farmers_count, fisherfolk_count, events_completed, events_ongoing, fund_allocated, fund_disbursed, coverage_rate, pin_color, pin_status}

### P1.5: DETERMINE PIN COLOR (Terminal Process)

**Input**: Event status presence flags (has_completed, has_ongoing, has_pending)

**Color Logic**:
```
IF has_completed = 1
  → color = '#28a745' (Green)
ELSE IF has_ongoing = 1
  → color = '#ffc107' (Yellow)
ELSE IF has_pending = 1
  → color = '#0d6efd' (Blue)
ELSE
  → color = '#dc3545' (Red)
```

**Output**: {pin_color: hex_string, status_label: string}

### P2.9: CALCULATE COMPOSITE METRICS (Terminal Process)

**Input**: Query results from P2.4-P2.8

**Calculations**:
1. Total barangays = count(result rows)
2. Total beneficiaries = SUM(all.total_beneficiaries)
3. Total events = SUM(all.event_count)
4. Average coverage = ROUND(AVG(all.coverage_rate))
5. Total funds allocated = SUM(all.fund_allocated)
6. Total funds disbursed = SUM(all.fund_disbursed)

**Output**: {barangay_count, total_beneficiaries, total_events, avg_coverage_pct, total_funds_allocated, total_funds_disbursed}

---

## DATA STORES DICTIONARY

| ID | Name | Database Table | Purpose |
|----|------|---------|---------|
| **D1** | Beneficiaries | beneficiaries | Beneficiary records with barangay_id location link |
| **D2** | Barangays | barangays | Geographic locations (name, latitude, longitude, municipality) |
| **D3** | Distribution Events | distribution_events | Distribution events with barangay_id and status |
| **D4** | Allocations | allocations | Resource allocations tracking distribution quantities |
| **D5** | Direct Assistance | direct_assistance | Direct cash/assistance records with beneficiary link |
| **D6** | Agencies | agencies | Agency data for filtering distributions |

### Key Tables

**D2: barangays**
- Attributes: id (PK), name, municipality, province, latitude (WGS84), longitude (WGS84)

**D3: distribution_events**
- Attributes: id (PK), name, barangay_id (FK), program_id (FK), status (Pending/Ongoing/Completed), created_at

**D4: allocations**
- Attributes: id (PK), event_id (FK), beneficiary_id (FK), quantity, amount, distributed_at

**D5: direct_assistance**
- Attributes: id (PK), beneficiary_id (FK), program_id (FK), amount, status (planned/ready_for_release/released/completed/not_received)

---

## GEOGRAPHIC SCOPE

**Location**: E.B. Magalona, Negros Occidental
**Bounds**: Latitude 10.740° to 10.920°, Longitude 122.935° to 123.175°
**Coordinate System**: WGS84 (standard GPS)
**Map Layers**: OpenStreetMap (Street), Esri World Imagery (Satellite)
**Zoom Levels**: 11 (overview) to 16 (detail)

**Features**:
- ✅ Interactive map display (Leaflet.js)
- ✅ Barangay-level data aggregation
- ✅ Filter-based data exploration
- ✅ Modal detail views
- ✅ Layer toggle (Street/Satellite)
- ❌ No heatmaps, KDE algorithms, or advanced spatial analysis

---

## SUMMARY

**Total Levels**: 3 (Level 0-3)
**Main Processes (Level 1)**: 3
**Sub-processes (Level 2)**: 8 (P1) + 10 (P2) = 18
**Terminal Processes (Level 3)**: 3 (Aggregate Metrics, Pin Color, Composite Metrics)
**Data Stores**: 6
**External Entities**: 3

All processes align with actual GeoMapController implementation and Leaflet.js map visualization.
