<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateReturnDiscountItemPackage extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'return_discount_item_packages';
}