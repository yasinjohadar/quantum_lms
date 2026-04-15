@extends('auth.layouts.glass')

@section('title', 'تأكيد كلمة المرور - Quantum LMS')
@section('brand-subtitle', 'منطقة آمنة')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">تأكيد الهوية</div>
        <h1>تأكيد كلمة المرور</h1>
        <p>هذه منطقة حساسة. أدخل كلمة مرورك للمتابعة.</p>
    </div>

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="auth-field">
            <label class="auth-label" for="password">كلمة المرور</label>
            <div class="auth-control auth-control--toggle-start">
                <input id="password" class="auth-input @error('password') invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="أدخل كلمة المرور الحالية">
                <button type="button" class="password-toggle" data-target="password" aria-label="إظهار أو إخفاء كلمة المرور">👁</button>
            </div>
            @error('password') <div class="auth-error">{{ $message }}</div> @enderror
        </div>

        <button class="auth-btn" type="submit">تأكيد</button>
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
