<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>iCarry {{ env('APP_ENV') == 'local' ? '測試用' : '' }}商家後台管理系統 - 商家管理 | 登入</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('vendor/Font-Awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.custom.css') }}">
</head>

@if(env('NOCAPTCHA_HIDE'))
<style>
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>
@endif

<body class="hold-transition login-page bg-navy" style="background-image: url({{ asset('img/icarry-index-cover-20191210.jpg') }});">
    {{-- alert訊息 --}}
    @include('vendor.layouts.alert_message')
    <div class="login-box">
        <div class="text-lg text-center mb-2">
            <a href="javascript:" class="text-white"><b>@if(env('APP_ENV') == 'local')開發團隊測試用<br>@endif iCarry 商家後台管理系統</b></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">請輸入 帳號 與 密碼</p>
                <form id="loginForm" action="{{ route('vendor.login.submit') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input id="account" type="account" placeholder="請輸入帳號" class="form-control {{ $errors->has('account') ? ' is-invalid' : '' }}" name="account" value="{{ old('account') }}" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @if ($errors->has('account'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('account') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="input-group mb-3">
                        <input id="password" type="password" placeholder="請輸入密碼" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                    {{-- Google reCAPTCHA v3 --}}
                    <div class="col-12 mb-3">
                        {!! no_captcha()->input() !!}
                        {!! no_captcha()->script() !!}
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <a href="{{ url('forget') }}"><span class="badge badge-danger text-sm">密碼重設/解除鎖定</span></a>
                        </div>
                        <div class="col-4">
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary btn-block btn-submit">登入</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- 背景動畫使用區塊 --}}
    <div id="particles-js"></div>
    {{-- REQUIRED SCRIPTS --}}
    <script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    {{-- VincentGarreau/particles.js --}}
    <script src="{{ asset('vendor/particles.js/particles.min.js') }}"></script>
    <script src="{{ asset('js/admin.common.js') }}"></script>
    {{-- 背景動畫 --}}
    <script>
        particlesJS.load('particles-js', "{{ asset('./js/particles.json') }}");
    </script>
    {{-- Google reCAPTCHA v3 --}}
    <script>
        $('.btn-submit').click(function(){
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ env('NOCAPTCHA_SITEKEY') }}', { action: 'submit' }).then(function(token) {
                    if (token) {
                        $('input[name=g-recaptcha-response]').val(token);
                        $('#loginForm').submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
