<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GateSFShipping as SFShippingDB;
use App\Traits\SFApiFunctionTrait;
use App\Traits\SFShippingFunctionTrait;

use Session;

class SFShippingController extends Controller
{
    use SFShippingFunctionTrait, SFApiFunctionTrait;

    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:vendor');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shippings = $compact = $appends = [];
        $vendorId = auth('vendor')->user()->vendor_id;
        request()->request->add(['vendorId' => $vendorId]);

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $shippings = $this->getSFShippingData(request(),'index');

        $compact = array_merge($compact, ['shippings','list','appends']);
        return view('vendor.order.sfShipping', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sfShipping = SFShippingDB::with('vendorShipping','vendorShipping.items','vendorShipping.items.express')->findOrFail($id);
        $sfShippingNo = $sfShipping->sf_express_no;
        $items = $sfShipping->vendorShipping->items;
        if($sfShipping->status == 9){
            //串API取消順豐運單
            $result = $this->cancelSFShippingNumber($sfShippingNo);
            if($result == 'success'){
                //更新出貨單運單資料
                foreach($items as $item){
                    foreach($item->express as $express){
                        $expressNos = explode(',',$express->express_no);
                        for($i=0;$i<count($expressNos);$i++){
                            if($expressNos[$i] == $sfShippingNo){
                                unset($expressNos[$i]);
                            }
                        }
                        count($expressNos) > 0 ? $express->update(['express_no' => join(',',$expressNos)]) : $express->delete();
                    }
                }
                $sfShipping->update(['status' => -1]);
            }else{
                Session::put('error',"取消失敗。");
            }
        }else{
            if($sfShipping->status == 2){
                Session::put('error',"此運單已送達，不可取消");
            }
        }
        return redirect()->back();
    }

    public function getStatus(Request $request)
    {
        if(isset($request->id)){
            $sfShipping = $this->getSFShippingData(request(),'show');
            $sfShippingNos = [$sfShipping->sf_express_no];
            $phoneNo = mb_substr($sfShipping->phone, -4); //寄件方
            $phoneNo = 3161; //收件方
            $result = $this->chkSFShippingNumber($sfShipping->sf_express_no,$phoneNo);
            if(!empty($result)){
                $result = $result[0];
                if($result['code'] == 0 && $result['msg'] == 'success') {
                    $sfShipping->traceItems = $result['trackDetailItems'];
                }
            }else{
                $sfShipping->traceItems = null;
            }
            return $sfShipping;
        }
        return null;
    }
}
