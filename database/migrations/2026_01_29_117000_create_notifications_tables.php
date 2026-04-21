<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('trigger_event')->unique();
                $table->string('subject')->nullable();
                $table->text('body');
                $table->json('channels'); // email, sms, whatsapp, in_app
                $table->string('language')->default('en');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_history')) {
            Schema::create('notification_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('channel');
                $table->string('event_type');
                $table->string('status'); // sent, failed
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_history');
        Schema::dropIfExists('notification_templates');
    }
};
