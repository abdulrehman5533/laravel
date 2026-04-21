<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = ['name', 'code', 'address', 'phone', 'email', 'manager_id', 'is_active', 'is_company'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_company' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(JewelleryProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function cashbooks(): HasMany
    {
        return $this->hasMany(Cashbook::class);
    }

    public function cashierShifts(): HasMany
    {
        return $this->hasMany(CashierShift::class);
    }

    public function pettyCash(): HasMany
    {
        return $this->hasMany(PettyCash::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function supplierLedgers(): HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }

    public function taxConfigurations(): HasMany
    {
        return $this->hasMany(TaxConfiguration::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function periodLocks(): HasMany
    {
        return $this->hasMany(PeriodLock::class);
    }
}
