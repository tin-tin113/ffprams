<?php

use App\Models\FormFieldOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('form_field_options', 'field_type')) {
            Schema::table('form_field_options', function (Blueprint $table) {
                $table->string('field_type', 30)
                    ->default(FormFieldOption::FIELD_TYPE_DROPDOWN)
                    ->after('field_group');
            });
        }

        DB::table('form_field_options')
            ->whereNull('field_type')
            ->orWhere('field_type', '')
            ->update(['field_type' => FormFieldOption::FIELD_TYPE_DROPDOWN]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('form_field_options', 'field_type')) {
            Schema::table('form_field_options', function (Blueprint $table) {
                $table->dropColumn('field_type');
            });
        }
    }
};
