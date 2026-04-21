<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosInvoice extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $table = 'pos_invoices';

    protected $fillable = [
        'pos_sale_id', 'format', 'content', 'file_path', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id')->withTrashed();
    }
}
