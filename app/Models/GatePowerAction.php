<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatePowerAction extends Model
{
    use HasFactory;
    public $timestamps = FALSE;
    protected $connection = 'erpGate';
    protected $table = 'power_actions';
}
