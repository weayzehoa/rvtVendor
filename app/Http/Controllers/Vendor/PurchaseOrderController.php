<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GateAdmin as AdminDB;
use App\Models\GatePurchaseOrderChangeLog as PurchaseOrderChangeLogDB;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Traits\PurchaseOrderFunctionTrait;
use App\Jobs\ConfirmOrderJob;
use App\Jobs\VendorExportJob;
use App\Jobs\CreateShippingJob;
use Session;
use DB;

class PurchaseOrderController extends Controller
{
    use PurchaseOrderFunctionTrait;

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
        $compact = $appends = [];
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

        $orders = $this->getOrderData(request(),'index');

        foreach($orders as $purchase){
            foreach($purchase->items as $item){
               $item->stockinQty = 0;
                if(strstr($item->sku,'BOM')){
                    foreach($item->packages as $package){
                        if(count($package->returns) > 0){
                            foreach($package->returns as $return){
                                $package->returnQty += $return->quantity;
                            }
                        }
                        if(count($package->stockins) > 0){
                            $package->stockinQty;
                            foreach($package->stockins as $stockin){
                                $package->stockinQty += $stockin->stockin_quantity;
                            }
                        }
                    }
                }else{
                    if(count($item->stockins) > 0){
                        foreach($item->stockins as $stockin){
                            $item->stockinQty += $stockin->stockin_quantity;
                        }
                    }
                    foreach($item->returns as $return){
                        $item->returnQty += $return->quantity;
                    }
                }
            }
        }
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();

        $shippingDates = PurchaseOrderItemDB::join($purchaseOrderTable,$purchaseOrderTable.'.purchase_no',$purchaseOrderItemTable.'.purchase_no')
        ->where($purchaseOrderTable.'.vendor_id',$vendorId)
        ->whereNull($purchaseOrderItemTable.'.vendor_shipping_no')
        ->where($purchaseOrderItemTable.'.is_del',0)
        ->where($purchaseOrderItemTable.'.quantity','!=',0)
        ->whereIn($purchaseOrderTable.'.status',[1]);

        env('APP_ENV') == 'local' ? '' : $shippingDates = $shippingDates->where($purchaseOrderTable.'.created_at','>','2024-05-23 00:00:00');

        $shippingDates = $shippingDates->select([
            $purchaseOrderItemTable.'.vendor_arrival_date',
            DB::raw("GROUP_CONCAT($purchaseOrderTable.id) as purchaseOrderIds"),
            // DB::raw("DATE_FORMAT($purchaseOrderItemTable.vendor_arrival_date,'%Y-%m-%d') as vendor_arrival_date"),
            // DB::raw("SUM(CASE WHEN vendor_arrival_date is not null THEN 1 ELSE 0 END) as count"),
            // DB::raw("count($purchaseOrderItemTable.id) as count"),
        ])->distinct()->groupBy('vendor_arrival_date')->orderBy('vendor_arrival_date','asc')->get();
        foreach($shippingDates as $shippingDate){
            $purchaseOrderIds = explode(',',$shippingDate->purchaseOrderIds);
            $purchaseOrderIds = array_unique($purchaseOrderIds);
            $shippingDate->count = count($purchaseOrderIds);
        }
        $compact = array_merge($compact, ['orders','shippingDates','list','appends']);
        return view('vendor.order.index', compact($compact));
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

    public function getChangeLog(Request $request)
    {
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $purchaseOrderChangeLogTable = env('DB_ERPGATE').'.'.(new PurchaseOrderChangeLogDB)->getTable();
        if(!empty($request->purchase_no) && is_numeric($request->purchase_no)){
            $purchaseNo = $request->purchase_no;
            $logs = PurchaseOrderChangeLogDB::where('purchase_no',$purchaseNo)
                ->select([
                    '*',
                    DB::raw("DATE_FORMAT(created_at,'%Y/%m/%d %H:%i:%s') as modify_time"),
                    'admin_name' => AdminDB::whereColumn($adminTable.'.id',$purchaseOrderChangeLogTable.'.admin_id')->select($adminTable.'.name')->limit(1),
                ])->orderBy('id','desc')->get();
            return response()->json($logs);
        }
        return null;
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
        $param['cate'] == 'ExportOrder' || $param['type'] == 'pdf' ? $param['filename'] = $param['name'].'_'.time().'.pdf' : $param['filename'] = $param['name'].'_'.time().'.xlsx';
        // $message = $param['name'].'，工作單號：'.$param['export_no'].'<br>匯出已於背端執行，請過一段時間至匯出中心下載，<br>檔案名稱：'.$param['filename'].'<br>匯出中心連結：<a href="'.$url.'" target="_blank"><span class="text-danger">'.$url.'</span></a>';
        if($param['cate'] == 'confirmOrder'){ //同步鼎新
            $message = '執行編號：'.$param['export_no'].' '.$param['name'].'，被選擇的iCarry採購單已確認。';
            ConfirmOrderJob::dispatchNow($param);
        }elseif($param['cate'] == 'CreateShipping'){ //建立出貨單
            if(isset($param['selected'])){
                $message = '執行編號：'.$param['export_no'].' '.$param['name'].'，被選擇的iCarry採購單已建立出貨單。';
                CreateShippingJob::dispatchNow($param);
            }else{
                Session::put('error', '未選擇商品，無法建立出貨單。');
                return redirect()->back();
            }
        }elseif($param['cate'] == 'ExportOrder'){ //匯出採購單
            return VendorExportJob::dispatchNow($param); //直接馬上下載則必須使用 return
        }
        Session::put('info', $message);
        return redirect()->back();
    }

    public function getUnShipping(Request $request)
    {
        $data['orderIds'] = $data['items'] = [];
        if(!empty($request->condition)){
            $condition = $request->condition;
            for($i=0;$i<count($condition);$i++){
                $con[$condition[$i]['name']] = $condition[$i]['value'];
            }
            $request->request->add(['con' => $con]);
        }
        //找出未建立出貨單的採購單資料及ID
        $orderIds = $this->getOrderData($request,'getUnShipping');

        //抓商品資料
        if(count($orderIds) > 0) {
            $items = $this->getUnShippingPurchaseOrderItemData($orderIds);
            if(count($items) > 0){
                foreach($items as $item){
                    $purchaseNos = explode(',',$item->purchaseNos);
                    if(count($purchaseNos) > 1){
                        for($i=0;$i<count($purchaseNos);$i++){
                            $purchaseItem = PurchaseOrderItemDB::where([['purchase_no',$purchaseNos[$i]],['product_model_id',$item->product_model_id]])->first();
                            if(!empty($purchaseItem) && $purchaseItem->quantity == 0){
                                unset($purchaseNos[$i]);
                            }
                        }
                        sort($purchaseNos);
                    }
                    $item->purchaseNos = join(',',$purchaseNos);
                }
                $data['items'] = $items;
            }
            $data['orderIds'] = $orderIds;
        }
        return response()->json($data);
    }
}
