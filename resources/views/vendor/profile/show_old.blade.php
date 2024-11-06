@extends('vendor.layouts.master')

@section('title', '商家資料')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家資料</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('profile') }}">商家資料</a></li>
                        <li class="breadcrumb-item active">{{ isset($vendor) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $vendor->company }} 商家資料</h3>
                </div>
                <div class="card-body">
                    <form id="myform" action="{{ route('vendor.profile.update', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PATCH">
                        @csrf
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
                                    <div class="form-group col-3">
                                        <label for="name"><span class="text-red">* </span>店名或品牌</label>
                                        <h4 class="text-purple">{{ $vendor->name }}</h4>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="company"><span class="text-red">* </span>公司名稱</label>
                                        <h4 class="text-purple">{{ $vendor->company }}</h4>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="VAT_number"><span class="text-red">* </span>公司統編</label>
                                        <h4 class="text-purple">{{ $vendor->VAT_number }}</h4>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="boss"><span class="text-red">* </span>負責人</label>
                                        <h4 class="text-purple" id="boss">{{ $vendor->boss }}</h4>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="contact_person"><span class="text-red">* </span>聯絡人 <a href="javascript:$('#contact').val($('#boss').html());void(0);" class=" btn-link"> (同負責人) </a></label>
                                        <input type="text" class="form-control {{ $errors->has('contact_person') ? ' is-invalid' : '' }}" id="contact" name="contact_person" value="{{ $vendor->contact_person ?? '' }}" placeholder="聯絡人姓名">
                                        @if ($errors->has('contact_person'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('contact_person') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="tel"><span class="text-red">* </span>電話</label>
                                        <input type="text" class="form-control {{ $errors->has('tel') ? ' is-invalid' : '' }}" id="tel" name="tel" value="{{ $vendor->tel ?? '' }}" placeholder="聯絡人電話號碼">
                                        @if ($errors->has('tel'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('tel') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="fax">傳真</label>
                                        <input type="text" class="form-control {{ $errors->has('fax') ? ' is-invalid' : '' }}" id="fax" name="fax" value="{{ $vendor->fax ?? '' }}" placeholder="傳真號碼">
                                        @if ($errors->has('fax'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('fax') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-3">
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="email"><span class="text-red">* </span>電子信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                        <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ $vendor->email ?? '' }}" placeholder="聯絡人電子信箱" required>
                                        @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="notify_email"><span class="text-red">* </span>採購通知信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                        <input type="email" class="form-control {{ $errors->has('notify_email') ? ' is-invalid' : '' }}" id="notify_email" name="notify_email" value="{{ $vendor->notify_email ?? '' }}" placeholder="採購通知電子信箱" required>
                                        @if ($errors->has('notify_email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('notify_email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="bill_email"><span class="text-red">* </span>對帳通知信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                        <input type="email" class="form-control {{ $errors->has('bill_email') ? ' is-invalid' : '' }}" id="bill_email" name="bill_email" value="{{ $vendor->bill_email ?? '' }}" placeholder="對帳通知電子信箱" required>
                                        @if ($errors->has('bill_email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bill_email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="address"><span class="text-red">* </span>地址</label>
                                        <input type="text" class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}" id="address" name="address" value="{{ $vendor->address ?? '' }}" placeholder="公司地址">
                                        @if ($errors->has('address'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('address') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="factory_address">工廠地址(收貨地址) <a href="javascript:$('#factory_address').val($('#address').val());void(0);" class=" btn-link"> (同地址) </a></label>
                                        <input type="text" class="form-control {{ $errors->has('factory_address') ? ' is-invalid' : '' }}" id="factory_address" name="factory_address" value="{{ $vendor->factory_address ?? '' }}" placeholder="工廠地址(發貨地址)">
                                        @if ($errors->has('factory_address'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('factory_address') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-12">
                                        <label for="categories"><span class="text-red">* </span>設定分類</label>
                                        <div class="row">
                                            @foreach($categories as $category)
                                            <div class="col-3">
                                                <input type="checkbox" id="cachk{{ $category->id }}" value="{{ $category->id }}" {{ isset($vendor) ? in_array($category->id,explode(',',$vendor->categories)) ? 'checked' : '' : '' }} disabled><span> {{ $category->name }}</span>
                                                <br>　<span class="text-purple text-sm">{{ $category->intro }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        @if ($errors->has('categories'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('categories') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="product_sold_country"><span class="text-red">* </span>發貨地區</label>
                                        <select class="form-control{{ $errors->has('product_sold_country') ? ' is-invalid' : '' }}" id="product_sold_country" disabled>
                                            <option value="">請選擇發貨地區</option>
                                            <option value="台灣" {{ isset($vendor) ? $vendor->product_sold_country == '台灣' ? 'selected' : '' : '' }}>台灣</option>
                                            <option value="日本" {{ isset($vendor) ? $vendor->product_sold_country == '日本' ? 'selected' : '' : '' }}>日本</option>
                                        </select>
                                        @if ($errors->has('product_sold_country'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('product_sold_country') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="shipping_setup"><span class="text-red">* </span>免運門檻</label>
                                        {{-- <a href="javascript:$('#shipping_setup').val(0);void(0);" class=" btn-link"> (免運) </a> <a href="javascript:$('#shipping_setup').val(99999999);void(0);" class="btn-link">(不提供)</a> --}}
                                        <input type="text" class="form-control {{ $errors->has('shipping_setup') ? ' is-invalid' : '' }}" id="shipping_setup" name="shipping_setup" value="{{ $vendor->shipping_setup == '99999999' ? '不提供免運費優惠' : '' }}" placeholder="設定免運門檻" disabled>
                                        @if ($errors->has('shipping_setup'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('shipping_setup') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="shipping_verdor_percent"><span class="text-red">* </span>商家補貼運費</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-truck"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="shipping_verdor_percent" value="{{ $vendor->shipping_verdor_percent ?? '0' }}" placeholder="商家補貼運費(%)" disabled>
                                            <div class="input-group-append">
                                                <div class="input-group-text"><i class="fas fa-percent"></i></div>
                                            </div>
                                        </div>
                                        @if ($errors->has('shipping_verdor_percent'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('shipping_verdor_percent') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="description">服務費</label>
                                        <div class="row">
                                            @foreach($serviceFees as $serviceFee)
                                            <div class="col-3">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            {{ $serviceFee->name }}
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control" id="service_fee{{ $loop->iteration }}" value="{{ isset($serviceFees) ? $serviceFee->percent ? $serviceFee->percent.' %' : '0 %' : '0 %' }}" readonly>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    {{-- <div class="form-group col-6">
                                        <label for="summary"><span class="text-red">* </span>簡介</label>
                                        <textarea rows="10" class="form-control {{ $errors->has('summary') ? ' is-invalid' : '' }}" name="summary" placeholder="簡短介紹...">{{ $vendor->summary ?? '' }}</textarea>
                                        @if ($errors->has('summary'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('summary') }}</strong>
                                        </span>
                                        @endif
                                    </div> --}}
                                    <div class="form-group col-6">
                                        <label for="description"><span class="text-red">* </span>描述</label>
                                        <textarea rows="10" class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}" name="description" placeholder="詳細描述...">{{ $vendor->description ?? '' }}</textarea>
                                        @if ($errors->has('description'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('description') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    {{-- <div class="form-group col-12">
                                        <label for="pause_start_date">暫停出貨區間<span class="text-red">(設定此區間從起始開始變更商品最快出貨日為結束日期)</span></label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datepicker" id="pause_start_date" name="pause_start_date" placeholder="格式：2016-06-06" value="{{ $vendor->pause_start_date ?? '' }}" autocomplete="off" />
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datepicker" id="pause_end_date" name="pause_end_date" placeholder="格式：2016-06-06" value="{{ $vendor->pause_end_date ?? '' }}" autocomplete="off" />
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                            @for($i=0;$i<count($langs);$i++)
                            <div class="tab-pane fade" id="curation-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curation-{{ $langs[$i]['code'] }}-tab">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label>{{ $langs[$i]['name'] }}店名/品牌</label>
                                        <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][name]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['name'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}店名/品牌">
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="caption">{{ $langs[$i]['name'] }}簡介</label>
                                        <textarea rows="5" class="form-control" name="langs[{{ $langs[$i]['code'] }}][summary]" placeholder="請輸入{{ $langs[$i]['name'] }}簡介">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['summary'] : '' }}</textarea>
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="caption">{{ $langs[$i]['name'] }}描述</label>
                                        <textarea rows="5" class="form-control" name="langs[{{ $langs[$i]['code'] }}][description]" placeholder="請輸入{{ $langs[$i]['name'] }}描述">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['description'] : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="text-center bg-white">
                            <button type="submit" class="btn btn-primary">修改</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">照片資料</h3>
                </div>
                @if(isset($vendor))
                <form class="img_upload" action="{{ route('vendor.profile.upload', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 card card-primary card-outline">
                                <div class="card-body">
                                    <div class="text-center mb-2">
                                        <h3>商家主視覺</h3>
                                        <img width="100%" class="img_cover" src="{{ $vendor->img_cover ?? '' }}" alt="">
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="file" id="img_cover" name="img_cover" class="custom-file-input {{ $errors->has('img_cover') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                            <label class="custom-file-label" for="img_cover">瀏覽選擇新圖片</label>
                                        </div>
                                        <p>上傳後等比縮放為1440x760</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 card card-primary card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center mb-2">
                                        <h3>LOGO</h3>
                                        <img width="100%" class="img_logo" src="{{ $vendor->img_logo ?? '' }}" alt="">
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="file" id="img_logo" name="img_logo" class="custom-file-input {{ $errors->has('img_logo') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                            <label class="custom-file-label" for="img_logo">瀏覽選擇新圖片</label>
                                        </div>
                                        <p>上傳後等比縮放為540x360</p>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-4 card card-primary card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center mb-2">
                                        <h3>網站滿版圖</h3>
                                        <img width="100%" class="img_site" src="{{ $vendor->img_site ?? '' }}" alt="">
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="file" id="img_site" name="img_site" class="custom-file-input {{ $errors->has('img_site') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                            <label class="custom-file-label" for="img_site">瀏覽選擇新圖片</label>
                                        </div>
                                        <p>上傳後等比縮放為1440x760</p>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                        <div class="text-center bg-white">
                            <button type="submit" class="btn btn-primary">送出</button>
                            <a href="{{ url('vendors') }}" class="btn btn-info">
                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                            </a>
                        </div>
                    </div>
                </form>
                @else
                <div class="card-body">
                    <h3>請先建立商家資料</h3>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\VendorsRequest', '#myform'); !!}
{!! JsValidator::formRequest('App\Http\Requests\VendorsUploadRequest', '.img_upload'); !!}
{!! JsValidator::formRequest('App\Http\Requests\VendorsLangRequest', '.myform_lang'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        var tab = window.location.hash;
    if(tab){
        $('#vendor-desc-tab').removeClass('active');
        $('#vendor-desc').removeClass('active');
        $('#vendor-desc').removeClass('show');
        $(tab+'-tab').addClass('active');
        $(tab).addClass('active');
        $(tab).addClass('show');
    }else{
        $('#vendor-desc-tab').addClass('active');
        $('#vendor-desc').addClass('active');
        $('#vendor-desc').addClass('show');
    }

        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        $('#stockmodify').click(function(){
            let product_model_id = $('input[name=product_model_id]').val();
            let quantity = $('input[name=quantity]').val();
            let safe_quantity = $('input[name=safe_quantity]').val();
            let reason = $('input[name=reason]').val();
            let token = '{{ csrf_token() }}';
            let url = '{{ url("admin/products/stockmodify") }}';
            $.ajax({
                type: "post",
                url: url,
                data: { product_model_id: product_model_id, quantity: quantity, safe_quantity: safe_quantity, reason: reason, _token: token },
                success: function(data) {
                    if(data['productQtyRecord']){
                        let x = $('.record').length;
                        let dateTime = new Date(data['productQtyRecord']['created_at']);
                        let timestamp = new Date(data['productQtyRecord']['created_at']).getTime();
                        let count = data['productQtyRecord']['after_quantity'] - data['productQtyRecord']['before_quantity'];
                        let record = '<tr class="record"><td class="align-middle">'+(x+1)+'</td><td class="align-middle">'+data['productQtyRecord']['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord']['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord']['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                        $('#record').prepend(record);
                        $('#quantity_'+product_model_id).html(data['productQtyRecord']['after_quantity']);
                    }else{
                        alert('新舊庫存未改變');
                    }
                }
            });
        });
    })(jQuery);

    // 舊的image網址及屬性
    img_cover = $('.img_cover').attr('src');
    img_logo = $('.img_logo').attr('src');
    img_site = $('.img_site').attr('src');
    $('input[type=file]').change(function(x) {
        name = this.name;
        name == 'img_cover' ? oldimg = img_cover : '';
        name == 'img_logo' ? oldimg = img_logo : '';
        name == 'img_site' ? oldimg = img_site : '';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
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

    function getstockrecord(id){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#myModal').modal('show');
        let token = '{{ csrf_token() }}';
        let url = '{{ url("admin/products/getstockrecord") }}';
        $.ajax({
            type: "post",
            url: url,
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
                    let x = data['productQtyRecord'].length - i;
                    let dateTime = new Date(data['productQtyRecord'][i]['created_at']);
                    let timestamp = new Date(data['productQtyRecord'][i]['created_at']).getTime();
                    count = data['productQtyRecord'][i]['after_quantity'] - data['productQtyRecord'][i]['before_quantity'];
                    record = record + '<tr class="record"><td class="align-middle">'+(x)+'</td><td class="align-middle">'+data['productQtyRecord'][i]['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord'][i]['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord'][i]['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                }
                $('#record').html(record);
                $('input[name=product_model_id]').val(data['productModel']['id']);
                $('input[name=quantity]').val(data['productModel']['quantity']);
                $('input[name=safe_quantity]').val(data['productModel']['safe_quantity']);
                $('span[name=sku]').html(data['productModel']['sku']);
            }
        });
    }

</script>
@endsection
