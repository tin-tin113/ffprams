<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Agency source tag (which agency this beneficiary was registered from)
            $table->foreignId('agency_id')->nullable()->after('id')->constrained('agencies')->nullOnDelete();

            // Common fields per reference document
            $table->string('home_address')->nullable()->after('barangay_id');
            $table->enum('sex', ['Male', 'Female'])->nullable()->after('full_name');
            $table->date('date_of_birth')->nullable()->after('sex');
            $table->string('photo_path')->nullable()->after('date_of_birth');

            // DA/RSBSA additional field
            $table->string('organization_membership')->nullable()->after('association_name');

            // BFAR/FishR additional fields
            $table->string('fishing_vessel_type')->nullable()->after('has_fishing_vessel');
            $table->decimal('fishing_vessel_tonnage', 8, 2)->nullable()->after('fishing_vessel_type');
            $table->integer('length_of_residency_months')->nullable()->after('fishing_vessel_tonnage');

            // DAR/ARB fields (ALL NEW)
            $table->string('cloa_ep_number')->nullable()->after('length_of_residency_months');
            $table->string('arb_classification')->nullable()->after('cloa_ep_number');
            $table->text('landholding_description')->nullable()->after('arb_classification');
            $table->decimal('land_area_awarded_hectares', 10, 2)->nullable()->after('landholding_description');
            $table->string('ownership_scheme')->nullable()->after('land_area_awarded_hectares');
            $table->string('barc_membership_status')->nullable()->after('ownership_scheme');

            // Index for agency lookups
            $table->index('agency_id');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropIndex(['agency_id']);
            $table->dropColumn([
                'agency_id',
                'home_address',
                'sex',
                'date_of_birth',
                'photo_path',
                'organization_membership',
                'fishing_vessel_type',
                'fishing_vessel_tonnage',
                'length_of_residency_months',
                'cloa_ep_number',
                'arb_classification',
                'landholding_description',
                'land_area_awarded_hectares',
                'ownership_scheme',
                'barc_membership_status',
            ]);
        });
    }
};
