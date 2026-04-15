@extends('auth.layouts.glass')

@section('title', 'التحقق من البريد الإلكتروني - Quantum LMS')
@section('brand-subtitle', 'تفعيل الحساب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">خطوة أخيرة</div>
        <h1>تحقق من بريدك الإلكتروني</h1>
        <p>أرسلنا رابط التفعيل إلى بريدك. افتح الرسالة واضغط على الرابط لإكمال التسجيل.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="auth-alert auth-alert-success">تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="auth-btn" type="submit">إعادة إرسال رابط التحقق</button>
    </form>

    <div class="auth-meta" style="margin-top:14px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-link" style="background:none;border:none;cursor:pointer;">تسجيل الخروج</button>
        </form>
    </div>
@endsection
