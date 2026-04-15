# MODULE 1: BENEFICIARY MANAGEMENT - DATA FLOW DIAGRAM (DFD)
## Complete Documentation - All Levels (0-4)

**Course**: Project Management
**Assignment**: Module Analysis with ERD, DFD (Level 0-4), and Use Case UML
**Deadline**: April 14, 2026, 12:00 NN
**Module**: Beneficiary Management System
**Date Created**: 2026-04-15
**Document Type**: DFD Specifications

---

## TABLE OF CONTENTS
1. [DFD Level 0 - System Context](#dfd-level-0)
2. [DFD Level 1 - Main Processes](#dfd-level-1)
3. [DFD Level 2 - Create Beneficiary Decomposition](#dfd-level-2)
4. [DFD Level 3 - Data Validation Details](#dfd-level-3)
5. [DFD Level 4 - Terminal Processes](#dfd-level-4)
6. [Data Stores Dictionary](#data-stores)
7. [Data Flows Summary](#data-flows)

---

## DFD LEVEL 0 - SYSTEM CONTEXT {#dfd-level-0}

### Context Diagram Description

The Beneficiary Management System operates within the FFPRAMS (Agricultural Assistance Distribution Management System) framework.

#### Entities:
- **External Actor 1**: Admin/Staff Users
  - Role: System users with authentication
  - Input: User commands, form submissions, search queries
  - Output: Results, confirmations, reports

- **External Actor 2**: File Storage System
  - Role: Document repository
  - Input: Beneficiary documents (photos, IDs, certifications)
  - Output: Stored file references

- **External Actor 3**: Database Management System
  - Role: Persistent data storage
  - Input: Data to store/update
  - Output: Retrieved records, query results

#### Flows:
```
USER INPUTS                                FILE STORAGE
     │                                          │
     ├────────────┐                      ┌──────┤
     │            │                      │      │
     │    ┌───────▼──────────────────────▼──┐  │
     │    │ BENEFICIARY MANAGEMENT MODULE  │  │
     │    │                                │  │
     │    │ • Create Beneficiary          │  │
     │    │ • Update Information          │  │
     │    │ • Search/View                 │  │
     │    │ • Manage Documents            │  │
     │    │ • Validate Data               │  │
     │    └───────┬──────────────────────┬───┘  │
     │            │                      │      │
     │            │                      └──────┘
     │            │
     │    DATABASE OPERATIONS
     │            │
     │            ▼
     │     ┌──────────────────┐
     │     │ POSTGRESQL DB    │
     │     │                  │
     │     │ Tables:          │
     │     │ • beneficiaries  │
     │     │ • agencies       │
     │     │ • barangays      │
     │     │ • attachments    │
     │     │ • audit_logs     │
     │     └──────────────────┘
     │
     └─────────────────────────────────────────▶ Output/Results
```

### Level 0 Data Flow Summary

| Flow ID | From | To | Data Content | Trigger |
|---------|------|-----|--------------|---------|
| L0.1 | Admin User | System | Form submissions, queries | User action |
| L0.2 | System | Admin User | Results, confirmations | Process complete |
| L0.3 | System | File Storage | Save/retrieve documents | Upload/download |
| L0.4 | File Storage | System | File metadata, paths | Document access |
| L0.5 | System | Database | INSERT/UPDATE/SELECT | Data operations |
| L0.6 | Database | System | Query results, records | Data retrieval |

---

## DFD LEVEL 1 - MAIN PROCESSES {#dfd-level-1}

### Process Decomposition

The system is decomposed into 6 main processes at Level 1:

```
MAIN PROCESSES:
┌────────────┐   ┌────────────┐   ┌────────────┐
│     P1     │   │     P2     │   │     P3     │
│   CREATE   │   │   UPDATE   │   │  SEARCH/   │
│ BENEFICIARY│   │ BENEFICIARY│   │   VIEW     │
└────────────┘   └────────────┘   └────────────┘

┌────────────┐   ┌────────────┐   ┌────────────┐
│     P4     │   │     P5     │   │     P6     │
│  MANAGE    │   │   VALIDATE │   │  AUDIT &   │
│ DOCUMENTS  │   │    DATA    │   │   LOGGING  │
└────────────┘   └────────────┘   └────────────┘
```

### Process Specifications - Level 1

#### **P1: CREATE BENEFICIARY**
- **Input**: New beneficiary form with personal & sector-specific data
- **Processing**:
  - Validate all input fields
  - Compute derived fields (full_name, age)
  - Normalize contact number
  - Insert into beneficiaries table
  - Create agency links
  - Generate audit entry
- **Output**: beneficiary_id, success confirmation
- **Data Stores Accessed**: D1 (beneficiaries), D8 (barangays), D9 (agencies), D6 (audit_logs), D10 (beneficiary_agencies)

#### **P2: UPDATE BENEFICIARY**
- **Input**: beneficiary_id + updated field values
- **Processing**:
  - Retrieve current record from D1
  - Validate changes
  - Update modified fields
  - Record old/new values for audit
  - Store to D1
- **Output**: success confirmation, updated_at timestamp
- **Data Stores Accessed**: D1, D6 (audit_logs)

#### **P3: SEARCH/VIEW BENEFICIARY**
- **Input**: Search filters (name, barangay, agency, classification, status)
- **Processing**:
  - Query D1 with filters
  - Join with D8 (barangays) and D9 (agencies)
  - Apply pagination
  - Format output
- **Output**: Beneficiary list or detail view
- **Data Stores Accessed**: D1, D8, D9

#### **P4: MANAGE DOCUMENTS**
- **Input**: File upload, file_id, beneficiary_id
- **Processing**:
  - Validate file (type, size, format)
  - Store file to file system (D7e)
  - Create attachment record in D7e
  - Link to beneficiary
  - Create audit log
- **Output**: attachment_id, file_path
- **Data Stores Accessed**: D7e (attachments), D1 (beneficiaries), D6 (audit_logs)

#### **P5: VALIDATE DATA**
- **Input**: Raw form data
- **Processing**:
  - Check required fields
  - Verify data formats (phone, email, date)
  - Verify relational integrity (FK references)
  - Check uniqueness constraints (duplicate phone)
  - Run business rules validation
- **Output**: Validation result (PASS/FAIL), error messages
- **Data Stores Accessed**: D1, D8, D9, D3 (classifications)

#### **P6: AUDIT & LOGGING**
- **Input**: Action details (user_id, action_type, beneficiary_id, old_values, new_values)
- **Processing**:
  - Create audit log entry
  - Record timestamp
  - Store to D6
  - Format for reporting
- **Output**: audit_log_id
- **Data Stores Accessed**: D6 (audit_logs)

---

## DFD LEVEL 2 - CREATE BENEFICIARY DECOMPOSITION {#dfd-level-2}

### P1: CREATE BENEFICIARY (Expanded into 9 Sub-processes)

```
LEVEL 2: P1 DECOMPOSITION ( Create Beneficiary )

┌─────────────────────────────────────────────────────────────┐
│  INPUT: Admin User Form Submission                          │
│  (name, sex, DOB, barangay, agency, sector_fields, phone)  │
└─────────────────────────────────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.1: Display Beneficiary Form  │
        │ • Query D8 (barangays)          │
        │ • Query D9 (agencies)           │
        │ • Query D3 (classifications)    │
        │ Output: HTML Form               │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.2: User Data Entry          │
        │ • Form completion              │
        │ Output: Form data (raw)        │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.3: Validate Input           │
        │ (Detailed in LEVEL 3)          │
        │ Output: Valid/Invalid + Errors │
        └────────────────────────────────┘
                         │
                    ┌────┴────┐
                    │          │
                 VALID      INVALID
                    │          │
                    │      ┌───────────────┐
                    │      │ Return to P1.1│
                    │      │ Show errors   │
                    │      └───────────────┘
                    │
                    ▼
        ┌────────────────────────────────┐
        │ P1.4: Compute Full Name        │
        │ full_name = Concat(            │
        │   first_name, middle_name,     │
        │   last_name, suffix            │
        │ )                              │
        │ Output: full_name value        │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.5: Normalize Phone Number   │
        │ • Convert to +63 format        │
        │ • Validate PH mobile pattern   │
        │ Output: normalized_phone       │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.6: Insert into Beneficiaries│
        │ (D1)                           │
        │ • Execute INSERT statement     │
        │ • Return new beneficiary_id    │
        │ Output: beneficiary_id         │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.7: Create Agency Links (M2M)│
        │ (D10: beneficiary_agencies)    │
        │ • Insert multiple rows if      │
        │   multiple agencies selected   │
        │ Output: link count             │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.8: Create Audit Log Entry   │
        │ (D6)                           │
        │ • Record user_id, action       │
        │ • new_values = all data        │
        │ • old_values = NULL            │
        │ Output: audit_log_id           │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │ P1.9: Return Success           │
        │ • JSON response with ID        │
        │ • Redirect to view page        │
        │ Output: 200 OK + beneficiary   │
        └────────────────────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────────────────────────┐
        │ FINAL OUTPUTS TO USER                               │
        │ • Success message                                   │
        │ • Redirect to: /beneficiaries/{id}                 │
        │ • beneficiary_id stored in session                  │
        └─────────────────────────────────────────────────────┘
```

### Level 2 Data Stores and Flows

| Process | Reads From | Writes To | Data Flow |
|---------|-----------|-----------|-----------|
| P1.1 | D8, D9, D3 | - | Dropdown options |
| P1.2 | - | - | Form input buffer |
| P1.3 | D1, D8, D9, D3 | - | Validation checks |
| P1.4 | - | - | String concatenation |
| P1.5 | - | - | String formatting |
| P1.6 | - | D1 | INSERT beneficiaries |
| P1.7 | - | D10 | INSERT agency links |
| P1.8 | - | D6 | INSERT audit log |
| P1.9 | D1 | - | Retrieve new record |

---

## DFD LEVEL 3 - VALIDATE DATA DECOMPOSITION {#dfd-level-3}

### P1.3: VALIDATE INPUT (Expanded into 8 Detailed Steps)

This process validates all input data before acceptance.

#### **P1.3.1: Check Required Fields**
```
Input: Form data
Process:
  IF first_name IS EMPTY → Error: "First name required"
  IF last_name IS EMPTY → Error: "Last name required"
  IF barangay_id IS NULL → Error: "Barangay required"
  IF agency_id IS NULL → Error: "Agency required"
  IF contact_number IS EMPTY → Error: "Contact required"

Output: Pass/Fail status
Data Store: None (validation only)
```

#### **P1.3.2: Validate Phone Format**
```
Input: contact_number (string)
Process:
  1. Trim whitespace
  2. Remove formatting chars: (  )  -  /
  3. Test against regex: ^(\+63|0)9\d{9}$
  IF fails → Error: "Invalid PH mobile format"

  4. Query D1: SELECT * WHERE contact_number = normalized
  IF found → Error: "Phone already registered"

Output: Normalized phone or error
Data Store: D1 (duplicate check)
```

#### **P1.3.3: Validate Date of Birth**
```
Input: date_of_birth (date)
Process:
  IF date > TODAY() → Error: "DOB cannot be in future"

  age = YEAR(TODAY()) - YEAR(date_of_birth)

  IF age < 15 → Error: "Beneficiary too young"
  IF age > 120 → Error: "Invalid age"

Output: Valid age or error
Data Store: None
```

#### **P1.3.4: Verify Barangay Reference**
```
Input: barangay_id (integer)
Process:
  Query D8: SELECT id WHERE id = barangay_id
  IF NOT found → Error: "Invalid barangay selected"

Output: Valid/Invalid flag
Data Store: D8 (lookup only)
```

#### **P1.3.5: Verify Agency Reference**
```
Input: agency_id (integer)
Process:
  Query D9: SELECT id WHERE id = agency_id AND active = true
  IF NOT found → Error: "Invalid or inactive agency"

Output: Valid/Invalid flag
Data Store: D9 (lookup only)
```

#### **P1.3.6: Validate Name Characters**
```
Input: first_name, middle_name, last_name (strings)
Process:
  FOR EACH name field:
    IF contains special chars (!@#$%^&*) → Error
    IF contains numbers (0-9) → Error
    IF contains multiple spaces → Error
    IF length > 100 → Error: "Name too long"

Output: Valid/Invalid flag
Data Store: None
```

#### **P1.3.7: Validate Sector-Specific Fields**
```
Input: classification, sector_fields (JSON)
Process:
  IF classification = 'Farmer':
    IF farm_ownership IS EMPTY → Error
    IF farm_size_hectares ≤ 0 → Error
    IF primary_commodity IS EMPTY → Error

  IF classification = 'Fisherfolk':
    IF fisherfolk_type IS EMPTY → Error
    IF main_fishing_gear IS EMPTY → Error

  IF classification = 'DAR Beneficiary':
    IF cloa_ep_number IS EMPTY → Error
    IF land_area_hectares ≤ 0 → Error

Output: Valid/Invalid flag
Data Store: D3 (classification reference)
```

#### **P1.3.8: Aggregate Validation Result**
```
Input: All validation flags from P1.3.1-P1.3.7
Process:
  IF any validation FAILED:
    RETURN: INVALID status + all error messages
  ELSE:
    RETURN: VALID status + cleaned_data

Output: Validation result object
Data Store: None
```

---

## DFD LEVEL 4 - TERMINAL PROCESSES {#dfd-level-4}

### P1.3.2: VALIDATE PHONE NUMBER (Terminal/Leaf Process)

This is a terminal process with detailed algorithmic steps.

#### **Process: Normalize and Validate Philippine Mobile Number**

```
INPUT PARAMETER:
  contact_number (string) - Any format

STEP 1: TRIM WHITESPACE
────────────────────────
  contact_number = TRIM(contact_number)
  • Remove leading spaces
  • Remove trailing spaces
  • Keep internal spaces (for now)

STEP 2: REMOVE FORMATTING CHARACTERS
─────────────────────────────────────
  contact_number = REPLACE(contact_number,
    ['(', ')', '-', '/', '.', ' '],  // characters to remove
    ''                                // replace with nothing
  )

  Examples:
    Input:  "(0917) 123-4567"
    Step 1: "(0917) 123-4567"
    Step 2: "09171234567"

    Input:  "+63 917 123 4567"
    Step 1: "+63 917 123 4567"
    Step 2: "+639171234567"

STEP 3: VALIDATE REGEX PATTERN
──────────────────────────────
  Pattern: ^(\+63|0)9\d{9}$

  Explanation:
    ^        = Start of string
    (\+63|0) = Either "+63" or "0" (country code)
    9        = Must start with 9
    \d{9}    = Exactly 9 more digits (0-9)
    $        = End of string

  IF regex_match(contact_number, pattern) = FALSE:
    ERROR_CODE: ERR_PHONE_INVALID_FORMAT
    ERROR_MSG:  "Invalid Philippine mobile number format"
    RETURN: {status: FAIL, error: ERROR_MSG}

  CONTINUE to Step 4

STEP 4: NORMALIZE TO +63 FORMAT
───────────────────────────────
  IF contact_number STARTS WITH '0':
    contact_number = '+63' + contact_number.substring(1)

  (If already starts with +63, leave as is)

  Examples:
    Input:  "09171234567"
    Output: "+639171234567"

    Input:  "+639171234567"
    Output: "+639171234567" (unchanged)

STEP 5: CHECK DUPLICATE IN DATABASE
────────────────────────────────────
  Query D1 (beneficiaries table):
    SELECT COUNT(*) as cnt
    FROM beneficiaries
    WHERE contact_number = contact_number
    AND deleted_at IS NULL  // exclude soft-deleted

  IF cnt > 0:
    ERROR_CODE: ERR_PHONE_DUPLICATE
    ERROR_MSG:  "This phone number is already registered"
    RETURN: {status: FAIL, error: ERROR_MSG}

  CONTINUE to Step 6

STEP 6: VALIDATION SUCCESS
──────────────────────────
  RETURN: {
    status: SUCCESS,
    value: contact_number,        // normalized +63 format
    error_code: null,
    error_message: null
  }

OUTPUT OBJECT:
──────────────
  {
    status: "SUCCESS" | "FAIL",
    value: "+639171234567",   // if successful
    error_code: "ERR_PHONE_*", // if failed
    error_message: "Human-readable message"
  }

TRANSITION TO NEXT PROCESS:
───────────────────────────
  if output.status === "SUCCESS":
    → Continue to P1.4 (Compute Full Name)
    → Pass normalized phone to database
  else:
    → Return to P1.3 (Report validation failure)
    → Display error to user in form
```

#### **Pseudocode Implementation**

```python
def validate_phone_number(contact_number):
    """
    Validates and normalizes Philippine mobile number
    Returns: {status, value, error_code, error_message}
    """

    # STEP 1: Trim
    contact_number = contact_number.strip()

    # STEP 2: Remove formatting
    chars_to_remove = ['(', ')', '-', '/', '.', ' ']
    for char in chars_to_remove:
        contact_number = contact_number.replace(char, '')

    # STEP 3: Regex validation
    import re
    pattern = r'^(\+63|0)9\d{9}$'
    if not re.match(pattern, contact_number):
        return {
            'status': 'FAIL',
            'value': None,
            'error_code': 'ERR_PHONE_INVALID_FORMAT',
            'error_message': 'Invalid Philippine mobile number format'
        }

    # STEP 4: Normalize to +63
    if contact_number.startswith('0'):
        contact_number = '+63' + contact_number[1:]

    # STEP 5: Check duplicate
    from db import db  # Simulated
    existing = db.query('''
        SELECT COUNT(*) as cnt FROM beneficiaries
        WHERE contact_number = %s AND deleted_at IS NULL
    ''', (contact_number,))

    if existing[0]['cnt'] > 0:
        return {
            'status': 'FAIL',
            'value': None,
            'error_code': 'ERR_PHONE_DUPLICATE',
            'error_message': 'This phone number is already registered'
        }

    # STEP 6: Success
    return {
        'status': 'SUCCESS',
        'value': contact_number,
        'error_code': None,
        'error_message': None
    }
```

---

## DATA STORES DICTIONARY {#data-stores}

### Complete List of Data Stores

| ID | Name | Tables | Purpose | Record Count Est. |
|----|------|--------|---------|------------------|
| D1 | Beneficiaries | beneficiaries | Core beneficiary records | 10,000+ |
| D3 | Classifications | (Enum/Config) | Beneficiary types, sector info | 20 |
| D6 | Audit Logs | audit_logs | System activity tracking | Grows daily |
| D7e | Attachments | attachments | Document storage metadata | 5,000+ |
| D8 | Barangays | barangays | Geographic locations | 100+ |
| D9 | Agencies | agencies | Implementing agencies | 5-10 |
| D10 | Beneficiary-Agency | beneficiary_agencies | M2M relationship | 20,000+ |

### D1: Beneficiaries Table Structure
```
beneficiaries {
  id (PK): integer
  first_name: string(50)
  middle_name: string(50) nullable
  last_name: string(50)
  name_suffix: string(20) nullable
  full_name: string(120) computed
  sex: enum(M,F,Other)
  date_of_birth: date
  age: integer computed
  barangay_id (FK): integer
  agency_id (FK): integer
  photo_path: string(255) nullable
  home_address: text
  contact_number: string(20) unique
  classification: string(50) // Farmer, Fisherfolk, DAR, etc

  // Farmer-specific
  rsbsa_number: string(20) nullable
  farm_ownership: string(50) nullable
  farm_size_hectares: decimal(8,2) nullable
  primary_commodity: string(50) nullable

  // Fisherfolk-specific
  fishr_number: string(20) nullable
  fisherfolk_type: string(50) nullable
  main_fishing_gear: string(50) nullable

  // DAR-specific
  cloa_ep_number: string(20) nullable
  arb_classification: string(50) nullable

  status: enum(active, inactive) default active
  civil_status: enum(Single, Married, Divorced, Widowed)
  highest_education: string(50)
  id_type: string(20)  // GSIS, SSS, Voter ID, etc
  custom_fields: json nullable

  created_at: timestamp
  updated_at: timestamp
  deleted_at: timestamp nullable // soft delete
}
```

### D6: Audit Logs Table Structure
```
audit_logs {
  id (PK): integer
  user_id (FK): integer
  beneficiary_id (FK): integer nullable
  action: string(100)  // 'beneficiary_created', 'beneficiary_updated', etc
  old_values: json nullable
  new_values: json nullable
  ip_address: string(45)
  user_agent: text nullable

  created_at: timestamp
}
```

---

## DATA FLOWS SUMMARY {#data-flows}

### All Data Flows in the Module

| Flow ID | Source | Destination | Data Content | Trigger | Frequency |
|---------|--------|-------------|--------------|---------|-----------|
| DF1.1 | Admin | System | Beneficiary form | Button submit | On demand |
| DF1.2 | System | Admin | Form validation errors | Validation fail | On demand |
| DF1.3 | System | D1 | INSERT beneficiary | After validation | On demand |
| DF1.4 | D1 | D10 | Create agency links | After insert | On demand |
| DF1.5 | System | D6 | INSERT audit log | Every action | On demand |
| DF2.1 | Admin | System | Search filter | Trigger search | On demand |
| DF2.2 | D1 | System | Retrieved records | Query executed | On demand |
| DF2.3 | System | Admin | Paginated list | Query complete | On demand |
| DF3.1 | Admin | System | Upload file | File select | On demand |
| DF3.2 | System | File System | Save document | Validation pass | On demand |
| DF3.3 | System | D7e | Create attachment record | File saved | On demand |

---

## BUSINESS RULES & CONSTRAINTS

### Data Validation Rules

1. **Phone Number**: Must be valid PH mobile format (09XX-XXXXXXX or +639XX-XXXXXXX)
2. **Age**: 15-120 years old at registration
3. **Uniqueness**: contact_number must be unique across active beneficiaries
4. **Required Fields**: first_name, last_name, barangay, agency, contact_number
5. **Sector Fields**: Must provide relevant fields based on classification
6. **Soft Delete**: Records not permanently deleted, marked with deleted_at

### Processing Rules

1. **Full Name Computation**: Trim, combine name parts, remove extra spaces
2. **Phone Normalization**: Convert to +63XXXXXXXXXX format
3. **Audit Trail**: Every create/update action logged with old/new values
4. **Timestamps**: All records tracked with created_at, updated_at

---

## DOCUMENT METADATA

- **Version**: 1.0
- **Status**: COMPLETE
- **Pages**: Approx. 12+ printed pages
- **Last Updated**: 2026-04-15
- **Author**: Project Team
- **For**: Project Management Assignment Submission
