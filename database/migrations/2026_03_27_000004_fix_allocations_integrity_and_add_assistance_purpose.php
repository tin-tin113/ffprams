<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('allocations', function (Blueprint $table) {
			if (! Schema::hasColumn('allocations', 'assistance_purpose_id')) {
				$table->foreignId('assistance_purpose_id')
					->nullable()
					->after('amount')
					->constrained('assistance_purposes')
					->nullOnDelete();

				$table->index('assistance_purpose_id');
			}
		});

		// The generated-column strategy below is MySQL-specific.
		if (DB::getDriverName() !== 'mysql') {
			return;
		}

		Schema::table('allocations', function (Blueprint $table) {
			$table->dropForeign('allocations_distribution_event_id_foreign');
			$table->dropForeign('allocations_beneficiary_id_foreign');
		});

		DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_beneficiary_unique');

		DB::statement(
			'ALTER TABLE allocations '
			. 'ADD COLUMN active_beneficiary_id BIGINT '
			. 'GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN beneficiary_id ELSE NULL END) STORED'
		);

		DB::statement(
			'CREATE UNIQUE INDEX allocation_event_active_beneficiary_unique '
			. 'ON allocations (distribution_event_id, active_beneficiary_id)'
		);

		Schema::table('allocations', function (Blueprint $table) {
			$table->foreign('distribution_event_id')
				->references('id')->on('distribution_events')
				->cascadeOnDelete();
			$table->foreign('beneficiary_id')
				->references('id')->on('beneficiaries')
				->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		if (DB::getDriverName() === 'mysql') {
			Schema::table('allocations', function (Blueprint $table) {
				$table->dropForeign('allocations_distribution_event_id_foreign');
				$table->dropForeign('allocations_beneficiary_id_foreign');
			});

			DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_active_beneficiary_unique');
			DB::statement('ALTER TABLE allocations DROP COLUMN active_beneficiary_id');
			DB::statement('CREATE UNIQUE INDEX allocation_event_beneficiary_unique ON allocations (distribution_event_id, beneficiary_id, deleted_at)');

			Schema::table('allocations', function (Blueprint $table) {
				$table->foreign('distribution_event_id')
					->references('id')->on('distribution_events')
					->cascadeOnDelete();
				$table->foreign('beneficiary_id')
					->references('id')->on('beneficiaries')
					->cascadeOnDelete();
			});
		}

		Schema::table('allocations', function (Blueprint $table) {
			if (Schema::hasColumn('allocations', 'assistance_purpose_id')) {
				$table->dropForeign(['assistance_purpose_id']);
				$table->dropIndex(['assistance_purpose_id']);
				$table->dropColumn('assistance_purpose_id');
			}
		});
	}
};
