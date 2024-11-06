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


use App\Models\GateSFShipping as SFShippingDB;

use DB;
use Carbon\Carbon;

trait SFShippingFunctionTrait
{
    protected function getSFShippingData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $sfShipingTable = env('DB_ERPGATE').'.'.(new SFShippingDB)->getTable();
        $shippings = SFShippingDB::join($vendorTable,$vendorTable.'.id',$sfShipingTable.'.vendor_id');

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

        $shippings = $shippings->where($sfShipingTable.'.vendor_id',$vendorId);

        isset($status) ? $shippings = $shippings->whereIn($sfShipingTable.'.status',explode(',',$status)) : '';
        isset($vendor_arrival_date) ? $shippings = $shippings->where($sfShipingTable.'.vendor_arrival_date','>=',$vendor_arrival_date) : '';
        isset($vendor_arrival_date_end) ? $shippings = $shippings->where($sfShipingTable.'.vendor_arrival_date','<=',$vendor_arrival_date_end) : '';
        isset($shipping_date) ? $shippings = $shippings->where($sfShipingTable.'.shipping_date','>=',$shipping_date) : '';
        isset($shipping_date_end) ? $shippings = $shippings->where($sfShipingTable.'.shipping_date','<=',$shipping_date_end) : '';
        isset($stockin_date) ? $shippings = $shippings->where($sfShipingTable.'.stockin_date','>=',$stockin_date) : '';
        isset($stockin_date_end) ? $shippings = $shippings->where($sfShipingTable.'.stockin_date','<=',$stockin_date_end) : '';

        if(isset($vendor_name)){
            $shippings = $shippings->where(function($query)use($vendorTable,$vendor_name){
                $query->where($vendorTable.'.name','like',"%$vendor_name%")->orWhere($vendorTable.'.company','like',"%$vendor_name%");
            });
        }

        if (!isset($list)) {
            $list = 50;
        }

        $shippings = $shippings->select([
            $sfShipingTable.'.*',
            $vendorTable.'.name as vendor_name',
        ]);

        if($type == 'index'){
            $shippings = $shippings->orderBy('status','desc')->paginate($list);
        }elseif($type == 'show'){
            $shippings = $shippings->findOrFail($request['id']);
        }else{
            $shippings = $shippings->get();
        }

        return $shippings;
    }
}
