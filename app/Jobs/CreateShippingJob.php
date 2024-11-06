<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GateAdmin as AdminDB;
use App\Models\GateSyncedOrderItem as SyncedOrderItemDB;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseOrderChangeLog as PurchaseOrderChangeLogDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GatePurchaseOrderItemPackage as PurchaseOrderItemPackageDB;
use App\Models\GatePurchaseOrderItemSingle as PurchaseOrderItemSingleDB;
use App\Models\GatePurchaseSyncedLog as PurchaseOrderSyncedLogDB;
use App\Models\GateVendorShipping as VendorShippingDB;
use App\Models\GateVendorShippingItem as VendorShippingItemDB;
use App\Models\GateVendorShippingItemPackage as VendorShippingItemPackageDB;

use App\Traits\PurchaseOrderFunctionTrait;

class CreateShippingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,PurchaseOrderFunctionTrait;

    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $shipping = $shippingItems = [];
        $param = $this->param;
        $vendorId = $param['vendorId'];
        if($param['cate'] == 'CreateShipping' && count($param['selected']) > 0){
            $purchaseOrderItemIds = explode(',',join(',',$param['selected']));
            $data = PurchaseOrderItemDB::with('packages')->whereIn('id',$purchaseOrderItemIds)
            ->where('quantity','>',0)
            ->orderBy('vendor_arrival_date','asc')->get();
            $data = $data->groupBy('vendor_arrival_date')->all();
            $serialNo = date('Ymd').str_pad($vendorId,4,0,STR_PAD_LEFT);
            $tmp = VendorShippingDB::where('shipping_no','like',"%$serialNo%")->select('shipping_no')->orderBy('shipping_no','desc')->first();
            !empty($tmp) ? $shippingNo = $tmp->shipping_no : $shippingNo = date('Ymd').str_pad($vendorId,4,0,STR_PAD_LEFT).str_pad(0,3,0,STR_PAD_LEFT);
            foreach($data as $date => $tmps){
                $shippingNo++;
                $shipping = VendorShippingDB::create([
                    'vendor_id' => $vendorId,
                    'shipping_no' => $shippingNo,
                    'vendor_arrival_date' => $date,
                    'shipping_finish_date' => null,
                    'status' => 0,
                ]);
                $temps = $tmps->groupBy('direct_shipment')->all();
                foreach($temps as $directShipment => $items){
                    if($directShipment == 1){ //直寄
                        foreach($items as $item){
                            //改從syncedOrderItem中的同步資料裡面找出訂單
                            $orderIds = SyncedOrderItemDB::where('purchase_no',$item->purchase_no)->where('is_del',0)
                            ->where('vendor_arrival_date',$date)->select('order_id')->groupBy('order_id')->get()
                            ->pluck('order_id')->all();
                            // $purchaseOrder = PurchaseOrderDB::where('purchase_no',$item->purchase_no)->first();
                            // $orderIds = explode(',',$purchaseOrder->order_ids);
                            // $orderIds = array_unique($orderIds);
                            // $orders = OrderDB::with('itemData')->whereIn('id',$orderIds)->where('vendor_arrival_date',$date)->get();
                            $orders = OrderDB::with('itemData')->whereIn('id',$orderIds)->get();
                            $product = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                            ->where($productModelTable.'.id', $item->product_model_id)
                            ->select([
                                $productModelTable.'.*',
                                $productTable.'.name as product_name',
                            ])->first();
                            $sku = $product->sku;
                            $digiwinNo = $product->digiwin_no;
                            if(count($orders) > 0){
                                foreach($orders as $order){
                                    foreach($order->itemData as $orderItem){
                                        !empty($orderItem->syncedOrderItem) ? $vendorArrivalDate = $orderItem->syncedOrderItem->vendor_arrival_date : $vendorArrivalDate = $order->vendor_arrival_date;
                                        if($orderItem->direct_shipment == 1){
                                            $productModelId = $orderItem->product_model_id;
                                            $quantity = $orderItem->quantity;
                                            if(!empty($orderItem->origin_digiwin_no)){ //轉換貨號
                                                $tmp = ProductModelDB::where('digiwin_no',$orderItem->origin_digiwin_no)->first();
                                                $productModelId = $tmp->id;
                                                $orderItem->digiwin_no = $tmp->digiwin_no;
                                            }
                                            if($item->product_model_id == $productModelId){
                                                $shippingItem = VendorShippingItemDB::create([
                                                    'shipping_no' => $shippingNo,
                                                    'purchase_no' => $item->purchase_no,
                                                    'order_ids' => $order->id,
                                                    'ori_id' => $orderItem->id,
                                                    'order_numbers' => $order->order_number,
                                                    'product_model_id' => $productModelId,
                                                    'poi_id' => $item->id,
                                                    'product_name' => $product->product_name,
                                                    'sku' => $sku,
                                                    'digiwin_no' => $digiwinNo,
                                                    'gtin13' => $item->gtin13,
                                                    'quantity' => $quantity,
                                                    'direct_shipment' => $directShipment,
                                                    'vendor_arrival_date' => $vendorArrivalDate,
                                                    'created_at' => date('Y-m-d H:i:s')
                                                ]);
                                                if(strstr($sku, 'BOM')) {
                                                    foreach($orderItem->package as $oPackage){
                                                        $oProductModelId = $oPackage->product_model_id;
                                                        if(!empty($oPackage->origin_digiwin_no)){ //轉換貨號
                                                            $tmp2 = ProductModelDB::where('digiwin_no',$oPackage->origin_digiwin_no)->first();
                                                            $oPackage->digiwin_no = $tmp2->digiwin_no;
                                                            $oProductModelId = $tmp2->id;
                                                        };
                                                        //找出 poip_id
                                                        foreach($item->packages as $pPackage){
                                                            if($pPackage->product_model_id == $oProductModelId){
                                                                $poipId = $pPackage->id;
                                                                break;
                                                            }
                                                        }
                                                        $shippingItemPackage = VendorShippingItemPackageDB::create([
                                                            'vsi_id' => $shippingItem->id,
                                                            'poi_id' => $item->id,
                                                            'poip_id' => $poipId,
                                                            'product_model_id' => $oProductModelId,
                                                            'product_name' => $oPackage->product_name,
                                                            'digiwin_no' => $oPackage->digiwin_no,
                                                            'quantity' => $oPackage->quantity,
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $item->update(['vendor_shipping_no' => $shippingNo]);
                            }
                        }
                    }else{ //入倉
                        foreach($items as $item){
                            if($item->direct_shipment == 0){
                                $purchaseOrder = PurchaseOrderDB::where('purchase_no',$item->purchase_no)->first();
                                $orderIds = explode(',',$purchaseOrder->order_ids);
                                $orderIds = array_unique($orderIds);
                                $orderNumbers = join(',',OrderDB::whereIn('id',$orderIds)->select('order_number')->get()->pluck('order_number')->all());
                                $product = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                                ->where($productModelTable.'.id', $item->product_model_id)
                                ->select([
                                    $productModelTable.'.*',
                                    $productTable.'.name as product_name',
                                ])->first();
                                $shippingItem = VendorShippingItemDB::create([
                                    'shipping_no' => $shippingNo,
                                    'purchase_no' => $item->purchase_no,
                                    'order_ids' => join(',',$orderIds),
                                    'order_numbers' => $orderNumbers,
                                    'product_model_id' => $item->product_model_id,
                                    'poi_id' => $item->id,
                                    'ori_id' => null,
                                    'product_name' => $product->product_name,
                                    'sku' => $product->sku,
                                    'digiwin_no' => $product->digiwin_no,
                                    'gtin13' => $item->gtin13 ?? $product->gtin13,
                                    'quantity' => $item->quantity,
                                    'direct_shipment' => $directShipment,
                                    'vendor_arrival_date' => $item->vendor_arrival_date,
                                ]);
                                if(strstr($product->sku,'BOM')){
                                    foreach($item->packages as $package){
                                        $shippingItemPackage = VendorShippingItemPackageDB::create([
                                            'vsi_id' => $shippingItem->id,
                                            'poi_id' => $item->id,
                                            'poip_id' => $package->id,
                                            'product_model_id' => $package->product_model_id,
                                            'product_name' => $package->product_name,
                                            'digiwin_no' => $package->digiwin_no,
                                            'quantity' => $package->quantity,
                                        ]);
                                    }
                                }
                                $item->update(['vendor_shipping_no' => $shippingNo]);
                            }
                        }
                    }
                }
            }
        }
    }
}
