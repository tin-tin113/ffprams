# FFPRAMS - Farmer-Fisherfolk Precision Resource Allocation Management System

A web-based management system built for the **Municipal Agriculture Office of Enrique B. Magalona, Negros Occidental, Philippines**. It streamlines the registration of farmer and fisherfolk beneficiaries, tracks resource distribution events (physical and financial), sends SMS notifications, and provides geographic mapping with comprehensive reporting.

## Tech Stack

- **Backend:** PHP 8.2+ / Laravel 12
- **Frontend:** Bootstrap 5, Alpine.js 3, Vite 7
- **Database:** MySQL
- **Mapping:** Leaflet.js with OpenStreetMap tiles
- **SMS:** Semaphore-compatible API gateway
- **Authentication:** Laravel Breeze

## Features

### Module 1: Beneficiary Management
- Registration of farmers, fisherfolk, or dual-classified beneficiaries
- Agency-based field sets: DA/RSBSA, BFAR/FishR, DAR/CARP
- Comprehensive profiles with agency-specific fields (farm details, fishing operations, land awards)
- CLOA/EP number required for DAR beneficiaries before saving
- Automatic duplicate detection blocks registration if matching records found
- Filter and search by barangay, agency, classification, status, or name/ID
- Bulk status updates & SMS sending capabilities
- 8 documented use cases with complete UML diagrams

### Module 2: Resource Allocation & Distribution
- Two types of events: **Physical** (resource quantities) and **Financial** (monetary amounts)
- Status workflow: Pending &rarr; Ongoing &rarr; Completed
- Single and bulk allocation of resources or funds to beneficiaries
- Barangay matching validation (beneficiary must belong to the event's barangay)
- Duplicate prevention with soft-delete awareness
- Mark individual allocations as distributed with timestamp tracking
- CSV import for bulk resource allocations
- Cost calculation algorithms with budget tracking
- 11 documented use cases covering resource flows and compliance

### Module 3: Geographic Mapping & Data Visualization
- Interactive Leaflet.js map of all 23 barangays of E.B. Magalona
- Color-coded pins by distribution status (Completed, Ongoing, Pending, No Distribution)
- Detailed per-barangay panel: beneficiary breakdown, household data, event counts, allocation stats, coverage rate, financial totals, resource types distributed, and timeline
- Spatial data visualization with kernel density estimation
- 5 documented use cases for map interactions and reporting

### SMS Notifications & Broadcasting
- **Tab-based interface** with Compose, History, and Templates sections
- **6 recipient targeting methods**: All Active, By Barangay, By Classification, By Program/Event, By Resource, or Manual Selection
- **Refined beneficiary selection** with "Select All" button for quick bulk operations
- **Search & filter** within selected beneficiaries
- SMS template management with defaults and custom templates
- Character count tracking (no length limits)
- Preview recipients count before sending
- Automatic notifications on: registration and allocation
- Full SMS log with delivery status tracking and history filtering

### Comprehensive Reporting
1. Beneficiaries per Barangay (with farmer/fisherfolk/both breakdown)
2. Resource Distribution Summary
3. Distribution Status per Barangay
4. Unreached Beneficiaries (zero allocations)
5. Monthly Distribution Summary
6. Financial Assistance Summary by Resource Type
7. Financial Assistance per Barangay
8. Financial Assistance Distribution by Purpose

### Admin System Settings
- **Agencies** &ndash; Manage core line agencies (DA, BFAR, DAR)
- **Assistance Purposes** &ndash; Categorized purposes (agricultural, fishery, livelihood, medical, emergency, other)
- **Resource Types** &ndash; Manage resources with agency linking
- **Form Field Options** &ndash; Configurable dropdown values for beneficiary forms with drag-and-drop reorder

### Audit Logging
- All CRUD operations logged with user, action, table, old/new values (JSON)
- Complete audit trail for compliance and accountability

### User Management
- Role-based access: **Admin** (user management, system settings, and admin controls) and **Staff** (operational modules)
- Admin and Staff functions are intentionally separated to avoid overlap in module responsibilities
- Admin-only actions: complete events, delete allocations, manage users, and manage system settings
- Staff note: Staff can access the Resource Types module for operational maintenance
- Self-registration is disabled; user accounts are created and managed by admins

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd ffprams

# Install dependencies, configure environment, and build assets
composer setup
```

The `composer setup` command will:
1. Install PHP and Node.js dependencies
2. Copy `.env.example` to `.env`
3. Generate the application key
4. Run database migrations
5. Build frontend assets

### Configure Environment

Edit `.env` and set your database and SMS credentials:

```env
DB_DATABASE=ffprams
DB_USERNAME=root
DB_PASSWORD=

SMS_API_URL=https://your-sms-api-endpoint
SMS_API_KEY=your-api-key
SMS_SENDER_NAME=FFPRAMS
SMS_SEND_ON_EVENT_ONGOING=true
SMS_SEND_ON_DIRECT_ASSISTANCE_STATUS_CHANGE=true
```

### Seed the Database

```bash
php artisan db:seed
```

This seeds default users, 23 barangays with GPS coordinates, agencies, resource types, assistance purposes, and form field options.

### Default Accounts

| Role  | Email              | Password     |
|-------|--------------------|--------------|
| Admin | admin@ffprams.com  | Admin@1234   |
| Staff | staff@ffprams.com  | Staff@1234   |

## Development

```bash
# Start dev server, queue worker, log viewer, and Vite concurrently
composer dev
```

## Testing

```bash
composer test
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/           # User management, system settings
│   │   ├── Auth/            # Laravel Breeze auth controllers
│   │   ├── AllocationController.php
│   │   ├── BeneficiaryController.php
│   │   ├── DashboardController.php
│   │   ├── DistributionEventController.php
│   │   ├── GeoMapController.php
│   │   ├── ReportsController.php
│   │   ├── ResourceTypeController.php
│   │   └── SmsController.php
│   ├── Middleware/
│   │   └── CheckRole.php    # Role-based access control
│   └── Requests/            # Form request validation
├── Models/                  # Eloquent models (11 total)
└── Services/
    ├── AuditLogService.php  # Centralized audit logging
    └── SemaphoreService.php # SMS API integration

database/
├── migrations/              # Schema definitions
└── seeders/                 # Default data seeders

resources/views/
├── admin/                   # Admin panels (users, settings)
├── beneficiaries/           # Beneficiary CRUD views
├── distribution_events/     # Distribution event views
├── geo-map/                 # Leaflet map view
├── reports/                 # Reports dashboard
├── sms/                     # SMS broadcast and logs
└── layouts/                 # App and guest layouts
```

## Municipality Coverage

The system covers all **23 barangays** of Enrique B. Magalona (formerly Saravia), Negros Occidental:

Alacaygan, Alicante, Batea, Canlusong, Consing, Cudangdang, Damgo, Gahit, Latasan, Madalag, Manta-angan, Nanca, Pasil, Poblacion I, Poblacion II, Poblacion III, San Isidro, San Jose, Santo Nino, Tabigue, Tanza, Tomongtong, Tuburan

## License

This project is proprietary software developed for the Municipal Agriculture Office of Enrique B. Magalona, Negros Occidental.
