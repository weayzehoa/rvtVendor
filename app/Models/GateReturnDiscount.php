<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateReturnDiscount extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'return_discounts';
}
