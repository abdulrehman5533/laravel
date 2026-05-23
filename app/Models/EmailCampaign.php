<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'subject',
        'description',
        'template_id',
        'content',
        'recipient_type',
        'recipient_segment',
        'total_recipients',
        'status',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get recipients
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class, 'campaign_id');
    }

    /**
     * Get analytics
     */
    public function analytics(): HasOne
    {
        return $this->hasOne(EmailCampaignAnalytics::class, 'campaign_id');
    }

    /**
     * Get sent recipients
     */
    public function sentRecipients()
    {
        return $this->recipients()->where('status', '!=', 'pending');
    }

    /**
     * Get opened recipients
     */
    public function openedRecipients()
    {
        return $this->recipients()->where('status', 'opened');
    }

    /**
     * Get clicked recipients
     */
    public function clickedRecipients()
    {
        return $this->recipients()->where('status', 'clicked');
    }

    /**
     * Get bounced recipients
     */
    public function bouncedRecipients()
    {
        return $this->recipients()->where('status', 'bounced');
    }

    /**
     * Get unsubscribed recipients
     */
    public function unsubscribedRecipients()
    {
        return $this->recipients()->where('status', 'unsubscribed');
    }

    /**
     * Calculate and update analytics
     */
    public function updateAnalytics(): void
    {
        $totalSent = $this->sentRecipients()->count();
        $totalOpened = $this->openedRecipients()->count();
        $totalClicked = $this->clickedRecipients()->count();
        $totalBounced = $this->bouncedRecipients()->count();
        $totalUnsubscribed = $this->unsubscribedRecipients()->count();

        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0;
        $bounceRate = $this->total_recipients > 0 ? round(($totalBounced / $this->total_recipients) * 100, 2) : 0;

        $analytics = $this->analytics ?? new EmailCampaignAnalytics();
        $analytics->campaign_id = $this->id;
        $analytics->total_sent = $totalSent;
        $analytics->total_opened = $totalOpened;
        $analytics->total_clicked = $totalClicked;
        $analytics->total_bounced = $totalBounced;
        $analytics->total_unsubscribed = $totalUnsubscribed;
        $analytics->open_rate = $openRate;
        $analytics->click_rate = $clickRate;
        $analytics->bounce_rate = $bounceRate;
        $analytics->save();
    }

    /**
     * Schedule campaign
     */
    public function schedule(\DateTime $scheduledAt): bool
    {
        $this->status = 'scheduled';
        $this->scheduled_at = $scheduledAt;
        $this->save();

        return true;
    }

    /**
     * Send campaign
     */
    public function send(): bool
    {
        $this->status = 'sending';
        $this->save();

        return true;
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): bool
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        return true;
    }

    /**
     * Pause campaign
     */
    public function pause(): bool
    {
        $this->status = 'paused';
        $this->save();

        return true;
    }

    /**
     * Resume campaign
     */
    public function resume(): bool
    {
        $this->status = 'sending';
        $this->save();

        return true;
    }

    /**
     * Check if campaign can be sent
     */
    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft', 'paused']) && $this->total_recipients > 0;
    }
}
