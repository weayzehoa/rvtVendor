<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\PurchaseOrderFunctionTrait;
use App\Models\GatePurchaseSyncedLog as PurchaseOrderSyncedLogDB;

class ConfirmOrderJob implements ShouldQueue
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
        $now = date('Y-m-d H:i:s');
        $orders = $this->getOrderData($this->param,'confirmOrder');
        if(count($orders) > 0){
            foreach($orders as $order){
                $purchaseOrderSyncedLogs = PurchaseOrderSyncedLogDB::where('purchase_order_id',$order->id)->whereNull('confirm_time')->orderBy('created_at','desc')->get();
                if(count($purchaseOrderSyncedLogs) > 0){
                    foreach($purchaseOrderSyncedLogs as $order){
                        $order->update(['confirm_time' => $now]);
                    }
                }
            }
        }
    }
}
