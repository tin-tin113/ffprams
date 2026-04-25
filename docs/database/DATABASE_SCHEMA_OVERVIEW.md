# FFPRAMS Database Schema & Relationships

This document provides a complete overview of the database structure, including tables, columns, and entity relationships.

## 📊 Database Overview
**Database Name:** `ffprams`  
**Total Tables:** 26  
**Last Updated:** 2026-04-25

---

## 🔗 Entity Relationships (Connections)

This section details how the tables are connected via Foreign Keys.

### 📍 Geography & Beneficiaries
*   **Barangays to Beneficiaries**: `barangays.id` → `beneficiaries.barangay_id` (Mandatory)
*   **Agencies to Beneficiaries**: `agencies.id` → `beneficiaries.agency_id` (Optional)
*   **Beneficiaries to SMS Logs**: `beneficiaries.id` → `sms_logs.beneficiary_id` (Mandatory)

### 🏢 Agencies & Programs
*   **Agencies to Users**: `agencies.id` → `users.agency_id` (Optional)
*   **Agencies to Programs**: `agencies.id` → `program_names.agency_id` (Mandatory)
*   **Agencies to Resource Types**: `agencies.id` → `resource_types.agency_id` (Optional)
*   **Programs to Legal Docs**: `program_names.id` → `program_legal_requirements.program_name_id` (Mandatory)

### 📦 Distribution & Allocations
*   **Barangays to Events**: `barangays.id` → `distribution_events.barangay_id` (Mandatory)
*   **Programs to Events**: `program_names.id` → `distribution_events.program_name_id` (Mandatory)
*   **Resource Types to Events**: `resource_types.id` → `distribution_events.resource_type_id` (Mandatory)
*   **Events to Allocations**: `distribution_events.id` → `allocations.distribution_event_id` (Optional)
*   **Beneficiaries to Allocations**: `beneficiaries.id` → `allocations.beneficiary_id` (Mandatory)

---

## 📁 Tables & Columns Breakdown

### 1. beneficiaries
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| first_name | varchar(255) | YES | | |
| last_name | varchar(255) | YES | | |
| full_name | varchar(255) | NO | | |
| barangay_id | bigint unsigned | NO | MUL | |
| classification | varchar(255) | NO | MUL | Farmer |
| contact_number | varchar(255) | NO | | |
| status | varchar(255) | NO | MUL | Active |
| rsbsa_number | varchar(255) | YES | UNI | |
| fishr_number | varchar(255) | YES | UNI | |
| id_number | varchar(255) | YES | | |

### 2. distribution_events
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | YES | | |
| barangay_id | bigint unsigned | NO | MUL | |
| program_name_id | bigint unsigned | NO | MUL | |
| resource_type_id | bigint unsigned | NO | MUL | |
| distribution_date | date | NO | MUL | |
| status | enum('Pending','Ongoing','Completed') | NO | MUL | Pending |
| compliance_overall_status | varchar(50) | YES | | not_available_yet |

### 3. allocations
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| distribution_event_id | bigint unsigned | YES | MUL | |
| beneficiary_id | bigint unsigned | NO | MUL | |
| quantity | decimal(8,2) | YES | | |
| amount | decimal(12,2) | YES | | |
| is_ready_for_release | tinyint(1) | NO | MUL | 0 |
| release_outcome | enum('received','not_received') | YES | MUL | |

### 4. agencies
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | NO | | |
| full_name | varchar(255) | NO | | |
| is_active | tinyint(1) | NO | | 1 |

### 5. program_names
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| agency_id | bigint unsigned | NO | MUL | |
| name | varchar(255) | NO | | |
| classification | enum('Farmer','Fisherfolk','Both') | NO | | Both |

### 6. users
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | NO | | |
| email | varchar(255) | NO | UNI | |
| role | enum('admin','staff','partner') | NO | | |
| agency_id | bigint unsigned | YES | MUL | |

### 7. sms_logs
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL | |
| message | text | NO | | |
| status | enum('sent','failed') | NO | | |
| segments | int | NO | | 1 |
| delivery_status | enum('pending','delivered','failed','undeliverable') | NO | | pending |

### 8. barangays
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | NO | | |
| quadrant | varchar(255) | YES | MUL | |
| municipality | varchar(255) | NO | MUL | E.B. Magalona |

### 9. resource_types
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | NO | | |
| unit | varchar(255) | NO | | |
| agency_id | bigint unsigned | YES | MUL | |

### 10. direct_assistance
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL | |
| program_name_id | bigint unsigned | NO | MUL | |
| resource_type_id | bigint unsigned | NO | MUL | |
| status | enum('planned','ready_for_release','released','not_received') | NO | MUL | planned |

### 11. sms_templates
| Column | Type | Null | Key | Default |
|---|---|---|---|---|
| id | bigint unsigned | NO | PRI | auto_increment |
| name | varchar(255) | NO | UNI | |
| content | text | NO | | |
| is_active | tinyint(1) | NO | | 1 |

---

> [!NOTE]
> This is a condensed version of the most critical tables. For a full list including technical tables like `migrations`, `sessions`, and `cache`, please refer to the detailed `DATABASE_TABLES_AND_COLUMNS.md` file.
