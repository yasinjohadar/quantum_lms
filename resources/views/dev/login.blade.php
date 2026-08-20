@extends('auth.layouts.glass')

@section('title', 'بوابة الدخول السريع (تطوير) - أكاديمية كوانتم التعليمية')
@section('brand-subtitle', 'بيئة التطوير فقط')

@section('content')
    <div class="auth-heading">
        <div class="auth-badge">بيئة التطوير — {{ app()->environment() }}</div>
        <h1>بوابة الدخول السريع</h1>
        <p>اختر حساباً للدخول الفوري بدون إدخال بيانات. هذه الصفحة معطّلة تماماً في بيئة الإنتاج.</p>
    </div>

    @if (session('dev_status'))
        <div class="auth-alert auth-alert-success">{{ session('dev_status') }}</div>
    @endif

    @if (session('dev_error'))
        <div class="auth-alert auth-alert-danger">{{ session('dev_error') }}</div>
    @endif

    <div class="dev-list">
        @foreach ($accounts as $account)
            <div class="dev-card">
                <div class="dev-card-info">
                    <div class="dev-card-title">
                        {{ $account['label'] }}
                        <span class="dev-chip">{{ $account['role'] }}</span>
                        @unless ($account['exists'])
                            <span class="dev-chip dev-chip--warn">غير موجود</span>
                        @endunless
                    </div>
                    <div class="dev-card-meta">{{ $account['email'] }} · {{ $account['password'] }}</div>
                    @if ($account['description'])
                        <div class="dev-card-desc">{{ $account['description'] }}</div>
                    @endif
                </div>
                <a class="dev-btn" href="{{ route('dev.login.as', $account['key']) }}">دخول فوري</a>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('dev.seed') }}" style="margin-top:18px;">
        @csrf
        <button type="submit" class="auth-btn">إنشاء / تحديث الحسابات التجريبية (Seed)</button>
    </form>

    <div class="dev-note">
        من الطرفية: <code>php artisan db:seed --class=DevAccountsSeeder</code>
        <br>
        للتعطيل: أضف <code>DEV_QUICK_LOGIN=false</code> في ملف <code>.env</code>
    </div>

    <div class="auth-meta">
        <a class="auth-link" href="{{ route('login') }}">دخول المشرفين والمعلمين</a>
        ·
        <a class="auth-link" href="{{ route('student.login') }}">دخول الطلاب</a>
    </div>
@endsection

@push('styles')
<style>
    .dev-list { display: grid; gap: 12px; margin-top: 8px; }
    .dev-card {
        display: flex; align-items: center; justify-content: space-between; gap: 14px;
        padding: 14px 16px; border-radius: 16px;
        background: var(--input-bg); border: 1px solid var(--input-border);
    }
    .dev-card-title { font-weight: 700; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .dev-card-meta { color: var(--text-muted); font-size: 13px; margin-top: 4px; direction: ltr; text-align: right; }
    .dev-card-desc { color: var(--text-muted); font-size: 12px; margin-top: 4px; }
    .dev-chip {
        font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px;
        background: rgba(99, 102, 241, 0.22); border: 1px solid rgba(99, 102, 241, 0.4);
        color: var(--text-main); direction: ltr;
    }
    .dev-chip--warn {
        background: var(--danger-bg); border-color: rgba(239, 68, 68, 0.4); color: var(--danger);
    }
    .dev-btn {
        flex-shrink: 0; text-decoration: none; font-weight: 700; font-size: 14px;
        padding: 10px 18px; border-radius: 12px; color: #0b1020;
        background: linear-gradient(135deg, var(--gold-1), var(--gold-3));
    }
    .dev-btn:hover { filter: brightness(1.07); }
    .dev-note { margin-top: 14px; font-size: 12px; color: var(--text-muted); line-height: 2; }
    .dev-note code {
        background: rgba(148, 163, 184, 0.16); padding: 2px 6px; border-radius: 6px; direction: ltr; display: inline-block;
    }
</style>
@endpush
