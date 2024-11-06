<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductImage as ProductImageDB;
use DB;

class iCarryUserFavorite extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    public function products(){
        return $this->hasMany(ProductDB::class);
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
}
