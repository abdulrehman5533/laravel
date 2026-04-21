<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->integer('max_users')->default(-1); // -1 for unlimited
            $table->integer('max_branches')->default(-1);
            $table->integer('max_products')->default(-1);
            $table->json('features')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Update tenants table to add plan_id foreign key if not already there or to ensure it's structured
        if (Schema::hasTable('tenants') && ! Schema::hasColumn('tenants', 'plan_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('id');
                $table->foreign('plan_id')->references('id')->on('plans');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
