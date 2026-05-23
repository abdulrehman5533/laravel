<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Barcode/RFID Configuration
        Schema::create('barcode_rfid_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->enum('barcode_type', ['ean13', 'code128', 'qr'])->default('qr');
            $table->boolean('rfid_enabled')->default(false);
            $table->string('rfid_reader_type')->nullable();
            $table->boolean('auto_generate_barcode')->default(true);
            $table->string('barcode_prefix')->nullable();
            $table->timestamps();
            $table->unique('tenant_id');
        });

        // Product Barcodes
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product_id')->nullable();
            $table->string('barcode_number')->unique();
            $table->string('barcode_type');
            $table->longText('barcode_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('inventory_product_id');
            $table->index('barcode_number');
        });

        // RFID Tags
        Schema::create('rfid_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product_id')->nullable();
            $table->string('rfid_tag_id')->unique();
            $table->string('tag_type');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_read_at')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('inventory_product_id');
            $table->index('rfid_tag_id');
        });

        // Barcode Scan Logs
        Schema::create('barcode_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product_id')->nullable();
            $table->string('barcode_number');
            $table->enum('scan_type', ['inbound', 'outbound', 'inventory_check', 'transfer'])->default('inventory_check');
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->timestamp('scan_timestamp');
            $table->timestamp('created_at')->nullable();
            $table->index('inventory_product_id');
            $table->index('barcode_number');
            $table->index('scan_type');
            $table->index('scan_timestamp');
        });

        // RFID Read Logs
        Schema::create('rfid_read_logs', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_tag_id');
            $table->foreignId('inventory_product_id')->nullable();
            $table->string('reader_id');
            $table->foreignId('warehouse_id')->nullable();
            $table->timestamp('read_timestamp');
            $table->integer('signal_strength')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('rfid_tag_id');
            $table->index('inventory_product_id');
            $table->index('read_timestamp');
        });

        // Barcode/RFID Discrepancies
        Schema::create('barcode_rfid_discrepancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product_id')->nullable();
            $table->enum('discrepancy_type', ['missing_barcode', 'invalid_barcode', 'rfid_mismatch', 'quantity_mismatch'])->default('missing_barcode');
            $table->integer('expected_quantity')->nullable();
            $table->integer('actual_quantity')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('inventory_product_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_rfid_discrepancies');
        Schema::dropIfExists('rfid_read_logs');
        Schema::dropIfExists('barcode_scan_logs');
        Schema::dropIfExists('rfid_tags');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('barcode_rfid_config');
    }
};
