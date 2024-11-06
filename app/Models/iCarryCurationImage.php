<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;

class iCarryCurationImage extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation_images';
    protected $fillable = [
        'style',
        'curation_id',
        'open_method',
        'main_title',
        'show_main_title',
        'sub_title',
        'show_sub_title',
        'caption',
        'text_position',
        'row',
        'url',
        'old_url',
        'url_open_window',
        'modal_content',
        'image',
        'sort',
    ];

    public function langs()
    {
        return $this->hasMany(CurationImageLangDB::class,'curation_image_id','id')
                ->select([
                    'curation_image_id',
                    'lang',
                    'main_title',
                    'sub_title',
                    'modal_content',
                ]);
    }

    public function curation(){
        return $this->belongsTo(CurationDB::class,'curation_id','id');
    }
}
