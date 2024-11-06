<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\VendorShop as VendorShopDB;
use App\Http\Requests\ChangePassWordRequest;
use App\Http\Requests\VendorAccountsRequest;
use Illuminate\Support\Str;
use Session;

class AccountController extends Controller
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
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        $accounts = VendorAccountDB::where('vendor_id',$this->vendorId)->orderBy('id','desc')->get();
        return view('vendor.account.index', compact('accounts','vendorId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->vendorId = $vendorId = auth('vendor')->user()->vendor_id;
        return view('vendor.account.show', compact('vendorId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //整理資料
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $data['password'] = sha1($data['password']); //密碼使用舊方式sha1編碼
        $data['icarry_token'] = Str::uuid()->toString(); //跨站認證碼
        //檢查account是否存在
        if( VendorAccountDB::where('account',$data['account'])->count() > 0){
            Session::put('error', '該帳號已存在');
        }else{
            if(!empty($data['account']) && !empty($data['name']) && !empty($data['email'])){
                $account = VendorAccountDB::create($data);
                $message = '商家帳號 '.$request->account.' 已建立成功！';
                Session::put('success', $message);
            }else{
                $message = null;
                empty($data['account']) ? $message .= " 帳號不可為空值。 " : '';
                empty($data['name']) ? $message .= " 名字不可為空值。 " : '';
                empty($data['email']) ? $message .= " 電子郵件不可為空值。 " : '';
                Session::put('error',$message);
            }
        }
        return redirect()->back();
    }

    public function storeOld(VendorAccountsRequest $request)
    {
        //整理資料
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $data['password'] = sha1($data['password']); //密碼使用舊方式sha1編碼
        $data['icarry_token'] = Str::uuid()->toString(); //跨站認證碼
        //檢查account是否存在
        if( VendorAccountDB::where('account',$data['account'])->count() > 0){
            return redirect()->back()->withInput($request->all())->withError('該帳號已存在，請重新輸入');
        }
        //新增後跳轉
        $account = VendorAccountDB::create($data);
        $message = '商家帳號 '.$request->account.' 已建立成功！';
        Session::put('success', $message);
        return redirect()->route('vendor.account.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $account = VendorAccountDB::findOrFail($id);
        return view('vendor.account.show', compact('account'));
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
        //找出該筆資料
        $account = VendorAccountDB::findOrFail($id);
        //整理資料
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $data['password'] == null ? $data['password'] = $account->password : $data['password'] = sha1($data['password']);

        //檢查是否變更帳號，若有檢查是否已經有相同的帳號存在
        if($data['account'] != $account->account){
            if(VendorAccountDB::where('account',$data['account'])->count() > 0){
                return redirect()->back()->withErrors(['account' => '該帳號已經存在，請重新輸入新的帳號']);
            }
        }
        //更新
        $account->update($data);
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
        $account = VendorAccountDB::find($id)->delete();
        return redirect()->back();
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = 1 : $is_on = 0;
        VendorAccountDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
    /*
        變更密碼表單
    */
    public function changePassWordForm()
    {
        return view('vendor.account.change_password');
    }
    /*
        變更密碼
    */
    public function changePassWord(ChangePassWordRequest $request)
    {
        $data = $request->all();
        $admin = VendorAccountDB::find($request->id);
        if(!empty($admin)){
            if(!empty($data['oldpass'])){
                $oldpass = sha1($request->oldpass);
                if($admin->password == $oldpass){
                    $data['password'] = $data['pwd'] = sha1($request->newpass);
                    $admin->update($data);
                    Session::put('success','密碼變更成功');
                }else{
                    Session::put('error','舊密碼輸入錯誤。');
                }
            }else{
                $admin->update($data);
            }
        }
        return redirect()->back();
    }

    public function getAccount($id)
    {
        $account = VendorAccountDB::find($id);
        if(!empty($account)){
            return response()->json($account);
        }
        return null;
    }

    public function updateAccount(Request $request)
    {
        if(!empty($request->id)){
            $id = $request->id;
            //找出該筆資料
            $account = VendorAccountDB::find($id);
            if(!empty($account)){
                //整理資料
                $data = $request->all();
                if(!empty($data['account']) && !empty($data['name']) && !empty($data['email'])){
                    isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
                    $data['password'] == null ? $data['password'] = $account->password : $data['password'] = sha1($data['password']);

                    //檢查是否變更帳號，若有檢查是否已經有相同的帳號存在
                    if($data['account'] != $account->account){
                        if(VendorAccountDB::where('account',$data['account'])->count() > 0){
                            Session::put('error','該帳號已經存在');
                            return redirect()->back();
                        }
                    }
                    //更新
                    $account->update($data);
                    $message = '商家帳號 '.$request->account.' 修改成功！';
                    Session::put('success', $message);
                }else{
                    $message = null;
                    empty($data['account']) ? $message .= " 帳號不可為空值。 " : '';
                    empty($data['name']) ? $message .= " 名字不可為空值。 " : '';
                    empty($data['email']) ? $message .= " 電子郵件不可為空值。 " : '';
                    Session::put('error',$message);
                }
            }
        }
        return redirect()->back();
    }

}
