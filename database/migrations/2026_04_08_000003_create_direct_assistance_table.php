<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create direct_assistance table (D9) as separate data store.
     * This table tracks direct allocation/assistance to specific beneficiaries
     * outside of scheduled distribution events.
     */
    public function up(): void
    {
        Schema::create('direct_assistance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')
                ->constrained('beneficiaries')
                ->onDelete('restrict');
            $table->foreignId('program_name_id')
                ->constrained('program_names')
                ->onDelete('restrict');
            $table->foreignId('resource_type_id')
                ->constrained('resource_types')
                ->onDelete('restrict');
            $table->foreignId('assistance_purpose_id')
                ->nullable()
                ->constrained('assistance_purposes')
                ->onDelete('set null');

            // Quantity for non-financial resources
            $table->decimal('quantity', 10, 2)->nullable();
            // Amount for financial resources (in PHP)
            $table->decimal('amount', 12, 2)->nullable();

            // Delivery/Distribution fields
            $table->timestamp('distributed_at')->nullable();
            $table->enum('release_outcome', [
                'accepted',
                'partially_received',
                'refused',
                'not_found',
                'deferred',
            ])->nullable();

            // Administrative fields
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('distributed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('beneficiary_id');
            $table->index('program_name_id');
            $table->index('resource_type_id');
            $table->index('created_by');
            $table->index('distributed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_assistance');
    }
};
