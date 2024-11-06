<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryOrderItem as OrderItemDB;
use App\Models\OrderShipping as OrderShippingDB;
use App\Models\iCarryOrderVendorShipping as OrderVendorShippingDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Models\iCarryDigiwinPayment as iCarryDigiwinPaymentDB;
use App\Models\iCarryShopcomOrder as ShopcomOrderDB;
use App\Models\iCarryTradevanOrder as TradevanOrderDB;
use App\Models\iCarryOrderLog as OrderLogDB;
use App\Models\ErpCustomer as ErpCustomerDB;
use App\Models\ErpOrder as ErpOrderDB;
use App\Models\ErpProduct as ErpProductDB;
use App\Models\SyncedOrder as SyncedOrderDB;
use App\Models\SyncedOrderItem as SyncedOrderItemDB;
use App\Models\SyncedOrderError as SyncedOrderErrorDB;
use App\Models\SellReturn as SellReturnDB;
use App\Models\Sell as SellDB;
use DB;

class iCarryOrder extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'orders';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function items(){
        // return $this->hasMany(OrderItemDB::class,'order_id','id')->with('erpQuotation')
        // 不知道為何, 只要是跨mssql的資料庫若使用with則會找不到資料. 只能在迴圈中使用 item->erpQuotation 方式將資料拉出.
        return $this->hasMany(OrderItemDB::class,'order_id','id')->with('syncedOrderItem')
        ->join('orders','orders.id','order_item.order_id')
        ->join('product_model','product_model.id','order_item.product_model_id')
            ->join('product','product.id','product_model.product_id')
            ->join('vendor','vendor.id','product.vendor_id')
            ->select([
                'order_item.*',
                'orders.status',
                'vendor.id as vendor_id',
                'vendor.name as vendor_name',
                // 'product.name as product_name',
                DB::raw("CONCAT(vendor.name,' ',product.name,'-',product_model.name) as product_name"),
                'product.eng_name as product_eng_name',
                'product.unit_name',
                'product.direct_shipment as directShip',
                'product_model.origin_digiwin_no',
                'product_model.digiwin_no',
                'product_model.sku',
                'product_model.gtin13',
                'product.serving_size',
                'product.unit_name',
                'product.id as product_id',
                'product.package_data',
                'product.category_id as product_category_id',
            ]);
    }

    public function shippingMethod(){
        return $this->belongsTo(ShippingMethodDB::class,'shipping_method','id');
    }

    public function shippings(){
        return $this->setConnection('mysql')->hasMany(OrderShippingDB::class,'order_id','id');
    }

    public function vendorShippings(){
        return $this->hasMany(OrderVendorShippingDB::class,'order_id','id');
    }

    //給鼎新匯出用
    public function itemData(){
        $key = env('APP_AESENCRYPT_KEY');
        return $this->hasMany(OrderItemDB::class,'order_id','id')->with('syncedOrderItem','package')
            ->join('orders', 'orders.id', 'order_item.order_id')
            ->join('product_model','product_model.id','order_item.product_model_id')
            ->join('product','product.id','product_model.product_id')
            ->join('vendor','vendor.id','product.vendor_id')
            ->where('order_item.is_del',0) //排除掉取消的
            ->select([
                DB::raw("DATE_FORMAT(orders.pay_time,'%Y-%m-%d') as pay_time"),
                DB::raw("DATE_FORMAT(orders.pay_time,'%Y%m%d') as payTime"),
                'orders.ship_to',
                'orders.promotion_code',
                'orders.create_type',
                'orders.shipping_method',
                'orders.status',
                'orders.book_shipping_date',
                'orders.user_memo',
                'orders.receiver_key_time',
                'orders.receiver_keyword',
                'orders.receiver_address',
                'orders.order_number',
                'orders.partner_order_number',
                'orders.receiver_name',
                DB::raw("IF(orders.receiver_phone_number IS NULL,'',AES_DECRYPT(orders.receiver_phone_number,'$key')) as receiver_phone_number"),
                DB::raw("IF(orders.receiver_tel IS NULL,'',AES_DECRYPT(orders.receiver_tel,'$key')) as receiver_tel"),
                'orders.discount',
                'orders.spend_point',
                'orders.shipping_fee',
                'orders.parcel_tax',
                'orders.shipping_memo',
                'product_model.id as product_model_id',
                'product_model.sku',
                'product_model.gtin13',
                'product_model.digiwin_no',
                'product_model.origin_digiwin_no',
                'product_model.name as product_model_name',
                DB::raw("(SELECT name FROM vendor WHERE id IN(SELECT vendor_id FROM product WHERE id IN(SELECT product_id from product_model where digiwin_no IN((SELECT origin_digiwin_no FROM product_model WHERE product_model.id=order_item.product_model_id))))) as origin_vendor_name"),
                DB::raw("(SELECT name FROM product WHERE id IN(SELECT product_id from product_model where digiwin_no IN((SELECT origin_digiwin_no from product_model WHERE product_model.id=order_item.product_model_id)))) as origin_product_name"),
                DB::raw("CONCAT(vendor.name,' ',product.name,'-',product_model.name) as product_name"),
                'vendor.id as vendor_id',
                'vendor.name as vendor_name',
                'order_item.direct_shipment',
                'order_item.quantity',
                'order_item.return_quantity',
                'order_item.price',
                'order_item.purchase_price',
                'order_item.gross_weight',
                'order_item.order_id',
                'order_item.id',
                'product.id as product_id',
                'product.category_id as product_category_id',
                'product.direct_shipment as directShip',
                'product.vendor_earliest_delivery_date',
                'product.serving_size',
                'product.unit_name',
                'product.eng_name as product_eng_name',
                'product.name',
                'product.ticket_group',
                'product.ticket_price',
                'product.ticket_memo',
                'product.package_data',
            ])->orderBy('orders.id', 'desc');
    }
}
