<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\GateVendorShipping as VendorShippingDB;
use App\Models\iCarryVendor as VendorDB;

class GateSFShipping extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sf_shippings';

    protected $fillable = [
        'vendor_shipping_no',
        'sf_express_no',
        'vendor_id',
        'phone',
        'sno',
        'vendor_arrival_date',
        'shipping_date',
        'stockin_date',
        'status',
        'invoice_url',
        'label_url',
        'trace_address',
    ];

    public function vendorShipping(){
        return $this->belongsTo(VendorShippingDB::class,'vendor_shipping_no','shipping_no');
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }
}
