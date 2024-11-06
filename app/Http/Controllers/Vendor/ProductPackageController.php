<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Http\Requests\ProductsRequest;
use App\Http\Requests\ProductsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use App\Exports\ProductPackagesExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ProductPackageController extends Controller
{
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
        $vendorId = $this->vendorId = auth('vendor')->user()->vendor_id;
        $compact = [];
        $appends = [];

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

        //限制 vendor 與 model_type = 3的商品
        $products = ProductDB::with('packages')
            ->where([['model_type',3],['vendor_id',$this->vendorId]]);

        if (!empty($keyword)) {
            $productIds = ProductPackageListDB::join('product_models','product_models.id','product_package_lists.product_model_id')
                ->join('product_packages','product_packages.id','product_package_lists.product_package_id')
                ->where('product_models.sku','like',"%$keyword%")->select('product_packages.product_id')->get()->pluck('product_id')->all();
            $products = $products->join('product_models','product_models.product_id','products.id')
                ->where(function ($query) use ($keyword,$productIds) {
                    $query->orWhere('products.name','like',"%$keyword%")
                    ->orWhere('product_models.sku','like',"%$keyword%")
                    ->orWhere('product_models.name','like',"%$keyword%")
                    ->orWhereIn('products.id',$productIds);
            });
        }

        $products = $products->select(
            'products.id',
            'products.name',
            'products.status',
            'products.serving_size',
            'products.price',
        )->orderBy('id','desc')->paginate($list);

        $compact = array_merge($compact, ['products','appends']);
        return view('vendor.product.package_index',compact($compact));
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
}
