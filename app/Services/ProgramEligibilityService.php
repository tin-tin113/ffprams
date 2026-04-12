<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\ProgramName;
use Illuminate\Database\Eloquent\Collection;

class ProgramEligibilityService
{
    /**
     * Get programs eligible for a beneficiary based on agency and classification.
     * Checks ALL registered agencies via pivot table (supports multi-agency beneficiaries).
     */
    public static function getEligiblePrograms(Beneficiary $beneficiary): Collection
    {
        // Get all agencies the beneficiary is registered under
        $registeredAgencyIds = $beneficiary->agencies()->pluck('agencies.id')->toArray();

        // If no agencies in pivot table, fall back to primary agency
        if (empty($registeredAgencyIds)) {
            $registeredAgencyIds = [$beneficiary->agency_id];
        }

        return ProgramName::whereIn('agency_id', $registeredAgencyIds)
            ->where('is_active', true)
            ->whereIn('classification', [$beneficiary->classification, 'Both'])
            ->with('agency')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a program is eligible for a beneficiary.
     * Checks ALL registered agencies via pivot table (supports multi-agency beneficiaries).
     */
    public static function isEligible(Beneficiary $beneficiary, ProgramName $program): bool
    {
        // Get all agencies the beneficiary is registered under
        $registeredAgencyIds = $beneficiary->agencies()->pluck('agencies.id')->toArray();

        // If no agencies in pivot table, fall back to primary agency
        if (empty($registeredAgencyIds)) {
            $registeredAgencyIds = [$beneficiary->agency_id];
        }

        // Check if beneficiary is registered under the program's agency
        if (! in_array($program->agency_id, $registeredAgencyIds)) {
            return false;
        }

        if (! $program->is_active) {
            return false;
        }

        return in_array($beneficiary->classification, [$program->classification, 'Both']);
    }

    /**
     * Get eligibility reason message.
     */
    public static function getIneligibilityReason(Beneficiary $beneficiary, ProgramName $program): string
    {
        // Get all agencies the beneficiary is registered under
        $registeredAgencyIds = $beneficiary->agencies()->pluck('agencies.id')->toArray();

        // If no agencies in pivot table, fall back to primary agency
        if (empty($registeredAgencyIds)) {
            $registeredAgencyIds = [$beneficiary->agency_id];
        }

        // Check if beneficiary is registered under the program's agency
        if (! in_array($program->agency_id, $registeredAgencyIds)) {
            $agenciesStr = implode(', ', $beneficiary->agencies()->pluck('agencies.name')->toArray());
            if (empty($agenciesStr)) {
                $agenciesStr = $beneficiary->agency->name ?? 'Unknown';
            }
            return "Program is for {$program->agency->name} agency only. Beneficiary is registered with {$agenciesStr}.";
        }

        if (! $program->is_active) {
            return 'This program is currently inactive.';
        }

        return "Beneficiary classification '{$beneficiary->classification}' does not match program requirement '{$program->classification}'.";
    }
}
