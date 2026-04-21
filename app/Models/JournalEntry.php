<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'reference_number', 'entry_date', 'narration', 'status',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isBalanced(): bool
    {
        $totalDebit = $this->items->sum('debit');
        $totalCredit = $this->items->sum('credit');

        return abs($totalDebit - $totalCredit) < 0.01;
    }
}
