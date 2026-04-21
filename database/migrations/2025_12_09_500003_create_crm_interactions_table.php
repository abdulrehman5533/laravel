<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->enum('interaction_type', ['call', 'sms', 'whatsapp', 'email', 'visit', 'demo', 'inquiry'])->default('call');
            $table->text('description');
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->enum('status', ['completed', 'follow_up_needed', 'pending'])->default('completed');
            $table->timestamp('interaction_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->timestamps();

            $table->index(['crm_customer_id', 'interaction_date']);
            $table->index('interaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_interactions');
    }
};
