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
use App\Models\GateStockinItemSingle as StockinItemSingleDB;
use DB;
use Carbon\Carbon;

trait PurchaseOrderFunctionTrait
{
    protected function getOrderData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $purchaseOrderItemPackageTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemPackageDB)->getTable();
        $purchaseOrderChangeLogTable = env('DB_ERPGATE').'.'.(new PurchaseOrderChangeLogDB)->getTable();
        $stockinItemSingleTable = env('DB_ERPGATE').'.'.(new StockinItemSingleDB)->getTable();

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

        $orderIds = PurchaseOrderSyncedLogDB::whereNotNull('notice_time')->where('vendor_id',$vendorId);

        if($type == 'getUnShipping'){
            $unConfirmOrderIds = PurchaseOrderSyncedLogDB::whereNull('confirm_time')
                ->where('vendor_id',$vendorId)
                ->groupBy('purchase_order_id')
                ->orderBy('created_at','desc')
                ->get()->pluck('purchase_order_id')->all();
        }

        if(isset($is_confirm)){
            if($is_confirm == 'Y'){
                $orderIds = $orderIds->whereNotNull('confirm_time');
            }else{
                $orderIds = $orderIds->whereNull('confirm_time');
            }
        }

        $type == 'confirmOrder' ? $orderIds = $orderIds->whereNull('confirm_time') : '';
        $type == 'getUnShipping' ? $orderIds = $orderIds->whereNotNull('confirm_time') : '';

        isset($notice_time) && !empty($notice_time) ? $orderIds = $orderIds->where('notice_time','>=',$notice_time) : '';
        isset($notice_time_end) && !empty($notice_time_end) ? $orderIds = $orderIds->where('notice_time','<=',$notice_time_end) : '';
        isset($request['id']) && is_array($request['id']) ? $orderIds = $orderIds->whereIn('purchase_order_id',$request['id']) : '';
        $orderIds = $orderIds->groupBy('purchase_order_id')->orderBy('created_at','desc')->get()->pluck('purchase_order_id')->all();

        if($type == 'getUnShipping') {
            $orderIds = array_diff($orderIds,$unConfirmOrderIds);
        }

        if($type == 'export'){
            $orders = PurchaseOrderDB::with('exportItems','exportItems.exportPackage','latestSynced','changeLogs');
        }else{
            $orders = PurchaseOrderDB::with('items','items.packages','items.stockins','items.returns','items.packages.stockins','items.packages.returns','latestSynced','changeLogs');
        }

        //排除 2024.05.23 以前的採購單
        env('APP_ENV') == 'local' ? '' : $orders = $orders->where($purchaseOrderTable.'.created_at','>','2024-05-23 00:00:00');

        $orders = $orders->where($purchaseOrderTable.'.vendor_id',$vendorId)->whereIn($purchaseOrderTable.'.id',$orderIds);

        isset($request['id']) && is_array($request['id']) ? $orders = $orders->whereIn($purchaseOrderTable.'.id',$request['id']) : '';

        isset($status) ? $orders = $orders->whereIn($purchaseOrderTable.'.status',explode(',',$status)) : $orders = $orders->whereIn($purchaseOrderTable.'.status',[-1,1,2,3]);
        isset($purchase_no) ? $orders = $orders->where($purchaseOrderTable.'.purchase_no','like',"%$purchase_no%") : '';

        if(!empty($is_modify)){
            $changeLogPurchaseNos = PurchaseOrderChangeLogDB::join($purchaseOrderTable,$purchaseOrderTable.'.purchase_no',$purchaseOrderChangeLogTable.'.purchase_no')
            ->whereIn($purchaseOrderTable.'.status',[-1,1,2,3])
            ->where($purchaseOrderTable.'.vendor_id',$vendorId)
            ->where($purchaseOrderChangeLogTable.'.status','修改')->select($purchaseOrderChangeLogTable.'.purchase_no')->get()->pluck('purchase_no')->all();
            if($is_modify == 'Y'){
                $orders = $orders->whereIn($purchaseOrderTable.'.purchase_no',$changeLogPurchaseNos);
            }else{
                $orders = $orders->whereNotIn($purchaseOrderTable.'.purchase_no',$changeLogPurchaseNos);
            }
        }

        if(!empty($is_shipping) || !empty($product_name) || !empty($digiwin_no) || !empty($vendor_arrival_date) || !empty($vendor_arrival_date_end)){
            $orders = $orders->rightJoin($purchaseOrderItemTable,$purchaseOrderItemTable.'.purchase_no',$purchaseOrderTable.'.purchase_no')
                ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemTable.'.product_model_id')
                ->join($productTable,$productTable.'.id',$productModelTable.'.product_id');

            $orderItemIds = [];
            if(!empty($product_name) || !empty($digiwin_no)) {
                $orderItemIds = PurchaseOrderItemPackageDB::join($productModelTable, $productModelTable.'.id', $purchaseOrderItemPackageTable.'.product_model_id')
                ->join($productTable, $productTable.'.id', $productModelTable.'.product_id');
                if(!empty($product_name)) {
                    $orderItemIds = $orderItemIds->where($productTable.'.name', 'like', "%$product_name%");
                }
                if(!empty($digiwin_no)) {
                    $orderItemIds = $orderItemIds->where($productModelTable.'.digiwin_no', 'like', "%$digiwin_no%");
                }
                $orderItemIds = $orderItemIds->get()->pluck('purchase_order_item_id')->all();
            }

            // $orders = $orders->where(function($query)use($productTable,$purchaseOrderItemTable,$productModelTable,$product_name,$digiwin_no,$orderItemIds,$vendor_arrival_date,$vendor_arrival_date_end,$is_shipping){
            $orders = $orders->where(function($query)use($productTable,$purchaseOrderItemTable,$productModelTable,$orderItemIds,$request){
                //將進來的資料作參數轉換
                foreach ($request->all() as $key => $value) {
                    $$key = $value;
                }
                if(!empty($product_name)){
                    $query = $query->where($productTable.'.name','like',"%$product_name%");
                }
                if(!empty($digiwin_no)){
                    $query = $query->where($productModelTable.'.digiwin_no','like',"%$digiwin_no%");
                }
                if(!empty($vendor_arrival_date)){
                    $query = $query->where($purchaseOrderItemTable.'.vendor_arrival_date','>=',$vendor_arrival_date);
                }
                if(!empty($vendor_arrival_date_end)){
                    $query = $query->where($purchaseOrderItemTable.'.vendor_arrival_date','<=',$vendor_arrival_date_end);
                }
                if(!empty($is_shipping)){
                    if($is_shipping == 'Y'){
                        $query = $query->whereNotNull($purchaseOrderItemTable.'.vendor_shipping_no');
                    }elseif($is_shipping == 'X'){
                        $query = $query->whereNull($purchaseOrderItemTable.'.vendor_shipping_no');
                    }
                }
                if(count($orderItemIds) > 0){
                    $query = $query->orWhereIn($purchaseOrderItemTable.'.id',$orderItemIds);
                }
            });

            $orders = $orders->groupBy($purchaseOrderTable.'.id');
        }

        if(!empty($is_stockin)){
            if($is_stockin == 'Y'){
                $orders = $orders->whereRaw("$purchaseOrderTable.purchase_no In (Select purchase_no from $stockinItemSingleTable where stockin_date is not null) ");
            }elseif($is_stockin == 'X'){
                $orders = $orders->whereRaw("$purchaseOrderTable.purchase_no not In (Select purchase_no from $stockinItemSingleTable where stockin_date is not null) ");
            }

        }

        if (!isset($list)) {
            $list = 50;
        }

        $orders = $orders->select([$purchaseOrderTable.'.*']);

        if($type == 'index'){
            $orders = $orders->orderBy($purchaseOrderTable.'.id', 'desc')->distinct()->paginate($list);
        }elseif($type == 'show'){
            if(isset($request['product_id'])){
                $orders = $orders->findOrFail($request['product_id']);
            }else{
                $orders = $orders->findOrFail($request['id']);
            }
        }else{
            if($type == 'export'){
                $orders = $orders->addSelect([
                    'vendor_name' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('name')->limit(1),
                    'company' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('company')->limit(1),
                    'contact_person' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('contact_person')->limit(1),
                    'address' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('address')->limit(1),
                    'tel' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('tel')->limit(1),
                    'fax' => VendorDB::whereColumn($vendorTable.'.id',$purchaseOrderTable.'.vendor_id')->select('fax')->limit(1),
                ]);
            }elseif($type == 'getUnShipping'){
                $purchaseOrderNos = PurchaseOrderItemDB::whereNull('vendor_shipping_no')->select('purchase_no')->groupBy('purchase_no')->get()->pluck('purchase_no')->all();
                $orders = $orders->whereIn($purchaseOrderTable.'.purchase_no',$purchaseOrderNos)
                                ->where($purchaseOrderTable.'.status','>',0);
            }

            $orders = $orders->orderBy($purchaseOrderTable.'.id', 'desc')->get();

            if($type == 'getUnShipping'){
                $orders = $orders->pluck('id')->all();
            }
        }

        return $orders;
    }

    protected function getUnShippingPurchaseOrderItemData($orderIds)
    {
        $items = [];
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        if(count($orderIds) > 0) {
            $items = PurchaseOrderItemDB::join($purchaseOrderTable,$purchaseOrderTable.'.purchase_no',$purchaseOrderItemTable.'.purchase_no')
                ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemTable.'.product_model_id')
                ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                // ->join($vendorTable,$vendorTable.'.id',$purchaseOrderTable.'.vendor_id')
                ->whereIn($purchaseOrderTable.'.id',$orderIds);
            env('APP_ENV') == 'local' ? '' : $items = $items->where($purchaseOrderTable.'.created_at','>','2024-05-23 00:00:00');
            $items = $items->where($purchaseOrderItemTable.'.is_del',0)
                ->whereNull($purchaseOrderItemTable.'.vendor_shipping_no')
                ->select([
                    $purchaseOrderItemTable.'.*',
                    DB::raw("(CASE WHEN $purchaseOrderItemTable.direct_shipment = 1 THEN '是' ELSE '否' END) as direct_shipment"),
                    $productModelTable.'.sku',
                    $productModelTable.'.digiwin_no',
                    $productTable.'.name as product_name',
                    // DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                    DB::raw("(CASE WHEN $purchaseOrderItemTable.gtin13 is not null THEN $purchaseOrderItemTable.gtin13 ELSE $productModelTable.gtin13 END) as gtin13"),
                    DB::raw("SUM($purchaseOrderItemTable.quantity) as quantity"),
                    DB::raw("GROUP_CONCAT($purchaseOrderTable.purchase_no) as purchaseNos"),
                    DB::raw("GROUP_CONCAT($purchaseOrderTable.id) as orderIds"),
                    DB::raw("GROUP_CONCAT($purchaseOrderItemTable.id) as orderItemIds"),
                ])->groupBy('vendor_arrival_date','digiwin_no','direct_shipment')
                ->get();
        }
        return $items;
    }
}
