<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\iCarryOrder as OrderDB;

class GateSellImport extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sell_imports';
    protected $fillable = [
        'import_no',
        'type',
        'order_number',
        'shipping_number',
        'gtin13',
        'purchase_no',
        'digiwin_no',
        'product_name',
        'quantity',
        'sell_date',
        'stockin_time',
        'status',
        'memo',
        'vsi_id',
    ];

    public function order()
    {
        return $this->hasOne(OrderDB::class,'order_number','order_number');
    }

}
