@extends('vendor.layouts.master')

@section('title', '行銷策展')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('vendor.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>行銷策展</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">商家後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('curation') }}">行銷策展</a></li>
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
                                <div class="col-5">
                                    <a href="{{ route('vendor.curation.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a>
                                </div>
                                <div class="col-7">
                                    <div class=" float-right">
                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                            <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($curations->total()) ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($curations) > 0)
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle" width="5%">順序</th>
                                        <th class="text-left align-middle" width="20%">主標題</th>
                                        <th class="text-left align-middle" width="30%">副標題</th>
                                        <th class="text-center align-middle" width="10%">開始時間</th>
                                        <th class="text-center align-middle" width="10%">結束時間</th>
                                        <th class="text-center align-middle" width="10%">排序</th>
                                        <th class="text-center align-middle" width="10%">啟用</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($curations as $curation)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $curation->sort }}
                                        </td>
                                        <td class="text-left align-middle">
                                            <a href="{{ route('vendor.curation.show',$curation->id) }}">{{ $curation->main_title }}</a>
                                        </td>
                                        <td class="text-left align-middle">{{ $curation->sub_title }}</td>
                                        <td class="text-center align-middle">{{ $curation->start_time }}</td>
                                        <td class="text-center align-middle">{{ $curation->end_time }}</td>
                                        <td class="text-center align-middle">
                                            @if($loop->iteration != 1)
                                            <a href="{{ url('curation/sortup/' . $curation->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($loop->iteration != count($curations))
                                            <a href="{{ url('curation/sortdown/' . $curation->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <form action="{{ url('curation/active/' . $curation->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $curation->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <h3>無資料</h3>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($curations->total()) ?? 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $curations->appends($appends)->render() }}
                                @else
                                {{ $curations->render() }}
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
