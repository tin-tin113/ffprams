<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable()->after('source_agency')->constrained('agencies')->nullOnDelete();

            $table->index('agency_id');
        });
    }

    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropIndex(['agency_id']);
            $table->dropColumn('agency_id');
        });
    }
};
