@extends('vendor.layouts.master')

@section('title', '商品管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商品管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('product') }}">商品管理</a></li>
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
                            <div class="float-left">
                                <a href="{{ route('vendor.product.create') }}" class="btn btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                <button id="showForm" class="btn btn-sm btn-success mr-5" title="使用欄位查詢">使用欄位查詢</button>
                                <a href="{{ url('product/?status=-1') }}" class="btn btn-sm btn-info mr-2"><i class="fas fa-search"></i> 未送審</a>
                                <a href="{{ url('product/?status=0') }}" class="btn btn-sm btn-purple mr-2"><i class="fas fa-search"></i> 送審中</a>
                                <a href="{{ url('product/?status=-2') }}" class="btn btn-sm btn-danger mr-2"><i class="fas fa-search"></i> 審核不通過</a>
                                <a href="{{ url('product/?status=-3') }}" class="btn btn-sm btn-warning mr-2"><i class="fas fa-search"></i> 停售中</a>
                                <a href="{{ url('product/?status=-9') }}" class="btn btn-sm btn-secondary mr-2"><i class="fas fa-search"></i> 已下架</a>
                                <a href="{{ url('product/?status=1') }}" class="btn btn-sm btn-success mr-2"><i class="fas fa-search"></i> 已上架</a>
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($products->total()) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('product') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-10 offset-1">
                                        <div class="col-6 mt-2">
                                            <label for="status">商品狀態: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="status" size="6" multiple>
                                                <option value="1" {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>上架中</option>
                                                <option value="2" {{ isset($status) ? in_array(2,explode(',',$status)) ? 'selected' : '' : 'selected' }}>待審核</option>
                                                <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>未送審(草稿)</option>
                                                <option value="-2" {{ isset($status) ? in_array(-2,explode(',',$status)) ? 'selected' : '' : 'selected' }}>審核不通過</option>
                                                <option value="-3" {{ isset($status) ? in_array(-3,explode(',',$status)) ? 'selected' : '' : 'selected' }}>暫停銷售</option>
                                                <option value="-9" {{ isset($status) ? in_array(-9,explode(',',$status)) ? 'selected' : '' : 'selected' }}>已下架</option>
                                            </select><input type="hidden" value="-9,-3,-2,-1,1,2" name="status" id="status_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="shipping_method">物流方式: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="shipping_methods" size="6" multiple>
                                                <option value="1" {{ isset($shipping_methods) ? in_array(1,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>機場提貨</option>
                                                <option value="2" {{ isset($shipping_methods) ? in_array(2,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>旅店提貨</option>
                                                <option value="3" {{ isset($shipping_methods) ? in_array(3,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>現場提貨</option>
                                                <option value="4" {{ isset($shipping_methods) ? in_array(4,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送海外</option>
                                                <option value="5" {{ isset($shipping_methods) ? in_array(5,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送台灣</option>
                                                <option value="6" {{ isset($shipping_methods) ? in_array(6,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送當地</option>
                                            </select><input type="hidden" value="1,2,3,4,5,6" name="shipping_methods" id="shipping_methods_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">上架時間區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="created_at" name="created_at" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at) && $created_at ? $created_at : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="created_at_end" name="created_at_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at_end) && $created_at_end ? $created_at_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">送審通過時間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time" name="pass_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time) && $pass_time ? $pass_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time_end" name="pass_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time_end) && $pass_time_end ? $pass_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-3 mt-2">
                                            <label class="mr-2">產品名稱:</label>
                                            <input type="text" class="form-control" id="sku" name="name" value="{{ isset($name) && $name ? $name : '' }}" placeholder="產品名稱" autocomplete="off">
                                        </div>
                                        <div class="col-3 mt-2">
                                            <label class="mr-2">產品貨號:</label>
                                            <input type="text" class="form-control" id="sku" name="sku" value="{{ isset($sku) && $sku ? $sku : '' }}" placeholder="產品貨號，EC or BOM" autocomplete="off">
                                        </div>
                                        <div class="col-2 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                {{-- <label class="mr-2">低於安全庫存:</label> --}}
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="low_quantity" name="low_quantity" value="1" {{ isset($low_quantity) && $low_quantity ? 'checked' : '' }}>
                                                    <label for="low_quantity">低於安全庫存</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                {{-- <label class="mr-2">庫存小於等於0:</label> --}}
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="zero_quantity" name="zero_quantity" value="1" {{ isset($zero_quantity) && $zero_quantity ? 'checked' : '' }}>
                                                    <label for="zero_quantity">庫存小於等於0</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2 mt-2">
                                            <label class="mr-2">每頁筆數</label>
                                            <select class="form-control" name="list">
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-sm text-center" width="5%">狀態</th>
                                        <th class="text-sm text-left" width="35%">品名/內容量</th>
                                        <th class="text-sm text-left" width="38%">
                                            <div class="row">
                                                <div class="text-sm col-4 text-left">款式名稱</div>
                                                <div class="text-sm col-4 text-left">貨號</div>
                                                <div class="text-sm col-1 text-right">庫存</div>
                                                <div class="text-sm col-1 text-right">安全</div>
                                                <div class="text-sm col-2 text-center">調整</div>
                                            </div>
                                        </th>
                                        <th class="text-sm text-right" width="5%">單價</th>
                                        <th class="text-sm text-center" width="4%">下架</th>
                                        <th class="text-sm text-center" width="4%">暫停<br>恢復</th>
                                        <th class="text-sm text-center" width="4%">刪除</th>
                                        <th class="text-sm text-center" width="4%">複製</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td class="text-center align-middle text-sm">
                                            @if($product->status == 1)
                                            <span class="right badge badge-success">上架中</span>
                                            @elseif($product->status == 0)
                                            <span class="right badge badge-purple">待審核</span>
                                            @elseif($product->status == -9)
                                            <span class="right badge badge-secondary">已下架</span>
                                            @elseif($product->status == -3)
                                            @if(!empty($product->pause_reason))
                                            <span class="right badge badge-warning">商家暫停銷售</span>
                                            @else
                                            <span class="right badge badge-warning">iCarry暫停銷售</span>
                                            @endif
                                            @elseif($product->status == -2)
                                            <span class="right badge badge-danger">審核不通過</span>
                                            @elseif($product->status == -1)
                                            <span class="right badge badge-info">未送審(草稿)</span>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <div class="col-12 text-warp">
                                            <a href="{{ route('vendor.product.show', $product->id ) }}">{{ $product->name }}</a>
                                            <span class="text-xs bg-info mr-2">{{ $product->serving_size }}</span>
                                            <span class="text-xs bg-success">{{ $product->categories }}</span>
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <div class="row">
                                                @foreach($product->models as $model)
                                                    <div class="col-4 text-left text-sm">{{ $model->name }}</div>
                                                    <div class="col-4 text-left text-sm">{{ $model->sku }}</div>
                                                    <div class="col-1 text-right text-sm">
                                                        @if($model->quantity < $model->safe_quantity)
                                                        <span class="text-danger"><b id="quantity_{{ $model->id }}">{{ number_format($model->quantity) }}</b></span>
                                                        @else
                                                        <span id="quantity_{{ $model->id }}">{{ number_format($model->quantity) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-1 text-right text-sm">{{ $model->safe_quantity }}</div>
                                                    <div class="col-2 text-center text-sm"><a href="javascript:" onclick="getstockrecord({{ $model->id }})"><span class="right badge badge-primary">調整</span></a></div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-right align-middle text-sm"><span class="text-primary"><b>{{ number_format($product->price) }}</b></span></td>
                                        <td class="text-center align-middle text-sm">
                                            @if(in_array($product->status,[1,-3]))
                                            <form action="{{ route('vendor.product.changeStatus', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="down">
                                                <button type="button" class="btn btn-sm btn-primary opbtn" value="下架" title="下架"><i class="fas fa-store-slash"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            @if($product->status == 1)
                                            <form action="{{ route('vendor.product.changeStatus', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="pause">
                                                <button type="button" class="btn btn-sm btn-warning opbtn" value="暫停銷售" title="暫停銷售"><i class="fas fa-hand-paper"></i></button>
                                            </form>
                                            @endif
                                            @if($product->status == -3)
                                            <form action="{{ route('vendor.product.changeStatus', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="relaunch">
                                                <button type="button" class="btn btn-sm btn-success opbtn" value="恢復銷售" title="恢復銷售"><i class="far fa-thumbs-up"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            @if(in_array($product->status,[0,-1,-2,-9]))
                                            <form action="{{ route('vendor.product.destroy', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger opbtn" value="刪除" title="刪除"><i class="far fa-trash-alt"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            <a href="{{ url('copy/'.$product->id) }}" class="btn btn-sm btn-secondary" title="複製">
                                                <i class="far fa-copy"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($products->total()) ?? 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $products->appends($appends)->render() }}
                                @else
                                {{ $products->render() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('modal')
{{-- 庫存調整 Modal --}}
<div id="myModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-sm">
                    <form id="modalForm">
                    <input type="hidden" name="product_model_id">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">貨號</span>
                        </div>
                        <div class="input-group-prepend">
                            <input type="text" class="form-control" name="sku_text" disabled>
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">安全庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="safe_quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">調整原因</span>
                        </div>
                        <div class="input-group-prepend" style="width:30%" >
                            <input type="text" class="form-control" name="reason" placeholder="非必填">
                        </div>
                        <div class="input-group-append">
                            <span id="stockmodify" class="btn btn-sm btn-danger">更新</span>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="form-group form-group-sm">
                    <label for="message-text" class="col-form-label">修改紀錄</label>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="10%" class="text-right">修改前庫存</th>
                                            <th width="10%" class="text-right">增減數量</th>
                                            <th width="10%" class="text-right">修改後庫存</th>
                                            <th width="25%">原因理由</th>
                                            <th width="10%">修改者</th>
                                            <th width="10%">庫存調整時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="record"></tbody>
                            </table>
                        </div>
                        </div>
                    </div>
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
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('.opbtn').click(function (e) {
            let text = $(this).val();
            if(text == '暫停銷售'){
                let reason = prompt("請輸入暫停銷售理由");
                if(reason){
                    let input = '<input type="hidden" name="reason" value="'+reason+'">';
                    $(this).parents('form').append(input);
                    $(this).parents('form').submit();
                }
            }else if(text == '下架'){
                let reason = prompt("請輸入下架理由");
                if(reason){
                    let input = '<input type="hidden" name="reason" value="'+reason+'">';
                    $(this).parents('form').append(input);
                    $(this).parents('form').submit();
                }
            }else if(text == '刪除'){
                let reason = prompt("請輸入刪除理由");
                if(reason){
                    let input = '<input type="hidden" name="reason" value="'+reason+'">';
                    $(this).parents('form').append(input);
                    $(this).parents('form').submit();
                }
            }else{
                if(confirm('請確認是否要'+text+'這個商品?')){
                    $(this).parents('form').append('<input type="hidden" name="reason" value="">');
                    $(this).parents('form').submit();
                };
            }
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('#stockmodify').click(function(){
            let product_model_id = $('input[name=product_model_id]').val();
            let quantity = $('input[name=quantity]').val();
            let safe_quantity = $('input[name=safe_quantity]').val();
            safe_quantity == 0 ? safe_quantity = 1 : '';
            let reason = $('input[name=reason]').val();
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'product/stockmodify',
                data: { product_model_id: product_model_id, quantity: quantity, safe_quantity: safe_quantity, reason: reason, _token: token },
                success: function(data) {
                    if(data['productQtyRecord']){
                        let x = $('.record').length;
                        let dateTime = new Date(data['productQtyRecord']['create_time']).toISOString().slice(0, 10);
                        let timestamp = new Date(data['productQtyRecord']['create_time']).getTime();
                        let count = data['productQtyRecord']['after_quantity'] - data['productQtyRecord']['before_quantity'];
                        let record = '';
                        record += '<tr class="record"><td class="align-middle">'+(x+1)+'</td><td class="align-middle text-right">'+data['productQtyRecord']['before_quantity']+'</td><td class="align-middle text-right">'+count+'</td><td class="align-middle text-right">'+data['productQtyRecord']['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord']['reason']+'</td>';
                        if(data['productQtyRecord']['admin'] != null){
                            record += '<td class="align-middle">iCarry-'+data['productQtyRecord']['admin']+'</td>';
                        }else if(data['productQtyRecord']['vendor'] != null){
                            record += '<td class="align-middle">廠商-'+data['productQtyRecord']['vendor']+'</td>';
                        }else{
                            record += '<td class="align-middle"></td>';
                        }
                        record += '<td class="align-middle">'+dateTime+'</td></tr>';
                        $('#record').prepend(record);
                        $('#quantity_'+product_model_id).html(quantity);
                        $('#safe_quantity_'+product_model_id).html(safe_quantity);
                    }else{
                        $('#safe_quantity_'+product_model_id).html(safe_quantity);
                        $('#myModal').modal('hide');
                    }
                }
            });
        });
    })(jQuery);

    function formSearch(){
        let sel="";
        $("#shipping_methods>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_methods_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        if($('#created_at').val() && $('#created_at_end').val() && ($('#created_at').val() > $('#created_at_end').val())){
            alert('上架時間區間的開始時間不能小於結束時間!');
        }else if($('#pass_time').val() && $('#pass_time_end').val() && ($('#pass_time').val() > $('#pass_time_end').val())){
            alert('送審通過區間的開始時間不能小於結束時間!');
        }else{
            $("#myForm").submit();
        }
    }

    function getstockrecord(id){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#myModal').modal('show');
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: 'product/getstockrecord',
            data: { id: id, _token: token },
            success: function(data) {
                let type = data['product']['model_type'];
                let spec = '';
                let name = '';
                if(type == 1){
                    spec = '單一規格';
                    name = data['product']['name'];
                }else if(type == 2){
                    spec = '多款規格';
                    name = data['productModel']['name'];
                }else if(type == 3){
                    spec = '組合商品';
                    name = data['productModel']['name'];
                }
                $('#ModalLabel').html('<span class="text-primary">'+name +'</span> > <span class="text-danger">'+spec+'</span> > 商品庫存調整');
                let record = '';
                for(i=0;i<data['productQtyRecord'].length;i++){
                    data['productQtyRecord'][i]['reason'] == null ? data['productQtyRecord'][i]['reason'] = '' : '';
                    let x = data['productQtyRecord'].length - i;
                    let dateTime = new Date(data['productQtyRecord'][i]['create_time']).toISOString().slice(0, 10);
                    let timestamp = new Date(data['productQtyRecord'][i]['create_time']).getTime();
                    count = data['productQtyRecord'][i]['after_quantity'] - data['productQtyRecord'][i]['before_quantity'];
                    record += '<tr class="record"><td class="align-middle">'+(x)+'</td><td class="align-middle text-right">'+data['productQtyRecord'][i]['before_quantity']+'</td><td class="align-middle text-right">'+count+'</td><td class="align-middle text-right">'+data['productQtyRecord'][i]['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord'][i]['reason']+'</td>';
                    if(data['productQtyRecord'][i]['admin'] != null){
                        record += '<td class="align-middle">iCarry-'+data['productQtyRecord'][i]['admin']+'</td>';
                    }else if(data['productQtyRecord'][i]['vendor'] != null){
                        record += '<td class="align-middle">廠商-'+data['productQtyRecord'][i]['vendor']+'</td>';
                    }else{
                        record += '<td class="align-middle"></td>';
                    }
                    record += '<td class="align-middle">'+dateTime+'</td></tr>';
                }
                $('#record').html(record);
                $('input[name=product_model_id]').val(data['productModel']['id']);
                $('input[name=quantity]').val(data['productModel']['quantity']);
                $('input[name=safe_quantity]').val(data['productModel']['safe_quantity']);
                $('input[name=sku_text]').val(data['productModel']['sku']);
            }
        });
    }
</script>
@endsection
