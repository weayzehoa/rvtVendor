<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatePurchaseOrderItemSingle extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'purchase_order_item_singles';
}
