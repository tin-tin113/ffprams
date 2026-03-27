<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            $table->foreignId('program_name_id')
                ->nullable()
                ->after('resource_type_id')
                ->constrained('program_names')
                ->nullOnDelete();

            $table->index('program_name_id');
        });
    }

    public function down(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            $table->dropForeign(['program_name_id']);
            $table->dropIndex(['program_name_id']);
            $table->dropColumn('program_name_id');
        });
    }
};
