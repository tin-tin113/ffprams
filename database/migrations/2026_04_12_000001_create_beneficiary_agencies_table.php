<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create pivot table to track beneficiary-agency relationships.
     * This enables:
     * - Beneficiaries to be registered under multiple agencies (DA + DAR, DA + BFAR, etc.)
     * - Storage of agency-specific identifiers (RSBSA, FishR, CLOA/EP)
     * - Tracking when beneficiary was registered under each agency
     */
    public function up(): void
    {
        Schema::create('beneficiary_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->onDelete('cascade');
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->string('identifier')->nullable(); // RSBSA, FishR, or CLOA/EP number
            $table->date('registered_at')->nullable();
            $table->timestamps();

            // Prevent duplicate beneficiary-agency combinations
            $table->unique(['beneficiary_id', 'agency_id']);

            // Index for frequent queries
            $table->index('beneficiary_id');
            $table->index('agency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_agencies');
    }
};
