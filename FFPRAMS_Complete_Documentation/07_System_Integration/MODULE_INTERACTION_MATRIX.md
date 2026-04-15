# MODULE INTERACTION MATRIX
## Dependencies, Data Flows, and Integration Points

**Document Type**: Integration Reference
**Date**: 2026-04-15
**Status**: Complete

---

## 📊 MODULE CROSS-REFERENCE TABLE

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                         MODULE INTERACTION MATRIX                                 │
└──────────────────────────────────────────────────────────────────────────────────┘

MODULE                    INPUT FROM          OUTPUT TO           PRIMARY PURPOSE
─────────────────────────────────────────────────────────────────────────────────
MODULE 1                  External Users      Module 2            Register & manage
Beneficiary               External Storage    Module 3            beneficiary data
Management                                    Analytics

MODULE 2                  Module 1             Module 3            Allocate &
Resource Allocation       Beneficiaries       Geo-Mapping         distribute
& Distribution            External APIs       Analytics           resources

MODULE 3                  Module 1             Manager             Generate maps,
Geo-Mapping &             Module 2             Analytics           dashboards,
Visualization             Database            Reporting           reports

CROSS-CUTTING             All Modules         All Modules         Logging,
Audit & Compliance        D6 (audit_logs)     Access control      security
```

---

## 🔀 DETAILED DATA FLOW MATRIX

### From Module 1 (Beneficiary) → Module 2 (Resources)

| Data Element | Source Table | Destination Table | Usage | Volume |
|--------------|--------------|-------------------|-------|--------|
| beneficiary_id | D1 | D17 | Allocation records | Every allocation |
| barangay_id | D1 | D20 (via event) | Event location | Event creation |
| classification | D1 | Eligibility check | Resource type match | On allocation |
| contact_number | D1 | D22 | Distribution contact | Distribution execution |
| status | D1 | Validation | Check if active | Before allocation |
| full_name | D1 | D22 (logs) | Documentation | Distribution log |
| civil_status | D1 | Reports | Demographic data | Analytics |

**Frequency**: Real-time (on-demand lookups)
**Volume**: 50,000+ allocations/year × beneficiary lookups
**Latency Requirement**: < 100ms

---

### From Module 2 (Resources) → Module 3 (Geo-Mapping)

| Data Element | Source Table | Destination Table | Usage | Volume |
|--------------|--------------|-------------------|-------|--------|
| event_id | D20 | D28 (heatmap) | Distribution event marker | Monthly computation |
| distribution_date | D22 | D28 | Timeline for heatmap | Timeline filter |
| allocation_count | D17 | D30 (analytics) | Metric computation | Daily cache update |
| beneficiary_id | D17 | D26 (via D1) | Point location | Map rendering |
| quantity | D17 | D28 | Allocation intensity | Heatmap density |
| event_location | D21 | D27 | Venue marker | Map display |
| resource_type | D19 | Legend | Layer categorization | Map layer |
| photos | D23 | D28 | Event documentation | Map popup |

**Frequency**: Real-time (map) + daily (analytics cache)
**Volume**: 1,000+ events/year × map renders
**Latency Requirement**: < 500ms for map, < 1s for dashboard

---

### From Module 1 (Beneficiary) → Module 3 (Geo-Mapping)

| Data Element | Source Table | Destination Table | Usage | Volume |
|--------------|--------------|-------------------|-------|--------|
| latitude | D26 (from D1) | D28, D29 | Map point | Point rendering |
| longitude | D26 (from D1) | D28, D29 | Map point | Point rendering |
| full_name | D1 | Popup | Beneficiary info | On click |
| classification | D1 | Icon type | Visual categorization | Map display |
| barangay_id | D1 | Boundary | Region aggregation | Heatmap cell |
| id (count) | D1 | D30 | Density metric | Analytics |

**Frequency**: Real-time (map) + daily (analytics)
**Volume**: 10,000+ points on map
**Latency Requirement**: < 300ms for initial load

---

## 🔗 PROCESS-LEVEL DEPENDENCIES

### How Module 2 Depends on Module 1

```
ALLOCATION PROCESS (Module 2):

P2.2: Allocate Resources
  ├─→ Fetch beneficiaries (Module 1, from D1)
  │   └─ Query: Barangay = event.barangay, Status = active
  │
  ├─→ Validate selections
  │   └─ Check beneficiary exists and is eligible
  │
  ├─→ Determine allocation quantity per beneficiary
  │   └─ Based on classification (Farmer/Fisherfolk/DAR)
  │
  └─→ Create allocation records (Module 2, D17)
      └─ Link beneficiary_id → allocation

DEPENDENCY: Cannot allocate if beneficiary doesn't exist in D1
```

### How Module 3 Depends on Module 1

```
HEATMAP GENERATION (Module 3):

P3.5: Generate Heatmaps
  ├─→ Fetch beneficiary locations (Module 1, from D26)
  │   └─ Query: WHERE barangay_id = target
  │
  ├─→ Extract lat/lon coordinates
  │   └─ Convert to grid cells
  │
  ├─→ Compute kernel density (Gaussian)
  │   └─ Points per cell × smoothing
  │
  └─→ Store heatmap (Module 3, D28)
      └─ Density score per grid cell

DEPENDENCY: Heatmap quality depends on accuracy of D26 coordinates
```

### How Module 3 Depends on Module 2

```
ANALYTICS DASHBOARD (Module 3):

P3.3: Generate Analytics Dashboard
  ├─→ Fetch allocation metrics (Module 2, from D17)
  │   └─ Query: WHERE status = 'Distributed'
  │
  ├─→ Aggregate by barangay
  │   └─ Count allocations, sum resources
  │
  ├─→ Fetch event locations (Module 2, from D21)
  │   └─ Map event venues as markers
  │
  ├─→ Compute coverage percentage
  │   └─ (Beneficiaries served / Total beneficiaries) × 100
  │
  └─→ Cache analytics (Module 3, D30)
      └─ Pre-computed for dashboard

DEPENDENCY: Dashboard metrics depend on allocation data accuracy
```

---

## 🔄 CIRCULAR REFERENCE HANDLING

### Module 1 → Module 2 → Module 3 → Module 1 Cycle

```
SCENARIO: "Update beneficiary's status affects everything"

Trigger: Admin sets beneficiary status = 'inactive'

Wave 1 - Module 1 Effect:
  ├─ Update D1.status = 'inactive'
  ├─ Add to audit log D6
  └─ Send soft notification

Wave 2 - Module 2 Effect:
  ├─ Query: Existing allocations where this beneficiary
  ├─ Decision: Leave as-is (allocation already made)
  ├─ Or flag for adjustment if not yet distributed
  └─ Update D17.status = 'inactive' (optional)

Wave 3 - Module 3 Effect:
  ├─ Query: Remove from current heatmap calculations
  ├─ Recompute D28 (heatmaps)
  ├─ Recompute D30 (analytics)
  └─ Trigger dashboard refresh

Wave 4 - Resolution:
  ├─ Module 1: No further action
  ├─ Module 2: Allocations remain for history
  └─ Module 3: Reflects updated analytics

Resolution Rule:
  • Data ownership: Module 1 owns beneficiary records
  • Dependent modules: Query, don't duplicate
  • Analytics update: Trigger after state change
```

---

## 📋 DATA CONSISTENCY RULES

### Transactional Integrity

```
Rule 1: Allocation Creation (Module 2)
──────────────────────────────────────
Required Pre-conditions:
  ✓ Beneficiary exists in D1
  ✓ Event exists in D20
  ✓ Resource exists in D19
  ✓ Sufficient stock available
  ✓ Budget allows allocation

Transaction:
  1. Lock D1 record (beneficiary)
  2. Lock D19 record (resource)
  3. INSERT D17 (allocation)
  4. UPDATE D19.stock (decrement)
  5. INSERT D22 (resource log)
  6. INSERT D6 (audit log)
  7. Commit or Rollback entire transaction

Post-condition:
  ✓ Allocation recorded
  ✓ Stock updated
  ✓ Audit trail created
```

### Cache Invalidation Rules

```
Rule 2: Analytics Cache Invalidation (Module 3)
──────────────────────────────────────────────
Trigger Events:
  • Allocation status changes (Module 2)
  • New distribution recorded (Module 2)
  • Beneficiary added/removed (Module 1)
  • Event date modified (Module 2)

Action:
  1. Identify affected barangay/period
  2. Mark cache entry as stale
  3. Queue recomputation
  4. Recompute within 1 hour
     (or immediately if real-time dashboard active)

Affected Caches:
  • D30: analytics_cache (daily refresh)
  • D28: heatmap_data (periodic refresh)
  • D29: dashboard_configs (invalidate dependent views)
```

---

## 🔗 EXTERNAL INTEGRATIONS

### Module 1 External: Geocoding API

```
Trigger: Beneficiary address provided
Process:
  1. Module 1 sends address to Geocoding Service
  2. Service returns lat/lon
  3. Module 1 stores in D26 (beneficiary_locations)
  4. Module 3 uses D26 for map points

Dependency: Module 3 depends on accurate geocoding
Monitor: Geocoding accuracy rate, API availability
Fallback: Manual coordinate entry by user
```

### Module 2 External: Budget Approval Service

```
Trigger: Event budget exceeds threshold
Process:
  1. Module 2 validates budget (P2.2.5)
  2. If > threshold, sends approval request
  3. External service (or manager approval)
  4. Module 2 receives approval/rejection
  5. Continues or aborts allocation

Dependency: Module 2 cannot proceed without approval
Monitor: Approval response time, approval rate
Fallback: Default approval for small budgets
```

### Module 3 External: Map Tile Server

```
Trigger: User loads map view
Process:
  1. Module 3 requests barangay boundary
  2. Tile server provides GeoJSON
  3. Module 3 renders base map
  4. Adds beneficiary points, heatmap overlay

Dependency: Map display depends on tile availability
Monitor: Tile server latency, availability
Fallback: Simplified map view or static image
```

---

## 📊 DEPENDENCY GRAPH

### Module Dependency Levels

```
LEVEL 0 (Core):
  └─ Module 1 (Beneficiary)
     • Independent, no Module dependencies
     • Only depends on external data input

LEVEL 1 (Primary):
  └─ Module 2 (Resources)
     └─ Depends on: D1 (Module 1 beneficiaries)
     └─ Supplies: D17 allocation records

LEVEL 2 (Secondary):
  └─ Module 3 (Geo-Mapping)
     └─ Depends on: D1 (Module 1 locations)
     └─ Depends on: D17 (Module 2 allocations)
     └─ Supplies: D28, D29, D30 analytics
```

### Operation Sequence Constraints

```
Constraint 1: Cannot allocate without beneficiaries
  Module 1 → (required) → Module 2

Constraint 2: Cannot generate analytics without allocations
  Module 2 → (required) → Module 3

Constraint 3: Cannot update allocation without beneficiary
  Module 1 (delete) → Cascades to Module 2 (handle carefully)

Constraint 4: Cannot have stale analytics
  Module 2 (change) → Triggers Module 3 (cache invalidation)
```

---

## 🔀 DATA FLOW TIMING

### Real-Time Paths (< 1 second)

```
Module 1 → Register Beneficiary
  ├─ P1.1: Create Beneficiary → D1 (100ms)
  ├─ P1.7: Create Agency Link → D10 (50ms)
  └─ P1.8: Audit Log → D6 (50ms)
  Total: ~200ms ✓ Acceptable

Module 2 → Allocate Resources
  ├─ P2.2: Validate & Insert → D17 (200ms)
  ├─ P2.2: Update Stock → D19 (100ms)
  └─ P2.2: Log Transaction → D22 (50ms)
  Total: ~350ms ✓ Acceptable
```

### Batch Processing Paths (Async, 1-24 hours)

```
Module 3 → Compute Analytics (Nightly)
  ├─ Query D1, D17, D20 (100,000+ rows) → 2 min
  ├─ Compute percentiles → 1 min
  ├─ Generate heatmaps (grid calculation) → 3 min
  ├─ Store D28, D30 → 1 min
  └─ Invalidate cache → 10s
  Total: ~7 min ✓ Acceptable (batched nightly)
```

### Report Generation Paths (On-Demand, 30-60 seconds)

```
Module 3 → Generate Report (User Request)
  ├─ Query analytics_cache D30 → 100ms
  ├─ Format tables & charts → 5 sec
  ├─ Generate PDF/Excel → 10 sec
  ├─ Return to user → 1 sec
  Total: ~16 sec ✓ Acceptable (within web timeout)
```

---

## ✅ INTEGRATION TESTING CHECKLIST

### Cross-Module Tests

- [ ] **Create Beneficiary → Search in Module 2**
  - Action: Register beneficiary (Module 1)
  - Result: Searchable in allocation form (Module 2)
  - Expected: <1 sec availability

- [ ] **Allocate → Appears in Module 3 Dashboard**
  - Action: Create allocation (Module 2)
  - Result: Updates analytics (Module 3)
  - Expected: <5 min cache update

- [ ] **Update Beneficiary → Heatmap Recalculation**
  - Action: Add location (Module 1)
  - Result: Heatmap includes new point (Module 3)
  - Expected: <1 hour recalculation

- [ ] **Distribute → Receipt Verification**
  - Action: Record distribution (Module 2)
  - Result: Status changes to 'Verified' (Module 2)
  - Expected: Real-time

- [ ] **Generate Report with All Data**
  - Action: Export analytics (Module 3)
  - Result: Includes beneficiary + allocation + location data
  - Expected: < 60 sec

---

## 📋 DOCUMENT METADATA

- **Version**: 1.0
- **Date**: 2026-04-15
- **Status**: Complete
- **Reference**: SYSTEM_ARCHITECTURE.md
