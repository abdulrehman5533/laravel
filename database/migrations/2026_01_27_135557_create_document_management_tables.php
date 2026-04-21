<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('documentable_type')->nullable(); // Polymorphic link
            $table->unsignedBigInteger('documentable_id')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->json('metadata')->nullable(); // Tags, OCR data, etc.
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->integer('version_number');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
