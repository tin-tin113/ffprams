# FFPRAMS SYSTEM ARCHITECTURE OVERVIEW
## Integration of Three Core Modules

**Document Type**: System Architecture & Module Integration
**Date**: 2026-04-15
**Status**: Complete

---

## 📊 THREE-MODULE ARCHITECTURE

```
┌────────────────────────────────────────────────────────────────────────────┐
│                          FFPRAMS SYSTEM ARCHITECTURE                        │
│                  (Agricultural Assistance Distribution System)              │
└────────────────────────────────────────────────────────────────────────────┘

                                    ┌─────────────────────────┐
                                    │   User Interface Layer  │
                                    │   • Web Dashboard       │
                                    │   • Mobile App          │
                                    │   • Reports             │
                                    └────────────┬────────────┘
                                                 │
                    ┌────────────────────────────┼────────────────────────────┐
                    │                            │                            │
    ┌───────────────▼──────────────┐ ┌─────────▼────────────┐  ┌───────────▼──────────┐
    │   MODULE 1                   │ │   MODULE 2           │  │   MODULE 3           │
    │   BENEFICIARY MANAGEMENT     │ │   RESOURCE ALLOC &   │  │   GEO-MAPPING &      │
    │                              │ │   DISTRIBUTION       │  │   VISUALIZATION      │
    │ • Register beneficiaries     │ │                      │  │                      │
    │ • Update profiles            │ │ • Plan events        │  │ • Interactive maps   │
    │ • Manage documents           │ │ • Allocate resources │  │ • Analytics dashboards
    │ • Track history              │ │ • Distribute items   │  │ • Heatmaps           │
    │ • Maintain classifications   │ │ • Verify receipts    │  │ • Reports (PDF/Excel)│
    │                              │ │ • Cost tracking      │  │ • Coverage analysis  │
    └───────────────┬──────────────┘ └─────────┬────────────┘  └───────────┬──────────┘
                    │                          │                           │
                    │                          │                           │
    ┌───────────────▼──────────────────────────▼───────────────────────────▼──────────┐
    │                                 Data Layer                                       │
    │                                                                                  │
    │  PostgreSQL Database with Spatial Extensions                                    │
    │  ┌────────────────────────────────────────────────────────────────────────────┐│
    │  │ D1: beneficiaries          │ D17: allocations      │ D25: barangays (spatial)││
    │  │ D8: barangays              │ D19: resources        │ D26: beneficiary_locations
    │  │ D9: agencies               │ D20: distribution_evts│ D28: heatmap_data      ││
    │  │ D6: audit_logs             │ D22: resource_logs    │ D29: dashboard_configs ││
    │  │ D10: beneficiary_agencies  │ D24: verification     │ D30: analytics_cache   ││
    │  └────────────────────────────────────────────────────────────────────────────┘│
    │                                                                                  │
    │  Cache Layer (Redis/APCu)                                                      │
    │  • Dashboard configurations (5-min TTL)                                        │
    │  • Analytics computations (7-day TTL)                                          │
    │  • Map tile cache (14-day TTL)                                                 │
    └───────────────────────────────────────────────────────────────────────────────┘
                    │                          │                           │
                    │                          │                           │
    ┌───────────────▼─────────────────────────▼───────────────────────────▼──────────┐
    │                            Business Logic Layer                                 │
    │                          (Laravel Controllers & Services)                       │
    │                                                                                 │
    │ BeneficiaryController    │ AllocationController     │ GeoMappingController     │
    │ • CRUD operations        │ • Allocation CRUD        │ • Map rendering          │
    │ • Validation             │ • Budget enforcement     │ • Heatmap computation    │
    │ • Document handling      │ • Stock management       │ • Analytics compute      │
    │ • Audit logging          │ • Distribution tracking  │ • Report generation      │
    │                          │ • Receipt verification   │ • Geocoding API calls    │
    └────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 DATA FLOW BETWEEN MODULES

### Processing Pipeline Example: From Beneficiary to Geo-Visualization

```
SCENARIO: Analyzing distribution coverage by barangay

Step 1: Beneficiary Data Entered (Module 1)
        User registers beneficiary in Module 1
        ├─ Stores in D1 (beneficiaries)
        ├─ Records in D8 barangay
        └─ Logs in D6 (audit)

Step 2: Resource Allocation (Module 2)
        Program Manager allocates resources to beneficiaries
        ├─ Queries D1 (beneficiary list)
        ├─ Creates D17 (allocation records)
        ├─ Updates D19 (resource stock)
        └─ Validates against budget (D20)

Step 3: Distribution Execution (Module 2)
        Enumerator distributes resources
        ├─ Records in D22 (resource logs)
        ├─ Captures D23 (distribution photos)
        ├─ Verifies in D24 (receipt verification)
        └─ Updates allocation status

Step 4: Geo-Mapping & Analysis (Module 3)
        System generates geographic visualization
        ├─ Fetches D26 (beneficiary locations)
        ├─ Fetches D27 (distribution points)
        ├─ Computes D28 (heatmap density)
        ├─ Aggregates D30 (analytics cache)
        ├─ Renders interactive map
        └─ Dashboard shows coverage by barangay

End Result: Manager can see which areas
           received most resources and where
           beneficiary density is highest
```

---

## 📊 MODULE DEPENDENCIES MATRIX

```
                 │ Module 1    │ Module 2    │ Module 3
                 │ Beneficiary │ Resources   │ Geo-Mapping
────────────────┼─────────────┼─────────────┼──────────────
Module 1         │     -       │ PRIMARY     │ PRIMARY
(Beneficiary)    │             │ (core data) │ (location data)
────────────────┼─────────────┼─────────────┼──────────────
Module 2         │ PRIMARY     │     -       │ SECONDARY
(Resources)      │ (who gets   │             │ (event coverage)
                 │  resources) │             │
────────────────┼─────────────┼─────────────┼──────────────
Module 3         │ PRIMARY     │ PRIMARY     │     -
(Geo-Mapping)    │ (location   │ (events,    │
                 │  points)    │  allocations)
────────────────┴─────────────┴─────────────┴──────────────

KEY RELATIONSHIPS:
• Module 1 → Module 2: Beneficiary records are recipients
• Module 2 → Module 1: Allocation data links beneficiaries
• Module 1 ← Module 3: Location coordinates for mapping
• Module 2 ← Module 3: Event coverage visualization
• Module 3 → All: Cross-cutting analytics/reporting
```

---

## 🗄️ COMPLETE DATABASE SCHEMA (Data Stores D1-D32)

### Module 1 Data Stores
```
D1:  beneficiaries (10,000+ records)
D3:  classifications (config)
D6:  audit_logs (daily growth)
D7e: attachments (5,000+ records)
D8:  barangays (100+)
D9:  agencies (5-15)
D10: beneficiary_agencies (M2M, 20,000+)
```

### Module 2 Data Stores
```
D17: allocations (50,000+)
D18: allocation_beneficiaries (M2M)
D19: resources (50+)
D20: distribution_events (1,000+/year)
D21: distribution_locations (100+)
D22: resource_logs (100,000+/year)
D23: distribution_photos (10,000+/year)
D24: allocation_verification (50,000+)
```

### Module 3 Data Stores
```
D25: barangays_spatial (with GIS)
D26: beneficiary_locations (GPS points)
D27: distribution_points (venues)
D28: heatmap_data (density grid)
D29: dashboard_configs (user dashboards)
D30: analytics_cache (pre-computed)
D31: map_layers (visualization config)
D32: geocoding_log (API tracking)
```

---

## 🔌 INTEGRATION POINTS

### Module 1 ↔ Module 2 Integration

**Connection**: Beneficiary → Allocation relationship

```
Module 1 Output              Module 2 Input
────────────────────────────────────────────
Beneficiary ID      ────→    Allocation records
Barangay ID         ────→    Event filtering
Classification      ────→    Eligibility check
Contact Number      ────→    Distribution contact
Status (active)     ────→    Allocation eligibility
```

### Module 2 ↔ Module 3 Integration

**Connection**: Allocation & Event Data → Visualization

```
Module 2 Output              Module 3 Input
────────────────────────────────────────────
Allocation count    ────→    Analytics metric
Event location      ────→    Map marker
Distribution date   ────→    Timeline filter
Resource quantity   ────→    Heatmap intensity
Beneficiary count   ────→    Density calculation
```

### Module 1 ↔ Module 3 Integration

**Connection**: Beneficiary Location → Geo-Mapping

```
Module 1 Output              Module 3 Input
────────────────────────────────────────────
Lat/Lon coords      ────→    Map point data
Barangay ID         ────→    Region boundary
Classification      ────→    Point categorization
Contact number      ────→    Geocoding validation
```

---

## 📈 CROSS-CUTTING CONCERNS

### 1. Audit & Compliance
- **Coverage**: All three modules
- **Implementation**: D6 (audit_logs) captures all actions
- **Purpose**: Compliance, accountability, dispute resolution

### 2. User Permissions
- **Role-Based Access Control (RBAC)**
  - Admin: Full access to all modules
  - Manager: Create/manage programs and events
  - Enumerator: Execute distribution, record data
  - Analyst: View dashboards, export reports
  - Supervisor: Verify distributions, approve

### 3. Data Validation
- **Centralized Validation Service**
  - Module 1: Beneficiary data format & uniqueness
  - Module 2: Budget constraints, stock availability
  - Module 3: Geographic data, coordinate bounds

### 4. Reporting
- **Cross-Module Reports**
  - Beneficiary distribution by barangay (All modules)
  - Resource allocation summary (Module 2 + 3)
  - Program impact analysis (All modules)
  - Coverage vs target (Module 2 + 3)

---

## 🚀 DATA PROCESSING WORKFLOWS

### Workflow A: Complete Beneficiary Registration → Distribution → Visualization

```
Timeline: Day 1-90

DAY 1: Beneficiary Registration (Module 1)
  ├─ Admin registers 1,000 beneficiaries
  ├─ System validates and stores D1
  ├─ Creates audit trail D6
  └─ Assigns to barangays D8

DAY 15: Event Planning (Module 2)
  ├─ Program Manager creates distribution event D20
  ├─ Plans venues D21
  ├─ Allocates resources D17
  ├─ Budget validation → Success
  └─ Generates allocation list

DAY 30-45: Distribution Execution (Module 2)
  ├─ Enumerators distribute resources
  ├─ Record distribution D22
  ├─ Capture photos D23
  ├─ Beneficiaries sign receipt
  ├─ Store verification D24
  └─ Update allocation status

DAY 60: Analytics & Reporting (Module 3)
  ├─ System computes analytics D30
  ├─ Generates heatmaps D28
  ├─ Creates dashboard D29
  ├─ Manager reviews coverage
  ├─ Generates report
  └─ Identifies underserved areas

DAY 90: Impact Analysis (All Modules)
  ├─ Query beneficiary status D1
  ├─ Review distributions D17, D22
  ├─ View coverage maps Module 3
  ├─ Generate cross-module report
  └─ Plan next phase
```

---

## 📱 USER INTERACTION FLOW

### Primary Use Case: "Manager Reviews Regional Distribution Coverage"

```
Manager Login
    ↓
    ├─→ [Module 3 Dashboard]
    │   ├─ View interactive map (Module 3, P3.1)
    │   ├─ Beneficiary locations (Module 1)
    │   ├─ Distribution events (Module 2)
    │   ├─ Heatmap overlay (Module 3, P3.5)
    │   └─ Analytics sidebar (Module 3, P3.2)
    │
    ├─→ [Filter by Barangay]
    │   ├─ Beneficiary count (Module 1)
    │   ├─ Allocations made (Module 2)
    │   ├─ Coverage %
    │   └─ Density score (Module 3)
    │
    ├─→ [Drill Down to Specific Area]
    │   ├─ View individual beneficiaries (Module 1)
    │   ├─ See their allocations (Module 2)
    │   ├─ Distribution photos (Module 2, D23)
    │   └─ Receipt verification status (Module 2, D24)
    │
    └─→ [Generate Report]
        ├─ Export analytics (Module 3, P3.6)
        ├─ Include beneficiary summary (Module 1)
        ├─ Include distribution metrics (Module 2)
        └─ Export as PDF/Excel
```

---

## ⚡ PERFORMANCE CONSIDERATIONS

### Scalability Metrics

| Metric | Module 1 | Module 2 | Module 3 |
|--------|----------|----------|----------|
| Peak QPS | 100 | 50 | 200 |
| Average Response Time | 200ms | 300ms | 500ms* |
| Cache Hit Rate Target | 60% | 70% | 85% |
| Database Size | 500MB | 1GB | 200MB |
| Max Records/Table | 100K | 250K | 50K |

*Map rendering can be slower due to data volume

### Optimization Strategies

1. **Module 1: Beneficiary Management**
   - Index on: barangay_id, agency_id, status, contact_number
   - Soft deletes for archive access
   - Pagination limit: 100 records/page

2. **Module 2: Resource Allocation**
   - Index on: event_id, beneficiary_id, status, distribution_date
   - Query optimization for budget validation
   - Denormalize allocation count for dashboard

3. **Module 3: Geo-Mapping**
   - Spatial index on coordinates (GiST or BRIN)
   - Cache tiles at multiple zoom levels
   - Analytics cache with 7-day TTL
   - Heatmap pre-computation scheduled daily

---

## 🔐 SECURITY & DATA PROTECTION

### Data Classification

```
PUBLIC DATA:
• Barangay names and boundaries
• Public program information

INTERNAL DATA:
• Beneficiary records (name, classification)
• Allocation details
• Distribution photos
• Analytics aggregates

SENSITIVE DATA:
• Contact numbers (encrypted)
• Addresses (access-controlled)
• Personal identification info
• Audit logs (internal only)
```

### Access Control

```
Module 1 (Beneficiary):
  Admin: Full CRUD
  Manager: Create/Edit
  Enumerator: Read only
  Public: No access

Module 2 (Resources):
  Admin: Full CRUD
  Manager: Full CRUD
  Enumerator: Execution only
  Public: No access

Module 3 (Geo-Mapping):
  Admin: Full CRUD
  Manager: View/Export
  Analyst: View/Export
  Public: View (aggregated only)
```

---

## 📋 SYSTEM REQUIREMENTS

### Technology Stack

- **Backend**: Laravel 10.x
- **Database**: PostgreSQL 13+ with PostGIS
- **Frontend**: Vue.js 3 / React
- **Mapping**: Leaflet.js / Mapbox GL
- **Cache**: Redis 7.x
- **Authentication**: Laravel Passport (OAuth2)

### Infrastructure

- **Servers**: 2-4 application servers
- **Database**: Dedicated PostgreSQL instance
- **Storage**: S3-compatible for documents/photos
- **CDN**: Optional for static assets

---

## ✅ DEPLOYMENT CHECKLIST

- [ ] Database created with all tables (D1-D32)
- [ ] Spatial indexes on geographic columns
- [ ] Redis cache configured
- [ ] User roles and permissions defined
- [ ] API endpoints secured with OAuth2
- [ ] File upload storage configured
- [ ] Backup strategy implemented
- [ ] Monitoring and alerting setup
- [ ] Load testing completed
- [ ] Security audit passed

---

## 📞 SUPPORT & DOCUMENTATION

- **Technical Docs**: `/PROJECT_DOCUMENTATION/` folder
- **DFD Documentation**: `MODULE{1,2,3}_DFD_COMPLETE.md`
- **UML Diagrams**: Draw.io files in `UML_Diagrams/` folders
- **API Documentation**: Generated from code (Swagger/OpenAPI)
- **Database Schema**: See ERD diagrams

---

## DOCUMENT METADATA

- **Version**: 1.0
- **Date**: 2026-04-15
- **Status**: Complete
- **For**: System Architecture Reference
