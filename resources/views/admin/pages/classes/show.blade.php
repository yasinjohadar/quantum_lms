@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الصف الدراسي
@stop

@push('styles')
    @include('admin.pages.classes.partials.show-styles')
@endpush

@section('content')
    @php
        $subjects = $class->subjects->sortBy('order')->values();
        $activeSubjectsCount = $subjects->where('is_active', true)->count();
        $classImage = $class->image ? media_public_url($class->image) : null;
    @endphp

    <div class="main-content app-content class-show-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="class-show-hero my-4">
                @if ($classImage)
                    <img src="{{ $classImage }}" alt="{{ $class->name }}" class="class-show-hero__cover">
                @else
                    <div class="class-show-hero__cover d-flex align-items-center justify-content-center"
                         style="background: var(--cs-soft); border-style: dashed;">
                        <i class="bi bi-mortarboard-fill fs-2" style="color: var(--cs-accent); opacity: 0.6;"></i>
                    </div>
                @endif

                <div class="class-show-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">الصفوف الدراسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $class->name }}</li>
                        </ol>
                    </nav>

                    <h4 class="class-show-hero__title">تفاصيل الصف: {{ $class->name }}</h4>

                    <div class="class-show-hero__meta">
                        @if ($class->stage)
                            <span class="class-show-hero__meta-item">
                                <i class="bi bi-layers"></i>{{ $class->stage->name }}
                            </span>
                        @endif
                        @if ($class->is_active)
                            <span class="class-show-badge class-show-badge--active">
                                <i class="bi bi-check-circle-fill"></i> صف نشط
                            </span>
                        @else
                            <span class="class-show-badge class-show-badge--inactive">
                                <i class="bi bi-x-circle-fill"></i> غير نشط
                            </span>
                        @endif
                        @if ($class->is_free)
                            <span class="class-show-badge class-show-badge--free">
                                <i class="bi bi-gift"></i> مجاني
                            </span>
                        @elseif ($class->show_price && $class->price > 0)
                            <span class="class-show-badge class-show-badge--free">
                                <i class="bi bi-tag"></i> {{ number_format($class->price, 2) }}
                            </span>
                        @endif
                        @if ($class->subscription_ends_at)
                            <span class="class-show-badge {{ $class->hasSubscriptionEnded() ? 'class-show-badge--inactive' : 'class-show-badge--free' }}">
                                <i class="bi bi-calendar-x"></i>
                                نهاية الاشتراك: {{ $class->subscription_ends_at->format('Y-m-d') }}
                                @if ($class->hasSubscriptionEnded())
                                    (منتهي)
                                @endif
                            </span>
                        @endif
                    </div>

                    <div class="class-show-stats">
                        <div class="class-show-stat">
                            <span class="class-show-stat__value">{{ $subjects->count() }}</span>
                            <span class="class-show-stat__label">مادة</span>
                        </div>
                        <div class="class-show-stat">
                            <span class="class-show-stat__value">{{ $activeSubjectsCount }}</span>
                            <span class="class-show-stat__label">نشطة</span>
                        </div>
                        <div class="class-show-stat">
                            <span class="class-show-stat__value">{{ $class->order }}</span>
                            <span class="class-show-stat__label">ترتيب العرض</span>
                        </div>
                    </div>
                </div>

                <div class="class-show-hero__actions">
                    @can('class-edit')
                        <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-warning btn-sm text-white">
                            <i class="bi bi-pencil me-1"></i> تعديل
                        </a>
                    @endcan
                    @can('class-enrolled-students')
                        <a href="{{ route('admin.classes.enrolled-students', $class->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people me-1"></i> الطلاب المنضمون
                        </a>
                    @endcan
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للقائمة
                    </a>
                </div>
            </div>

            <div class="class-show-section">
                <div class="class-show-section__header">
                    <div class="class-show-section__title-wrap">
                        <span class="class-show-section__icon"><i class="bi bi-journal-bookmark"></i></span>
                        <div>
                            <h6 class="class-show-section__title">المواد المرتبطة بهذا الصف</h6>
                            <span class="class-show-section__count">{{ $subjects->count() }} مادة مرتبطة</span>
                        </div>
                    </div>
                    @can('subject-create')
                        <a href="{{ route('admin.subjects.create') }}?class_id={{ $class->id }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> إضافة مادة جديدة
                        </a>
                    @endcan
                </div>

                <div class="class-show-section__body">
                    @if ($subjects->count())
                        <div class="class-subject-grid">
                            @foreach ($subjects as $subject)
                                @php $subjectImage = $subject->image ? media_public_url($subject->image) : null; @endphp
                                <article class="class-subject-card">
                                    <div class="class-subject-card__media">
                                        @if ($subjectImage)
                                            <img src="{{ $subjectImage }}" alt="{{ $subject->name }}" loading="lazy">
                                        @else
                                            <div class="class-subject-card__placeholder">
                                                <i class="bi bi-book"></i>
                                                <span>لا توجد صورة</span>
                                            </div>
                                        @endif
                                        <span class="class-subject-card__order">ترتيب {{ $subject->order ?? 0 }}</span>
                                    </div>

                                    <div class="class-subject-card__body">
                                        <h6 class="class-subject-card__name">{{ $subject->name }}</h6>
                                    </div>

                                    <div class="class-subject-card__free">
                                        <span class="class-subject-card__free-label">مجانية دائماً</span>
                                        @can('subject-edit')
                                            <div class="form-check form-switch mb-0">
                                                <input type="checkbox"
                                                       class="form-check-input cs-free-override-toggle"
                                                       role="switch"
                                                       data-url="{{ route('admin.subjects.toggle-free-override', $subject->id) }}"
                                                       id="classFreeOverride{{ $subject->id }}"
                                                       {{ $subject->is_free_override ? 'checked' : '' }}>
                                                <label class="form-check-label class-subject-card__free-state"
                                                       for="classFreeOverride{{ $subject->id }}">
                                                    {{ $subject->is_free_override ? 'مجانية' : 'مدفوعة' }}
                                                </label>
                                            </div>
                                        @else
                                            <span class="class-subject-card__status {{ $subject->is_free_override ? 'class-subject-card__status--active' : 'class-subject-card__status--inactive' }}">
                                                {{ $subject->is_free_override ? 'مجانية' : 'مدفوعة' }}
                                            </span>
                                        @endcan
                                    </div>

                                    <div class="class-subject-card__footer">
                                        @if ($subject->is_active)
                                            <span class="class-subject-card__status class-subject-card__status--active">نشطة</span>
                                        @else
                                            <span class="class-subject-card__status class-subject-card__status--inactive">غير نشطة</span>
                                        @endif
                                        <a href="{{ route('admin.subjects.show', $subject->id) }}?return_to_class_id={{ $class->id }}"
                                           class="class-subject-card__link">
                                            عرض المادة <i class="bi bi-arrow-left-short"></i>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="class-show-empty">
                            <div class="class-show-empty__icon">
                                <i class="bi bi-journal-x"></i>
                            </div>
                            <h6 class="class-show-empty__title">لا توجد مواد مرتبطة بعد</h6>
                            <p class="class-show-empty__text">ابدأ بإضافة أول مادة دراسية لهذا الصف لعرضها هنا.</p>
                            @can('subject-create')
                                <a href="{{ route('admin.subjects.create') }}?class_id={{ $class->id }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> إضافة مادة جديدة
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        (function () {
            const grid = document.querySelector('.class-subject-grid');
            if (!grid) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            grid.addEventListener('change', function (e) {
                const toggle = e.target.closest('.cs-free-override-toggle');
                if (!toggle) return;

                const label = toggle.closest('.form-check')?.querySelector('.form-check-label');
                const previousChecked = !toggle.checked;
                toggle.disabled = true;

                fetch(toggle.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.success) throw new Error(data.message || 'فشل التحديث');
                        toggle.checked = data.is_free_override;
                        if (label) label.textContent = data.is_free_override ? 'مجانية' : 'مدفوعة';
                    })
                    .catch(function (err) {
                        toggle.checked = previousChecked;
                        alert(err.message || 'فشل تحديث حالة المادة');
                    })
                    .finally(function () {
                        toggle.disabled = false;
                    });
            });
        })();
    </script>
@endpush
