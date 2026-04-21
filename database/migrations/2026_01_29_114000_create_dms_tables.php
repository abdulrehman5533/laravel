<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('id')->constrained('document_folders')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->after('folder_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->after('tenant_id')->constrained()->onDelete('cascade');
            $table->date('expiry_date')->nullable()->after('file_size');
            $table->boolean('is_verified')->default(false)->after('expiry_date');
            $table->foreignId('verified_by')->nullable()->after('is_verified')->constrained('users');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->string('version')->default('1.0')->after('file_path');
        });

        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // view, download, upload, edit
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['folder_id', 'tenant_id', 'branch_id', 'expiry_date', 'is_verified', 'verified_by', 'verified_at', 'version']);
        });

        Schema::dropIfExists('document_folders');
    }
};
