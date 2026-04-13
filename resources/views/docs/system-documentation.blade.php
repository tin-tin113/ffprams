<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FFPRAMS System Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            text-align: justify;
        }

        .page-break {
            page-break-after: always;
            margin-top: 40px;
        }

        .page-break-before {
            page-break-before: always;
        }

        /* Cover Page */
        .cover-page {
            text-align: center;
            padding-top: 120px;
            height: 100%;
        }

        .cover-page h1 {
            font-size: 32pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .cover-page .subtitle {
            font-size: 16pt;
            margin-bottom: 40px;
            font-style: italic;
        }

        .cover-page .description {
            font-size: 12pt;
            margin-bottom: 60px;
            line-height: 1.6;
        }

        .cover-page .footer {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
        }

        /* Section Headers */
        h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 12pt;
            color: #000;
            border-bottom: 2pt solid #000;
            padding-bottom: 6pt;
        }

        h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 16pt;
            margin-bottom: 10pt;
            color: #000;
        }

        h3 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 12pt;
            margin-bottom: 8pt;
            color: #000;
        }

        p {
            margin-bottom: 8pt;
            text-align: justify;
        }

        /* Tables - Core Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12pt 0;
            page-break-inside: avoid;
            font-size: 9pt;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10pt 0;
            font-size: 9pt;
        }

        thead {
            background-color: #f0f0f0;
        }

        th {
            background-color: #e0e0e0;
            border: 1pt solid #999;
            padding: 6pt 8pt;
            text-align: left;
            font-weight: bold;
            color: #000;
            font-size: 9pt;
        }

        td {
            border: 1pt solid #ccc;
            padding: 5pt 8pt;
            vertical-align: top;
            word-wrap: break-word;
            font-size: 8.5pt;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Narrow column tables */
        table.narrow-cols {
            font-size: 8pt;
        }

        table.narrow-cols th,
        table.narrow-cols td {
            padding: 4pt 6pt;
            font-size: 8pt;
        }

        /* Two-column tables */
        table.two-col {
            width: 100%;
            font-size: 9pt;
        }

        table.two-col th,
        table.two-col td {
            padding: 6pt 8pt;
            font-size: 8.5pt;
        }

        /* Column width classes */
        .col-20 { width: 20%; }
        .col-25 { width: 25%; }
        .col-30 { width: 30%; }
        .col-40 { width: 40%; }
        .col-50 { width: 50%; }
        .col-auto { width: auto; }

        /* Lists */
        ul, ol {
            margin-left: 20pt;
            margin-bottom: 8pt;
        }

        li {
            margin-bottom: 4pt;
            text-align: justify;
        }

        /* Box styling */
        .info-box {
            background-color: #f5f5f5;
            border: 1pt solid #999;
            padding: 10pt;
            margin: 12pt 0;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .info-box strong {
            font-weight: bold;
        }

        /* Table of Contents */
        .toc {
            margin-top: 20pt;
            font-size: 10pt;
        }

        .toc ul {
            list-style: none;
            margin-left: 0;
        }

        .toc li {
            margin-bottom: 6pt;
            text-align: left;
        }

        .toc li.level1 {
            margin-left: 0;
            font-weight: bold;
            margin-top: 8pt;
        }

        .toc li.level2 {
            margin-left: 20pt;
        }

        /* Header/Footer area */
        .header {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12pt;
            border-bottom: 1pt solid #999;
            padding-bottom: 6pt;
        }

        /* Dividers */
        hr {
            border: none;
            border-top: 1pt solid #999;
            margin: 16pt 0;
        }

        /* Code/Reference blocks */
        .code-block {
            background-color: #f0f0f0;
            border: 1pt solid #999;
            padding: 8pt;
            font-family: monospace;
            font-size: 8pt;
            overflow-x: auto;
            margin: 10pt 0;
            page-break-inside: avoid;
        }

        /* Feature list */
        .feature-list {
            margin-left: 0;
        }

        .feature-list li {
            list-style: disc;
            margin-left: 20pt;
            margin-bottom: 4pt;
        }

        /* Multi-column text */
        .column-2 {
            column-count: 2;
            column-gap: 15pt;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            table {
                page-break-inside: avoid;
            }

            tr {
                page-break-inside: avoid;
            }

            h1, h2, h3 {
                page-break-after: avoid;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

<!-- COVER PAGE -->
<div class="cover-page">
    <h1>FFPRAMS</h1>
    <div class="subtitle">Farmer-Fisherfolk Precision Resource Allocation Management System</div>
    <div class="description">
        <strong>Complete System Documentation</strong><br>
        Municipal Agriculture Office<br>
        Enrique B. Magalona, Negros Occidental<br>
        Philippines
    </div>
    <div class="footer">
        <p>Version 1.0</p>
        <p>Generated: {{ date('F d, Y') }}</p>
    </div>
</div>

<div class="page-break"></div>

<!-- TABLE OF CONTENTS -->
<h1>Table of Contents</h1>
<div class="toc">
    <ul>
        <li class="level1">1. Project Overview</li>
        <li class="level1">2. System Architecture</li>
        <li class="level1">3. Core Features</li>
        <li class="level1">4. Database Schema Reference</li>
        <li class="level1">5. System Settings (Agencies, Programs, Resources)</li>
        <li class="level1">6. Forms Documentation</li>
        <li class="level1">7. API Endpoints</li>
        <li class="level1">8. User Roles and Permissions</li>
    </ul>
</div>

<div class="page-break"></div>

<!-- SECTION 1: PROJECT OVERVIEW -->
<h1>1. Project Overview</h1>

<h2>Purpose</h2>
<p>FFPRAMS is a web-based management system designed specifically for the Municipal Agriculture Office (MAO) of Enrique B. Magalona, Negros Occidental. The system streamlines the registration of farmer and fisherfolk beneficiaries, manages resource distribution events, handles financial assistance allocations, enables SMS notifications, and provides geographic mapping with comprehensive reporting capabilities.</p>

<h2>Target Users</h2>
<ul class="feature-list">
    <li>LGU Admin: System administration, user management, system settings</li>
    <li>MAO Staff: Operational modules, beneficiary management, allocations</li>
    <li>Beneficiaries: SMS notifications regarding allocations and events</li>
    <li>National Partners: Integration with E4 (external entity) for program data</li>
</ul>

<h2>Geographic Coverage</h2>
<p>The system covers all 23 barangays of Enrique B. Magalona:</p>
<p style="font-size: 9pt; line-height: 1.5;">Alacaygan, Alicante, Batea, Canlusong, Consing, Cudangdang, Damgo, Gahit, Latasan, Madalag, Manta-angan, Nanca, Pasil, Poblacion I, Poblacion II, Poblacion III, San Isidro, San Jose, Santo Nino, Tabigue, Tanza, Tomongtong, Tuburan</p>

<h2>Technical Stack</h2>
<table class="data-table">
    <thead>
        <tr>
            <th class="col-25">Component</th>
            <th class="col-50">Technology</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Backend Framework</strong></td>
            <td>PHP 8.2+ / Laravel 12</td>
        </tr>
        <tr>
            <td><strong>Frontend Framework</strong></td>
            <td>Bootstrap 5.3.3, Alpine.js 3, Vite 7</td>
        </tr>
        <tr>
            <td><strong>Database</strong></td>
            <td>MySQL</td>
        </tr>
        <tr>
            <td><strong>Geographic Mapping</strong></td>
            <td>Leaflet.js with OpenStreetMap tiles</td>
        </tr>
        <tr>
            <td><strong>SMS Gateway</strong></td>
            <td>Semaphore-compatible API</td>
        </tr>
        <tr>
            <td><strong>Authentication</strong></td>
            <td>Laravel Breeze</td>
        </tr>
        <tr>
            <td><strong>PDF Generation</strong></td>
            <td>DOMPDF via laravel-dompdf</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 2: SYSTEM ARCHITECTURE -->
<h1>2. System Architecture</h1>

<h2>DFD Processes (6 Main Operational Flows)</h2>
<table class="data-table">
    <thead>
        <tr>
            <th class="col-20">Process ID</th>
            <th class="col-40">Name</th>
            <th class="col-40">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>1.0</strong></td>
            <td>Register Beneficiary</td>
            <td>Record farmer/fisherfolk information with agency-specific fields (DA/RSBSA, BFAR/FishR, DAR/CARP)</td>
        </tr>
        <tr>
            <td><strong>2.0</strong></td>
            <td>Create Distribution Event</td>
            <td>Establish physical or financial resource distribution events for specific barangays</td>
        </tr>
        <tr>
            <td><strong>3.0</strong></td>
            <td>Allocate Resources</td>
            <td>Assign resources or funds to beneficiaries from distribution events or direct assistance</td>
        </tr>
        <tr>
            <td><strong>4.0</strong></td>
            <td>Send SMS Notifications</td>
            <td>Broadcast or individual SMS messages to beneficiaries regarding registrations and allocations</td>
        </tr>
        <tr>
            <td><strong>5.0</strong></td>
            <td>View Geographic Distribution</td>
            <td>Interactive map showing distribution status across all 23 barangays</td>
        </tr>
        <tr>
            <td><strong>6.0</strong></td>
            <td>Generate Reports</td>
            <td>Eight different report types for beneficiary, resource, and financial analysis</td>
        </tr>
    </tbody>
</table>

<h2>External Entities</h2>
<table class="data-table">
    <thead>
        <tr>
            <th class="col-20">Entity ID</th>
            <th class="col-30">Name</th>
            <th class="col-50">Role</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>E1</strong></td>
            <td>LGU Admin</td>
            <td>System administrator, user and settings management</td>
        </tr>
        <tr>
            <td><strong>E2</strong></td>
            <td>MAO Staff</td>
            <td>Operational staff handling beneficiary and allocation records</td>
        </tr>
        <tr>
            <td><strong>E3</strong></td>
            <td>Farmers/Fisherfolk/ARBs</td>
            <td>Beneficiary recipients of assistance</td>
        </tr>
        <tr>
            <td><strong>E4</strong></td>
            <td>National Partners</td>
            <td>External program data integration point</td>
        </tr>
        <tr>
            <td><strong>E5</strong></td>
            <td>SMS Gateway</td>
            <td>Semaphore API for message delivery</td>
        </tr>
    </tbody>
</table>

<h2>Core Application Modules</h2>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-30">Module</th>
            <th class="col-70">Purpose</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Dashboard</strong></td>
            <td>Overview metrics and quick access to main functions</td>
        </tr>
        <tr>
            <td><strong>Beneficiaries</strong></td>
            <td>CRUD operations, duplicate detection, agency-based field sets</td>
        </tr>
        <tr>
            <td><strong>Distribution Events</strong></td>
            <td>Create and manage physical/financial events</td>
        </tr>
        <tr>
            <td><strong>Allocations</strong></td>
            <td>Single and bulk resource/fund assignments</td>
        </tr>
        <tr>
            <td><strong>SMS</strong></td>
            <td>Broadcasting and delivery logs</td>
        </tr>
        <tr>
            <td><strong>Geographic Map</strong></td>
            <td>Leaflet-based interactive map with barangay distribution status</td>
        </tr>
        <tr>
            <td><strong>Reports</strong></td>
            <td>Eight predefined report types for analysis</td>
        </tr>
        <tr>
            <td><strong>Admin Settings</strong></td>
            <td>Agencies, programs, resources, purposes, form field options</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 3: CORE FEATURES -->
<h1>3. Core Features</h1>

<h2>3.1 Beneficiary Management</h2>

<h3>Classification System (Strict Mode)</h3>
<p>Beneficiaries are classified as either <strong>Farmer</strong> or <strong>Fisherfolk</strong> (no "Both" classification). This strict classification ensures proper program eligibility:</p>
<ul class="feature-list">
    <li><strong>Farmer:</strong> Agricultural workers registered under DA (Department of Agriculture) or DAR (Department of Agrarian Reform)</li>
    <li><strong>Fisherfolk:</strong> Fishery workers registered under BFAR (Bureau of Fisheries and Aquatic Resources)</li>
    <li><strong>Multi-Agency:</strong> A single beneficiary can be registered with multiple agencies (DA+BFAR, for example)</li>
</ul>

<h3>Agency-Specific Field Sets</h3>
<p>Form fields change based on selected agency:</p>
<ul class="feature-list">
    <li><strong>DA/RSBSA:</strong> RSBSA number, farm size, crop types, equipment</li>
    <li><strong>BFAR/FishR:</strong> FishR number, fishing gear, fishing ground, vessel details</li>
    <li><strong>DAR/CARP:</strong> CLOA/EP number (mandatory before save), land size, crop details</li>
</ul>

<h3>Duplicate Detection</h3>
<p>Automatic system that blocks registration if matching records are found (prevents accidental duplicate entries).</p>

<h3>Beneficiary Search and Filters</h3>
<p>Staff can filter by: barangay, agency, classification, status, or search by name/contact number.</p>

<h2>3.2 Program Classification System</h2>

<h3>Classification Matching Rules</h3>
<p>Each program is classified for access by specific beneficiary types:</p>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Program Type</th>
            <th class="col-75">Eligible Beneficiaries</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Farmer Program</strong></td>
            <td>Only beneficiaries with Farmer classification</td>
        </tr>
        <tr>
            <td><strong>Fisherfolk Program</strong></td>
            <td>Only beneficiaries with Fisherfolk classification</td>
        </tr>
        <tr>
            <td><strong>Both Program</strong></td>
            <td>All beneficiaries (Farmer, Fisherfolk, or Both)</td>
        </tr>
    </tbody>
</table>

<h3>Multi-Point Validation</h3>
<p>Classification eligibility is enforced at 4 levels:</p>
<ol>
    <li><strong>Frontend:</strong> Program dropdown filtered by beneficiary classification on selection</li>
    <li><strong>Form Validation:</strong> AllocationRequest and DirectAssistanceStoreRequest check eligibility</li>
    <li><strong>Controller:</strong> store(), storeBulk(), storeDirectBatch() methods validate per-row</li>
    <li><strong>Database:</strong> Enum columns enforce valid classifications</li>
</ol>

<h2>3.3 Distribution Events</h2>

<h3>Event Types</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Type</th>
            <th class="col-75">Characteristics</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Physical</strong></td>
            <td>Quantified resources (bags, units, pieces) distributed to beneficiaries</td>
        </tr>
        <tr>
            <td><strong>Financial</strong></td>
            <td>Monetary amounts (cash assistance) distributed to beneficiaries</td>
        </tr>
    </tbody>
</table>

<h3>Event Status Workflow</h3>
<p>Events follow this progression: <strong>Pending</strong> → <strong>Ongoing</strong> → <strong>Completed</strong></p>

<h3>Event Properties</h3>
<ul class="feature-list">
    <li>Linked to specific barangay</li>
    <li>Linked to resource/fund type</li>
    <li>Total fund tracking (financial events)</li>
    <li>Timestamp tracking for status transitions</li>
    <li>Beneficiary validation (barangay match required)</li>
</ul>

<h2>3.4 Smart Beneficiary Search for Direct Assistance</h2>

<h3>Features</h3>
<ul class="feature-list">
    <li><strong>Live Search:</strong> Type name or phone number with 300ms debounce</li>
    <li><strong>Quick Filters:</strong> Filter by barangay and/or classification</li>
    <li><strong>Result Display:</strong> Shows up to 20 matching beneficiaries with classification badge</li>
    <li><strong>Instant Selection:</strong> Click to select beneficiary; updates form immediately</li>
    <li><strong>Change Option:</strong> Easy switching without closing form</li>
</ul>

<h2>3.5 SMS Notifications</h2>

<h3>Notification Types</h3>
<ul class="feature-list">
    <li><strong>Automatic:</strong> Sent on beneficiary registration and allocation events</li>
    <li><strong>Broadcast:</strong> Manual messaging to all active beneficiaries</li>
    <li><strong>Targeted:</strong> Target specific groups by barangay, classification, or individual selection</li>
</ul>

<h3>SMS Log Features</h3>
<ul class="feature-list">
    <li>Full delivery status tracking</li>
    <li>Preview recipients before sending</li>
    <li>Timestamp and message content logging</li>
</ul>

<h2>3.6 Geographic Map (GeoMap)</h2>

<h3>Interactive Map Features</h3>
<ul class="feature-list">
    <li>Leaflet.js-based map showing all 23 barangays</li>
    <li>Color-coded pins by distribution status</li>
    <li>Detailed per-barangay information panel</li>
    <li>OpenStreetMap tile integration</li>
</ul>

<h3>Per-Barangay Data Panel</h3>
<p>Shows breakdown of:</p>
<ul>
    <li>Beneficiary counts (total, farmer, fisherfolk, both)</li>
    <li>Household data and event counts</li>
    <li>Allocation statistics and coverage rate</li>
    <li>Financial totals and resource types distributed</li>
    <li>Distribution timeline</li>
</ul>

<h2>3.7 Reporting</h2>

<h3>Eight Report Types</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-10">#</th>
            <th class="col-40">Report Name</th>
            <th class="col-50">Information Provided</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Beneficiaries per Barangay</td>
            <td>Farmer/fisherfolk/both breakdown by location</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Resource Distribution Summary</td>
            <td>Resources distributed, quantities, recipient counts</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Distribution Status per Barangay</td>
            <td>Completed/ongoing/pending status by location</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Unreached Beneficiaries</td>
            <td>Beneficiaries with zero allocations</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Monthly Distribution Summary</td>
            <td>Month-by-month activity overview</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Financial Assistance Summary by Resource Type</td>
            <td>Fund distribution by resource type</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Financial Assistance per Barangay</td>
            <td>Monetary assistance breakdown by location</td>
        </tr>
        <tr>
            <td>8</td>
            <td>Financial Assistance Distribution by Purpose</td>
            <td>Fund allocation by assistance purpose category</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 4: DATABASE SCHEMA -->
<h1>4. Database Schema Reference</h1>

<h2>4.1 Core Data Models</h2>

<p><strong>Total Data Stores:</strong> 13 main tables supporting the information architecture</p>

<h3>Users Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key, auto-increment</td>
        </tr>
        <tr>
            <td>name</td>
            <td>VARCHAR(255)</td>
            <td>Full name of user</td>
        </tr>
        <tr>
            <td>email</td>
            <td>VARCHAR(255) UQ</td>
            <td>User email, unique</td>
        </tr>
        <tr>
            <td>role</td>
            <td>ENUM</td>
            <td>User role: 'admin' or 'staff'</td>
        </tr>
        <tr>
            <td>password</td>
            <td>VARCHAR(255)</td>
            <td>Hashed password</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h3>Beneficiaries Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key, auto-increment</td>
        </tr>
        <tr>
            <td>full_name</td>
            <td>VARCHAR(255)</td>
            <td>Full name of beneficiary</td>
        </tr>
        <tr>
            <td>contact_number</td>
            <td>VARCHAR(20)</td>
            <td>Mobile/phone number for SMS</td>
        </tr>
        <tr>
            <td>classification</td>
            <td>ENUM</td>
            <td>'Farmer' or 'Fisherfolk' (strict)</td>
        </tr>
        <tr>
            <td>barangay_id</td>
            <td>BIGINT FK</td>
            <td>Reference to barangay location</td>
        </tr>
        <tr>
            <td>status</td>
            <td>ENUM</td>
            <td>'active' or 'inactive'</td>
        </tr>
        <tr>
            <td>is_household_head</td>
            <td>BOOLEAN</td>
            <td>Whether beneficiary is household head</td>
        </tr>
        <tr>
            <td>household_id</td>
            <td>VARCHAR(20)</td>
            <td>Unique household identifier</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Registration timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
        <tr>
            <td>deleted_at</td>
            <td>TIMESTAMP NULL</td>
            <td>Soft delete timestamp (for duplicate detection)</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h3>Agencies Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>code</td>
            <td>VARCHAR(10) UQ</td>
            <td>Agency code (e.g., 'DA', 'BFAR', 'DAR')</td>
        </tr>
        <tr>
            <td>name</td>
            <td>VARCHAR(255)</td>
            <td>Full agency name</td>
        </tr>
        <tr>
            <td>description</td>
            <td>TEXT</td>
            <td>Agency description and purpose</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<h3>Barangays Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>name</td>
            <td>VARCHAR(255) UQ</td>
            <td>Barangay name (23 total in municipality)</td>
        </tr>
        <tr>
            <td>latitude</td>
            <td>DECIMAL(10,7)</td>
            <td>GPS latitude for map display</td>
        </tr>
        <tr>
            <td>longitude</td>
            <td>DECIMAL(10,7)</td>
            <td>GPS longitude for map display</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h3>ProgramNames Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>program_name</td>
            <td>VARCHAR(255) UQ</td>
            <td>Program name (must be unique)</td>
        </tr>
        <tr>
            <td>classification</td>
            <td>ENUM</td>
            <td>'Farmer', 'Fisherfolk', or 'Both'</td>
        </tr>
        <tr>
            <td>agency_id</td>
            <td>BIGINT FK</td>
            <td>Associated agency</td>
        </tr>
        <tr>
            <td>description</td>
            <td>TEXT</td>
            <td>Program description and objectives</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<h3>ResourceTypes Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>name</td>
            <td>VARCHAR(255) UQ</td>
            <td>Resource/fund type name (unique)</td>
        </tr>
        <tr>
            <td>type</td>
            <td>ENUM</td>
            <td>'Physical' or 'Financial'</td>
        </tr>
        <tr>
            <td>agency_id</td>
            <td>BIGINT FK</td>
            <td>Associated agency</td>
        </tr>
        <tr>
            <td>unit</td>
            <td>VARCHAR(50)</td>
            <td>Unit of measurement (e.g., 'kg', 'bag', 'peso')</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h3>DistributionEvents Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>event_name</td>
            <td>VARCHAR(255)</td>
            <td>Name of distribution event</td>
        </tr>
        <tr>
            <td>type</td>
            <td>ENUM</td>
            <td>'Physical' or 'Financial'</td>
        </tr>
        <tr>
            <td>barangay_id</td>
            <td>BIGINT FK</td>
            <td>Event location</td>
        </tr>
        <tr>
            <td>resource_type_id</td>
            <td>BIGINT FK</td>
            <td>Type of resource being distributed</td>
        </tr>
        <tr>
            <td>status</td>
            <td>ENUM</td>
            <td>'Pending', 'Ongoing', or 'Completed'</td>
        </tr>
        <tr>
            <td>total_fund</td>
            <td>DECIMAL(12,2) NULL</td>
            <td>Total fund amount (financial events only)</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Event creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<h3>Allocations Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>beneficiary_id</td>
            <td>BIGINT FK</td>
            <td>Recipient beneficiary</td>
        </tr>
        <tr>
            <td>distribution_event_id</td>
            <td>BIGINT FK NULL</td>
            <td>Event source (null for direct assistance)</td>
        </tr>
        <tr>
            <td>resource_type_id</td>
            <td>BIGINT FK</td>
            <td>Resource/fund type allocated</td>
        </tr>
        <tr>
            <td>program_id</td>
            <td>BIGINT FK</td>
            <td>Program under which allocation made</td>
        </tr>
        <tr>
            <td>quantity</td>
            <td>DECIMAL(10,2)</td>
            <td>Amount/quantity allocated</td>
        </tr>
        <tr>
            <td>is_distributed</td>
            <td>BOOLEAN</td>
            <td>Whether allocation has been distributed</td>
        </tr>
        <tr>
            <td>distributed_at</td>
            <td>TIMESTAMP NULL</td>
            <td>Timestamp when distribution marked complete</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Allocation creation timestamp</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h3>AssistancePurposes Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>name</td>
            <td>VARCHAR(255) UQ</td>
            <td>Assistance purpose name (unique)</td>
        </tr>
        <tr>
            <td>category</td>
            <td>VARCHAR(100)</td>
            <td>Category grouping (agricultural, fishery, livelihood, etc.)</td>
        </tr>
        <tr>
            <td>description</td>
            <td>TEXT</td>
            <td>Detailed description of purpose</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Record creation timestamp</td>
        </tr>
        <tr>
            <td>updated_at</td>
            <td>TIMESTAMP</td>
            <td>Last update timestamp</td>
        </tr>
    </tbody>
</table>

<h3>SmsLogs Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>beneficiary_id</td>
            <td>BIGINT FK NULL</td>
            <td>Recipient (null for broadcast)</td>
        </tr>
        <tr>
            <td>phone_number</td>
            <td>VARCHAR(20)</td>
            <td>Phone number message sent to</td>
        </tr>
        <tr>
            <td>message</td>
            <td>TEXT</td>
            <td>SMS message content</td>
        </tr>
        <tr>
            <td>status</td>
            <td>ENUM</td>
            <td>'sent', 'delivered', 'failed', etc.</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Message sent timestamp</td>
        </tr>
    </tbody>
</table>

<h3>AuditLogs Table</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-20">Type</th>
            <th class="col-55">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>id</td>
            <td>BIGINT PK</td>
            <td>Primary key</td>
        </tr>
        <tr>
            <td>user_id</td>
            <td>BIGINT FK</td>
            <td>User performing action</td>
        </tr>
        <tr>
            <td>action</td>
            <td>VARCHAR(50)</td>
            <td>Action type (create, update, delete, etc.)</td>
        </tr>
        <tr>
            <td>table_name</td>
            <td>VARCHAR(100)</td>
            <td>Table affected</td>
        </tr>
        <tr>
            <td>record_id</td>
            <td>BIGINT</td>
            <td>ID of affected record</td>
        </tr>
        <tr>
            <td>old_values</td>
            <td>JSON</td>
            <td>Previous values (for updates)</td>
        </tr>
        <tr>
            <td>new_values</td>
            <td>JSON</td>
            <td>New values (for updates)</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>TIMESTAMP</td>
            <td>Action timestamp</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 5: SYSTEM SETTINGS -->
<h1>5. System Settings (Agencies, Programs, Resources)</h1>

<h2>5.1 Agencies</h2>

<p>Three core agencies managed through the system:</p>

<table class="data-table">
    <thead>
        <tr>
            <th class="col-15">Code</th>
            <th class="col-35">Full Name</th>
            <th class="col-50">Role in System</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>DA</strong></td>
            <td>Department of Agriculture</td>
            <td>Manages agricultural assistance programs and resources</td>
        </tr>
        <tr>
            <td><strong>BFAR</strong></td>
            <td>Bureau of Fisheries and Aquatic Resources</td>
            <td>Manages fishery assistance programs and resources</td>
        </tr>
        <tr>
            <td><strong>DAR</strong></td>
            <td>Department of Agrarian Reform</td>
            <td>Manages land reform and CARP beneficiary programs</td>
        </tr>
    </tbody>
</table>

<h2>5.2 Programs (Program Names)</h2>

<p>Currently configured programs: {{ count($programs) }} total</p>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-30">Program Name</th>
            <th class="col-15">Classification</th>
            <th class="col-20">Agency</th>
            <th class="col-35">Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($programs as $program)
        <tr>
            <td><strong>{{ $program->program_name }}</strong></td>
            <td>{{ $program->classification }}</td>
            <td>{{ $program->agency->code ?? 'N/A' }}</td>
            <td>{{ Str::limit($program->description, 40) ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align: center;">No programs configured</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="page-break"></div>

<h2>5.3 Resource Types</h2>

<p>Currently configured resource types: {{ count($resourceTypes) }} total</p>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Resource Name</th>
            <th class="col-15">Type</th>
            <th class="col-15">Unit</th>
            <th class="col-20">Agency</th>
            <th class="col-25">Purpose</th>
        </tr>
    </thead>
    <tbody>
        @forelse($resourceTypes as $resource)
        <tr>
            <td><strong>{{ $resource->name }}</strong></td>
            <td>{{ $resource->type }}</td>
            <td>{{ $resource->unit ?? '-' }}</td>
            <td>{{ $resource->agency->code ?? 'N/A' }}</td>
            <td>Allocation of {{ $resource->type === 'Financial' ? 'monetary' : 'physical' }} resources</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align: center;">No resource types configured</td>
        </tr>
        @endforelse
    </tbody>
</table>

<h2>5.4 Assistance Purposes</h2>

<p>Currently configured assistance purposes: {{ count($purposes) }} total</p>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Purpose Name</th>
            <th class="col-25">Category</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purposes as $purpose)
        <tr>
            <td><strong>{{ $purpose->name }}</strong></td>
            <td>{{ $purpose->category }}</td>
            <td>{{ Str::limit($purpose->description, 60) ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="text-align: center;">No assistance purposes configured</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 6: FORMS DOCUMENTATION -->
<h1>6. Forms Documentation</h1>

<h2>6.1 Beneficiary Registration Form</h2>

<h3>Common Fields (All Beneficiaries)</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-15">Type</th>
            <th class="col-15">Required</th>
            <th class="col-45">Notes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Full Name</td>
            <td>Text</td>
            <td>Yes</td>
            <td>Complete name of beneficiary</td>
        </tr>
        <tr>
            <td>Contact Number</td>
            <td>Phone</td>
            <td>Yes</td>
            <td>For SMS communication</td>
        </tr>
        <tr>
            <td>Classification</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Farmer or Fisherfolk (strict)</td>
        </tr>
        <tr>
            <td>Barangay</td>
            <td>Select</td>
            <td>Yes</td>
            <td>One of 23 barangays</td>
        </tr>
        <tr>
            <td>Is Household Head</td>
            <td>Checkbox</td>
            <td>No</td>
            <td>Mark if household representative</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Active or Inactive</td>
        </tr>
    </tbody>
</table>

<h3>Agency-Specific Fields</h3>

<h4>DA/RSBSA Fields (Agriculture)</h4>
<ul class="feature-list">
    <li>RSBSA Number (text, optional)</li>
    <li>Farm Size (numeric, hectares)</li>
    <li>Crop Types (multi-select dropdown)</li>
    <li>Equipment Owned (text area)</li>
    <li>Farming Method (select: traditional, organic, mechanized)</li>
</ul>

<h4>BFAR/FishR Fields (Fishery)</h4>
<ul class="feature-list">
    <li>FishR Number (text, optional)</li>
    <li>Fishing Gear Type (multi-select)</li>
    <li>Fishing Ground (text)</li>
    <li>Vessel Details (text area)</li>
    <li>Years in Fishing (numeric)</li>
</ul>

<h4>DAR/CARP Fields (Land Reform)</h4>
<ul class="feature-list">
    <li>CLOA/EP Number (text, <strong>required before save</strong>)</li>
    <li>Land Size (numeric, hectares)</li>
    <li>Crop Details (text area)</li>
    <li>Land Status (select: titled, pending, etc.)</li>
    <li>Co-Farmers (multi-select)</li>
</ul>

<h2>6.2 Distribution Event Form</h2>

<h3>Event Details</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-15">Type</th>
            <th class="col-15">Required</th>
            <th class="col-45">Notes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Event Name</td>
            <td>Text</td>
            <td>Yes</td>
            <td>Descriptive name for event</td>
        </tr>
        <tr>
            <td>Event Type</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Physical or Financial</td>
        </tr>
        <tr>
            <td>Barangay</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Event location</td>
        </tr>
        <tr>
            <td>Resource Type</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Resource being distributed</td>
        </tr>
        <tr>
            <td>Total Fund</td>
            <td>Decimal</td>
            <td>Conditional</td>
            <td>Required for financial events only</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h2>6.3 Allocation Form</h2>

<h3>Single/Direct Allocation</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-15">Type</th>
            <th class="col-15">Required</th>
            <th class="col-45">Notes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Beneficiary</td>
            <td>Search/Select</td>
            <td>Yes</td>
            <td>Smart search enabled; shows eligibility</td>
        </tr>
        <tr>
            <td>Program</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Auto-filtered by beneficiary classification</td>
        </tr>
        <tr>
            <td>Distribution Event</td>
            <td>Select</td>
            <td>Conditional</td>
            <td>Required for event-based allocation</td>
        </tr>
        <tr>
            <td>Resource Type</td>
            <td>Select</td>
            <td>Yes</td>
            <td>Auto-filtered by program agency</td>
        </tr>
        <tr>
            <td>Quantity</td>
            <td>Decimal</td>
            <td>Yes</td>
            <td>Amount or number to allocate</td>
        </tr>
    </tbody>
</table>

<h3>Bulk Allocation</h3>
<p>Accepts CSV file with columns: Beneficiary ID, Program, Resource Type, Quantity</p>
<ul class="feature-list">
    <li>Validation performed on each row</li>
    <li>Ineligible beneficiaries automatically skipped</li>
    <li>Processing summary provided after upload</li>
</ul>

<h2>6.4 SMS Broadcast Form</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-25">Field</th>
            <th class="col-15">Type</th>
            <th class="col-15">Required</th>
            <th class="col-45">Notes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Message Content</td>
            <td>Textarea</td>
            <td>Yes</td>
            <td>SMS message text</td>
        </tr>
        <tr>
            <td>Target Type</td>
            <td>Select</td>
            <td>Yes</td>
            <td>All, barangay, classification, or individual</td>
        </tr>
        <tr>
            <td>Barangay Filter</td>
            <td>Select</td>
            <td>Conditional</td>
            <td>Required if target type is barangay</td>
        </tr>
        <tr>
            <td>Classification Filter</td>
            <td>Select</td>
            <td>Conditional</td>
            <td>Required if target type is classification</td>
        </tr>
        <tr>
            <td>Selected Beneficiaries</td>
            <td>Multi-select</td>
            <td>Conditional</td>
            <td>Required if target type is individual</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 7: API ENDPOINTS -->
<h1>7. API Endpoints</h1>

<h2>7.1 Beneficiary Management</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/api/beneficiaries/search</td>
            <td>Smart search with filters (q, barangay_id, classification)</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/beneficiaries</td>
            <td>List all beneficiaries (web view)</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/beneficiaries</td>
            <td>Create new beneficiary</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/beneficiaries/{id}</td>
            <td>View beneficiary details</td>
        </tr>
        <tr>
            <td>PUT</td>
            <td>/beneficiaries/{id}</td>
            <td>Update beneficiary information</td>
        </tr>
        <tr>
            <td>DELETE</td>
            <td>/beneficiaries/{id}</td>
            <td>Soft delete beneficiary</td>
        </tr>
    </tbody>
</table>

<h2>7.2 Program Eligibility</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/api/allocations/eligible-programs/{beneficiary_id}</td>
            <td>Get programs eligible for beneficiary classification</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/api/direct-assistance/eligible-programs/{beneficiary_id}</td>
            <td>Get eligible programs for direct assistance</td>
        </tr>
    </tbody>
</table>

<h2>7.3 Resource Allocation</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/allocations</td>
            <td>List all allocations (with filters)</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/allocations</td>
            <td>Create single or event-based allocation</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/allocations/bulk</td>
            <td>Bulk upload allocations from CSV</td>
        </tr>
        <tr>
            <td>PATCH</td>
            <td>/allocations/{id}/mark-distributed</td>
            <td>Mark allocation as distributed</td>
        </tr>
        <tr>
            <td>DELETE</td>
            <td>/allocations/{id}</td>
            <td>Delete allocation (admin only)</td>
        </tr>
    </tbody>
</table>

<h2>7.4 Distribution Events</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/distribution-events</td>
            <td>List all distribution events</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/distribution-events</td>
            <td>Create new distribution event</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/distribution-events/{id}</td>
            <td>View event details</td>
        </tr>
        <tr>
            <td>PUT</td>
            <td>/distribution-events/{id}</td>
            <td>Update event information</td>
        </tr>
        <tr>
            <td>PATCH</td>
            <td>/distribution-events/{id}/complete</td>
            <td>Mark event as completed (admin only)</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<h2>7.5 SMS Management</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/sms</td>
            <td>View SMS logs with delivery status</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/sms/broadcast</td>
            <td>Send broadcast SMS message</td>
        </tr>
        <tr>
            <td>POST</td>
            <td>/sms/send</td>
            <td>Send individual SMS to beneficiary</td>
        </tr>
    </tbody>
</table>

<h2>7.6 Reports</h2>

<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-15">Method</th>
            <th class="col-35">Endpoint</th>
            <th class="col-50">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>GET</td>
            <td>/reports</td>
            <td>Reports dashboard overview</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/reports/beneficiaries-per-barangay</td>
            <td>Beneficiary distribution by location</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/reports/resource-distribution</td>
            <td>Resource distribution summary</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/reports/monthly-summary</td>
            <td>Monthly activity overview</td>
        </tr>
        <tr>
            <td>GET</td>
            <td>/reports/unreached-beneficiaries</td>
            <td>Beneficiaries with zero allocations</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 8: USER ROLES AND PERMISSIONS -->
<h1>8. User Roles and Permissions</h1>

<h2>8.1 Admin Role</h2>

<h3>Permissions</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-50">Action</th>
            <th class="col-50">Allowed</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Manage Users</td>
            <td>Yes - Create, edit, delete user accounts</td>
        </tr>
        <tr>
            <td>System Settings</td>
            <td>Yes - All configuration management</td>
        </tr>
        <tr>
            <td>Manage Agencies</td>
            <td>Yes - Create, update, manage agencies</td>
        </tr>
        <tr>
            <td>Manage Programs</td>
            <td>Yes - Create, edit programs and classifications</td>
        </tr>
        <tr>
            <td>Manage Resource Types</td>
            <td>Yes - Create, edit resource types</td>
        </tr>
        <tr>
            <td>Manage Assistance Purposes</td>
            <td>Yes - Create, edit purposes</td>
        </tr>
        <tr>
            <td>Complete Distribution Events</td>
            <td>Yes - Mark events as completed</td>
        </tr>
        <tr>
            <td>Delete Allocations</td>
            <td>Yes - Remove allocation records</td>
        </tr>
        <tr>
            <td>View Audit Logs</td>
            <td>Yes - All system activity logs</td>
        </tr>
        <tr>
            <td>Operational Modules (Beneficiary, Allocation, etc.)</td>
            <td>Limited - Read-only for audit purposes</td>
        </tr>
    </tbody>
</table>

<h2>8.2 Staff Role</h2>

<h3>Permissions</h3>
<table class="data-table narrow-cols">
    <thead>
        <tr>
            <th class="col-50">Action</th>
            <th class="col-50">Allowed</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Manage Beneficiaries</td>
            <td>Yes - Register, edit, view all beneficiaries</td>
        </tr>
        <tr>
            <td>Create Distribution Events</td>
            <td>Yes - Create and manage events</td>
        </tr>
        <tr>
            <td>Manage Allocations</td>
            <td>Yes - Create, edit, bulk upload, mark distributed</td>
        </tr>
        <tr>
            <td>Send SMS</td>
            <td>Yes - Broadcast and individual messages</td>
        </tr>
        <tr>
            <td>View Reports</td>
            <td>Yes - All report types accessible</td>
        </tr>
        <tr>
            <td>Geographic Map</td>
            <td>Yes - View distribution maps</td>
        </tr>
        <tr>
            <td>View Resource Types</td>
            <td>Yes - Operational access only</td>
        </tr>
        <tr>
            <td>Manage Users</td>
            <td>No - Admin only</td>
        </tr>
        <tr>
            <td>System Settings</td>
            <td>No - Admin only (except read resource types)</td>
        </tr>
        <tr>
            <td>Complete Events</td>
            <td>No - Admin only</td>
        </tr>
        <tr>
            <td>Delete Allocations</td>
            <td>No - Admin only</td>
        </tr>
    </tbody>
</table>

<h2>8.3 Beneficiary User</h2>

<h3>Access Level</h3>
<p>Beneficiaries do not have direct system access. They receive information through:</p>
<ul class="feature-list">
    <li>SMS notifications about registrations</li>
    <li>SMS alerts about allocation events</li>
    <li>SMS confirmation of assistance received</li>
</ul>

<div class="page-break"></div>

<!-- FOOTER PAGE -->
<h1>System Maintenance and Support</h1>

<h2>Audit Trail</h2>
<p>All system operations are logged in the Audit Logs table with the following information:</p>
<ul class="feature-list">
    <li>User performing the action</li>
    <li>Action type (create, update, delete, etc.)</li>
    <li>Table affected</li>
    <li>Record ID affected</li>
    <li>Old and new values (JSON format)</li>
    <li>Timestamp of action</li>
</ul>

<h2>Duplicate Detection Service</h2>
<p>The system includes an intelligent duplicate detection service that:</p>
<ul class="feature-list">
    <li>Scans for matching beneficiary records on registration</li>
    <li>Compares name, contact number, and classification</li>
    <li>Prevents accidental duplicate registrations</li>
    <li>Maintains soft-delete awareness for historical accuracy</li>
</ul>

<h2>Program Eligibility Service</h2>
<p>Comprehensive service ensuring proper program access:</p>
<ul class="feature-list">
    <li>Validates beneficiary classification against program requirements</li>
    <li>Checks agency registration for beneficiary eligibility</li>
    <li>Supports multi-agency beneficiary models</li>
    <li>Provides real-time eligibility checking</li>
</ul>

<h2>Database Maintenance</h2>
<p><strong>Backup Recommendations:</strong></p>
<ul>
    <li>Daily automated backups recommended</li>
    <li>Test restore procedures monthly</li>
    <li>Maintain offsite backup copies</li>
    <li>Archive old SMS and audit logs quarterly</li>
</ul>

<h2>Contact Information</h2>
<div class="info-box">
    <strong>Municipal Agriculture Office</strong><br>
    Enrique B. Magalona, Negros Occidental<br>
    Philippines<br><br>
    For technical support or questions about this documentation, contact the system administrator.
</div>

<hr>

<p style="text-align: center; margin-top: 40pt; font-size: 9pt; color: #666;">
    <strong>FFPRAMS System Documentation</strong><br>
    Generated: {{ date('F d, Y H:i:s') }}<br>
    Version 1.0 - Comprehensive System Reference
</p>

</body>
</html>
