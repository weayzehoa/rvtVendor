<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarrySubCategory as SubCategoryDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProductUpdateRecord as ProductUpdateRecordDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Models\GateAdmin as AdminDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryLangProductEn as ProductEnDB;
use App\Models\iCarryLangProductJp as ProductJpDB;
use App\Models\iCarryLangProductKr as ProductKrDB;
use App\Models\iCarryLangProductTh as ProductThDB;
use App\Http\Requests\ProductsRequest;
use App\Http\Requests\ProductsStoreRequest;
use App\Http\Requests\ProductsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use DB;
use Validator;
use App\Jobs\SendEmailJob;
use App\Traits\ProductFunctionTrait;

class ProductController extends Controller
{
    use ProductFunctionTrait;

    protected $vendorId;
    protected $viewOnly;

    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:vendor');
        $this->langRules = [
            'product_id' => 'required',
            'name' => 'required|max:64',
            'lang' => 'required|max:5',
            'brand' => 'required|max:64',
            'serving_size' => 'required|max:255',
            'title' => 'required|max:64',
            'intro' => 'required|max:500',
            'model_name' => 'nullable|max:32',
            'specification' => 'required|max:5000',
            'unable_buy' => 'nullable|max:100',
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appends = [];
        $compact = [];
        // dd(request());
        $products = $this->getProductData(request(),'index');
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
        foreach($products as $product){
            $product->categories = CategoryDB::whereIn('id',explode(',',$product->category_id))->where('is_on',1)
            ->select(DB::raw("GROUP_CONCAT(name) as name"))->first()->name;
        }
        $compact = array_merge($compact, ['products','list','appends']);
        return view('vendor.product.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewOnly = 0;
        $this->vendorId = auth('vendor')->user()->vendor_id;
        $vendor = VendorDB::findOrFail($this->vendorId);
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        $shippingMethods = ShippingMethodDB::all();
        $categories = CategoryDB::where([['id',explode(',',$vendor->categories)],['is_on',1]])->get();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get();
        $unitNames = ProductUnitNameDB::all();
        return view('vendor.product.show',compact('vendor','langs','shippingMethods','categories','unitNames','countries','viewOnly'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductsStoreRequest $request)
    {
        foreach($request->all() as $key => $value){
            $data[$key] = !empty($value) && !is_array($value) ? $this->removeEmoji($value) : $value;
        }
        isset($data['category_id']) ? $data['category_id'] = join(',',$data['category_id']) : $data['category_id'] = null;
        isset($data['sub_categories']) ? $data['sub_categories'] = join(',',$data['sub_categories']) : $data['sub_categories'] = null;
        isset($data['name']) ? $data['name'] = str_replace('_','-',$data['name']) : '';
        if(!empty($data['status'])){
            $data['status'] == '存為草稿' ? $data['status'] = -1 : '';
            $data['status'] == '送審' ? $data['status'] = 0 : '';
        }else{
            return redirect()->back()->withInput($request->all());
        }
        if ($data['model_type'] == 2) {
            if(!isset($data['model_data'])){
                $message = '您是不是忘記填寫款式資料??';
                Session::put('error', $message);
                return redirect()->back()->withInput($request->all());
            }
        }elseif ($data['model_type'] == 3) {
            if(!isset($data['packageData'])){
                $message = '您是不是忘記填寫組合商品資料??';
                Session::put('error', $message);
                return redirect()->back()->withInput($request->all());
            }
        }
        $data['TMS_price'] ?? $data['TMS_price'] = 0;
        $data['vendor_price'] ?? $data['vendor_price'] = 0;
        $data['airplane_days'] ?? $data['airplane_days'] = 0;
        $data['hotel_days'] ?? $data['hotel_days'] = 0;
        $data['is_hot'] ?? $data['is_hot'] = 0;
        $data['storage_life'] ?? $data['storage_life'] = 0;
        isset($data['shipping_methods']) ? $data['shipping_methods'] = join(',',$data['shipping_methods']) : '';
        if(isset($data['allow_country_ids'])){
            $data['allow_country'] = join(',',CountryDB::whereIn('id',$data['allow_country_ids'])->get()->pluck('name')->all());
            $data['allow_country_ids'] = join(',',$data['allow_country_ids']);
        }else{
            $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
            $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
            $allCountries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get()->pluck('name')->all();
            $data['allow_country'] = join(',',$allCountries);
            $data['allow_country_ids'] = join(',',CountryDB::whereIn('name',$allCountries)->get()->pluck('id')->all());
        }
        if(isset($data['from_country_id'])){
            $tmp = CountryDB::find($data['from_country_id']);
            !empty($tmp) ? $data['product_sold_country'] = $tmp->name : $data['product_sold_country'] = '台灣';
        }else{
            $data['from_country_id'] = 1;
            $data['product_sold_country'] = '台灣'; //強制設定為台灣
        }
        $data['type'] = 1;
        if(isset($data['unit_name_id'])){
            $tmp = ProductUnitNameDB::find($data['unit_name_id']);
            !empty($tmp) ? $data['unit_name'] = $tmp->name : $data['unit_name'] = '個';
        }else{
            $data['unit_name_id'] = 1;
            $data['unit_name'] = '個'; //強制設定為個
        }
        //最快出貨日大於最晚出貨日 清空最晚出貨日
        if(!empty($data['vendor_earliest_delivery_date'])) {
            strtotime($data['vendor_earliest_delivery_date']) > strtotime($data['vendor_latest_delivery_date']) ? $data['vendor_latest_delivery_date'] = null : '';
        }
        if(is_numeric($data['model_type']) && $data['model_type'] >= 1 || $data['model_type'] <= 3 ){
            $chk = 0;
            if($data['model_type'] == 1){
                $chk++;
            }elseif($data['model_type'] == 2){
                count($data['model_data']) > 0 ? $chk++ : '';
            }elseif($data['model_type'] == 3){
                for($i=0;$i<count($data['packageData']);$i++){
                    if(isset($data['packageData'][$i]['list'])){ //未選擇商品不建立
                        $chk++;
                    }
                }
            }
            if($chk > 0){
                $product = ProductDB::create($data);
                //處理圖片
                for($i=1;$i<=5;$i++){
                    $columnName = 'new_photo'.$i;
                    if ($request->hasFile($columnName)) {
                        $file = $request->file($columnName);
                        $result = $this->storeImageFile($columnName, $product, $request);
                        !empty($result) ? $product->update([$columnName => $result]) : '';
                    }
                }
                switch ($data['model_type']) {
                    case 1: //單一商品
                        $productModelData['quantity'] = $data['quantity'];
                        $productModelData['safe_quantity'] = $data['safe_quantity'];
                        $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                        !empty($data['vendor_product_model_id']) ?  $productModelData['vendor_product_model_id'] = $data['vendor_product_model_id'] : $productModelData['vendor_product_model_id'] = null;
                        $productModelData['gtin13'] = $data['gtin13'];
                        $productModelData['name'] = '單一規格';
                        $productModelData['is_del'] = 0;
                        $productModelData['product_id'] = $product->id;
                        $productModel = ProductModelDB::create($productModelData);
                        //建立庫存調整紀錄
                        $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值');
                        //產生SKU及鼎新代碼
                        $data['product_model_id'] = $productModel->id;
                        $output = $this->makeSku($data);
                        $productModel->update($output);
                        break;
                    case 2: //多款商品
                        if(isset($data['model_data'])){
                            for($i=0;$i<count($data['model_data']);$i++){
                                $productModelData['quantity'] = $data['model_data'][$i]['quantity'];
                                $productModelData['safe_quantity'] = $data['model_data'][$i]['safe_quantity'];
                                $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                                $productModelData['vendor_product_model_id'] = $data['model_data'][$i]['vendor_product_model_id'];
                                $productModelData['gtin13'] = $data['model_data'][$i]['gtin13'];
                                $productModelData['name'] = $data['model_data'][$i]['name'];
                                $productModelData['is_del'] = 0;
                                $productModelData['product_id'] = $product->id;
                                $productModel = ProductModelDB::create($productModelData);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值');
                                //產生SKU及鼎新代碼
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                            }
                        }
                        break;
                    case 3: //組合商品
                        // dd($data['packageData']);
                        for($i=0;$i<count($data['packageData']);$i++){
                            $productPackageData['sku'] = $data['packageData'][$i]['sku'];
                            $productPackageData['name'] = $data['packageData'][$i]['name'];
                            $productPackageData['vendor_product_model_id'] = $data['packageData'][$i]['vendor_product_model_id'];
                            $productPackageData['quantity'] = $data['packageData'][$i]['quantity'];
                            $productPackageData['safe_quantity'] = $data['packageData'][$i]['safe_quantity'];
                            $productPackageData['safe_quantity'] == 0 ? $productPackageData['safe_quantity'] = 1 : '';
                            $productPackageData['is_del'] = 0;
                            $productPackageData['product_id'] = $product->id;
                            if(isset($data['packageData'][$i]['list'])){ //未選擇商品不建立
                                $productModel = ProductModelDB::create($productPackageData);
                                $productPackage = ProductPackageDB::create([
                                    'product_id' => $product->id,
                                    'product_model_id' => $productModel->id,
                                ]);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值');
                                //產生SKU及鼎新代碼
                                $data['sku'] = $data['packageData'][$i]['sku'];
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                                //建立組合商品-商品資料
                                if(isset($data['packageData'][$i]['list'])){
                                    for($j=0;$j<count($data['packageData'][$i]['list']);$j++){
                                        rsort($data['packageData'][$i]['list']); //有可能在建立時被刪除所以需要重新整理key排序,不然會報錯
                                        $listData['product_package_id'] = $productPackage->id;
                                        $listData['product_model_id'] = $data['packageData'][$i]['list'][$j]['product_model_id'];
                                        $listData['quantity'] = $data['packageData'][$i]['list'][$j]['quantity'];
                                        $productPackageList = ProductPackageListDB::create($listData);
                                    }
                                }
                            }
                        }
                        //回寫package_data欄位
                        $packageData = [];
                        $packages = $product->packagesWithTrashed;
                        if(count($packages) > 0){
                            $i=0;
                            foreach($packages as $package){
                                $packageData[$i]['name'] = $package->name;
                                $packageData[$i]['bom'] = $package->sku;
                                !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                                $packageData[$i]['quantity'] = '';
                                $packageData[$i]['safe_quantity'] = '';
                                $x = 0;
                                foreach($package->lists as $list){
                                    $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                                    $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                                    $packageData[$i]['lists'][$x]['name'] = '';
                                    $packageData[$i]['lists'][$x]['price'] = '';
                                    $x++;
                                }
                                $i++;
                            }
                        }
                        if(count($packageData) > 0){
                            $product->update(['package_data' => json_encode($packageData)]);
                        }

                        break;
                    default:
                        break;
                }
            }else{
                $message = '款式商品/組合商品內容不正確。';
                Session::put('error', $message);
                return redirect()->back()->withInput($request->all());
            }
        }else{
            $message = '款式類別錯誤!!';
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        if (!empty($data['copy'])) {
            $message = '商品複製完成!!';
            Session::put('success', $message);
        }else{
            $message = '商品建立完成!!';
            Session::put('success', $message);
        }
        return redirect()->to('product');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewOnly = 0;
        $shippingMethods = ShippingMethodDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->get();
        $unitNames = ProductUnitNameDB::all();
        request()->request->add(['id' => $id]); //加入request
        $product = $this->getProductData(request(),'show');
        $categories = CategoryDB::whereIn('id',explode(',',$product->category_ids))->where('is_on',1)->get();
        $subCategories = SubCategoryDB::whereIn('category_id',explode(',',$product->category_id))->where('is_on',1)->orderBy('sort_id','asc')->get();
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = ProductLangDB::where([['lang',$langs[$i]['code']],['product_id',$product->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }
        $oldImages[] = $product->new_photo1;
        $oldImages[] = $product->new_photo2;
        $oldImages[] = $product->new_photo3;
        $oldImages[] = $product->new_photo4;
        $oldImages[] = $product->new_photo5;
        $vendor = VendorDB::findOrFail($product->vendor_id);
        return view('vendor.product.show',compact('langs','shippingMethods','vendor','categories','subCategories','product','unitNames','countries','oldImages','viewOnly'));
    }

    public function viewOnly($id)
    {
        $viewOnly = 1;
        $shippingMethods = ShippingMethodDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->get();
        $unitNames = ProductUnitNameDB::all();
        request()->request->add(['id' => $id]); //加入request
        $product = $this->getProductData(request(),'show');
        $categories = CategoryDB::whereIn('id',explode(',',$product->category_ids))->where('is_on',1)->get();
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = ProductLangDB::where([['lang',$langs[$i]['code']],['product_id',$product->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }
        $oldImages[] = $product->new_photo1;
        $oldImages[] = $product->new_photo2;
        $oldImages[] = $product->new_photo3;
        $oldImages[] = $product->new_photo4;
        $oldImages[] = $product->new_photo5;
        return view('vendor.product.show',compact('langs','shippingMethods','categories','product','unitNames','countries','oldImages','viewOnly'));
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
    public function update(ProductsRequest $request, $id)
    {
        foreach($request->all() as $key => $value){
            $data[$key] = !empty($value) && !is_array($value) ? $this->removeEmoji($value) : $value;
        }
        isset($data['category_id']) ? $data['category_id'] = join(',',$data['category_id']) : $data['category_id'] = null;
        isset($data['sub_categories']) ? $data['sub_categories'] = join(',',$data['sub_categories']) : $data['sub_categories'] = null;
        isset($data['name']) ? $data['name'] = str_replace('_','-',$data['name']) : '';
        $modelType = $data['model_type'];
        $message = null;
        if ($modelType == 2) {
            if(!isset($data['model_data'])){
                $message = '您是不是忘記填寫款式資料??';
            }
        }elseif ($modelType == 3) {
            if(!isset($data['packageData'])) {
                $message = '您是不是忘記建立組合品資料??';
            } else {
                $chkData = 0;
                for($i = 0;$i < count($data['packageData']);$i++) {
                    if(isset($data['packageData'][$i]['list']) && count($data['packageData'][$i]['list']) > 0) {
                        $chkData++;
                    }
                }
                if($chkData == 0) {
                    $message = '您是不是忘記填寫組合品內的商品資料??';
                }
            }
        }
        if(!empty($message)){
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $product = ProductDB::findOrFail($id);
        $vendor = VendorDB::find($product->vendor_id);
        $param['productName'] = $product->name;
        $param['vendorName'] = $vendor->name;
        $param['status'] = $data['status'];
        if(!empty($data['status'])){
            //狀態, -9:下架 -3:補貨中 -2:送審失敗 -1:未送審(草稿) 1:上架中 2:送審中
            if($data['status'] == '修改並送審'){
                $data['status'] = 0;
                $data['pause_reason'] = null;
            }elseif($data['status'] == '恢復銷售'){
                $data['status'] = 1;
                $product = $product->update(['status' => $data['status'], 'pause_reason' => null, 'verification_reason' => '商家恢復上架']);
                $param['subject'] = $param['vendorName'].' 商家後台商品 '.$param['productName'].' 恢復銷售通知';
                $param['reason'] = null;
                $this->sendMail($param);
                return redirect()->back();
            }elseif($data['status'] == '存為草稿'){
                $data['status'] = -1;
                $data['pause_reason'] = null;
            }elseif($data['status'] == '暫停銷售'){
                $data['status'] = -3;
                $product = $product->update(['status' => $data['status'], 'pause_reason' => $data['pause_reason']]);
                $param['subject'] = $param['vendorName'].' 商家後台商品 '.$param['productName'].' 暫停銷售通知';
                $param['reason'] = $data['pause_reason'];
                $this->sendMail($param);
                return redirect()->back();
            }elseif($data['status'] == '下架編輯'){
                $data['status'] = -9;
                $product = $product->update(['status' => $data['status'], 'pause_reason' => $data['pause_reason'], 'verification_reason' => '商家下架編輯']);
                $param['subject'] = $param['vendorName'].' 商家後台商品 '.$param['productName'].' 下架編輯通知';
                $param['reason'] = $data['pause_reason'];
                $this->sendMail($param);
                return redirect()->back();
            }elseif($data['status'] == '修改確認'){
                $data['status'] = $product->status;
            }
        }else{
            return redirect()->back();
        }
        $data['airport_days'] ?? $data['airport_days'] = 0;
        $data['hotel_days'] ?? $data['hotel_days'] = 0;
        $data['is_hot'] ?? $data['is_hot'] = 0;
        $data['storage_life'] ?? $data['storage_life'] = 0;
        isset($data['shipping_methods']) ? $data['shipping_methods'] = join(',',$data['shipping_methods']) : '';
        if(isset($data['allow_country_ids'])){
            $data['allow_country'] = join(',',CountryDB::whereIn('id',$data['allow_country_ids'])->get()->pluck('name')->all());
            $data['allow_country_ids'] = join(',',$data['allow_country_ids']);
        }else{
            $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
            $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
            $allCountries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get()->pluck('name')->all();
            $data['allow_country'] = join(',',$allCountries);
            $data['allow_country_ids'] = join(',',CountryDB::whereIn('name',$allCountries)->get()->pluck('id')->all());
        }
        if(isset($data['unit_name_id'])){
            $tmp = ProductUnitNameDB::find($data['unit_name_id']);
            !empty($tmp) ? $data['unit_name'] = $tmp->name : $data['unit_name'] = '個';
        }else{
            $data['unit_name_id'] = 1;
            $data['unit_name'] = '個'; //強制設定為個
        }
        //最快出貨日大於最晚出貨日 清空最晚出貨日
        if(!empty($data['vendor_earliest_delivery_date'])) {
            strtotime($data['vendor_earliest_delivery_date']) > strtotime($data['vendor_latest_delivery_date']) ? $data['vendor_latest_delivery_date'] = null : '';
        }
        //紀錄 price && storage_life 變動
        // if($product->price != $data['price']){
        //     $this->productUpdateRecord($product->id,'price',$product->price,$data['price']);
        // }
        if($product->storage_life != $data['storage_life']){
            $this->productUpdateRecord($product->id,'storage_life',$product->storage_life,$data['storage_life']);
        }
        //處理圖片
        for($i=1;$i<=5;$i++){
            $columnName = 'new_photo'.$i;
            if ($request->hasFile($columnName)) {
                $file = $request->file($columnName);
                $result = $this->storeImageFile($columnName, $product, $request);
                !empty($result) ? $data[$columnName] = $result : '';
            }
        }
        $product->update($data);
        $modelType = (INT)$request->model_type;
        if(is_numeric($modelType) && $modelType >= 1 || $modelType <= 3 ){
            switch ($modelType) {
                case 1://單一款式
                    if($data['product_model_id']){
                        !empty($data['quantity']) ? $productModelData['quantity'] = $data['quantity'] : $productModelData['quantity'] = 0;
                        !empty($data['safe_quantity']) ? $productModelData['safe_quantity'] = $data['safe_quantity'] : $productModelData['safe_quantity'] = 0;
                        $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                        !empty($data['vendor_product_model_id']) ?  $productModelData['vendor_product_model_id'] = $data['vendor_product_model_id'] : $productModelData['vendor_product_model_id'] = null;
                        !empty($data['gtin13']) ?  $productModelData['gtin13'] = $data['gtin13'] : $productModelData['gtin13'] = null;
                        $productModel = ProductModelDB::findOrFail($data['product_model_id']);
                        if(!empty($productModel)){
                            // 建立庫存調整紀錄 或 gtin13 國際條碼變動
                            if ($productModel->gtin13 != $productModelData['gtin13']){
                                if($productModel->quantity != $productModelData['quantity']){
                                    $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],$productModel->gtin13,$productModelData['gtin13'],'國際條碼及庫存變更');
                                }else{
                                    $this->productQuantityRecord($productModel->id,null,null,$productModel->gtin13,$productModelData['gtin13'],'國際條碼變更');
                                }
                            }else{
                                if($productModel->quantity != $productModelData['quantity']){
                                    $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],null,null,'商品庫存變更');
                                }
                            }
                            $productModel->update($productModelData);
                        }
                    }
                    break;
                case 2://多款產品
                    // dd($data['model_data']);
                    if(isset($data['model_data'])){
                        for($i=0;$i<count($data['model_data']);$i++){
                            $productModelData['name'] = $data['model_data'][$i]['name'];
                            $productModelData['quantity'] = (INT)$data['model_data'][$i]['quantity'];
                            $productModelData['safe_quantity'] = (INT)$data['model_data'][$i]['safe_quantity'];
                            $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                            $productModelData['gtin13'] = $data['model_data'][$i]['gtin13'];
                            $productModelData['vendor_product_model_id'] = $data['model_data'][$i]['vendor_product_model_id'];
                            $productModelId = $data['model_data'][$i]['product_model_id'];
                            if(!empty($productModelId)){
                                $productModel = ProductModelDB::findOrFail($productModelId);
                                // 建立庫存調整紀錄 或 gtin13 國際條碼變動
                                if ($productModel->gtin13 != $productModelData['gtin13']){
                                    if($productModel->quantity != $productModelData['quantity']){
                                        $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],$productModel->gtin13,$productModelData['gtin13'],'國際條碼及庫存變更');
                                    }else{
                                        $this->productQuantityRecord($productModel->id,null,null,$productModel->gtin13,$productModelData['gtin13'],'國際條碼變更');
                                    }
                                }else{
                                    if($productModel->quantity != $productModelData['quantity']){
                                        $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],null,null,'商品庫存變更');
                                    }
                                }
                                $productModel->update($productModelData);
                            }else{
                                $productModelData['is_del'] = 0;
                                $productModelData['product_id'] = $product->id;
                                $productModel = ProductModelDB::create($productModelData);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值');
                                //產生SKU及鼎新代碼
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                            }
                        }
                    }
                    break;
                case 3:
                    // dd($data['packageData']);
                    if(isset($data['packageData'])){
                        for($i=0;$i<count($data['packageData']);$i++){
                            if(isset($data['packageData'][$i]['list'])){
                                isset($data['packageData'][$i]['product_package_id']) ? $productPackageData['product_package_id'] = $data['packageData'][$i]['product_package_id'] : $productPackageData['product_package_id'] = '';
                                $productPackageData['vendor_product_model_id'] = $data['packageData'][$i]['vendor_product_model_id'];
                                $productPackageData['sku'] = $data['packageData'][$i]['sku'];
                                $productPackageData['name'] = $data['packageData'][$i]['name'];
                                $productPackageData['quantity'] = $data['packageData'][$i]['quantity'];
                                $productPackageData['safe_quantity'] = $data['packageData'][$i]['safe_quantity'];
                                $productPackageData['safe_quantity'] == 0 ? $productPackageData['safe_quantity'] = 1 : '';
                                isset($data['packageData'][$i]['list']) ? $packageListData = $data['packageData'][$i]['list'] : $packageListData = null;
                                if(!empty($productPackageData['product_package_id'])){
                                    $productPackage = ProductPackageDB::findOrFail($productPackageData['product_package_id']);
                                    $productModel = ProductModelDB::findOrFail($productPackage->product_model_id);
                                    if($productModel->quantity != $productPackageData['quantity']){
                                        $this->productQuantityRecord($productModel->id,$productModel->quantity,$productPackageData['quantity'],null,null,'商品庫存變更');
                                    }
                                    $productModel->update($productPackageData);
                                    if(isset($packageListData)){
                                        for($j=0;$j<count($packageListData);$j++){
                                            $listData['product_package_id'] = $productPackage->id;
                                            $listData['product_model_id'] = $packageListData[$j]['product_model_id'];
                                            $listData['quantity'] = $packageListData[$j]['quantity'];
                                            if(isset($packageListData[$j]['product_package_list_id'])){
                                                ProductPackageListDB::findOrFail($packageListData[$j]['product_package_list_id'])->update($listData);
                                            }else{
                                                $productPackageList = ProductPackageListDB::create($listData);
                                            }
                                        }
                                    }
                                }else{
                                    $productPackageData['product_id'] = $product->id;
                                    $productModel = ProductModelDB::create($productPackageData);
                                    //新增Package資料
                                    $productPackage = ProductPackageDB::create([
                                        'product_id' => $product->id,
                                        'product_model_id' => $productModel->id,
                                    ]);
                                    //建立庫存調整紀錄
                                    $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值');
                                    //產生SKU及鼎新代碼
                                    $data['sku'] = $data['packageData'][$i]['sku'];
                                    $data['product_model_id'] = $productModel->id;
                                    $output = $this->makeSku($data);
                                    $productModel->update($output);
                                    //新增PackageList資料
                                    if ($packageListData) {
                                        for ($j=0;$j<count($packageListData);$j++) {
                                            $listData['product_package_id'] = $productPackage->id;
                                            $listData['product_model_id'] = $packageListData[$j]['product_model_id'];
                                            $listData['quantity'] = $packageListData[$j]['quantity'];
                                            $productPackageList = ProductPackageListDB::create($listData);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //回寫package_data欄位
                    $packageData = [];
                    $packages = $product->packagesWithTrashed;
                    if(count($packages) > 0){
                        $i=0;
                        foreach($packages as $package){
                            $packageData[$i]['name'] = $package->name;
                            $packageData[$i]['bom'] = $package->sku;
                            !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                            $packageData[$i]['quantity'] = '';
                            $packageData[$i]['safe_quantity'] = '';
                            $x = 0;
                            foreach($package->lists as $list){
                                $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                                $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                                $packageData[$i]['lists'][$x]['name'] = '';
                                $packageData[$i]['lists'][$x]['price'] = '';
                                $x++;
                            }
                            $i++;
                        }
                    }
                    if(count($packageData) > 0){
                        $product->update(['package_data' => json_encode($packageData)]);
                    }
                    break;
                default:
                    break;
            }
        }else{
            $message = '款式類別錯誤!!';
            Session::put('error', $message);
            return redirect()->back();
        }
        $message = '產品資料更新完成!!';
        Session::put('success', $message);
        return redirect()->to('product');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = ProductDB::findOrFail($id);
        $vendor = VendorDB::find($product->vendor_id);
        $param['productName'] = $product->name;
        $param['vendorName'] = $vendor->name;
        if(!empty(request()->reason)){
            $param['reason'] = request()->reason;
            $param['status'] = '刪除';
            $param['subject'] = $vendor->name.' 商家後台商品 '.$product->name.' 刪除通知';
            $product->update(['is_del' => 1, 'status' => -9, 'pause_reason' => $param['reason']]);
            $this->sendMail($param);
        }else{
            Session::put('error','未填寫刪除理由。');
        }
        return redirect()->back();
    }

    public function changeStatus(Request $request, $id)
    {
        $this->vendorId = auth('vendor')->user()->vendor_id;
        $product = ProductDB::where([['id',$id],['vendor_id',$this->vendorId]])->first();
        $vendor = VendorDB::find($product->vendor_id);
        $param['productName'] = $product->name;
        $param['vendorName'] = $vendor->name;
        if(!empty($request->status) && !empty($product)){
            if($request->status == 'down'){
                $data['status'] = -9;
                $param['reason'] = $data['pause_reason'] = $request->reason;
                $param['status'] = '下架';
                $param['subject'] = $vendor->name.' 商家後台商品 '.$product->name.' 下架通知';
            }
            if($request->status == 'relaunch'){
                $data['status'] = 1;
                $param['reason'] = $data['pause_reason'] = null;
                $param['status'] = '恢復銷售';
                $param['subject'] = $vendor->name.' 商家後台商品 '.$product->name.' 恢復銷售通知';
            }
            if($request->status == 'pause'){
                $data['status'] = -3;
                $param['reason'] = $data['pause_reason'] = $request->reason;
                $param['status'] = '暫停銷售';
                $param['subject'] = $vendor->name.' 商家後台商品 '.$product->name.' 暫停銷售通知';
            }
            $product->update($data);
            $this->sendMail($param);
        }
        return redirect()->back();
    }
    /*
        語言功能
     */
    public function lang(Request $request)
    {
        if (Validator::make($request->all(), $this->langRules)->fails()) {
            return redirect()->route('vendor.product.show', $request->product_id.'#lang-'.$request->lang)->withErrors(Validator::make($request->all(), $this->langRules));
        }
        $data = $request->all();
        $modelType = $request->model_type;
        $data['id'] = $data['product_id'] = $request->product_id;
        $langId = $request->langId;
        $product = ProductDB::find($data['product_id'])->toArray();
        $data = array_merge($product,$data);
        unset($data['is_del']);
        unset($data['pass_time']);
        unset($data['unit_name_id']);
        unset($data['from_country_id']);
        unset($data['model_type']);
        unset($data['curation_text_top']);
        unset($data['curation_text_bottom']);
        //回寫舊資料
        if($data['lang'] == 'en'){
            $tmp = ProductEnDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductEnDB::create($data);
        }elseif($data['lang'] == 'jp'){
            $tmp = ProductJpDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductJpDB::create($data);
        }elseif($data['lang'] == 'kr'){
            $tmp = ProductKrDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductKrDB::create($data);
        }elseif($data['lang'] == 'th'){
            $tmp = ProductThDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductThDB::create($data);
        }
        if(!empty($langId)){
            $productLang = ProductLangDB::find($langId);
            if(!empty($productLang)){
                $productLang->update($data);
            }
        }else{
            $productLang = ProductLangDB::create($data);
        }
        switch ($modelType) {
            case 1:
                //只有多款及組合商品需要多語言，單一款式多國語言已包含在ProductLang那邊
                break;

            case 2:
                for($i=0;$i<count($data['model_data']);$i++){
                    $productModel['id'] = $data['model_data'][$i]['product_model_id'];
                    $productModel['name_'.$data['lang']] = $data['model_data'][$i]['name_'.$data['lang']];
                    $pm = ProductModelDB::find($productModel['id']);
                    !empty($pm) ? $pm->update($productModel) : '';
                }
                break;

            case 3:
                for($i=0;$i<count($data['packageData']);$i++){
                    $productModel['id'] = $data['packageData'][$i]['product_model_id'];
                    $productModel['name_'.$data['lang']] = $data['packageData'][$i]['name_'.$data['lang']];
                    $pm = ProductModelDB::find($productModel['id']);
                    !empty($pm) ? $pm->update($productModel) : '';
                }
                break;

            default:
                # code...
                break;
        }
        return redirect()->route('vendor.product.show', $request->product_id.'#lang-'.$request->lang);
    }

    /*
        產品搜尋列表
     */
    public function getList(Request $request)
    {
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        $keyword = $request->search;
        $data = ProductDB::join('product_model', 'product.id', '=', 'product_model.product_id')
        ->join('vendor', 'vendor.id', '=', 'product.vendor_id');
        $vendorId != 185 ? $data = $data->where('product.vendor_id',$vendorId) : '';
        $data = $data->where(function($q){
            $q->where('product.package_data','')->orWhere('product.package_data',null);
        })->where('product.is_del',0)
        ->whereIn('status',[1,-3,-9])
        ->where(function ($query) use ($keyword) {
            $query->where('product.name','like',"%$keyword%")
            ->orWhere('product_model.sku','like',"%$keyword%")
            ->orWhere('vendor.name','like',"%$keyword%");
        })->select(
            'product.name as name',
            DB::raw('(CASE WHEN product.model_name is null or product.model_name = "" THEN product_model.name ELSE product.model_name END) as model_name'),
            'product_model.sku as sku',
            'vendor.name as vendor_name',
            'product_model.id as product_model_id')->get();
        return response()->json($data);
    }

    public function delModel(Request $input){
        $id = (int)request()->id;
        $productModel = ProductModelDB::findOrFail($id);
        if($productModel){
            $productModel->update(['is_del' => 1]);
        }
        return redirect()->back();
    }

    public function delPackage(Request $input){
        $id = (int)request()->id;
        $productPackage = ProductPackageDB::findOrFail($id);
        if($productPackage){
            $productModel = ProductModelDB::find($productPackage->product_model_id);
            !empty($productModel) ? $productModel->update(['is_del' => 1]) : '';

            $productPackage->delete();

            //回寫package_data欄位
            $product = ProductDB::with('packagesWithTrashed')->find($productPackage->product_id);
            if(!empty($product)){
                $packageData = [];
                $packages = $product->packagesWithTrashed;
                if(count($packages) > 0){
                    $i=0;
                    foreach($packages as $package){
                        $packageData[$i]['name'] = $package->name;
                        $packageData[$i]['bom'] = $package->sku;
                        !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                        $packageData[$i]['quantity'] = '';
                        $packageData[$i]['safe_quantity'] = '';
                        $x = 0;
                        foreach($package->lists as $list){
                            $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                            $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                            $packageData[$i]['lists'][$x]['name'] = '';
                            $packageData[$i]['lists'][$x]['price'] = '';
                            $x++;
                        }
                        $i++;
                    }
                }
                if(count($packageData) > 0){
                    $product->update(['package_data' => json_encode($packageData)]);
                }
            }
        }
        return redirect()->back();
    }

    public function delList(Request $input){
        $id = (int)request()->id;
        $productPackageList = ProductPackageListDB::findOrFail($id);
        if($productPackageList){
            $productPackageList->delete();
        }
        return redirect()->back();
    }

    public function getStockRecord(Request $input){
        $id = (int)request()->id;
        $productModel = ProductModelDB::find($id);
        $product = ProductDB::find($productModel->product_id);
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $productQuantityRecordTable = env('DB_ICARRY').'.'.(new ProductQuantityRecordDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        $productQtyRecord = ProductQuantityRecordDB::where($productQuantityRecordTable.'.product_model_id',$id)->orderBy($productQuantityRecordTable.'.create_time','desc')->select([
            $productQuantityRecordTable.'.*',
            'admin' => AdminDB::whereColumn($adminTable.'.id',$productQuantityRecordTable.'.admin_id')->select($adminTable.'.name')->limit(1),
            'vendor' => VendorAccountDB::whereColumn($vendorAccountTable.'.id',$productQuantityRecordTable.'.vendor_id')->select($vendorAccountTable.'.name')->limit(1),
        ])->get();
            $data = collect(['product' => $product, 'productModel' => $productModel, 'productQtyRecord' => $productQtyRecord]);
        return response($data);
    }

    public function stockModifyOld(Request $request){
        $productModelId = (int)request()->product_model_id;
        $newStock = (int)request()->quantity;
        $safeStock = (int)request()->safe_quantity;
        $newStock == 0 ?$newStock = 1 : '';
        $reason = request()->reason;
        //找出product model 舊的資料
        $productModel = ProductModelDB::find($productModelId);
        if($productModel->quantity != $newStock || $productModel->safe_quantity != $safeStock){
            $productModel->update(['quantity' => $newStock, 'safe_quantity' => $safeStock]);
            if($productModel->quantity != $newStock){
                $this->productQuantityRecord($productModel->id,$productModel->quantity,$newStock,null,null,$reason);
            }
        }
        return redirect()->back();
    }

    public function stockModify(Request $input){
        $productModelId = (int)request()->product_model_id;
        $newStock = (int)request()->quantity;
        $newSafeStock = (int)request()->safe_quantity;
        $newSafeStock == 0 ? $newSafeStock = 1 : ''; //安全庫存強制改為1
        $reason = request()->reason;
        $productQtyRecord = null;
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $productQuantityRecordTable = env('DB_ICARRY').'.'.(new ProductQuantityRecordDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        //找出product model 舊的資料
        $productModel = ProductModelDB::with('product','product.vendor')->findOrFail($productModelId);
        $vendorId = $productModel->product->vendor->id;
        if($productModel->quantity != $newStock || $productModel->safe_quantity != $newSafeStock){
            ProductModelDB::where('id',$productModelId)->update(['quantity' => $newStock, 'safe_quantity' => $newSafeStock]);
            if($productModel->quantity != $newStock){
                $pqrId = $this->productQuantityRecord($productModel->id,$productModel->quantity,$newStock,null,null,$reason);
                $productQtyRecord = ProductQuantityRecordDB::select([
                    $productQuantityRecordTable.'.*',
                    'admin' => AdminDB::whereColumn($adminTable.'.id',$productQuantityRecordTable.'.admin_id')->select($adminTable.'.name')->limit(1),
                    'vendor' => VendorAccountDB::whereColumn($vendorAccountTable.'.id',$productQuantityRecordTable.'.vendor_id')->select($vendorAccountTable.'.name')->limit(1),
                ])->find($pqrId);
            }
        }
        $data = collect(['productModel' => $productModel, 'productQtyRecord' => $productQtyRecord]);
        return response($data);
    }


    public function copy($id)
    {
        $copy = true;
        $viewOnly = 0;
        $shippingMethods = ShippingMethodDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->get();
        $unitNames = ProductUnitNameDB::all();
        request()->request->add(['id' => $id]); //加入request
        $product = $this->getProductData(request(),'show');
        $vendor = VendorDB::find($product->vendor_id);
        $categories = CategoryDB::whereIn('id',explode(',',$product->category_ids))->where('is_on',1)->get();
        $subCategories = SubCategoryDB::where([['category_id',$product->category_id],['is_on',1]])->orderBy('sort_id','asc')->get();
        $productImages = ProductImageDB::where('product_id',$product->id)->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','asc')->get();
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = ProductLangDB::where([['lang',$langs[$i]['code']],['product_id',$product->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }

        //找出款式商品資料
        $product->models = ProductModelDB::where('product_id',$product->id)
            ->select(
                'id',
                'name',
                'sku',
                'quantity',
                'safe_quantity',
                'gtin13',
            )->get();

        //找出組合商品資料
        if($product->model_type == 3){
            $product->packages = ProductPackageDB::join('product_model','product_model.id','product_packages.product_model_id')
            ->where('product_packages.product_id',$product->id)
            ->select(
                'product_packages.id',
                'product_model.name',
                'product_model.name_en',
                'product_model.name_jp',
                'product_model.name_kr',
                'product_model.name_th',
                'product_model.sku',
                'product_model.quantity',
                'product_model.safe_quantity',
            )->get();

            foreach ($product->packages as $package) {
                $lists = ProductPackageListDB::join('product_model','product_model.id','product_package_lists.product_model_id')
                ->join('product','product.id','product_model.product_id')
                ->where('product_package_lists.product_package_id',$package->id)
                ->select(
                    'product_package_lists.id',
                    'product_package_lists.product_model_id',
                    'product_model.sku',
                    'product.name',
                    'product_package_lists.quantity',
                )->get();
                $package->lists = $lists;
            }
        }
        $oldImages[] = $product->new_photo1;
        $oldImages[] = $product->new_photo2;
        $oldImages[] = $product->new_photo3;
        $oldImages[] = $product->new_photo4;
        $oldImages[] = $product->new_photo5;
        return view('vendor.product.show',compact('vendor','langs','shippingMethods','subCategories','categories','product','unitNames','countries','productImages','copy','oldImages','viewOnly'));
    }
    /*
        Quantity && gtin13 變更紀錄
    */
    private function productQuantityRecord($productModelId,$beforeQuantity,$afterQuantity,$beforeGtin13,$afterGtin13,$reason)
    {
        $record = ProductQuantityRecordDB::create([
            'product_model_id' => $productModelId,
            'vendor_id' => auth('vendor')->user()->id,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => $afterQuantity,
            'before_gtin13' => $beforeGtin13,
            'after_gtin13' => $afterGtin13,
            'reason' => '商家-'.$reason,
        ]);
        return $record->id;
    }
    /*
        price && storage_life 變更紀錄
    */
    private function productUpdateRecord($productId,$column,$beforeValue,$afterValue)
    {
        $record = ProductUpdateRecordDB::create([
            'product_id' => $productId,
            'vendor_id' => auth('vendor')->user()->vendor_id,
            'column' => $column,
            'before_value' => $beforeValue,
            'after_value' => $afterValue,
        ]);
    }
        /*
        舊版圖檔上傳
    */
    public function upload(Request $request)
    {
        $columnName = $request->column_name;
        if(!empty($columnName)){
        //檢查表單是否有檔案
        if(!$request->hasFile($request->column_name)){
            $message = "請選擇要上傳的檔案再按送出按鈕";
            Session::put('info',$message);
        }else{
            $result = $this->storeFile($request);
            if($result == true){
                $message = "檔案上傳成功";
                Session::put('success',$message);
            }else{
                $message = "檔案上傳失敗";
                Session::put('error',$message);
            }
        }
            return redirect()->route('vendor.product.show',$request->product_id.'#old-product-image');
        }
    }
    public function storeFile($request){
        isset($request->column_name) ? $columnName = $request->column_name : $columnName = null;
        $product = ProductDB::find($request->product_id);
        if(!empty($columnName) && !empty($product)){
            //目的目錄
            $destPath = '/upload/product/';
            //檢查本地目錄是否存在，不存在則建立
            !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
            //檢查S3目錄是否存在，不存在則建立
            !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
            //實際檔案
            $file = $request->file($columnName);
            //副檔名
            $ext = $file->getClientOriginalExtension();
            //新檔名
            $fileName = str_replace('new_','',$columnName).'_'.$request->product_id.'_'. Carbon::now()->timestamp . '.' . $ext;

            //變更尺寸寬高
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;

            //將檔案搬至本地目錄
            $file->move(public_path().$destPath, $fileName);
            //使用Spatie/image的套件Resize圖檔
            Image::load(public_path().$destPath.$fileName)
            ->width($reSizeWidth)
            ->height($reSizeHeigh)
            ->save(public_path().$destPath.$fileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
            //刪除本地檔案
            unlink(public_path().$destPath.$fileName);
            //更新檔案名稱至資料表中
            $product->update([$columnName => $destPath.$fileName]);
            return true;
        }
        return false;
    }
    public function storeImageFile($columnName, $product, $request){
        if(!empty($columnName) && !empty($product)){
            //目的目錄
            $destPath = '/upload/product/';
            //檢查本地目錄是否存在，不存在則建立
            !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
            //檢查S3目錄是否存在，不存在則建立
            !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
            //實際檔案
            $file = $request->file($columnName);
            //副檔名
            $ext = $file->getClientOriginalExtension();
            //新檔名
            $fileName1 = str_replace('new_','',$columnName).'_'.$product->id.'_'. Carbon::now()->timestamp;
            $fileName = $fileName1. '.' . $ext;
            $smallFileName = $fileName1. '_s.' . $ext;
            //變更尺寸寬高
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
            $originFileName = 'originFileName.'.$ext;
            //將檔案搬至本地目錄
            $file->move(public_path().$destPath, $originFileName);

            //使用Spatie/image的套件Resize圖檔
            Image::load(public_path().$destPath.$originFileName)
            ->width($reSizeWidth)
            ->height($reSizeHeigh)
            ->save(public_path().$destPath.$fileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');

            //縮圖
            Image::load(public_path().$destPath.$originFileName)
            ->width(600)
            ->height(320)
            ->save(public_path().$destPath.$smallFileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$smallFileName, file_get_contents(public_path().$destPath.$smallFileName) , 'public');

            //刪除本地檔案
            unlink(public_path().$destPath.$originFileName);
            unlink(public_path().$destPath.$fileName);
            unlink(public_path().$destPath.$smallFileName);

            //更新檔案名稱至資料表中
            $product->update([$columnName => $destPath.$fileName]);

            return $destPath.$fileName;
        }
        return null;
    }
    public function getHistory(Request $request){
        $data = ProductUpdateRecordDB::where([['product_id',$request->product_id],['column',$request->column]])
        ->select([
            '*',
            'admin_name' => AdminDB::whereColumn('id','admin_id')->select('name')->limit(1),
            'vendor_name' => VendorDB::whereColumn('id','vendor_id')->select('name')->limit(1),
            DB::raw("DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as createTime"),
        ])->orderBy('create_time','desc')->get();
        return response($data);
    }

    public function getGtin13History(Request $request){
        $gtin13 = $request->gtin13;
        $data = ProductQuantityRecordDB::where('product_model_id',$request->product_model_id)
        ->where(function($query)use($gtin13){
            $query->where('before_gtin13',"$gtin13")
            ->orWhere('after_gtin13',"$gtin13");
        })->select([
            '*',
            'admin_name' => AdminDB::whereColumn('id','admin_id')->select('name')->limit(1),
            'vendor_name' => VendorDB::whereColumn('id','vendor_id')->select('name')->limit(1),
            DB::raw("DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as createTime"),
        ])->orderBy('create_time','desc')->get();
        return response($data);
    }

    public function getSubCate(Request $request)
    {
        if(count($request->category_id) > 0){
            $subCategories = SubCategoryDB::whereIn('category_id',$request->category_id)->where('is_on',1)->get();
            $product = ProductDB::find($request->product_id);
            !empty($product) ? $subCates = $product->sub_categories : $subCates = null;
            !empty($subCates) ? $subCates = explode(',',$subCates) : '';
            foreach($subCategories as $subCate){
                $subCate->chk = null;
                if(is_array($subCates)){
                    in_array($subCate->id,$subCates) ? $subCate->chk = 'checked' : '';
                }
            }
            return $subCategories;
        }
        return [];
    }

    public function deloldimage(Request $request)
    {
        if(!empty($request->id) && !empty($request->columnName)){
            $product = ProductDB::findOrFail($request->id);
            $product->update([$request->columnName => null]);
            return 'success';
        }
        return null;
    }

    private function sendMail($param)
    {
        if(!empty($param['subject'])){
            $param['model'] = 'productChangeMailBody';
            $param['from'] = 'icarry@icarry.me'; //寄件者
            $param['name'] = 'iCarry商家後台管理系統'; //寄件者名字
            strstr(env('APP_URL'),'localhost') ? $param['to'] = ['roger@icarry.me'] : $param['to'] = ['icarryop@icarry.me','sales@icarry.me'];
            SendEmailJob::dispatchNow($param);
        }
        return null;
    }

    protected function removeEmoji($string) {

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }
}
