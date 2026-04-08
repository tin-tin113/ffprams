<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'first_name')) {
                $table->string('first_name')->nullable()->after('agency_id');
            }

            if (! Schema::hasColumn('beneficiaries', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('beneficiaries', 'last_name')) {
                $table->string('last_name')->nullable()->after('middle_name');
            }

            if (! Schema::hasColumn('beneficiaries', 'name_suffix')) {
                $table->string('name_suffix')->nullable()->after('last_name');
            }
        });

        DB::table('beneficiaries')
            ->select(['id', 'full_name', 'first_name', 'middle_name', 'last_name', 'name_suffix'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                $suffixMap = [
                    'jr' => 'Jr.',
                    'sr' => 'Sr.',
                    'ii' => 'II',
                    'iii' => 'III',
                    'iv' => 'IV',
                    'v' => 'V',
                ];

                foreach ($rows as $row) {
                    if (! empty($row->first_name) && ! empty($row->last_name) && ! empty($row->name_suffix)) {
                        continue;
                    }

                    $parts = preg_split('/\s+/', trim((string) $row->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

                    if (count($parts) === 0) {
                        continue;
                    }

                    $suffix = null;

                    if (count($parts) > 1) {
                        $lastToken = (string) end($parts);
                        $normalizedSuffix = strtolower(str_replace('.', '', $lastToken));

                        if (array_key_exists($normalizedSuffix, $suffixMap)) {
                            array_pop($parts);
                            $suffix = $suffixMap[$normalizedSuffix];
                        }
                    }

                    $first = array_shift($parts);
                    $last = count($parts) ? array_pop($parts) : null;
                    $middle = count($parts) ? implode(' ', $parts) : null;

                    $updates = [];

                    if (empty($row->first_name)) {
                        $updates['first_name'] = $first;
                    }

                    if (empty($row->middle_name)) {
                        $updates['middle_name'] = $middle;
                    }

                    if (empty($row->last_name)) {
                        $updates['last_name'] = $last;
                    }

                    if (empty($row->name_suffix) && $suffix) {
                        $updates['name_suffix'] = $suffix;
                    }

                    if (empty($updates)) {
                        continue;
                    }

                    DB::table('beneficiaries')
                        ->where('id', $row->id)
                        ->update($updates);
                }
            });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('beneficiaries', 'first_name')) {
                $dropColumns[] = 'first_name';
            }

            if (Schema::hasColumn('beneficiaries', 'middle_name')) {
                $dropColumns[] = 'middle_name';
            }

            if (Schema::hasColumn('beneficiaries', 'last_name')) {
                $dropColumns[] = 'last_name';
            }

            if (Schema::hasColumn('beneficiaries', 'name_suffix')) {
                $dropColumns[] = 'name_suffix';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
