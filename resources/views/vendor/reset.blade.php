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
            <a href="javascript:" class="text-white"><b>@if(env('APP_ENV') == 'local')開發團隊測試用<br>@endif 密碼重新設定</b></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">請輸入 驗證碼 與 新密碼</p>
                <form id="resetForm" action="{{ route('vendor.reset.submit') }}" method="post">
                    @csrf
                    <input type="hidden" name="account" value="{{ $account }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div class="form-group">
                        <label for="newpass">新密碼</label>
                        <input id="newpass" type="password" placeholder="請輸入新密碼" class="bg-white form-control {{ $errors->has('newpass') ? ' is-invalid' : '' }}" name="newpass" required autocomplete="off" onfocus="this.removeAttribute('readonly');" readonly>
                        @if ($errors->has('newpass'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('newpass') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="newpass_confirmation">新密碼確認</label>
                        <input id="newpass_confirmation" type="password" placeholder="請再次輸入新密碼" class="bg-white form-control {{ $errors->has('newpass_confirmation') ? ' is-invalid' : '' }}" name="newpass_confirmation" required autocomplete="off">
                        @if ($errors->has('newpass_confirmation'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('newpass_confirmation') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="newpass_confirmation">驗證碼</label>
                        <input id="otp" type="text" placeholder="請輸入驗證碼" class="bg-white form-control {{ $errors->has('otp') ? ' is-invalid' : '' }}" name="otp" required autocomplete="off">
                        @if ($errors->has('otp'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('otp') }}</strong>
                        </span>
                        @endif
                    </div>
                    {{-- Google reCAPTCHA v3 --}}
                    <div class="col-12 mb-3">
                        {!! no_captcha()->input() !!}
                        {!! no_captcha()->script() !!}
                    </div>
                    <div class="row">
                        <div class="col-8">
                            @if ($errors->has('otp'))
                            @if(!strstr($errors->first('otp'),'驗證碼錯誤'))
                            <a href="{{ url('forget') }}"><span class="badge badge-danger text-sm">* 忘記密碼重設</span></a>
                            @endif
                            @else
                            <span class="text-sm text-primary">驗證碼有效時間10分鐘<br>至 {{ $otpTime }}</span>
                            @endif
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary btn-block btn-submit">送出</button>
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
                        $('#resetForm').submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
