<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\GateReturnDiscount as ReturnDiscountDB;
use App\Models\GateReturnDiscountItemPackage as ReturnDiscountItemPackageDB;
use App\Models\GateStockinItemSingle as StockinItemSingleDB;

class GatePurchaseOrderItemPackage extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'purchase_order_item_packages';

    public function returns()
    {
        $returnDiscountTable = env('DB_ERPGATE').'.'.(new ReturnDiscountDB)->getTable();
        $returnDiscountItemPackageTable = env('DB_ERPGATE').'.'.(new ReturnDiscountItemPackageDB)->getTable();
        return $this->hasMany(ReturnDiscountItemPackageDB::class,'poip_id','id')
            ->join($returnDiscountTable,$returnDiscountTable.'.return_discount_no',$returnDiscountItemPackageTable.'.return_discount_no')
            ->where($returnDiscountTable.'.is_del',0)
            ->select([
                $returnDiscountItemPackageTable.'.*',
                $returnDiscountTable.'.return_date',
            ]);
    }

    public function stockins()
    {
        return $this->hasMany(StockinItemSingleDB::class,'poip_id','id')->where('is_del',0)->orderBy('stockin_date','asc');
    }
}
