<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryCategory as CategoryDB;

class iCarrySubCategory extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'sub_category';
}
