@extends('auth.layouts.glass')

@section('title', 'استعادة كلمة المرور - منصة كوانتم التعليمية')
@section('brand-subtitle', 'استعادة الحساب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">مساعدة تسجيل الدخول</div>
        <h1>نسيت كلمة المرور؟</h1>
        <p>أدخل بريدك الإلكتروني وسنرسل لك رابطًا آمنًا لإعادة تعيين كلمة المرور.</p>
    </div>

    @if (session('status'))
        <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">تعذر إرسال الرابط. تحقق من البريد الإلكتروني وحاول مرة أخرى.</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="auth-field">
            <label class="auth-label" for="email">البريد الإلكتروني</label>
            <div class="auth-control">
                <input id="email" class="auth-input @error('email') invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus placeholder="name@example.com">
                <span class="auth-icon">✉</span>
            </div>
            @error('email') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="auth-btn">إرسال رابط إعادة التعيين</button>

        <div class="auth-meta">
            <a href="{{ route('login') }}" class="auth-link">العودة إلى تسجيل الدخول</a>
        </div>
    </form>
@endsection
