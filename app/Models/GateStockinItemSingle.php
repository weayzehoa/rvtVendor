<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateStockinItemSingle extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'stockin_item_singles';
}
