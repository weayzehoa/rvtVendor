@extends('vendor.layouts.master')

@section('title', '行銷策展')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1 class="m-0 text-dark"><b>行銷策展</b><small> ({{ isset($curation) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('curation') }}">行銷策展</a></li>
                        <li class="breadcrumb-item active">{{ isset($curation) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">資料設定</h3>
                </div>
                <div class="card-body">
                    @if(isset($curation))
                    <form id="myform" action="{{ route('vendor.curation.update', $curation->id) }}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PATCH">
                    @else
                    <form id="myform" action="{{ route('vendor.curation.store') }}" method="POST" enctype="multipart/form-data">
                    @endif
                        <input type="hidden" name="vendor_id" value="{{ Auth::user()->vendor_id }}">
                        <input type="hidden" name="category" value="vendor">
                        <input type="hidden" name="type" value="product">
                        <input type="hidden" name="columns" value="5">
                        @csrf
                        <div class="row">
                            <div class="form-group col-12">
                                <div class="row">
                                    <nav class="w-100">
                                        <div class="nav nav-tabs" id="curation-tab" role="tablist">
                                            <a class="nav-item nav-link active" id="curation-chinese-tab" data-toggle="tab" href="#curation-chinese" role="tab" aria-controls="curation-chinese" aria-selected="true">中文</a>
                                            @for($i=0;$i<count($langs);$i++)
                                            <a class="nav-item nav-link" id="curation-{{ $langs[$i]['code'] }}-tab" data-toggle="tab" href="#curation-{{ $langs[$i]['code'] }}" role="tab" aria-controls="curation-{{ $langs[$i]['code'] }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                            @endfor
                                        </div>
                                    </nav>
                                    <div class="col-12 tab-content p-3" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="curation-chinese" role="tabpanel" aria-labelledby="curation-chinese-tab">
                                            <div class="row">
                                                <div class="form-group col-5">
                                                    <label for="main_title"><span class="text-red">* </span>主標題</label>
                                                    <input type="text" class="form-control {{ $errors->has('main_title') ? ' is-invalid' : '' }}" id="main_title" name="main_title" value="{{ $curation->main_title ?? '' }}" placeholder="請輸入主標題">
                                                    @if ($errors->has('main_title'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('main_title') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="show_main_title">顯示主標題</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_main_title == 1 ? 'checked' : '' : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="form-group col-5">
                                                    <label for="sub_title">副標題</label>
                                                    <input type="text" class="form-control {{ $errors->has('sub_title') ? ' is-invalid' : '' }}" id="sub_title" name="sub_title" value="{{ $curation->sub_title ?? '' }}" placeholder="請輸入副標題">
                                                    @if ($errors->has('sub_title'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('sub_title') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="show_sub_title">顯示副標題</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_sub_title == 1 ? 'checked' : '' : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @for($i=0;$i<count($langs);$i++)
                                        <div class="tab-pane fade" id="curation-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curation-{{ $langs[$i]['code'] }}-tab">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label>{{ $langs[$i]['name'] }}主標題</label>
                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['main_title'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label>{{ $langs[$i]['name'] }}副標題</label>
                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['sub_title'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                    </div>
                                    <div class="form-group col-5">
                                        <label for="datetime">策展時間區間: (未填寫則期間無限)</label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datetimepicker" id="start_time" name="start_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($curation) ? $curation->start_time : '' }}" autocomplete="off">
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datetimepicker" id="end_time" name="end_time" placeholder="格式：2018-06-16 15:55:00" value="{{ isset($curation) ? $curation->end_time : '' }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="is_on">啟用狀態</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->is_on == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        @if(empty($curation))
                                        <h3 class="text-danger">新增時，請先設定好基本資料並儲存後，才能選擇產品資料，設定好產品資料後再啟用。</h3>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center bg-white">
                            <button type="submit" class="btn btn-primary">{{ isset($curation) ? '修改' : '新增' }}</button>
                            <a href="{{ url('curations') }}" class="btn btn-info">
                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @if(isset($curation))
            <div id="product" class="type">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">產品版型資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-sm bg-success edit-btn mr-2" value="product_data_add">{{ $curation->vendors ? '選擇' : '新增' }}</button>
                        <div class="product_data_add" style="display:none">
                            <div class="card-primary card-outline mt-2"></div>
                            <form class="curationProductForm" action="{{ route('vendor.curationProduct.store') }}" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <div class="row">
                                                <div class="col-5">
                                                    <label>產品列表</label>
                                                    <select id="productSelect" class="form-control" size="12" multiple="multiple">
                                                        @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-1">
                                                    <div>
                                                        <label>　</label><br><br><br>
                                                        <button type="button" id="productSelect_rightAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-right"></i></button>
                                                        <button type="button" id="productSelect_rightSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-right"></i></button>
                                                        <button type="button" id="productSelect_leftSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-left"></i></button>
                                                        <button type="button" id="productSelect_leftAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-left"></i></button>
                                                        <button type="submit" class="btn btn-success btn-block">儲存</button>
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <label>已選擇產品</label>
                                                    <select name="product_id[]" id="productSelect_to" class="form-control" size="12" multiple="multiple">
                                                        @if($curation->products)
                                                        @foreach($curation->products as $product)
                                                        <option value="{{ $product->product_id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-1 align-items-middle">
                                                    <label>　</label><br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="productSelect_move_up" class="btn btn-primary"><i class="fas fa-caret-up"></i></button>
                                                    </div>
                                                    <br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="productSelect_move_down" class="btn btn-primary"><i class="fas fa-caret-down"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-danger">只有已上架的產品才會顯示在前台與選擇框中。按下儲存後，未上架的產品資料將被清除。</div>
                                    </div>
                                </div>
                            </form>
                            <div class="card-primary card-outline mb-2"></div>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center align-middle">順序</th>
                                    <th width="15%" class="text-center align-middle">商品圖</th>
                                    <th width="30%" class="text-left align-middle">商品名稱</th>
                                    <th width="15%" class="text-left align-middle">上標文字</th>
                                    <th width="15%" class="text-left align-middle">下標文字</th>
                                    <th width="10%" class="text-center align-middle">排序</th>
                                    <th width="10%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->products) > 0)
                                @foreach($curation->products as $product)
                                <tr>
                                    <td class="text-center align-middle">
                                        {{ $product->sort }}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($product->image)
                                        <a href="{{ $product->image }}" data-toggle="lightbox" data-title="{{ $product->name .' 商品圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="product_image_{{ $product->id }}" src="{{ $product->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="product_image_{{ $product->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->name }}
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->curation_text_top }}
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->curation_text_bottom }}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($loop->iteration != 1)
                                        <a href="{{ url('curationProduct/sortup/' . $product->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($loop->iteration != count($curation->products))
                                        <a href="{{ url('curationProduct/sortdown/' . $product->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="product_{{ $product->id }}"><i class="fas fa-edit"></i></button>
                                        <form class="d-inline" action="{{ route('vendor.curationProduct.destroy', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="product_{{ $product->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationProductForm_product_{{ $product->id }}" action="{{ route('vendor.curationProduct.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="text-center">
                                                            @if($product->image)
                                                            <a href="{{ $product->image }}" data-toggle="lightbox" data-title="{{ $product->name .' 商品圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                                <img height="200" class="product_image_{{ $product->id }}" src="{{ $product->image }}" alt="">
                                                            </a>
                                                            <label>顯示商品第一張照片，不提供修改，修改請至<a href="{{ url('product/'.$product->product_id.'#product-image' ) }}">本商品</a>頁面</label>
                                                            @else
                                                            <a href="{{ url('products/show/'.$product->product_id.'#product-image' ) }}">
                                                                <img height="200" class="product_image_{{ $product->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            </a>
                                                            <label>此商品尚未上傳任何圖片，請至<a href="{{ url('products/'.$product->product_id.'#product-image' ) }}">本商品</a>頁面上傳</label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-8">
                                                        <div class="row">
                                                            <nav class="w-100">
                                                                <div class="nav nav-tabs" id="curationproduct-tab" role="tablist">
                                                                    <a class="nav-item nav-link active" id="curationproduct-chinese-{{ $product->id }}-tab" data-toggle="tab" href="#curationproduct-chinese-{{ $product->id }}" role="tab" aria-controls="curationproduct-chinese-{{ $product->id }}" aria-selected="true">中文</a>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <a class="nav-item nav-link" id="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}-tab" data-toggle="tab" href="#curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" role="tab" aria-controls="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                    @endfor
                                                                </div>
                                                            </nav>
                                                            <div class="col-12 tab-content" id="nav-tabContent">
                                                                <div class="tab-pane fade show active" id="curationproduct-chinese-{{ $product->id }}" role="tabpanel" aria-labelledby="curationproduct-chinese-{{ $product->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-6">
                                                                            <label>上標文字</label>
                                                                            <input type="text" class="form-control" name="curation_text_top" value="{{ $product->curation_text_top ?? '' }}" placeholder="請輸入上標文字">
                                                                        </div>
                                                                        <div class="form-group col-6">
                                                                            <label>下標文字</label>
                                                                            <input type="text" class="form-control" name="curation_text_bottom" value="{{ $product->curation_text_bottom ?? '' }}" placeholder="請輸入上標文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @for($i=0;$i<count($langs);$i++)
                                                                <div class="tab-pane fade" id="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" role="tabpanel" aria-labelledby="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-6">
                                                                            <label>{{ $langs[$i]['name'] }}上標文字</label>
                                                                            <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][curation_text_top]" value="{{ $langs[$i]['productdata'][$product->id]['curation_text_top'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}上標文字">
                                                                        </div>
                                                                        <div class="form-group col-6">
                                                                            <label>{{ $langs[$i]['name'] }}下標文字</label>
                                                                            <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][curation_text_bottom]" value="{{ $langs[$i]['productdata'][$product->id]['curation_text_botom'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}下標文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-1">
                                                        <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-left align-middle">
                                        <h3>尚未選擇資料</h3>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
    <form id="myproductform" action="{{ route('vendor.curationProduct.sort') }}" method="POST" class="form-inline" role="search">
        <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
        @csrf
    </form>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
{{-- Ekko Lightbox --}}
<link rel="stylesheet" href="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- 顏色選擇器 --}}
<script src="{{ asset('vendor/vanilla-picker/dist/vanilla-picker.min.js') }}"></script>
{{-- Ekko Lightbox --}}
<script src="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.min.js') }}"></script>
{{-- multiselect --}}
<script src="{{ asset('vendor/multiselect/dist/js/multiselect.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Ckeditor 4.x --}}
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\CurationsRequest', '#myform'); !!}
{!! JsValidator::formRequest('App\Http\Requests\CurationsLangRequest', '.myform_lang'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        // CKEDITOR.replaceAll('modal_content');
        CKEDITOR.replaceAll( function( textarea, config ) {
            if (textarea.className === "modal_content") {
                config.height = '30em';
                // config.customConfig = "/js/ckeditor-config.js";
                return true;
            }
            return false; //非上面判斷則關閉
        } );

        var parent = document.querySelector('#main_title_background');
        var picker = new Picker({
            popup: 'left',
            parent: parent,
            color: '#FFFFFFFF',
            onDone: function(color) {
                parent.style.background = color.hex;
                $("input[name=main_title_background]").val(color.hex);
            },
        });

        var parent2 = document.querySelector('#background_color');
        var picker2 = new Picker({
            popup: 'top',
            parent: parent2,
            color: '#FFFFFFFF',
            onDone: function(color) {
                parent2.style.background = color.hex;
                $("input[name=background_color]").val(color.hex);
            },
        });

        let checktype = $('input[name=type]:checked').val();
        let checkrows = $('input[name=rows]:checked').val();
        $('#'+checktype).show();
        if(checktype == 'header'){
            $('#layoutcolumns').hide();
        }else{
            $('#layoutcolumns').show();
        }
        if(checktype == 'block' || checktype == 'nowordblock'){
            $('#layoutrows').show();
            if(checkrows==2){
                $('.checkrows2').show();
            }else{
                $('.checkrows2').hide();
            }
        }else{
            $('#layoutrows').hide();
        }

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
        $('.edit-btn').click(function(e){
            $('.'+$(this).val()).toggle();
            if($(this).html() == '新增'){
                $(this).html('取消');
            }else if($(this).html() == '選擇'){
                $(this).html('取消');
            }else if($(this).html() == '取消'){
                $(this).html('新增');
            }
        });
        $('input[name=rows]').change(function(){
            if($(this).val() == 2){
                $('.checkrows2').show();
            }else{
                $('.checkrows2').hide();
            }
        });
        $('input[name=type]').change(function(){
            $('.type').hide();
            $('#'+$(this).val()).show();
            if($(this).val() == 'header'){
                $('#layoutcolumns').hide();
            }else{
                $('#layoutcolumns').show();
            }
            if($(this).val() != 'block' && $(this).val() != 'nowordblock'){
                $('#layoutrows').hide();
                let html = '';
                let checked = '';
                let columns = '{{ isset($curation) ? $curation->columns : 4}}';
                for(let i=2;i<=6;i++){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }else{
                $('#layoutrows').show();
                let html = '';
                let checked = '';
                let columns = '{{ isset($curation) ? $curation->columns : 4}}';
                for(let i=4; i<=8; i=i+4){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        $(document).ready(function($) {
            $('#vendorSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
            $('#productSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
        });

        $('.removeBackgroundImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                let form = $('#myform');
                form.append($('<input type="hidden" name="background_image" value="">'));
                $('#background_image_div').remove();
                $(this).parent().remove();
                $('label[for=background_image]').html('瀏覽選擇新圖片');
            }
        });

        $('.removeImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                var name = $(this).val().split('_')[0];
                var id = $(this).val().split('_')[1];
                if($(this).val() == 'background_image'){
                    var form = $('#myform');
                    form.append($('<input type="hidden" name="background_image" value="">'));
                    $('#background_image_div').remove();
                    $(this).parent().remove();
                    $('label[for=background_image]').html('瀏覽選擇新圖片');
                }else if(name == 'image'){
                    var form = $('.curationImageForm_image_'+id);
                    form.append($('<input type="hidden" name="image" value="">'));
                    $('.image_' + id).attr('src', '{{ asset("img/sample_upload.png") }}');
                    $(this).parent().remove();
                    $('label[for=image_'+id+']').html('瀏覽選擇新圖片');
                }else if(name == 'imglogo' || name == 'imgcover'){
                    var form = $('.curationVendorForm_vendor_'+id);
                    if(name == 'imglogo'){
                        form.append($('<input type="hidden" name="img_logo" value="">'));
                        $('.vendor_image_logo_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_logo_'+id+']').html('瀏覽選擇新圖片');
                    }else if(name == 'imgcover'){
                        form.append($('<input type="hidden" name="img_cover" value="">'));
                        $('.vendor_image_cover_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_cover_'+id+']').html('瀏覽選擇新圖片');
                    }
                }
            }
        });

        $('.sort-btn').click(function(){
            if($(this).val() == 'vendor'){
                $('.vendor-sort').prop('readonly',false);
                $(this).val('vendor-submit');
                $('#vendor_sort_text').html('儲存排序');
            }else if($(this).val() == 'product'){
                $('.product-sort').prop('readonly',false);
                $(this).val('product-submit');
                $('#product_sort_text').html('儲存排序');
            }else if($(this).val() == 'vendor-submit' || $(this).val() == 'product-submit'){
                if($(this).val() == 'vendor-submit'){
                    var form = $('#myvendorform');
                    var ids = $('.vendor-sort').serializeArray().map( item => item.name );
                    var sorts = $('.vendor-sort').serializeArray().map( item => item.value );
                }else if($(this).val() == 'product-submit'){
                    var form = $('#myproductform');
                    var ids = $('.product-sort').serializeArray().map( item => item.name );
                    var sorts = $('.product-sort').serializeArray().map( item => item.value );
                }
                for(let j=0; j<ids.length;j++){
                    var tmp = '';
                    var tmp2 = '';
                    tmp = $('<input type="hidden" class="formappend" name="id['+j+']" value="'+ids[j]+'">');
                    tmp2 = $('<input type="hidden" class="formappend" name="sort['+j+']" value="'+sorts[j]+'">');
                    form.append(tmp);
                    form.append(tmp2);
                }
                form.submit();
            }
        });

        $('#selectByCategory').change(function(){
            $('input[name=keyword]').val('');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search($(this).val(),'');
            }
        });

        $('#selectByVendor').change(function(){
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            $('input[name=keyword]').val('');
            if($(this).val()){
                search('','',$(this).val());
            }
        });

        $('.search-btn').click(function(){
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            var keyword = $('#keyword').val();
            if(keyword){
                search('',keyword,'');
            }
        });
        $('input[name=keyword]').keyup(function(){
            // $('#selectByVendor').val(null).trigger('selected');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search('',$(this).val(),'');
            }
        });

    })(jQuery);

    $('input[type=file]').change(function(x) {
        // name = this.name;
        name = this.id;
        oldimg = '{{ asset("img/sample_upload.png") }}';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
                alert('請注意，此圖片尚未實際上傳到伺服器，待按下新增或修改按鈕才會實際上傳到伺服器。');
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇新圖片');
                $('.' + name).attr('src', oldimg); //沒照片時還原
            }
        } else {
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', oldimg); //沒照片時還原
        }
    });

    function readURL(input, imgclass) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imgclass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function checkFileName(filename) {
        var chk = 0;
        var specialSymbols = new Array("`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "+", "=", "(", ")", "[", "]", "{", "}", "<", ">", "/", "?", ":", ";", "'", "\"", "\\", "|");
        for (j = 0; j < specialSymbols.length; j++) {
            if (filename.indexOf(specialSymbols[j]) >= 0) {
                chk++;
            }
        }
        if (chk >= 1) {
            alert("檔案名稱中不可含有兩個\"點\"符號或下列特殊符號。\n(  ` ~ ! @ # $ % ^ & * + = ( ) [ ] { } < > / ? : ; ' \" \\ |  )");
            return false;
        } else {
            return true;
        }
    }

    function checkFileSize(size) {
        if (size > 10240 * 1024) {
            alert('檔案大小超過10MB');
            return false;
        } else {
            return true;
        }
    }

    function checkFileExt(ext) {
        if ($.inArray(ext, ['.png', '.jpg', '.jpeg', '.gif', '.svg']) == -1) {
            alert('檔案格式不被允許，限JPG、PNG、GIF或SVG格式');
            return false;
        } else {
            return true;
        }
    }

    function checkMyImage(input) {
        if (input) {
            var filename = input[0].name;
            var size = input[0].size;
            var ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
            if (checkFileName(filename)) {
                if (checkFileExt(ext)) {
                    if (checkFileSize(size)) {
                        return filename;
                    }
                }
            }
        }
    }

    function search(category,keyword,vendor){
        let token = '{{ csrf_token() }}';
        // let id = '{{ isset($curation) ? $curation->id : '' }}';
        let selected = $('#productSelect_to').find('option');
        let ids = [];
        for(let x=0;x<selected.length;x++){
            ids[x] = selected[x].value;
        }
        $.ajax({
            type: "post",
            url: 'getproducts',
            data: {ids: ids, category: category, keyword: keyword, vendor: vendor, _token: token },
            success: function(data) {
                var options = '';
                for(let i=0;i<data.length;i++){
                    options +='<option value="'+data[i]['id']+'">'+data[i]['name']+'</option>';
                }
                $('#productSelect').html(options);
            }
        });
    }

</script>
@endsection
