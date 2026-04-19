# FFPRAMS ERD Connector Guide

This guide tells you exactly which connectors to draw in your Draw.io ERD using Crow's Foot notation.

Validation scope:

- Checked against migration files in `database/migrations`.
- Live DB query was not executed in this session because `php` is not available in terminal PATH.
- If your local DB is behind migrations, treat the "Required/Optional" notes below as target state after full migration.

## Connector Legend (Crow's Foot)

- One-to-many: parent side = `|`, child side = crow's foot
- Optional FK (nullable): put a small circle on child side (`o<`)
- Required FK (not nullable): child side is mandatory (`|<`)
- Relationship label to write on line: `MANDATORY` for `|<`, `OPTIONAL` for `o<`

Use orthogonal connectors for readability.

## Draw.io Connector Description Mapping

If Draw.io shows labels like "Many Optional to Many Mandatory", use this mapping:

1. `Many Optional to Many Mandatory`
- Meaning: `0..*` on one side, `1..*` on the other side.
- Use case: Rare in normalized physical DB design.
- In this project: Usually not used directly; replace with a junction table (`beneficiary_agencies`, `agency_classifications`) and two 1-to-many links.

2. `Many Optional to One Mandatory`
- Meaning: `0..*` child rows can exist per parent, but each child must reference exactly one parent.
- Typical FK case: Parent can have none/many children; child has required FK.
- Example here: `beneficiaries` -> `beneficiary_attachments` (if using that table).

3. `Many Optional to One Optional`
- Meaning: Parent may have `0..*` children, and child FK can be null.
- Use when FK column is nullable.
- Example here: `distribution_events` -> `allocations` (`distribution_event_id` is nullable for direct releases).

4. `Many Mandatory to One Mandatory`
- Meaning: Parent must have at least one child and child must have one parent.
- Use carefully: This is a business-rule relationship, not just FK structure.
- In this project: Avoid unless you enforce minimum-child rules in app/database logic.

5. `One Mandatory to One Mandatory`
- Meaning: strict 1:1 required both sides.
- In this project: Not a primary pattern in current core tables.

Quick rule for your schema:

1. If child FK is `NOT NULL`, pick connector ending in `One Mandatory` on parent side.
2. If child FK is nullable, pick connector ending in `One Optional` on parent side.
3. On the child-count side, use `Many Optional` unless business rules force at least one child.

Recommended default in FFPRAMS:

- Use `Many Optional to One Mandatory` for most required FKs.
- Use `Many Optional to One Optional` for nullable FKs.
- Do not use direct many-to-many connectors in physical ERD; show the pivot table instead.

## Core Connectors To Draw

### 1) Geography and Beneficiaries

1. `barangays.id` -> `beneficiaries.barangay_id`
- Cardinality: One barangay to many beneficiaries
- FK optionality: Required child (`beneficiaries.barangay_id` is required)

2. `agencies.id` -> `beneficiaries.agency_id`
- Cardinality: One agency to many beneficiaries
- FK optionality: Optional child (`beneficiaries.agency_id` nullable)

3. `beneficiaries.id` -> `sms_logs.beneficiary_id`
- Cardinality: One beneficiary to many SMS logs
- FK optionality: Required child

### 2) Agencies, Programs, and Resources

4. `agencies.id` -> `users.agency_id`
- Cardinality: One agency to many users
- FK optionality: Optional child (`users.agency_id` nullable)

5. `agencies.id` -> `resource_types.agency_id`
- Cardinality: One agency to many resource types
- FK optionality: Optional child (`resource_types.agency_id` nullable)

6. `agencies.id` -> `program_names.agency_id`
- Cardinality: One agency to many program names
- FK optionality: Required child

7. `program_names.id` -> `program_legal_requirements.program_name_id`
- Cardinality: One program to many legal requirement files
- FK optionality: Required child

8. `users.id` -> `program_legal_requirements.uploaded_by`
- Cardinality: One user to many uploaded legal files
- FK optionality: Optional child (`uploaded_by` nullable)

### 3) Dynamic Agency System

9. `agencies.id` -> `agency_classifications.agency_id`
- Cardinality: One agency to many agency_classifications rows
- FK optionality: Required child

10. `classifications.id` -> `agency_classifications.classification_id`
- Cardinality: One classification to many agency_classifications rows
- FK optionality: Required child

11. `agencies.id` -> `agency_form_fields.agency_id`
- Cardinality: One agency to many agency form fields
- FK optionality: Required child

12. `agency_form_fields.id` -> `agency_form_field_options.agency_form_field_id`
- Cardinality: One form field to many selectable options
- FK optionality: Required child

13. `beneficiaries.id` -> `beneficiary_agencies.beneficiary_id`
- Cardinality: One beneficiary to many beneficiary_agencies rows
- FK optionality: Required child

14. `agencies.id` -> `beneficiary_agencies.agency_id`
- Cardinality: One agency to many beneficiary_agencies rows
- FK optionality: Required child

### 4) Distribution Event Flow

15. `barangays.id` -> `distribution_events.barangay_id`
- Cardinality: One barangay to many distribution events
- FK optionality: Required child

16. `resource_types.id` -> `distribution_events.resource_type_id`
- Cardinality: One resource type to many distribution events
- FK optionality: Required child

17. `program_names.id` -> `distribution_events.program_name_id`
- Cardinality: One program to many distribution events
- FK optionality: Required child in current schema

18. `users.id` -> `distribution_events.created_by`
- Cardinality: One user to many created distribution events
- FK optionality: Required child

19. `users.id` -> `distribution_events.beneficiary_list_approved_by`
- Cardinality: One user to many approvals
- FK optionality: Optional child (`beneficiary_list_approved_by` nullable)

### 5) Allocation and Direct Assistance

20. `distribution_events.id` -> `allocations.distribution_event_id`
- Cardinality: One event to many allocations
- FK optionality: Optional child in current schema (supports direct allocations)

21. `beneficiaries.id` -> `allocations.beneficiary_id`
- Cardinality: One beneficiary to many allocations
- FK optionality: Required child

22. `program_names.id` -> `allocations.program_name_id`
- Cardinality: One program to many allocations
- FK optionality: Optional child

23. `resource_types.id` -> `allocations.resource_type_id`
- Cardinality: One resource type to many allocations
- FK optionality: Optional child

24. `assistance_purposes.id` -> `allocations.assistance_purpose_id`
- Cardinality: One purpose to many allocations
- FK optionality: Optional child

25. `beneficiaries.id` -> `direct_assistance.beneficiary_id`
- Cardinality: One beneficiary to many direct assistance rows
- FK optionality: Required child

26. `program_names.id` -> `direct_assistance.program_name_id`
- Cardinality: One program to many direct assistance rows
- FK optionality: Required child

27. `resource_types.id` -> `direct_assistance.resource_type_id`
- Cardinality: One resource type to many direct assistance rows
- FK optionality: Required child

28. `assistance_purposes.id` -> `direct_assistance.assistance_purpose_id`
- Cardinality: One purpose to many direct assistance rows
- FK optionality: Optional child

29. `users.id` -> `direct_assistance.created_by`
- Cardinality: One user to many created direct assistance rows
- FK optionality: Required child

30. `users.id` -> `direct_assistance.distributed_by`
- Cardinality: One user to many distributed direct assistance rows
- FK optionality: Optional child

31. `distribution_events.id` -> `direct_assistance.distribution_event_id`
- Cardinality: One event to many direct assistance rows
- FK optionality: Optional child

### 6) Audit Trail

32. `users.id` -> `audit_logs.user_id`
- Cardinality: One user to many audit log rows
- FK optionality: Required child

33. `users.id` -> `record_attachments.uploaded_by`
- Cardinality: One user to many uploaded attachments
- FK optionality: Optional child (`uploaded_by` nullable)
- Notes: `record_attachments.attachable_type` + `record_attachments.attachable_id` is polymorphic; do not draw hard FK lines from those fields.

## Tables In Your Diagram That Are Standalone (No FK connector)

1. `form_field_options`
- Legacy system options table (not FK-linked to other tables).

2. `sessions`
- Has `user_id` index but no FK constraint in migration.

3. `audit_logs.table_name` + `audit_logs.record_id`
- Polymorphic-style reference by value, not a real FK; do not draw hard FK connectors from these two fields.

4. `record_attachments.attachable_type` + `record_attachments.attachable_id`
- Polymorphic reference; do not draw hard FK connectors from these two fields.

## Realistic Diagram Notes

If your goal is a clean presentation ERD (not a full physical schema dump), prioritize these as must-show connectors:

1. `barangays` -> `beneficiaries`
2. `agencies` -> `beneficiaries`
3. `agencies` -> `program_names`
4. `agencies` -> `resource_types`
5. `barangays` -> `distribution_events`
6. `resource_types` -> `distribution_events`
7. `program_names` -> `distribution_events`
8. `distribution_events` -> `allocations`
9. `beneficiaries` -> `allocations`
10. `beneficiaries` -> `direct_assistance`
11. `program_names` -> `direct_assistance`
12. `resource_types` -> `direct_assistance`

## Final Non-Redundant Connector Set (Use This)

Draw only these in your main ERD canvas to keep it clean and realistic:

1. `barangays.id` -> `beneficiaries.barangay_id` (`MANDATORY`, `Many Optional to One Mandatory`)
2. `agencies.id` -> `beneficiaries.agency_id` (`OPTIONAL`, `Many Optional to One Optional`)
3. `agencies.id` -> `program_names.agency_id` (`MANDATORY`, `Many Optional to One Mandatory`)
4. `agencies.id` -> `resource_types.agency_id` (`OPTIONAL`, `Many Optional to One Optional`)
5. `barangays.id` -> `distribution_events.barangay_id` (`MANDATORY`, `Many Optional to One Mandatory`)
6. `resource_types.id` -> `distribution_events.resource_type_id` (`MANDATORY`, `Many Optional to One Mandatory`)
7. `program_names.id` -> `distribution_events.program_name_id` (`MANDATORY`, `Many Optional to One Mandatory`)
8. `distribution_events.id` -> `allocations.distribution_event_id` (`OPTIONAL`, `Many Optional to One Optional`)
9. `beneficiaries.id` -> `allocations.beneficiary_id` (`MANDATORY`, `Many Optional to One Mandatory`)
10. `program_names.id` -> `allocations.program_name_id` (`OPTIONAL`, `Many Optional to One Optional`)
11. `resource_types.id` -> `allocations.resource_type_id` (`OPTIONAL`, `Many Optional to One Optional`)
12. `assistance_purposes.id` -> `allocations.assistance_purpose_id` (`OPTIONAL`, `Many Optional to One Optional`)
13. `beneficiaries.id` -> `direct_assistance.beneficiary_id` (`MANDATORY`, `Many Optional to One Mandatory`)
14. `program_names.id` -> `direct_assistance.program_name_id` (`MANDATORY`, `Many Optional to One Mandatory`)
15. `resource_types.id` -> `direct_assistance.resource_type_id` (`MANDATORY`, `Many Optional to One Mandatory`)
16. `assistance_purposes.id` -> `direct_assistance.assistance_purpose_id` (`OPTIONAL`, `Many Optional to One Optional`)

## Draw.io Label Per Final Connector (Exact Pick List)

Use these exact Draw.io relationship labels when drawing each final connector:

1. `barangays.id` -> `beneficiaries.barangay_id`
- Draw.io label: `Many Optional to One Mandatory`

2. `agencies.id` -> `beneficiaries.agency_id`
- Draw.io label: `Many Optional to One Optional`

3. `agencies.id` -> `program_names.agency_id`
- Draw.io label: `Many Optional to One Mandatory`

4. `agencies.id` -> `resource_types.agency_id`
- Draw.io label: `Many Optional to One Optional`

5. `barangays.id` -> `distribution_events.barangay_id`
- Draw.io label: `Many Optional to One Mandatory`

6. `resource_types.id` -> `distribution_events.resource_type_id`
- Draw.io label: `Many Optional to One Mandatory`

7. `program_names.id` -> `distribution_events.program_name_id`
- Draw.io label: `Many Optional to One Mandatory`

8. `distribution_events.id` -> `allocations.distribution_event_id`
- Draw.io label: `Many Optional to One Optional`

9. `beneficiaries.id` -> `allocations.beneficiary_id`
- Draw.io label: `Many Optional to One Mandatory`

10. `program_names.id` -> `allocations.program_name_id`
- Draw.io label: `Many Optional to One Optional`

11. `resource_types.id` -> `allocations.resource_type_id`
- Draw.io label: `Many Optional to One Optional`

12. `assistance_purposes.id` -> `allocations.assistance_purpose_id`
- Draw.io label: `Many Optional to One Optional`

13. `beneficiaries.id` -> `direct_assistance.beneficiary_id`
- Draw.io label: `Many Optional to One Mandatory`

14. `program_names.id` -> `direct_assistance.program_name_id`
- Draw.io label: `Many Optional to One Mandatory`

15. `resource_types.id` -> `direct_assistance.resource_type_id`
- Draw.io label: `Many Optional to One Mandatory`

16. `assistance_purposes.id` -> `direct_assistance.assistance_purpose_id`
- Draw.io label: `Many Optional to One Optional`

Direction tip while drawing:

1. Start connector from child table (FK column) to parent table (PK `id`).
2. If your visual looks reversed, keep it; cardinality meaning is still correct as long as ends are attached to the right tables.

Add these only in full technical ERD version:

1. `agencies` <-> `agency_classifications` <-> `classifications`
2. `agencies` -> `agency_form_fields` -> `agency_form_field_options`
3. `beneficiaries` -> `beneficiary_agencies` <- `agencies`
4. `users` -> `audit_logs`
5. `users` -> `program_legal_requirements`
6. `users` -> `record_attachments`
7. `beneficiaries` -> `sms_logs`

## Redundant / Wrong Connectors To Remove

1. `allocations` -> `direct_assistance` (no FK exists)
2. `programs` box connectors (replace with `program_names`)
3. Hard FK lines from `audit_logs.record_id` to operational tables (value reference only)
4. Hard FK lines from `record_attachments.attachable_id` to operational tables (polymorphic reference)
5. Duplicate parallel lines for the same FK pair on the same canvas

## Suggested DB Improvements (Normalize + Clean)

1. Resolve dual agency modeling on beneficiaries
- Current design has both `beneficiaries.agency_id` and `beneficiary_agencies` pivot.
- Cleaner options:
	- Option A: keep pivot as source of truth and remove `beneficiaries.agency_id`.
	- Option B: keep `beneficiaries.agency_id` as `primary_agency_id` and enforce consistency with pivot.

2. Consolidate direct release data model
- You currently use both `allocations` (`release_method='direct'`) and `direct_assistance`.
- Pick one canonical transactional table to avoid duplicate workflow logic and reporting drift.

3. Normalize status domains
- Status values are spread across event/allocation/direct tables.
- Standardize status enum sets and transition rules in one service/policy layer.

4. Consider lookup tables for frequently changing enums
- Move business enums (`release_outcome`, legal/fund/liquidation domains) to lookup tables if these are expected to evolve.

5. Add check constraints for value integrity (if MySQL version supports)
- Enforce at least one of `quantity` or `amount` depending on resource type context.
- Enforce non-negative numeric constraints.

6. Clarify soft-delete uniqueness strategy
- Keep unique indexes aligned with soft delete behavior for all key pairings (similar to your allocations fix).

7. Keep legacy tables out of main ERD
- Show `form_field_options` only in legacy/appendix diagram if still physically present.

## Strict 3NF Target (Before -> After)

Use this as your practical normalization blueprint.

| Area | Current (Before) | 3NF Target (After) | Benefit |
|---|---|---|---|
| Beneficiary-Agency relation | `beneficiaries.agency_id` plus `beneficiary_agencies` pivot | Keep `beneficiary_agencies` as source of truth; replace `beneficiaries.agency_id` with optional `primary_agency_id` only if needed for UX | Removes duplicated relationship meaning and update anomalies |
| Direct distribution flow | Both `allocations` (`release_method='direct'`) and `direct_assistance` carry similar facts | Keep one transactional table only (recommended: keep `allocations` and archive/merge `direct_assistance`) | Single source of truth for reports, workflow, and audits |
| Program/resource fields on allocations | `allocations.program_name_id` and `allocations.resource_type_id` can duplicate event-derived context | Keep these fields only for direct rows (`distribution_event_id IS NULL`), enforce consistency rule for event rows | Prevents transitive inconsistency between event and allocation rows |
| Status domains | Multiple enum-like status sets spread across tables | Create lookup tables (`release_statuses`, `release_outcomes`, `event_statuses`) and reference by FK | Central governance of allowed states |
| Polymorphic attachments | `record_attachments.attachable_type` and `attachable_id` | Keep polymorphic if flexibility needed, or split to typed join tables for strict FK model | Better integrity guarantees if strict mode required |
| Legacy option catalogs | `form_field_options` and new agency-driven metadata both present | Keep only active metadata model and deprecate legacy options table from operational model | Cleaner schema surface and less ambiguity |

### Minimal 3NF Rule Set To Enforce

1. One business fact lives in one place only.
2. No table stores attributes derivable from another non-key attribute path.
3. All changing domain vocabularies use lookup tables, not repeated enums.
4. Optional convenience columns must be declared as derived/cache fields and validated.

### Phased Migration Plan (Low Risk)

1. Phase 1: Add constraints/checks
- Add validation constraints for allocation consistency (`event` rows must align with event program/resource).

2. Phase 2: Converge direct workflow
- Backfill one canonical transactional table and switch reads/reports to it.

3. Phase 3: Remove duplicate relationship paths
- Drop redundant agency relation column or formally rename it to `primary_agency_id`.

4. Phase 4: Replace enums with lookup FKs
- Introduce lookup tables and migrate existing values.

5. Phase 5: Archive/deprecate legacy tables from main ERD
- Keep historical data accessible, but remove deprecated structures from operational diagram.

## Important Note About "PROGRAMS" Box In Diagram

Your current migrations create `program_names` and do not create a `programs` table. If your ERD still shows `programs`, mark it as deprecated or remove it to avoid confusion.

## Suggested Draw.io Layout Order

1. Place master tables first: `agencies`, `classifications`, `barangays`, `users`, `resource_types`, `program_names`, `assistance_purposes`.
2. Place pivots/definitions second: `agency_classifications`, `agency_form_fields`, `agency_form_field_options`, `beneficiary_agencies`.
3. Place operations third: `beneficiaries`, `distribution_events`, `allocations`, `direct_assistance`, `sms_logs`, `program_legal_requirements`, `audit_logs`.
4. Route all optional connectors from child side using optional marker (`o<`) so required vs optional is visually clear.

## Quick Checklist Before Finalizing ERD

- Every FK field has one connector to its referenced PK.
- Required FK fields are shown as mandatory crow's foot connectors.
- Nullable FK fields are shown as optional crow's foot connectors.
- `program_names` is used instead of `programs`.
- `beneficiaries.agency_id` connector is present (`agencies` -> `beneficiaries`).
- If `record_attachments` is shown, only draw `users` -> `record_attachments` as hard FK.
- No fake FK line from `audit_logs.record_id` to every table.
- No `allocations` -> `direct_assistance` connector.
