<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\GateVendorShipping as ShippingDB;
use App\Models\GateVendorShippingItem as ShippingItemDB;
use App\Models\GateVendorShippingExpress as ExpressDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GateSpecialVendor as SpecialVendorDB;
use App\Models\GateSellImport as SellImportDB;
use App\Models\GateSellItemSingle as SellItemSingleDB;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;
use App\Models\GateSFShipping as SFShippingDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Jobs\ShippingExportJob;
use App\Jobs\ShippingImportJob;

use App\Traits\ShippingFunctionTrait;
use App\Traits\SFApiFunctionTrait;

use Carbon\Carbon;
use Session;
use DB;

class ShippingController extends Controller
{
    use ShippingFunctionTrait,SFApiFunctionTrait;

    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:vendor');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $waits = $compact = $appends = [];
        $shippingTable = env('DB_ERPGATE').'.'.(new ShippingDB)->getTable();
        $shippingItemTable = env('DB_ERPGATE').'.'.(new ShippingItemDB)->getTable();
        $vendorId = auth('vendor')->user()->vendor_id;
        request()->request->add(['vendorId' => $vendorId]);
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        $vendor = VendorDB::find($vendorId);
        $shippings = $this->getShippingData(request(),'index');
        foreach($shippings as $shipping){
            $chkExpress = $chkItem = $chkShipping = $chkStockin = 0;
            $stockinFinishDate = [];
            $chkDirectShip = $i = 0;
            foreach($shipping->items as $item){
                //直寄必須將入庫資料細分是否出貨
                if($item->direct_shipment == 1) {
                    $chkDirectShip++;
                    if(strstr($item->sku, 'BOM')) {
                        if(count($item->express) > 0){
                            foreach($item->packages as $package){
                                if(count($package->stockins) > 0){
                                    $sells = SellItemSingleDB::where('order_number',$item->order_numbers)->where('product_model_id',$package->product_model_id)->get();
                                    if(count($sells) != 0){
                                        foreach($package->stockins as $stockin){
                                            $stockinFinishDate[] = $stockin->stockin_date;
                                        }
                                        $item->stockins = [1]; //確認出貨將 $item->stockins 設定為有資料
                                    }
                                }
                            }
                        }
                    }else{
                        $sells = SellItemSingleDB::where('order_number',$item->order_numbers)->where('product_model_id',$item->product_model_id)->get();
                        if(count($sells) == 0){
                            $item->stockins = [];
                        }
                    }
                }else{
                    if(count($item->stockins) > 0){
                        foreach($item->stockins as $stockin){
                            $stockinFinishDate[] = $stockin->stockin_date;
                        }
                    }
                }
                $item->is_del == 0 ? $i++ : ''; //未被取消的item數量
                count($item->stockins) > 0 ? $chkStockin++ : ''; //有入庫的item數量
            }

            if(count($stockinFinishDate) > 0){
                if($shipping->status != 4 && $i > 0 && $i == $chkStockin){
                    $shipping->update(['status' => 4, 'stockin_finish_date' => max($stockinFinishDate)]);
                }
            }
            if(count($shipping->items) == $chkDirectShip){
                $shipping->noWarehourse = 1;
            }else{
                $shipping->noWarehourse = 0;
            }
            $shipping->use_sf = $vendor->use_sf;
        }

        $items = ShippingItemDB::join($shippingTable,$shippingTable.'.shipping_no',$shippingItemTable.'.shipping_no')
        ->where([
            [$shippingTable.'.vendor_id',$vendorId],[$shippingItemTable.'.is_del',0],[$shippingTable.'.status','<=',0],[$shippingTable.'.status','<=',2],
        ])->get();
        $items = $items->groupBy('vendor_arrival_date')->all();
        $i=0;
        foreach($items as $date => $temps){
            $count = 0;
            $temps = $temps->groupBy('shipping_no')->all();
            foreach($temps as $shippingNo => $tmp){
                $chk = 0;
                foreach($tmp as $t){
                    if($t->shipping_date == null){
                        $chk++;
                    }
                }
                if($chk > 0){
                    $count++;
                }
            }
            if($count > 0){
                $waits[$i]['vendor_arrival_date'] = $date;
                $waits[$i]['count'] = $count;
            }
            $i++;
        }
        $compact = array_merge($compact, ['shippings','list','appends','waits']);
        return view('vendor.order.shipping', compact($compact));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateMemo(Request $request)
    {
        if(!empty($request->id)){
            $shipping = ShippingDB::findOrFail($request->id);
            $shipping->update(['memo' => $request->memo]);
        }
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function cancel(Request $request)
    {
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();

        if($request->method == 'item'){
            $shippingItem = ShippingItemDB::find($request->id);
            $purchaseOrderItem = PurchaseOrderItemDB::with('stockins','packages','packages.stockins')
            ->join($productModelTable,$productModelTable.'.id',$purchaseOrderItemTable.'.product_model_id')
            ->select([
                $purchaseOrderItemTable.'.*',
                $productModelTable.'.sku',
            ])->find($shippingItem->poi_id);
            if($shippingItem->direct_shipment == 0){
                $chkStockin = 0;
                if(strstr($purchaseOrderItem->sku,'BOM')){
                    foreach($purchaseOrderItem->packages as $package){
                        count($package->stockins) > 0 ? $chkStockin++ : '';
                    }
                }else{
                    count($purchaseOrderItem->stockins) > 0 ? $chkStockin++ : '';
                }
                if($chkStockin == 0){
                    $purchaseOrderItem->update(['vendor_shipping_no' => null]);
                    $shippingItem->update(['is_del' => 1]);
                }
            }else{ //直寄
                $chkStockin = 0;
                $shippingItems = ShippingItemDB::with('stockins','packages','packages.stockins')->where([['purchase_no',$shippingItem->purchase_no],['product_model_id',$shippingItem->product_model_id],['direct_shipment',1],['is_del',0]])->get();
                foreach($shippingItems as $item){
                    if(strstr($item->sku,'BOM')){
                        foreach($item->packages as $package){
                            if(count($package->stockins) > 0){
                                $sells = SellItemSingleDB::where('order_number',$item->order_numbers)->where('product_model_id',$package->product_model_id)->get();
                                if(count($sells) > 0){
                                    $chkStockin++;
                                }
                            }
                        }
                    }else{
                        if(count($item->stockins) > 0){
                            $sells = SellItemSingleDB::where('order_number',$item->order_numbers)->where('product_model_id',$item->product_model_id)->get();
                            if(count($sells) > 0) {
                                $chkStockin++;
                            }
                        }
                    }
                }
                if($chkStockin == 0){ //取消全部並將採購標記移除
                    $purchaseOrderItem->update(['vendor_shipping_no' => null]);
                    foreach($shippingItems as $item){
                        $item->update(['is_del' => 1]);
                    }
                }else{ //只取消被選擇的這筆
                    $shippingItem->update(['is_del' => 1]);
                }
            }
            //檢查商家出貨單是否全部被取消, 若是則取消整張出貨單
            $vendorShipping = ShippingDB::with('items')->where('shipping_no',$shippingItem->shipping_no)->first();
            $chkVendorShipping = 0;
            foreach($vendorShipping->items as $vendorItem){
                $vendorItem->is_del == 1 ? $chkVendorShipping++ : '';
            }
            $chkVendorShipping == count($vendorShipping->items) ? $vendorShipping->update(['status' => -1, 'memo' => '已被iCarry系統取消。']) : '';
        }elseif($request->method == 'shipping'){
            $shipping = ShippingDB::with('items','items.packages')->find($request->id);
            if(!empty($shipping)){
                if($shipping->status == 4){
                    $message = "已完成入庫後無法取消";
                    Session::put('error',$message);
                }else{
                    //取消採購商品的出貨單註記
                    foreach($shipping->items as $item){
                        $purchaseOrderItem = PurchaseOrderItemDB::where('purchase_no',$item->purchase_no)->find($item->poi_id);
                        !empty($purchaseOrderItem) ?  $purchaseOrderItem->update(['vendor_shipping_no' => null]) : '';
                        if(strstr($item->sku,'BOM')){
                            foreach($item->packages as $package){
                                $package->update(['is_del' => 1]);
                            }
                        }
                        $item->update(['is_del' => 1]);
                    }
                    //取消整張出貨單
                    $shipping->update(['status' => -1, 'memo' => '商家取消。']);
                    $message = "出貨單 $shipping->shipping_no 取消成功。";
                    Session::put('success',$message);
                }
            }
        }
        return redirect()->back();
    }

    public function multiProcess(Request $request)
    {
        //將進來的資料作參數轉換及附加到$param中
        foreach ($request->all() as $key => $value) {
            $param[$key] = $value;
        }
        $method = null;
        $url = 'https://'.env('VENDOR_DOMAIN').'/exportCenter';
        $param['vendorId'] = auth('vendor')->user()->vendor_id;
        $param['vendor_account_id'] = auth('vendor')->user()->id;
        $param['vendor_account_name'] = auth('vendor')->user()->name;
        if(!empty($param['method'])){
            $param['method'] == 'selected' ? $method = '自行勾選' : '';
            $param['method'] == 'allOnPage' ? $method = '目前頁面全選' : '';
            $param['method'] == 'byQuery' ? $method = '依查詢條件' : '';
            $param['method'] == 'allData' ? $method = '全部資料' : '';
        }
        !empty($method) ? $param['name'] = $param['filename'].'_'.$method : $param['name'] = $param['filename'];
        $param['export_no'] = time();
        $param['start_time'] = date('Y-m-d H:i:s');
        $param['type'] = 'oneShipping' ? $param['name'] = $param['filename'] : '';
        if($param['cate'] == 'shippingExport'){ //同步鼎新
            if(strstr($param['filename'],'單筆出貨單')){
                $shippingData = ShippingDB::find($param['id'][0]);
                !empty($shippingData) ? $shippingNo = $shippingData->shipping_no : $shippingNo = null;
                $param['name'] = $param['name']."($shippingNo)";
            }
            $param['filename'] = $param['name'].'_'.time().'.zip';
            $message = '執行編號：'.$param['export_no'].' '.$param['name'].'，被選擇的出貨單已匯出入庫單(Zip)，<br>請使用貴司統編解壓縮檔案並列印，入庫管理表(PDF)填入有效日期並隨貨物提交給我司倉庫，<br>若有直寄訂單入庫管理表(Excel)請填入物流資訊後匯入。';
            $fileName = ShippingExportJob::dispatchNow($param);
            if(!empty($fileName)){
                return response()->download(storage_path('app/exports/').$fileName);
            }else{
                return redirect()->back();
            }
        }
        Session::put('info', $message);
        return redirect()->back();
    }

    public function fillData(Request $request)
    {
        $vendorId = auth('vendor')->user()->vendor_id;
        $expresses = [];
        $data = $request->data;
        sort($data);
        if($request->method == 'shipping'){ //出貨單
            $shipping = ShippingDB::with('items','nonDirectShip')->findOrFail($request->id);
            $shippingNo = $shipping->shipping_no;
            if(count($shipping->nonDirectShip) > 0){
                foreach($shipping->nonDirectShip as $item){
                    //檢查輸入的資料是否存在
                    if($this->chkData($data)){
                        $memo = null;
                        //建立新的資料
                        for($i=0;$i<count($data);$i++){
                            $oriShippingDate = $data[$i]['shipping_date'];
                            $shippingDate = $data[$i]['shipping_date'];
                            $shippingDate = str_replace(['-','/'],['',''],$shippingDate);
                            if ($this->convertAndValidateDate($shippingDate) == false) {
                                $shippingDate = null;
                                $item->direct_shipment == 0 ? $memo = "日期 $oriShippingDate 格式錯誤，請重新填寫。" : '';
                            }else{
                                $shippingDate = $this->convertAndValidateDate($shippingDate);
                            }
                            if(empty($memo) && !empty($shippingDate)){
                                //清除之前的紀錄
                                $express = ExpressDB::where('shipping_no',$item->shipping_no)->where('vsi_id',$item->id)->delete();
                                $expresses[] = [
                                    'shipping_no' => $item->shipping_no,
                                    'vsi_id' => $item->id,
                                    'poi_id' => $item->poi_id,
                                    'shipping_date' => $shippingDate,
                                    'express_way' => $data[$i]['express_way'],
                                    'express_no' => $data[$i]['express_no'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                ];
                                $shipping->update(['method' => 2]);
                            }
                        }
                    }
                }
            }
        }
        if($request->method == 'item'){ //單一單品
            $memo = null;
            $item = ShippingItemDB::with('sellImport')->findOrFail($request->id);
            $shipping = ShippingDB::with('items','nonDirectShip')->where('shipping_no',$item->shipping_no)->first();
            $shippingNo = $shipping->shipping_no;
            //檢查輸入的資料是否存在
            if($this->chkData($data)){
                //建立新的資料
                for($i=0;$i<count($data);$i++){
                    $oriShippingDate = $data[$i]['shipping_date'];
                    $shippingDate = $data[$i]['shipping_date'];
                    $shippingDate = str_replace(['-','/'],['',''],$shippingDate);
                    if ($this->convertAndValidateDate($shippingDate) == false) {
                        $shippingDate = null;
                        $item->direct_shipment == 0 ? $memo = "日期 $oriShippingDate 格式錯誤，請重新填寫。" : '';
                    }else{
                        $shippingDate = $this->convertAndValidateDate($shippingDate);
                    }
                    if(empty($memo) && !empty($shippingDate)){
                        //清除之前的紀錄
                        $express = ExpressDB::where('shipping_no',$item->shipping_no)->where('vsi_id',$item->id)->delete();
                        $expresses[] = [
                            'shipping_no' => $item->shipping_no,
                            'vsi_id' => $item->id,
                            'poi_id' => $item->poi_id,
                            'shipping_date' => $shippingDate,
                            'express_way' => $data[$i]['express_way'],
                            'express_no' => $data[$i]['express_no'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
                if($item->direct_shipment == 1){ //直寄
                    $memo = null;
                    $oriShippingDate = $data[0]['shipping_date'];
                    $shippingNumber = $data[0]['express_no'];
                    $shippingVendor = $data[0]['express_way'];
                    $sellDate =  $data[0]['shipping_date'];
                    if(!empty($sellDate)){
                        $sellDate = str_replace(['-','/'],['',''],$sellDate);
                        if ($this->convertAndValidateDate($sellDate) == false) {
                            $memo .= "出貨日期 $oriShippingDate 錯誤，請重新填寫。";
                            $sellDate = null;
                        }else{
                            $sellDate = $this->convertAndValidateDate($sellDate);
                        }
                    }else{
                        $memo .= "出貨日期未填寫。";
                    }
                    if(!empty($sellDate)){
                        //特殊廠商排除檢查日期
                        $spVendors = SpecialVendorDB::where('vendor_id',$vendorId)->orderBy('code','asc')->first();
                        if(empty($spVendors)){
                            $sd = strtotime($sellDate);
                            $vt = strtotime($item->vendor_arrival_date);
                            $befor5days = strtotime(Carbon::create(date('Y',$vt), date('m',$vt), date('d',$vt))->addDays(-5));
                            $after3days = strtotime(Carbon::create(date('Y',$vt), date('m',$vt), date('d',$vt))->addDays(3));
                            if($sd < $befor5days || $sd > $after3days){
                                $status = -1;
                                $memo .= "出貨日期範圍錯誤。";
                            }
                        }
                    }
                    if(!empty($sellDate)){
                        empty($shippingNumber) ? $memo .= "物流單號未填寫。" : '';
                        empty($shippingVendor) ? $memo .= "物流商未填寫。" : '';
                        !empty($shippingVendor) && !empty($shippingNumber) ? $shippingNumber = $shippingVendor.'_'.$shippingNumber : $shippingNumber = null;
                        !empty($memo) ? $status = -1 : $status = 0;
                        if(empty($memo)){
                            //找出sell_import是否有資料
                            if(empty($item->sellImport)){
                                $sellImport = SellImportDB::create([
                                    'import_no' => time(),
                                    'type' => 'directShip',
                                    'order_number' => $item->order_numbers,
                                    'shipping_number' => $shippingNumber,
                                    'gtin13' => null,
                                    'purchase_no' => $item->purchase_no,
                                    'digiwin_no' => $item->digiwin_no,
                                    'product_name' => $item->product_name,
                                    'quantity' => $item->quantity,
                                    'sell_date' => $sellDate,
                                    'stockin_time' => $item->vendor_arrival_date, //對應廠商到貨日給入庫用
                                    'status' => $status,
                                    'memo' => $memo,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'vsi_id' => $item->id,
                                ]);
                            }else{
                                $sellImport = $item->sellImport;
                                $sellImport->update([
                                    'sell_date' => $sellDate,
                                    'shipping_number' => $shippingNumber,
                                    'status' => $status,
                                    'memo' => $memo,
                                ]);
                            }
                            $item->update(['si_id' => $sellImport->id]);
                        }
                    }
                }
            }
        }
        !empty($memo) ? Session::put('error','填入資料有誤：'.$memo) : '';

        if(count($expresses) > 0){
            ExpressDB::insert($expresses);
        }

        //檢查出貨狀況
        $i = $chkShipping = 0;
        $items = $shipping->items;
        foreach($items as $item){
            $item->is_del == 0 ? $i++ : '';
            $express = ExpressDB::where('shipping_no',$item->shipping_no)->where('vsi_id',$item->id)->count();
            $express > 0 ? $chkShipping++ : '';
        }
        if($chkShipping == $i){
            $temp = ExpressDB::where('shipping_no',$shippingNo)->selectRaw("MAX(shipping_date) as shippingDate")->groupBy('shipping_no')->first();
            !empty($temp) ? $shippingDate = $temp->shippingDate : $shippingDate = null;
            $shipping->update(['status' => 3, 'shipping_finish_date' => $shippingDate]);
        }elseif($chkShipping > 0){
            $shipping->update(['status' => 2]);
        }

        return redirect()->back();
    }

    public function import(Request $request)
    {
        if($request->hasFile('filename')){
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            if(in_array($uploadedFileMimeType, $mimes)){
                //檔案不可以直接放入Job中使用dispatch去跑
                $result = ShippingImportJob::dispatchNow($request); //直接馬上處理
                if($result == 'rows error'){
                    $message = '檔案內資料欄數錯誤，請檢查檔案是否符合。';
                    Session::put('error', $message);
                }elseif($result == 'sheets error'){
                    $message = '檔案內資料的 Sheet 數超過 1 個，請檢查檔案資料是否只有 1 個 Sheet。';
                    Session::put('error', $message);
                }elseif($result == 'no data'){
                    $message = '檔案內資料未被儲存，請檢查所有資料是否正確或已經處理完成。';
                    Session::put('warning', $message);
                }elseif(is_array($result)){
                    if($result['error'] == 0){
                        $success = $result['success'];
                        $message = "資料已全部處理完成，共 $success 筆。";
                        Session::put('success', $message);
                    }else{
                        $success = $result['success'];
                        $error = $result['error'];
                        $message = "共完成 $success 筆，失敗 $error 筆，錯誤訊息如下：<br>";
                        for($i=0;$i<count($result['message']);$i++){
                            $message .= $result['message'][$i].'<br>';
                        }
                        rtrim($message,'<br>');
                        Session::put('error', $message);
                    }
                }
                return redirect()->back();
            } else{
                $message = '只接受 xls 或 xlsx 檔案格式';
                Session::put('error', $message);
                return redirect()->back();
            }
        }
        return redirect()->back();
    }

    protected function chkData($data)
    {
        $chk = 0;
        for($i=0;$i<count($data);$i++){
            if(!empty($data[$i]['shipping_date']) && !empty($data[$i]['express_way']) && !empty($data[$i]['express_no'])){
                $chk++;
            }
        }
        if($chk > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getMemo (Request $request)
    {
        if(!empty($request->id)){
            $shipping = ShippingDB::find($request->id);
            if(!empty($shipping)){
                return $shipping->memo;
            }
        }
        return null;
    }

    public function getSFnumber (Request $request)
    {
        $error = $success = null;
        if(isset($request->id) && isset($request->quantity) && $request->shipping_date){
            $shippingDate = $request->shipping_date;
            $shipping = ShippingDB::with('nonDirectShip','nonDirectShip.packages','nonDirectShip.express')->find($request->id);
            $vendor = VendorDB::find($shipping->vendor_id);
            if(!empty($vendor->tel)){
                if(!empty($vendor->factory_address)){
                    //檢查是否有寄送至icarry的商品
                    $count = count($shipping->nonDirectShip);
                    if($count > 0){
                        if($request->quantity > 0){
                            if($request->quantity < 25){
                                $getSuccess = 0;
                                $qty = (INT)$request->quantity;
                                $param['shipping'] = $shipping;
                                $param['vendor'] = $vendor;
                                //檢查是否有使用順豐運單
                                $chkSFShipping = SFShippingDB::where('vendor_shipping_no',$shipping->shipping_no)->count();
                                for($i=0;$i<$qty;$i++){
                                    $param['sno'] = $chkSFShipping+($i+1);
                                    //取號功能,儲存號碼
                                    $result = $this->getSFShippingNumber($param);
                                    if(!empty($result)){
                                        //儲存
                                        SFShippingDB::create([
                                            'vendor_shipping_no' => $shipping->shipping_no,
                                            'sno' => $param['sno'],
                                            'phone' => mb_substr($vendor->tel,0,20),
                                            'sf_express_no' => $result['sfWaybillNo'],
                                            'vendor_id' => $shipping->vendor_id,
                                            'vendor_arrival_date' => $shipping->vendor_arrival_date,
                                            'shipping_date' => $shippingDate,
                                            'invoice_url' =>  $result['invoiceUrl'],
                                            'label_url' =>  $result['labelUrl'],
                                            'status' => 9,
                                            'trace_address' => '順豐尚未取件。',
                                        ]);
                                        $getSuccess++;
                                    }
                                }
                                if($getSuccess > 0){
                                    //找出所有順豐運單資料
                                    $sfNumbers = SFShippingDB::where([['vendor_shipping_no',$shipping->shipping_no],['status',9]])->select('sf_express_no')->get()->pluck('sf_express_no')->all();
                                    //更新至出貨單
                                    foreach($shipping->items as $item) {
                                        if($item->direct_shipment == 0){
                                            //移除
                                            if(count($item->express) > 0){
                                                foreach($item->express as $express){
                                                    $express->delete();
                                                }
                                            }
                                            $shippingExpress = ExpressDB::create([
                                                'shipping_no' => $item->shipping_no,
                                                'vsi_id' => $item->id,
                                                'poi_id' => $item->poi_id,
                                                'shipping_date' => $shippingDate,
                                                'express_way' => '順豐-台灣',
                                                'express_no' => join(',',$sfNumbers),
                                                'created_at' => date('Y-m-d H:i:s'),
                                            ]);
                                        }
                                    }
                                    $shipping->update(['method' => 1, 'status' => 2]);
                                    $success = "取號成功，請至順豐運單管理，列印順豐出貨單，並貼於箱上。";
                                }else{
                                    $error = "取號失敗。";
                                }
                            }else{
                                $error = "取號數量超過25。";
                            }
                        }else{
                            $error = "取號數量必須大於1。";
                        }
                    }else{
                        $error = "沒有寄送至 iCarry 的商品，無法使用順豐運單取號功能。";
                    }
                }else{
                    $error = "商家工廠地址(收貨地址)不存在，請至商家資料管理頁面填寫工廠地址(收貨地址)。";
                }
            }else{
                $error = "商家電話不存在，請至商家資料管理頁面填寫商家電話。";
            }
        }
        !empty($error) ? Session::put('error',$error) : '';
        !empty($success) ? Session::put('success',$success) : '';
        return redirect()->back();
    }
}
