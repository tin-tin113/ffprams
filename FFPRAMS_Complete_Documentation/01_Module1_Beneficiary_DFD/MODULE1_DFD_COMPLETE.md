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
