<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'partner' role for national agencies (E4 external entity).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change enum to include 'partner' role
            $table->enum('role', ['admin', 'staff', 'partner'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to original enum
            $table->enum('role', ['admin', 'staff'])->change();
        });
    }
};
