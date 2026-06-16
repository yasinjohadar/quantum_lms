@extends('admin.layouts.master')

@section('page-title')
    إعدادات نظام التحفيز
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إعدادات نظام التحفيز',
            'gamiSubtitle' => 'ضبط قواعد النقاط والشارات والمهام والإشعارات',
            'gamiIcon' => 'bi-gear',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'الإعدادات', 'active' => true],
            ],
            'gamiHeroActions' => '<form action="' . route('admin.gamification.settings.reset') . '" method="POST" class="d-inline" onsubmit="return confirm(\'هل أنت متأكد من إعادة تعيين جميع الإعدادات؟\')">' . csrf_field() . '<button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i> إعادة تعيين</button></form>',
        ])

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.gamification.settings.save') }}" method="POST">
            @csrf

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-coin"></i></span>
                        <span>قواعد النقاط</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط الحضور</label>
                            <input type="number" name="points[attendance]" class="form-control"
                                   value="{{ $settings->get('gamification_points_attendance')?->value ?? 10 }}" min="0">
                            <small class="text-muted">النقاط الممنوحة عند حضور درس</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط إكمال الدرس</label>
                            <input type="number" name="points[lesson_completed]" class="form-control"
                                   value="{{ $settings->get('gamification_points_lesson_completed')?->value ?? 15 }}" min="0">
                            <small class="text-muted">النقاط الممنوحة عند إكمال درس</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط إكمال الاختبار</label>
                            <input type="number" name="points[quiz_completed]" class="form-control"
                                   value="{{ $settings->get('gamification_points_quiz_completed')?->value ?? 25 }}" min="0">
                            <small class="text-muted">النقاط الممنوحة عند إكمال اختبار</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط الإجابة على السؤال</label>
                            <input type="number" name="points[question_answered]" class="form-control"
                                   value="{{ $settings->get('gamification_points_question_answered')?->value ?? 5 }}" min="0">
                            <small class="text-muted">النقاط الممنوحة عند الإجابة على سؤال</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط الاختبار المثالي (100%)</label>
                            <input type="number" name="points[quiz_perfect_score]" class="form-control"
                                   value="{{ $settings->get('gamification_points_quiz_perfect_score')?->value ?? 50 }}" min="0">
                            <small class="text-muted">نقاط إضافية للحصول على 100%</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نقاط إكمال الكورس</label>
                            <input type="number" name="points[course_completed]" class="form-control"
                                   value="{{ $settings->get('gamification_points_course_completed')?->value ?? 100 }}" min="0">
                            <small class="text-muted">النقاط الممنوحة عند إكمال كورس كامل</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-award"></i></span>
                        <span>إعدادات الشارات</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="badges[auto_award]" id="badges_auto_award"
                               value="1" {{ ($settings->get('gamification_badges_auto_award')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="badges_auto_award">
                            منح الشارات تلقائياً عند استيفاء الشروط
                        </label>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-star"></i></span>
                        <span>إعدادات الإنجازات</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="achievements[auto_check]" id="achievements_auto_check"
                               value="1" {{ ($settings->get('gamification_achievements_auto_check')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="achievements_auto_check">
                            فحص الإنجازات تلقائياً عند الأحداث
                        </label>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-bar-chart-steps"></i></span>
                        <span>إعدادات المستويات</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="levels[auto_upgrade]" id="levels_auto_upgrade"
                               value="1" {{ ($settings->get('gamification_levels_auto_upgrade')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="levels_auto_upgrade">
                            ترقية المستويات تلقائياً عند الوصول للنقاط المطلوبة
                        </label>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-calendar-check"></i></span>
                        <span>إعدادات المهام</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">وقت إعادة تعيين المهام اليومية</label>
                            <input type="time" name="tasks[daily_reset_time]" class="form-control"
                                   value="{{ $settings->get('gamification_tasks_daily_reset_time')?->value ?? '00:00' }}">
                            <small class="text-muted">الوقت اليومي لإعادة تعيين المهام (HH:MM)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">يوم إعادة تعيين المهام الأسبوعية</label>
                            <select name="tasks[weekly_reset_day]" class="form-select">
                                <option value="1" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '1' ? 'selected' : '' }}>الاثنين</option>
                                <option value="2" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '2' ? 'selected' : '' }}>الثلاثاء</option>
                                <option value="3" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '3' ? 'selected' : '' }}>الأربعاء</option>
                                <option value="4" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '4' ? 'selected' : '' }}>الخميس</option>
                                <option value="5" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '5' ? 'selected' : '' }}>الجمعة</option>
                                <option value="6" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '6' ? 'selected' : '' }}>السبت</option>
                                <option value="7" {{ ($settings->get('gamification_tasks_weekly_reset_day')?->value ?? '1') == '7' ? 'selected' : '' }}>الأحد</option>
                            </select>
                            <small class="text-muted">اليوم الأسبوعي لإعادة تعيين المهام</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-graph-up-arrow"></i></span>
                        <span>إعدادات لوحة المتصدرين</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="leaderboard[auto_refresh]" id="leaderboard_auto_refresh"
                                       value="1" {{ ($settings->get('gamification_leaderboard_auto_refresh')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="leaderboard_auto_refresh">
                                    تحديث لوحة المتصدرين تلقائياً
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">فترة التحديث (بالدقائق)</label>
                            <input type="number" name="leaderboard[refresh_interval]" class="form-control"
                                   value="{{ $settings->get('gamification_leaderboard_refresh_interval')?->value ?? 60 }}" min="1">
                            <small class="text-muted">الفترة بين كل تحديث تلقائي</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gami-card gami-form-card mb-4">
                <div class="gami-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gami-card__header-icon"><i class="bi bi-bell"></i></span>
                        <span>إعدادات الإشعارات</span>
                    </div>
                </div>
                <div class="gami-card__body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notifications[enabled]" id="notifications_enabled"
                               value="1" {{ ($settings->get('gamification_notifications_enabled')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifications_enabled">
                            تفعيل إشعارات نظام التحفيز
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notifications[email]" id="notifications_email"
                               value="1" {{ ($settings->get('gamification_notifications_email')?->value ?? 'false') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifications_email">
                            إرسال إشعارات التحفيز عبر البريد الإلكتروني
                        </label>
                    </div>
                </div>
            </div>

            <div class="gami-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> حفظ الإعدادات
                </button>
                <a href="{{ route('admin.gamification.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i> إلغاء
                </a>
            </div>
        </form>

    </div>
</div>
@stop
