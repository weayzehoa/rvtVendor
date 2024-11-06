<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\iCarryVendor as VendorDB;

use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\ErpPURTC as ErpPURTCDB;
use App\Models\ErpVendor as ErpVendorDB;

use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;
use App\Models\GatePurchaseOrderItemSingle as PurchaseOrderItemSingleDB;
use App\Models\GateSyncedOrderItem as SyncedOrderItemDB;

use App\Exports\Sheets\VendorDirectShipStockinSheet;

use App\Traits\ShippingFunctionTrait;

use DB;
use PDF;
use File;
use Excel;
use ZipArchive;

class ShippingExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ShippingFunctionTrait;

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
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $purchaseOrderItemSingleTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemSingleDB)->getTable();
        $purchaseOrderItemTable = env('DB_ERPGATE').'.'.(new PurchaseOrderItemDB)->getTable();
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $x = 0;
        $files = [];
        $vendorId = $param['vendorId'];
        $vendor = VendorDB::find($vendorId);
        $vendorVATNo = $vendor->VAT_number;
        $vendorName = $vendor->name;
        $vendorCode = 'A'.str_pad($vendorId,5,'0',STR_PAD_LEFT);
        //目的目錄
        $destPath = storage_path('app/exports/');
        //檢查本地目錄是否存在，不存在則建立
        !File::isDirectory($destPath) ? File::makeDirectory($destPath, 0755, true) : '';
        //找出採購單資料, 包含商品資料
        $shippings = $this->getShippingData($this->param,'export');

        foreach($shippings as $shipping){
            $vendorArrivalDate = $shipping->vendor_arrival_date;
            $poiIds = $purchaseNos = $purchaseOrderIds = [];
            foreach($shipping->items as $item){
                if($item->is_del==0){
                    $orderNumbers[] = $item->order_numbers;
                    $purchaseNos[] = $item->purchase_no;
                    $poiIds[] = $item->poi_id;
                    $temp = PurchaseOrderDB::where('purchase_no',$item->purchase_no)->first();
                    $item->direct_shipment == 1 && !empty($temp) ? $purchaseOrderIds[] = $temp->id : '';
                }
            }
            $poiIds = array_unique($poiIds);
            $purchaseNos = array_unique($purchaseNos);
            $purchaseOrderIds = array_unique($purchaseOrderIds);
            sort($poiIds);
            sort($purchaseNos);
            sort($purchaseOrderIds);
            // 訂單入庫單存PDF
            $purchaseItems = PurchaseOrderItemSingleDB::join($productModelTable,$productModelTable.'.id',$purchaseOrderItemSingleTable.'.product_model_id')
                ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                ->whereIn($purchaseOrderItemSingleTable.'.purchase_no',$purchaseNos)
                ->whereIn($purchaseOrderItemSingleTable.'.poi_id',$poiIds)
                ->where($purchaseOrderItemSingleTable.'.is_del',0)
                ->where($purchaseOrderItemSingleTable.'.quantity','>',0)
                ->where(function($query)use($purchaseOrderItemSingleTable){
                    $query->where($purchaseOrderItemSingleTable.'.direct_shipment',0)
                    ->orWhereNull($purchaseOrderItemSingleTable.'.direct_shipment');
                })//排除直寄
                ->select([
                    $purchaseOrderItemSingleTable.'.*',
                    $productModelTable.'.sku',
                    $productModelTable.'.gtin13',
                    $productModelTable.'.digiwin_no',
                    // $productTable.'.name as product_name',
                    DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                    $productTable.'.serving_size',
                    $vendorTable.'.name as vendor_name',
                    DB::raw("GROUP_CONCAT($purchaseOrderItemSingleTable.purchase_no) as purchaseNos"),
                    DB::raw("SUM($purchaseOrderItemSingleTable.quantity) as purchase_quantity"),
                ])->groupBy($purchaseOrderItemSingleTable.'.vendor_arrival_date',$purchaseOrderItemSingleTable.'.product_model_id')->get();
            $purchaseItems = $purchaseItems->groupBy('vendor_arrival_date')->all();
            if(count($purchaseItems) > 0){
                foreach($purchaseItems as $date => $items){
                    $viewFile = 'vendor.order.pdf_view_purchase_stockin';
                    $title = '入庫管理表';
                    $files[$x][] = $fileName2 = $date.'_'.$title.($x+1).'_'.$param['export_no'].'.pdf';
                    $c = 1;
                    $pNoString = null;
                    foreach($items as $item){
                        $pNoString .= $item->purchaseNos.',';
                        $pNos = explode(',',rtrim($pNoString,','));
                        $item->snoForStockin = str_pad($c,4,'0',STR_PAD_LEFT);
                        $c++;
                    }
                    $pNos = array_unique($pNos);
                    sort($pNos);
                    $pdf = PDF::loadView($viewFile, compact('items', 'pNos','title'));
                    $pdf = $pdf->setPaper('A4', 'landscape')->setOptions(['defaultFont' => 'TaipeiSansTCBeta-Regular']);
                    $pdf->save($destPath.$fileName2);
                }
            }
            if(count($shipping->directShip) > 0){
                // 訂單廠商直寄存excel
                $param['title'] = '直寄訂單入庫管理表_(應到貨日'.$vendorArrivalDate.')';
                $param['shipping'] = $shipping;
                $files[$x][] = $fileName3 = date('Y-m-d').'_'.$param['title'].($x+1).'_'.$param['export_no'].'.xlsx';
                Excel::store(new vendorDirectShipStockinSheet($param), $fileName3, 'export');
            }
            if(count($purchaseItems) > 0 || count($shipping->directShip) > 0){
                $shipping->status == 0 ? $shipping->update(['status' => 1]) : '';
            }
            $x++;
        }
        $file = $param['filename'];
        env('APP_ENV') == 'local' ? $password = '123456' : $password = date('Ymd').$vendorVATNo; // 設定壓縮檔案的密碼
        if(count($files) > 0){

            $zip = new ZipArchive();
            if ($zip->open($destPath.$file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                for($i=0; $i<count($files);$i++) {
                    for($j=0; $j<count($files[$i]); $j++) {
                        if(file_exists($destPath . $files[$i][$j])){
                            // windows 10/11 不支援 EM_AES_256 加密方式會導致加密失效 須改用 ZIPCrypto 加密算法 EM_TRAD_PKWARE,
                            // 但 ZIPCrypto 在 php7.4 是無法使用的 只能使用 EM_AES_256
                            env('APP_ENV') == 'local' ? '' : $zip->setEncryptionName(basename($files[$i][$j]), ZipArchive::EM_AES_256, $password);
                            $zip->addFile($destPath . $files[$i][$j], basename($files[$i][$j]));
                        }
                    }
                }
                $zip->close();
            }
            //刪除檔案
            for($xx = 0; $xx<count($files); $xx++){
                for($y=0; $y<count($files[$xx]);$y++){
                    unlink($destPath . $files[$xx][$y]);
                }
            }
            return $file;
        }
        return null;
    }
}
