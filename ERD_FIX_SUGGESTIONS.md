# FFPRAMS ERD Fix Suggestions

This document lists practical fixes for your current ERD so it matches the latest schema direction and stays clean.

## Priority Fixes (Do First)

1. Use one schema version only
- Remove mixed old/new columns from the same table box.
- Base the ERD on latest migration direction, not historical snapshots.

2. Standardize program naming
- Use program_names and program_name_id consistently.
- Remove generic programs/program_id naming from diagram labels.

3. Keep one meaning per relationship
- Keep classifications as master list.
- Keep agency_classifications as junction mapping.
- Do not merge them into one table.

4. Remove fake connectors
- Remove allocations -> direct_assistance (no FK).
- Remove hard FK lines from polymorphic fields:
  - audit_logs.record_id
  - record_attachments.attachable_id

## Main ERD Connector Set (Clean Operational View)

Draw only these in the main canvas:

1. barangays.id -> beneficiaries.barangay_id
2. agencies.id -> beneficiaries.agency_id
3. agencies.id -> program_names.agency_id
4. agencies.id -> resource_types.agency_id
5. barangays.id -> distribution_events.barangay_id
6. resource_types.id -> distribution_events.resource_type_id
7. program_names.id -> distribution_events.program_name_id
8. distribution_events.id -> allocations.distribution_event_id
9. beneficiaries.id -> allocations.beneficiary_id
10. program_names.id -> allocations.program_name_id
11. resource_types.id -> allocations.resource_type_id
12. assistance_purposes.id -> allocations.assistance_purpose_id
13. beneficiaries.id -> direct_assistance.beneficiary_id
14. program_names.id -> direct_assistance.program_name_id
15. resource_types.id -> direct_assistance.resource_type_id
16. assistance_purposes.id -> direct_assistance.assistance_purpose_id

## Optional vs Mandatory (Quick Rule)

1. FK column NOT NULL -> Mandatory relationship to parent.
2. FK column nullable -> Optional relationship to parent.
3. Child-count side is usually Many Optional unless business rule enforces at least one child.

## Table-Level Fixes

### beneficiaries

Keep:
- barangay_id relation
- agency_id relation (or rename to primary_agency_id if you keep pivot as source of truth)

Check:
- classification strategy consistency with classifications table.

### classifications + agency_classifications

Keep both:
- classifications = vocabulary
- agency_classifications = mapping

Do not drop unless your business rule is fixed and global.

### agency_form_fields + agency_form_field_options

Use these for dynamic agency field modeling.
If legacy form_field_options is still shown, mark it legacy/appendix only.

### allocations + direct_assistance

Current risk:
- Two tables represent direct-release-like workflows.

Suggested direction:
- Choose one canonical transaction table for direct flow.
- Keep the other as legacy/archive path until fully migrated.

### record_attachments

Only hard FK to users.uploaded_by should be drawn.
Do not draw hard FK from attachable_type/attachable_id to business tables.

## Redundancy Cleanup Checklist

1. Remove duplicate parallel connector lines between same FK pair.
2. Keep one connector per FK pair.
3. Move admin/technical-only links to a separate technical ERD page.
4. Keep operational ERD readable (avoid long crossing lines).

## 3NF-Oriented Improvements

1. Remove duplicate storage of the same business fact.
- Example: avoid two active tables for one direct assistance transaction concept.

2. Reduce transitive risk.
- Event-linked allocations should not drift from event-level program/resource context.

3. Replace evolving enum domains with lookup tables.
- release outcomes
- status sets
- legal/compliance vocabularies

4. Add integrity checks.
- Ensure amount/quantity rules are valid by resource context.
- Prevent negative values where invalid.

## Concrete Schema Change Checklist

Use this as a practical migration plan. Items marked Conditional depend on your chosen final model.

### Tables To Add

| Table | Why | Status |
|---|---|---|
| release_statuses | Centralize workflow status values used across allocation/direct flows | Suggested |
| release_outcomes | Centralize release outcome vocabulary | Suggested |
| event_statuses | Centralize distribution event status domain | Suggested |
| legal_basis_types | Replace hardcoded legal basis enum with lookup values | Suggested |
| fund_sources | Replace hardcoded fund source enum with lookup values | Suggested |

### Tables To Remove or Deprecate

| Table | Action | Reason |
|---|---|---|
| form_field_options | Deprecate from operational ERD | Legacy option catalog, replaced by agency_form_fields + agency_form_field_options |
| direct_assistance | Conditional remove after data migration | Duplicates direct-flow concept already represented in allocations |

### Columns To Add / Rename / Remove

| Table | Add | Rename | Remove | Notes |
|---|---|---|---|---|
| beneficiaries | primary_agency_id (nullable FK to agencies, Conditional) | agency_id -> primary_agency_id (Conditional) | agency_id (only if fully pivot-driven) | Keep one agency source of truth with beneficiary_agencies |
| beneficiaries | classification_id (nullable FK to classifications, Conditional) | - | classification enum/text (Conditional) | Align classification to lookup strategy |
| allocations | release_status_id (FK to release_statuses) | - | release_outcome enum (if moved to lookup) | Can also keep release_outcome_id as separate FK |
| allocations | release_outcome_id (FK to release_outcomes) | - | - | Normalized status/outcome domain |
| allocations | check/context columns for event-vs-direct integrity | - | - | Enforce consistency when distribution_event_id is not null |
| distribution_events | event_status_id (FK to event_statuses) | - | status enum | Normalize event status vocabulary |
| distribution_events | legal_basis_type_id (FK to legal_basis_types) | - | legal_basis_type enum | Remove hardcoded legal basis enum |
| distribution_events | fund_source_id (FK to fund_sources) | - | fund_source enum | Remove hardcoded fund source enum |
| program_names | - | - | - | Keep agency_id FK and classification strategy consistent |
| direct_assistance | legacy_allocation_id (nullable FK, Conditional) | - | - | Temporary bridge during migration if consolidating into allocations |
| direct_assistance | - | - | duplicated workflow columns (Conditional) | Drop only after full functional migration |

### Index and Constraint Additions

| Table | Change |
|---|---|
| allocations | Add composite index for direct flow queries: (release_method, distributed_at, status/release_status_id) |
| allocations | Add check: if financial then amount not null and quantity null; if physical then quantity not null |
| allocations | Add check: amount >= 0 and quantity >= 0 |
| distribution_events | Add FK indexes for new lookup IDs (event_status_id, legal_basis_type_id, fund_source_id) |
| beneficiaries | Add unique/consistency rules for pivot + primary agency strategy |

### Safe Implementation Order

1. Add new lookup tables and seed values.
2. Add new FK columns as nullable.
3. Backfill FK values from existing enums/text.
4. Update application reads/writes to new columns.
5. Add constraints and non-null rules where applicable.
6. Drop deprecated columns/tables only after verification.

## Suggested Two-Diagram Strategy

1. Operational ERD (for presentation)
- Only core business flow tables and required connectors.

2. Technical ERD (for implementation)
- Include pivots, audit, attachments, dynamic metadata tables.
- Include nullable/mandatory markers and index notes.

## Final Review Before Submission

1. No programs/program_id naming remains.
2. No allocations -> direct_assistance connector.
3. No hard FK lines from polymorphic fields.
4. Optional/mandatory markers match FK nullability.
5. Main ERD stays readable without excessive crossing lines.
