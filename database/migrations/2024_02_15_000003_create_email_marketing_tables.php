<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('subject');
            $table->longText('content');
            $table->json('variables')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
        });

        // Email Campaigns
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->longText('content');
            $table->enum('recipient_type', ['all', 'segment', 'list'])->default('all');
            $table->string('recipient_segment')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
            $table->index('status');
        });

        // Email Recipients
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable();
            $table->string('email');
            $table->enum('status', ['pending', 'sent', 'opened', 'clicked', 'bounced', 'unsubscribed'])->default('pending');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamps();
            $table->index('campaign_id');
            $table->index('email');
            $table->index('status');
        });

        // Email Campaign Analytics
        Schema::create('email_campaign_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->integer('total_sent')->default(0);
            $table->integer('total_opened')->default(0);
            $table->integer('total_clicked')->default(0);
            $table->integer('total_bounced')->default(0);
            $table->integer('total_unsubscribed')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('click_rate', 5, 2)->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->unique('campaign_id');
        });

        // Email Unsubscribers
        Schema::create('email_unsubscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('email');
            $table->text('reason')->nullable();
            $table->timestamp('unsubscribed_at');
            $table->timestamp('created_at')->nullable();
            $table->unique(['email', 'tenant_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribers');
        Schema::dropIfExists('email_campaign_analytics');
        Schema::dropIfExists('email_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
    }
};
