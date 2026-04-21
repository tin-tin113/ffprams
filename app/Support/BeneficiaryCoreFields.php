<?php

namespace App\Support;

final class BeneficiaryCoreFields
{
    /**
     * Personal-information core fields that must remain schema-controlled.
     * These should not be managed through dynamic Settings CRUD.
     */
    private const PERSONAL_INFORMATION_CORE_FIELD_NAMES = [
        'first_name',
        'middle_name',
        'last_name',
        'name_suffix',
        'full_name',
        'sex',
        'date_of_birth',
        'photo_path',
        'home_address',
        'barangay_id',
        'contact_number',
        'civil_status',
        'highest_education',
        'id_type',
        'status',
        'registered_at',
        'association_member',
        'association_name',
    ];

    /**
     * Core beneficiary fields that are managed by the static schema/flows.
     * These must never be duplicated in agency-specific dynamic field definitions.
     */
    private const AGENCY_SPECIFIC_CORE_FIELD_NAMES = [
        'rsbsa_number',
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',
        'organization_membership',
        'fishr_number',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',
        'fishing_vessel_type',
        'fishing_vessel_tonnage',
        'length_of_residency_months',
        'cloa_ep_number',
        'arb_classification',
        'landholding_description',
        'land_area_awarded_hectares',
        'ownership_scheme',
        'barc_membership_status',
    ];

    /**
     * Locked core fields that cannot be edited via Agencies > Manage Fields.
     * Note: Some agency-specific fields are intentionally unlocked and managed per agency.
     */
    private const RESERVED_AGENCY_FORM_FIELD_NAMES = [
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',
        'organization_membership',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',
        'fishing_vessel_type',
        'fishing_vessel_tonnage',
        'length_of_residency_months',
    ];

    /**
     * Classification-core fields rendered in static beneficiary sections.
     * These should not be duplicated in dynamic agency fields.
     */
    private const CLASSIFICATION_CORE_FIELD_NAMES = [
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',
        'organization_membership',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',
        'fishing_vessel_type',
        'fishing_vessel_tonnage',
        'length_of_residency_months',
    ];

    /**
     * @var array<string, string>
     */
    private const REASON_COLUMN_BY_FIELD_NAME = [
        'rsbsa_number' => 'rsbsa_unavailability_reason',
        'farm_ownership' => 'rsbsa_unavailability_reason',
        'farm_size_hectares' => 'rsbsa_unavailability_reason',
        'primary_commodity' => 'rsbsa_unavailability_reason',
        'farm_type' => 'rsbsa_unavailability_reason',
        'organization_membership' => 'rsbsa_unavailability_reason',

        'fishr_number' => 'fishr_unavailability_reason',
        'fisherfolk_type' => 'fishr_unavailability_reason',
        'main_fishing_gear' => 'fishr_unavailability_reason',
        'has_fishing_vessel' => 'fishr_unavailability_reason',
        'fishing_vessel_type' => 'fishr_unavailability_reason',
        'fishing_vessel_tonnage' => 'fishr_unavailability_reason',
        'length_of_residency_months' => 'fishr_unavailability_reason',

        'cloa_ep_number' => 'cloa_ep_unavailability_reason',
        'arb_classification' => 'cloa_ep_unavailability_reason',
        'landholding_description' => 'cloa_ep_unavailability_reason',
        'land_area_awarded_hectares' => 'cloa_ep_unavailability_reason',
        'ownership_scheme' => 'cloa_ep_unavailability_reason',
        'barc_membership_status' => 'cloa_ep_unavailability_reason',
    ];

    /**
     * @var array<string, string>
     */
    private const RESERVED_FIELD_SECTION_BY_FIELD_NAME = [
        'rsbsa_number' => 'farmer_information',
        'farm_ownership' => 'farmer_information',
        'farm_size_hectares' => 'farmer_information',
        'primary_commodity' => 'farmer_information',
        'farm_type' => 'farmer_information',
        'organization_membership' => 'farmer_information',

        'fishr_number' => 'fisherfolk_information',
        'fisherfolk_type' => 'fisherfolk_information',
        'main_fishing_gear' => 'fisherfolk_information',
        'has_fishing_vessel' => 'fisherfolk_information',
        'fishing_vessel_type' => 'fisherfolk_information',
        'fishing_vessel_tonnage' => 'fisherfolk_information',
        'length_of_residency_months' => 'fisherfolk_information',

        'cloa_ep_number' => 'dar_information',
        'arb_classification' => 'dar_information',
        'landholding_description' => 'dar_information',
        'land_area_awarded_hectares' => 'dar_information',
        'ownership_scheme' => 'dar_information',
        'barc_membership_status' => 'dar_information',
    ];

    /**
     * @return array<int, string>
     */
    public static function reservedAgencyFormFieldNames(): array
    {
        return self::RESERVED_AGENCY_FORM_FIELD_NAMES;
    }

    /**
     * Core agency/classification fields intended to be managed per agency.
     *
     * @return array<int, string>
     */
    public static function agencySpecificCoreFieldNames(): array
    {
        return self::AGENCY_SPECIFIC_CORE_FIELD_NAMES;
    }

    /**
     * @return array<int, string>
     */
    public static function personalInformationCoreFieldNames(): array
    {
        return self::PERSONAL_INFORMATION_CORE_FIELD_NAMES;
    }

    /**
     * @return array<int, string>
     */
    public static function classificationCoreFieldNames(): array
    {
        return self::CLASSIFICATION_CORE_FIELD_NAMES;
    }

    public static function isReservedAgencyFormFieldName(string $fieldName): bool
    {
        return in_array(strtolower(trim($fieldName)), self::RESERVED_AGENCY_FORM_FIELD_NAMES, true);
    }

    public static function isAgencySpecificCoreFieldName(string $fieldName): bool
    {
        return in_array(strtolower(trim($fieldName)), self::AGENCY_SPECIFIC_CORE_FIELD_NAMES, true);
    }

    public static function isPersonalInformationCoreFieldName(string $fieldName): bool
    {
        return in_array(strtolower(trim($fieldName)), self::PERSONAL_INFORMATION_CORE_FIELD_NAMES, true);
    }

    public static function isClassificationCoreFieldName(string $fieldName): bool
    {
        return in_array(strtolower(trim($fieldName)), self::CLASSIFICATION_CORE_FIELD_NAMES, true);
    }

    public static function reservedAgencyFormFieldSection(string $fieldName): ?string
    {
        $normalized = strtolower(trim($fieldName));

        return self::RESERVED_FIELD_SECTION_BY_FIELD_NAME[$normalized] ?? null;
    }

    public static function unavailabilityReasonColumnFor(string $fieldName): ?string
    {
        $normalized = strtolower(trim($fieldName));

        return self::REASON_COLUMN_BY_FIELD_NAME[$normalized] ?? null;
    }
}
