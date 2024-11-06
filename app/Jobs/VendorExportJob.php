<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\OrderExport;
use App\Exports\TicketExport;
use App\Exports\PurchaseOrderExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use File;
use PDF;
use App\Models\ErpPURTC as ErpPURTCDB;
use App\Models\ErpVendor as ErpVendorDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\GatePurchaseSyncedLog as PurchaseSyncedLogDB;
use App\Models\GatePurchaseOrderItemSingle as PurchaseOrderItemSingleDB;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseOrderItem as PurchaseOrderItemDB;

use App\Models\ExportCenter as ExportCenterDB;
use App\Traits\PurchaseOrderFunctionTrait;

class VendorExportJob implements ShouldQueue
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
        $param = $this->param;
        //目的目錄
        $destPath = storage_path('app/exports/');
        //檢查本地目錄是否存在，不存在則建立
        // dd(file_exists($destPath));
        !file_exists($destPath) ? File::makeDirectory($destPath, 0755, true) : '';

        //找出採購單資料, 包含商品資料
        $orders = $this->getOrderData($this->param,'export');
        // dd($data);
        if(count($orders) > 0){
            $purchaseOrderIds = $purchaseNos = [];
            foreach ($orders as $order) {
                $purchaseOrderIds[] = $order->id;
                $i = $c = 1;
                $purchasePrice = $totalQty = $totalPrice = $totalTax = $stockin = $directShipment = 0;
                $productModelIds = $directShipProducts = [];
                $tmp = ErpPURTCDB::where('TC001',$order->type)->find($order->erp_purchase_no);

                foreach ($order->exportItems as $item) {
                    if($order->type != 'A332'){ //鼎新匯入的採購單已經算過稅額所以不再計算
                        //應稅內含與不計稅商家不扣掉稅
                        $tmp->TC018 != 1 && $tmp->TC018 != 9 ? $item->purchase_price = $item->purchase_price /1.05 : '';
                    }
                    $totalQty += $item->quantity;
                    $purchasePrice += $item->quantity * $item->purchase_price;
                    if ($item->direct_shipment == 1) {
                        $directShipment++;
                        $productModelIds[] = $item->product_model_id;
                    } else {
                        $stockin++;
                    }
                    if (strstr($item->sku, 'BOM')) {
                        foreach ($item->exportPackage as $package) {
                            $package->snoForStockin = str_pad($c, 4, '0', STR_PAD_LEFT);
                            $c++;
                        }
                    } else {
                        $item->snoForStockin = str_pad($c, 4, '0', STR_PAD_LEFT);
                        $c++;
                    }
                    $item->sno = str_pad($i, 4, '0', STR_PAD_LEFT);
                    $i++;
                }
                //課稅類別
                if ($tmp->TC018 == 1) {
                    $purchasePrice = $purchasePrice / 1.05;
                    $order->taxType = '應稅內含';
                } elseif ($tmp->TC018 == 2) {
                    $order->taxType = '應稅外加';
                } elseif ($tmp->TC018 == 3) {
                    $order->taxType = '零稅率';
                } elseif ($tmp->TC018 == 4) {
                    $order->taxType = '免稅';
                } elseif ($tmp->TC018 == 9) {
                    $order->taxType = '不計稅';
                } else {
                    $order->taxType = null;
                }
                $tmp = ErpVendorDB::find('A'.str_pad($order->vendor_id, 5, '0', STR_PAD_LEFT));
                $order->payCondition = $tmp->MA025;
                $order->purchasePrice = $purchasePrice;
                $purchaseNos[] = $order->purchase_no;
                $order->totalQty = $totalQty;
            }
            // 訂單採購單存PDF
            $title = 'iCarry訂單採購單';
            // dd($param);
            $fileName = date('Y-m-d').'_iCarry訂單採購單_'.$param['export_no'].'.pdf';
            $viewFile = 'vendor.order.pdf_view_purchase_order';
            $pdf = PDF::loadView($viewFile, compact('orders', 'title'));
            $pdf = $pdf->setPaper('A4', 'landscape')->setOptions(['defaultFont' => 'TaipeiSansTCBeta-Regular']);
            // $pdf->save($destPath.$fileName);
            return $pdf->download($fileName);
        }
    }
}
