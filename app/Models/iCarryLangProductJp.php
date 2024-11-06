<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryLangProductJp extends Model
{
    use HasFactory;
    protected $connection = 'icarryLang';
    protected $table = 'product_jp';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'id',
        'vendor_id',
        'category_id',
        'unit_name_id',
        'from_country_id',
        'name',
        'export_name_en',
        'brand',
        'serving_size',
        'shipping_methods',
        'price',
        'gross_weight',
        'net_weight',
        'title',
        'intro',
        'model_name',
        'model_type',
        'is_tax_free',
        'specification',
        'verification_reason',
        'status',
        'is_hot',
        'hotel_days',
        'airplane_days',
        'storage_life',
        'fake_price',
        'TMS_price',
        'allow_country',
        'vendor_price',
        'unable_buy',
        'pause_reason',
        'tags',
        'is_del',
        'pass_time',
        'curation_text_top',
        'curation_text_bottom',
        'service_fee_percent',
        'package_data',
        'new_photo1',
        'new_photo2',
        'new_photo3',
        'new_photo4',
        'new_photo5',
    ];
}
