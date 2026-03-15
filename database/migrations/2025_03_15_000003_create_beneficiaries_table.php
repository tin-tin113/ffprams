<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->enum('classification', ['Farmer', 'Fisherfolk', 'Both']);
            $table->string('contact_number');
            $table->unsignedInteger('household_size');
            $table->string('id_type');
            $table->string('government_id')->unique();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->date('registered_at');

            // --- Farmer-Specific (DA RSBSA) ---
            $table->string('rsbsa_number')->nullable()->unique();
            $table->enum('farm_ownership', ['Owner', 'Lessee', 'Share Tenant'])->nullable();
            $table->decimal('farm_size_hectares', 8, 2)->nullable();
            $table->string('primary_commodity')->nullable();
            $table->enum('farm_type', ['Irrigated', 'Rainfed Lowland', 'Upland'])->nullable();

            // --- Fisherfolk-Specific (BFAR FishR) ---
            $table->string('fishr_number')->nullable()->unique();
            $table->enum('fisherfolk_type', ['Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker'])->nullable();
            $table->string('main_fishing_gear')->nullable();
            $table->boolean('has_fishing_vessel')->nullable()->default(false);

            // --- Common New Fields ---
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Separated'])->nullable();
            $table->string('highest_education')->nullable();
            $table->unsignedInteger('number_of_dependents')->nullable();
            $table->string('main_income_source')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->boolean('association_member')->default(false);
            $table->string('association_name')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
