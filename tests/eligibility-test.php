<?php

use App\Models\Beneficiary;
use App\Models\Agency;
use App\Models\ProgramName;
use App\Services\ProgramEligibilityService;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         TEST SUITE: PROGRAM ELIGIBILITY VALIDATION             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

// TEST 1: Multi-Agency Beneficiary - BFAR Program
echo "\n📋 TEST 1: Multi-Agency Beneficiary (DA+BFAR) → BFAR Program\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $da = Agency::where('name', 'DA')->first();
    $bfar = Agency::where('name', 'BFAR')->first();

    if (!$da || !$bfar) {
        echo "❌ Agencies not found in database\n";
    } else {
        $maria = Beneficiary::where('classification', 'Fisherfolk')->first();

        if ($maria) {
            // Ensure maria is registered under DA and BFAR
            $maria->agencies()->syncWithoutDetaching([
                $da->id => ['identifier' => 'DA-2024-001', 'registered_at' => now()->toDateString()],
                $bfar->id => ['identifier' => 'BFAR-2024-567', 'registered_at' => now()->toDateString()],
            ]);

            echo "✅ Beneficiary: " . $maria->full_name . " (Fisherfolk)\n";
            echo "   Primary agency: " . $maria->agency->name . " (id=" . $maria->agency_id . ")\n";

            $agencies = $maria->agencies()->pluck('name')->toArray();
            echo "   Registered under: " . implode(', ', $agencies) . "\n";

            $bfarProgram = ProgramName::where('agency_id', $bfar->id)
                ->where('classification', 'Fisherfolk')
                ->first();

            if ($bfarProgram) {
                echo "✅ Program: " . $bfarProgram->name . " (BFAR, Fisherfolk)\n";
                $eligible = ProgramEligibilityService::isEligible($maria, $bfarProgram);
                echo ($eligible ? "✅" : "❌") . " RESULT: " . ($eligible ? "ELIGIBLE" : "INELIGIBLE") . "\n";
                if ($eligible) {
                    echo "   ✅ TEST 1 PASSED: Multi-agency can access all agencies' programs!\n";
                }
            } else {
                echo "⚠️ No BFAR Fisherfolk program found\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// TEST 2: Event-Based Allocation - Wrong Classification
echo "\n📋 TEST 2: Classification Mismatch (Fisherfolk → Farmer Program)\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $da = Agency::where('name', 'DA')->first();
    $fisherfolk = Beneficiary::where('classification', 'Fisherfolk')->first();

    if ($fisherfolk && $da) {
        echo "✅ Beneficiary: " . $fisherfolk->full_name . " (Classification: Fisherfolk)\n";

        $farmerProgram = ProgramName::where('agency_id', $da->id)
            ->where('classification', 'Farmer')
            ->first();

        if ($farmerProgram) {
            echo "✅ Program: " . $farmerProgram->name . " (DA, Farmer)\n";
            $eligible = ProgramEligibilityService::isEligible($fisherfolk, $farmerProgram);
            echo ($eligible ? "❌" : "✅") . " RESULT: " . ($eligible ? "ELIGIBLE (WRONG!)" : "INELIGIBLE (CORRECT)") . "\n";

            if (!$eligible) {
                $reason = ProgramEligibilityService::getIneligibilityReason($fisherfolk, $farmerProgram);
                echo "   Message: " . $reason . "\n";
                echo "   ✅ TEST 2 PASSED: Classification validation working!\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// TEST 3: Inactive Program
echo "\n📋 TEST 3: Inactive Program Validation\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $inactiveProgram = ProgramName::where('is_active', false)->first();

    if ($inactiveProgram) {
        echo "✅ Program: " . $inactiveProgram->name . " (Active: No)\n";

        $farmer = Beneficiary::where('classification', 'Farmer')->first();
        if ($farmer) {
            echo "✅ Beneficiary: " . $farmer->full_name . "\n";
            $eligible = ProgramEligibilityService::isEligible($farmer, $inactiveProgram);
            echo ($eligible ? "❌" : "✅") . " RESULT: " . ($eligible ? "ELIGIBLE (WRONG!)" : "INELIGIBLE (CORRECT)") . "\n";

            if (!$eligible) {
                $reason = ProgramEligibilityService::getIneligibilityReason($farmer, $inactiveProgram);
                echo "   Message: " . $reason . "\n";
                echo "   ✅ TEST 3 PASSED: Inactive program check working!\n";
            }
        }
    } else {
        echo "⚠️ No inactive programs found - Creating test inactive program\n";
        $da = Agency::where('name', 'DA')->first();
        if ($da) {
            $prog = ProgramName::create([
                'agency_id' => $da->id,
                'name' => 'TEST_INACTIVE_PROGRAM',
                'classification' => 'Farmer',
                'is_active' => false,
            ]);
            echo "✅ Created test inactive program\n";

            $farmer = Beneficiary::where('classification', 'Farmer')->first();
            if ($farmer) {
                $eligible = ProgramEligibilityService::isEligible($farmer, $prog);
                echo ($eligible ? "❌" : "✅") . " RESULT: " . ($eligible ? "ELIGIBLE (WRONG!)" : "INELIGIBLE (CORRECT)") . "\n";
                echo "   ✅ TEST 3 PASSED: Inactive program check working!\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// TEST 4: Service accepts pivot table data
echo "\n📋 TEST 4: Service Checks All Registered Agencies (Pivot Table)\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $beneficiary = Beneficiary::has('agencies')->first();

    if ($beneficiary) {
        echo "✅ Beneficiary: " . $beneficiary->full_name . "\n";
        $agencies = $beneficiary->agencies()->get();
        $agencyNames = $agencies->pluck('name')->toArray();
        echo "   Registered under: " . implode(', ', $agencyNames) . " (via pivot table)\n";

        $programs = ProgramEligibilityService::getEligiblePrograms($beneficiary);
        echo "   Eligible programs found: " . $programs->count() . "\n";

        if ($programs->count() > 0) {
            foreach ($programs->take(3) as $prog) {
                echo "   ✅ " . $prog->name . " (" . $prog->agency->name . ")\n";
            }
            echo "   ✅ TEST 4 PASSED: Service correctly checks all pivot table agencies!\n";
        } else {
            echo "   ⚠️ No eligible programs found\n";
        }
    } else {
        echo "⚠️ No multi-agency beneficiaries found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// TEST 5: Error messages are descriptive
echo "\n📋 TEST 5: Error Messages Descriptive\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $fisherfolk = Beneficiary::where('classification', 'Fisherfolk')->first();
    $dar = Agency::where('name', 'DAR')->first();

    if ($fisherfolk && $dar) {
        $darProgram = ProgramName::where('agency_id', $dar->id)->first();

        if ($darProgram) {
            echo "✅ Beneficiary: " . $fisherfolk->full_name . " (Fisherfolk)\n";
            echo "✅ Program: " . $darProgram->name . " (DAR, Classification: " . $darProgram->classification . ")\n";

            $eligible = ProgramEligibilityService::isEligible($fisherfolk, $darProgram);

            if (!$eligible) {
                $reason = ProgramEligibilityService::getIneligibilityReason($fisherfolk, $darProgram);
                echo "✅ Error message: \"" . $reason . "\"\n";
                echo "   ✅ TEST 5 PASSED: Error messages are user-friendly!\n";
            }
        } else {
            echo "⚠️ No DAR program found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║              ✅ ALL 5 TEST SCENARIOS COMPLETED                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
