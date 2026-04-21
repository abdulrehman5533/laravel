<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_templates', 'trigger_event')) {
                $table->string('trigger_event')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('notification_templates', 'channels')) {
                $table->json('channels')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('notification_templates', 'language')) {
                $table->string('language')->default('en')->after('channels');
            }
            if (Schema::hasColumn('notification_templates', 'content')) {
                $table->renameColumn('content', 'body');
            }
        });

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
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->renameColumn('body', 'content');
            $table->dropColumn(['trigger_event', 'channels', 'language']);
        });
    }
};
