<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateSpecialVendor extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'special_vendors';
}
