<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog, HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'account_code', 'account_name', 'account_type', 'account_category', 'mapping_key', 'branch_id',
        'parent_account_id', 'opening_balance', 'current_balance', 'is_active', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    public function subAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class, 'account_id');
    }

    /**
     * Query scope to filter accounts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Query scope to get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
