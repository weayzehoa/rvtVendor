<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GatePurchaseSyncedLog as PurchaseSyncedLogDB;
use App\Models\GatePurchaseOrderChangeLog as PurchaseOrderChangeLogDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use DB;

class GatePurchaseOrder extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'purchase_orders';

    public function items()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();

        return $this->hasMany(PurchaseOrderItemDB::class,'purchase_no','purchase_no')
            ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemTable.'.product_model_id')
            ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
            ->select([
                $purchaseOrderItemTable.'.*',
                // $productTable.'.name as product_name',
                DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                $productTable.'.unit_name',
                $productTable.'.category_id as product_category_id',
                $productTable.'.vendor_price',
                $productTable.'.price as product_price',
                $productTable.'.serving_size',
                $productTable.'.model_name',
                $productModelTable.'.name as product_model_name',
                $productModelTable.'.digiwin_no',
                $productModelTable.'.sku',
                $productModelTable.'.gtin13',
                $productModelTable.'.vendor_product_model_id',
                $vendorTable.'.id as vendor_id',
                $vendorTable.'.name as vendor_name',
                $vendorTable.'.service_fee',
            ])->orderBy('vendor_arrival_date','asc');
    }

    public function exportItems()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();

        return $this->hasMany(PurchaseOrderItemDB::class,'purchase_no','purchase_no')
            ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemTable.'.product_model_id')
            ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
            ->where($purchaseOrderItemTable.'.is_del',0)
            ->select([
                $purchaseOrderItemTable.'.*',
                // $productTable.'.name as product_name',
                DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                $productTable.'.unit_name',
                $productTable.'.category_id as product_category_id',
                $productTable.'.vendor_price',
                $productTable.'.price as product_price',
                $productTable.'.serving_size',
                $productTable.'.model_name',
                $productModelTable.'.name as product_model_name',
                $productModelTable.'.digiwin_no',
                $productModelTable.'.sku',
                $productModelTable.'.gtin13',
                $productModelTable.'.vendor_product_model_id',
                $vendorTable.'.id as vendor_id',
                $vendorTable.'.name as vendor_name',
                $vendorTable.'.service_fee',
            ])->orderBy('vendor_arrival_date','asc');
    }

    public function latestSynced()
    {
        return $this->hasOne(PurchaseSyncedLogDB::class,'purchase_order_id','id')->whereNotNull('notice_time')->orderBy('created_at','desc');
    }

    public function changeLogs()
    {
        return $this->hasMany(PurchaseOrderChangeLogDB::class,'purchase_no','purchase_no');
    }
}
