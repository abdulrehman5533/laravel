<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Welcome Email, Invoice SMS
            $table->string('channel'); // email, sms, whatsapp, push
            $table->string('subject')->nullable();
            $table->text('content'); // Supports placeholders like {customer_name}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient');
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('content');
            $table->string('status')->default('sent'); // sent, failed, delivered, read
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('notification_templates');
    }
};
