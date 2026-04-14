<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database cleanup: drop tables that have no code references
 * and are confirmed unused by the application.
 *
 * - duplicate_flags: no model, no controller, no code references, 0 rows
 * - beneficiary_filter_presets: orphaned model, never wired to routes/controllers
 * - password_reset_tokens: no password-reset feature exists in the app
 * - jobs / failed_jobs / job_batches: app never dispatches queued jobs
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('duplicate_flags');
        Schema::dropIfExists('beneficiary_filter_presets');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }

    public function down(): void
    {
        // duplicate_flags
        Schema::create('duplicate_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_beneficiary_id')->constrained('beneficiaries')->cascadeOnDelete();
            $table->foreignId('existing_beneficiary_id')->constrained('beneficiaries')->cascadeOnDelete();
            $table->string('match_type', 50);
            $table->tinyInteger('match_score')->unsigned();
            $table->enum('status', ['pending', 'merged', 'rejected', 'kept_both'])->default('pending');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // beneficiary_filter_presets
        Schema::create('beneficiary_filter_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->json('filters');
            $table->timestamps();
        });

        // password_reset_tokens (Laravel default)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // jobs (Laravel default)
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // job_batches (Laravel default)
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // failed_jobs (Laravel default)
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
};
