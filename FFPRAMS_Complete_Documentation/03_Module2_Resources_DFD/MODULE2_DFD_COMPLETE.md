# MODULE 2: RESOURCE ALLOCATION & DISTRIBUTION
## Complete Project Management Deliverables

**Course**: Project Management
**Assignment**: Module Analysis with ERD, DFD (Level 0-4), and Use Case UML
**Deadline**: April 14, 2026, 12:00 NN
**Module**: Resource Allocation & Distribution
**Date Created**: 2026-04-15

---

## 1. ENTITY-RELATIONSHIP DIAGRAM (ERD)

### ERD Narrative

**Primary Entity**: Resource Allocation
- Tracks distribution of resources to beneficiaries
- Links: Events → Allocations → Beneficiaries
- Secondary: Resource Types, Distribution Events, Locations

**Core Entities**:
- **Resources**: Types of items to distribute (seeds, fertilizer, tools, cash)
- **Distribution Events**: Organized distribution schedules
- **Allocations**: Individual resource allocations to beneficiaries
- **Locations**: Distribution points/venues
- **ResourceLog**: Transaction history for each allocation

### ERD Diagram (Crow's Foot Notation)

```
┌────────────────────────────────────────────────────────────────┐
│              RESOURCE ALLOCATION & DISTRIBUTION ERD             │
└────────────────────────────────────────────────────────────────┘

┌─────────────────────────────┐
│  RESOURCE TYPES             │
├─────────────────────────────┤
│ PK  id                      │
│     name                    │
│     category (Seeds,        │
│     Fertilizer, Tools, etc) │
│     unit_of_measure         │
│     description             │
│     active                  │
│     created_at              │
└──────────┬──────────────────┘
          │  (1:M)
          │  has
          │
┌─────────v─────────────────────────────┐
│  DISTRIBUTION EVENTS                  │
├───────────────────────────────────────┤
│ PK  id                                │
│     name / title                      │
│     description                       │
│ FK  program_id                        │
│ FK  barangay_id                       │
│     scheduled_date                    │
│     venue / distribution_point         │
│     total_budget / amount              │
│     status (Planned/Ongoing/Complete) │
│     created_by_user                   │
│     created_at                        │
└─────────┬───────────────────────────┬─┘
          │ (1:M)                    │
          │                          │
    ┌─────v──────────────────────────┴────────┐
    │  ALLOCATIONS (Core Entity)              │
    │                                         │
    ├─────────────────────────────────────────┤
    │ PK  id                                  │
    │ FK  event_id                            │
    │ FK  beneficiary_id                      │
    │ FK  resource_id                         │
    │     quantity_allocated                  │
    │     unit_price / cost_per_unit          │
    │     total_cost                          │
    │     allocation_date                     │
    │     actual_distribution_date            │
    │     received_quantity                   │
    │     status (Allocated/Distributed/")   │
    │     notes / remarks                     │
    │     created_at, updated_at              │
    └─────────┬───────────────────────────────┘
              │ (M:1)
              │ receives
              │
        ┌─────v────────────────────────┐
        │  BENEFICIARIES               │
        │  (Link to Module 1)          │
        ├───────────────────────────────┤
        │ PK  id                        │
        │     full_name                 │
        │     barangay_id               │
        │     contact_number            │
        │     classification            │
        │     status                    │
        └───────────────────────────────┘

┌─────────────────────────────────────────┐
│  DISTRIBUTION LOCATIONS                 │
├─────────────────────────────────────────┤
│ PK  id                                  │
│     name / venue_name                   │
│ FK  barangay_id                         │
│     address                             │
│     latitude / longitude                │
│     capacity (people/items)             │
│     contact_person / phone              │
│     active                              │
│     created_at                          │
└──────────────────────────────┬──────────┘
                               │  (FK)
                               │  FK: event_id
                               │

┌─────────────────────────────────────────┐
│  RESOURCE LOGS (Transaction History)    │
├─────────────────────────────────────────┤
│ PK  id                                  │
│ FK  allocation_id                       │
│ FK  beneficiary_id                      │
│     action (Allocated/Distributed/")   │
│     quantity                            │
│     previous_quantity                   │
│     performed_by (user_id)              │
│     action_date / timestamp             │
│     notes                               │
│     created_at                          │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  DISTRIBUTION PHOTOS                    │
├─────────────────────────────────────────┤
│ PK  id                                  │
│ FK  event_id                            │
│     photo_path / file_name              │
│     uploaded_by (user_id)               │
│     uploaded_at                         │
│     description / caption               │
└─────────────────────────────────────────┘
```

---

## 2. DATA FLOW DIAGRAM (DFD) - ALL LEVELS

### DFD LEVEL 0 - SYSTEM CONTEXT

```
┌────────────────────────────────────────────────────────┐
│         SYSTEM BOUNDARY: FFPRAMS                       │
│                                                         │
│   RESOURCE ALLOCATION & DISTRIBUTION MODULE            │
│                                                         │
└────────────────────────────────────────────────────────┘
        ▲                                    ▲
        │                                    │
   User Input                         File/Photo Storage
        │                                    │
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  • Plan Distribution Events                            │
│  • Allocate Resources                                  │
│  • Track Distribution                                  │
│  • Record Receipt                                      │
│  • Generate Reports                                    │
│                                                         │
└─────────────────┬──────────────────────┬───────────────┘
                  │                      │
    DATABASE      │                      │
    OPERATIONS    │                      │
                  ▼                      ▼
        ┌──────────────────────────────────┐
        │  POSTGRESQL DB TABLES:           │
        │  • resources                     │
        │  • distribution_events           │
        │  • allocations                   │
        │  • allocation_beneficiaries      │
        │  • distribution_locations        │
        │  • resource_logs                 │
        │  • distribution_photos           │
        │  • allocation_verification       │
        └──────────────────────────────────┘
```

### DFD LEVEL 1 - MAIN PROCESSES

```
MAIN PROCESSES:
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│      P2.1      │  │      P2.2      │  │      P2.3      │
│    PLAN EVENT  │  │  ALLOCATE      │  │  DISTRIBUTE    │
│                │  │  RESOURCES     │  │  RESOURCES    │
└────────────────┘  └────────────────┘  └────────────────┘

┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│      P2.4      │  │      P2.5      │  │      P2.6      │
│  RECORD        │  │  VERIFY        │  │  GENERATE      │
│  DISTRIBUTION  │  │  RECEIPT       │  │  REPORTS       │
└────────────────┘  └────────────────┘  └────────────────┘
```

**P2.1: PLAN DISTRIBUTION EVENT**
- Input: Event details (name, date, location, program, barangay)
- Processing: Validate event data, reserve location, budget approval
- Output: event_id, confirmation
- Data Stores: distribution_events, distribution_locations, programs

**P2.2: ALLOCATE RESOURCES**
- Input: event_id, resource selections with quantities
- Processing: Link resources to beneficiaries, compute costs, validate stock
- Output: allocation_ids, allocation summary
- Data Stores: allocations, resources, beneficiaries, allocation_logs

**P2.3: DISTRIBUTE RESOURCES**
- Input: allocation_ids to distribute
- Processing: Generate distribution lists, track actual distribution
- Output: distribution_date, received_quantity updates
- Data Stores: allocations, resource_logs

**P2.4: RECORD DISTRIBUTION**
- Input: Distribution evidence (photos, signatures, notes)
- Processing: Validate photos, link to event, create transaction logs
- Output: distribution_photo_id, log_id
- Data Stores: distribution_photos, resource_logs

**P2.5: VERIFY RECEIPT**
- Input: Beneficiary confirmation of receipt
- Processing: Match expected vs actual quantities
- Output: verification_id, status update
- Data Stores: allocation_verification

**P2.6: GENERATE REPORTS**
- Input: Date range, resource type, barangay filters
- Processing: Query allocations, sum distributions, compute statistics
- Output: PDF/Excel report
- Data Stores: allocations, resources, beneficiaries (read-only)

---

### DFD LEVEL 2 - ALLOCATE RESOURCES (P2.2) DECOMPOSITION

```
P2.2 DECOMPOSITION: ALLOCATE RESOURCES

Input: event_id, resource selections with quantities
       │
       ▼
┌────────────────────────────────┐
│ P2.2.1: Validate Event Exists  │
│ Query distribution_events (D20)│
│ IF not found → Error           │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.2: Retrieve Event Details │
│ Get event: program, location,  │
│ current budget, beneficiaries  │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.3: Validate Resources     │
│ FOR each resource selected:    │
│   • Resource exists (D19)      │
│   • Sufficient stock available │
│   • Unit price defined         │
│   • Valid quantity             │
│ IF any invalid → Error         │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.4: Calculate Allocation   │
│ Costs                          │
│                                │
│ FOR each allocation:           │
│   total_cost =                 │
│     quantity * unit_price      │
│                                │
│   program_cost += total_cost   │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.5: Validate Budget        │
│                                │
│ IF program_cost > event_budget:│
│   Error: "Exceeds budget"      │
│ ELSE:                          │
│   Continue                     │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.6: Select Beneficiaries   │
│                                │
│ Query beneficiaries (D1):      │
│   WHERE barangay_id =          │
│     event.barangay_id          │
│   AND status = 'active'        │
│   AND NOT in allocation pool   │
│                                │
│ FOR each resource:             │
│   Select N beneficiaries       │
│   Create allocation records    │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.7: Insert Allocations     │
│ (D17: allocations)             │
│                                │
│ INSERT INTO allocations:       │
│   event_id, beneficiary_id,    │
│   resource_id, quantity,       │
│   cost, status='Allocated'     │
│ RETURN: allocation_id          │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.8: Update Resource Stock  │
│ (D19: resources)               │
│                                │
│ FOR each resource:             │
│   UPDATE resources             │
│   SET total_allocated +=       │
│     sum(quantities)            │
│   SET available_stock -=       │
│     sum(quantities)            │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────────────────────┐
│ P2.2.9: Create Audit Entries   │
│ (D6: audit_logs)               │
│                                │
│ Log: "Allocations_created"     │
│ Quantity: N beneficiaries      │
│ Total cost: program_cost       │
└────────┬───────────────────────┘
         │
         ▼
    RETURN SUCCESS
    allocation_ids[]
    allocation_count
    total_cost
```

---

### DFD LEVEL 3 - VALIDATE BUDGET (P2.2.5) DECOMPOSITION

```
P2.2.5 DECOMP: VALIDATE BUDGET

Inputs: event_id, computed_program_cost

Step 1: Retrieve Event Budget
────────────────────────────
Query distribution_events (D20):
  SELECT total_budget
  WHERE id = event_id

Step 2: Retrieve Current Spending
──────────────────────────────────
Query allocations (D17):
  SELECT SUM(total_cost) as spent
  WHERE event_id = event_id
  AND status IN ('Allocated', 'Distributed')

Step 3: Calculate Remaining Budget
───────────────────────────────────
remaining_budget = total_budget - spent

Step 4: Compare Costs
─────────────────────
IF computed_program_cost > remaining_budget:
  RETURN: {status: FAIL, budget_exceeded: true}
  ERROR: Amount exceeds remaining budget
ELSE:
  RETURN: {status: PASS, remaining: remaining_budget}

OUTPUT:
  • Validation result (PASS/FAIL)
  • Remaining budget amount
  • Overage amount (if failed)
```

---

### DFD LEVEL 4 - CALCULATE TOTAL COST (Terminal Process)

```
P2.2.4.1: CALCULATE ALLOCATION COST (Terminal)

INPUTS:
  quantity (integer)
  unit_price (decimal)
  tax_rate (percentage, optional)

ALGORITHM:
──────────

Step 1: Validate Inputs
  IF quantity <= 0: return ERROR
  IF unit_price < 0: return ERROR

Step 2: Calculate Base Cost
  base_cost = quantity * unit_price

Step 3: Apply Tax (if applicable)
  IF tax_rate > 0:
    tax_amount = base_cost * (tax_rate / 100)
    total_cost = base_cost + tax_amount
  ELSE:
    total_cost = base_cost

Step 4: Round to 2 Decimals
  total_cost = ROUND(total_cost, 2)

Step 5: Return Result
  RETURN: {
    quantity: quantity,
    unit_price: unit_price,
    base_cost: base_cost,
    tax_amount: tax_amount (if applicable),
    total_cost: total_cost,
    currency: 'PHP'
  }

EXAMPLE:
──────
quantity = 100
unit_price = 250.00
tax_rate = 5

base_cost = 100 * 250.00 = 25,000.00
tax_amount = 25,000.00 * (5/100) = 1,250.00
total_cost = 25,000.00 + 1,250.00 = 26,250.00
```

---

## 3. USE CASE DIAGRAM (UML)

### Use Case Specifications

**UC1: Plan Distribution Event**
```
Actors: Program Manager, Admin
Precondition: User authenticated, program exists
Main Flow:
  1. User enters event details
  2. System validates barangay, program, date
  3. User selects distribution location
  4. System reserves location, budget
  5. Event confirmed → event_id assigned
Postcondition: Event created, ready for allocations
```

**UC2: Allocate Resources to Beneficiaries**
```
Actors: Program Manager
Precondition: Event exists and approved
Main Flow:
  1. User selects event
  2. System shows resources available
  3. User selects resource types + quantities
  4. System auto-selects beneficiaries
  5. System validates budget
  6. Allocations created and confirmed
Postcondition: Allocations stored, ready to distribute
```

**UC3: Execute Distribution**
```
Actors: Enumerator, Staff
Precondition: Allocations exist for event
Main Flow:
  1. Staff receives distribution list
  2. Staff distributes resources to beneficiaries
  3. Staff records received quantity
  4. Staff takes distribution photos
  5. Staff gets beneficiary signature/acknowledgment
Postcondition: Distribution marked complete, photos logged
```

**UC4: Verify Receipt & Generate Report**
```
Actors: Supervisor, Manager
Precondition: Distribution complete
Main Flow:
  1. User reviews allocation vs actual receipt
  2. System flags discrepancies
  3. Supervisor approves or flags for correction
  4. Generate distribution report
Postcondition: Report generated, discrepancies logged
```

---

## 4. DATA STORES SUMMARY

| ID | Name | Purpose | Est. Records |
|----|------|---------|--------------|
| D19 | Resource Types | Catalog of resources | 50+ |
| D20 | Distribution Events | Event management | 1,000+/year |
| D17 | Allocations | Resource assignments | 50,000+ |
| D21 | Distribution Locations | Venues | 100+ |
| D22 | Resource Logs | Transaction history | 100,000+/year |
| D23 | Distribution Photos | Event documentation | 10,000+/year |
| D24 | Verification Records | Receipt confirmation | 50,000+ |

---

## 5. BUSINESS RULES

1. **Budget Control**: Total allocations cannot exceed event budget
2. **Stock Management**: Allocated quantity cannot exceed available inventory
3. **Beneficiary Limits**: One beneficiary per resource type per event
4. **Distribution Timeline**: Must complete within 30 days of event date
5. **Photo Documentation**: At least 1 photo per distribution location
6. **Verification**: All distributions must have receipt verification

---

## DOCUMENT METADATA

- **Version**: 1.0
- **Status**: COMPLETE
- **Pages**: 10+ deliverable pages
- **Date**: 2026-04-15
- **For**: Project Management Assignment
