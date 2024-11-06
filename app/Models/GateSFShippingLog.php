<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateSFShippingLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sf_shipping_logs';

    protected $fillable = [
        'type',
        'headers',
        'post_json',
        'get_json',
        'rtnCode',
        'rtnMsg',
    ];
}
