# FFPRAMS: Strictly Separated DFD Taxonomy (Gane & Sarson)

This document maps all Data Flow Diagram routes separated strictly by their architectural Level, adhering perfectly to Gane & Sarson noun-payload notation and boundaries.

---

# LEVEL 1: CONTEXT SUBSYSTEMS
**Description:** The high-level overview detailing the major process hubs and how they interface with External Entities and Data Stores.

### 1.0 SYSTEM CONFIGURATION
* **Inbound Flow:** [System Config Parameters] from External Entity (E1 Admin) into Process (1.0)
* **Outbound Flow:** [User Profiles] from Process (1.0) into Data Store (D1 users)
* **Outbound Flow:** [System Rules] from Process (1.0) into Data Store (D2 settings)
* **Outbound Flow:** [Partner Organization Profiles] from Process (1.0) into Data Store (D3 agencies)
* **Outbound Flow:** [Resource Taxonomies] from Process (1.0) into Data Store (D4 resource_types)

### 2.0 BENEFICIARY REGISTRATION
* **Inbound Flow:** [Beneficiary Intake Paperwork] from External Entity (E4 Beneficiary) to Process (2.0)
* **Inbound Flow:** [Digitized Beneficiary Data] from External Entity (E2 Staff) to Process (2.0)
* **Outbound Flow:** [Validated Demographic Profile] from Process (2.0) to Data Store (D5 beneficiaries)
* **Outbound Flow:** [Scanned ID Media] from Process (2.0) to Data Store (D6 attachments)

### 3.0 PROGRAM MANAGEMENT
* **Inbound Flow:** [Program Parameters] from External Entity (E1 Admin) to Process (3.0)
* **Inbound Flow:** [Disbursement Logs] from External Entity (E2 Staff) to Process (3.0)
* **Inbound Flow:** [Beneficiary Status Data] from Data Store (D5 beneficiaries) to Process (3.0)
* **Outbound Flow:** [Program Master Data] to Data Store (D7 programs)
* **Outbound Flow:** [Approved Entitlements] to Data Store (D8 allocations)
* **Outbound Flow:** [Physical Delivery Events] to Data Store (D9 distribution_events)

### 4.0 REPORTS & DASHBOARD
* **Inbound Flow:** [Report Query Parameters] from External Entity (E3 Partner / E1 Admin) to Process (4.0)
* **Inbound Flow:** [Raw System Tables] from All Data Stores (D5, D8, D10) to Process (4.0)
* **Outbound Flow:** [Interactive Metric Dashboard] from Process (4.0) to E1 Admin / E2 Staff
* **Outbound Flow:** [Aggregated Data Digest] from Process (4.0) to E3 Partner

### 5.0 SMS CENTER
* **Inbound Flow:** [Dispatch Trigger] from External Entity (E2 Staff) to Process (5.0)
* **Inbound Flow:** [Delivery Status Callback] from External Entity (E5 SMS Gateway) to Process (5.0)
* **Outbound Flow:** [API Encrypted Payload] from Process (5.0) to E5 SMS Gateway
* **Outbound Flow:** [Transmission Status Log] from Process (5.0) to Data Store (D11 sms_logs)

### 6.0 GEO-MAP MONITORING
* **Inbound Flow:** [Geocoded Anchor Points] from Data Store (D5 beneficiaries) to Process (6.0)
* **Inbound Flow:** [Volume Load Data] from Data Store (D8 allocations) to Process (6.0)
* **Outbound Flow:** [Compiled Map Geometries] from Process (6.0) to Data Store (D12 geo_cache)
* **Outbound Flow:** [Interactive Map Layer UI] from Process (6.0) to External Entity (E1 Admin)

---
<div style="page-break-after: always;"></div>

# LEVEL 2: PROCESS DECOMPOSITION
**Description:** The expansion of each Subsystem into its distinct operational processes.

### From 1.0 System Configuration
* **Process 1.1 Manage User Accounts:** Input Flow: [Account Details] from E1 Admin ➔ Output Flow: [Validated Account Record] to D1 users
* **Process 1.2 Configure Global Settings:** Input Flow: [App Thresholds] from E1 Admin ➔ Output Flow: [Configuration Rules] to D2 settings
* **Process 1.3 Manage Partner Agencies:** Input Flow: [Partner Agreement Data] from E1 Admin ➔ Output Flow: [Agency Profile Data] to D3 agencies
* **Process 1.4 Define Resource Types:** Input Flow: [Item Definitions] from E1 Admin ➔ Output Flow: [Resource Master List] to D4 resource_types

### From 2.0 Beneficiary Registration
* **Process 2.1 Intake Identification:** Input Flow: [Physical/Digital Documents] from E2 / E4 ➔ Output Flow: [Unverified Registration Packet] to Process 2.2
* **Process 2.2 Validate Identity Rules:** Input Flow: [Unverified Registration Packet] from Process 2.1 ➔ Output Flow: [Sanitized Profile Record] to Process 2.4
* **Process 2.3 Process Attachments:** Input Flow: [Scan Binaries] from Process 2.1 ➔ Output Flow: [Formatted Image Files] to D6 attachments
* **Process 2.4 Commit Profile DB:** Input Flow: [Sanitized Profile Record] from Process 2.2 ➔ Output Flow: [Active Beneficiary Row] to D5 beneficiaries

### From 3.0 Program Management
* **Process 3.1 Define Aid Program:** Input Flow: [Target Demographics] from E1 Admin ➔ Output Flow: [Program Structure] to D7 programs
* **Process 3.2 Evaluate Eligibility:** Input Flow: [Program Execution Rules] from D7 programs ➔ Output Flow: [Eligible Participant Array] to Process 3.3
* **Process 3.3 Generate Allocations:** Input Flow: [Eligible Participant Array] from Process 3.2 ➔ Output Flow: [Ledger Allocation Row] to D8 allocations
* **Process 3.4 Record Distribution:** Input Flow: [Handover Receipt Logs] from E2 Staff ➔ Output Flow: [Distribution Audit Trail] to D9 distribution_events
* **Process 3.5 Log Direct Assistance:** Input Flow: [Emergency Fund Dispense Docs] from E2 Staff ➔ Output Flow: [Direct Aid Record] to D10 direct_assistances

### From 4.0 Reports & Dashboard
* **Process 4.1 Aggregate Statistics:** Input Flow: [Raw Records] from D5, D8, D10 ➔ Output Flow: [Grouped Analytic Matrices] to Process 4.4
* **Process 4.2 Tally Distributions:** Input Flow: [Raw Delivery Events] from D9 distribution_events ➔ Output Flow: [Total Dispersed Volume] to Process 4.4
* **Process 4.3 Generate Partner Digest:** Input Flow: [Report Query Parameters] from E3 Partner ➔ Output Flow: [Masked Data Digest] to E3 Partner
* **Process 4.4 Render View Cache:** Input Flow: [Grouped Analytic Matrices] from Process 4.1 ➔ Output Flow: [Graphical Dashboard Payload] to E1 Admin

### From 5.0 SMS Center
* **Process 5.1 Extract Contact Book:** Input Flow: [Beneficiary Contact Nodes] from D5 beneficiaries ➔ Output Flow: [Mobile Number Array] to Process 5.2
* **Process 5.2 Queue SMS Batch:** Input Flow: [Mobile Number Array] from Process 5.1 ➔ Output Flow: [Formatted JSON Request] to Process 5.3
* **Process 5.3 Transmit to Gateway:** Input Flow: [Formatted JSON Request] from Process 5.2 ➔ Output Flow: [SMS Network Post Request] to E5 SMS Gateway
* **Process 5.4 Process Delivery Status:** Input Flow: [Webhook Ping Payload] from E5 SMS Gateway ➔ Output Flow: [Logged Execution State] to D11 sms_logs

### From 6.0 Geo-Map Monitoring
* **Process 6.1 Fetch Geo-tagged Profiles:** Input Flow: [Beneficiary Address Meta] from D5 beneficiaries ➔ Output Flow: [Location Coordinate Array] to Process 6.2
* **Process 6.2 Aggregate Regional Stats:** Input Flow: [Area Volume Data] from D8 allocations ➔ Output Flow: [Combined Polygon Matrix] to Process 6.3
* **Process 6.3 Update Geo-JSON Cache:** Input Flow: [Combined Polygon Matrix] from Process 6.2 ➔ Output Flow: [Static GeoJSON Flatfile] to D12 geo_cache
* **Process 6.4 Render Interactive Map:** Input Flow: [Static GeoJSON Flatfile] from D12 geo_cache ➔ Output Flow: [Mapbox Render Layers] to E1 Admin

---
<div style="page-break-after: always;"></div>

# LEVEL 3: PRIMITIVE PROCESSES
**Description:** The atomic rule validations and logical bounds. Purely algorithmic transitions.

* **[Level 3] 1.1.1 Validate Token:** Input Flow: [Auth Token] ➔ Output Flow: [Boolean Validation Flag]
* **[Level 3] 1.1.2 Hash Password:** Input Flow: [Raw Password] ➔ Output Flow: [Bcrypt Hash String]
* **[Level 3] 1.4.1 Validate Units:** Input Flow: [Unit String] ➔ Output Flow: [Standard Unit Enum]
* **[Level 3] 1.4.2 Compute Shelf-Life:** Input Flow: [Manufacture Date] ➔ Output Flow: [Expiration Timestamp]
* **[Level 3] 2.2.1 Check Age Limits:** Input Flow: [Birthdate String] ➔ Output Flow: [Age Eligibility Flag]
* **[Level 3] 2.2.2 Check Duplicates:** Input Flow: [Gov ID Number] ➔ Queries D5 ➔ Output Flow: [Similarity Match Count]
* **[Level 3] 2.2.3 Issue Rejections:** Input Flow: [Fail State Parameters] ➔ Output Flow: [Rejection Alert] to E2 Staff
* **[Level 3] 3.2.1 Pull Target Rule:** Input Flow: [Program ID] ➔ Output Flow: [Constraint Logic Enum]
* **[Level 3] 3.2.2 Sweep Enrollee Base:** Input Flow: [D5 Dataset] ➔ Output Flow: [Filtered Eligible Dataset]
* **[Level 3] 3.2.3 Calculate Quota:** Input Flow: [Constraint Logic Enum] ➔ Output Flow: [Assigned Resource Quantity]
* **[Level 3] 4.1.1 Format Date Boundary:** Input Flow: [HTTP Date Params] ➔ Output Flow: [SQL Date Range Obj]
* **[Level 3] 4.1.2 Summatic DB Join:** Input Flow: [Relational Tables] ➔ Output Flow: [Joined DB Table Matrix]
* **[Level 3] 5.4.1 Parse Webhook:** Input Flow: [JSON HTTP Body] ➔ Output Flow: [Parsed Internal Properties]
* **[Level 3] 5.4.2 Update Database State:** Input Flow: [Parsed Internal Properties] ➔ Output Flow: [SQL Update Commit]
* **[Level 3] 5.4.3 Check Retry Threshold:** Input Flow: [Failure Loop Signal] ➔ Output Flow: [Requeue Array Push]

---

# LEVEL 4: EXECUTION TERMINATIONS
**Description:** The absolute lowest boundary of the system where flow terminates or triggers a physical loop.

* **[Level 4] 2.2.2.1 Core Match Logic Engine:** Scans string hashes against indexed records. Limits flow if Result > 0.
* **[Level 4] 4.1.2.1 SQL Execution Engine:** Fires compiled joins in raw SQL. Awaits matrix build.
* **[Level 4] 5.4.3.1 Cron Job Terminator:** Increments the integer value of payload failure. If > 3, terminates flow forever.
