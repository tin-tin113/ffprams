<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiary_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 100)->nullable();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path', 500);
            $table->string('disk', 50)->default('beneficiary_documents');
            $table->string('mime_type', 150);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->char('sha256', 64)->nullable();
            $table->timestamps();

            $table->index(['beneficiary_id', 'created_at']);
            $table->unique(['disk', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiary_attachments');
    }
};
