# FFPRAMS: Complete, Exhaustive DFD Taxonomy (Level 1 to Level 4)

This document provides a strictly segregated, hierarchical Data Flow Diagram (DFD) breakdown following Gane & Sarson rules. It traces the logic linearly from Subsystem (Level 1) down to the absolute algorithmic Termination limit (Level 4), with all redundant processes omitted for clarity.

---

# LEVEL 1: CONTEXT SUBSYSTEMS
**Description:** The macro-level processes and their data interactions with entities and stores.

### 1.0 SYSTEM CONFIGURATION
* **Inbound Flow:** [System Config Parameters] from E1 Admin
* **Outbound Flow:** [Validated App Records] to D1 users, D2 settings, D3 agencies, D4 resource_types

### 2.0 BENEFICIARY REGISTRATION
* **Inbound Flow:** [Intake Paperwork] from E4 Beneficiary / E2 Staff
* **Outbound Flow:** [Demographic Matrix] to D5 beneficiaries / [Scanned Media] to D6 attachments

### 3.0 PROGRAM MANAGEMENT
* **Inbound Flow:** [Program Parameters] from E1 Admin / [Physical Disbursals] from E2 Staff
* **Outbound Flow:** [Calculated Entitlements] to D7 programs, D8 allocations, D9 distribution_events

### 4.0 REPORTS & DASHBOARD
* **Inbound Flow:** [Metric Requests] from E3 Partner / E1 Admin
* **Outbound Flow:** [Dashboards] returned to E1, E2 / [PDF Digests] to E3

### 5.0 SMS CENTER
* **Inbound Flow:** [Notification Triggers] from E2 Staff / [Status Ping] from E5 Gateway
* **Outbound Flow:** [API Payload] to E5 SMS Gateway / [Audit Status] to D11 sms_logs

### 6.0 GEO-MAP MONITORING
* **Inbound Flow:** [Coordinates / Volumes] from D5 and D8
* **Outbound Flow:** [Geometries] to D12 geo_cache / [Visual Map] to E1 Admin

---

# LEVEL 2: PROCESS DECOMPOSITIONS
**Description:** Structural decomposition of Level 1 subsystems into discrete operational hubs.

### From 1.0 System Configuration
* **Process 1.1 Manage User Accounts:** Input: [Account Details] ➔ Output: [User DB Row] to D1 users
* **Process 1.2 Configure Global Settings:** Input: [App Thresholds] ➔ Output: [Config Key] to D2 settings
* **Process 1.4 Define Resource Types:** Input: [Item Specs] ➔ Output: [Resource Enum] to D4 resource_types

### From 2.0 Beneficiary Registration
* **Process 2.1 Intake Identification:** Input: [Forms] ➔ Output: [Unverified Struct] to Process 2.2
* **Process 2.2 Validate Identity Rules:** Input: [Struct] ➔ Output: [Sanctified Object] to Process 2.4
* **Process 2.4 Commit Profile DB:** Input: [Sanctified Object] ➔ Output: [Active Row] to D5 beneficiaries

### From 3.0 Program Management
* **Process 3.2 Evaluate Eligibility:** Input: [Rules + D5 Profiles] ➔ Output: [Participant Array] to Process 3.3
* **Process 3.3 Generate Allocations:** Input: [Participant Array] ➔ Output: [Ledger Matrix] to D8 allocations
* **Process 3.4 Record Distribution:** Input: [Signature Logs] ➔ Output: [Completed Row] to D9 distribution_events

### From 4.0 Reports & Dashboard
* **Process 4.1 Aggregate Statistics:** Input: [Raw Records] ➔ Output: [Mathematical Arrays] to Process 4.4
* **Process 4.4 Render View Cache:** Input: [Arrays] ➔ Output: [Graphs/Charts Payload] to E1 Admin

### From 5.0 SMS Center
* **Process 5.2 Queue SMS Batch:** Input: [Contact Set] ➔ Output: [Formatted Struct] to Process 5.3
* **Process 5.4 Process Delivery Status:** Input: [Webhook Ping] ➔ Output: [Delivery State] to D11 sms_logs

### From 6.0 Geo-Map Monitoring
* **Process 6.2 Aggregate Regional Stats:** Input: [Volume Maps] ➔ Output: [Density Heatmap] to Process 6.3

---

# LEVEL 3: ATOMIC PROCESSES
**Description:** The explicit logic sequences operating inside the Level 2 hubs.

### 1.0 Subsystem Logic
* **[Level 3] 1.1.1 Verify ACL Roles:** Input: [Token] ➔ Output: [Session State]
* **[Level 3] 1.4.1 Validate Unit Constraints:** Input: [Measurements] ➔ Output: [Bound Struct]

### 2.0 Subsystem Logic
* **[Level 3] 2.2.1 Check Demographics:** Input: [Biometric String] ➔ Output: [Age Grouping]
* **[Level 3] 2.2.2 Check Database Duplicates:** Input: [ID String] ➔ Output: [Match Query Matrix]

### 3.0 Subsystem Logic
* **[Level 3] 3.2.1 Load Target Filters:** Input: [Program Rules] ➔ Output: [Query Variables]
* **[Level 3] 3.2.2 Intersect Beneficiary Pool:** Input: [D5 Array + Rules] ➔ Output: [Eligible Float Values]
* **[Level 3] 3.3.1 Pack Batch Matrix:** Input: [Eligible Array] ➔ Output: [Compiled Buffer]

### 4.0 Subsystem Logic
* **[Level 3] 4.1.1 Parse Query Bounds:** Input: [Date/Zone Limit] ➔ Output: [Timeframe Variables]
* **[Level 3] 4.1.2 Summatic DB Join:** Input: [Tables] ➔ Output: [Raw Unformatted Relational Data]

### 5.0 Subsystem Logic
* **[Level 3] 5.4.1 Parse Webhook Auth:** Input: [HTTP Post] ➔ Output: [Authenticated Route Variables]

### 6.0 Subsystem Logic
* **[Level 3] 6.2.1 Calculate Volumetric Weight:** Input: [Allocations] ➔ Output: [Hex Color Codes]

---

# LEVEL 4: EXECUTION TERMINATIONS
**Description:** The deepest execution boundaries where logic ceases and physical limits or strict database commits occur. (The End-State of the flows).

### Database Terminations (SQL Executions)
* **[Level 4] 1.1.1.1 Auth Halt Trigger:** Rejects system transit entirely if Token = Invalid.
* **[Level 4] 1.4.1.1 Resource INSERT Engine:** Executes physical commit of Item Enum arrays permanently into D4 limit blocks.
* **[Level 4] 2.2.2.1 Duplicate Rejection Lock:** Scrapes similarity hashes vs D5. Instantly terminates registration route if Match > 0.
* **[Level 4] 2.4.1.1 Profile INSERT Engine:** Binds parameters and saves exact string geometries permanently to D5 schema.
* **[Level 4] 3.3.1.1 Allocation Batch Runner:** Executes bulk INSERT ignoring duplicates; finalizes irreversible ledger allocation strings in D8.
* **[Level 4] 3.4.1.1 Stock Deduction Commit:** Executes mathematical subtraction against remaining bounds; alters D9 state flags.
* **[Level 4] 4.1.2.1 Aggregation View Yield:** Ends relational joins and locks output state arrays for cache rendering.
* **[Level 4] 5.4.1.1 Retry Exhaustion Terminator:** Evaluates if Failed SMS count > 3. Irreversibly purges queue token and commits dead state to D11.
* **[Level 4] 6.2.1.1 Disk Cache Yield:** Finalizes output parsing and permanently dumps GeoJSON blob physical file to D12 disk volume.
