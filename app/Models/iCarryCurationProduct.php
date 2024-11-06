<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductImage as ProductImageDB;
use DB;

class iCarryCurationProduct extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation_products';

    protected $fillable = [
        'curation_id',
        'product_id',
        'sort',
    ];

    public function curation(){
        return $this->belongsTo(CurationDB::class,'curation_id','id');
    }
    public function langs()
    {
        return $this->hasMany(ProductLangDB::class,'product_id','product_id')
                ->select([
                    'product_id',
                    'lang',
                    'name',
                    'curation_text_top',
                    'curation_text_bottom',
                ]);
    }
    public function data(){
        return $this->belongsTo(ProductDB::class,'product_id','id')
                ->join('vendors','vendors.id','products.vendor_id')->where([['products.status',1],['vendors.is_on',1]])
                ->select([
                    'products.id',
                    'products.name',
                    'products.curation_text_top',
                    'products.curation_text_bottom',
                    'products.fake_price',
                    'products.price',
                    'products.status',
                    'vendors.name as vendor_name',
                ]);
    }

    public function image()
    {
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class,'product_id','product_id')
                ->where('is_on',1)->orderBy('sort','asc')->select([
                    'product_id',
                    DB::raw("CONCAT('$host',filename) as filename"),
                ]);
    }

    public function curationImage()
    {
        return $this->hasOne(ProductImageDB::class,'product_id','product_id')
                ->where('is_on',1)->orderBy('sort','asc');
    }
}
