# MODULE 2: RESOURCE ALLOCATION & DISTRIBUTION - DATA FLOW DIAGRAM

**Document**: DFD Specifications (Revised - Core Elements Only)
**Module**: Resource Allocation & Distribution System
**Date**: 2026-04-15

---

## DFD LEVEL 0 - SYSTEM CONTEXT

### External Entities
- **E1**: Program Manager/Supervisor (user)
- **E2**: File Storage (photos, documents)
- **E3**: Database System

### System Boundary: FFPRAMS Resource Allocation & Distribution

**Main Functions:**
- P0: Plan distribution events
- Allocate resources to beneficiaries
- Execute and verify distribution
- Generate reports and audit records

### Level 0 Data Flows

| Flow | From | To | Data |
|------|------|-----|------|
| 1 | User | System | Event creation, allocation data, distribution instructions |
| 2 | System | User | Results, confirmations, reports |
| 3 | System | File Storage | Save distribution photos and documents |
| 4 | File Storage | System | File metadata and paths |
| 5 | System | Database | INSERT/UPDATE/SELECT/DELETE operations |
| 6 | Database | System | Query results, event and allocation records |

---

## DFD LEVEL 1 - MAIN PROCESSES

Six main processes:

| Process | Name | Function |
|---------|------|----------|
| **P1** | Plan Distribution Event | Create distribution event with budget and resource planning |
| **P2** | Allocate Resources | Assign resources to beneficiaries and calculate costs |
| **P3** | Distribute Resources | Execute distribution and track actual receipt |
| **P4** | Record Distribution | Document distribution with photos and transaction logs |
| **P5** | Verify Receipt | Confirm beneficiary receipt and reconcile quantities |
| **P6** | Generate Reports | Create distribution and budget reports |

### Process Specifications - Level 1

#### **P1: PLAN DISTRIBUTION EVENT**
- **Input**: Event details (name, date, location, program, barangay, budget)
- **Processing**: Validate event data → Reserve location → Approve budget → Create event record
- **Output**: event_id, confirmation
- **Data Stores**: D20 (events), D23 (locations), D24 (programs)

#### **P2: ALLOCATE RESOURCES**
- **Input**: event_id, resource selections with quantities and beneficiary list
- **Processing**: Validate resources → Calculate costs → Match to beneficiaries → Validate budget
- **Output**: allocation_ids, allocation summary
- **Data Stores**: D22 (allocations), D26 (direct_assistance), D19 (resources), D1 (beneficiaries)

#### **P3: DISTRIBUTE RESOURCES**
- **Input**: allocation_ids to mark as distributed
- **Processing**: Generate distribution lists → Record distribution → Update allocation status
- **Output**: distribution confirmation, receipt records
- **Data Stores**: D22 (allocations), D25 (resource_logs)

#### **P4: RECORD DISTRIBUTION**
- **Input**: Distribution evidence (photos, signatures, notes)
- **Processing**: Validate and store photos → Link to event → Create transaction logs
- **Output**: photo_id, log_id
- **Data Stores**: D27 (photos), D25 (resource_logs)

#### **P5: VERIFY RECEIPT**
- **Input**: Beneficiary confirmation of resource receipt
- **Processing**: Verify expected vs actual quantities → Reconcile → Update allocation status
- **Output**: verification_id, status update
- **Data Stores**: D28 (verification), D22 (allocations)

#### **P6: GENERATE REPORTS**
- **Input**: Report filters (date range, program, barangay, resource type)
- **Processing**: Query allocations → Sum distributions → Calculate statistics → Format output
- **Output**: PDF/Excel report with summary and details
- **Data Stores**: D22, D20, D19, D1 (read-only)

---

## DFD LEVEL 2 - ALLOCATE RESOURCES (P2) DECOMPOSITION

### Sub-processes of P2

```
P2.1 → Validate Event Exists (query D20)
P2.2 → Retrieve Event Details (event metadata, program, budget)
P2.3 → Validate Resources (existence, stock, pricing)
P2.4 → Calculate Allocation Costs (quantity × unit_price)
P2.5 → Validate Budget (verify cost ≤ event budget)
P2.6 → Select Beneficiaries (filter eligible beneficiaries)
P2.7 → Create Allocations (insert to D22)
P2.8 → Create Direct Assistance (if applicable - insert to D26)
P2.9 → Create Audit Log (insert to D21)
```

### Level 2 Data Flows

| Process | Reads | Writes | Data |
|---------|-------|--------|------|
| P2.1 | D20 | - | Event lookup |
| P2.3 | D19 | - | Resource validation |
| P2.4 | - | - | Cost calculation (in-memory) |
| P2.5 | - | - | Budget comparison |
| P2.6 | D1 | - | Beneficiary query |
| P2.7 | - | D22 | INSERT allocations |
| P2.8 | - | D26 | INSERT direct_assistance records |
| P2.9 | - | D21 | INSERT audit log |

---

## DFD LEVEL 3 - VALIDATE BUDGET (P2.5) & CALCULATE COST (P2.4)

### P2.4: CALCULATE TOTAL COST (Terminal Process)

**Input**:
- allocations array with {quantity, unit_price} for each
- event_budget (available budget)

**Algorithm**:
1. Initialize total_cost = 0
2. FOR each allocation:
   - allocation_cost = quantity × unit_price
   - total_cost += allocation_cost
3. FOR each direct_assistance (if any):
   - da_cost = amount
   - total_cost += da_cost
4. Return total_cost

**Output**: {total_cost: decimal, allocation_count: integer, status: "CALCULATED"}

### P2.5: VALIDATE BUDGET (Terminal Process)

**Input**:
- total_cost (calculated)
- event_budget (available)

**Validation Logic**:
1. IF total_cost > event_budget:
   - Return ERROR: "Total cost ($X) exceeds budget ($Y)"
2. ELSE:
   - remaining_budget = event_budget - total_cost
   - Return SUCCESS with remaining_budget

**Output**: {status: SUCCESS|FAIL, remaining_budget: decimal, error_message: string}

---

## DATA STORES DICTIONARY

| ID | Name | Database Table | Purpose |
|----|------|---------|---------|
| **D1** | Beneficiaries | beneficiaries | Link to Module 1 (beneficiary records) |
| **D19** | Resource Types | resource_types | Resource catalog (seeds, fertilizer, tools, cash) |
| **D20** | Distribution Events | distribution_events | Main distribution event records |
| **D21** | Audit Logs | audit_logs | System activity and transaction history |
| **D22** | Allocations | allocations | Core allocation records (event → beneficiary → resource) |
| **D23** | Locations | distribution_locations | Distribution venue data (coordinates, capacity, contact) |
| **D24** | Programs | program_names | Program information (name, agency, description) |
| **D25** | Resource Logs | resource_logs | Transaction log (who, what, when, quantity changes) |
| **D26** | Direct Assistance | direct_assistance | Direct cash/assistance distribution records |
| **D27** | Photos | distribution_photos | Distribution event photos (metadata and file paths) |
| **D28** | Verification | allocation_verification | Receipt verification records |

### Key Tables

**D22: allocations**
- Attributes: id (PK), event_id (FK), beneficiary_id (FK), resource_id (FK), quantity, unit_price, total_cost, status, distributed_at

**D26: direct_assistance**
- Attributes: id (PK), beneficiary_id (FK), program_id (FK), amount, status (planned/ready/released/completed/not_received), created_at

---

## SUMMARY

**Total Levels**: 3 (Level 0-3)
**Main Processes (Level 1)**: 6
**Sub-processes (Level 2)**: 9 (P2 decomposition)
**Terminal Processes (Level 3)**: 2 (Calculate Cost, Validate Budget)
**Data Stores**: 11
**External Entities**: 3

All processes align with actual system implementation in DistributionEventController, AllocationController, and DirectAssistanceController.
