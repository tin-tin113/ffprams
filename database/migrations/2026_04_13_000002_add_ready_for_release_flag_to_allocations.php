<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('allocations', 'is_ready_for_release')) {
            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->boolean('is_ready_for_release')
                ->default(false)
                ->after('amount');

            $table->index('is_ready_for_release');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('allocations', 'is_ready_for_release')) {
            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropIndex(['is_ready_for_release']);
            $table->dropColumn('is_ready_for_release');
        });
    }
};
