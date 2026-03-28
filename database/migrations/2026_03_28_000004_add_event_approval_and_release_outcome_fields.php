<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            if (! Schema::hasColumn('distribution_events', 'beneficiary_list_approved_at')) {
                $table->timestamp('beneficiary_list_approved_at')
                    ->nullable()
                    ->after('status');
            }

            if (! Schema::hasColumn('distribution_events', 'beneficiary_list_approved_by')) {
                $table->foreignId('beneficiary_list_approved_by')
                    ->nullable()
                    ->after('beneficiary_list_approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
                $table->index('beneficiary_list_approved_by');
            }
        });

        Schema::table('allocations', function (Blueprint $table) {
            if (! Schema::hasColumn('allocations', 'release_outcome')) {
                $table->enum('release_outcome', ['received', 'not_received'])
                    ->nullable()
                    ->after('distributed_at');
                $table->index('release_outcome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            if (Schema::hasColumn('allocations', 'release_outcome')) {
                $table->dropIndex(['release_outcome']);
                $table->dropColumn('release_outcome');
            }
        });

        Schema::table('distribution_events', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_events', 'beneficiary_list_approved_by')) {
                $table->dropForeign(['beneficiary_list_approved_by']);
                $table->dropIndex(['beneficiary_list_approved_by']);
                $table->dropColumn('beneficiary_list_approved_by');
            }

            if (Schema::hasColumn('distribution_events', 'beneficiary_list_approved_at')) {
                $table->dropColumn('beneficiary_list_approved_at');
            }
        });
    }
};
