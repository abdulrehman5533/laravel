<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBin extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'warehouse_id', 'bin_code', 'bin_type'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
