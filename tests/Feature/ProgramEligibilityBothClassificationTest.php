<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\ProgramName;
use App\Services\ProgramEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramEligibilityBothClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_is_eligible_for_both_classification_program(): void
    {
        [$beneficiary, $program] = $this->makeFixtures('Farmer', 'Both');

        $this->assertTrue(
            ProgramEligibilityService::isEligible($beneficiary, $program),
            'Farmer beneficiaries should be eligible when program classification is Both.'
        );
    }

    public function test_fisherfolk_is_eligible_for_both_classification_program(): void
    {
        [$beneficiary, $program] = $this->makeFixtures('Fisherfolk', 'Both');

        $this->assertTrue(
            ProgramEligibilityService::isEligible($beneficiary, $program),
            'Fisherfolk beneficiaries should be eligible when program classification is Both.'
        );
    }

    /**
     * @return array{Beneficiary, ProgramName}
     */
    private function makeFixtures(string $beneficiaryClassification, string $programClassification): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Eligibility Barangay '.uniqid(),
            'latitude' => 10.20000000,
            'longitude' => 123.20000000,
        ]);

        $beneficiary = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'Eligibility',
            'last_name' => 'Tester',
            'barangay_id' => $barangay->id,
            'classification' => $beneficiaryClassification,
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $program = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'Program '.uniqid(),
            'description' => 'Eligibility test program',
            'is_active' => true,
            'classification' => $programClassification,
        ]);

        return [$beneficiary, $program];
    }
}
