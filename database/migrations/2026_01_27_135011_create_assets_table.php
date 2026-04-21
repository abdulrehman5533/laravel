<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->foreignId('branch_id')->constrained();
            $table->string('category'); // Furniture, IT, Machinery, Vaults
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->string('status')->default('active'); // active, maintenance, disposed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
