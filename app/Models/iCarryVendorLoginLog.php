<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryVendorLoginLog extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor_login_logs';
    protected $fillable = [
        'vendor_account_id',
        'result',
        'ip',
        'account',
    ];
}
