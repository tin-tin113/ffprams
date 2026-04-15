# MODULE 1: BENEFICIARY MANAGEMENT - DATA FLOW DIAGRAM

**Document**: DFD Specifications (Revised - Core Elements Only)
**Module**: Beneficiary Management System
**Date**: 2026-04-15

---

## DFD LEVEL 0 - SYSTEM CONTEXT

### External Entities
- **E1**: Admin/Staff Users
- **E2**: File Storage System
- **E3**: Database Management System

### System Boundary: FFPRAMS Beneficiary Management

**Main Functions:**
- P0: Register and manage beneficiary records
- Validate input data
- Manage beneficiary documents
- Maintain audit trail

### Level 0 Data Flows

| Flow | From | To | Data |
|------|------|-----|------|
| 1 | User | System | Beneficiary forms, search queries |
| 2 | System | User | Results, confirmations, beneficiary records |
| 3 | System | File Storage | Save/retrieve documents |
| 4 | File Storage | System | File metadata, document paths |
| 5 | System | Database | INSERT/UPDATE/SELECT operations |
| 6 | Database | System | Query results, beneficiary records |

---

## DFD LEVEL 1 - MAIN PROCESSES

Six main processes:

| Process | Name | Function |
|---------|------|----------|
| **P1** | Create Beneficiary | Register new beneficiary with validation |
| **P2** | Update Beneficiary | Modify beneficiary information |
| **P3** | Search/View Beneficiary | Query and display beneficiary data |
| **P4** | Manage Documents | Upload/download beneficiary attachments |
| **P5** | Validate Data | Verify input completeness and accuracy |
| **P6** | Audit & Logging | Record all system actions for traceability |

### Process Specifications - Level 1

#### **P1: CREATE BENEFICIARY**
- **Input**: Beneficiary form data (name, age, barangay, agency, contact, sector fields)
- **Processing**: Validate input → Compute full name → Normalize phone → Insert record → Create audit log
- **Output**: beneficiary_id, success confirmation
- **Data Stores**: D1, D8, D9, D6

#### **P2: UPDATE BENEFICIARY**
- **Input**: beneficiary_id, updated field values
- **Processing**: Retrieve current record → Validate changes → Update fields → Log changes
- **Output**: Updated record confirmation
- **Data Stores**: D1, D6

#### **P3: SEARCH/VIEW BENEFICIARY**
- **Input**: Search filters (name, barangay, agency, classification, status)
- **Processing**: Query database with filters → Join related tables → Format output
- **Output**: Beneficiary list or detail view
- **Data Stores**: D1, D8, D9

#### **P4: MANAGE DOCUMENTS**
- **Input**: File upload, beneficiary_id
- **Processing**: Validate file → Store to file system → Create attachment record → Link to beneficiary
- **Output**: attachment_id, file_path
- **Data Stores**: D7, D1, D6

#### **P5: VALIDATE DATA**
- **Input**: Raw form data
- **Processing**: Check required fields → Verify formats → Verify relationships → Check constraints
- **Output**: PASS/FAIL status with error messages
- **Data Stores**: D1, D3, D8, D9

#### **P6: AUDIT & LOGGING**
- **Input**: Action details (user_id, action_type, beneficiary_id, old/new values)
- **Processing**: Create log entry → Record timestamp → Store to database
- **Output**: audit_log_id
- **Data Stores**: D6

---

## DFD LEVEL 2 - CREATE BENEFICIARY (P1) DECOMPOSITION

### Sub-processes of P1

```
P1.1 → Display Form (fetch dropdown data)
P1.2 → User Data Entry (collect form input)
P1.3 → Validate Input (detailed validation - see Level 3)
P1.4 → Compute Full Name (concatenate name fields)
P1.5 → Normalize Phone Number (format to +63)
P1.6 → Insert into Database (D1)
P1.7 → Create Agency Links (D10 - M2M relationship)
P1.8 → Create Audit Log (D6)
P1.9 → Return Success Response (JSON + redirect)
```

### Level 2 Data Flows

| Process | Reads | Writes | Data |
|---------|-------|--------|------|
| P1.1 | D8, D9, D3 | - | Dropdown options |
| P1.3 | D1, D8, D9, D3 | - | Validation checks |
| P1.6 | - | D1 | INSERT new beneficiary |
| P1.7 | - | D10 | INSERT M2M links |
| P1.8 | - | D6 | INSERT audit log |

---

## DFD LEVEL 3 - VALIDATE DATA (P1.3) DECOMPOSITION

### P1.3 Validation Steps

| Step | Validation | Error Condition | Data Store |
|------|-----------|-----------------|------------|
| P1.3.1 | Required Fields | first_name, last_name, barangay, agency, contact empty | - |
| P1.3.2 | Phone Format | Invalid PH mobile pattern or duplicate phone | D1 |
| P1.3.3 | Date of Birth | Future date or invalid age (< 15 or > 120) | - |
| P1.3.4 | Barangay Reference | Invalid barangay_id in selection | D8 |
| P1.3.5 | Agency Reference | Invalid or inactive agency_id | D9 |
| P1.3.6 | Name Characters | Special characters, numbers, or length > 100 | - |
| P1.3.7 | Sector-Specific | Missing required sector fields based on classification | D3 |
| P1.3.8 | Aggregate Result | Combine all validations, return PASS or FAIL | - |

---

## DFD LEVEL 4 - TERMINAL PROCESS

### P1.3.2: VALIDATE PHONE NUMBER (Algorithm)

**Input**: contact_number (any format)

**Steps**:
1. **Trim whitespace** - Remove leading/trailing spaces
2. **Remove formatting** - Strip (, ), -, /, ., spaces
3. **Validate regex** - Pattern: `^(\+63|0)9\d{9}$`
   - If invalid → Return ERROR: "Invalid PH mobile format"
4. **Normalize to +63 format**
   - If starts with '0' → Replace with '+63'
   - Else keep as is (+63 already present)
5. **Check duplicate** - Query D1 WHERE contact_number = value
   - If found → Return ERROR: "Phone already registered"
6. **Return success** - Return normalized phone in +63 format

**Output**: `{status: SUCCESS|FAIL, value: normalized_phone, error_message: string}`

---

## DATA STORES DICTIONARY

| ID | Name | Database Table | Purpose |
|----|----|---------|---------|
| **D1** | Beneficiaries | beneficiaries | Core beneficiary records with all demographics |
| **D3** | Classifications | Configuration/Enum | Beneficiary types (Farmer, Fisherfolk, DAR) and sector fields |
| **D6** | Audit Logs | audit_logs | System activity tracking (user, action, old/new values) |
| **D7** | Attachments | beneficiary_attachments | Document metadata and file references |
| **D8** | Barangays | barangays | Geographic locations and administrative boundaries |
| **D9** | Agencies | agencies | Implementing agencies and organization data |
| **D10** | Beneficiary-Agency | beneficiary_agencies | Many-to-many relationship (beneficiary can have multiple agencies) |

### Key Tables

**D1: beneficiaries**
- Primary key: id
- Attributes: first_name, middle_name, last_name, full_name, sex, date_of_birth, age, barangay_id (FK), agency_id (FK), contact_number (UNIQUE), classification, sector-specific fields, status, timestamps

**D6: audit_logs**
- Primary key: id
- Attributes: user_id (FK), beneficiary_id (FK), action, old_values (JSON), new_values (JSON), timestamp

---

## SUMMARY

**Total Levels**: 4 (Level 0-4)
**Main Processes (Level 1)**: 6
**Sub-processes (Level 2)**: 9 (P1 decomposition)
**Validation Steps (Level 3)**: 8
**Terminal Processes (Level 4)**: 1 (Phone validation algorithm)
**Data Stores**: 7
**External Entities**: 3

All processes are traceable to actual system implementation in BeneficiaryController and supporting services.


---


# MODULE 2: RESOURCE ALLOCATION & DISTRIBUTION - DATA FLOW DIAGRAM

**Document**: DFD Specifications (Revised - Core Elements Only)
**Module**: Resource Allocation & Distribution System
**Date**: 2026-04-15

---

## DFD LEVEL 0 - SYSTEM CONTEXT

### External Entities
- **E1**: Program Manager/Supervisor (user)
- **E2**: File Storage (photos, documents)
- **E3**: Database System

### System Boundary: FFPRAMS Resource Allocation & Distribution

**Main Functions:**
- P0: Plan distribution events
- Allocate resources to beneficiaries
- Execute and verify distribution
- Generate reports and audit records

### Level 0 Data Flows

| Flow | From | To | Data |
|------|------|-----|------|
| 1 | User | System | Event creation, allocation data, distribution instructions |
| 2 | System | User | Results, confirmations, reports |
| 3 | System | File Storage | Save distribution photos and documents |
| 4 | File Storage | System | File metadata and paths |
| 5 | System | Database | INSERT/UPDATE/SELECT/DELETE operations |
| 6 | Database | System | Query results, event and allocation records |

---

## DFD LEVEL 1 - MAIN PROCESSES

Six main processes:

| Process | Name | Function |
|---------|------|----------|
| **P1** | Plan Distribution Event | Create distribution event with budget and resource planning |
| **P2** | Allocate Resources | Assign resources to beneficiaries and calculate costs |
| **P3** | Distribute Resources | Execute distribution and track actual receipt |
| **P4** | Record Distribution | Document distribution with photos and transaction logs |
| **P5** | Verify Receipt | Confirm beneficiary receipt and reconcile quantities |
| **P6** | Generate Reports | Create distribution and budget reports |

### Process Specifications - Level 1

#### **P1: PLAN DISTRIBUTION EVENT**
- **Input**: Event details (name, date, location, program, barangay, budget)
- **Processing**: Validate event data → Reserve location → Approve budget → Create event record
- **Output**: event_id, confirmation
- **Data Stores**: D20 (events), D23 (locations), D24 (programs)

#### **P2: ALLOCATE RESOURCES**
- **Input**: event_id, resource selections with quantities and beneficiary list
- **Processing**: Validate resources → Calculate costs → Match to beneficiaries → Validate budget
- **Output**: allocation_ids, allocation summary
- **Data Stores**: D22 (allocations), D26 (direct_assistance), D19 (resources), D1 (beneficiaries)

#### **P3: DISTRIBUTE RESOURCES**
- **Input**: allocation_ids to mark as distributed
- **Processing**: Generate distribution lists → Record distribution → Update allocation status
- **Output**: distribution confirmation, receipt records
- **Data Stores**: D22 (allocations), D25 (resource_logs)

#### **P4: RECORD DISTRIBUTION**
- **Input**: Distribution evidence (photos, signatures, notes)
- **Processing**: Validate and store photos → Link to event → Create transaction logs
- **Output**: photo_id, log_id
- **Data Stores**: D27 (photos), D25 (resource_logs)

#### **P5: VERIFY RECEIPT**
- **Input**: Beneficiary confirmation of resource receipt
- **Processing**: Verify expected vs actual quantities → Reconcile → Update allocation status
- **Output**: verification_id, status update
- **Data Stores**: D28 (verification), D22 (allocations)

#### **P6: GENERATE REPORTS**
- **Input**: Report filters (date range, program, barangay, resource type)
- **Processing**: Query allocations → Sum distributions → Calculate statistics → Format output
- **Output**: PDF/Excel report with summary and details
- **Data Stores**: D22, D20, D19, D1 (read-only)

---

## DFD LEVEL 2 - ALLOCATE RESOURCES (P2) DECOMPOSITION

### Sub-processes of P2

```
P2.1 → Validate Event Exists (query D20)
P2.2 → Retrieve Event Details (event metadata, program, budget)
P2.3 → Validate Resources (existence, stock, pricing)
P2.4 → Calculate Allocation Costs (quantity × unit_price)
P2.5 → Validate Budget (verify cost ≤ event budget)
P2.6 → Select Beneficiaries (filter eligible beneficiaries)
P2.7 → Create Allocations (insert to D22)
P2.8 → Create Direct Assistance (if applicable - insert to D26)
P2.9 → Create Audit Log (insert to D21)
```

### Level 2 Data Flows

| Process | Reads | Writes | Data |
|---------|-------|--------|------|
| P2.1 | D20 | - | Event lookup |
| P2.3 | D19 | - | Resource validation |
| P2.4 | - | - | Cost calculation (in-memory) |
| P2.5 | - | - | Budget comparison |
| P2.6 | D1 | - | Beneficiary query |
| P2.7 | - | D22 | INSERT allocations |
| P2.8 | - | D26 | INSERT direct_assistance records |
| P2.9 | - | D21 | INSERT audit log |

---

## DFD LEVEL 3 - VALIDATE BUDGET (P2.5) & CALCULATE COST (P2.4)

### P2.4: CALCULATE TOTAL COST (Terminal Process)

**Input**:
- allocations array with {quantity, unit_price} for each
- event_budget (available budget)

**Algorithm**:
1. Initialize total_cost = 0
2. FOR each allocation:
   - allocation_cost = quantity × unit_price
   - total_cost += allocation_cost
3. FOR each direct_assistance (if any):
   - da_cost = amount
   - total_cost += da_cost
4. Return total_cost

**Output**: {total_cost: decimal, allocation_count: integer, status: "CALCULATED"}

### P2.5: VALIDATE BUDGET (Terminal Process)

**Input**:
- total_cost (calculated)
- event_budget (available)

**Validation Logic**:
1. IF total_cost > event_budget:
   - Return ERROR: "Total cost ($X) exceeds budget ($Y)"
2. ELSE:
   - remaining_budget = event_budget - total_cost
   - Return SUCCESS with remaining_budget

**Output**: {status: SUCCESS|FAIL, remaining_budget: decimal, error_message: string}

---

## DATA STORES DICTIONARY

| ID | Name | Database Table | Purpose |
|----|------|---------|---------|
| **D1** | Beneficiaries | beneficiaries | Link to Module 1 (beneficiary records) |
| **D19** | Resource Types | resource_types | Resource catalog (seeds, fertilizer, tools, cash) |
| **D20** | Distribution Events | distribution_events | Main distribution event records |
| **D21** | Audit Logs | audit_logs | System activity and transaction history |
| **D22** | Allocations | allocations | Core allocation records (event → beneficiary → resource) |
| **D23** | Locations | distribution_locations | Distribution venue data (coordinates, capacity, contact) |
| **D24** | Programs | program_names | Program information (name, agency, description) |
| **D25** | Resource Logs | resource_logs | Transaction log (who, what, when, quantity changes) |
| **D26** | Direct Assistance | direct_assistance | Direct cash/assistance distribution records |
| **D27** | Photos | distribution_photos | Distribution event photos (metadata and file paths) |
| **D28** | Verification | allocation_verification | Receipt verification records |

### Key Tables

**D22: allocations**
- Attributes: id (PK), event_id (FK), beneficiary_id (FK), resource_id (FK), quantity, unit_price, total_cost, status, distributed_at

**D26: direct_assistance**
- Attributes: id (PK), beneficiary_id (FK), program_id (FK), amount, status (planned/ready/released/completed/not_received), created_at

---

## SUMMARY

**Total Levels**: 3 (Level 0-3)
**Main Processes (Level 1)**: 6
**Sub-processes (Level 2)**: 9 (P2 decomposition)
**Terminal Processes (Level 3)**: 2 (Calculate Cost, Validate Budget)
**Data Stores**: 11
**External Entities**: 3

All processes align with actual system implementation in DistributionEventController, AllocationController, and DirectAssistanceController.


---


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
