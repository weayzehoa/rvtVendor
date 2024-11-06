<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GateAdmin as AdminDB;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseOrderChangeLog as PurchaseOrderChangeLogDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GatePurchaseOrderItemPackage as PurchaseOrderItemPackageDB;
use App\Models\GatePurchaseOrderItemSingle as PurchaseOrderItemSingleDB;
use App\Models\GatePurchaseSyncedLog as PurchaseOrderSyncedLogDB;
use App\Models\GateVendorShipping as ShippingDB;
use App\Models\GateVendorShippingItem as ShippingItemDB;
use App\Models\GateVendorShippingExpress as ExpressDB;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;
use DB;
use Carbon\Carbon;

trait ShippingFunctionTrait
{
    protected function getShippingData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $purchaseOrderItemPackageTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemPackageDB)->getTable();
        $purchaseOrderChangeLogTable = env('DB_ERPGATE').'.'.(new PurchaseOrderChangeLogDB)->getTable();
        $shippingTable = env('DB_ERPGATE').'.'.(new ShippingDB)->getTable();
        $shippingItemTable = env('DB_ERPGATE').'.'.(new ShippingItemDB)->getTable();
        $expressTable = env('DB_ERPGATE').'.'.(new ExpressDB)->getTable();

        auth('vendor')->user() ? $vendorId = auth('vendor')->user()->vendor_id : $vendorId = $request->vendorId;

        if(isset($request['id'])){ //指定選擇的訂單

        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }

        $shippings = ShippingDB::with('items','items.packages','items.packages.stockins','items.purchasePackages','items.orderPackages','items.express','items.stockins','nonDirectShip');
        $shippings = $shippings->rightJoin($shippingItemTable,$shippingItemTable.'.shipping_no',$shippingTable.'.shipping_no');
        $shippings = $shippings->where($shippingTable.'.vendor_id',$vendorId);
        $type == 'export' ? $shippings = $shippings->whereIn($shippingTable.'.status',[0,1,2,3]) : $shippings = $shippings->where($shippingTable.'.status','>=',0);
        $type == 'export' ? $shippings = $shippings->with('directShip') : '';

        isset($request['id']) && is_array($request['id']) ? $shippings = $shippings->whereIn($shippingTable.'.id',$request['id']) : '';

        isset($status) ? $shippings = $shippings->whereIn($shippingTable.'.status',explode(',',$status)) : $shippings = $shippings->whereIn($shippingTable.'.status',[0,1,2,3,4]);
        isset($vendor_arrival_date) ? $shippings = $shippings->where($shippingTable.'.vendor_arrival_date','>=',$vendor_arrival_date) : '';
        isset($vendor_arrival_date_end) ? $shippings = $shippings->where($shippingTable.'.vendor_arrival_date','<=',$vendor_arrival_date_end) : '';
        isset($purchase_no) ? $shippings = $shippings->where($shippingItemTable.'.purchase_no','like',"%$purchase_no%") : '';
        isset($order_number) ? $shippings = $shippings->where($shippingItemTable.'.order_numbers','like',"%$order_number%")->where($shippingItemTable.'.direct_shipment',1) : '';
        isset($digiwin_no) ? $shippings = $shippings->where($shippingItemTable.'.digiwin_no','like',"%$digiwin_no%") : '';

        if(isset($shipping_date) || isset($shipping_date_end) || isset($express_way) || isset($express_no)){
            $shippings = $shippings->rightJoin($expressTable,$expressTable.'.vsi_id',$shippingItemTable.'.id');
            !empty($shipping_date) ? $shippings->where($expressTable.'.shipping_date','>=',$shipping_date) : '';
            !empty($shipping_date_end) ? $shippings->where($expressTable.'.shipping_date','<=',$shipping_date_end) : '';
            !empty($express_way) ? $shippings->where($expressTable.'.express_way','like',"%$express_way%") : '';
            !empty($express_no) ? $shippings->where($expressTable.'.express_no','like',"%$express_no%") : '';
            $shippings = $shippings->groupBy($expressTable.'.shipping_no');
        }else{
            $shippings = $shippings->groupBy($shippingTable.'.shipping_no');
        }
        if (!isset($list)) {
            $list = 50;
        }
        //使用rightJoin需選擇特定資料
        $shippings = $shippings->select([
            $shippingTable.'.*',
        ]);
        $shippings = $shippings->orderBy($shippingTable.'.status', 'asc')->orderBy($shippingTable.'.id', 'desc')->distinct();

        if($type == 'index'){
            $shippings = $shippings->paginate($list);
        }elseif($type == 'show'){
            if(isset($request['product_id'])){
                $shippings = $shippings->findOrFail($request['product_id']);
            }else{
                $shippings = $shippings->findOrFail($request['id']);
            }
        }else{
            $shippings = $shippings->get();
        }
        // //找出組合品入庫資料
        // foreach($shippings as $shipping){
        //     foreach($shipping->items as $item){
        //         if(strstr($item->sku,'BOM')){
        //             foreach($item->packages as $package){
        //                 $package->stockins = StockinItemSingleDB::where('poip_id',$package->poip_id)->where('is_del',0)->orderBy('stockin_date','asc')->get();
        //             }
        //         }
        //     }
        // }
        return $shippings;
    }
}
