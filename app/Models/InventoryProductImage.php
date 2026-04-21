<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryProductImage extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = ['product_id', 'image_path', 'is_primary', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
