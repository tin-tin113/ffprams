<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize classifications that imply both Farmer and Fisherfolk
        DB::table('beneficiaries')
            ->where(function ($query) {
                $query->where('classification', 'like', '%Farmer%')
                    ->where('classification', 'like', '%Fisherfolk%');
            })
            ->orWhere('classification', 'Both')
            ->orWhere('classification', 'Farmer/Fisherfolk')
            ->update(['classification' => 'Farmer & Fisherfolk']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No simple way to reverse without knowing original values, but usually not needed for normalization
    }
};
