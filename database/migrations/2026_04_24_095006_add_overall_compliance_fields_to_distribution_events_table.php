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
        Schema::table('distribution_events', function (Blueprint $table) {
            if (! Schema::hasColumn('distribution_events', 'compliance_overall_status')) {
                $table->string('compliance_overall_status', 50)
                    ->nullable()
                    ->default('not_available_yet')
                    ->after('compliance_field_states');
            }

            if (! Schema::hasColumn('distribution_events', 'compliance_overall_reason')) {
                $table->text('compliance_overall_reason')
                    ->nullable()
                    ->after('compliance_overall_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            $table->dropColumn(['compliance_overall_status', 'compliance_overall_reason']);
        });
    }
};
