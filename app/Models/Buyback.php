<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyback extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'buyback_number',
        'customer_id',
        'branch_id',
        'item_description',
        'metal_type',
        'gross_weight',
        'stone_weight',
        'net_weight',
        'purity_reported',
        'purity_tested',
        'melting_loss_expected',
        'net_fine_weight',
        'rate_applied',
        'total_value',
        'exchange_type',
        'status',
        'refinery_batch_id',
        'internal_notes',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
