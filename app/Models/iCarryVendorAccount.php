<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\iCarryVendor as VendorDB;

class iCarryVendorAccount extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    protected $connection = 'icarry';
    protected $table = 'vendor_account';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'account',
        'pwd',
        'password',
        'name',
        'shop_id',
        'shop_admin',
        'pos_admin',
        'email',
        'is_on',
        'editor',
        'vendor_id',
        'create_time',
        'update_time',
        'icarry_token',
        'lock_on',
        'otp',
        'otp_time',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pwd',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    /**
     * 覆蓋Laravel中預設的getAuthPassword方法, 返回使用者的password和salt欄位
     * @return array
     */
    public function getAuthPassword()
    {
        return ['password' => $this->attributes['password'], 'salt' => ''];
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }
}
