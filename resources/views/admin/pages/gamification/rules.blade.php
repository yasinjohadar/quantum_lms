@extends('admin.layouts.master')

@section('page-title')
    قواعد النقاط والأحداث — نظام التحفيز
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">قواعد النقاط والأحداث</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.gamification.index') }}">نظام التحفيز</a></li>
                        <li class="breadcrumb-item active" aria-current="page">القواعد</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.gamification.settings') }}" class="btn btn-primary btn-sm">
                    <i class="fe fe-settings"></i> تعديل القيم في الإعدادات
                </a>
            </div>
        </div>

        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">كيف يعمل النظام</div>
            </div>
            <div class="card-body">
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

        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">أنواع الأحداث والقيم الافتراضية للنقاط</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
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
                            <tr>
                                <td><code>library_item_viewed</code></td>
                                <td>مشاهدة عنصر في المكتبة الرقمية</td>
                                <td>2</td>
                            </tr>
                            <tr>
                                <td><code>library_item_downloaded</code></td>
                                <td>تحميل من المكتبة</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td><code>library_item_rated</code></td>
                                <td>تقييم عنصر في المكتبة</td>
                                <td>3</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">ملاحظات</div>
            </div>
            <div class="card-body">
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
