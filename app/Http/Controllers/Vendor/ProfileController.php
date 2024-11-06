<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\OldVendorLangEn as OldVendorLangEnDB;
use App\Models\OldVendorLangJp as OldVendorLangJpDB;
use App\Models\OldVendorLangKr as OldVendorLangKrDB;
use App\Models\OldVendorLangTh as OldVendorLangThDB;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;

use App\Http\Requests\VendorsRequest;

class ProfileController extends Controller
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
        $this->vendorId = auth('vendor')->user()->vendor_id;
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        $categories = CategoryDB::where('is_on',1)->get();
        foreach($categories as $category){
            if($category->name == '糕餅零食'){
                $category->color = 'warning';
            }elseif($category->name == '天然農特'){
                $category->color = 'primary';
            }elseif($category->name == '飲品茶葉'){
                $category->color = 'success';
            }elseif($category->name == '女人我最大'){
                $category->color = 'danger';
            }elseif($category->name == '最新上架'){
                $category->color = 'info';
            }elseif($category->name == '保健商品'){
                $category->color = 'secondary';
            }elseif($category->name == '文創生活'){
                $category->color = 'dark';
            }else{
                $category->color = 'warning';
            }
        }
        $vendor = VendorDB::find($this->vendorId);
        if(!empty($vendor)){
            $serviceFees = $this->serviceFee($vendor->service_fee);
            if($vendor->langs){
                foreach ($vendor->langs as $lang) {
                    for ($i=0;$i<count($langs);$i++) {
                        if ($lang->lang == $langs[$i]['code']) {
                            $langs[$i]['data'] = $lang->toArray();
                        }
                    }
                }
            }
        }
        return view('vendor.profile.show',compact('langs','categories','vendor','serviceFees'));
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
    public function update(VendorsRequest $request, $id)
    {
        $newBillMails = $newNotifyMails = $newMails = [];
        $vendor = VendorDB::find($id);
        if(!empty($vendor)){
            $data = $request->all();
            $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i"; //檢驗email規則
            if(!empty($data['email'])){
                $data['email'] = str_replace(['/',':',';','|'],[',',',',',',','],$data['email']);
                $mails = explode(',',$data['email']);
                for($i=0;$i<count($mails);$i++){
                    $mail = strtolower($mails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newMails[] = $mail;
                    };
                }
                if(count($newMails) > 0){
                    $newMails = array_unique($newMails);
                    $data['email'] = join(',',$newMails);
                }
            }
            if(!empty($data['notify_email'])){
                $data['notify_email'] = str_replace(['/',':',';','|'],[',',',',',',','],$data['notify_email']);
                $mails = explode(',',$data['notify_email']);
                for($i=0;$i<count($mails);$i++){
                    $mail = strtolower($mails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newNotifyMails[] = $mail;
                    };
                }
                if(count($newNotifyMails) > 0){
                    $newNotifyMails = array_unique($newNotifyMails);
                    $data['notify_email'] = join(',',$newNotifyMails);
                }
            }
            if(!empty($data['bill_email'])){
                $data['bill_email'] = str_replace(['/',':',';','|'],[',',',',',',','],$data['bill_email']);
                $mails = explode(',',$data['bill_email']);
                for($i=0;$i<count($mails);$i++){
                    $mail = strtolower($mails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newBillMails[] = $mail;
                    };
                }
                if(count($newBillMails) > 0){
                    $newBillMails = array_unique($newBillMails);
                    $data['bill_email'] = join(',',$newBillMails);
                }
            }
            $vendor->update($data);
            //語言資料
            if(isset($data['langs'])){
                foreach($data['langs'] as $lang => $value){
                    $find = VendorLangDB::where([['vendor_id',$id],['lang',$lang]])->first();
                    if($find){
                        $find->update([
                            'name' => $value['name'],
                            'summary' => $value['summary'],
                            'description' => $value['description'],
                        ]);
                    }else{
                        $find = VendorLangDB::create([
                            'vendor_id' => $id,
                            'lang' => $lang,
                            'name' => $value['name'],
                            'summary' => $value['summary'],
                            'description' => $value['description'],
                        ]);
                    }
                    $langData['name'] = $value['name'];
                    $langData['summary'] = $value['summary'];
                    $langData['description'] = $value['description'];
                    //舊語言資料更新
                    if($lang == 'en'){
                        $oldLangEnDB = OldVendorLangEnDB::find($id);
                        empty($oldLangEnDB) ? $oldLangEnDB = OldVendorLangEnDB::create($vendor->toArray()) : '';
                        $oldLangEnDB->update($langData);
                    }
                    if($lang == 'jp'){
                        $oldLangJpDB = OldVendorLangEnDB::find($id);
                        empty($oldLangJpDB) ? $oldLangJpDB = OldVendorLangEnDB::create($vendor->toArray()) : '';
                        $oldLangJpDB->update($langData);
                    }
                    if($lang == 'kr'){
                        $oldLangKrDB = OldVendorLangKrDB::find($id);
                        empty($oldLangKrDB) ? $oldLangKrDB = OldVendorLangKrDB::create($vendor->toArray()) : '';
                        $oldLangKrDB->update($langData);
                    }
                    if($lang == 'th'){
                        $oldLangThDB = OldVendorLangThDB::find($id);
                        empty($oldLangThDB) ? $oldLangThDB = OldVendorLangThDB::create($vendor->toArray()) : '';
                        $oldLangThDB->update($langData);
                    }
                }
            }
        }
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
        圖檔上傳
     */
    public function upload(Request $request)
    {
        //先檢查vendor是否存在
        $id = $request->id;
        $vendor = VendorDB::find($id);
        if(!empty($vendor)){
            //檢查表單是否有檔案
            if($request->hasFile('new_cover') || $request->hasFile('new_logo') || $request->hasFile('new_site_cover')){
                if($request->hasFile('new_cover')){
                    $request->rowName = 'new_cover';
                    $this->storeFile($request);
                }

                if($request->hasFile('new_logo')){
                    $request->rowName = 'new_logo';
                    $this->storeFile($request);
                }

                if($request->hasFile('new_site_cover')){
                    $request->rowName = 'new_site_cover';
                    $this->storeFile($request);
                }

                $message = "檔案上傳成功";
                Session::put('success',$message);
            }else{
                $message = "請選擇要上傳的檔案在按送出按鈕";
                Session::put('info',$message);
            }
        }
        return redirect()->back();
    }

    public function storeFile($request){
        $id = $request->id;
        //目的目錄
        $destPath = '/upload/vendor/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file($request->rowName);
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $time = Carbon::now()->timestamp;
        $fileName = $request->rowName.'_'.$request->id.'_'. $time . '.' . $ext;
        $sfileName = $request->rowName.'_'.$request->id.'_'. $time . '_s.' . $ext;
        //變更尺寸寬高
        if($request->rowName == 'new_cover' || $request->rowName == 'new_site_cover'){
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
        }else{
            $reSizeWidth = 540;
            $reSizeHeigh = 360;
        }
        //新的檔案名稱
        $request->rowName == 'new_cover' ? $columnName = 'img_cover' : '';
        $request->rowName == 'new_logo' ? $columnName = 'img_log' : '';
        $request->rowName == 'new_site_cover' ? $columnName = 'img_site' : '';
        //檔案路徑名稱資料寫入資料庫
        $vendor = VendorDB::find($id);
        $vendor->update([$columnName => $destPath.$fileName, $request->rowName => $destPath.$fileName]);
        $vendorData = $vendor->toArray();
        //檢查舊的語言資料是否存在, 建立或更新
        $oldLangEnDB = OldVendorLangEnDB::find($id);
        empty($oldLangEnDB) ? $oldLangEnDB = OldVendorLangEnDB::create($vendorData) : $oldLangEnDB->update($vendorData);
        $oldLangJpDB = OldVendorLangEnDB::find($id);
        empty($oldLangJpDB) ? $oldLangJpDB = OldVendorLangEnDB::create($vendorData) : $oldLangJpDB->update($vendorData);
        $oldLangKrDB = OldVendorLangKrDB::find($id);
        empty($oldLangKrDB) ? $oldLangKrDB = OldVendorLangKrDB::create($vendorData) : $oldLangKrDB->update($vendorData);
        $oldLangThDB = OldVendorLangThDB::find($id);
        empty($oldLangThDB) ? $oldLangThDB = OldVendorLangThDB::create($vendorData) : $oldLangThDB->update($vendorData);
        //將檔案搬至本地目錄
        $file->move(public_path().$destPath, $fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().$destPath.$fileName)
        ->width($reSizeWidth)
        ->height($reSizeHeigh)
        ->save(public_path().$destPath.$fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().$destPath.$fileName)
        ->width($reSizeWidth/4)
        ->height($reSizeHeigh/4)
        ->save(public_path().$destPath.$sfileName);
        //將檔案傳送至 S3
        //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
        Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
        Storage::disk('s3')->put($destPath.$sfileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
        //刪除本地檔案
        unlink(public_path().$destPath.$fileName);
    }
}
