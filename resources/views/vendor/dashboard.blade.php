@extends('vendor.layouts.master')

@section('title', 'Dashboard')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h1 class="m-0 text-dark"><img height="40" src="{{ asset('img/icarry-logo-text.png') }}"> <b>資訊看板</b></h1> --}}
                    <h1 class="m-0 text-dark"><b>資訊看板</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('dashboard') }}">資訊看板</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card bg-light-yellow">
                    <div class="card-header">
                        <h3 class="card-title text-danger text-bold">【iCarry 公告】 因應公司政策調整，自2020年12月起，貨款結帳日更改為每月25日。造成不便，敬請見諒。</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <p>新版商家後台上架須知說明！</p>
                    </div>
                    <div class="icon">
                        <i class="far fa-file-pdf"></i>
                    </div>
                    <a href="{{ asset('sample/商家後台上架須知說明.pdf') }}" class="small-box-footer" target="_blank">
                        查看說明 (PDF) <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <a href="{{ asset('sample/商家後台上架須知說明.zip') }}" class="small-box-footer" target="_blank">
                        下載檔案 (ZIP) <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <p>採購對帳出貨流程說明！</p>
                    </div>
                    <div class="icon">
                        <i class="far fa-file-pdf"></i>
                    </div>
                    <a href="{{ asset('sample/採購對帳出貨流程說明.pdf') }}" class="small-box-footer" target="_blank">
                        查看說明 (PDF) <i class="fas fa-arrow-circle-right"></i>
                    </a>
                    <a href="{{ asset('sample/採購對帳出貨流程說明.zip') }}" class="small-box-footer" target="_blank">
                        下載檔案 (ZIP) <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            {{-- <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($data->productFail) }}</h3>
                        <p>審核不通過商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('product?status=-2') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($data->productNeedReplenishment) }}</h3>
                        <p>需補貨商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('product?low_quantity=yes&status=1') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div> --}}
        </div>
    </section>
</div>
@endsection
