<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderVendorShipping as OrderVendorShippingDB;
use App\Models\Product as ProductDB;
use App\Models\ProductModel as ProductModelDB;
use Auth;
use View;
use Storage;
use DB;
use Session;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     * 進到這個控制器需要透過middleware檢驗是否為後台的使用者
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:vendor');
    }
    /**
     * 顯示 dashboard.
     * 並將 使用者的資料拋出
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        return View::make('vendor.dashboard');
    }
}
