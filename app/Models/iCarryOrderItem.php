<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryOrderItemPackage as OrderItemPackageDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\PurchaseOrder as PurchaseOrderDB;
use App\Models\GateSyncedOrderItem as SyncedOrderItemDB;
use DB;

class iCarryOrderItem extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'order_item';
    //變更 Laravel 預設 created_at 與 不使用 updated_at 欄位
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    public function order()
    {
        return $this->belongsTo(OrderDB::class,'order_id','id');
    }

    public function package()
    {
        //關聯組合商品-單品
        return $this->hasMany(OrderItemPackageDB::class,'order_item_id','id')
        ->join('product_model','product_model.id','order_item_package.product_model_id')
        ->join('product','product.id','product_model.product_id')
        ->join('vendor','vendor.id','product.vendor_id')
        ->select([
            'order_item_package.*',
            'product_model.origin_digiwin_no',
            'product_model.digiwin_no',
            'product_model.sku',
            'product_model.gtin13',
            'product.unit_name',
            'product.id as product_id',
            'product.serving_size',
            'product.price',
            'product.price as product_price',
            'product.direct_shipment as directShip',
            'product.price as origin_price',
            'vendor.id as vendor_id',
            'vendor.name as vendor_name',
            DB::raw("CONCAT(vendor.name,' ',product.name,'-',product_model.name) as product_name"),
        ]);
    }
    public function purchaseOrder()
    {
        return $this->setConnection('mysql')->hasOne(PurchaseOrderDB::class,'id','order_item_id');
    }

    public function model(){
        return $this->belongsTo(ProductModelDB::class,'product_model_id','id');
    }

    public function SyncedOrderItem(){
        return $this->setConnection('erpGate')->hasOne(syncedOrderItemDB::class,'order_item_id','id');
    }
}
