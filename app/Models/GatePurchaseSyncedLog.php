<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatePurchaseSyncedLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'purchase_synced_logs';
    protected $fillable = [
        'confirm_time',
    ];
}
