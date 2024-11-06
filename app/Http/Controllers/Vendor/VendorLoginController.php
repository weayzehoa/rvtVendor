<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLoginLog as VendorLoginLogDB;
use Illuminate\Support\Str;
use Session;
use Carbon\Carbon;
use App\Jobs\SendEmailJob;

class VendorLoginController extends Controller
{
    // 先經過 middleware 檢查
    public function __construct()
    {
        $this->middleware('guest:vendor', ['except' => ['showLoginForm','logout','forgetForm']]);
    }

    // 顯示 vendor login form 表單視圖
    public function showLoginForm()
    {
        $account = request()->account;
        $icarryToken = request()->icarryToken;
        // 直接從iCarry後台過來不檢查商家或帳號是否啟用
        if(!empty($account) && !empty($icarryToken)){
            $adminUser = VendorAccountDB::where([['account',$account],['icarry_token',$icarryToken]])->first();
            if (!empty($adminUser)) {
                //更新token
                $adminUser->update(['icarry_token' => strtoupper(str_replace('-','',Str::uuid()->toString()))]);
                //登入
                auth('vendor')->login($adminUser);
                // 驗證無誤 記錄後轉入 dashboard
                $log = VendorLoginLogDB::create([
                    'vendor_account_id' => $adminUser->id,
                    'result' => "iCarry後台管理者登入。",
                    'ip' => request()->ip(),
                ]);
                return redirect()->intended(route('vendor.dashboard'));
            }
        }
        return view('vendor.login');
    }

    // 登入
    public function login(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account'   => 'required',
            'password' => 'required',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);
        $message = null;
        // 檢驗帳號密碼權限及商家是否開啟
        $password = sha1($request->password);
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        $checkVendor = VendorAccountDB::join($vendorTable,$vendorTable.'.id',$vendorAccountTable.'.vendor_id')
        ->where([
            [$vendorAccountTable.'.account',$request->account],
            // [$vendorTable.'.is_on',1],
        ])->first();
        if (!empty($checkVendor)) {
            $adminUser = VendorAccountDB::where('account',$request->account)->first();
            if($adminUser->is_on != 1){
                $message = "帳號已被停用！請聯繫 iCarry 管理員。";
                Session::put('error',$message);
                $log = VendorLoginLogDB::create([
                    'vendor_account_id' => $adminUser->id,
                    'account' => $adminUser->account,
                    'result' => "登入失敗，該帳號 $adminUser->account 已被停用。",
                    'ip' => $request->ip(),
                ]);
            }elseif($adminUser->lock_on >= 10){
                $message = "帳號已被鎖定！請聯繫 iCarry 管理員。";
                Session::put('error',$message);
                $log = VendorLoginLogDB::create([
                    'vendor_account_id' => $adminUser->id,
                    'account' => $adminUser->account,
                    'result' => "登入失敗，該帳號 $adminUser->account 已被鎖定。",
                    'ip' => $request->ip(),
                ]);
            }else{
                //將帳號密碼送去guard驗證登入
                if (Auth::guard('vendor')->attempt(['account' => $request->account, 'password' => $request->password])) {
                    $adminUser->update(['lock_on' => 0]);
                    Auth::guard('vendor')->login($adminUser);
                    // 驗證無誤 記錄後轉入 dashboard
                    $log = VendorLoginLogDB::create([
                        'vendor_account_id' => $adminUser->id,
                        'account' => $adminUser->account,
                        'result' => "$adminUser->account 登入成功。",
                        'ip' => $request->ip(),
                    ]);
                    Session::put('success',"登入成功。");
                    return redirect()->intended(route('vendor.dashboard'));
                }else{
                    $adminUser->increment('lock_on');
                    if($adminUser->lock_on <= 9){
                        $remind = 10 - $adminUser->lock_on;
                        $message = "登入失敗第 $adminUser->lock_on 次，剩下 $remind 次機會。";
                        Session::put('error',$message);
                        $log = VendorLoginLogDB::create([
                            'vendor_account_id' => $adminUser->id,
                            'account' => $adminUser->account,
                            'result' => "登入失敗第 $adminUser->lock_on 次，輸入的密碼 $request->password 錯誤。",
                            'ip' => $request->ip(),
                        ]);
                    }else{
                        $message = "登入失敗超過 10 次，帳號已被鎖定！請聯繫 iCarry 管理員。";
                        Session::put('error',$message);
                        $log = VendorLoginLogDB::create([
                            'vendor_account_id' => $adminUser->id,
                            'account' => $adminUser->account,
                            'result' => "登入失敗超過 $adminUser->lock_on 次，帳號已被鎖定。",
                            'ip' => $request->ip(),
                        ]);
                    }
                }
            }
        }else{
            $message = "使用者名稱、密碼錯誤或無權限。";
            Session::put('error',$message);
            $log = VendorLoginLogDB::create([
                'vendor_account_id' => null,
                'result' => "$request->account 不存在/被停用或商家未啟用。",
                'account' => $request->account,
                'ip' => $request->ip(),
            ]);
        }

        // 驗證失敗 返回並拋出表單內容 只拋出 account 欄位資料
        // 只顯示訊息 [使用者名稱、密碼錯誤或無權限] 為了不讓別人知道到底商家或帳號是否存在
        return redirect()->back()->withInput($request->only('account'))->withErrors(['account' => $message]);
    }

    // 登出
    public function logout()
    {
        $adminuser = VendorAccountDB::find(Auth::guard('vendor')->id());
        // 紀錄行為
        $log = VendorLoginLogDB::create([
            'vendor_account_id' => $adminuser->id,
            'result' => "$adminuser->account 登出成功。",
            'account' => $adminuser->account,
            'ip' => request()->ip(),
        ]);
        // 登出
        Auth::guard('vendor')->logout();
        return redirect('/');
    }

    // 忘記密碼表單
    public function forgetForm()
    {
        return view('vendor.forget');
    }

    // 忘記密碼
    public function forget(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account'   => 'required',
            'email' => 'required|email',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);
        $vendorAccount = VendorAccountDB::where([['account',$request->account],['email',$request->email]])->first();
        if(!empty($vendorAccount)){
            $code = rand(100000,999999);
            $otpTime = Carbon::now()->addMinutes(10);
            Session::put('accountData',['account'=>$vendorAccount->account, 'email'=>$vendorAccount->email,'otpTime'=>$otpTime]);
            $vendorAccount->update(['otp' => $code, 'otp_time' => $otpTime]);

            $param['subject'] = 'iCarry商家後台密碼重置驗證碼';
            $param['model'] = 'resetPassWordMailBody';
            $param['from'] = 'icarry@icarry.me'; //寄件者
            $param['name'] = 'iCarry商家後台管理系統'; //寄件者名字
            $param['to'] = [$vendorAccount->email];
            $param['otp'] = $code;
            SendEmailJob::dispatchNow($param);

            Session::put('info',"驗證碼已傳送至您的信箱中，請輸入正確驗證碼重設您的密碼。");


            return redirect()->route('vendor.reset');
        }
        return redirect()->back()->withInput($request->only('account'))->withErrors(['account' => '帳號與Email不符合，請輸入設定於iCarry的信箱。']);
    }


    // 重設表單
    public function resetForm()
    {
        $data = Session::get('accountData');
        $account = $data['account'];
        $email = $data['email'];
        $otpTime = $data['otpTime'];
        $compact = ['otpTime','account','email'];
        return view('vendor.reset',compact($compact));
    }

    // 重設密碼
    public function reset(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account' => 'required',
            'email' => 'required|email',
            'newpass' => 'required|different:oldpass|confirmed',
            'newpass_confirmation' => 'required|different:oldpass',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);
        $account = VendorAccountDB::where([['account',$request->account],['email',$request->email]])->first();
        if(!empty($account)){
            if(strtotime($account->otp_time) > strtotime(date('Y-m-d H:i:s'))){
                if($account->otp == $request->otp){
                    $password = sha1($request->newpass);
                    $account->update(['pwd' => $password,'password' => $password, 'lock_on' => 0]);
                    Session::put('success','密碼更新完成，請使用新密碼登入。');
                    return redirect()->route('vendor.login');
                }else{
                    return redirect()->back()->withErrors(['otp' => '驗證碼錯誤。']);
                }
            }else{
                return redirect()->back()->withErrors(['otp' => '驗證碼已過期。請回登入頁重新取得驗證碼。']);
            }
        }
        return redirect()->back();
    }
}
