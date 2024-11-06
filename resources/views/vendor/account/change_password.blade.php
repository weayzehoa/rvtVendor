@extends('vendor.layouts.master')

@section('title', '管理員帳號管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>我的帳號資料</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('account') }}">商家帳號管理</a></li>
                        <li class="breadcrumb-item active">我的帳號資料</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form id="myform" action="{{ route('vendor.account.changePassWord') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">帳號資料</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="account"><span class="text-red">* </span>帳號</label>
                                                <input type="text" class="form-control {{ $errors->has('account') ? ' is-invalid' : '' }}" value="{{ Auth::user()->account }}" disabled>
                                                @if ($errors->has('account'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('account') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="name"><span class="text-red">* </span>姓名</label>
                                                <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ Auth::user()->name }}">
                                                @if ($errors->has('name'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="email"><span class="text-red">* </span>EMail</label>
                                                <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ Auth::user()->email }}">
                                                @if ($errors->has('email'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="oldpass"><span class="text-red">* </span>舊密碼</label>
                                                <input type="password" class="form-control {{ $errors->has('oldpass') ? ' is-invalid' : '' }}" id="oldpass" name="oldpass" value="" placeholder="請輸入舊密碼">
                                                @if ($errors->has('oldpass'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('oldpass') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="newpass"><span class="text-red">* </span>新密碼</label>
                                                <input type="password" class="form-control {{ $errors->has('newpass') ? ' is-invalid' : '' }}" id="newpass" name="newpass" value="" placeholder="請輸入新密碼">
                                                @if ($errors->has('newpass'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('newpass') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="newpass_confirmation"><span class="text-red">* </span>確認密碼</label>
                                                <input type="password" class="form-control {{ $errors->has('newpass_confirmation') ? ' is-invalid' : '' }}" id="newpass_confirmation" name="newpass_confirmation" value="" placeholder="請再次輸入新密碼">
                                                @if ($errors->has('newpass_confirmation'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('newpass_confirmation') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">修改</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </section>
</div>
@endsection

@section('css')
@endsection

@section('script')
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\ChangePassWordRequest', '#myform'); !!}
@endsection

@section('CustomScript')
@endsection
