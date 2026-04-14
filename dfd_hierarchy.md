# FFPRAMS: Complete, Exhaustive DFD Taxonomy (Gane & Sarson)

This document provides the exhaustive, 100% complete Data Flow Diagram breakdown. It is strictly segregated into Level 1, Level 2, and Level 3 chapters, mapping every single process and data flow boundary down to the lowest programmable action.

---

# LEVEL 1: CONTEXT SUBSYSTEMS

### 1.0 SYSTEM CONFIGURATION
* **Inbound Flow:** [System Config Parameters] from E1 Admin
* **Outbound Flow:** [Validated App Records] to D1 users, D2 settings, D3 agencies, D4 resource_types

### 2.0 BENEFICIARY REGISTRATION
* **Inbound Flow:** [Intake Paperwork] from E4 Beneficiary / [Digitized Profiles] from E2 Staff
* **Outbound Flow:** [Demographic Matrix] to D5 beneficiaries / [Scanned Media] to D6 attachments

### 3.0 PROGRAM MANAGEMENT
* **Inbound Flow:** [Program Parameters] from E1 Admin / [Physical Disbursals] from E2 Staff
* **Inbound Flow:** [Beneficiary Queries] read from D5 beneficiaries
* **Outbound Flow:** [Calculated Entitlements] to D7 programs, D8 allocations, D9 distribution_events, D10 direct_assistances

### 4.0 REPORTS & DASHBOARD
* **Inbound Flow:** [Metric Requests] from E3 Partner / E1 Admin
* **Inbound Flow:** [Raw Ledgers] read from D5, D8, D10
* **Outbound Flow:** [Interactive Dashboards] returned to E1, E2 / [Masked PDF Digests] to E3

### 5.0 SMS CENTER
* **Inbound Flow:** [Notification Triggers] from E2 Staff / [Delivery Status Ping] from E5 SMS Gateway
* **Outbound Flow:** [API Payload] to E5 SMS Gateway / [Audit Log Status] to D11 sms_logs

### 6.0 GEO-MAP MONITORING
* **Inbound Flow:** [Address Coordinates] from D5 and [Sum Volumes] from D8 allocations
* **Outbound Flow:** [Generated Map Files] to D12 geo_cache / [Visual Map Interface] to E1 Admin

---
<div style="page-break-after: always;"></div>

# LEVEL 2: PROCESS DECOMPOSITIONS

### Subsystem 1: System Configuration
* **Process 1.1 Manage User Accounts:** Input: [Account Details] from E1 ➔ Output: [User DB Row] to D1 users
* **Process 1.2 Configure Global Settings:** Input: [App Thresholds] from E1 ➔ Output: [Config Key Array] to D2 settings
* **Process 1.3 Manage Partner Agencies:** Input: [Agreement Data] from E1 ➔ Output: [Agency Profile] to D3 agencies
* **Process 1.4 Define Resource Types:** Input: [Item Specifications] from E1 ➔ Output: [Resource Enum] to D4 resource_types

### Subsystem 2: Beneficiary Registration
* **Process 2.1 Intake Identification:** Input: [Forms] from E2/E4 ➔ Output: [Unverified Struct] to Process 2.2
* **Process 2.2 Validate Identity Rules:** Input: [Unverified Struct] from P 2.1 ➔ Output: [Sanctified Object] to Process 2.4
* **Process 2.3 Process Attachments:** Input: [Image Binaries] from P 2.1 ➔ Output: [Saved File Blocks] to D6 attachments
* **Process 2.4 Commit Profile DB:** Input: [Sanctified Object] from P 2.2 ➔ Output: [Active DB Row] to D5 beneficiaries

### Subsystem 3: Program Management
* **Process 3.1 Define Aid Program:** Input: [Target Demographics] from E1 ➔ Output: [Program DB Node] to D7 programs
* **Process 3.2 Evaluate Eligibility:** Input: [Target Rules] from D7 + [Profiles] from D5 ➔ Output: [Qualified Participant Array] to Process 3.3
* **Process 3.3 Generate Allocations:** Input: [Participant Array] from P 3.2 ➔ Output: [Batch Ledger Matrix] to D8 allocations
* **Process 3.4 Record Distribution:** Input: [Signature Logs] from E2 ➔ Output: [Completed Event Row] to D9 distribution_events
* **Process 3.5 Log Direct Assistance:** Input: [Fund Dispense Form] from E2 ➔ Output: [Aid Audit Record] to D10 direct_assistances

### Subsystem 4: Reports & Dashboard
* **Process 4.1 Aggregate Statistics:** Input: [Raw Records] from D5, D8, D10 ➔ Output: [Grouped Mathematical Arrays] to Process 4.4
* **Process 4.2 Tally Distributions:** Input: [Physical Logs] from D9 ➔ Output: [Rolled-up Volume Sums] to Process 4.4
* **Process 4.3 Generate Partner Digest:** Input: [Date/Area Limits] from E3 ➔ Output: [Privacy-Masked Report] to E3 Partner
* **Process 4.4 Render View Cache:** Input: [Analytic Arrays] from P 4.1 & 4.2 ➔ Output: [Graphs/Charts Payload] to E1 Admin

### Subsystem 5: SMS Center
* **Process 5.1 Extract Contact Book:** Input: [Distribution List] from D5 ➔ Output: [Filtered Contact Set] to Process 5.2
* **Process 5.2 Queue SMS Batch:** Input: [Filtered Contact Set] from P 5.1 ➔ Output: [Formatted Text Struct] to Process 5.3
* **Process 5.3 Transmit to Gateway:** Input: [Formatted Text Struct] from P 5.2 ➔ Output: [HTTP Auth Payload] to E5 SMS Gateway
* **Process 5.4 Process Delivery Status:** Input: [Webhook Ping] from E5 SMS ➔ Output: [Delivery State Value] to D11 sms_logs

### Subsystem 6: Geo-Map Monitoring
* **Process 6.1 Fetch Geo-tagged Profiles:** Input: [Address Maps] from D5 ➔ Output: [Coordinate Pairs] to Process 6.2
* **Process 6.2 Aggregate Regional Stats:** Input: [Volume Maps] from D8 ➔ Output: [Density Heatmap Data] to Process 6.3
* **Process 6.3 Update Geo-JSON Cache:** Input: [Heatmap Data] from P 6.2 ➔ Output: [GeoJSON Blob] to D12 geo_cache
* **Process 6.4 Render Interactive Map:** Input: [GeoJSON Blob] from D12 ➔ Output: [Canvas Base Layers] to E1 Admin

---
<div style="page-break-after: always;"></div>

# LEVEL 3: ATOMIC PROCESSES (PRIMITIVES)
**Description:** The absolute lowest boundary of the system. These represent algorithmic stops, API boundaries, or Database commits. Every Level 2 process is broken down here.

### 1.0 Subsystem Primitives
* **[Level 3] 1.1.1 Verify Admin ACL Rights:** Input: [Session Token] ➔ Output: [Pass/Fail Flag]
* **[Level 3] 1.1.2 Hash Login Credentials:** Input: [Raw Text] ➔ Output: [Bcrypt Hash]
* **[Level 3] 1.1.3 Execute User INSERT:** Input: [Sanitized Account] ➔ Output: [D1 SQL Row]
* **[Level 3] 1.2.1 Parse Environment Variables:** Input: [Setting Thresholds] ➔ Output: [Validated Number]
* **[Level 3] 1.2.2 Execute Settings UPDATE:** Input: [Validated Number] ➔ Output: [D2 SQL Row]
* **[Level 3] 1.3.1 Clean Partner Entity String:** Input: [Raw Agency Name] ➔ Output: [Sanitized String]
* **[Level 3] 1.3.2 Execute Agency INSERT:** Input: [Sanitized String] ➔ Output: [D3 SQL Row]
* **[Level 3] 1.4.1 Validate Resource Taxonomy:** Input: [Item Types & Measurements] ➔ Output: [Strict Enum Array]
* **[Level 3] 1.4.2 Execute Resource INSERT:** Input: [Strict Enum Array] ➔ Output: [D4 SQL Row]

### 2.0 Subsystem Primitives
* **[Level 3] 2.1.1 Parse Form Data:** Input: [Physical/Digital Arrays] ➔ Output: [Internal JSON Format]
* **[Level 3] 2.2.1 Check Rule Boundaries:** Input: [Demographic String] ➔ Output: [Age Status]
* **[Level 3] 2.2.2 Scan Master Data Duplicates:** Input: [ID String] ➔ Output: [Matches Found Integer] from D5
* **[Level 3] 2.2.3 Prompt Interface Flag:** Input: [Matches Found] ➔ Output: [Visual Error to E2]
* **[Level 3] 2.3.1 Compress Binary Image:** Input: [Raw Image File] ➔ Output: [WebP/JPEG Block]
* **[Level 3] 2.3.2 Move To Storage Route:** Input: [WebP Box] ➔ Output: [File Path String Save] to D6
* **[Level 3] 2.4.1 Build Beneficiary Object:** Input: [Sanitized JSON + File Path String] ➔ Output: [Commit-Ready Object]
* **[Level 3] 2.4.2 Execute Profile INSERT:** Input: [Commit-Ready Object] ➔ Output: [D5 SQL Row]

### 3.0 Subsystem Primitives
* **[Level 3] 3.1.1 Evaluate Budget Parameters:** Input: [Financial Cap/Volume Cap] ➔ Output: [Validated Math Node]
* **[Level 3] 3.1.2 Execute Program INSERT:** Input: [Validated Rules] ➔ Output: [D7 SQL Row]
* **[Level 3] 3.2.1 Load Target Filters:** Input: [Program Rules] ➔ Output: [Query Constructor Variables]
* **[Level 3] 3.2.2 Execute Beneficiary Filter Join:** Input: [Constructor Variables] ➔ Output: [Approved User Array] from D5
* **[Level 3] 3.2.3 Compute Individual Shares:** Input: [Approved User Array vs D4 limits] ➔ Output: [Exact Unit Float Value]
* **[Level 3] 3.3.1 Map Allocation Rows:** Input: [Approved User Array] ➔ Output: [Batch Insert Packet]
* **[Level 3] 3.3.2 Execute Allocation Batch Insert:** Input: [Batch Insert Packet] ➔ Output: [D8 SQL Commit]
* **[Level 3] 3.4.1 Parse Delivery Tokens:** Input: [Beneficiary Scans] ➔ Output: [Verified Token Match]
* **[Level 3] 3.4.2 Execute Execution UPDATE:** Input: [Verified Token Match] ➔ Output: [D9 Status State]
* **[Level 3] 3.5.1 Validate Emergency Action:** Input: [Reason Form] ➔ Output: [Allowed Pass]
* **[Level 3] 3.5.2 Execute Immediate Aid Insert:** Input: [Allowed Pass Data] ➔ Output: [D10 SQL Row]

### 4.0 Subsystem Primitives
* **[Level 3] 4.1.1 Parse Query String Limits:** Input: [Date Start/End] ➔ Output: [Timeframe Bounds]
* **[Level 3] 4.1.2 Fire SQL Joins:** Input: [Timeframe Bounds] ➔ Output: [Union Matrix of Profiles and Volumes]
* **[Level 3] 4.2.1 Pull Warehouse Sums:** Input: [D9 Query] ➔ Output: [Disbursement Total Integer]
* **[Level 3] 4.3.1 Compile Privacy Filters:** Input: [Partner ACL] ➔ Output: [Exclusion Rules]
* **[Level 3] 4.3.2 Render PDF Exporter:** Input: [Masked Data] ➔ Output: [PDF Binary Buffer]
* **[Level 3] 4.4.1 Build ChartJS Payloads:** Input: [Raw Mathematical Flow] ➔ Output: [JSON Axis Variables]

### 5.0 Subsystem Primitives
* **[Level 3] 5.1.1 Query Missing Contact Flags:** Input: [D5 Array] ➔ Output: [Removal of Null Phones]
* **[Level 3] 5.1.2 Clean MSISDN Integers:** Input: [Raw Contacts] ➔ Output: [+63 Standard Format String]
* **[Level 3] 5.2.1 Inject Dynamic Placeholders:** Input: [Msg Template] ➔ Output: [Personalized Node]
* **[Level 3] 5.3.1 Wrap Gateway Auth Header:** Input: [API Secrets] ➔ Output: [Secure HTTPS Packet]
* **[Level 3] 5.3.2 Transmit Axios POST:** Input: [Secure Packet] ➔ Output: [TCP Transmission Route]
* **[Level 3] 5.4.1 Parse Inbound Webhook:** Input: [JSON Header] ➔ Output: [Route Identity]
* **[Level 3] 5.4.2 Execute Network Log UPDATE:** Input: [Delivery Route ID] ➔ Output: [D11 Message State]

### 6.0 Subsystem Primitives
* **[Level 3] 6.1.1 Query Spatial Index Limits:** Input: [Regional Viewport] ➔ Output: [Boundary Box Nodes]
* **[Level 3] 6.1.2 Scrape D5 Lat/Long Fields:** Input: [Nodes] ➔ Output: [Clean Vector Arrays]
* **[Level 3] 6.2.1 Join Resource Weight Math:** Input: [Allocated Volumes] ➔ Output: [Heatmap Color Codes]
* **[Level 3] 6.3.1 Format Structural JSON Feature:** Input: [Arrays + Colors] ➔ Output: [Valid RFC 7946 Blob]
* **[Level 3] 6.3.2 Overwrite Cache Disk:** Input: [Geo Blob Buffer] ➔ Output: [D12 File Row]
* **[Level 3] 6.4.1 Render Tile Protocol:** Input: [Cached Blob] ➔ Output: [DOM Canvas Execution]
