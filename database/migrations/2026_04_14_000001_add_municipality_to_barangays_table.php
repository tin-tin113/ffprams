<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            $table->string('municipality')->default('E.B. Magalona')->after('name');
            $table->string('province')->default('Negros Occidental')->after('municipality');
            $table->index(['municipality', 'province']);
        });
    }

    public function down(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex(['municipality', 'province']);
            $table->dropColumn(['municipality', 'province']);
        });
    }
};
