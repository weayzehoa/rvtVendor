<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Laravel 8.x 需將所有Controller列出於function中
use App\Http\Controllers\Vendor\VendorLoginController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\AccountController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\ProductImageController;
use App\Http\Controllers\Vendor\ProductPackageController;
use App\Http\Controllers\Vendor\CurationController;
use App\Http\Controllers\Vendor\CurationProductController;
use App\Http\Controllers\Vendor\CopyProductController;
use App\Http\Controllers\Vendor\PurchaseOrderController;
use App\Http\Controllers\Vendor\ShippingController;
use App\Http\Controllers\Vendor\SFShippingController;

//iCarry 後台 用的路由 網址看起來就像 https://vendor.localhost/{名稱}
//使用多個網域時須使用name()來將路由區分開, 不然會被後面的網域覆蓋掉.
Route::name('vendor.')->group(function() {
    Route::get('login', [VendorLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [VendorLoginController::class, 'login'])->name('login.submit');
    Route::get('logout', [VendorLoginController::class, 'logout'])->name('logout');
    Route::get('forget', [VendorLoginController::class, 'forgetForm'])->name('forget');
    Route::post('forget', [VendorLoginController::class, 'forget'])->name('forget.submit');
    Route::get('reset', [VendorLoginController::class, 'resetForm'])->name('reset');
    Route::post('reset', [VendorLoginController::class, 'reset'])->name('reset.submit');
    Route::get('', [VendorLoginController::class, 'showLoginForm']);
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    //上傳圖檔先優化
    Route::middleware('optimizeImages')->group(function () {
        Route::post('profile/upload/{id}', [ProfileController::class, 'upload'])->name('profile.upload');
        Route::post('productimages/upload/{id}', [ProductImageController::class, 'upload'])->name('productimages.upload');
        Route::post('product/upload', [ProductController::class, 'upload'])->name('product.upload');
    });
    //商家資料
    Route::resource('profile', ProfileController::class);
    //變更密碼
    Route::get('account/changePassWord', [AccountController::class, 'changePassWordForm'])->name('account.changePassWordForm');
    Route::post('account/changePassWord', [AccountController::class, 'changePassWord'])->name('account.changePassWord');
    //帳號管理
    Route::post('account/active/{id}', [AccountController::class, 'active']); //啟用
    // Route::post('account/changePassWord/{id}', [AccountController::class, 'changePassWord'])->name('account.changePassWord'); //變更密碼
    // Route::post('account/updateAccount/{id}', [AccountController::class, 'updateAccount'])->name('account.updateAccount'); //修改
    // Route::get('account/getAccount/{id}',[AccountController::class, 'getAccount']);
    Route::resource('account', AccountController::class);
    //商品管理
    Route::post('product/getHistory', [ProductController::class, 'getHistory'])->name('product.getHistory');
    Route::post('product/getGtin13History', [ProductController::class, 'getGtin13History'])->name('product.getGtin13History');
    Route::get('product/viewOnly/{id}', [ProductController::class, 'viewOnly'])->name('product.viewOnly');
    Route::get('product/copy/{id}', [ProductController::class, 'copy'])->name('product.copy');
    Route::post('product/changeStatus/{id}', [ProductController::class, 'changeStatus'])->name('product.changeStatus');
    Route::post('product/lang/{vendor_id}', [ProductController::class, 'lang'])->name('product.lang');
    Route::post('product/getSubCate', [ProductController::class, 'getSubCate'])->name('product.getSubCate');
    Route::post('product/deloldimage', [ProductController::class, 'deloldimage'])->name('products.deloldimage');
    Route::post('product/getlist', [ProductController::class, 'getList'])->name('product.getlist');
    Route::post('product/delmodel', [ProductController::class, 'delModel'])->name('product.delmodel');
    Route::post('product/delpackage', [ProductController::class, 'delPackage'])->name('product.delpackage');
    Route::post('product/dellist', [ProductController::class, 'delList'])->name('product.dellist');
    Route::post('product/getstockrecord', [ProductController::class, 'getStockRecord'])->name('product.getstockrecord');
    Route::post('product/stockmodify', [ProductController::class, 'stockModify'])->name('product.stockmodify');
    Route::resource('product', ProductController::class);
    //複製商品
    Route::post('copy/getSubCate', [ProductController::class, 'getSubCate'])->name('copy.getSubCate');
    Route::post('copy/getlist', [ProductController::class, 'getList'])->name('getlist');
    Route::resource('copy', CopyProductController::class);
    //商品照片
    Route::get('productimages/sortup/{id}',[ProductImageController::class, 'sortup']);
    Route::get('productimages/sortdown/{id}',[ProductImageController::class, 'sortdown']);
    Route::post('productimages/active/{id}', [ProductImageController::class, 'active']);
    Route::post('productimages/top/{id}', [ProductImageController::class, 'top'])->name('productimages.top');
    Route::resource('productimages', ProductImageController::class);
    //組合商品
    Route::resource('package', ProductPackageController::class);
    //iCarry訂單管理
    Route::post('icarryOrder/getUnShipping', [PurchaseOrderController::class, 'getUnShipping'])->name('icarryOrder.getUnShipping');
    Route::post('icarryOrder/multiProcess', [PurchaseOrderController::class, 'multiProcess'])->name('icarryOrder.multiProcess');
    Route::post('icarryOrder/getChangeLog', [PurchaseOrderController::class, 'getChangeLog'])->name('icarryOrder.getChangeLog');
    Route::resource('icarryOrder', PurchaseOrderController::class);
    //促銷策展
    Route::get('curation/sortup/{id}',[CurationController::class, 'sortup'])->name('curation.sortup');
    Route::get('curation/sortdown/{id}',[CurationController::class, 'sortdown'])->name('curation.sortdown');
    Route::post('curation/active/{id}', [CurationController::class, 'active']);
    Route::post('curation/getproducts', [CurationController::class, 'getProducts']);
    Route::resource('curation', CurationController::class);
    //促銷策展-商品策展
    Route::get('curationProduct/sortup/{id}',[CurationProductController::class, 'sortup']);
    Route::get('curationProduct/sortdown/{id}',[CurationProductController::class, 'sortdown']);
    Route::post('curationProduct/sort',[CurationProductController::class, 'sort'])->name('curationProduct.sort');
    Route::resource('curationProduct', CurationProductController::class);
    //出貨單管理
    Route::post('shipping/getSFnumber',[ShippingController::class, 'getSFnumber'])->name('shipping.getSFnumber');
    Route::post('shipping/updateMemo',[ShippingController::class, 'updateMemo'])->name('shipping.updateMemo');
    Route::post('shipping/getMemo',[ShippingController::class, 'getMemo'])->name('shipping.getMemo');
    Route::post('shipping/import',[ShippingController::class, 'import'])->name('shipping.import');
    Route::post('shipping/fillData',[ShippingController::class, 'fillData'])->name('shipping.fillData');
    Route::post('shipping/multiProcess',[ShippingController::class, 'multiProcess'])->name('shipping.multiProcess');
    Route::post('shipping/cancel',[ShippingController::class, 'cancel'])->name('shipping.cancel');
    Route::resource('shipping', ShippingController::class);
    //順豐運單管理
    Route::post('sfShipping/getStatus',[SFShippingController::class, 'getStatus'])->name('sfShipping.cancel');
    Route::resource('sfShipping', SFShippingController::class);
});
