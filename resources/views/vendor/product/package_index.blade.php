@extends('vendor.layouts.master')

@section('title', '組合商品')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('vendor.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>組合商品</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('package') }}">組合商品</a></li>
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
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($products->total()) ?? 0 }}</span>
                                    <form action="" method="GET" class="form-inline" role="search">
                                        <span class="badge badge-primary text-sm mr-2">快搜 <i class="fas fa-hand-point-right"></i></span>
                                    </form>
                                    <form action="{{ url('package') }}" method="GET" class="form-inline" role="search">
                                        <div class="form-group-sm">
                                            <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋商品名稱或商品貨號" aria-label="Search">
                                            <button type="submit" class="btn btn-sm btn-info" title="搜尋商品名稱或商品貨號" >
                                                <i class="fas fa-search"></i>
                                                搜尋
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr style="border-top:2px #000000 solid;border-bottom:2px #000000 solid;">
                                        <th class="text-center" width="5%">狀態</th>
                                        <th class="text-left" width="40%">商品名稱</th>
                                        <th class="text-left" width="55%">
                                            <div class="row">
                                                <div class="col-4 row text-left">
                                                    <span>組合內容</span>
                                                </div>
                                                <div class="col-8 row text-left">
                                                    <div class="col-3">商品貨號</div>
                                                    <div class="col-7">商品名稱</div>
                                                    <div class="col-2 text-right">數量</div>
                                                </div>
                                            </div>
                                        </th>
                                        {{-- <th class="text-right" width="5%">單價</th> --}}
                                    </tr>
                                    <tr style="border-top:2px #000000 solid;border-bottom:2px #000000 solid;"></tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr style="border-top:2px #000000 solid;border-bottom:2px #000000 solid;">
                                        <td class="text-center align-middle text-sm">
                                            @if($product->status == 1)
                                            <span class="right badge badge-success">上架中</span>
                                            @elseif($product->status == 2)
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
                                                <br><span class="text-xs bg-info">{{ $product->serving_size }}</span>
                                            </div>
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @foreach($product->packages as $package)
                                            <div class="row">
                                                <div class="col-4 row text-left">
                                                    <div class="col-12">
                                                        {{ $package->name }}
                                                    </div>
                                                    <div class="col-12">
                                                        <span class="text-success"><b>{{ $package->sku }}</b></span><br>
                                                    </div>
                                                    <div class="col-12 row">
                                                        <div class="col-6">
                                                            庫存：
                                                            @if($package->quantity < $package->safe_quantity)
                                                            <span class="text-danger"><b>{{ number_format($package->quantity) }}</b></span>
                                                            @else
                                                            {{ number_format($package->quantity) }}
                                                            @endif
                                                        </div>
                                                        <div class="col-6">安全庫存：{{ $package->safe_quantity }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-8 row text-left">
                                                    @foreach($package->lists as $lists)
                                                        <div class="col-3">{{ $lists->sku }}</div>
                                                        <div class="col-7">{{ $lists->name }}</div>
                                                        <div class="col-2 text-right">{{ $lists->quantity }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if(count($product->packages) != $loop->iteration)
                                            <hr>
                                            @endif
                                            @endforeach
                                        </td>
                                        {{-- <td class="text-right align-middle"><span class="text-danger"><b>{{ number_format($product->price) }}</b></span></td> --}}
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
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });
    })(jQuery);
</script>
@endsection
