@extends('admin.layouts.master')

@section('page-title')
    قواعد النقاط والأحداث — نظام التحفيز
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'قواعد النقاط والأحداث',
            'gamiSubtitle' => 'مرجع أنواع الأحداث والقيم الافتراضية للنقاط',
            'gamiIcon' => 'bi-journal-code',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'القواعد', 'active' => true],
            ],
            'gamiHeroActions' => '<a href="' . route('admin.gamification.settings') . '" class="btn btn-sm btn-primary"><i class="bi bi-gear me-1"></i> تعديل القيم في الإعدادات</a>',
        ])

        @include('partials.gamification-help-box', ['helpKey' => 'admin.rules', 'showQueueStatus' => true])

        <div class="gami-card mb-4">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-info-circle"></i></span>
                    <span>كيف يعمل النظام</span>
                </div>
            </div>
            <div class="gami-card__body">
                <p class="mb-2">
                    تُحسب النقاط من جدول <code>system_settings</code> (مفاتيح تبدأ بـ <code>gamification_points_</code>)، مع قيم افتراضية إذا لم تُضبط.
                    عند حدوث فعل (حضور، إكمال درس، اختبار، إجابة سؤال، نشاط مكتبة، إلخ) يستدعي التطبيق
                    <strong>GamificationService</strong> لمنح النقاط وفحص المهام والشارات والإنجازات والمستوى.
                </p>
                <p class="mb-0 text-muted small">
                    للتفاصيل التقنية الكاملة راجع <code>docs/GAMIFICATION_SYSTEM.md</code> في المشروع.
                </p>
            </div>
        </div>

        <div class="gami-card gami-card--flush mb-4">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-list-ul"></i></span>
                    <span>أنواع الأحداث والقيم الافتراضية للنقاط</span>
                </div>
            </div>
            <div class="gami-card__body">
                <div class="gami-table-wrap">
                    <table class="table gami-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>نوع الحدث (في الكود)</th>
                                <th>الوصف</th>
                                <th>نقاط افتراضية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>lesson_attended</code></td>
                                <td>حضور درس</td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td><code>lesson_completed</code></td>
                                <td>إكمال درس</td>
                                <td>15</td>
                            </tr>
                            <tr>
                                <td><code>quiz_completed</code></td>
                                <td>إكمال اختبار (مع إمكانية مكافأة 100% من الإعدادات)</td>
                                <td>25 (+ مكافأة اختيارية)</td>
                            </tr>
                            <tr>
                                <td><code>question_answered</code></td>
                                <td>إجابة سؤال (مع نقاط إضافية للإجابة الصحيحة في المنطق البرمجي)</td>
                                <td>5 (+2 عادة للصحيح)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="gami-card">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-sticky"></i></span>
                    <span>ملاحظات</span>
                </div>
            </div>
            <div class="gami-card__body">
                <ul class="mb-0">
                    <li>سجل النقاط: جدول <code>point_transactions</code> (نوع الحدث، المبلغ، بيانات إضافية).</li>
                    <li>الشارات والإنجازات والمستويات تُحدَّث بعد منح النقاط ضمن نفس مسار المعالجة.</li>
                    <li>استبدال المكافآت يخصم نقاطاً عبر معاملة نوعها <code>reward</code> (قيمة سالبة للتكلفة).</li>
                </ul>
            </div>
        </div>

    </div>
</div>
@stop
