<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryShippingFee extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'shipping_set';
    //不使用timestamps
    public $timestamps = FALSE;
}
