<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add delivery status and callback tracking for SMS logs (D10).
     */
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            // Add delivery status field (separate from HTTP response)
            if (!Schema::hasColumn('sms_logs', 'delivery_status')) {
                $table->enum('delivery_status', ['pending', 'delivered', 'failed', 'undeliverable'])->default('pending')->after('status');
            }

            // Add gateway message ID for tracking
            if (!Schema::hasColumn('sms_logs', 'gateway_message_id')) {
                $table->string('gateway_message_id')->nullable()->after('response');
            }

            // Add callback received timestamp
            if (!Schema::hasColumn('sms_logs', 'callback_received_at')) {
                $table->timestamp('callback_received_at')->nullable()->after('sent_at');
            }

            // Add retry count
            if (!Schema::hasColumn('sms_logs', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('callback_received_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'gateway_message_id', 'callback_received_at', 'retry_count']);
        });
    }
};
