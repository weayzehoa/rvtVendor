<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;
use App\Models\GateVendorShippingItemPackage as ShippingItemPackageDB;

class GateVendorShippingItemPackage extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'vendor_shipping_item_packages';
    protected $fillable = [
        'vsi_id',
        'poi_id',
        'poip_id',
        'product_model_id',
        'product_name',
        'digiwin_no',
        'quantity',
        'is_del',
    ];

    public function stockins()
    {
        $stockinItemSingleTable = env('DB_ERPGATE').'.'.(new StockinItemSingleDB)->getTable();
        $vendorShippingItemPackageTable = env('DB_ERPGATE').'.'.(new ShippingItemPackageDB)->getTable();

        return $this->hasMany(StockinItemSingleDB::class,'poip_id','poip_id')->orderBy($stockinItemSingleTable.'.stockin_date','asc');
    }
}
