<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GateSellImport as SellImportDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GateSpecialVendor as SpecialVendorDB;
use App\Models\GateVendorShippingItem as ShippingItemDB;
use App\Models\GateVendorShippingExpress as ExpressDB;
use App\Imports\VendorDirectShipImport;
use App\Traits\GenerallyFunctionTrait;
use Carbon\Carbon;
use Excel;

class ShippingImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GenerallyFunctionTrait;

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
        $param = $this->param;
        $result = Excel::toArray(new VendorDirectShipImport, $param['filename']); //0代表第一個sheet
        if(count($result) == 1){
            if(count($result[0][0]) == 19){
                return $this->resultImport($result[0]);
            }else{
                return 'rows error';
            }
        }else{
            return 'sheets error';
        }
    }


    private function resultImport($data)
    {
        $param = $this->param;
        // $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        // $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        // $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        // $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $results = [];
        $results['success'] = $results['error'] = 0;
        $results['message'] = [];
        $vendorId = auth('vendor')->user()->vendor_id;
        $importNo = time();
        if(count($data) > 0){
            for($i=0;$i<count($data);$i++){
                $memo = null;
                $row = $i+5;
                if($this->chkData($data[$i]) == true){
                    $shippingData = $stockinTime = $purchaseNo = $digiwinNo = $gtin13 = $sellDate = $status = $memo = null;
                    $purchaseNo = $data[$i][0];
                    $vedorArrivalDate = $data[$i][1];
                    $stockinTime = null;
                    $digiwinNo = $this->strClean($data[$i][2]);
                    $productName = $data[$i][3];
                    $quantity = (INT)$data[$i][5];
                    $orderNumber = (INT)$data[$i][13];
                    $shippingVendor = $data[$i][16];
                    $shippingNumber = $data[$i][17];
                    $sellDate = $data[$i][18];
                    $sellDate == '' ? $sellDate = null : '';
                    //檢查採購單號
                    $purchase = PurchaseOrderDB::where('purchase_no',$purchaseNo)->first();
                    if(!empty($purchaseNo)){
                        if(!empty($purchase)){
                            if($purchase->status != 1){
                                $memo .= "iCarry採購單狀態錯誤。";
                            }
                        }else{
                            $memo .= "iCarry採購單不存在。";
                        }
                    }else{
                        $memo .= "採購單號不可為空值。";
                    }
                    //檢查廠商到貨日
                    if(!empty($vedorArrivalDate)){
                        $vedorArrivalDate = str_replace(['-','/'],['',''],$vedorArrivalDate);
                        if($this->convertAndValidateDate($vedorArrivalDate) == false){
                            $memo .= "廠商出貨日格式/資料錯誤。";
                        }else{
                            $vedorArrivalDate = $this->convertAndValidateDate($vedorArrivalDate);
                            $stockinTime = $vedorArrivalDate.' '.date('H:i:s');
                        }
                    }else{
                        $memo .= "廠商到貨日不可為空值。";
                    }
                    //檢查訂單號碼
                    if(!empty($orderNumber)){
                        $order = OrderDB::where('order_number',$orderNumber)->first();
                        if(!empty($order)){
                            if($order->status != 2){
                                $memo .= "訂單狀態錯誤。";
                            }
                        }else{
                            $memo .= "訂單不存在。";
                        }
                    }else{
                        $memo .= "訂單號碼不可為空值。";
                    }
                    //檢查鼎新或號
                    if(!empty($digiwinNo)){
                        $productModel = ProductModelDB::where('digiwin_no',$digiwinNo)->where('is_del',0)->first();
                        if(empty($productModel)){
                            $memo .= "商品不存在。";
                        }
                    }else{
                        $memo .= "貨號不可為空值。";
                    }
                    //檢查數量
                    if(!empty($quantity)){
                        if($quantity <= 0){
                            $memo .= "數量不可小於等於0。";
                        }
                    }else{
                        $memo .= "數量不可為空值。";
                    }
                    //檢查物流資料
                    empty($shippingVendor) ? $memo .= "物流商不可為空值。" : '';
                    empty($shippingNumber) ? $memo .= "物流單號不可為空值。" : '';
                    if(!empty($shippingVendor) && !empty($shippingNumber)){
                        $shippingData = $shippingVendor.'_'.$shippingNumber;
                    }
                    //檢查出貨日其
                    if(!empty($sellDate)){
                        if(!is_numeric($sellDate) || strlen($sellDate) != 8){
                            $memo .="出貨日期格式錯誤。(8個數字)";
                        }else{
                            if($this->convertAndValidateDate($sellDate) == false){
                                $memo .= "出貨日期 $sellDate 錯誤。";
                            }else{
                                $sellDate = $this->convertAndValidateDate($sellDate);
                            }
                            if(!empty($sellDate)){
                                //特殊廠商排除檢查日期
                                $spVendors = SpecialVendorDB::where('vendor_id',$vendorId)->orderBy('code','asc')->first();
                                if(empty($spVendors)){
                                    $sd = strtotime($sellDate);
                                    $vt = strtotime($order->vendor_arrival_date);
                                    $befor5days = strtotime(Carbon::create(date('Y',$vt), date('m',$vt), date('d',$vt))->addDays(-5));
                                    $after3days = strtotime(Carbon::create(date('Y',$vt), date('m',$vt), date('d',$vt))->addDays(3));
                                    if($sd < $befor5days || $sd > $after3days){
                                        $memo .= "出貨日期範圍錯誤。(前5後3)";
                                    }
                                }
                            }
                        }
                    }else{
                        $memo .= "出貨日期未填寫。";
                    }
                    //檢查是否已經有SellImport資料匯入
                    $item = ShippingItemDB::with('shipping','express','sellImport')->where([['purchase_no',$purchaseNo],['order_numbers',$orderNumber],['digiwin_no',$digiwinNo],['direct_shipment',1],['vendor_arrival_date',$vedorArrivalDate],['is_del',0]])->first();
                    $shipping = $item->shipping;
                    $sellImport = $item->sellImport;
                    $shippingNo = $item->shipping_no;
                    if(!empty($sellImport)){
                        if($sellImport->status == 1){
                            $memo .= "該資料iCarry已經處理完成，不能修改。";
                        }
                    }
                    if($shipping->status == 4){
                        $memo .= "出貨單已完成入庫，不能修改。";
                    }
                    if(empty($memo)){
                        //更新或新增sell import資料
                        if(empty($sellImport)){
                            SellImportDB::create([
                                'import_no' => $importNo,
                                'type' => 'directShip',
                                'order_number' => $orderNumber,
                                'shipping_number' => $shippingData,
                                'gtin13' => null,
                                'purchase_no' => $purchaseNo,
                                'digiwin_no' => $digiwinNo,
                                'product_name' => $item->product_name,
                                'quantity' => $quantity,
                                'sell_date' => $sellDate,
                                'stockin_time' => $stockinTime, //對應廠商到貨日給入庫用
                                'status' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }else{
                            $sellImport->update([
                                'sell_date' => $sellDate,
                                'shipping_number' => $shippingData,
                            ]);
                        }
                        //移除之前舊的物流資料
                        if(count($item->express) > 0){
                            foreach($item->express as $express){
                                $express->delete();
                            }
                        }
                        //重建新的物流資料
                        $express = ExpressDB::create([
                            'shipping_no' => $shippingNo,
                            'vsi_id' => $item->id,
                            'poi_id' => $item->poi_id,
                            'shipping_date' => $sellDate,
                            'express_way' => $shippingVendor,
                            'express_no' => $shippingNumber,
                        ]);
                        //檢查出貨狀況
                        $chkShipping = 0;
                        $items = $shipping->items;
                        foreach($items as $item){
                            $express = ExpressDB::where('shipping_no',$item->shipping_no)->where('vsi_id',$item->id)->count();
                            if($express > 0){
                                $chkShipping++;
                            }
                        }
                        if($chkShipping == count($items)){
                            $temp = ExpressDB::where('shipping_no',$shippingNo)->selectRaw("MAX(shipping_date) as shippingDate")->groupBy('shipping_no')->first();
                            !empty($temp) ? $shippingDate = $temp->shippingDate : $shippingDate = null;
                            $shipping->update(['status' => 3, 'shipping_finish_date' => $shippingDate]);
                        }elseif($chkShipping > 0){
                            $shipping->status <= 2 ? $shipping->update(['status' => 2]) : '';
                        }
                        $results['success']++;
                    }else{
                        $results['error']++;
                        $results['message'][] = "第 $row 行，$memo";
                    }
                }
            }
        }
        return $results;
    }

    private function strClean($str)
    {
        $text = str_replace([' ','	'],['',''],$str);
        return $text;
    }

    private function chkData($result)
    {
        $count = count($result);
        $chk = 0;
        for($i=0;$i<count($result);$i++){
            empty($result[$i]) ? $chk++ : '';
        }
        if($chk != count($result)){ //表示有資料
            return true;
        }else{ //表示全部空值
            return false;
        }
    }
}
