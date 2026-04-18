<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('program_legal_requirements')) {
            Schema::create('program_legal_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('program_name_id')->constrained('program_names')->cascadeOnDelete();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('document_type', 100)->nullable();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('path', 500);
                $table->string('disk', 50)->default('program_documents');
                $table->string('mime_type', 150);
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->char('sha256', 64)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['program_name_id', 'created_at']);
                $table->unique(['disk', 'path']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('program_legal_requirements');
    }
};
