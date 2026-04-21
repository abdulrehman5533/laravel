<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseReconciliation extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'warehouse_id',
        'reconciliation_number',
        'status',
        'conducted_by',
        'conducted_at',
        'notes',
    ];

    protected $casts = [
        'conducted_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(WarehouseReconciliationItem::class, 'reconciliation_id');
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
