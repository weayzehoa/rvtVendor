<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendorLang as VendorLangDB;
use DB;

class iCarryVendor extends Model
{
    use HasFactory;

    protected $connection = 'icarry';
    protected $table = 'vendor';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'name',
        'company',
        'VAT_number',
        'boss',
        'contact_person',
        'tel',
        'fax',
        'email',
        'notify_email',
        'bill_email',
        'categories',
        'address',
        'shipping_setup',
        'shipping_verdor_percent',
        'is_on',
        'summary',
        'description',
        'shopping_notice',
        'platforms',
        'editor',
        'service_fee',
        'cover',
        'new_cover',
        'img_cover',
        'logo',
        'new_logo',
        'img_log',
        'shipping_self',
        'site_cover',
        'new_site_cover',
        'img_site',
        'single_shop',
        'factory_address',
        'product_sold_country',
        'create_time',
        'update_time',
        'pause_start_date',
        'pause_end_date'
    ];

    public function langs(){
        return $this->hasMany(VendorLangDB::class,'vendor_id','id');
    }

    // public function shops(){
    //     return $this->hasMany(VendorShopDB::class);
    // }

    // public function accounts(){
    //     return $this->hasMany(VendorAccountDB::class);
    // }

    // public function products(){
    //     return $this->hasMany(ProductDB::class);
    // }

    // public function orderItems(){
    //     return $this->hasMany(OrderItemDB::class);
    // }

    // public function orderShippings(){
    //     return $this->hasMany(OrderVendorShippingDB::class);
    // }
}
