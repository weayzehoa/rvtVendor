<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCurationImage as CurationImageDB;

class iCarryCurationImageLang extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation_image_langs';
    protected $fillable = [
        'curation_image_id',
        'main_title',
        'sub_title',
        'caption',
        'modal_content',
        'lang',
    ];

    public function curationImage(){
        return $this->belongsTo(CurationImageDB::class,'curation_image_id','id');
    }
}
