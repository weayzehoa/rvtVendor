<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationVendor as CurationVendorDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryCurationImage as CurationImageDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;
use App\Models\iCarryCurationLang as CurationLangDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryProduct as ProductDB;
use App\Http\Requests\CurationsRequest;
use App\Http\Requests\CurationsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use DB;

class CurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:vendor');
    }
    public function index()
    {
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        $appends = [];
        $compact = [];
        $curations = CurationDB::where([['category','vendor'],['vendor_id',$vendorId]]);
        $curations = $curations->orderBy('sort','asc')->paginate(15);
        $compact = array_merge($compact,['curations']);
        return view('vendor.curation.index',compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        return view('vendor.curation.show',compact('langs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        //資料處理
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $curation = CurationDB::create($data);
        $this->autoSort('vendor');
        //語言資料
        foreach($data['langs'] as $lang => $value){
            CurationLangDB::create([
                'curation_id' => $curation->id,
                'lang' => $lang,
                'main_title' => $value['main_title'],
                'sub_title' => $value['sub_title'],
            ]);
        }
        return redirect()->route('vendor.curation.show',$curation->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        $curation = CurationDB::findOrFail($id);
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        if($curation->langs){
            foreach ($curation->langs as $lang) {
                for ($i=0;$i<count($langs);$i++) {
                    if ($lang->lang == $langs[$i]['code']) {
                        $langs[$i]['data'] = $lang->toArray();
                    }
                }
            }
        }
        if($curation->type == 'product'){ //產品版型
            foreach ($curation->products as $product) {
                foreach($product->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['productdata'][$product->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        $products = ProductDB::where('vendor_id',$vendorId)->where('status',1)->select(['id','name'])->orderBy('create_time','desc')->get();
        return view('vendor.curation.show',compact('langs','curation','products'));

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
        $data = $request->all();
        //資料處理
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        //語言資料
        foreach($data['langs'] as $lang => $value){
            $find = CurationLangDB::where([['curation_id',$id],['lang',$lang]])->first();
            if($find){
                $find->update([
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                ]);
            }else{
                $find = CurationLangDB::create([
                    'curation_id' => $id,
                    'lang' => $lang,
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                ]);
            }
        }
        $curation = CurationDB::findOrFail($id)->update($data);
        return redirect()->back();
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
        手動排序
    */
    public function sort(Request $request)
    {
        $ids = $request->id;
        $sorts = $request->sort;
        if(count($ids) == count($sorts)){
            for($i=0;$i<count($ids);$i++){
                CurationDB::where('id',$ids[$i])->update(['sort' => $sorts[$i]]);
            }
        }
        return redirect()->back();
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $curation = CurationDB::findOrFail($id);
        $up = ($curation->sort) - 1.5;
        $curation->fill(['sort' => $up]);
        $curation->save();
        $this->autoSort('vendor');
        return redirect()->back();
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $curation = CurationDB::findOrFail($id);
        $up = ($curation->sort) + 1.5;
        $curation->fill(['sort' => $up]);
        $curation->save();
        $this->autoSort('vendor');
        return redirect()->back();
    }
    /*
        自動排序處理
    */
    public function autoSort($category)
    {
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        $curations = CurationDB::where([['category',$category],['vendor_id',$vendorId]])->orderBy('sort','asc')->get();
        $i = 1;
        foreach ($curations as $curation) {
            $id = $curation->id;
            CurationDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        CurationDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }

    public function getProducts(Request $request)
    {
        $products = ProductDB::join('vendors','vendors.id','products.vendor_id')->where('products.status',1);

        if($request->category){
            $products = $products->where('products.category_id',$request->category);
        }elseif($request->vendor){
            $products = $products->where('products.vendor_id',$request->vendor);
        }elseif($request->keyword){
            $keyword = $request->keyword;
            $products = $products->where(function ($query) use ($keyword) {
                $query->where('products.name', 'like', "%$keyword%")
                ->orWhere('vendors.name', 'like', "%$keyword%");
            });

        }else{
            return null;
        }

        //去除掉被選擇的商品
        if($request->ids){
            $products = $products->whereNotIn('products.id',$request->ids);
        }

        $products = $products->distinct()->select(['products.id','products.name'])->orderBy('products.created_at','desc')->get();
        return $products;
    }
}
