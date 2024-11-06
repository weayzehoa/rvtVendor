<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use DB;

class iCarryCurationVendor extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation_vendors';
    protected $fillable = [
        'curation_id',
        'vendor_id',
        'sort',
    ];

    public function curation(){
        return $this->belongsTo(CurationDB::class,'curation_id','id');
    }
    public function langs()
    {
        return $this->hasMany(VendorLangDB::class,'vendor_id','vendor_id')
                ->select([
                    'vendor_id',
                    'lang',
                    'name',
                    'curation',
                ]);
    }
    public function data(){
        $host = env('AWS_FILE_URL');
        return $this->belongsTo(VendorDB::class,'vendor_id','id')->where('is_on',1)
                ->select([
                    'id',
                    'name',
                    DB::raw("CONCAT('$host',img_logo) as img_logo"),
                    DB::raw("CONCAT('$host',img_cover) as img_cover"),
                    'curation',
                    'is_on',
                ]);
    }
}
