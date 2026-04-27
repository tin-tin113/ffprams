<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\ProgramName;
use Illuminate\Database\Eloquent\Collection;

class ProgramEligibilityService
{
    /**
     * Convert a beneficiary classification to the set of program classification
     * values that the beneficiary is eligible for.
     *
     * Beneficiaries store: 'Farmer' | 'Fisherfolk' | 'Farmer & Fisherfolk'
     * Programs store:      'Farmer' | 'Fisherfolk' | 'Both'
     */
    public static function eligibleProgramClassifications(string $beneficiaryClassification): array
    {
        $eligible = ['Both'];

        if ($beneficiaryClassification === 'Farmer' || $beneficiaryClassification === 'Farmer & Fisherfolk') {
            $eligible[] = 'Farmer';
        }

        if ($beneficiaryClassification === 'Fisherfolk' || $beneficiaryClassification === 'Farmer & Fisherfolk') {
            $eligible[] = 'Fisherfolk';
        }

        return $eligible;
    }

    /**
     * Get programs eligible for a beneficiary based on classification.
     */
    public static function getEligiblePrograms(Beneficiary $beneficiary): Collection
    {
        $programClassifications = self::eligibleProgramClassifications(
            (string) $beneficiary->classification
        );

        return ProgramName::where('is_active', true)
            ->whereIn('classification', $programClassifications)
            ->with('agency')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a beneficiary is eligible for a given program.
     */
    public static function isEligible(Beneficiary $beneficiary, ProgramName $program): bool
    {
        if (! $program->is_active) {
            return false;
        }

        return in_array(
            $program->classification,
            self::eligibleProgramClassifications((string) $beneficiary->classification),
            true
        );
    }

    /**
     * Get a human-readable ineligibility reason.
     */
    public static function getIneligibilityReason(Beneficiary $beneficiary, ProgramName $program): string
    {
        if (! $program->is_active) {
            return 'This program is currently inactive.';
        }

        $eligible = self::eligibleProgramClassifications((string) $beneficiary->classification);

        if (! in_array($program->classification, $eligible, true)) {
            return "Beneficiary classification '{$beneficiary->classification}' does not match program requirement '{$program->classification}'.";
        }

        return 'Beneficiary is ineligible due to unknown criteria.';
    }
}
