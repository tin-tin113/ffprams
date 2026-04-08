<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            if (! Schema::hasColumn('distribution_events', 'legal_basis_type')) {
                $table->enum('legal_basis_type', ['resolution', 'ordinance', 'memo', 'special_order', 'other'])
                    ->nullable()
                    ->after('total_fund_amount');
                $table->index('legal_basis_type');
            }

            if (! Schema::hasColumn('distribution_events', 'legal_basis_reference_no')) {
                $table->string('legal_basis_reference_no', 150)
                    ->nullable()
                    ->after('legal_basis_type');
            }

            if (! Schema::hasColumn('distribution_events', 'legal_basis_date')) {
                $table->date('legal_basis_date')
                    ->nullable()
                    ->after('legal_basis_reference_no');
            }

            if (! Schema::hasColumn('distribution_events', 'legal_basis_remarks')) {
                $table->text('legal_basis_remarks')
                    ->nullable()
                    ->after('legal_basis_date');
            }

            if (! Schema::hasColumn('distribution_events', 'fund_source')) {
                $table->enum('fund_source', ['lgu_trust_fund', 'nga_transfer', 'local_program', 'other'])
                    ->nullable()
                    ->after('legal_basis_remarks');
                $table->index('fund_source');
            }

            if (! Schema::hasColumn('distribution_events', 'trust_account_code')) {
                $table->string('trust_account_code', 100)
                    ->nullable()
                    ->after('fund_source');
            }

            if (! Schema::hasColumn('distribution_events', 'fund_release_reference')) {
                $table->string('fund_release_reference', 150)
                    ->nullable()
                    ->after('trust_account_code');
            }

            if (! Schema::hasColumn('distribution_events', 'liquidation_status')) {
                $table->enum('liquidation_status', ['not_required', 'pending', 'submitted', 'verified'])
                    ->default('not_required')
                    ->after('fund_release_reference');
                $table->index('liquidation_status');
            }

            if (! Schema::hasColumn('distribution_events', 'liquidation_due_date')) {
                $table->date('liquidation_due_date')
                    ->nullable()
                    ->after('liquidation_status');
                $table->index('liquidation_due_date');
            }

            if (! Schema::hasColumn('distribution_events', 'liquidation_submitted_at')) {
                $table->timestamp('liquidation_submitted_at')
                    ->nullable()
                    ->after('liquidation_due_date');
            }

            if (! Schema::hasColumn('distribution_events', 'liquidation_reference_no')) {
                $table->string('liquidation_reference_no', 150)
                    ->nullable()
                    ->after('liquidation_submitted_at');
            }

            if (! Schema::hasColumn('distribution_events', 'requires_farmc_endorsement')) {
                $table->boolean('requires_farmc_endorsement')
                    ->default(false)
                    ->after('liquidation_reference_no');
                $table->index('requires_farmc_endorsement');
            }

            if (! Schema::hasColumn('distribution_events', 'farmc_endorsed_at')) {
                $table->timestamp('farmc_endorsed_at')
                    ->nullable()
                    ->after('requires_farmc_endorsement');
            }

            if (! Schema::hasColumn('distribution_events', 'farmc_reference_no')) {
                $table->string('farmc_reference_no', 150)
                    ->nullable()
                    ->after('farmc_endorsed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribution_events', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_events', 'farmc_reference_no')) {
                $table->dropColumn('farmc_reference_no');
            }

            if (Schema::hasColumn('distribution_events', 'farmc_endorsed_at')) {
                $table->dropColumn('farmc_endorsed_at');
            }

            if (Schema::hasColumn('distribution_events', 'requires_farmc_endorsement')) {
                $table->dropIndex(['requires_farmc_endorsement']);
                $table->dropColumn('requires_farmc_endorsement');
            }

            if (Schema::hasColumn('distribution_events', 'liquidation_reference_no')) {
                $table->dropColumn('liquidation_reference_no');
            }

            if (Schema::hasColumn('distribution_events', 'liquidation_submitted_at')) {
                $table->dropColumn('liquidation_submitted_at');
            }

            if (Schema::hasColumn('distribution_events', 'liquidation_due_date')) {
                $table->dropIndex(['liquidation_due_date']);
                $table->dropColumn('liquidation_due_date');
            }

            if (Schema::hasColumn('distribution_events', 'liquidation_status')) {
                $table->dropIndex(['liquidation_status']);
                $table->dropColumn('liquidation_status');
            }

            if (Schema::hasColumn('distribution_events', 'fund_release_reference')) {
                $table->dropColumn('fund_release_reference');
            }

            if (Schema::hasColumn('distribution_events', 'trust_account_code')) {
                $table->dropColumn('trust_account_code');
            }

            if (Schema::hasColumn('distribution_events', 'fund_source')) {
                $table->dropIndex(['fund_source']);
                $table->dropColumn('fund_source');
            }

            if (Schema::hasColumn('distribution_events', 'legal_basis_remarks')) {
                $table->dropColumn('legal_basis_remarks');
            }

            if (Schema::hasColumn('distribution_events', 'legal_basis_date')) {
                $table->dropColumn('legal_basis_date');
            }

            if (Schema::hasColumn('distribution_events', 'legal_basis_reference_no')) {
                $table->dropColumn('legal_basis_reference_no');
            }

            if (Schema::hasColumn('distribution_events', 'legal_basis_type')) {
                $table->dropIndex(['legal_basis_type']);
                $table->dropColumn('legal_basis_type');
            }
        });
    }
};
