<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProduct as ProductDB;
//使用軟刪除
use Illuminate\Database\Eloquent\SoftDeletes;

class iCarryProductImage extends Model
{
    use HasFactory;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $connection = 'icarry';
    protected $table = 'product_images';
    protected $fillable = [
        'product_id',
        'filename',
        'sort',
        'is_top',
        'is_on',
    ];

    public function product(){
        return $this->belongsTo(ProductDB::class,'product_id','id');
    }
}
