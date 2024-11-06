<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\iCarryOrder as OrderDB;

class GateSellItemSingle extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sell_item_singles';
}
