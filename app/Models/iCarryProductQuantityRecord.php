<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryProductQuantityRecord extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product_quantity_record';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'product_model_id',
        'before_quantity',
        'after_quantity',
        'reason',
        'before_gtin13',
        'after_gtin13',
        'admin_id',
        'vendor_id',
    ];
}
