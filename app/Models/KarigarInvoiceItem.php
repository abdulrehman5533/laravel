<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KarigarInvoiceItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'karigar_invoice_id', 'service_job_item_id', 'description', 'weight',
        'labor_rate', 'labor_amount', 'wastage_weight', 'wastage_amount',
        'total_amount',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'labor_rate' => 'decimal:2',
        'labor_amount' => 'decimal:2',
        'wastage_weight' => 'decimal:3',
        'wastage_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(KarigarInvoice::class, 'karigar_invoice_id');
    }

    public function serviceJobItem()
    {
        return $this->belongsTo(ServiceJobItem::class);
    }
}
