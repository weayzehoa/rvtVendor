<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateVendorShippingExpress extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'vendor_shipping_expresses';
    protected $fillable = [
        'shipping_no',
        'vsi_id',
        'poi_id',
        'shipping_date',
        'express_way',
        'express_no',
    ];
}
