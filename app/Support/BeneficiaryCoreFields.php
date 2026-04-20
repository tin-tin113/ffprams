<?php

namespace App\Support;

final class BeneficiaryCoreFields
{
    /**
     * Core beneficiary fields that are managed by the static schema/flows.
     * These must never be duplicated in agency-specific dynamic field definitions.
     */
    private const RESERVED_AGENCY_FORM_FIELD_NAMES = [
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
     * @return array<int, string>
     */
    public static function reservedAgencyFormFieldNames(): array
    {
        return self::RESERVED_AGENCY_FORM_FIELD_NAMES;
    }

    public static function isReservedAgencyFormFieldName(string $fieldName): bool
    {
        return in_array(strtolower(trim($fieldName)), self::RESERVED_AGENCY_FORM_FIELD_NAMES, true);
    }

    public static function unavailabilityReasonColumnFor(string $fieldName): ?string
    {
        $normalized = strtolower(trim($fieldName));

        return self::REASON_COLUMN_BY_FIELD_NAME[$normalized] ?? null;
    }
}
