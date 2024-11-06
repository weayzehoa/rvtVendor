<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryOldCuration as OldCurationDB;
use App\Models\iCarryCurationLang as CurationLangDB;
use App\Models\iCarryCurationImage as CurationImageDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryUserFavorite as UserFavoriteDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryCurationVendor as CurationVendorDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProduct as ProductDB;
use DB;

class iCarryCuration extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curations';
    protected $fillable = [
        'category',
        'vendor_id',
        'main_title',
        'show_main_title',
        'main_title_background',
        'show_main_title_background',
        'sub_title',
        'show_sub_title',
        'background_color',
        'background_image',
        'background_css',
        'show_background_type',
        'columns',
        'rows',
        'caption',
        'type',
        'url',
        'old_url',
        'url_open_window',
        'show_url',
        'start_time',
        'end_time',
        'is_on',
        'sort',
        'old_curation_id',
        'old_text_layout',
    ];

    protected $langs;
    protected $lang = '';
    protected $awsFileUrl;

    public function oldCuration(){
        return $this->belongsTo(OldCurationDB::class,'old_curation_id','id');
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }

    public function langs()
    {
        return $this->hasMany(CurationLangDB::class,'curation_id','id')->select([
            'curation_id',
            'lang',
            'main_title',
            'sub_title',
            'caption',
        ]);
    }

    public function images()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $images = $this->hasMany(CurationImageDB::class,'curation_id','id')
                ->where('style','image')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'open_method',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    'url_open_window',
                    'modal_content',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort'
                ])->orderBy('sort','asc');

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $images = $images->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
                DB::raw("(CASE WHEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.modal_content END) as modal_content"),
            ]);
        }

        return $images;
    }

    public function blocks()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $blocks = $this->hasMany(CurationImageDB::class,'curation_id','id')
                ->where('style','block')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    'url_open_window',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                    'row',
                ])->orderBy('sort','asc');

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $blocks = $blocks->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
            ]);
        }

        return $blocks;
    }

    public function noWordBlocks()
    {
        return $this->hasMany(CurationImageDB::class,'curation_id','id')
                ->where('style','nowordblock')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'url',
                    'url_open_window',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                    'row',
                ])->orderBy('sort','asc');
    }

    public function events()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $events = $this->hasMany(CurationImageDB::class,'curation_id','id')
                ->where('style','event')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                ])->orderBy('sort','asc');

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $events = $events->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
                ]);
        }

        return $events;
    }

    public function vendors()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorLangTable = env('DB_ICARRY').'.'.(new VendorLangDB)->getTable();
        $curationTable = env('DB_ICARRY').'.'.(new CurationDB)->getTable();
        $curationVendorTable = env('DB_ICARRY').'.'.(new CurationVendorDB)->getTable();
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $vendors = $this->hasMany(CurationVendorDB::class,'curation_id','id')
                ->join($vendorTable,$vendorTable.'.id',$curationVendorTable.'.vendor_id')
                ->where($vendorTable.'.is_on',1)
                ->select([
                    $curationVendorTable.'.id',
                    $curationVendorTable.'.curation_id',
                    $curationVendorTable.'.vendor_id',
                    $curationVendorTable.'.sort',
                    $vendorTable.'.name',
                    DB::raw("(CASE WHEN $vendorTable.img_logo is not null THEN CONCAT('$this->awsFileUrl',$vendorTable.img_logo) END) as img_logo"),
                    DB::raw("(CASE WHEN $vendorTable.img_cover is not null THEN CONCAT('$this->awsFileUrl',$vendorTable.img_cover) END) as img_cover"),
                    $vendorTable.'.curation',
                    $vendorTable.'.is_on',
                ])->orderBy($curationVendorTable.'.sort','asc');

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $vendors = $vendors->addSelect([
                DB::raw("(CASE WHEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) != '' THEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) ELSE $vendorTable.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT curation from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) != '' THEN (SELECT curation from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) ELSE $vendorTable.curation END) as curation"),
            ]);
        }

        return $vendors;
    }

    //前後台共用，前台判斷參數帶語言資料，後台用with語言資料加快速度，前台unset掉即可
    public function products()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $this->userId = request()->userId;
        $curationTable = env('DB_ICARRY').'.'.(new CurationDB)->getTable();
        $curationVendorTable = env('DB_ICARRY').'.'.(new CurationVendorDB)->getTable();

        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorLangTable = env('DB_ICARRY').'.'.(new VendorLangDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productLangTable = env('DB_ICARRY').'.'.(new ProductLangDB)->getTable();
        $productImageTable = env('DB_ICARRY').'.'.(new ProductImageDB)->getTable();
        $curationProductTable = env('DB_ICARRY').'.'.(new CurationProductDB)->getTable();
        $userFavoriteTable = env('DB_ICARRY').'.'.(new UserFavoriteDB)->getTable();
        $products = $this->hasMany(CurationProductDB::class,'curation_id','id')->with('langs')
            ->join($productTable,$productTable.'.id',$curationProductTable.'.product_id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
            ->where($vendorTable.'.is_on',1)
            ->whereIn($productTable.'.status',[1,-3])
            ->select([
                $curationProductTable.'.id',
                $curationProductTable.'.curation_id',
                $curationProductTable.'.sort',
                $productTable.'.id as product_id',
                $productTable.'.name',
                $productTable.'.curation_text_top',
                $productTable.'.curation_text_bottom',
                DB::raw("(CASE WHEN $productTable.fake_price > 0 THEN $productTable.fake_price END) as fake_price"),
                $productTable.'.price',
                $productTable.'.status',
                $vendorTable.'.name as vendor_name',
                'image' => ProductImageDB::whereColumn($productTable.'.id', $productImageTable.'.product_id')->where($productImageTable.'.is_on',1)
                ->select(DB::raw("(CASE WHEN $productImageTable.filename is not null THEN (CONCAT('$this->awsFileUrl',$productImageTable.filename)) END) as image"))->orderBy($productImageTable.'.sort','asc')->limit(1),
            ])->orderBy($curationProductTable.'.sort','asc');
        if(!empty($this->userId)){
            $products = $products->addSelect([
                'is_favorite' => UserFavoriteDB::whereColumn($productTable.'.id', $userFavoriteTable.'.table_id')
                ->where($userFavoriteTable.'.user_id',$this->userId)
                ->where($userFavoriteTable.'table_name','product')->select([
                    DB::raw("(CASE WHEN count(table_id) > 0 THEN 1 ELSE 0 END)")
                ])->limit(1),
            ]);
        }
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) != '' THEN (SELECT name from $vendorLangTable where $vendorLangTable.vendor_id = $vendorTable.id and $vendorLangTable.lang = 'en' limit 1) ELSE $vendorTable.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from $productLangTable where $productLangTable.product_id = produc$productTablets.id and $productLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT name from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) != '' THEN (SELECT name from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) ELSE $productTable.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation_text_top from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_top from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_top from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) != '' THEN (SELECT curation_text_top from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) ELSE $productTable.curation_text_top END) as curation_text_top"),
                DB::raw("(CASE WHEN (SELECT curation_text_bottom from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_bottom from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_bottom from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) != '' THEN (SELECT curation_text_bottom from $productLangTable where $productLangTable.product_id = $productTable.id and $productLangTable.lang = 'en' limit 1) ELSE $productTable.curation_text_bottom END) as curation_text_bottom"),
            ]);
        }
        $products = $products->orderBy('sort','asc');
        return $products;
    }
}
