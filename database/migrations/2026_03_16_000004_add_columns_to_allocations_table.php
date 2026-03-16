<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->foreignId('assistance_purpose_id')->nullable()->after('remarks')->constrained('assistance_purposes')->nullOnDelete();
            $table->foreignId('field_assessment_id')->nullable()->after('assistance_purpose_id')->constrained('field_assessments')->nullOnDelete();
            $table->foreignId('assessed_by')->nullable()->after('field_assessment_id')->constrained('users')->nullOnDelete();

            $table->index('assistance_purpose_id');
            $table->index('field_assessment_id');
            $table->index('assessed_by');
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign(['assistance_purpose_id']);
            $table->dropForeign(['field_assessment_id']);
            $table->dropForeign(['assessed_by']);
            $table->dropIndex(['assistance_purpose_id']);
            $table->dropIndex(['field_assessment_id']);
            $table->dropIndex(['assessed_by']);
            $table->dropColumn(['assistance_purpose_id', 'field_assessment_id', 'assessed_by']);
        });
    }
};
