<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            if (! Schema::hasColumn('distribution_events', 'compliance_field_states')) {
                $table->json('compliance_field_states')
                    ->nullable()
                    ->after('farmc_reference_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_events', 'compliance_field_states')) {
                $table->dropColumn('compliance_field_states');
            }
        });
    }
};
