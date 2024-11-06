<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\GateAdmin as AdminDB;

class iCarryProductModel extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product_model';
    //不使用時間戳記
    public $timestamps = FALSE;
    protected $fillable = [
        'product_id',
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'quantity',
        'safe_quantity',
        'stock',
        'gtin13',
        'digiwin_no',
        'sku',
        'is_del',
        'origin_digiwin_no',
        'vendor_product_model_id',
    ];

    public function product(){
        return $this->belongsTo(ProductDB::class, 'product_id', 'id');
    }

    public function qtyRecords(){
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productQuantityRecordTable = env('DB_ICARRY').'.'.(new ProductQuantityRecordDB)->getTable();
        return $this->hasMany(ProductQuantityRecordDB::class,'product_model_id','id')
        ->select([
            $productQuantityRecordTable.'.*',
            'admin' => AdminDB::whereColumn($adminTable.'.id',$productQuantityRecordTable.'.admin_id')->select($adminTable.'.name')->limit(1),
            'vendor' => VendorDB::whereColumn($vendorTable.'.id',$productQuantityRecordTable.'.vendor_id')->select($vendorTable.'.name')->limit(1),
        ])->orderBy($productQuantityRecordTable.'.create_time','desc');
    }
}
