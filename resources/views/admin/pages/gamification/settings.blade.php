@extends('admin.layouts.master')

@section('page-title')
    إعدادات نظام التحفيز
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إعدادات نظام التحفيز</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.gamification.index') }}">نظام التحفيز</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الإعدادات</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.gamification.settings.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إعادة تعيين جميع الإعدادات؟')">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fe fe-refresh-cw"></i> إعادة تعيين
                    </button>
                </form>
            </div>
        </div>
        <!-- End Page Header -->

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.gamification.settings.save') }}" method="POST">
            @csrf
            
            <!-- قواعد النقاط -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">قواعد النقاط</div>
                </div>
                <div class="card-body">
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

            <!-- إعدادات الشارات -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات الشارات</div>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="badges[auto_award]" id="badges_auto_award" 
                               value="1" {{ ($settings->get('gamification_badges_auto_award')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="badges_auto_award">
                            منح الشارات تلقائياً عند استيفاء الشروط
                        </label>
                    </div>
                </div>
            </div>

            <!-- إعدادات الإنجازات -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات الإنجازات</div>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="achievements[auto_check]" id="achievements_auto_check" 
                               value="1" {{ ($settings->get('gamification_achievements_auto_check')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="achievements_auto_check">
                            فحص الإنجازات تلقائياً عند الأحداث
                        </label>
                    </div>
                </div>
            </div>

            <!-- إعدادات المستويات -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات المستويات</div>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="levels[auto_upgrade]" id="levels_auto_upgrade" 
                               value="1" {{ ($settings->get('gamification_levels_auto_upgrade')?->value ?? 'true') == 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="levels_auto_upgrade">
                            ترقية المستويات تلقائياً عند الوصول للنقاط المطلوبة
                        </label>
                    </div>
                </div>
            </div>

            <!-- إعدادات المهام -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات المهام</div>
                </div>
                <div class="card-body">
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

            <!-- إعدادات لوحة المتصدرين -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات لوحة المتصدرين</div>
                </div>
                <div class="card-body">
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

            <!-- إعدادات الإشعارات -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">إعدادات الإشعارات</div>
                </div>
                <div class="card-body">
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

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fe fe-save"></i> حفظ الإعدادات
                </button>
                <a href="{{ route('admin.gamification.index') }}" class="btn btn-secondary">
                    <i class="fe fe-x"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
<!-- End::app-content -->
@stop

