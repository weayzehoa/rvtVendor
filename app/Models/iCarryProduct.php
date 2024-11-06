<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use DB;

class iCarryProduct extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $fillable = [
        'vendor_id',
        'category_id',
        'sub_categories',
        'unit_name',
        'unit_name_id',
        'from_country_id',
        'allow_country_ids',
        'name',
        'eng_name',
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
        'type',
        'direct_shipment',
        'digiwin_product_category',
        'vendor_earliest_delivery_date',
        'vendor_latest_delivery_date',
        'shipping_fee_category_id',
        'ticket_price',
        'ticket_group',
        'ticket_memo',
    ];

    public function models(){
        return $this->hasMany(ProductModelDB::class,'product_id','id')->where('is_del',0);
    }
    public function vendor(){
        return $this->belongsTo(VendorDB::class, 'vendor_id', 'id');
    }

    public function category(){
        return $this->belongsTo(CategoryDB::class, 'category_id', 'id');
    }

    public function image(){
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class,'product_id','id')
        ->select([
            '*',
            DB::raw("CONCAT('$host',filename) as filename"),
        ])->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','asc');
    }

    public function images(){
        $host = env('AWS_FILE_URL');
        return $this->hasMany(ProductImageDB::class,'product_id','id')
        ->select([
            '*',
            DB::raw("CONCAT('$host',filename) as filename"),
        ])->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','asc');
    }

    public function packages(){
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $productPackageTable = env('DB_ICARRY').'.'.(new ProductPackageDB)->getTable();
        return $this->hasMany(ProductPackageDB::class, 'product_id', 'id')
            ->with('lists')
            ->join($productModelTable,$productModelTable.'.id',$productPackageTable.'.product_model_id')
            ->select([
                $productPackageTable.'.*',
                $productModelTable.'.name',
                $productModelTable.'.name_en',
                $productModelTable.'.name_jp',
                $productModelTable.'.name_kr',
                $productModelTable.'.name_th',
                $productModelTable.'.sku',
                $productModelTable.'.quantity',
                $productModelTable.'.safe_quantity',
                $productModelTable.'.vendor_product_model_id',
            ]);
    }

    public function packagesWithTrashed(){
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $productPackageTable = env('DB_ICARRY').'.'.(new ProductPackageDB)->getTable();
        return $this->hasMany(ProductPackageDB::class, 'product_id', 'id')
            ->with('lists')
            ->join($productModelTable,$productModelTable.'.id',$productPackageTable.'.product_model_id')
            ->select([
                $productPackageTable.'.*',
                $productModelTable.'.name',
                $productModelTable.'.name_en',
                $productModelTable.'.name_jp',
                $productModelTable.'.name_kr',
                $productModelTable.'.name_th',
                $productModelTable.'.sku',
                $productModelTable.'.quantity',
                $productModelTable.'.safe_quantity',
                $productModelTable.'.vendor_product_model_id',
            ])->groupBy('product_model_id')->withTrashed();
    }
}
