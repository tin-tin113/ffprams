# FFPRAMS: System Architecture and DFD Documentation

This document provides the formal Data Flow Diagram (DFD) decomposition for the Farmer-Fisherfolk Resource and Management System (FFPRAMS). It serves as the official architectural thesis documentation mapping processes natively from Level 1 macro-subsystems down to Level 4 execution terminations.

---

# LEVEL 1: CONTEXT SUBSYSTEMS
**Overview:** Level 1 Subsystems represent the highest macro-level processes of the application, acting as the primary boundaries bridging the External Entities with the internal operational logic and Data Stores.

### Figure 1.0 System Configuration
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [System Config Parameters] from External Entity (E1 Admin) ➔ Outbound [Validated App Records] to Data Store (D1 users), Data Store (D2 settings), Data Store (D3 agencies), and Data Store (D4 resource_types).
* **Description:** The System Configuration subsystem acts as the centralized control matrix for the entire FFPRAMS platform framework. It securely intercepts master administrative parameters dictated manually by the External Entity (E1 Admin) during initial setup and routine system maintenance tasks. This subsystem is rigorously responsible for orchestrating the routing of critical system rules, user role taxonomies, and partner agency profiles into their respective secure Data Stores. By functioning as the primary gateway for administrative control, it ensures that all global system constraints are thoroughly validated before establishing any persistent state logic within the application bounds.

### Figure 2.0 Beneficiary Registration
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Intake Paperwork] from External Entity (E4 Beneficiary) and External Entity (E2 Staff) ➔ Outbound [Demographic Matrix] to Data Store (D5 beneficiaries) and [Media Scans] to Data Store (D6 attachments).
* **Description:** Serving as the primary data ingress point for the system, this process is singularly responsible for the digitization of Farmer and Fisherfolk biodata. It diligently evaluates incoming application paperwork supplied by the External Entity (E4 Beneficiary) and processed by the External Entity (E2 Staff). Upon successfully sanitizing these inbound strings and uploaded forms, it commits the verified structural data to the primary Data Store (D5 beneficiaries). Simultaneously, it safely encrypts and routes all sensitive document imagery into the secure Data Store (D6 attachments) vault.

### Figure 3.0 Program Management
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Program Parameters] from External Entity (E1 Admin) ➔ Outbound [Calculated Entitlements] to Data Store (D7 programs), Data Store (D8 allocations), and Data Store (D9 distribution_events).
* **Description:** Recognized as the core business logic hub of the application, this subsystem manages all programmatic aid distribution logic. It dynamically intersects massive registered demographic pools against active aid rules uploaded by the External Entity (E1 Admin). By processing these inputs, it reliably calculates the precise physical resource entitlements owed to each vetted beneficiary within the network. Furthermore, it actively maintains the immutable operational audit trail detailing exactly when and where physical distributions successfully terminate within the Data Stores.

### Figure 4.0 Reports & Dashboard
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Raw Ledgers] fetched from Data Store (D5), Data Store (D8), Data Store (D10) ➔ Outbound [Interactive Dashboards] to External Entity (E1 Admin) and External Entity (E2 Staff), and [PDF Digests] to External Entity (E3 Partner).
* **Description:** Operating as a strict read-only analytical engine, this subsystem systematically harvests dispersed logistical data from across multiple foundational operational data stores. It executes enormous algorithmic sweeps to aggregate these mathematical sums, seamlessly converting raw ledger tables into fluid graphical interfaces tailored for internal administrators. Additionally, the subsystem actively filters and conceals classified beneficiary data before exporting it, safely yielding privacy-masked intelligence digests to the External Entity (E3 Partner).

### Figure 5.0 SMS Center
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Notification Triggers] from External Entity (E2 Staff) ➔ Outbound [API Transmission Payload] to External Entity (E5 SMS Gateway) and [Audit Status] to Data Store (D11 sms_logs).
* **Description:** Functioning as the autonomous communication bridge for the application, this subsystem governs the outbound messaging pipeline. It systematically extracts vetted contact numbers and formats outgoing SMS payloads designated by the External Entity (E2 Staff) for specific geographic areas. The platform interfaces securely with the targeted External Entity (E5 SMS Gateway), exchanging encrypted programmatic post requests to execute remote text messages. Crucially, it manages asynchronous delivery callbacks, maintaining a flawlessly transparent chronological transmission ledger securely within the Data Store (D11 sms_logs).

### Figure 6.0 Geo-Map Monitoring
* **Process Level:** Level 1 Subsystem
* **Data Flow:** Inbound [Coordinates] fetched from Data Store (D5) and [Volumes] from Data Store (D8) ➔ Outbound [Map Geometries] to Data Store (D12 geo_cache) and [Visual Frame] to External Entity (E1 Admin).
* **Description:** Developed specifically as an auxiliary spatial evaluation engine, this mapping subsystem translates linear tabular logic into distinctly interactive geographical polygon arrays. It routinely scrubs massive arrays of mathematical resource distribution statistics and pairs them precisely with encoded GPS latitude variables. By consolidating these disparate metrics, it enables the system to construct beautifully rendered resource allocation heat-maps stored efficiently in the Data Store (D12 geo_cache). Ultimately, this enables the External Entity (E1 Admin) to seamlessly visualize exact asset flow density across rural municipality lines.

---
<div style="page-break-after: always;"></div>

# LEVEL 2: PROCESS DECOMPOSITIONS
**Overview:** Level 2 processes break open the macro subsystems into discrete, actionable hubs. These processes strictly define what internal operations take place to transform external inputs into database commitments.

### Figure 1.1 Manage User Accounts
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Account Details Form] from External Entity (E1 Admin) ➔ Outbound [Formulated User Row] to Data Store (D1 users).
* **Description:** This distinct operational hub natively handles the secure ingestion of new administrative accounts or field staff roles. It evaluates incoming dynamic JSON credential payloads transmitted heavily from the governing External Entity (E1 Admin). During execution, it actively formats the demographic details and permission hierarchies into the exact normalized schemas required by the system. Finally, it routes the completed identity structure down into the persistent physical storage layer residing in Data Store (D1 users).

### Figure 1.4 Define Resource Types
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Item Specification Packet] from External Entity (E1 Admin) ➔ Outbound [Validated Resource Enum] to Data Store (D4 resource_types).
* **Description:** Acting as the dictionary authority, this hub comprehensively defines the variable physical taxonomies of available agricultural aid. It immediately intercepts inbound structural definitions specifying the categorization of new crop seeds or chemical fertilizers. The engine rigidly validates the specific measurement strings to prevent floating-point allocation errors from impacting rural distributions. Once the structure holds physical integrity, the hub yields standard, locked resource categorizations mapped linearly to the Data Store (D4 resource_types).

### Figure 2.1 Intake Identification
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Physical/Digital Application Sets] from External Entity (E4 Beneficiary) and External Entity (E2 Staff) ➔ Outbound [Unverified Structure Packet] passed directly to Process 2.2.
* **Description:** Operating strictly as the preliminary data receptor, this hub absorbs all external friction associated with initial beneficiary enrollment. It willingly accepts inbound multi-part application form arrays populated natively by operational staff units and direct civilian inputs. Because the data originates from uncontrolled mediums, the system intentionally strips irrelevant garbage code and temporarily suspends the strings securely in active system memory. It subsequently packages the normalized data into a manageable JSON array, actively dispatching it to deeper algorithmic validation loops.

### Figure 2.2 Validate Identity Rules
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Unverified Structure Packet] received from Process 2.1 ➔ Outbound [Sanctified Target Profile] dispatched logically to Process 2.4.
* **Description:** This critical processing hub functionally serves as the operational firewall combating fraudulent or duplicate demographic infiltration. By capturing the suspended profile structures moving inwards from the intake buffer, it aggressively deploys cross-referencing similarity sweeps to detect data shadows. The node diligently purges redundant identity vectors, intentionally dropping payloads that violate strict governmental matching constraints. Ultimately, it outputs incredibly clean and completely sanctified profile records ready for finalized transactional writes.

### Figure 2.4 Commit Profile DB
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Sanctified Target Profile] received from Process 2.2 ➔ Outbound [Active DB Schema Row] actively written to Data Store (D5 beneficiaries).
* **Description:** Recognized as the final transactional execution hub for the registration pipeline, this node closes the loop on applicant enrollment. It systematically parses completely processed demographic structures handed over by the validation engine. By manipulating these pristine payloads, it actively binds them structurally to conform perfectly to the rigid relational limits of the primary application database. Consequently, placing the target string data formally on disk within Data Store (D5 beneficiaries) formally ends the linear inbound process.

### Figure 3.2 Evaluate Eligibility
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Eligibility Criteria Logic] read from Data Store (D7 programs) intersecting with [Registered Profiles] from Data Store (D5 beneficiaries) ➔ Outbound [Qualified Participant Logic Array] to Process 3.3.
* **Description:** Acting as a highly complex algorithmic cross-referencing engine, this hub guarantees resources only reach legally designated parties. It natively reads mathematically defined geographic restrictions natively anchored in the D7 data store while concurrently polling millions of active status flags located throughout the D5 registry. By logically intersecting the specific demographic vectors against the inflexible active constraint rules, it intelligently forces massive pools of ineligible recipients into exception failures. Ultimately, the hub successfully extracts an approved mapping list containing perfectly legally qualified individuals.

### Figure 3.3 Generate Allocations
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Qualified Participant Logic Array] passed consecutively from Process 3.2 ➔ Outbound [Programmatic Ledger Matrix] permanently written to Data Store (D8 allocations).
* **Description:** Engineered explicitly to construct the anticipated transactional ledger mapping, this hub handles bulk data architecture. It continuously ingests the pre-approved, mathematically qualified structural lists output dynamically from the eligibility checking phases. Utilizing optimized relational structuring methodologies, the process tightly binds individual beneficiary identifiers into massive, cohesive batch arrays tailored for low-latency network performance. Upon confirming batch uniformity, it effectively finalizes thousands of expected material entitlement pipelines directly into Data Store (D8).

### Figure 4.1 Aggregate Statistics
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Raw Fragmented Telemetry] fetched continuously from Data Store (D5), Data Store (D8), and Data Store (D10) ➔ Outbound [Grouped Mathematical Arrays] forwarded cleanly to Process 4.4.
* **Description:** This distinct operational hub acts to transmute hundreds of thousands of fragmented flat-file strings into dense dimensional matrices. By executing incredibly deep SQL relational intersection logics seamlessly traversing beneficiary identities and disbursement records, it unifies disparate table constraints. The processing block dynamically collapses repetitive values and successfully computes regional distribution volumetric weights mathematically. Following these massive computations, it explicitly prepares these grouped structural arrays required exclusively for presentation extraction engines.

### Figure 5.4 Process Delivery Status
* **Process Level:** Level 2 Operational Hub
* **Data Flow:** Inbound [Asynchronous Webhook Ping] generated by External Entity (E5 SMS Gateway) ➔ Outbound [Verified Delivery State] forcibly committed to Data Store (D11 sms_logs).
* **Description:** Tasked identically as a high-availability webhook listener port, this process functions fully autonomously within the routing architecture. It actively stands by to receive inbound asynchronous HTTP network posts transmitted randomly by external telecommunication carrier gateways. Utilizing rigorous decryption parsing, it comprehensively analyzes the success or failure transmission states deeply embedded within the network structures. Once authenticated, the subsystem ensures immediate synchronization of the chronological log structure locked deeply within Data Store (D11).

---
<div style="page-break-after: always;"></div>

# LEVEL 3: ATOMIC PROCESSES
**Overview:** Level 3 defines the granular logic sequences. They represent the exact programmatic loops or comparative algorithms executing fluidly inside the Level 2 operational hubs.

### Figure 1.4.1 Validate Unit Constraints
* **Process Level:** Level 3 Logic Sequence
* **Data Flow:** Inbound [Raw Measurement Strings] ➔ Outbound [Secured Limit Structural Enum]
* **Description:** This highly explicit algorithmic loop forces unpredictable string inputs mapping towards unit measurements directly against a rigid internal enumerator array. If a user attempts to define a resource utilizing impossible logic—such as evaluating grain in liters—the process algorithm instantly throws a system error. By explicitly rejecting unmapped or structurally compromised data models, the sequence preserves deep systemic relational database integrity. Consequently, calculations proceeding sequentially downstream will never encounter fatal mathematical processing crashes.

### Figure 2.2.2 Check Database Duplicates
* **Process Level:** Level 3 Logic Sequence
* **Data Flow:** Inbound [Fingerprint Identification String] ➔ Outbound [Quantitative Match Query Matrix] reading directly from Data Store (D5).
* **Description:** Deployed effectively as the system's absolute anti-fraud countermeasure, this granular sequence performs real-time relational entity matching. It actively projects executing queries aggressively traversing the D5 target namespace, meticulously scanning tens of thousands of previously validated identity string structures. By seeking exact string matches focusing intimately on government identification sequences, it effectively builds a similarity calculation probability limit. Outputting this raw mathematical integer enables downstream termination locks to trigger flawlessly protecting the storage drives.

### Figure 3.2.2 Intersect Beneficiary Pool
* **Process Level:** Level 3 Logic Sequence
* **Data Flow:** Inbound [Active D5 Profile Array] intersecting with [D7 Target Bounds] ➔ Outbound [Filtered Eligible Float Matrix]
* **Description:** Widely recognized as the single most graphically intense algorithmic processing loop within the core architecture, this sequence iteratively manipulates database views. It dynamically runs constant execution intersections forcing every explicit demographic factor directly against previously defined bounding box programmatic parameters. As the sequence traverses the collected registry arrays, it explicitly drops any node falling marginally outside the defined thresholds seamlessly. This incredibly aggressive elimination loop culminates by successfully yielding a purely filtered subset matrix consisting entirely of valid identifiers.

### Figure 4.1.2 Summatic DB Join
* **Process Level:** Level 3 Logic Sequence
* **Data Flow:** Inbound [Individual System Storage Tables] ➔ Outbound [Raw Unformatted Multi-Dimensional Relational Dataset]
* **Description:** Leveraging natively optimized Laravel Eloquent Object-Relational Mapping sequences, this computational structure forces fragmented external layers together. Operating entirely within logical memory layers, the node bridges multiple separate internal repository structures spanning D5 boundaries directly over D8 structures. Utilizing intricate foreign-key linkages, it physically collapses otherwise unconnected variables into highly dense and strictly continuous dataset mappings. This atomic join eliminates database latency and subsequently produces the perfect structural foundation required by volumetric aggregation functions.

### Figure 5.4.1 Parse Webhook Auth
* **Process Level:** Level 3 Logic Sequence
* **Data Flow:** Inbound [Bare HTTP Network Post] ➔ Outbound [Decrypted Authenticated Variable Route Objects]
* **Description:** Implementing incredibly sophisticated payload decryption methodologies, this node guards internal servers from manipulated telecommunication routing requests. Continuously receiving scattered asynchronous transmission pings initiated by the integrated external APIs, it systematically breaks open the HTTPS packet structures. The verification sequence rigorously challenges encrypted network payloads against locally bound server secrets explicitly checking cryptographic authenticity loops natively. Validating the structural signatures enables the processor to accurately separate spoofed data drops from genuine execution status return routes.

---
<div style="page-break-after: always;"></div>

# LEVEL 4: EXECUTION TERMINATIONS
**Overview:** The final computational limitations. Level 4 represents the exact locations where flows irreversibly stop progressing and terminate abruptly in procedural failure, or by permanently committing strings into the relational Data Stores.

### Figure 1.4.1.1 Resource INSERT Engine
* **Process Level:** Level 4 Terminal Execution
* **Data Flow:** Inbound [Verified Item Enum Constraints] ➔ Outbound [Irreversible Physical Memory Write] natively altering Data Store (D4 resource_types).
* **Description:** Recognized structurally as the absolute completion boundary for dictionary building limits, this sequence dictates hard drives physically. It successfully executes the direct data binding forcing perfectly evaluated JSON structural variables strictly against hardcoded predefined immutable SQL relational limits. Upon finalizing the variable mappings effectively, it decisively executes a wholly irreversible transactional `INSERT` command deeply embedded directly onto the primary application disk arrays natively updating Data Store (D4).

### Figure 2.2.2.1 Duplicate Rejection Lock
* **Process Level:** Level 4 Terminal Execution
* **Data Flow:** Inbound [Match Return Value Exceeding Threshold] ➔ Outbound [Terminal Exception Feedback Signal] terminating linear logic routes natively.
* **Description:** Characterized as a catastrophic algorithmic blockade, this termination directly controls active progression routes intelligently. By continuously scraping incoming integer variables natively derived from preliminary similarity check comparisons it enforces boolean gate controls effortlessly. Whenever calculated return counts surpass the absolute baseline constraint metric (`> 0`), the logical boundary violently rejects processing structures immediately severing transmission. It permanently seals the data pathway, ejecting the broken queue natively and forcing immediate terminal error exception warnings across the staff user interface completely halting structural flows.

### Figure 2.4.1.1 Profile INSERT Engine
* **Process Level:** Level 4 Terminal Execution
* **Data Flow:** Inbound [Completely Vetted Parametric Bindings] ➔ Outbound [Immediate Row Execution Limit] committed irreplaceably directly inside Data Store (D5 beneficiaries).
* **Description:** Functioning natively as the ultimate endpoint ending operational intake logic linearly, this engine officially closes all open routing schemas manually. It exclusively binds and commits fully formed internal data layouts perfectly to mimic the rigorous external physical drive requirements flawlessly. Furthermore, the termination mechanism successfully interpolates mandatory dynamic timestamps directly into the target variables locking execution dates structurally. Driving these completely validated structures physically onto local Data Store (D5) sectors permanently cements the explicit string data inside the operational warehouse structure natively securing the pipeline continuously.

### Figure 3.3.1.1 Allocation Batch Runner
* **Process Level:** Level 4 Terminal Execution
* **Data Flow:** Inbound [Compiled Programmatic Batch Ledger] ➔ Outbound [Bulk Raw Array Transaction Execution] forcibly injected into Data Store (D8 allocations).
* **Description:** Specifically engineered natively to overwrite traditional processing delay matrices efficiently, this high-velocity terminal explicitly bypasses singular looping paradigms effortlessly. Targeting incredibly large volumes spanning tens of thousands of allocation paths it selectively deploys massive raw vectorized SQL insertion chains continually. By purposefully dismissing granular transactional event triggers entirely it aggressively optimizes memory allocation dramatically reducing expected execution latency limitations seamlessly. Submitting this massively aggregated execution block forces definitive finalized relational logic strings deep inside Data Store (D8) natively cementing legal resource obligations completely.

### Figure 6.2.1.1 Disk Cache Yield
* **Process Level:** Level 4 Terminal Execution
* **Data Flow:** Inbound [Complex Output Mapping Matrix] ➔ Outbound [Hard Drive String Output Blob] written destructively against standard Data Store (D12 geo_cache) mapping sectors.
* **Description:** Terminating highly intensive regional volumetric evaluations natively, this yielding process physically drops algorithmic artifacts into functional existence effectively. It resolves ongoing structural interpolations finalizing the explicit calculation matrices flawlessly capturing output values entirely before wiping virtual system memory seamlessly. The execution sequence translates purely abstract array lists fundamentally yielding massive dense localized file system elements encoded using static `.json` structural blocks consistently. Terminating native logic boundaries permanently stores these physical geometries forcefully onto standard server nodes locking active spatial logic directly within Data Store (D12).
