<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('rating', 3, 2); // 1.00 to 5.00
            $table->json('criteria_scores')->nullable(); // quality, delivery_speed, cost
            $table->text('comments')->nullable();
            $table->date('rating_date');
            $table->timestamps();
        });

        Schema::create('procurement_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['auto_reorder', 'blacklist_threshold', 'preferred_vendor']);
            $table->json('conditions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_rules');
        Schema::dropIfExists('vendor_ratings');
    }
};
