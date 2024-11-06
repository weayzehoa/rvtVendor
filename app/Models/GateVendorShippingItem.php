<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GateSellImport as SellImportDB;
use App\Models\GateVendorShipping as ShippingDB;
use App\Models\GateVendorShippingExpress as ShippingExpressDB;
use App\Models\GateVendorShippingItemPackage as ShippingItemPackageDB;
use App\Models\GatePurchaseOrderItemPackage as PurchaseOrderItemPackageDB;
use App\Models\iCarryOrderItemPackage as OrderItemPackageDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;


class GateVendorShippingItem extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'vendor_shipping_items';

    protected $fillable = [
        'shipping_no',
        'purchase_no',
        'product_model_id',
        'poi_id',
        'ori_id',
        'si_id',
        'product_name',
        'sku',
        'digiwin_no',
        'gtin13',
        'order_ids',
        'order_numbers',
        'quantity',
        'direct_shipment',
        'vendor_arrival_date',
        'is_del',
    ];

    public function packages(){
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $shippingItemPackageTable = env('DB_ERPGATE').'.'.(new ShippingItemPackageDB)->getTable();
        return $this->hasMany(ShippingItemPackageDB::class,'vsi_id','id')
        ->join($productModelTable,$productModelTable.'.id',$shippingItemPackageTable.'.product_model_id')
        ->select([
            $shippingItemPackageTable.'.*',
            $productModelTable.'.vendor_product_model_id',
        ]);
    }

    public function purchasePackages(){
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemPackageTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemPackageDB)->getTable();

        return $this->hasMany(PurchaseOrderItemPackageDB::class,'purchase_order_item_id','poi_id')->with('stockins')
        ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemPackageTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->select([
            $purchaseOrderItemPackageTable.'.*',
            $productTable.'.name as product_name',
            $productModelTable.'.digiwin_no',
            $productModelTable.'.vendor_product_model_id',
        ]);
    }

    public function orderPackages(){
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $orderItemPackageTable = env('DB_ICARRY').'.'.(new OrderItemPackageDB)->getTable();

        return $this->hasMany(OrderItemPackageDB::class,'order_item_id','ori_id')
        ->join($productModelTable,$productModelTable.'.id',$orderItemPackageTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->select([
            $orderItemPackageTable.'.*',
            $productTable.'.name as product_name',
            $productModelTable.'.origin_digiwin_no',
            $productModelTable.'.vendor_product_model_id',
        ]);
    }

    public function express(){
        return $this->hasMany(ShippingExpressDB::class,'vsi_id','id');
    }

    public function sellImport(){
        return $this->hasOne(SellImportDB::class,'vsi_id','id');
    }

    public function shipping(){
        return $this->beLongsTo(ShippingDB::class,'shipping_no','shipping_no');
    }

    public function stockins()
    {
        return $this->hasMany(StockinItemSingleDB::class,'poi_id','poi_id')->whereNull('poip_id')->where('is_del',0)->orderBy('stockin_date','asc');
    }
}
