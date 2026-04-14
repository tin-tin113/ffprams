# FFPRAMS: System Architecture and DFD Documentation

This document provides the formal Data Flow Diagram (DFD) decomposition for the Farmer-Fisherfolk Resource and Management System (FFPRAMS). It serves as the official architectural thesis documentation mapping processes from Level 1 macro-subsystems down to Level 4 execution terminations.

---

# LEVEL 1: CONTEXT SUBSYSTEMS
**Overview:** Level 1 Subsystems represent the highest macro-level processes of the application, acting as the primary boundaries bridging the external user entities with the internal operational logic and data stores.

### Figure 1.0 System Configuration Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [System Config Parameters] ➔ Outbound [Validated App Records]
* **Description:** This subsystem acts as the centralized control matrix for the FFPRAMS platform. It securely intercepts master parameters dictated by the Administrator (E1) and orchestrates the routing of critical system rules, user taxonomy, and agency profiles into Data Stores D1 through D4.

### Figure 2.0 Beneficiary Registration Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Intake Paperwork] ➔ Outbound [Demographic Matrix] / [Media]
* **Description:** Serving as the primary data ingress point, this process is responsible for the digitization of Farmer and Fisherfolk identities. It evaluates paperwork from external beneficiaries and commits verified strings to the primary D5 beneficiary database and D6 attachment vault.

### Figure 3.0 Program Management Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Program Parameters] ➔ Outbound [Calculated Entitlements]
* **Description:** The core business logic hub of the application. It dynamically intersects registered demographics against active aid rules, calculating precise physical resource entitlements and maintaining the immutable audit trail of physical distributions mapping to D7, D8, and D9.

### Figure 4.0 Reports & Dashboard Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Raw Ledgers] ➔ Outbound [Interactive Dashboards] / [PDFs]
* **Description:** A strict read-only analytical engine that harvests dispersed data from multiple operational data stores. It aggregates mathematical sums to yield graphical interfaces for internal administrators and privacy-masked digests for external Partner Agencies (E3).

### Figure 5.0 SMS Center Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Notification Triggers] ➔ Outbound [API Payload]
* **Description:** An autonomous communication bridge. It extracts vetted contact numbers and formats outgoing SMS payloads, interfacing securely with external SMS Gateways (E5) and maintaining a transparent transmission ledger within D11.

### Figure 6.0 Geo-Map Monitoring Subsystem
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Coordinates / Volumes] ➔ Outbound [Map Geometries]
* **Description:** An auxiliary spatial evaluation engine. It translates linear tabular logic into graphical polygon arrays, enabling administrators to visualize geographical resource allocation densities fetched from D12 caching structures.

---
<div style="page-break-after: always;"></div>

# LEVEL 2: PROCESS DECOMPOSITIONS
**Overview:** Level 2 processes break open the macro subsystems into discrete, actionable hubs. These processes strictly define what internal operations take place to transform external inputs into database commitments.

### Figure 1.1 Manage User Accounts
* **Process Level:** Level 2 Operational Hub
* **Description:** This process handles the secure ingestion of new administrative accounts or staff roles. It evaluates incoming JSON credential payloads from the Administrator and formats them into exact schemas required by the D1 user repository.

### Figure 1.2 Configure Global Settings
* **Process Level:** Level 2 Operational Hub
* **Description:** Responsible for managing global threshold caps. This process parses raw numeric boundary inputs (such as maximum file size caps or active season limits) and permanently commits them to the D2 settings table.

### Figure 1.4 Define Resource Types
* **Process Level:** Level 2 Operational Hub
* **Description:** Defines the physical taxonomies of aid. It intercepts raw strings defining new crop seeds or fertilizers, validates the enumerator values, and yields standard resource types to the D4 master list.

### Figure 2.1 Intake Identification
* **Process Level:** Level 2 Operational Hub
* **Description:** Acts as the preliminary data receptor. It accepts incoming form arrays and uploaded image binaries from local Staff units, temporarily holding unverified structures in memory before dispatching them to deeper validation loops.

### Figure 2.2 Validate Identity Rules
* **Process Level:** Level 2 Operational Hub
* **Description:** The system firewall against duplicate demographic data. This logic node sanitizes incoming data structures, rejecting redundant identity vectors and returning only clean, sanctified profiles.

### Figure 2.4 Commit Profile DB
* **Process Level:** Level 2 Operational Hub
* **Description:** The final data execution hub for registration. It accepts completely sanitized and formatted demographic objects and maps them perfectly to the SQL constraints of the D5 beneficiaries store.

### Figure 3.2 Evaluate Eligibility
* **Process Level:** Level 2 Operational Hub
* **Description:** A highly complex cross-referencing process. It simultaneously reads defined rules from D7 and scans the active node array in D5, logically matching demographic variables against required aid criteria to output an array of legally qualified individuals.

### Figure 3.3 Generate Allocations
* **Process Level:** Level 2 Operational Hub
* **Description:** Generates the anticipated ledger. This process accepts the mathematically qualified participant arrays and groups them into batch structures, finalizing the expected material entitlement arrays into D8.

### Figure 3.4 Record Distribution
* **Process Level:** Level 2 Operational Hub
* **Description:** The physical terminal tracking process. It verifies physical signatures obtained by field staff and commits verified distribution events, irreversibly marking resources as successfully handed over within D9.

### Figure 4.1 Aggregate Statistics
* **Process Level:** Level 2 Operational Hub
* **Description:** Transmutes flat rows into dimensional matrices. This analytical process runs deep relational joins across the beneficiary and ledger arrays, compiling grouped arrays intended exclusively for interface extraction.

### Figure 4.4 Render View Cache
* **Process Level:** Level 2 Operational Hub
* **Description:** The presentation formatter. It absorbs the raw, mathematically grouped statistical arrays and formats them strictly into JSON objects configured specifically for ChartJS rendering on the administrative frontend.

### Figure 5.2 Queue SMS Batch
* **Process Level:** Level 2 Operational Hub
* **Description:** The messaging template compiler. It dynamically pulls identified mobile string integers, interjecting contextual program names and timelines to form the exact JSON request structures required by external telecom APIs.

### Figure 5.4 Process Delivery Status
* **Process Level:** Level 2 Operational Hub
* **Description:** The webhook listener port. It autonomously receives external asynchronous HTTP pings from active SMS gateways, deciphering the success/fail states of network payloads and logging the results into D11.

### Figure 6.2 Aggregate Regional Stats
* **Process Level:** Level 2 Operational Hub
* **Description:** A volumetric density calculator. It maps resource output integers dynamically onto specified municipal bounds, generating clustered mathematical models required for heat-map overlays.

---
<div style="page-break-after: always;"></div>

# LEVEL 3: ATOMIC PROCESSES
**Overview:** Level 3 defines the granular logic sequences. They represent the exact programmatic loops or algorithms executing inside the operational hubs.

### Figure 1.1.1 Verify ACL Roles
* **Process Level:** Level 3 Logic Sequence
* **Description:** Algorithmically inspects incoming route headers, matching session tokens against assigned database authorizations before allowing processing to continue.

### Figure 1.4.1 Validate Unit Constraints
* **Process Level:** Level 3 Logic Sequence
* **Description:** Forces string inputs (e.g., 'Kilos', 'Sacks') against a rigid enumerator array, rejecting undefined structures to maintain relational database integrity.

### Figure 2.2.1 Check Demographics
* **Process Level:** Level 3 Logic Sequence
* **Description:** Applies calculation algorithms against submitted birthdates, comparing outputs to current server timestamps to return valid age demographics required for processing.

### Figure 2.2.2 Check Database Duplicates
* **Process Level:** Level 3 Logic Sequence
* **Description:** Executes active querying methodologies against the D5 target namespace, scanning previously ingested ID constraints seeking identical overlapping string matrices.

### Figure 3.2.1 Load Target Filters
* **Process Level:** Level 3 Logic Sequence
* **Description:** Programmatically extracts complex array values dynamically stored within JSON parameters of the D7 program store, defining the boundary conditions for evaluations.

### Figure 3.2.2 Intersect Beneficiary Pool
* **Process Level:** Level 3 Logic Sequence
* **Description:** An intense loop sequence iteratively matching every target integer constraint against the extracted active farmer arrays, ultimately dropping ineligible iterations.

### Figure 3.3.1 Pack Batch Matrix
* **Process Level:** Level 3 Logic Sequence
* **Description:** A grouping mechanism that sequentially formats thousands of individual float outputs into one highly optimized packet structure tailored for rapid mass dataset insertion.

### Figure 4.1.1 Parse Query Bounds
* **Process Level:** Level 3 Logic Sequence
* **Description:** Evaluates incoming HTTP GET queries dynamically requested by user interfaces, sanitizing malicious input and establishing secure time-block parameters.

### Figure 4.1.2 Summatic DB Join
* **Process Level:** Level 3 Logic Sequence
* **Description:** Executes chained relational joins using Laravel ORM logic. Unifying dispersed rows between D5 and D8 into highly dense raw structural groups.

### Figure 5.4.1 Parse Webhook Auth
* **Process Level:** Level 3 Logic Sequence
* **Description:** Runs payload decryption methodologies over inbound asynchronous pings received from external telecom APIs, verifying payload signature validity.

### Figure 6.2.1 Calculate Volumetric Weight
* **Process Level:** Level 3 Logic Sequence
* **Description:** An algorithm converting literal string integers derived from batch volumes into corresponding hex-based color arrays for interactive vector rendering.

---
<div style="page-break-after: always;"></div>

# LEVEL 4: EXECUTION TERMINATIONS
**Overview:** The final computational limitations. Level 4 represents the exact locations where flows irreversibly stop progressing and terminate either abruptly in failure, or by permanently committing into standard Data Stores.

### Figure 1.1.1.1 Auth Halt Trigger
* **Process Level:** Level 4 Terminal Execution
* **Description:** Terminates the data pipeline sequence immediately. If an ACL evaluation detects missing or invalid signatures, it locks down flow and forcefully ejects the packet with a 403 Forbidden constraint response.

### Figure 1.4.1.1 Resource INSERT Engine
* **Process Level:** Level 4 Terminal Execution
* **Description:** Executes the physical binding of evaluated JSON variables to predefined exact SQL column tables. Finalizes array data and executes an irreversible INSERT transaction onto the D4 disk store.

### Figure 2.2.2.1 Duplicate Rejection Lock
* **Process Level:** Level 4 Terminal Execution
* **Description:** Scrapes return-variables derived from similarity checks. If detected database match counts exceed zero (`> 0`), it universally rejects the entity's input queue, immediately returning a terminal error exception payload to the staff user interface.

### Figure 2.4.1.1 Profile INSERT Engine
* **Process Level:** Level 4 Terminal Execution
* **Description:** Commits finalized data architectures directly to the D5 physical storage layout. Processes all required timestamp derivations and formally establishes the physical row limit inside the target data warehouse.

### Figure 3.3.1.1 Allocation Batch Runner
* **Process Level:** Level 4 Terminal Execution
* **Description:** Overrides singular standard loop methodologies specifically executing high-velocity, low-latency bulk SQL queries. It ignores specific transactional conflicts and aggressively finalizes irreversible ledger volumes into D8 structures.

### Figure 3.4.1.1 Stock Deduction Commit
* **Process Level:** Level 4 Terminal Execution
* **Description:** Employs explicit mathematical float deduction (`Allocated - Issued`). Alters existing state columns in the D8 layout structure and finalizes the successful handshake status arrays permanently in D9.

### Figure 4.1.2.1 Aggregation View Yield
* **Process Level:** Level 4 Terminal Execution
* **Description:** Formally commands relational engines to cease recursive table searches and locks collected memory allocations, finally yielding immutable cache structures for frontend consumption.

### Figure 5.4.1.1 Retry Exhaustion Terminator
* **Process Level:** Level 4 Terminal Execution
* **Description:** Analyzes iterative counter variables mapping to network fail points. Inherently terminates transmission loops tracking failures strictly exceeding standard limits (`count > 3`), actively stripping the packet from operational queuing channels permanently.

### Figure 6.2.1.1 Disk Cache Yield
* **Process Level:** Level 4 Terminal Execution
* **Description:** Resolves computational outputs natively and yields the generated spatial data into a persistent file structure block format (`.json` payload), terminating logic mapping and physically holding the string payload locally on the D12 cache disk sector.
