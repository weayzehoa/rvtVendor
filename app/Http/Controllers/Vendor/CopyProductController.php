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
use App\Http\Requests\ProductsRequest;
use App\Http\Requests\ProductsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use DB;
use Validator;

use App\Traits\ProductFunctionTrait;

class CopyProductController extends Controller
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
        //
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
    public function store(ProductsRequest $request)
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
        $copy = true;
        $viewOnly = 0;
        $shippingMethods = ShippingMethodDB::all();
        $categories = CategoryDB::where('is_on',1)->get();
        // $countries = CountryDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->get();
        $unitNames = ProductUnitNameDB::all();
        $product = ProductDB::findOrFail($id);
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
        return view('vendor.product.show',compact('langs','shippingMethods','subCategories','categories','product','unitNames','countries','productImages','copy','oldImages','viewOnly'));
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

    public function getSubCate(Request $request)
    {
        if(!empty($request->category_id)){
            $subCategories = SubCategoryDB::where([['category_id',$request->category_id],['is_on',1]])->get();
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
}
