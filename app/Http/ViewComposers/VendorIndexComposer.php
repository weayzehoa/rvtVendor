<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Auth;

use App\Models\iCarryVendor as VendorDB;
use App\Models\GateMainmenu as MainmenuDB;
use App\Models\GatePowerAction as PowerActionDB;
use App\Models\GatePurchaseOrder as PurchaseOrderDB;
use App\Models\GatePurchaseSyncedLog as PurchaseSyncedLogDB;

class VendorIndexComposer
{
    public function compose(View $view){
        $purchaseOrderTable = env('DB_ERPGATE').'.'.(new PurchaseOrderDB)->getTable();
        $purchaseSyncedLogTable = env('DB_ERPGATE').'.'.(new PurchaseSyncedLogDB)->getTable();

        $mainmenus = MainmenuDB::with('submenu')->where(['type' => 2, 'is_on' => 1])->orderBy('sort','asc')->get();
        $poweractions = PowerActionDB::all();
       if(Auth::user()){
        $vendorId = Auth::user()->vendor_id;
        $vendor = VendorDB::find($vendorId);
        $unconfirmCount = PurchaseSyncedLogDB::join($purchaseOrderTable,$purchaseOrderTable.'.id',$purchaseSyncedLogTable.'.purchase_order_id')
        ->whereNull($purchaseSyncedLogTable.'.confirm_time')
        ->whereNotNull($purchaseSyncedLogTable.'.notice_time')
        ->where($purchaseOrderTable.'.status',1)
        ->where($purchaseOrderTable.'.created_at','>','2024-05-23 00:00:00')
        ->where($purchaseSyncedLogTable.'.vendor_id',$vendorId)
        ->groupBy($purchaseSyncedLogTable.'.purchase_order_id')
        ->orderBy($purchaseSyncedLogTable.'.created_at','desc')
        ->get()->count();
            $view->with('unconfirmCount', $unconfirmCount);
            $view->with('mainmenus', $mainmenus);
            $view->with('poweractions', $poweractions);
            $view->with('vendor', $vendor);
        }
    }
}
