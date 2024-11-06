<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductLang as ProductLangDB;

class CurationProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $data = $request->all();

        $curationId = $data['curation_id'];
        isset($data['product_id']) ? $newProductIds = $data['product_id'] : $newProductIds = [];
        $oldProductIds = CurationProductDB::where('curation_id',$data['curation_id'])->orderBy('sort','asc')->get()->pluck('product_id')->all();
        $removed = array_diff($oldProductIds,$newProductIds); //被移除
        foreach ($removed as $key => $value) {
            CurationProductDB::where([['curation_id',$curationId],['product_id',$value]])->delete();
        }
        for($i=0;$i<count($newProductIds);$i++){
            $curationProduct = CurationProductDB::with('curation')->where([['curation_id',$data['curation_id']],['product_id',$newProductIds[$i]]])->first();
            if($curationProduct){
                $curationProduct = $curationProduct->update(['sort' => $i+1]);
            }else{
                $curationProduct = CurationProductDB::create([
                    'curation_id' => $data['curation_id'],
                    'product_id' => $newProductIds[$i],
                    'sort' => $i+1,
                ]);
            }
        }
        return redirect()->route('vendor.curation.show',$data['curation_id'].'#product');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
        $data = $request->all();
        $curationProduct = CurationProductDB::with('curation')->findOrFail($id);
        $productId = $curationProduct->product_id;
        $product = ProductDB::findOrFail($productId);
        $product = $product->update($data);
        $this->lang($productId,$data['langs']);
        return redirect()->route('vendor.curation.show',$curationProduct->curation_id.'#product');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $curationProduct = CurationProductDB::with('curation')->findOrFail($id);
        $data['curation_id'] = $curationProduct->curation_id;
        $curationProduct->delete();
        $this->autoSort($curationProduct->curation_id);
        return redirect()->route('vendor.curation.show',$curationProduct->curation_id.'#product');
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $curationProduct = CurationProductDB::with('curation')->findOrFail($id);
        $up = ($curationProduct->sort) - 1.5;
        $curationProduct->fill(['sort' => $up]);
        $curationProduct->save();
        $this->autoSort($curationProduct->curation_id);
        return redirect()->route('vendor.curation.show',$curationProduct->curation_id.'#product');
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $curationProduct = CurationProductDB::findOrFail($id);
        $up = ($curationProduct->sort) + 1.5;
        $curationProduct->fill(['sort' => $up]);
        $curationProduct->save();
        $this->autoSort($curationProduct->curation_id);
        return redirect()->route('vendor.curation.show',$curationProduct->curation_id.'#product');
    }
    /*
        排序
    */
    public function autoSort($curationId)
    {
        $curationProducts = CurationProductDB::where('curation_id',$curationId)->orderBy('sort','asc')->get();
        $i = 1;
        foreach ($curationProducts as $curationProduct) {
            $id = $curationProduct->id;
            CurationProductDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
    }
    /*
        語言資料
    */
    public function lang($id,$langData)
    {
        foreach($langData as $lang => $value){
            $find = ProductLangDB::where([['product_id',$id],['lang',$lang]])->first();
            if($find){
                $find->update([
                    'curation_text_top' => $value['curation_text_top'],
                    'curation_text_bottom' => $value['curation_text_bottom'],
                ]);
            }else{
                $find = ProductLangDB::create([
                    'product_id' => $id,
                    'lang' => $lang,
                    'curation_text_top' => $value['curation_text_top'],
                    'curation_text_bottom' => $value['curation_text_bottom'],
                ]);
            }
        }
    }
}
