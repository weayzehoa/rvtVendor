<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryOrderItemPackage extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'order_item_package';
    //變更 Laravel 預設 created_at 與 不使用 updated_at 欄位
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
}
