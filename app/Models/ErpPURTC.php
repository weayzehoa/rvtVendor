<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPURTC extends Model
{
    use HasFactory;
    protected $connection = 'iCarrySMERP';
    protected $table = 'dbo.PURTC';
    protected $primaryKey = 'TC002';
    protected $keyType = 'string';
    //不自動增加
    public $incrementing = false;
    public $timestamps = FALSE;

}
