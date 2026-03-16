<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('field_assessments');
    }
};
