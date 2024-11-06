<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GatePurchaseOrderItemPackage as PurchaseOrderItemPackageDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GateReturnDiscount as ReturnDiscountDB;
use App\Models\GateReturnDiscountItem as ReturnDiscountItemDB;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;
use DB;

class GatePurchaseOrderItem extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'vendor_shipping_no',
    ];

    public function packages()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $purchaseOrderItemPackageTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemPackageDB)->getTable();

        //關聯組合商品-單品
        return $this->hasMany(PurchaseOrderItemPackageDB::class,'purchase_order_item_id','id')
        ->with('stockins')
        ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemPackageTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->select([
            $purchaseOrderItemPackageTable.'.*',
            $productModelTable.'.digiwin_no',
            $productModelTable.'.sku',
            $productModelTable.'.gtin13',
            $productModelTable.'.vendor_product_model_id',
            $productTable.'.name as product_name',
            // DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
            $productTable.'.unit_name',
            $productTable.'.category_id as product_category_id',
            $productTable.'.price as product_price',
            $productTable.'.vendor_price',
            $productTable.'.price as origin_price',
            $productTable.'.serving_size',
            $vendorTable.'.id as vendor_id',
            $vendorTable.'.name as vendor_name',
            $vendorTable.'.service_fee',
        ])->orderBy('vendor_arrival_date','asc');
    }

    public function exportPackage()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $purchaseOrderItemPackageTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemPackageDB)->getTable();

        //關聯組合商品-單品
        return $this->hasMany(PurchaseOrderItemPackageDB::class,'purchase_order_item_id','id')
        ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemPackageTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->where($purchaseOrderItemPackageTable.'.is_del',0)
        ->select([
            $purchaseOrderItemPackageTable.'.*',
            $productModelTable.'.digiwin_no',
            $productModelTable.'.sku',
            $productModelTable.'.gtin13',
            $productModelTable.'.vendor_product_model_id',
            // $productTable.'.name as product_name',
            DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
            $productTable.'.unit_name',
            $productTable.'.category_id as product_category_id',
            $productTable.'.price as product_price',
            $productTable.'.vendor_price',
            $productTable.'.price as origin_price',
            $productTable.'.serving_size',
            $vendorTable.'.id as vendor_id',
            $vendorTable.'.name as vendor_name',
            $vendorTable.'.service_fee',
        ])->orderBy('vendor_arrival_date','asc');
    }

    public function returns()
    {
        $returnDiscountTable = env('DB_ERPGATE').'.'.(new ReturnDiscountDB)->getTable();
        $returnDiscountItemTable = env('DB_ERPGATE').'.'.(new ReturnDiscountItemDB)->getTable();
        return $this->hasMany(ReturnDiscountItemDB::class,'poi_id','id')
            ->join($returnDiscountTable,$returnDiscountTable.'.return_discount_no',$returnDiscountItemTable.'.return_discount_no')
            ->where($returnDiscountTable.'.is_del',0)
            ->select([
                $returnDiscountItemTable.'.*',
                $returnDiscountTable.'.return_date',
            ]);
    }

    public function stockins()
    {
        return $this->hasMany(StockinItemSingleDB::class,'poi_id','id')->whereNull('poip_id')->where('is_del',0)->orderBy('stockin_date','asc');
    }
}
