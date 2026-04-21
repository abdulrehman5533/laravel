<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('purity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('karat');
            $table->decimal('percentage', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['shop', 'warehouse', 'locker', 'safe', 'branch']);
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('capacity', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('purity_id')->nullable()->constrained('purity_levels')->onDelete('set null');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            $table->decimal('weight', 10, 4);
            $table->string('unit')->default('gram');
            $table->integer('stone_count')->nullable();
            $table->decimal('stone_carat', 10, 4)->nullable();
            $table->string('stone_type')->nullable();

            $table->string('barcode')->nullable()->unique();
            $table->longText('qr_code')->nullable();
            $table->string('image_path')->nullable();

            $table->decimal('cost_price', 15, 2);
            $table->decimal('selling_price', 15, 2);

            $table->decimal('current_stock', 15, 4)->default(0);
            $table->decimal('reorder_level', 15, 4);
            $table->decimal('reorder_quantity', 15, 4);

            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['branch_id', 'current_stock']);
        });

        Schema::create('stone_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->onDelete('cascade');
            $table->string('stone_type');
            $table->string('cut')->nullable();
            $table->string('clarity')->nullable();
            $table->string('color')->nullable();
            $table->decimal('carat', 10, 4);
            $table->string('origin')->nullable();
            $table->string('treatment')->nullable();
            $table->string('certification')->nullable();
            $table->string('certificate_number')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('stock_locations')->onDelete('set null');
            $table->decimal('quantity', 15, 4);
            $table->enum('type', ['add', 'subtract', 'transfer', 'wastage', 'damage']);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['location_id', 'created_at']);
        });

        Schema::create('wastage_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('damage_repair_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->enum('status', ['damaged', 'repairing', 'repaired', 'discarded']);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_repair_trackings');
        Schema::dropIfExists('wastage_trackings');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stone_attributes');
        Schema::dropIfExists('inventory_products');
        Schema::dropIfExists('stock_locations');
        Schema::dropIfExists('purity_levels');
        Schema::dropIfExists('product_categories');
    }
};
