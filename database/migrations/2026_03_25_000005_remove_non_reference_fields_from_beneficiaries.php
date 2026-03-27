<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WARNING: This migration permanently deletes data from the following columns:
 * - household_size
 * - id_type
 * - government_id
 * - highest_education
 * - number_of_dependents
 * - main_income_source
 * - emergency_contact_name
 * - emergency_contact_number
 *
 * These fields are NOT in the FFPRAMS reference document and were removed per user decision.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn([
                'household_size',
                'id_type',
                'government_id',
                'highest_education',
                'number_of_dependents',
                'main_income_source',
                'emergency_contact_name',
                'emergency_contact_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->integer('household_size')->nullable();
            $table->string('id_type')->nullable();
            $table->string('government_id')->nullable();
            $table->string('highest_education')->nullable();
            $table->integer('number_of_dependents')->nullable();
            $table->string('main_income_source')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
        });
    }
};
