<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_event_id')->constrained('distribution_events')->cascadeOnDelete();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->dateTime('distributed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['distribution_event_id', 'beneficiary_id'], 'allocation_event_beneficiary_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
