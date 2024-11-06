<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCuration as CurationDB;

class iCarryCurationLang extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation_langs';

    protected $fillable = [
        'curation_id',
        'main_title',
        'sub_title',
        'caption',
        'lang',
    ];

    public function curation(){
        return $this->belongsTo(CurationDB::class,'curation_id','id');
    }
}
