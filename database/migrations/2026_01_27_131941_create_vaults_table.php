<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default('safe'); // safe, locker, strong_room
            $table->string('security_level')->default('normal'); // normal, high, ultra
            $table->integer('capacity_items')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaults');
    }
};
