<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmInteraction extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'crm_customer_id', 'interaction_type', 'description', 'sentiment', 'status',
        'interaction_date', 'created_by', 'assigned_to', 'follow_up_date', 'follow_up_notes',
    ];

    protected $casts = [
        'interaction_date' => 'datetime',
        'follow_up_date' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'crm_customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('crm_customer_id', $customerId);
    }

    public function scopeNeedingFollowUp($query)
    {
        return $query->where('status', 'follow_up_needed')
            ->where('follow_up_date', '<=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('interaction_type', $type);
    }

    // Methods
    public function getSentimentColor()
    {
        return match ($this->sentiment) {
            'positive' => 'success',
            'neutral' => 'secondary',
            'negative' => 'danger',
            default => 'info',
        };
    }

    public function getInteractionTypeIcon()
    {
        return match ($this->interaction_type) {
            'call' => 'phone',
            'sms' => 'envelope',
            'whatsapp' => 'whatsapp',
            'email' => 'envelope-open',
            'visit' => 'map-pin',
            'demo' => 'eye',
            'inquiry' => 'question-circle',
            default => 'circle',
        };
    }

    public function needsFollowUp()
    {
        return $this->status === 'follow_up_needed' && $this->follow_up_date <= now();
    }
}
