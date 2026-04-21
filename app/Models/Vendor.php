<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'vendor_code', 'name', 'contact_person', 'email', 'phone',
        'address', 'gst_number', 'material_speciality', 'total_purchases', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_purchases' => 'decimal:2',
    ];

    public static function generateVendorCode()
    {
        $lastVendor = self::orderBy('id', 'desc')->first();
        $number = $lastVendor ? intval(substr($lastVendor->vendor_code, 3)) + 1 : 1;

        return 'VEN'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
