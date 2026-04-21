<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module'); // e.g., Purchase, Sale, HR
            $table->json('steps'); // stores approval hierarchy
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained();
            $table->string('model_type'); // e.g., App\Models\PurchaseOrder
            $table->unsignedBigInteger('model_id');
            $table->integer('current_step')->default(1);
            $table->foreignId('approver_id')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected', 'escalated'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflows');
    }
};
