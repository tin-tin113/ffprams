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

		try {
			Schema::table('allocations', function (Blueprint $table) {
				$table->dropForeign('allocations_distribution_event_id_foreign');
			});
		} catch (\Throwable) {
			// Ignore if the FK does not exist in current DB state.
		}

		try {
			Schema::table('allocations', function (Blueprint $table) {
				$table->dropForeign('allocations_beneficiary_id_foreign');
			});
		} catch (\Throwable) {
			// Ignore if the FK does not exist in current DB state.
		}

		try {
			DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_beneficiary_unique');
		} catch (\Throwable) {
			// Ignore if the index does not exist in current DB state.
		}

		if (! Schema::hasColumn('allocations', 'active_beneficiary_id')) {
			DB::statement(
				'ALTER TABLE allocations '
				. 'ADD COLUMN active_beneficiary_id BIGINT '
				. 'GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN beneficiary_id ELSE NULL END) STORED'
			);
		}

		try {
			DB::statement(
				'CREATE UNIQUE INDEX allocation_event_active_beneficiary_unique '
				. 'ON allocations (distribution_event_id, active_beneficiary_id)'
			);
		} catch (\Throwable) {
			// Ignore if the index already exists in current DB state.
		}

		// Remove orphan rows before re-adding strict FKs.
		DB::statement('DELETE a FROM allocations a LEFT JOIN distribution_events d ON d.id = a.distribution_event_id WHERE d.id IS NULL');
		DB::statement('DELETE a FROM allocations a LEFT JOIN beneficiaries b ON b.id = a.beneficiary_id WHERE b.id IS NULL');

		try {
			Schema::table('allocations', function (Blueprint $table) {
				$table->foreign('distribution_event_id')
					->references('id')->on('distribution_events')
					->cascadeOnDelete();
			});
		} catch (\Throwable) {
			// Ignore if already present or not re-creatable in this DB state.
		}

		try {
			Schema::table('allocations', function (Blueprint $table) {
				$table->foreign('beneficiary_id')
					->references('id')->on('beneficiaries')
					->cascadeOnDelete();
			});
		} catch (\Throwable) {
			// Ignore if already present or not re-creatable in this DB state.
		}
	}

	public function down(): void
	{
		if (DB::getDriverName() === 'mysql') {
			try {
				Schema::table('allocations', function (Blueprint $table) {
					$table->dropForeign('allocations_distribution_event_id_foreign');
				});
			} catch (\Throwable) {
				// Ignore if the FK does not exist in current DB state.
			}

			try {
				Schema::table('allocations', function (Blueprint $table) {
					$table->dropForeign('allocations_beneficiary_id_foreign');
				});
			} catch (\Throwable) {
				// Ignore if the FK does not exist in current DB state.
			}

			try {
				DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_active_beneficiary_unique');
			} catch (\Throwable) {
				// Ignore if the index does not exist in current DB state.
			}

			try {
				DB::statement('ALTER TABLE allocations DROP COLUMN active_beneficiary_id');
			} catch (\Throwable) {
				// Ignore if the generated column does not exist in current DB state.
			}

			try {
				DB::statement('CREATE UNIQUE INDEX allocation_event_beneficiary_unique ON allocations (distribution_event_id, beneficiary_id, deleted_at)');
			} catch (\Throwable) {
				// Ignore if the index already exists in current DB state.
			}

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
