@extends('auth.layouts.glass')

@section('title', 'إعادة تعيين كلمة المرور - منصة كوانتم التعليمية')
@section('brand-subtitle', 'أمان الحساب')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">إعادة تعيين آمنة</div>
        <h1>تعيين كلمة مرور جديدة</h1>
        <p>أدخل بياناتك التالية لإكمال استعادة الحساب.</p>
    </div>

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label class="auth-label" for="email">البريد الإلكتروني</label>
            <div class="auth-control">
                <input id="email" class="auth-input @error('email') invalid @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="name@example.com">
                <span class="auth-icon">✉</span>
            </div>
            @error('email') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">كلمة المرور الجديدة</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password" class="auth-input @error('password') invalid @enderror" type="password" name="password" required autocomplete="new-password" placeholder="كلمة مرور جديدة">
                <button type="button" class="password-toggle" data-target="password" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">تأكيد كلمة المرور</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password_confirmation" class="auth-input @error('password_confirmation') invalid @enderror" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="أعد كتابة كلمة المرور">
                <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <button class="auth-btn" type="submit">حفظ كلمة المرور</button>
    </form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(btn.getAttribute('data-target'));
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            btn.textContent = isPassword ? '🙈' : '👁';
        });
    });
</script>
@endpush
