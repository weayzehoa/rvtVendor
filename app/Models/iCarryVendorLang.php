<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;

class iCarryVendorLang extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor_langs';
    protected $fillable = [
        'vendor_id',
        'lang',
        'name',
        'summary',
        'description',
        'curation',
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }
}
