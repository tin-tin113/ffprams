# FFPRAMS Main Processes (Step by Step)

This document lists the main operational processes of FFPRAMS in simple, step-by-step form.

## A) Initial Setup Process (One-Time)

1. Install project dependencies and setup environment.
2. Configure database and SMS credentials in `.env`.
3. Run migrations and seeders.
4. Login using default Admin account.
5. Open Admin Settings and verify master data:
   - Agencies
   - Program Names
   - Resource Types
   - Assistance Purposes
   - Form Field Options

## B) User Access and Role Process

1. Admin creates user accounts (self-registration is disabled).
2. Assign role to each user:
   - Admin
   - Staff
3. Users login and access allowed modules.

Role rules:

- Admin has full control, including user management and system settings.
- Staff handles operations (beneficiaries, events, allocations, reports, SMS, map).
- Some sensitive actions are Admin-only (for example: completing events, deleting records in protected flows).

## C) Master Data Maintenance Process (Admin)

1. Go to Admin Settings.
2. Maintain Agencies.
3. Maintain Program Names (per agency).
4. Maintain Resource Types (with unit and agency).
5. Maintain Assistance Purposes.
6. Maintain Form Field Options used in beneficiary forms.

Important:

- Program Names are required in event creation.
- Allocations are expected to be tied to a Program Name in normal app workflow.

## D) Beneficiary Registration and Maintenance Process

1. Login as Admin or Staff.
2. Go to Beneficiaries.
3. Click Add New.
4. Fill required common fields:
   - Full name
   - Sex
   - Date of birth
   - Home address
   - Barangay
   - Contact number
   - Civil status
   - Classification
   - Registered date
5. Fill required agency/classification fields:
   - DA or Farmer/Both: farm details
   - BFAR or Fisherfolk/Both: fisherfolk details
   - DAR: CLOA/EP number required plus ARB details
6. Save.
7. Duplicate detection runs automatically.
8. If duplicate is detected, creation is blocked and user is redirected to the existing record.
9. If no duplicate is found, beneficiary is saved as Active.
10. SMS registration confirmation is sent if contact number is available.

## D2) Pre-Event Beneficiary List Review and Approval (Admin)

This happens after beneficiary maintenance and before event creation/allocation.

1. Admin opens the proposed beneficiary list for the target barangay/program.
2. Review and remove duplicates.
3. Exclude inactive beneficiaries.
4. Exclude ineligible entries based on current program criteria.
5. Finalize and approve the clean list for event use (Approve Beneficiary List action in Event Details).

Why this step exists:

- This aligns with actual municipal review practice before distribution scheduling.
- System rule: event status cannot move from Pending to Ongoing until this approval is recorded.

## E) Distribution Event Process

1. Go to Distribution Events.
2. Click Create Event.
3. Fill required fields:
   - Barangay
   - Resource Type
   - Program Name
   - Distribution Date
   - Type (Physical or Financial)
   - Total Fund Amount (required for Financial)
4. For Financial events, encode legal/compliance metadata before start:
   - Legal basis type, reference number, and date
   - Fund source and trust account details (if applicable)
   - Liquidation status and due date
   - FARMC endorsement requirement and reference (for applicable fishery cases)
   - Conditional rules enforced by system:
     - If Legal Basis Type = Other, remarks are required.
     - If Fund Source = LGU Trust Fund, trust account code is required.
     - If Liquidation Status = Pending/Submitted/Verified, liquidation due date is required.
     - If Liquidation Status = Submitted/Verified, submitted date/time and liquidation reference no. are required.
     - If FARMC endorsement is required, FARMC reference no. is required.
5. Save. Event starts as Pending.
6. Move status forward as work progresses:
   - Pending -> Ongoing -> Completed

Outputs prepared during event operations:

- Printable distribution list per event/program/barangay.
- List contains at minimum beneficiary name, barangay, allocation quantity/amount, and signature column for acknowledgement.
- This printable list acts as the local turnover/acknowledgement sheet used during release.

Rules:

- Status cannot move backward.
- Only Pending events can be edited/deleted.
- Only Admin can set event to Completed.
- Event cannot start (Pending -> Ongoing) until beneficiary list approval is completed by Admin.
- Financial events cannot start without legal basis and fund source details.
- Financial events use strict conditional validation for legal/fund/liquidation/FARMC fields while in Pending.
- Financial events cannot be completed until liquidation is verified.
- If FARMC endorsement is required, event start is blocked until endorsement is encoded.

## F) Allocation Process (Event-Based)

### F1) Single Allocation

1. Open an event.
2. Select one beneficiary.
3. Enter allocation details.
4. Save.

Validation:

- Beneficiary must be in the same barangay as the event.
- Duplicate allocation for same event + beneficiary is blocked.
- Physical event requires quantity.
- Financial event requires amount.

After save:

- Allocation is created under the event program.
- SMS notice is sent if contact number is available.

### F2) Bulk Allocation

1. Open an event.
2. Use bulk allocation input.
3. Submit rows.

System behavior:

- Skips beneficiaries from other barangays.
- Skips duplicates in request.
- Skips beneficiaries already allocated in the event.
- Saves valid rows.
- Sends SMS notices for saved rows (with contact number).

## G) Allocation Process (Direct Assistance)

This is used when release is not tied to an event.

Expected use cases:

- Emergency releases that cannot wait for the next scheduled event.
- Individual walk-in claims validated by authorized staff.
- Correction releases for prior encoding or release gaps.

Documentation note:

- This is a system extension for operational flexibility and is outside the standard event-first flow.

1. Go to Assistance Allocation.
2. Select Release Method: Direct.
3. Select Beneficiary.
4. Select Program Name.
5. Select Resource Type.
6. Enter quantity (physical) or amount (financial).
7. Save.

After save:

- Direct allocation is stored.
- SMS notice is sent if contact number is available.

## H) Distribution Confirmation Process

1. Open allocation list/event details.
2. Record release outcome once actual release is done.
3. Use Distribute/Mark Released for Received outcome.
4. Use Not Received button when beneficiary did not receive assistance in that release schedule.

Rules:

- Cannot mark distributed if event is still Pending.
- Cannot finalize the same allocation outcome twice.
- Agency paper practice usually tracks two outcomes: Received and Not Received.
- System now supports both outcomes as explicit final states.

## I) SMS Communication Process

### I1) Automatic SMS

1. Registration SMS is sent after successful beneficiary creation.
2. Allocation SMS is sent after successful single/bulk/direct allocation.

### I2) Manual/Broadcast SMS

1. Open SMS module.
2. Choose recipient type:
   - All active beneficiaries
   - By barangay
   - By classification
   - Selected beneficiaries
3. Preview recipients.
4. Send message.
5. Review sent/failed counts and SMS logs.

## J) Reports Process

1. Open Reports module.
2. Review built-in summaries, including:
   - Beneficiaries per barangay
   - Resource distribution summary
   - Distribution status per barangay
   - Unreached beneficiaries
   - Monthly distribution summary
   - Financial assistance summaries
3. Use reports for planning, validation, and compliance reporting.
4. After event closure, generate and review the distribution summary report for agency/LGU submission.
5. Event Details page shows a post-completion prompt that links directly to Reports.
6. Compliance snapshot in Reports highlights missing legal basis, pending/overdue liquidation, and FARMC-pending events.

## K) Geo-Map Monitoring Process

1. Open Geo-Map module.
2. Optionally filter by agency.
3. Review barangay pins and colors by distribution status.
4. Check per-barangay metrics:
   - Beneficiary counts
   - Event counts
   - Allocation/distribution counts
   - Coverage rate
   - Financial totals
   - Resource types distributed

## L) Admin User Management Process

1. Admin opens User Management.
2. Create users (Admin/Staff).
3. Edit user profile/role when needed.
4. Delete users when no longer needed.

Rules:

- Admin cannot delete own account.
- Role controls module access and sensitive actions.

## M) Audit and Traceability Process

1. System automatically logs create/update/delete actions for key modules.
2. Logs include user, action, table, and value changes.
3. Use logs for accountability and troubleshooting.

## N) Daily Operations Quick Flow

1. Verify Program Names, Resource Types, and Purposes are ready.
2. Register or update beneficiaries.
3. Admin reviews and approves the clean beneficiary list (remove duplicate/inactive/ineligible entries).
4. Create event with correct Program Name.
5. Move event to Ongoing.
6. Allocate beneficiaries (single or bulk) and/or add direct assistance.
7. Print and use the distribution list with acknowledgement signatures during release.
8. Record release outcomes per beneficiary (Received or Not Received).
9. Admin marks event Completed.
10. For financial events, ensure liquidation status reaches Verified before completion.
11. Generate and review distribution summary report for agency submission.
12. Send targeted SMS reminders/announcements as needed.
13. Review reports and geo-map for coverage and gaps.

## O) Scope and Evidence Notes

1. FFPRAMS scope is focused on LGU-level service delivery operations (beneficiary handling, event scheduling, allocation, release confirmation, SMS, and reporting).
2. Procurement and national-level registry administration are intentionally out of scope in this process guide.
3. Event-based distribution is the primary flow; direct assistance is a documented exception flow.
4. Source transparency:
   - Core agency practice alignment is based on official DA/BFAR/DAR public process descriptions and your project Chapter 1 factual-basis references.
   - If strict external citation is required for evaluation, use `docs/PROCESS_REFERENCES.md` and keep attached copies of circulars/memoranda used by your institution.
