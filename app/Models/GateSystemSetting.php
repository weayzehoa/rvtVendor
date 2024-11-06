<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateSystemSetting extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'system_settings';
    protected $fillable = [
        'sf_token'
    ];
}
