<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryCountry as CountryDB;
use DB;
use Carbon\Carbon;

trait ProductFunctionTrait
{
    protected function getProductData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        auth('vendor')->user() ? $vendorId = auth('vendor')->user()->vendor_id : $vendorId = null;

        $products = ProductDB::join($productModelTable,$productModelTable.'.product_id',$productTable.'.id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id');

        if($type == 'index'){
            $products = $products->with('category','models','image');
        }elseif($type == 'show'){
            $products = $products->with('models','images','image');
        }

        //找出特定商家的商品
        $products = $products->where([[$productTable.'.vendor_id',$vendorId],[$productTable.'.is_del',0],[$productModelTable.'.is_del',0]]);

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $products = $products->whereIn($productTable.'.id',$request['id']) : '';
        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }

        if(isset($status)){
            $st = explode(',',$status);
            $products = $products->whereIn('status',$st);
        }

        if(!empty($shipping_methods)){
            $shipping_methods = ltrim(rtrim($shipping_methods,','),','); //去除左右邊逗點
            $sm = explode(',',$shipping_methods);
            $c = 'shipping_methods is null ';
            for($i=0;$i<count($sm);$i++){
                $c .= " OR FIND_IN_SET('$sm[$i]',shipping_methods) ";
            }
            $products = $products->where(function($query)use($c){
                $query->whereRaw($c);
            });
        }
        isset($name) ? $products = $products->where($productTable.'.name','like',"%$name%") : '';
        isset($sku) ? $products = $products->where($productModelTable.'.sku','like',"%$sku%") : '';
        isset($low_quantity) && $low_quantity == 1 ? $products = $products->whereRaw(" $productModelTable.quantity < $productModelTable.safe_quantity ") : '';
        isset($zero_quantity) && $zero_quantity == 1 ? $products = $products->whereRaw($productModelTable.'.quantity <= 0') : '';
        !empty($created_at) ? $products = $products->where($productTable.'.create_time', '>=', $created_at) : '';
        !empty($created_at_end) ? $products = $products->where($productTable.'.create_time', '<=', $created_at_end) : '';
        !empty($pass_time) ? $products = $products->where($productTable.'.pass_time', '>=', $pass_time) : '';
        !empty($pass_time_end) ? $products = $products->where($productTable.'.pass_time', '<=', $pass_time_end) : '';

        if (!isset($list)) {
            $list = 50;
        }

        $products = $products->select([
            $productTable.'.*',
            $vendorTable.'.name as vendor_name',
            $vendorTable.'.categories as category_ids',
        ]);

        if($type == 'index'){
            $products = $products->orderBy($productTable.'.id', 'desc')->distinct()->paginate($list);
        }elseif($type == 'show'){
            if(isset($request['product_id'])){
                $products = $products->findOrFail($request['product_id']);
            }else{
                $products = $products->findOrFail($request['id']);
            }
        }else{
            $products = $products->orderBy($productTable.'.id', 'desc')->get();
        }

        return $products;
    }

    protected function getProductExportData($param)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $categoryTable = env('DB_ICARRY').'.'.(new CategoryDB)->getTable();

        $products = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
            ->join($categoryTable,$categoryTable.'.id',$productTable.'.category_id');

        if($param['method'] == 'allData'){ //全部商品
            //不做任何事情
        }elseif(isset($param['id'])){ //指定選擇的商品 or 目前頁面所有商品
            $products = $products->whereIn($productTable.'.id',$param['id']);
        }elseif(isset($param['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($param['con'] as $key => $value) {
                $$key = $value;
            }

            //查詢參數
            !empty($status) ? $products = $products->whereIn($productTable.'.status',explode(',',$status)) : '';
            if(!empty($shipping_methods)){
                $shipping_methods = ltrim(rtrim($shipping_methods,','),','); //去除左右邊逗點
                $sm = explode(',',$shipping_methods);
                $c = $productTable.'.shipping_methods is null ';
                for($i=0;$i<count($sm);$i++){
                    $c .= " OR FIND_IN_SET('$sm[$i]',$productTable.shipping_methods) ";
                }
                $products = $products->where(function($query)use($c){
                    $query->whereRaw($c);
                });
            }
            !empty($digiwin_no) ? $products = $products->where($productModelTable.'.digiwin_no','like',"%$digiwin_no%") : '';
            !empty($sku) ? $products = $products->where($productModelTable.'.sku','like',"%$sku%") : '';
            !empty($name) ? $products = $products->where($productTable.'.name','like',"%$name%") : '';
            !empty($low_quantity) ? $products = $products->whereRaw($productModelTable.'.quantity < $productModelTable.safe_quantity') : '';
            !empty($zero_quantity) ? $products = $products->whereRaw($productModelTable.'.quantity <= 0') : '';
            !empty($vendor_id) ? $products = $products->where($productTable.'.vendor_id', $vendor_id) : '';
            !empty($category_id) ? $products = $products->where($productTable.'.category_id', $category_id) : '';
            !empty($product_name) ? $products = $products->where($productTable.'.name', 'like', "%$product_name%") : '';
            !empty($vendor_name) ? $products = $products->where($vendorTable.'.name', 'like', "%$vendor_name%") : '';
            !empty($created_at) ? $products = $products->where($productTable.'.created_at', '>=', $created_at) : '';
            !empty($created_at_end) ? $products = $products->where($productTable.'.created_at', '<=', $created_at_end) : '';
            !empty($pass_time) ? $products = $products->where($productTable.'.pass_time', '>=', $pass_time) : '';
            !empty($pass_time_end) ? $products = $products->where($productTable.'.pass_time', '<=', $pass_time_end) : '';
        }

        //選擇資料
        $products = $products->select([
            $productTable.'.*',
            $vendorTable.'.name as vendor_name',
            $vendorTable.'.service_fee',
            $categoryTable.'.name as category_name',
            DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
            DB::raw("(CASE WHEN $productTable.status = -9 THEN '已下架' WHEN $productTable.status = -3 THEN '停售中' WHEN $productTable.status = -2 THEN '審核失敗' WHEN $productTable.status = -1 THEN '未送審' WHEN $productTable.status = 0 THEN '送審中' WHEN $productTable.status = 1 THEN '上架中' WHEN $productTable.status = 2 THEN '送審中' END) as status_name"),
            DB::raw("(CASE WHEN $vendorTable.is_on = 1 THEN '啟用中' ELSE '停用中' END) as vendor_status"),
            DB::raw("(CASE WHEN $productModelTable.name is null THEN $productTable.model_name ELSE $productModelTable.name END) as model_name"),
            DB::raw("(CASE WHEN $productTable.is_del = 1 THEN 'V' WHEN $productModelTable.is_del = 1 THEN 'V' END) as is_del"),
            $productModelTable.'.id as product_model_id',
            $productModelTable.'.gtin13',
            $productModelTable.'.sku',
            $productModelTable.'.digiwin_no',
            $productModelTable.'.quantity',
            $productModelTable.'.safe_quantity',
        ]);
        $products = $products->orderBy($productTable.'.id','asc')->get();

        return $products;
    }

    protected function getProductPackageData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        $products = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->where($productModelTable.'.sku','like',"%BOM%")
        ->where($productTable.'.is_del',0);

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $products = $products->whereIn($productTable.'.id',$request['id']) : '';
        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }

        if(isset($status)){
            $st = explode(',',$status);
            $products = $products->whereIn('status',$st);
        }


        !empty($vendorId) ? $products = $products->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')->where($vendorTable.'.id',$vendorId) : '';
        !empty($categoryId) ? $products = $products->where($productTable.'.category_id',$categoryId) : '';
        !empty($keyword) ? $products = $products->where($productTable.'.name','like',"%$keyword%") : '';

        if (!isset($list)) {
            $list = 50;
        }

        $products = $products->select([
            $productTable.'.*',
            'vendor_id' => VendorDB::whereColumn($vendorTable.'.id',$productTable.'.vendor_id')->select('id')->limit(1),
            'vendor_name' => VendorDB::whereColumn($vendorTable.'.id',$productTable.'.vendor_id')->select('name')->limit(1),
        ]);

        if($type == 'index'){
            $products = $products->orderBy($productTable.'.id', 'desc')->paginate($list);
        }elseif($type == 'show'){
            if(isset($request['product_id'])){
                $products = $products->findOrFail($request['product_id']);
            }else{
                $products = $products->findOrFail($request['id']);
            }
        }else{
            $products = $products->orderBy($productTable.'.id', 'desc')->get();
        }
        return $products;
    }

    protected function makeSku($input){
        if(isset($input['sku'])){
            $output['sku'] = $input['sku'];
        }else{
            //sku的編碼方式 EC 0001 000001
            $output['sku'] = "EC" . str_pad($input['vendor_id'],5,'0',STR_PAD_LEFT) . str_pad($input['product_model_id'],6,'0',STR_PAD_LEFT);
        }

        //digiwin_no的編碼方式
        $digiwinNo="5";
        $countryId = $input['from_country_id'];
        $country = CountryDB::find($countryId);
        !empty($country) ? $digiwinNo .= strtoupper($country->lang) : $digiwinNo .= 'TW'; //語言代碼 1:tw, 5:jp
        $digiwinNo .= "A".str_pad($input['vendor_id'],5,"0",STR_PAD_LEFT);

        // 找出product_models與product_id跟vendor_id關聯的總數
        $vendorProductModelCounts = ProductModelDB::where('id','<=',$input['product_model_id'])
            ->whereIn('product_id', ProductDB::where('vendor_id',$input['vendor_id'])->select('id')->get())
            ->count();

        //鼎新編碼原則（包括單品與組合商品）
        if(substr($output['sku'],0,3)=="BOM"){
            $digiwinNo .= "B".str_pad(base_convert($vendorProductModelCounts, 10, 36),3,"0",STR_PAD_LEFT);
        }else{
            $digiwinNo .= str_pad(base_convert($vendorProductModelCounts, 10, 36),4,"0",STR_PAD_LEFT);
        }

        $output['digiwin_no'] = strtoupper($digiwinNo);
        return $output;
    }
}
