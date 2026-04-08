<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\ProgramName;
use Illuminate\Database\Eloquent\Collection;

class ProgramEligibilityService
{
    /**
     * Get programs eligible for a beneficiary based on agency and classification.
     *
     * @param Beneficiary $beneficiary
     * @return Collection
     */
    public static function getEligiblePrograms(Beneficiary $beneficiary): Collection
    {
        return ProgramName::where('agency_id', $beneficiary->agency_id)
            ->where('is_active', true)
            ->whereIn('classification', [$beneficiary->classification, 'Both'])
            ->with('agency')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a program is eligible for a beneficiary.
     *
     * @param Beneficiary $beneficiary
     * @param ProgramName $program
     * @return bool
     */
    public static function isEligible(Beneficiary $beneficiary, ProgramName $program): bool
    {
        if ($program->agency_id !== $beneficiary->agency_id) {
            return false;
        }

        if (!$program->is_active) {
            return false;
        }

        return in_array($beneficiary->classification, [$program->classification, 'Both']);
    }

    /**
     * Get eligibility reason message.
     *
     * @param Beneficiary $beneficiary
     * @param ProgramName $program
     * @return string
     */
    public static function getIneligibilityReason(Beneficiary $beneficiary, ProgramName $program): string
    {
        if ($program->agency_id !== $beneficiary->agency_id) {
            return "Program is for {$program->agency->name} agency only. Beneficiary is registered with {$beneficiary->agency->name}.";
        }

        if (!$program->is_active) {
            return "This program is currently inactive.";
        }

        return "Beneficiary classification '{$beneficiary->classification}' does not match program requirement '{$program->classification}'.";
    }
}
