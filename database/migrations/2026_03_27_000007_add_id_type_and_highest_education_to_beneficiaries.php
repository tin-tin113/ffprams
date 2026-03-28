<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'id_type')) {
                $table->string('id_type')->nullable()->after('civil_status');
            }

            if (! Schema::hasColumn('beneficiaries', 'highest_education')) {
                $table->string('highest_education')->nullable()->after('id_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'highest_education')) {
                $table->dropColumn('highest_education');
            }

            if (Schema::hasColumn('beneficiaries', 'id_type')) {
                $table->dropColumn('id_type');
            }
        });
    }
};
