<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->string('field_name', 100);       // e.g. id_type, highest_education
            $table->string('label', 255);             // Display text
            $table->string('value', 255);             // Stored value
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('field_name');
            $table->unique(['field_name', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_options');
    }
};
