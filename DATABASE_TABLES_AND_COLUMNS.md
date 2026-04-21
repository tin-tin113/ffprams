# Database Tables and Columns

**Database:** ffprams

**Generated:** 2026-04-21 08:09:25

## agencies

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO |  |  |  |
| full_name | varchar(255) | NO |  |  |  |
| description | text | YES |  |  |  |
| is_active | tinyint(1) | NO |  | 1 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## agency_classifications

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| agency_id | bigint unsigned | NO | MUL |  |  |
| classification_id | bigint unsigned | NO | MUL |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## agency_form_field_options

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| agency_form_field_id | bigint unsigned | NO | MUL |  |  |
| label | varchar(255) | NO |  |  |  |
| value | varchar(255) | NO |  |  |  |
| sort_order | int | NO |  | 0 |  |
| is_active | tinyint(1) | NO | MUL | 1 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## agency_form_fields

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| agency_id | bigint unsigned | NO | MUL |  |  |
| field_name | varchar(255) | NO |  |  |  |
| display_label | varchar(255) | NO |  |  |  |
| field_type | enum('text','number','decimal','date','datetime','dropdown','checkbox') | NO |  |  |  |
| is_required | tinyint(1) | NO |  | 0 |  |
| is_active | tinyint(1) | NO |  | 1 |  |
| sort_order | int | NO |  | 0 |  |
| help_text | text | YES |  |  |  |
| validation_rules | json | YES |  |  |  |
| form_section | varchar(255) | NO | MUL | additional_information |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## allocations

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| distribution_event_id | bigint unsigned | YES | MUL |  |  |
| release_method | enum('event','direct') | NO | MUL | event |  |
| beneficiary_id | bigint unsigned | NO | MUL |  |  |
| quantity | decimal(8,2) | YES |  |  |  |
| amount | decimal(12,2) | YES |  |  |  |
| is_ready_for_release | tinyint(1) | NO | MUL | 0 |  |
| distributed_at | datetime | YES |  |  |  |
| release_outcome | enum('received','not_received') | YES | MUL |  |  |
| remarks | text | YES |  |  |  |
| assistance_purpose_id | bigint unsigned | YES | MUL |  |  |
| program_name_id | bigint unsigned | YES | MUL |  |  |
| resource_type_id | bigint unsigned | YES | MUL |  |  |
| deleted_at | timestamp | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |
| active_beneficiary_id | bigint | YES |  |  | STORED GENERATED |

## assistance_purposes

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO |  |  |  |
| category | varchar(255) | NO |  |  |  |
| type | varchar(255) | YES |  |  |  |
| is_active | tinyint(1) | NO |  | 1 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## audit_logs

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| user_id | bigint unsigned | NO | MUL |  |  |
| action | varchar(255) | NO |  |  |  |
| table_name | varchar(255) | NO | MUL |  |  |
| record_id | bigint unsigned | YES |  |  |  |
| old_values | json | YES |  |  |  |
| new_values | json | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## barangays

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO |  |  |  |
| municipality | varchar(255) | NO | MUL | E.B. Magalona |  |
| province | varchar(255) | NO |  | Negros Occidental |  |
| latitude | decimal(10,8) | NO |  |  |  |
| longitude | decimal(11,8) | NO |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## beneficiaries

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| agency_id | bigint unsigned | YES | MUL |  |  |
| first_name | varchar(255) | YES |  |  |  |
| middle_name | varchar(255) | YES |  |  |  |
| last_name | varchar(255) | YES |  |  |  |
| name_suffix | varchar(255) | YES |  |  |  |
| full_name | varchar(255) | NO |  |  |  |
| sex | enum('Male','Female') | YES |  |  |  |
| date_of_birth | date | YES |  |  |  |
| photo_path | varchar(255) | YES |  |  |  |
| barangay_id | bigint unsigned | NO | MUL |  |  |
| home_address | varchar(255) | YES |  |  |  |
| classification | enum('Farmer','Fisherfolk') | NO | MUL | Farmer |  |
| contact_number | varchar(255) | NO |  |  |  |
| status | enum('Active','Inactive') | NO | MUL | Active |  |
| registered_at | date | NO |  |  |  |
| rsbsa_number | varchar(255) | YES | UNI |  |  |
| farm_ownership | enum('Registered Owner','Tenant','Lessee','Owner','Share Tenant') | YES |  |  |  |
| farm_size_hectares | decimal(8,2) | YES |  |  |  |
| primary_commodity | varchar(255) | YES |  |  |  |
| farm_type | enum('Irrigated','Rainfed Upland','Rainfed Lowland','Upland') | YES |  |  |  |
| fishr_number | varchar(255) | YES | UNI |  |  |
| fisherfolk_type | enum('Capture Fishing','Aquaculture','Post-Harvest','Fish Farming','Fish Vendor','Fish Worker') | YES |  |  |  |
| main_fishing_gear | varchar(255) | YES |  |  |  |
| has_fishing_vessel | tinyint(1) | YES |  | 0 |  |
| fishing_vessel_type | varchar(255) | YES |  |  |  |
| fishing_vessel_tonnage | decimal(8,2) | YES |  |  |  |
| length_of_residency_months | int | YES |  |  |  |
| cloa_ep_number | varchar(255) | YES | UNI |  |  |
| arb_classification | varchar(255) | YES |  |  |  |
| landholding_description | text | YES |  |  |  |
| land_area_awarded_hectares | decimal(10,2) | YES |  |  |  |
| ownership_scheme | varchar(255) | YES |  |  |  |
| barc_membership_status | varchar(255) | YES |  |  |  |
| cloa_ep_unavailability_reason | text | YES |  |  |  |
| custom_field_unavailability_reasons | json | YES |  |  |  |
| custom_fields | json | YES |  |  |  |
| civil_status | enum('Single','Married','Widowed','Separated') | YES |  |  |  |
| id_type | varchar(255) | YES |  |  |  |
| highest_education | varchar(255) | YES |  |  |  |
| association_member | tinyint(1) | NO |  | 0 |  |
| association_name | varchar(255) | YES |  |  |  |
| organization_membership | varchar(255) | YES |  |  |  |
| rsbsa_unavailability_reason | text | YES |  |  |  |
| fishr_unavailability_reason | text | YES |  |  |  |
| deleted_at | timestamp | YES |  |  |  |
| created_at | timestamp | YES | MUL |  |  |
| updated_at | timestamp | YES |  |  |  |

## beneficiary_agencies

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL |  |  |
| agency_id | bigint unsigned | NO | MUL |  |  |
| identifier | varchar(255) | YES |  |  |  |
| registered_at | date | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## beneficiary_attachments

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL |  |  |
| uploaded_by | bigint unsigned | YES | MUL |  |  |
| document_type | varchar(100) | YES |  |  |  |
| original_name | varchar(255) | NO |  |  |  |
| stored_name | varchar(255) | NO |  |  |  |
| path | varchar(500) | NO |  |  |  |
| disk | varchar(50) | NO | MUL | beneficiary_documents |  |
| mime_type | varchar(150) | NO |  |  |  |
| extension | varchar(20) | YES |  |  |  |
| size_bytes | bigint unsigned | NO |  | 0 |  |
| sha256 | char(64) | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## cache

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| key | varchar(255) | NO | PRI |  |  |
| value | mediumtext | NO |  |  |  |
| expiration | int | NO | MUL |  |  |

## cache_locks

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| key | varchar(255) | NO | PRI |  |  |
| owner | varchar(255) | NO |  |  |  |
| expiration | int | NO | MUL |  |  |

## classifications

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO | UNI |  |  |
| description | text | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## direct_assistance

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL |  |  |
| program_name_id | bigint unsigned | NO | MUL |  |  |
| resource_type_id | bigint unsigned | NO | MUL |  |  |
| assistance_purpose_id | bigint unsigned | YES | MUL |  |  |
| quantity | decimal(10,2) | YES |  |  |  |
| amount | decimal(12,2) | YES |  |  |  |
| distributed_at | timestamp | YES | MUL |  |  |
| release_outcome | enum('accepted','partially_received','refused','not_found','deferred') | YES |  |  |  |
| status | enum('planned','ready_for_release','released','not_received') | NO | MUL | planned |  |
| distribution_event_id | bigint unsigned | YES | MUL |  |  |
| remarks | text | YES |  |  |  |
| created_by | bigint unsigned | NO | MUL |  |  |
| distributed_by | bigint unsigned | YES | MUL |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |
| deleted_at | timestamp | YES |  |  |  |

## distribution_events

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| barangay_id | bigint unsigned | NO | MUL |  |  |
| resource_type_id | bigint unsigned | NO | MUL |  |  |
| program_name_id | bigint unsigned | NO | MUL |  |  |
| distribution_date | date | NO | MUL |  |  |
| status | enum('Pending','Ongoing','Completed') | NO | MUL | Pending |  |
| beneficiary_list_approved_at | timestamp | YES |  |  |  |
| beneficiary_list_approved_by | bigint unsigned | YES | MUL |  |  |
| created_by | bigint unsigned | NO | MUL |  |  |
| type | enum('physical','financial') | NO | MUL | physical |  |
| total_fund_amount | decimal(12,2) | YES |  |  |  |
| legal_basis_type | enum('resolution','ordinance','memo','special_order','other') | YES | MUL |  |  |
| legal_basis_reference_no | varchar(150) | YES |  |  |  |
| legal_basis_date | date | YES |  |  |  |
| legal_basis_remarks | text | YES |  |  |  |
| fund_source | enum('lgu_trust_fund','nga_transfer','local_program','other') | YES | MUL |  |  |
| trust_account_code | varchar(100) | YES |  |  |  |
| fund_release_reference | varchar(150) | YES |  |  |  |
| liquidation_status | enum('not_required','pending','submitted','verified') | NO | MUL | not_required |  |
| liquidation_due_date | date | YES | MUL |  |  |
| liquidation_submitted_at | timestamp | YES |  |  |  |
| liquidation_reference_no | varchar(150) | YES |  |  |  |
| requires_farmc_endorsement | tinyint(1) | NO | MUL | 0 |  |
| farmc_endorsed_at | timestamp | YES |  |  |  |
| farmc_reference_no | varchar(150) | YES |  |  |  |
| compliance_field_states | json | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |
| deleted_at | timestamp | YES |  |  |  |

## form_field_options

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| field_group | varchar(100) | NO | MUL |  |  |
| field_type | varchar(30) | NO |  | dropdown |  |
| placement_section | varchar(50) | NO |  | personal_information |  |
| label | varchar(255) | NO |  |  |  |
| value | varchar(255) | NO |  |  |  |
| sort_order | int unsigned | NO |  | 0 |  |
| is_required | tinyint(1) | NO |  | 0 |  |
| is_active | tinyint(1) | NO |  | 1 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## migrations

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int unsigned | NO | PRI |  | auto_increment |
| migration | varchar(255) | NO |  |  |  |
| batch | int | NO |  |  |  |

## password_reset_tokens

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| email | varchar(255) | NO | PRI |  |  |
| token | varchar(255) | NO |  |  |  |
| created_at | timestamp | YES |  |  |  |

## program_legal_requirements

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| program_name_id | bigint unsigned | NO | MUL |  |  |
| uploaded_by | bigint unsigned | YES | MUL |  |  |
| document_type | varchar(100) | YES |  |  |  |
| original_name | varchar(255) | NO |  |  |  |
| stored_name | varchar(255) | NO |  |  |  |
| path | varchar(500) | NO |  |  |  |
| disk | varchar(50) | NO | MUL | program_documents |  |
| mime_type | varchar(150) | NO |  |  |  |
| extension | varchar(20) | YES |  |  |  |
| size_bytes | bigint unsigned | NO |  | 0 |  |
| sha256 | char(64) | YES |  |  |  |
| remarks | text | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## program_names

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| agency_id | bigint unsigned | NO | MUL |  |  |
| name | varchar(255) | NO |  |  |  |
| description | text | YES |  |  |  |
| is_active | tinyint(1) | NO | MUL | 1 |  |
| classification | enum('Farmer','Fisherfolk','Both') | NO |  | Both |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## record_attachments

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| attachable_type | varchar(200) | NO | MUL |  |  |
| attachable_id | bigint unsigned | NO |  |  |  |
| uploaded_by | bigint unsigned | YES | MUL |  |  |
| document_type | varchar(100) | YES |  |  |  |
| original_name | varchar(255) | NO |  |  |  |
| stored_name | varchar(255) | NO |  |  |  |
| path | varchar(500) | NO |  |  |  |
| disk | varchar(50) | NO | MUL | record_documents |  |
| mime_type | varchar(150) | NO |  |  |  |
| extension | varchar(20) | YES |  |  |  |
| size_bytes | bigint unsigned | NO |  | 0 |  |
| sha256 | char(64) | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## resource_types

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO |  |  |  |
| unit | varchar(255) | NO |  |  |  |
| description | text | YES |  |  |  |
| agency_id | bigint unsigned | YES | MUL |  |  |
| is_active | tinyint(1) | NO | MUL | 1 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## sessions

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | varchar(255) | NO | PRI |  |  |
| user_id | bigint unsigned | YES | MUL |  |  |
| ip_address | varchar(45) | YES |  |  |  |
| user_agent | text | YES |  |  |  |
| payload | longtext | NO |  |  |  |
| last_activity | int | NO | MUL |  |  |

## sms_logs

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| beneficiary_id | bigint unsigned | NO | MUL |  |  |
| message | text | NO |  |  |  |
| status | enum('sent','failed') | NO |  |  |  |
| delivery_status | enum('pending','delivered','failed','undeliverable') | NO |  | pending |  |
| response | text | YES |  |  |  |
| gateway_message_id | varchar(255) | YES |  |  |  |
| sent_at | datetime | NO |  |  |  |
| callback_received_at | timestamp | YES |  |  |  |
| retry_count | int | NO |  | 0 |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

## users

| Column | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | bigint unsigned | NO | PRI |  | auto_increment |
| name | varchar(255) | NO |  |  |  |
| email | varchar(255) | NO | UNI |  |  |
| email_verified_at | timestamp | YES |  |  |  |
| password | varchar(255) | NO |  |  |  |
| role | enum('admin','staff','partner') | NO |  |  |  |
| agency_id | bigint unsigned | YES | MUL |  |  |
| remember_token | varchar(100) | YES |  |  |  |
| created_at | timestamp | YES |  |  |  |
| updated_at | timestamp | YES |  |  |  |

