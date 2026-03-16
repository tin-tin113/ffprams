<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            $table->enum('type', ['physical', 'financial'])->default('physical')->after('created_by');
            $table->decimal('total_fund_amount', 12, 2)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            $table->dropColumn(['type', 'total_fund_amount']);
        });
    }
};
