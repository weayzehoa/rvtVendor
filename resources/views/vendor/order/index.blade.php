@extends('vendor.layouts.master')

@section('title', 'iCarry訂單管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('vendor.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>iCarry採購單</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('icarryOrder') }}">iCarry採購單</a></li>
                        <li class="breadcrumb-item active">清單</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-1">
                                    <button id="showForm" class="btn btn-sm btn-success mr-2" title="使用欄位查詢">使用欄位查詢</button>
                                </div>
                                <div class="col-4 float-right">
                                    <div class="float-right d-flex align-items-center">
                                            <div class="icheck-primary d-inline mr-2">
                                                <input type="radio" id="selectorder" name="multiProcess" value="selected">
                                                <label for="selectorder">自行勾選 <span id="chkallbox_text"></span></label>
                                            </div>
                                            <div class="icheck-primary d-inline mr-2">
                                                <input type="radio" id="chkallbox" name="multiProcess" value="allOnPage">
                                                <label for="chkallbox">目前頁面全選</label>
                                            </div>
                                            <div class="icheck-primary d-inline mr-2">
                                                <input type="radio" id="queryorder" name="multiProcess" value="byQuery">
                                                <label for="queryorder">依查詢條件</label>
                                            </div>
                                        <button class="btn btn-sm btn-info" id="multiProcess" disabled><span>多筆處理</span></button>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class=" float-right">
                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                            <span class="badge badge-purple text-lg mr-2">總筆數：{{ !empty($orders) ? number_format($orders->total()) : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if(count($shippingDates) > 0)
                                <div class="col-12 mt-2">
                                    <button class="btn btn-sm bg-navy mr-1 mt-1" title="尚未建立出貨單">指定到貨日</button>
                                    @foreach($shippingDates as $shippingDate)
                                    <a href="{{ env('APP_URL').'/icarryOrder?status=1&vendor_arrival_date='.$shippingDate->vendor_arrival_date.'&vendor_arrival_date_end='.$shippingDate->vendor_arrival_date }}" class="{{ isset($vendor_arrival_date) && $vendor_arrival_date == $shippingDate->vendor_arrival_date ? 'btn-primary' : 'fc-button text-primary' }} btn btn-sm mr-1 mt-1">{{ $shippingDate->vendor_arrival_date }} <span class="badge badge-sm badge-secondary">{{ $shippingDate->count }}</span></a>
                                    @endforeach
                                    <a href="{{ env('APP_URL').'/icarryOrder' }}" class="{{ isset($vendor_arrival_date) && $vendor_arrival_date == $shippingDate->vendor_arrival_date ? 'btn-primary' : 'fc-button text-primary' }} btn btn-sm mr-1 mt-1">清除選項</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="orderSearchForm" class="card card-primary" style="display: none">
                                <div class="card-header">
                                    <h3 class="card-title">使用欄位查詢</h3>
                                </div>
                                <form id="searchForm" role="form" action="{{ url('icarryOrder') }}" method="get">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-3">
                                                <label class="control-label" for="is_confirm">是否已確認?</label>
                                                <select class="form-control" id="is_confirm" name="is_confirm">
                                                    <option value="" {{ isset($is_confirm) && $is_confirm == '' ? 'selected' : '' }}>不拘</option>
                                                    <option value="Y" {{ isset($is_confirm) && $is_confirm == 'Y' ? 'selected' : '' }}>已確認</option>
                                                    <option value="X" {{ isset($is_confirm) && $is_confirm == 'X' ? 'selected' : '' }}>未確認</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label for="order_number">iCarry採購單號:</label>
                                                <input type="number" inputmode="numeric" class="form-control" id="purchase_no" name="purchase_no" placeholder="iCarry採購單編號" value="{{ isset($purchase_no) && $purchase_no ? $purchase_no : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-3">
                                                <label class="control-label" for="product_name">商品名稱:</label>
                                                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="填寫商品名稱ex:肉鬆蛋捲" value="{{ isset($product_name) ? $product_name ?? '' : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-3">
                                                <label class="control-label" for="digiwin_no">商品貨號:</label>
                                                <input type="text" class="form-control" id="digiwin_no" name="digiwin_no" placeholder="填寫商品貨號" value="{{ isset($digiwin_no) ? $digiwin_no ?? '' : '' }}" autocomplete="off" />
                                            </div>

                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-6 mt-2">
                                                        <label for="status">採購單狀態:</label>
                                                        <select class="form-control" id="status" size="5" multiple>
                                                            <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}  class="text-danger">已取消</option>
                                                            <option value="1"  {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-primary">待出貨</option>
                                                            <option value="2"  {{ isset($status) ? in_array(2,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-info">已全部入庫</option>
                                                            <option value="3"  {{ isset($status) ? in_array(3,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-success">已結案</option>
                                                        </select><input type="hidden" value="-1,1,2,3" name="status" id="status_hidden" />
                                                    </div>
                                                    <div class="col-6 mt-2">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label class="control-label" for="created_at">iCarry通知時間區間:</label>
                                                                <div class="input-group">
                                                                    <input type="datetime" class="form-control datetimepicker" id="notice_time" name="notice_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($notice_time) ? $notice_time ?? '' : '' }}" autocomplete="off" />
                                                                    <span class="input-group-addon bg-primary">~</span>
                                                                    <input type="datetime" class="form-control datetimepicker" id="notice_time_end" name="notice_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($notice_time_end) ? $notice_time_end ?? '' : '' }}" autocomplete="off" />
                                                                </div>
                                                            </div>
                                                            <div class="col-12 mt-2">
                                                                <label for="vendor_arrival_date">應到貨日區間:</label>
                                                                <div class="input-group">
                                                                    <input type="datetime" class="form-control datepicker" id="vendor_arrival_date" name="vendor_arrival_date" placeholder="格式：2016-06-06" value="{{ isset($vendor_arrival_date) ? $vendor_arrival_date ?? '' : '' }}" autocomplete="off" />
                                                                    <span class="input-group-addon bg-primary">~</span>
                                                                    <input type="datetime" class="form-control datepicker" id="vendor_arrival_date_end" name="vendor_arrival_date_end" placeholder="格式：2016-06-06" value="{{ isset($vendor_arrival_date_end) ? $vendor_arrival_date_end ?? '' : '' }}" autocomplete="off" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3 mt-2">
                                                <label class="control-label" for="is_modify">採購單是否有修改?</label>
                                                <select class="form-control" id="is_modify" name="is_modify">
                                                    <option value="" {{ isset($is_modify) && $is_modify == '' ? 'selected' : '' }}>不拘</option>
                                                    <option value="Y" {{ isset($is_modify) && $is_modify == 'Y' ? 'selected' : '' }}>是</option>
                                                    <option value="X" {{ isset($is_modify) && $is_modify == 'X' ? 'selected' : '' }}>否</option>
                                                </select>
                                            </div>
                                            <div class="col-3 mt-2">
                                                <label class="control-label" for="is_shipping">是否有建立出貨單?</label>
                                                <select class="form-control" id="is_shipping" name="is_shipping">
                                                    <option value="" {{ isset($is_shipping) && $is_shipping == '' ? 'selected' : '' }}>不拘</option>
                                                    <option value="Y" {{ isset($is_shipping) && $is_shipping == 'Y' ? 'selected' : '' }}>是</option>
                                                    <option value="X" {{ isset($is_shipping) && $is_shipping == 'X' ? 'selected' : '' }}>否</option>
                                                </select>
                                            </div>
                                            <div class="col-3 mt-2">
                                                <label class="control-label" for="is_stockin">是否已入庫?</label>
                                                <select class="form-control" id="is_stockin" name="is_stockin">
                                                    <option value="" {{ isset($is_stockin) && $is_stockin == '' ? 'selected' : '' }}>不拘</option>
                                                    <option value="Y" {{ isset($is_stockin) && $is_stockin == 'Y' ? 'selected' : '' }}>是</option>
                                                    <option value="X" {{ isset($is_stockin) && $is_stockin == 'X' ? 'selected' : '' }}>否</option>
                                                </select>
                                            </div>
                                            <div class="col-3 mt-2">
                                                <label class="control-label" for="list">每頁筆數:</label>
                                                <select class="form-control" id="list" name="list">
                                                    <option value="50" {{ $list == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ $list == 100 ? 'selected' : '' }}>100</option>
                                                    <option value="300" {{ $list == 300 ? 'selected' : '' }}>300</option>
                                                    <option value="500" {{ $list == 500 ? 'selected' : '' }}>500</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mb-2">
                                        <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                        <input type="reset" class="btn btn-default" value="清空">
                                        {{-- <button type="button" class="btn btn-success moreOption">更多選項</button> --}}
                                    </div>
                                    <div class="col-12 text-center mb-2">
                                    </div>
                                </form>
                            </div>
                            @if(count($orders) > 0)
                            <div class="col-12"  style="overflow: auto">
                                <table class="table table-hover table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-left" width="25%">iCarry採購單資訊</th>
                                            <th class="text-left" width="75%">採購品項<br></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                        <tr style="border-bottom:3px #000000 solid;border-bottom:3px #000000 solid;">
                                            <td class="text-left align-top p-0">
                                                <div>
                                                    <input type="checkbox" class="chk_box_{{ $order->id }}" name="chk_box" value="{{ $order->id }}">
                                                    <span class="text-lg text-bold text-primary">{{ $order->purchase_no }}</span>
                                                    @if(count($order->changeLogs) > 0)
                                                    <a href="javascript:" class="badge btn-sm btn badge-purple" onclick="getChange({{ $order->purchase_no }},{{ $order->id }},this)">修改紀錄</a>
                                                    @endif
                                                    @if(!empty($order->latestSynced) && $order->latestSynced['confirm_time'] == null)
                                                    <a href="javascript:" class="badge btn-sm btn badge-primary" onclick="confirmOrder({{ $order->id }})">確認訂單</a>
                                                    @endif
                                                </div>
                                                <hr class="mb-1 mt-1">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="text-sm">金　額：{{ $order->amount }}</span><br>
                                                        <span class="text-sm">稅　金：{{ $order->tax }}</span><br>
                                                        <span class="text-sm">總金額：{{ $order->amount + $order->tax }}</span><br>
                                                        <span class="text-sm">單品數量：{{ $order->quantity }}</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="status_{{ $order->id }} text-bold">
                                                            @if($order->status == -1)
                                                                已取消
                                                            @elseif($order->status == 1)
                                                                @if(!empty($order->latestSynced) && $order->latestSynced['confirm_time'] == null)
                                                                未確認
                                                                @else
                                                                已確認，待出貨
                                                                @endif
                                                            @elseif($order->status == 2)
                                                            已全部入庫
                                                            @elseif($order->status == 3)
                                                            已結案
                                                            @endif
                                                            <br>
                                                            <span class="forhide mt-1 badge badge-primary">訂單已確認：{{ !empty($order->latestSynced['confirm_time']) ? str_replace('-','/',explode(' ',$order->latestSynced['confirm_time'])[0]) : '無' }}</span><br>
                                                            <span class="forhide mt-1 badge badge-success">已全部入庫：{{ $order->status == 2 ? str_replace('-','/',$order->stockin_finish_date) : '無' }}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-left align-top p-0">
                                                @if($order->ng == 1)
                                                <span class="text-sm text-bold text-danger">注意!!此採購單內有商品數量為0</span>
                                                @endif
                                                <table class="table table-sm">
                                                    <thead class="table-info">
                                                        <th width="10%" class="text-left align-middle text-sm">廠商應到貨日</th>
                                                        <th width="15%" class="text-left align-middle text-sm">iCarry貨號/廠商料號</th>
                                                        <th width="25%" class="text-left align-middle text-sm">品名</th>
                                                        <th width="5%" class="text-center align-middle text-sm">單位</th>
                                                        <th width="5%" class="text-right align-middle text-sm">採購量</th>
                                                        <th width="5%" class="text-right align-middle text-sm">入庫量</th>
                                                        <th width="5%" class="text-right align-middle text-sm">退貨量</th>
                                                        <th width="8%" class="text-right align-middle text-sm">採購價</th>
                                                        <th width="7%" class="text-right align-middle text-sm">總價</th>
                                                    </thead>
                                                    <tbody>
                                                            @foreach($order->items as $item)
                                                            <tr>
                                                                <td class="text-left align-middle text-sm order_item_modify_{{ $item->id }}" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}">
                                                                    <span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->vendor_arrival_date }}</span>
                                                                    @if(!empty($item->vendor_shipping_no))
                                                                    <span class="text-danger"><i class="fas fa-tags" title="出貨單"></i></span>
                                                                    @endif

                                                                </td>
                                                                <td class="text-left align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}">
                                                                    <span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->digiwin_no }}</span>
                                                                    @if($item->direct_shipment == 1)
                                                                    <span class="text-primary"><i class="fas fa-truck" title="廠商直寄"></i></span>
                                                                    @endif
                                                                    <br><span class="text-primary {{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->vendor_product_model_id }}</span>
                                                                </td>
                                                                <td class="text-left align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}">
                                                                    <span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->product_name }}</span>
                                                                    @if($item->is_del == 1 || $item->quantity == 0)<span class="text-gray text-sm text-bold">(已取消)</span>@endif
                                                                </td>
                                                                <td class="text-center align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}"><span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->unit_name }}</span></td>
                                                                <td class="text-right align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}"><span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ number_format($item->quantity) }}</span></td>
                                                                <td class="text-right align-middle text-sm item_qty_{{ $item->id }}" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}">
                                                                    @if(!empty($item->stockinQty) && $item->stockinQty > 0)
                                                                    <span class="text-primary {{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}" >{{ $item->stockinQty }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-right align-middle text-sm item_qty_{{ $item->id }}" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}">
                                                                    @if(count($item->returns) > 0)
                                                                    <span class="text-danger">
                                                                    @if(!empty($item->returnQty))
                                                                    -{{ $item->returnQty }}
                                                                    @endif
                                                                    </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-right align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}"><span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->purchase_price }}</span></td>
                                                                <td class="text-right align-middle text-sm" style="{{ strstr($item->sku,'BOM') ? 'border-top:1px #000000 solid;' : 'border-top:1px #000000 solid;border-bottom:1px #000000 solid;' }}"><span class="{{ $item->is_del == 1 || $item->quantity == 0 ? 'double-del-line' : '' }}">{{ $item->purchase_price * $item->quantity }}</span></td>
                                                            </tr>
                                                            @if(strstr($item->sku,'BOM'))
                                                                @if(count($item->packages)>0)
                                                                <tr class="m-0 p-0">
                                                                    <td colspan="10" class="text-sm p-0">
                                                                        <table width="100%" class="table-sm m-0 p-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th width="10%" class="text-left align-middle text-sm" style="border: none; outline: none"></th>
                                                                                    <th width="15%" class="text-left align-middle text-sm" style="border: none; outline: none">iCarry單品貨號/廠商料號</th>
                                                                                    <th width="25%" class="text-left align-middle text-sm" style="border: none; outline: none">品名</th>
                                                                                    <th width="5%" class="text-center align-middle text-sm" style="border: none; outline: none">單位</th>
                                                                                    <th width="5%" class="text-right align-middle text-sm" style="border: none; outline: none">採購量</th>
                                                                                    <th width="5%" class="text-right align-middle text-sm" style="border: none; outline: none">入庫量</th>
                                                                                    <th width="5%" class="text-right align-middle text-sm" style="border: none; outline: none">退貨量</th>
                                                                                    <th width="8%" class="text-right align-middle text-sm" style="border: none; outline: none">採購價</th>
                                                                                    <th width="7%" class="text-right align-middle text-sm" style="border: none; outline: none"></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($item->packages as $packageItem)
                                                                                <tr>
                                                                                    <td class="text-left align-middle text-sm" ></td>
                                                                                    <td class="text-left align-middle text-sm" >
                                                                                        <span class="{{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['digiwin_no'] }}</span>
                                                                                        @if(!empty($packageItem->stockin_date))
                                                                                        <span data-toggle="popover" class="text-primary stockin_package_{{ $packageItem['id'] }}" data-content="
                                                                                            <small>
                                                                                                入庫單號：{{ $packageItem->erp_stockin_no }}<br>
                                                                                                入庫日期：{{ $packageItem->stockin_date }}
                                                                                            </small>
                                                                                            "><i class="fas fa-store-alt"></i></span>
                                                                                        @endif
                                                                                        <br><span class="text-primary {{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['vendor_product_model_id'] }}</span>
                                                                                    </td>
                                                                                    <td class="text-left align-middle text-sm" ><span class="{{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['product_name'] }}</span></td>
                                                                                    <td class="text-center align-middle text-sm" ><span class="{{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['unit_name'] }}</span></td>
                                                                                    <td class="text-right align-middle text-sm" ><span class="{{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['quantity'] }}</span></td>
                                                                                    <td class="text-right align-middle text-sm package_qty_{{ $packageItem['id'] }}" >
                                                                                        @if(!empty($packageItem['stockinQty']) && $packageItem['stockinQty'] > 0)
                                                                                        <span class="text-primary {{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['stockinQty'] == 0 ? null : $packageItem['stockinQty'].' ' }}</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-right align-middle text-sm package_qty_{{ $packageItem['id'] }}" >
                                                                                        @if(count($packageItem->returns) > 0)
                                                                                        <span class="text-danger">
                                                                                        -{{ $packageItem['returnQty'] }}
                                                                                        </span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-right align-middle text-sm" ><span class="{{ $packageItem['is_del'] == 1 ? 'double-del-line' : '' }}">{{ $packageItem['purchase_price'] }}</span></td>
                                                                                    <td class="text-right align-middle text-sm" ></td>
                                                                                </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                @endif
                                                            @endif
                                                            @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <h3>無資料</h3>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-primary text-lg ml-1">總筆數：{{ !empty($orders) ? number_format($orders->total()) : 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $orders->appends($appends)->render() }}
                                @else
                                {{ $orders->render() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <form id="multiProcessForm" action="{{ url('icarryOrder/multiProcess') }}" method="POST">
        @csrf
    </form>
</div>
@endsection

@section('modal')
{{-- 同步紀錄 Modal --}}
<div id="syncModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ url('purchases/notice') }}" method="POST" class="float-right">
                    @csrf
                    <input type="hidden" id="purchaseOrderId" name="id" value="">
                    <button id="NoticeBtn" type="submit" class="btn btn-sm btn-primary" style="display: none">通知廠商</button>
                </form>
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">#</th>
                            <th width="15%" class="text-left">同步時間</th>
                            <th width="10%" class="text-left">單品數量</th>
                            <th width="10%" class="text-right">金額</th>
                            <th width="10%" class="text-right">稅金</th>
                            <th width="10%" class="text-right">總金額</th>
                            <th width="15%" class="text-center">通知時間</th>
                            <th width="15%" class="text-center">廠商確認時間</th>
                        </tr>
                    </thead>
                    <tbody id="syncRecord"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 多處理Modal --}}
<div id="multiModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="multiModalLabel">請選擇功能</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <button class="btn btn-sm btn-primary multiProcess mr-2" value="confirmOrder">確認訂單</button>
                    <button class="btn btn-sm btn-success multiProcess mr-2" value="ExportOrder">匯出訂單</button>
                    <button class="btn btn-sm btn-warning multiProcess mr-2" value="CreateShipping">建立出貨單</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 修改紀錄 Modal --}}
<div id="modifyModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifyModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">狀態</th>
                            <th width="10%" class="text-left">時間</th>
                            <th width="10%" class="text-left">iCarry管理者</th>
                            <th width="15%" class="text-left">iCarry/鼎新品號</th>
                            <th width="25%" class="text-left">商品名稱</th>
                            <th width="5%" class="text-right">金額</th>
                            <th width="5%" class="text-right">數量</th>
                            <th width="10%" class="text-left">日期</th>
                            <th width="15%" class="text-left">備註</th>
                        </tr>
                    </thead>
                    <tbody id="modifyRecord"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 出貨 Modal --}}
<div id="shppingModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90%;height: 95%;">
        <div class="modal-content" style="max-height: 95%;">
            <div class="modal-header">
                <h5 class="modal-title" id="shppingModalLabel">選擇要建立出貨單的商品</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body table-responsive" style="max-height: calc(100% - 60px);overflow-y: auto;">
                <span class="text-danger mb-2">注意! 建立出貨單資料前請先確認所有 <b>iCarry採購單</b> 已經確認完成，否則該採購單資料將不會與其他採購單資料合併。</span>
                <table width="100%" id="shippingTable" class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th width="15%" class="text-left">iCarry採購單號</th>
                            <th width="10%" class="text-left">廠商應到貨日</th>
                            <th width="10%" class="text-left">採購品號</th>
                            <th width="20%" class="text-left">品名</th>
                            <th width="5%" class="text-center">廠商直寄</th>
                            <th width="8%" class="text-right">採購價</th>
                            <th width="7%" class="text-right">數量</th>
                            <th width="5%" class="text-center"></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div>
                <div class="m-3 float-right">
                    <button class="btn btn-sm btn-secondary" id="cancelAll">取消全部</button>
                    <button class="btn btn-sm btn-success" id="selectAll">全選</button>
                    <button class="btn btn-sm btn-primary shippingProcess" value="selected">送出勾選</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
{{-- DataTable --}}
<link rel="stylesheet" href="{{ asset('vendor/datatables/media/css/jquery.dataTables.min.css') }}">
{{-- <link rel="stylesheet" href="{{ asset('vendor/datatables/media/css/dataTables.bootstrap4.min.css') }}"> --}}
<link rel="stylesheet" href="{{ 'https://cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css' }}">

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- DataTable --}}
<script src="{{ asset('vendor/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ 'https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js' }}"></script>
{{-- <script src="{{ asset('vendor/datatables/media/js/dataTables.bootstrap4.min.js') }}"></script> --}}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $('[data-toggle="popover"]').popover({
            html: true,
            sanitize: false,
        });
        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('.timepicker').timepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        var num_all = $('input[name="chk_box"]').length;
        var num = $('input[name="chk_box"]:checked').length;
        $("#chkallbox_text").text("("+num+"/"+num_all+")");

        $('input[name="chk_box"]').change(function(){
            var num_all = $('input[name="chk_box"]').length;
            var num = $('input[name="chk_box"]:checked').length;
            num > 0 ? $("#selectorder").prop("checked",true) : $("#selectorder").prop("checked",false);
            if(num == 0){
                $('#chkallbox').prop("checked",false);
                $('#multiProcess').prop("disabled",true);
            }else if(num > 0){
                $("#selectorder").prop("checked",true)
                $('#multiProcess').prop("disabled",false);
            }else if(num == num_all){
                $("#chkallbox").prop("checked",true);
                $('#multiProcess').prop("disabled",false);
            }
            $("#chkallbox_text").text("("+num+"/"+num_all+")");
        });

        $('input[name="multiProcess"]').click(function(){
            if($(this).val() == 'allOnPage'){
                $('input[name="chk_box"]').prop("checked",true);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",false);
            }else if($(this).val() == 'selected'){
                $('input[name="chk_box"]').prop("checked",false);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",false);
            }else if($(this).val() == 'byQuery'){
                $('input[name="chk_box"]').prop("checked",false);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",true);
            }else{
                $('#multiProcess').prop("disabled",true);
                $('#oit').prop("disabled",false);
            }
            $('#orderSearchForm').hide();
            $('#showForm').html('使用欄位查詢');
            var num_all = $('input[name="chk_box"]').length;
            var num = $('input[name="chk_box"]:checked').length;
            $("#chkallbox_text").text("("+num+"/"+num_all+")");
        });

        $('.multiProcess').click(function (e){
            let form = $('#multiProcessForm');
            let cate = $(this).val().split('_')[0];
            let type = $(this).val().split('_')[1];
            let filename = $(this).html();
            let condition = null;
            let multiProcess = $('input[name="multiProcess"]:checked').val();
            let ids = $('input[name="chk_box"]:checked').serializeArray().map( item => item.value );
            if(multiProcess == 'allOnPage' || multiProcess == 'selected'){
                if(cate == 'Export'){
                    let start = $('#vendor_arrival_date').val();
                    let end = $('#vendor_arrival_date_end').val();
                    form.append($('<input type="hidden" class="formappend" name="vendor_arrival_date">').val(start));
                    form.append($('<input type="hidden" class="formappend" name="vendor_arrival_date_end">').val(end));
                }
                if(ids.length > 0){
                    for(let i=0;i<ids.length;i++){
                        form.append($('<input type="hidden" class="formappend" name="id['+i+']">').val(ids[i]));
                    }
                }else{
                    alert('尚未選擇訂單或商品');
                    return;
                }
            }else if(multiProcess == 'byQuery'){ //by條件
                let sel="";
                $("#shipping_method>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#shipping_method_hidden").val(sel.substring(1));

                sel = "";
                $("#status>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#status_hidden").val(sel.substring(1));

                sel = "";
                $("#pay_method>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#pay_method_hidden").val(sel.substring(1));

                sel = "";
                $("#origin_country>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#origin_country_hidden").val(sel.substring(1));
                condition = $('#searchForm').serializeArray();
                let con_val = $('#searchForm').serializeArray().map( item => item.value );
                let con_name = $('#searchForm').serializeArray().map( item => item.name );
                for(let j=0; j<con_name.length;j++){
                    let tmp = '';
                    tmp = $('<input type="hidden" class="formappend" name="con['+con_name[j]+']" value="'+con_val[j]+'">');
                    form.append(tmp);
                }
            }else{
                return;
            }
            let export_method = $('<input type="hidden" class="formappend" name="method" value="'+multiProcess+'">');
            let export_cate = $('<input type="hidden" class="formappend" name="cate" value="'+cate+'">');
            let export_type = $('<input type="hidden" class="formappend" name="type" value="'+type+'">');
            form.append(export_method);
            form.append(export_cate);
            form.append(export_type);
            form.append( $('<input type="hidden" class="formappend" name="filename" value="'+filename+'">') );
            form.append( $('<input type="hidden" class="formappend" name="model" value="purchase">') );
            if(cate == 'Export' || cate == 'Notice'){
                alert('注意! 匯出採購單與通知廠商僅會 匯出/通知 已採購、已完成入庫 狀態，\n尚未採購狀態的採購單，請先同步至鼎新才可使用此功能。')
            }
            if(cate == 'CreateShipping'){
                $('#shippingData').html('');
                $("#shippingTable").DataTable().destroy();
                let token = '{{ csrf_token() }}';
                $.ajax({
                    type: "post",
                    url: 'icarryOrder/getUnShipping',
                    data: { id: ids, condition: condition, cate: cate, filename: filename, method: multiProcess, model: 'orders', _token: token },
                    success: function(data) {
                        let record = '';
                        let items = null;
                        let orderIds = null;
                        let itemData = [];
                        data != null ? items = data['items'] : '';
                        data != null ? orderIds = data['orderIds'] : '';
                        if(items != null){
                            let s = 0;
                            for(let i=0; i<items.length; i++){
                                if(items[i]['quantity'] != 0){
                                    // let value = items[i]['product_model_id']+'_@_'+items[i]['orderItemIds']+'_@_'+items[i]['purchaseNos']+'_@_'+items[i]['quantity'];
                                    let value = items[i]['orderItemIds'];
                                    itemData[s] = [
                                        items[i]['purchaseNos'],
                                        items[i]['vendor_arrival_date'],
                                        items[i]['sku'],
                                        items[i]['product_name'],
                                        items[i]['direct_shipment'],
                                        items[i]['purchase_price'],
                                        items[i]['quantity'],
                                        '<div class="icheck-primary"><input type="checkbox" id="pchkbox'+i+'" name="shipping_data[]" class="shipping_data" value="'+value+'"><label for="pchkbox'+i+'"></label></div>'
                                    ];
                                    s++;
                                }
                            }
                            $('#shippingTable').DataTable({
                                "data": itemData,
                                // "columns": [ // 列的標題一般是從DOM中讀取（也可以使用這個屬性為表格創建列標題)
                                //     { title: "廠商到貨日"},
                                //     { title: "品號"},
                                //     { title: "廠商名稱"},
                                //     { title: "品名"},
                                //     { title: "數量",},
                                //     { title: "勾選",},
                                // ],
                                // select: true,
                                // order: [[ 1, 'asc' ]],
                                // select: {
                                //     style:    'multi',
                                //     selector: 'td:first-child'
                                // },
                                "columnDefs":[
                                    // {width: "5%", orderable: false, targets:0, className: 'dt-body-center select-checkbox'},
                                    { width: "15%", targets: 0, className: 'dt-body-left'},
                                    { width: "10%", targets: 1, className: 'dt-body-left'},
                                    { width: "10%", targets: 2, className: 'dt-body-left'},
                                    { width: "25%", targets: 3, className: 'dt-body-left'},
                                    { width: "10%", targets: 4, className: 'dt-body-center' },
                                    { width: "10%", targets: 5, className: 'dt-body-right' },
                                    { width: "10%", targets: 6, className: 'dt-body-right' },
                                    { width: "10%", targets: 7, className: 'dt-body-center',orderable: false },
                                ],
                                "order": [[1, 'asc']],
                                "paging": true,
                                "pageLength": 10,
                                "lengthChange": true,
                                "lengthMenu": [[10, 25, 50, 100 ,300 ,500, -1], [10, 25, 50, 100 ,300 ,500, "全部"]],
                                "searching": true,
                                "ordering": true,
                                "info": true,
                                "autoWidth": true,
                                "responsive": true,
                                "deferRender": true,
                                // "scrollY": 600,
                                "scrollCollapse": true,
                                "scroller": true,
                                "autoWidth": false,
                                "language": {
                                    "decimal": ",",
                                    "thousands": "."
                                },
                                "oLanguage": {
                                    "sUrl": "//cdn.datatables.net/plug-ins/1.11.3/i18n/zh_Hant.json"
                                }
                            });

                            form.append( $('<input type="hidden" class="formappend" name="orderIds" value="'+orderIds+'">') );
                            $('.shippingProcess').prop('disabled',false);
                        }else{
                            record = '<tr><td class="text-left" colspan="6"><h3>無未採購商品</h3></td></tr>';
                            $('#purchaseData').append(record);
                            $('.shippingProcess').prop('disabled',true);
                        }
                        $('#multiModal').modal('hide');
                        $('#shppingModal').modal('show');
                    }
                });
            }else{
                form.submit();
                $('.formappend').remove();
                $('#multiModal').modal('hide');
            }
            $('.formappend').remove();
            return;
        });

        $('.shippingProcess').click(function(){
            $('.shippingProcess').prop('disabled',true);
            let form = $('#multiProcessForm');
            form.append( $('<input type="hidden" class="formappend" name="type" value="CreateShipping">') );
            form.append( $('<input type="hidden" class="formappend" name="cate" value="CreateShipping">') );
            form.append( $('<input type="hidden" class="formappend" name="filename" value="出貨單建立">') );
            $('#multiProcessForm > input[name=type]').val($(this).val());
            $('#multiProcessForm > input[name=orderIds]').remove();
            let selected = $('.shipping_data:checked').serializeArray().map( item => item.value );
            for(let j=0; j<selected.length;j++){
                let tmp = '';
                tmp = $('<input type="hidden" class="formappend" name="selected['+j+']" value="'+selected[j]+'">');
                form.append(tmp);
            }
            form.submit();
            $('.formappend').remove();
        });


        $('#selectAll').click(function(){
            $('.shipping_data').prop('checked',true);
        });

        $('#cancelAll').click(function(){
            $('.shipping_data').prop('checked',false);
        });

        $('#multiProcess').click(function(){
            if($('input[name="multiProcess"]:checked').val() == 'selected'){
                let num = $('input[name="chk_box"]:checked').length;
                if(num == 0){
                    alert('尚未選擇iCarry採購單');
                    return;
                }
            }
            $('#multiModal').modal('show');
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#orderSearchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('.moreOption').click(function(){
            $('#MoreSearch').toggle();
            $(this).html() == '更多選項' ? $(this).html('隱藏更多選項') : $(this).html('更多選項');
        });

        $('.modifybyQuerySend').click(function(){
            $('#mark').submit();
        });

        $('.btn-cancel').click(function (e) {
            let id = $(this).val();
            let form = $('#cancelForm');
            if(confirm('請確認是否要取消這筆採購單?')){
                form.append('<input type="hidden" class="formappend" name="id" value="'+id+'">')
                form.submit();
                $('.formappend').remove();
            };
        });

        $('#stockinImport').click(function(){
            $('#importModal').modal('show');
        });

        $('input[type=file]').change(function(x) {
            let name = this.name;
            let file = x.currentTarget.files;
            let filename = file[0].name; //不檢查檔案直接找出檔名
            if (file.length >= 1) {
                if (filename) {
                    $('label[for=' + name + ']').html(filename);
                } else {
                    $(this).val('');
                    $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
                }
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
            }
        });

        $('.btn-stockinModify').click(function(){
            let url = window.location.href;
            let form = $('#stockinModifyForm');
            form.append($('<input type="hidden" class="formappend" name="url" value="'+url+'">'));
            form.submit();
        });
    })(jQuery);

    function formSearch(){
        let sel="";
        $("#shipping_method>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_method_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        sel = "";
        $("#pay_method>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#pay_method_hidden").val(sel.substring(1));

        sel = "";
        $("#origin_country>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#origin_country_hidden").val(sel.substring(1));

        $("#searchForm").submit();
    }

    function getLog(purchase_number,purchase_id,column_name,column_value,e,item_id){
        let token = '{{ csrf_token() }}';
        let id = purchase_id;
        let datepicker = '';
        let dateFormat = 'yy-mm-dd';
        let timeFormat = 'HH:mm:ss';
        let html = '';
        let label = '採購單號：'+purchase_number+'，同步紀錄';
        $('#myform').html('');
        $('#record').html('');
        $('#syncRecord').html('');
        $('#myrecord').addClass('d-none');
        $('#syncModal').modal('show');
        $('#myrecord').removeClass('d-none');
        $.ajax({
            type: "post",
            url: 'purchases/getlog',
            data: { id: id, _token: token },
            success: function(data) {
                let record = '';
                if(data.length > 0){
                        let status = data[0]['status'];
                        let purchaseNo = data[0]['purchase_no'];
                        let erpPurchaseNo = data[0]['erp_purchase_no'];
                        let purchaseOrderId = data[0]['purchase_order_id'];
                        for(let i=0; i<data.length; i++){
                            let dateTime = data[i]['synced_time'];
                            let amount = data[i]['amount'];
                            let quantity = data[i]['quantity'];
                            let tax = data[i]['tax'];
                            let noticeTime = data[i]['notice_time'];
                            let confirmTime = data[i]['confirm_time'];
                            noticeTime == null ? noticeTime = '' : '';
                            confirmTime == null ? confirmTime = '' : '';
                            let total = parseFloat(amount) + parseFloat(tax);
                            let record = '<tr><td class="text-center">'+(i+1)+'</td><td class="text-left">'+dateTime+'</td><td class="text-left">'+quantity+'</td><td class="text-right">'+amount+'</td><td class="text-right">'+tax+'</td><td class="text-right">'+total+'</td><td class="text-center">'+noticeTime+'</td><td class="text-center">'+confirmTime+'</td></tr>';
                            $('#syncRecord').append(record);
                            $('#purchaseOrderId').val(purchaseOrderId);
                        }
                        label = '採購單編號：'+purchaseNo+'，鼎新採購單編號：'+erpPurchaseNo+'，同步紀錄';
                        status == 3 ? $('#NoticeBtn').hide() : $('#NoticeBtn').show();
                }
                $('#syncModalLabel').html(label);
                $('#syncModal').modal('show');
            }
        });
    }

    function getChange(purchase_number,purchase_id,e)
    {
        let token = '{{ csrf_token() }}';
        let id = purchase_id;
        let datepicker = '';
        let dateFormat = 'yy-mm-dd';
        let timeFormat = 'HH:mm:ss';
        let html = '';
        let label = '採購單號：'+purchase_number+'，退貨紀錄';
        $('#modifyRecord').html('');
        $('#modifyModal').modal('show');
        $.ajax({
            type: "post",
            url: 'icarryOrder/getChangeLog',
            data: { purchase_no: purchase_number, _token: token },
            success: function(data) {
                let record = '';
                if(data.length > 0){
                    let purchaseNo = data[0]['purchase_no'];
                    let erpPurchaseNo = data[0]['erp_purchase_no'];
                    let purchaseOrderId = data[0]['purchase_order_id'];
                    for(let i=0; i<data.length; i++){
                        let dateTime = data[i]['modify_time'];
                        let admin = data[i]['admin_name'] != null ? data[i]['admin_name'] : '';
                        let sku = data[i]['sku'] != null ? data[i]['sku'] : '';
                        let digiwinNo = data[i]['digiwin_no'] != null ? data[i]['digiwin_no'] : '';
                        let productName = data[i]['product_name'] != null ? data[i]['product_name'] : '';
                        let quantity = data[i]['quantity'] != null ? data[i]['quantity'] : '';
                        let price = data[i]['price'] != null ? data[i]['price'] : '';
                        let date = data[i]['date'] != null ? data[i]['date'] : '';
                        let status = data[i]['status'] != null ? data[i]['status'] : '';
                        let memo = data[i]['memo'] != null ? data[i]['memo'] : '' ;
                        let record = '<tr><td class="text-center">'+status+'</td><td class="text-left">'+dateTime+'</td><td class="text-left">'+admin+'</td><td class="text-left">'+sku+'<br>'+digiwinNo+'</td><td class="text-left">'+productName+'</td><td class="text-right">'+price.replace(' => ', '<br>')+'</td><td class="text-right">'+quantity.replace(' => ', '<br>')+'</td><td class="text-left">'+date.replace(' => ', '<br>')+'</td><td class="text-left">'+memo+'</td></tr>';
                        $('#modifyRecord').append(record);
                    }
                    label = 'iCarry採購單編號：'+purchaseNo+'，修改紀錄';
                }
                $('#modifyModalLabel').html(label);
                $('#modifyModal').modal('show');
            }
        });
    }

    function itemmemo (event,id){
        if(event.keyCode==13){
            event.preventDefault();
            let memo = $(event.target).val();//.replace(/\n/g,"");
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'purchases/itemmemo',
                data: { id: id, memo: memo , _token: token },
                success: function(data) {
                    if(data == 'success'){
                        $("#item_memo_"+id).attr("data-content",'<textarea class="text-danger" onkeydown="itemmemo(event,'+id+');">'+memo+'</textarea>');
                        $("#item_memo_"+id).html('<i class="fa fa-info-circle"></i>');
                        $("#item_memo_"+id).popover('hide');
                    }
                }
            });
        }
    }

    function itemQty (event,id){
        if(event.keyCode==13){
            event.preventDefault();
            let qty = $(event.target).val();//.replace(/\n/g,"");
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'purchases/qtyModify',
                data: { id: id, type: 'item', qty: qty , _token: token },
                success: function(data) {
                    if(data == 'success'){
                        // if(qty == 0){
                        //     $("#item_qty_"+id).popover('hide');
                        //     $("#item_qty_"+id).remove();
                        //     $(".stockin_item_"+id).html('');
                        //     $(".stockin_item_"+id).attr("data-content",'');
                        //     $(".item_qty_"+id).html('');
                        // }else{
                            $("#item_qty_"+id).popover('hide');
                            $("#item_qty_"+id).attr("data-content",'<textarea class="text-danger" onkeydown="itemQty(event,'+id+');">'+qty+'</textarea>');
                            $("#item_qty_"+id).html('<span class="new_item_qty_'+id+'"></span>');
                            $(".new_item_qty_"+id).html('<u>'+qty+'</u>');
                        // }
                    }
                }
            });
        }
    }

    function packageQty (event,id){
        if(event.keyCode==13){
            event.preventDefault();
            let qty = $(event.target).val();//.replace(/\n/g,"");
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'purchases/qtyModify',
                data: { id: id, type: 'package', qty: qty , _token: token },
                success: function(data) {
                    if(data == 'success'){
                        // if(qty == 0){
                        //     $("#package_qty_"+id).popover('hide');
                        //     $("#package_qty_"+id).remove();
                        //     $(".stockin_package_"+id).html('');
                        //     $(".stockin_package_"+id).attr("data-content",'');
                        //     $(".package_qty_"+id).html('');
                        // }else{
                            $("#package_qty_"+id).attr("data-content",'<textarea class="text-danger" onkeydown="packageQty(event,'+id+');">'+qty+'</textarea>');
                            $("#package_qty_"+id).html('<span class="new_package_qty_'+id+'"></span>');
                            $("#package_qty_"+id).popover('hide');
                            $(".new_package_qty_"+id).html('<u>'+qty+'</u>');
                        // }
                    }
                }
            });
        }
    }

    function removeCondition(name){
        let sel="";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));
        if(name == 'vendor_arrival_date' || name == 'created_at' || name == 'book_shipping_date'){
            $('input[name="'+name+'"]').val('');
            $('input[name="'+name+'_end"]').val('');
        }else if(name == 'notice_vendor'){
            $('select[name="'+name+'"]').val('');
        }else if(name == 'status'){
            $('input[name="'+name+'"]').val('0');
        }else{
            $('input[name="'+name+'"]').val('');
        }
        $("#searchForm").submit();
    }

    function stockinModify(poisId){
        $('#stockinModifyRecord').html('');
        let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'purchases/getStockin',
                data: { poisId: poisId, _token: token },
                success: function(data) {
                    if(data){
                        let label = '採購單號：' + data[0]['purchase_no'] + '，鼎新單號：'+ data[0]['erp_purchase_no'] + ' 入庫單數量修改 <br><span class="text-sm text-primary">' + data[0]['sku'] + ' ' + data[0]['product_name'] + '</span>';
                        let purchaseQty = '<span class="text-primary text-bold">總採購數量：'+data[0]['quantity']+'</span>';
                        for(let i=0; i<data.length; i++){
                            let record = '<tr><td class="text-center">'+(i+1)+'</td><td class="text-left">'+data[i]['erp_stockin_no']+'</td><td class="text-left">'+data[i]['erp_stockin_sno']+'</td><td class="text-left">'+data[i]['product_name']+'</td><td class="text-right">'+data[i]['purchase_price']+'</td><td class="text-right"><input type="hidden" class="form-control form-control-sm text-right" name="data['+i+'][id]" value="'+data[i]['id']+'"><input type="number" class="form-control form-control-sm text-right" name="data['+i+'][qty]" placeholder="輸入修改數量" value="'+data[i]['stockin_quantity']+'"></td><td class="text-right">'+data[i]['stockin_date']+'</td></tr>';
                            $('#stockinModifyRecord').append(record);
                        }
                        $('#stockinModifyModalLabel').html(label);
                        $('#purchaseQty').html(purchaseQty);
                    }
                }
            });
        $('#stockinModifyModal').modal('show');
    }

    function confirmOrder(id)
    {
        let form = $('#multiProcessForm');
        form.append($('<input type="hidden" class="formappend" name="id[0]">').val(id));
        form.append($('<input type="hidden" class="formappend" name="method" value="selected">'));
        form.append($('<input type="hidden" class="formappend" name="cate" value="confirmOrder">'));
        form.append($('<input type="hidden" class="formappend" name="type" value="oneOrder">'));
        form.append( $('<input type="hidden" class="formappend" name="filename" value="確認訂單_單一訂單">') );
        form.append( $('<input type="hidden" class="formappend" name="model" value="purchase">') );
        form.submit();
    }
</script>
@endsection
