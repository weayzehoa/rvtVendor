<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Http\Requests\ProductImagesUploadRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;

class ProductImageController extends Controller
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
        $productImage = ProductImageDB::findOrFail($id);
        $productImage->delete();
        return redirect()->route('vendor.product.show',$productImage->product_id.'#product-image');
    }
    /*
        圖檔上傳
     */
    public function upload(ProductImagesUploadRequest $request)
    {
        // dd($request);
        //檢查表單是否有檔案
        if(!$request->hasFile('filename')){
            $message = "請選擇要上傳的檔案再按送出按鈕";
            Session::put('info',$message);
            return redirect()->back();
        }else{
            $this->storeFile($request);
            $message = "檔案上傳成功";
            Session::put('success',$message);
            return redirect()->route('vendor.product.show',$request->product_id.'#product-image');
        }
    }

    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/product/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file('filename');
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName = 'product_image_'.$request->id.'_'. Carbon::now()->timestamp . '.' . $ext;
        //變更尺寸寬高
        $reSizeWidth = 1440;
        $reSizeHeigh = 760;
        //檔案路徑名稱資料寫入資料庫
        ProductImageDB::create([
            'product_id' => $request->product_id,
            'filename' => $destPath.$fileName,
            'is_top' => 0,
            'is_on' => 1,
        ]);
        //重新排序
        $productImages = ProductImageDB::where('product_id',$request->product_id)->orderBy('is_top','desc')->orderBy('is_on','desc')->get();
        $i = 1;
        foreach ($productImages as $image) {
            $id = $image->id;
            ProductImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
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
    }
    /*
        置頂或取消置頂
     */
    public function top(Request $request)
    {
        $productImage = ProductImageDB::findOrFail($request->id);
        $productImage->fill(['is_top' => $request->is_top])->save();
        //重新排序
        $productImages = ProductImageDB::where('product_id',$productImage->product_id)->orderBy('is_top','desc')->orderBy('is_on','desc')->get();
        $i = 1;
        foreach ($productImages as $image) {
            $id = $image->id;
            ProductImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->route('vendor.product.show',$productImage->product_id.'#product-image');
    }

    /*
        啟用或禁用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = 1 : $is_on = 0;
        $productImage = ProductImageDB::findOrFail($request->id);
        $is_on == 0 ? $productImage->fill(['is_on' => 0,'is_top' => 0])->save() : $productImage->fill(['is_on' => $is_on])->save();
        //重新排序
        $productImages = ProductImageDB::where('product_id',$productImage->product_id)->orderBy('is_top','desc')->orderBy('is_on','desc')->get();
        $i = 1;
        foreach ($productImages as $image) {
            $id = $image->id;
            ProductImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->route('vendor.product.show',$productImage->product_id.'#product-image');
    }

    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $productImage = ProductImageDB::findOrFail($id);
        $up = ($productImage->sort) - 1.5;
        $productImage->fill(['sort' => $up])->save();

        $productImages = ProductImageDB::where('product_id',$productImage->product_id)->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($productImages as $image) {
            $id = $image->id;
            ProductImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->route('vendor.product.show',$productImage->product_id.'#product-image');
    }

    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $productImage = ProductImageDB::findOrFail($id);
        $up = ($productImage->sort) + 1.5;
        $productImage->fill(['sort' => $up])->save();

        $productImages = ProductImageDB::where('product_id',$productImage->product_id)->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($productImages as $image) {
            $id = $image->id;
            ProductImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->route('vendor.product.show',$productImage->product_id.'#product-image');
    }
}
