<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;

class OldVendorLangTh extends Model
{
    use HasFactory;
    protected $connection = 'icarryLang';
    protected $table = 'vendor_th';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'id',
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
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }
}
