<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the new dynamic agency system with:
     * - classifications table: Farmer, Fisherfolk
     * - agency_classifications pivot: links agencies to classifications
     * - agency_form_fields: defines custom fields per agency
     * - agency_form_field_options: predefined options for dropdown/checkbox fields
     * - Unavailability reason columns on beneficiaries table
     */
    public function up(): void
    {
        // 1. Create classifications table
        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'Farmer', 'Fisherfolk'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Create agency_classifications pivot table
        Schema::create('agency_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('classification_id')->constrained('classifications')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate agency-classification combinations
            $table->unique(['agency_id', 'classification_id']);

            // Index for queries
            $table->index('agency_id');
            $table->index('classification_id');
        });

        // 3. Create agency_form_fields table
        Schema::create('agency_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');

            // Field identification and labeling
            $table->string('field_name'); // internal slug: 'rsbsa_number', 'fishr_certificate'
            $table->string('display_label'); // 'RSBSA Number', 'FishR Certificate'

            // Field type and behavior
            $table->enum('field_type', ['text', 'number', 'decimal', 'date', 'datetime', 'dropdown', 'checkbox']);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);

            // Metadata
            $table->integer('sort_order')->default(0);
            $table->text('help_text')->nullable();
            $table->json('validation_rules')->nullable(); // min, max, pattern, regex, etc.
            $table->string('form_section')->default('additional_information'); // which section of form

            $table->timestamps();

            // Prevent duplicate field names per agency
            $table->unique(['agency_id', 'field_name']);

            // Index for queries
            $table->index('agency_id');
            $table->index('form_section');
        });

        // 4. Create agency_form_field_options table (for dropdown/checkbox choices)
        Schema::create('agency_form_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_form_field_id')->constrained('agency_form_fields')->onDelete('cascade');

            $table->string('label'); // Display label for the option
            $table->string('value'); // Stored value
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Index for queries
            $table->index('agency_form_field_id');
        });

        // 5. Add unavailability reason columns to beneficiaries table
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Existing agency-specific unavailability reasons
            $table->text('rsbsa_unavailability_reason')->nullable()->after('organization_membership');
            $table->text('fishr_unavailability_reason')->nullable()->after('rsbsa_unavailability_reason');
            $table->text('cloa_ep_unavailability_reason')->nullable()->after('barc_membership_status');

            // Generic JSON for new agencies
            $table->json('custom_field_unavailability_reasons')->nullable()->after('cloa_ep_unavailability_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns from beneficiaries
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn([
                'rsbsa_unavailability_reason',
                'fishr_unavailability_reason',
                'cloa_ep_unavailability_reason',
                'custom_field_unavailability_reasons',
            ]);
        });

        // Drop new tables
        Schema::dropIfExists('agency_form_field_options');
        Schema::dropIfExists('agency_form_fields');
        Schema::dropIfExists('agency_classifications');
        Schema::dropIfExists('classifications');
    }
};
