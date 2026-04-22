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
        return ProgramName::where('is_active', true)
            ->where(function ($query) use ($beneficiary) {
                if ($beneficiary->classification === 'Both') {
                    // Beneficiary is both, so they are eligible for any classification
                    $query->whereIn('classification', ['Farmer', 'Fisherfolk', 'Both']);
                } else {
                    // Beneficiary is specific, program must be their classification or Both
                    $query->whereIn('classification', [$beneficiary->classification, 'Both']);
                }
            })
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
        if (! $program->is_active) {
            return false;
        }

        if ($program->classification === 'Both' || $beneficiary->classification === 'Both') {
            return true;
        }

        return $program->classification === $beneficiary->classification;
    }

    /**
     * Get eligibility reason message.
     */
    public static function getIneligibilityReason(Beneficiary $beneficiary, ProgramName $program): string
    {
        if (! $program->is_active) {
            return 'This program is currently inactive.';
        }

        if ($program->classification !== 'Both' && $beneficiary->classification !== 'Both' && $program->classification !== $beneficiary->classification) {
            return "Beneficiary classification '{$beneficiary->classification}' does not match program requirement '{$program->classification}'.";
        }

        return 'Beneficiary is ineligible due to unknown criteria.';
    }
}
