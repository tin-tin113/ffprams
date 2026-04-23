<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rescue orphaned DAR data into the dynamic JSON columns
        DB::table('beneficiaries')
            ->where(function ($query) {
                $query->whereNotNull('cloa_ep_number')
                    ->orWhereNotNull('arb_classification')
                    ->orWhereNotNull('landholding_description')
                    ->orWhereNotNull('land_area_awarded_hectares')
                    ->orWhereNotNull('ownership_scheme')
                    ->orWhereNotNull('barc_membership_status')
                    ->orWhereNotNull('cloa_ep_unavailability_reason');
            })
            ->orderBy('id')
            ->chunk(100, function ($beneficiaries) {
                foreach ($beneficiaries as $beneficiary) {
                    $customFields = json_decode($beneficiary->custom_fields ?? '{}', true) ?: [];
                    $reasons = json_decode($beneficiary->custom_field_unavailability_reasons ?? '{}', true) ?: [];

                    $darData = [];
                    if ($beneficiary->cloa_ep_number) $darData['cloa_ep_number'] = $beneficiary->cloa_ep_number;
                    if ($beneficiary->arb_classification) $darData['arb_classification'] = $beneficiary->arb_classification;
                    if ($beneficiary->landholding_description) $darData['landholding_description'] = $beneficiary->landholding_description;
                    if ($beneficiary->land_area_awarded_hectares) $darData['land_area_awarded_hectares'] = $beneficiary->land_area_awarded_hectares;
                    if ($beneficiary->ownership_scheme) $darData['ownership_scheme'] = $beneficiary->ownership_scheme;
                    if ($beneficiary->barc_membership_status) $darData['barc_membership_status'] = $beneficiary->barc_membership_status;

                    if (!empty($darData)) {
                        if (!isset($customFields['agency_dynamic'])) $customFields['agency_dynamic'] = [];
                        if (!isset($customFields['agency_dynamic']['3'])) $customFields['agency_dynamic']['3'] = [];
                        
                        $customFields['agency_dynamic']['3'] = array_merge(
                            $customFields['agency_dynamic']['3'], 
                            $darData
                        );
                    }

                    if ($beneficiary->cloa_ep_unavailability_reason) {
                        if (!isset($reasons['agency_dynamic'])) $reasons['agency_dynamic'] = [];
                        if (!isset($reasons['agency_dynamic']['3'])) $reasons['agency_dynamic']['3'] = [];
                        
                        $reasons['agency_dynamic']['3']['cloa_ep_number'] = $beneficiary->cloa_ep_unavailability_reason;
                    }

                    DB::table('beneficiaries')
                        ->where('id', $beneficiary->id)
                        ->update([
                            'custom_fields' => json_encode($customFields),
                            'custom_field_unavailability_reasons' => json_encode($reasons),
                        ]);
                }
            });

        // Drop the unique index and legacy columns
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropUnique('beneficiaries_cloa_ep_number_unique');
            $table->dropColumn([
                'cloa_ep_number',
                'arb_classification',
                'landholding_description',
                'land_area_awarded_hectares',
                'ownership_scheme',
                'barc_membership_status',
                'cloa_ep_unavailability_reason',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->string('cloa_ep_number')->nullable()->unique('beneficiaries_cloa_ep_number_unique');
            $table->string('arb_classification')->nullable();
            $table->text('landholding_description')->nullable();
            $table->decimal('land_area_awarded_hectares', 10, 2)->nullable();
            $table->string('ownership_scheme')->nullable();
            $table->string('barc_membership_status')->nullable();
            $table->text('cloa_ep_unavailability_reason')->nullable();
        });
    }
};
