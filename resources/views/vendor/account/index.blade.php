@extends('vendor.layouts.master')

@section('title', '商家帳號列表')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('vendor.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家帳號管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('account') }}">商家帳號管理</a></li>
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
                                <a href="{{ route('vendor.account.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a>
                            </div>
                            <div class="float-right">
                                {{-- <form action="{{ url('account') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                            <option value="2" {{ $is_on == 2 ? 'selected' : '' }}>所有狀態 ({{ $totalAccounts }})</option>
                                            <option value="1" {{ $is_on == 1 ? 'selected' : '' }}>啟用 ({{ $totalEnable }})</option>
                                            <option value="0" {{ $is_on == 0 ? 'selected' : '' }}>停用 ({{ $totalDisable }})</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋帳號、姓名、電子郵件" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋帳號、姓名、電子郵件" >
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form> --}}
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">帳號</th>
                                        <th class="text-left" width="10%">姓名</th>
                                        <th class="text-left" width="20%">電子郵件</th>
                                        <th class="text-left" width="20%">所屬商家店名/品牌</th>
                                        <th class="text-center" width="5%">啟用</th>
                                        <th class="text-center" width="5%">刪除</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $account)
                                    <tr>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('vendor.account.show', $account->id ) }}">{{ $account->account }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $account->name }}</td>
                                        <td class="text-left align-middle text-sm">{{ $account->email }}</td>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('vendor.profile.index') }}">{{ $account->vendor->name }}</a>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(Auth::user()->id != $account->id)
                                            <form action="{{ url('account/active/' . $account->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $account->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(Auth::user()->id != $account->id)
                                            <form action="{{ route('vendor.account.destroy', $account->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
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

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);
</script>
@endsection
