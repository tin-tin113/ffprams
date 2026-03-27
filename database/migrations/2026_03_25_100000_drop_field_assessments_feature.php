<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove field assessment feature - this feature was not part of
     * the documented FFPRAMS system processes.
     */
    public function up(): void
    {
        // Remove field_assessment_id and assessed_by columns from allocations table
        if (Schema::hasTable('allocations')) {
            Schema::table('allocations', function (Blueprint $table) {
                // Drop foreign keys first if they exist
                if (Schema::hasColumn('allocations', 'field_assessment_id')) {
                    $table->dropForeign(['field_assessment_id']);
                    $table->dropColumn('field_assessment_id');
                }

                if (Schema::hasColumn('allocations', 'assessed_by')) {
                    $table->dropForeign(['assessed_by']);
                    $table->dropColumn('assessed_by');
                }
            });
        }

        // Drop the field_assessments table
        Schema::dropIfExists('field_assessments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate field_assessments table
        Schema::create('field_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnDelete();
            $table->foreignId('assessed_by')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->time('visit_time')->nullable();
            $table->text('findings');
            $table->enum('eligibility_status', ['pending', 'eligible', 'not_eligible'])->default('pending');
            $table->text('eligibility_notes')->nullable();
            $table->foreignId('recommended_assistance_purpose_id')->nullable()->constrained('assistance_purposes')->nullOnDelete();
            $table->decimal('recommended_amount', 12, 2)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->index('beneficiary_id');
            $table->index('assessed_by');
            $table->index('recommended_assistance_purpose_id', 'fa_recommended_purpose_index');
            $table->index('approved_by');
        });

        // Add back columns to allocations
        Schema::table('allocations', function (Blueprint $table) {
            $table->foreignId('field_assessment_id')->nullable()->after('assistance_purpose_id')->constrained('field_assessments')->nullOnDelete();
            $table->foreignId('assessed_by')->nullable()->after('field_assessment_id')->constrained('users')->nullOnDelete();
            $table->index('field_assessment_id');
        });
    }
};
