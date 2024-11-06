<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryCountry extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'countries';
}
