<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateAdmin extends Model
{
    //指定 table 名稱
    protected $connection = 'erpGate';
    protected $table = 'admins';
}
