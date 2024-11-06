@extends('vendor.layouts.master')

@section('title', '商家帳號管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家帳號管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('account') }}">商家帳號管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($account) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $account->name ?? '' }} 帳號資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($account))
                                    <form class="myform" action="{{ route('vendor.account.update', $account->id) }}" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="_method" value="PATCH">
                                        <input type="hidden" name="from" value="{{ $from ?? '' }}">
                                    @else
                                    <form class="myform" action="{{ route('vendor.account.store') }}" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="vendor_id" value="{{ $vendorId ?? '' }}">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            @if(isset($account))
                                            <div class="col-12">
                                                <label><span class="text-red">* </span>所屬商家店名或品牌</label>
                                                @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                                <p><label><a href="{{ url('vendors/'.$account->vendor_id) }}">　{{ $account->vendor->name }}</a></label></p>
                                                @else
                                                <p><label>　{{ $account->vendor->name }}</label></p>
                                                @endif
                                            </div>
                                            @endif
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-6">
                                                        <label for="account"><span class="text-red">* </span>帳號</label>
                                                        <input type="text" class="form-control {{ $errors->has('account') ? ' is-invalid' : '' }}" id="account" name="account" value="{{ $account->account ?? old('account') }}" placeholder="輸入帳號">
                                                        @if ($errors->has('account'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('account') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="password"><span class="text-red">* </span>密碼</label>
                                                        <input type="text" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" value="{{ old('password') }}" placeholder="{{ isset($account) ? '留空白代表不修改密碼' : '輸入密碼' }}">
                                                        @if ($errors->has('password'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('password') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="name"><span class="text-red">* </span>姓名</label>
                                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $account->name ?? old('name') }}" placeholder="店名或品牌">
                                                        @if ($errors->has('name'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('name') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="email">電子信箱</label>
                                                        <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ $account->email ?? old('email') }}" placeholder="聯絡人電子信箱">
                                                        @if ($errors->has('email'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('email') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="is_on">狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停權" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->is_on == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            <button type="submit" class="btn btn-primary">{{ isset($account) ? '修改' : '新增' }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\VendorAccountsRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
    })(jQuery);
</script>
@endsection
