@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الاختبار
@stop

@push('styles')
    @include('admin.pages.quizzes.partials.show-styles')
@endpush

@section('content')
    @php
        $user = auth()->user();
        $submitsForReview = $user->shouldSubmitQuizForReview();
        $canReview = $user->canReviewContent();
        $reviewBadgeClass = match ($quiz->review_status) {
            \App\Models\Quiz::REVIEW_STATUS_PENDING => 'ls-review-badge--warning',
            \App\Models\Quiz::REVIEW_STATUS_APPROVED => 'ls-review-badge--success',
            \App\Models\Quiz::REVIEW_STATUS_REJECTED => 'ls-review-badge--danger',
            default => 'ls-review-badge--secondary',
        };
    @endphp

    <div class="main-content app-content quiz-show-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif
            @if($quiz->needsRelink())
                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-link-45deg me-2"></i>
                    هذا الاختبار <strong>بانتظار الربط</strong> بمكان في المنهج ولن يظهر للطلاب حتى تكمل الربط من صفحة التعديل.
                    @can('quiz-edit')
                        <a href="{{ route('admin.quizzes.edit', ['quiz' => $quiz->id, 'relink' => 1]) }}" class="alert-link">إكمال الربط الآن</a>
                    @endcan
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="qs-hero my-4">
                @if($quiz->image)
                    <div class="qs-hero__media">
                        <img src="{{ media_public_url($quiz->image) }}" alt="{{ $quiz->title }}">
                    </div>
                @else
                    <div class="qs-hero__icon">
                        <i class="bi bi-clipboard2-check-fill"></i>
                    </div>
                @endif

                <div class="qs-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($quiz->title, 40) }}</li>
                        </ol>
                    </nav>
                    <h1 class="qs-hero__title">{{ $quiz->title }}</h1>
                    <div class="qs-hero__chips">
                        @if($quiz->needsRelink())
                            <span class="qs-chip qs-chip--warning"><i class="bi bi-link-45deg"></i> بانتظار الربط</span>
                        @endif
                        @if($quiz->is_published)
                            <span class="qs-chip qs-chip--success"><i class="bi bi-check-circle"></i> منشور</span>
                        @else
                            <span class="qs-chip qs-chip--muted"><i class="bi bi-file-earmark"></i> مسودة</span>
                        @endif
                        @if($quiz->is_active)
                            <span class="qs-chip qs-chip--primary"><i class="bi bi-lightning-charge"></i> نشط</span>
                        @else
                            <span class="qs-chip qs-chip--warning"><i class="bi bi-pause-circle"></i> معطل</span>
                        @endif
                        <span class="qs-chip qs-chip--{{ $quiz->review_status_color === 'success' ? 'success' : ($quiz->review_status_color === 'warning' ? 'warning' : 'muted') }}">
                            <i class="bi bi-clipboard-check"></i> {{ $quiz->review_status_name }}
                        </span>
                        <span class="qs-chip qs-chip--success">
                            <i class="bi bi-calendar-check"></i> {{ $quiz->availability_status_name }}
                        </span>
                    </div>
                    <div class="qs-hero__stats">
                        <div class="qs-hero-stat">
                            <span class="qs-hero-stat__value">{{ $quiz->questions_count }}</span>
                            <span class="qs-hero-stat__label">أسئلة</span>
                        </div>
                        <div class="qs-hero-stat">
                            <span class="qs-hero-stat__value" id="total-points">{{ $quiz->total_points }}</span>
                            <span class="qs-hero-stat__label">درجة</span>
                        </div>
                        <div class="qs-hero-stat">
                            <span class="qs-hero-stat__value">{{ $quiz->pass_percentage }}%</span>
                            <span class="qs-hero-stat__label">نسبة النجاح</span>
                        </div>
                        <div class="qs-hero-stat">
                            <span class="qs-hero-stat__value">{{ $quiz->max_attempts > 0 ? $quiz->max_attempts : '∞' }}</span>
                            <span class="qs-hero-stat__label">محاولات</span>
                        </div>
                    </div>
                </div>

                <div class="qs-hero__actions">
                    @can('quiz-questions')
                        <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-list-check me-1"></i> إدارة الأسئلة
                        </a>
                    @endcan
                    @can('quiz-edit')
                        <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> تعديل
                        </a>
                    @endcan
                    @can('quiz-preview')
                        <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i> معاينة
                        </a>
                    @endcan
                </div>
            </div>

            @if($canReview && $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                <div class="qs-review-bar">
                    <div class="qs-review-bar__text">
                        <i class="bi bi-hourglass-split me-1"></i>
                        هذا الاختبار بانتظار مراجعتك قبل النشر للطلاب.
                    </div>
                    @canany(['quiz-approve-review', 'quiz-reject-review'])
                        <div class="d-flex flex-wrap gap-2">
                            @can('quiz-approve-review')
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="bi bi-check-circle me-1"></i> الموافقة
                                </button>
                            @endcan
                            @can('quiz-reject-review')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-1"></i> رفض
                                </button>
                            @endcan
                        </div>
                    @endcanany
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">

                    {{-- معلومات الاختبار --}}
                    <div class="qs-card">
                        <div class="qs-card__header">
                            <h2 class="qs-card__header-title">
                                <span class="qs-card__header-icon"><i class="bi bi-info-circle"></i></span>
                                معلومات الاختبار
                            </h2>
                        </div>
                        <div class="qs-card__body">
                            @if($quiz->description)
                                <div class="qs-desc">
                                    <div class="qs-desc__title">الوصف</div>
                                    <p class="mb-0 text-muted">{{ $quiz->description }}</p>
                                </div>
                            @endif

                            @if($quiz->instructions)
                                <div class="alert alert-info border-0 rounded-3 mb-3">
                                    <h6 class="mb-2"><i class="bi bi-info-circle me-1"></i> تعليمات الاختبار</h6>
                                    <p class="mb-0">{{ $quiz->instructions }}</p>
                                </div>
                            @endif

                            <div class="qs-meta-grid">
                                <div class="qs-meta-item">
                                    <span class="qs-meta-item__icon"><i class="bi bi-book"></i></span>
                                    <div>
                                        <div class="qs-meta-item__label">المادة</div>
                                        <div class="qs-meta-item__value">{{ $quiz->subject->name ?? '—' }}</div>
                                    </div>
                                </div>
                                @if($quiz->section)
                                    <div class="qs-meta-item">
                                        <span class="qs-meta-item__icon"><i class="bi bi-folder2"></i></span>
                                        <div>
                                            <div class="qs-meta-item__label">القسم</div>
                                            <div class="qs-meta-item__value">{{ $quiz->section->title }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($quiz->unit)
                                    <div class="qs-meta-item">
                                        <span class="qs-meta-item__icon"><i class="bi bi-collection"></i></span>
                                        <div>
                                            <div class="qs-meta-item__label">الوحدة</div>
                                            <div class="qs-meta-item__value">{{ $quiz->unit->title }}</div>
                                        </div>
                                    </div>
                                @endif
                                @if($quiz->lesson)
                                    <div class="qs-meta-item">
                                        <span class="qs-meta-item__icon"><i class="bi bi-mortarboard"></i></span>
                                        <div>
                                            <div class="qs-meta-item__label">الدرس</div>
                                            <div class="qs-meta-item__value">{{ $quiz->lesson->title }}</div>
                                        </div>
                                    </div>
                                @endif
                                <div class="qs-meta-item">
                                    <span class="qs-meta-item__icon"><i class="bi bi-clock"></i></span>
                                    <div>
                                        <div class="qs-meta-item__label">المدة</div>
                                        <div class="qs-meta-item__value">{{ $quiz->formatted_duration }}</div>
                                    </div>
                                </div>
                                <div class="qs-meta-item">
                                    <span class="qs-meta-item__icon"><i class="bi bi-award"></i></span>
                                    <div>
                                        <div class="qs-meta-item__label">طريقة التقييم</div>
                                        <div class="qs-meta-item__value">{{ $quiz->grading_method_name }}</div>
                                    </div>
                                </div>
                            </div>

                            @if($quiz->available_from || $quiz->available_to)
                                <div class="qs-desc mt-3">
                                    <div class="qs-desc__title"><i class="bi bi-calendar3 me-1"></i> الجدول الزمني</div>
                                    <div class="row g-2">
                                        @if($quiz->available_from)
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block">يبدأ من</small>
                                                <span class="fw-semibold">{{ $quiz->available_from->format('Y/m/d h:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($quiz->available_to)
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block">ينتهي في</small>
                                                <span class="fw-semibold">{{ $quiz->available_to->format('Y/m/d h:i A') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- حالة المراجعة --}}
                    <div class="qs-card">
                        <div class="qs-card__header">
                            <h2 class="qs-card__header-title">
                                <span class="qs-card__header-icon"><i class="bi bi-clipboard-check"></i></span>
                                حالة المراجعة
                            </h2>
                        </div>
                        <div class="qs-card__body text-center">
                            <span class="ls-review-badge {{ $reviewBadgeClass }}">
                                <i class="bi bi-shield-check"></i> {{ $quiz->review_status_name }}
                            </span>

                            @if($quiz->review_notes)
                                <div class="alert alert-{{ $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_REJECTED ? 'danger' : 'info' }} text-start mt-3 mb-0">
                                    <strong class="d-block mb-1">ملاحظات المراجعة</strong>
                                    {{ $quiz->review_notes }}
                                </div>
                            @endif

                            @if($quiz->reviewed_at || $quiz->submitted_for_review_at)
                                <div class="mt-3 text-start small text-muted">
                                    @if($quiz->reviewed_at)
                                        <div class="mb-1">
                                            <i class="bi bi-person-check me-1"></i>
                                            مراجع من: <strong>{{ $quiz->reviewer->name ?? 'غير معروف' }}</strong>
                                            — {{ \Carbon\Carbon::parse($quiz->reviewed_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                    @if($quiz->submitted_for_review_at)
                                        <div>
                                            <i class="bi bi-send me-1"></i>
                                            أُرسل للمراجعة: {{ \Carbon\Carbon::parse($quiz->submitted_for_review_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($submitsForReview && in_array($quiz->review_status, [\App\Models\Quiz::REVIEW_STATUS_DRAFT, \App\Models\Quiz::REVIEW_STATUS_REJECTED]))
                                @can('quiz-submit-for-review')
                                    <form action="{{ route('admin.quizzes.submit-for-review', $quiz->id) }}" method="POST" class="mt-3">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"
                                                onclick="return confirm('هل أنت متأكد من إرسال الاختبار للمراجعة؟')">
                                            <i class="bi bi-send me-1"></i> إرسال للمراجعة
                                        </button>
                                    </form>
                                @endcan
                            @endif

                            @if($canReview && $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                                @canany(['quiz-approve-review', 'quiz-reject-review'])
                                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                        @can('quiz-approve-review')
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                                                <i class="bi bi-check-circle me-1"></i> الموافقة على النشر
                                            </button>
                                        @endcan
                                        @can('quiz-reject-review')
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                <i class="bi bi-x-circle me-1"></i> رفض النشر
                                            </button>
                                        @endcan
                                    </div>
                                @endcanany
                            @endif
                        </div>
                    </div>

                    {{-- الأسئلة --}}
                    <div class="qs-card">
                        <div class="qs-card__header">
                            <h2 class="qs-card__header-title">
                                <span class="qs-card__header-icon"><i class="bi bi-list-check"></i></span>
                                أسئلة الاختبار
                            </h2>
                            @can('quiz-questions')
                                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-gear me-1"></i> إدارة الأسئلة
                                </a>
                            @endcan
                        </div>
                        <div class="qs-card__body qs-card__body--flush">
                            @if($quiz->questions->isEmpty())
                                <div class="qs-empty">
                                    <i class="bi bi-question-circle"></i>
                                    <p class="mb-2">لم يتم إضافة أسئلة بعد</p>
                                    @can('quiz-questions')
                                        <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> إضافة أسئلة
                                        </a>
                                    @endcan
                                </div>
                            @else
                                @foreach($quiz->questions as $index => $question)
                                    <div class="qs-question-item">
                                        <div class="d-flex align-items-start gap-2 flex-grow-1">
                                            <span class="qs-question-item__index">{{ $index + 1 }}</span>
                                            <div class="flex-grow-1 min-w-0">
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }} mb-1">
                                                    <i class="bi {{ $question->type_icon }} me-1"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <p class="mb-0 small">{{ Str::limit(strip_tags($question->title), 120) }}</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <span class="badge bg-primary question-points-badge"
                                                  data-quiz-id="{{ $quiz->id }}"
                                                  data-question-id="{{ $question->id }}"
                                                  id="question-points-{{ $quiz->id }}-{{ $question->id }}">
                                                {{ $question->pivot->points }} درجة
                                            </span>
                                            @can('question-edit')
                                                <a href="{{ route('admin.questions.edit', $question->id) }}?quiz_id={{ $quiz->id }}"
                                                   class="btn btn-sm btn-outline-primary" title="تعديل السؤال">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- آخر المحاولات --}}
                    <div class="qs-card">
                        <div class="qs-card__header">
                            <h2 class="qs-card__header-title">
                                <span class="qs-card__header-icon"><i class="bi bi-people"></i></span>
                                آخر المحاولات
                            </h2>
                            @can('quiz-results')
                                <a href="{{ route('admin.quizzes.results', $quiz->id) }}" class="btn btn-sm btn-outline-primary">
                                    عرض الكل
                                </a>
                            @endcan
                        </div>
                        <div class="qs-card__body qs-card__body--flush">
                            @if($quiz->attempts->isEmpty())
                                <div class="qs-empty py-4">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0">لا توجد محاولات بعد</p>
                                </div>
                            @else
                                <div class="qs-table-wrap">
                                    <div class="table-responsive">
                                        <table class="table qs-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>الطالب</th>
                                                    <th>الدرجة</th>
                                                    <th>الحالة</th>
                                                    <th>التاريخ</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($quiz->attempts as $attempt)
                                                    <tr>
                                                        <td>{{ $attempt->user->name ?? 'محذوف' }}</td>
                                                        <td>
                                                            <span class="fw-semibold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                                {{ $attempt->percentage }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $attempt->status_color }}">
                                                                {{ $attempt->status_name }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $attempt->started_at->format('Y/m/d H:i') }}</td>
                                                        <td>
                                                            @can('quiz-attempt-show')
                                                                <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}"
                                                                   class="btn btn-sm btn-info-transparent">
                                                                    عرض
                                                                </a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="qs-sidebar-sticky">

                        {{-- إحصائيات --}}
                        <div class="qs-card">
                            <div class="qs-card__header">
                                <h2 class="qs-card__header-title">
                                    <span class="qs-card__header-icon"><i class="bi bi-bar-chart-line"></i></span>
                                    إحصائيات
                                </h2>
                            </div>
                            <div class="qs-card__body">
                                <div class="qs-stat-grid">
                                    <div class="qs-stat-tile qs-stat-tile--primary">
                                        <div class="qs-stat-tile__value">{{ $stats['total_attempts'] }}</div>
                                        <div class="qs-stat-tile__label">إجمالي المحاولات</div>
                                    </div>
                                    <div class="qs-stat-tile qs-stat-tile--success">
                                        <div class="qs-stat-tile__value">{{ $stats['passed_count'] }}</div>
                                        <div class="qs-stat-tile__label">ناجحون</div>
                                    </div>
                                    <div class="qs-stat-tile qs-stat-tile--danger">
                                        <div class="qs-stat-tile__value">{{ $stats['failed_count'] }}</div>
                                        <div class="qs-stat-tile__label">راسبون</div>
                                    </div>
                                    <div class="qs-stat-tile qs-stat-tile--info">
                                        <div class="qs-stat-tile__value">{{ round($stats['average_score']) }}%</div>
                                        <div class="qs-stat-tile__label">متوسط الدرجات</div>
                                    </div>
                                </div>
                                @if($stats['total_attempts'] > 0)
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">أعلى درجة</span>
                                            <span class="text-success fw-semibold">{{ $stats['highest_score'] }}%</span>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-muted">أدنى درجة</span>
                                            <span class="text-danger fw-semibold">{{ $stats['lowest_score'] }}%</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- الإعدادات --}}
                        <div class="qs-card">
                            <div class="qs-card__header">
                                <h2 class="qs-card__header-title">
                                    <span class="qs-card__header-icon"><i class="bi bi-sliders"></i></span>
                                    الإعدادات
                                </h2>
                            </div>
                            <div class="qs-card__body">
                                <ul class="qs-setting-list">
                                    <li>
                                        <i class="bi bi-{{ $quiz->shuffle_questions ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        خلط الأسئلة
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->shuffle_options ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        خلط الخيارات
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->show_timer ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        إظهار المؤقت
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->show_result_immediately ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        إظهار النتيجة فوراً
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->show_correct_answers ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        إظهار الإجابات الصحيحة
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->requires_password ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        يتطلب كلمة مرور
                                    </li>
                                    <li>
                                        <i class="bi bi-{{ $quiz->prevent_copy_paste ? 'check-circle-fill text-success' : 'x-circle text-muted' }}"></i>
                                        منع النسخ واللصق
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- إجراءات --}}
                        <div class="qs-card">
                            <div class="qs-card__header">
                                <h2 class="qs-card__header-title">
                                    <span class="qs-card__header-icon"><i class="bi bi-lightning"></i></span>
                                    إجراءات سريعة
                                </h2>
                            </div>
                            <div class="qs-card__body">
                                <div class="qs-action-stack">
                                    @can('quiz-preview')
                                        <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> معاينة الاختبار
                                        </a>
                                    @endcan
                                    @can('quiz-results')
                                        <a href="{{ route('admin.quizzes.results', $quiz->id) }}" class="btn btn-outline-info">
                                            <i class="bi bi-bar-chart me-1"></i> النتائج والتقارير
                                        </a>
                                    @endcan
                                    @can('quiz-duplicate')
                                        <form action="{{ route('admin.quizzes.duplicate', $quiz->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning w-100">
                                                <i class="bi bi-copy me-1"></i> نسخ الاختبار
                                            </button>
                                        </form>
                                    @endcan
                                    @can('quiz-delete')
                                        <button type="button" class="btn btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="bi bi-trash me-1"></i> حذف الاختبار
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Modal الموافقة --}}
            @if($canReview && $quiz->review_status === \App\Models\Quiz::REVIEW_STATUS_PENDING)
                @can('quiz-approve-review')
                    <div class="modal fade" id="approveModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.quizzes.approve-review', $quiz->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">الموافقة على نشر الاختبار</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات (اختياري)</label>
                                            <textarea name="review_notes" class="form-control" rows="3"
                                                      placeholder="أضف ملاحظات للمعلم..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success">الموافقة</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('quiz-reject-review')
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.quizzes.reject-review', $quiz->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">رفض نشر الاختبار</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات <span class="text-danger">*</span></label>
                                            <textarea name="review_notes" class="form-control" rows="3"
                                                      placeholder="أضف ملاحظات للمعلم..." required></textarea>
                                            <small class="text-muted">يجب إضافة ملاحظات توضح سبب الرفض</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger">رفض</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
            @endif

            @can('quiz-delete')
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4">
                            <div class="border-0 text-center pt-4 px-4">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                                    <span class="me-2 fs-4 text-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </span>
                                    <h5 class="modal-title mb-0 fw-bold">حذف الاختبار</h5>
                                </div>
                                <button type="button" class="btn-close position-absolute top-0 start-0 m-3"
                                        data-bs-dismiss="modal"></button>
                            </div>
                            <div class="text-center mt-2">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm"
                                     style="width:80px;height:80px;">
                                    <i class="bi bi-trash fs-2"></i>
                                </div>
                            </div>
                            <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-body text-center pt-0 pb-3 px-4">
                                    <p class="mb-1 text-muted">هل أنت متأكد من حذف الاختبار:</p>
                                    <p class="fw-bold mb-1">{{ $quiz->title }}</p>
                                    @if($quiz->attempts_count > 0)
                                        <p class="text-danger small mb-0 mt-2">
                                            سيتم حذف {{ $quiz->attempts_count }} محاولة طالب مرتبطة بهذا الاختبار. لا يمكن التراجع.
                                        </p>
                                    @endif
                                </div>
                                <div class="modal-footer border-0 justify-content-center pb-4">
                                    <button type="button" class="btn btn-outline-secondary px-4 me-2"
                                            data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="bi bi-trash me-1"></i> حذف
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const channel = new BroadcastChannel('quiz-question-updates');

    channel.addEventListener('message', function(event) {
        const { type, quizId, questionId, points, totalPoints } = event.data;

        if (type === 'points-updated' && quizId == {{ $quiz->id }}) {
            const badge = document.getElementById(`question-points-${quizId}-${questionId}`);
            if (badge) {
                badge.textContent = `${parseFloat(points).toFixed(2)} درجة`;
                badge.style.transition = 'all 0.3s ease';
                badge.style.transform = 'scale(1.1)';
                badge.style.backgroundColor = '#0d6efd';
                setTimeout(() => { badge.style.transform = 'scale(1)'; }, 300);
            }

            if (totalPoints !== undefined) {
                const totalEl = document.getElementById('total-points');
                if (totalEl) {
                    totalEl.textContent = parseFloat(totalPoints).toFixed(2);
                }
            }
        }
    });

    window.addEventListener('storage', function(e) {
        if (e.key === 'quiz-question-updated') {
            try {
                const data = JSON.parse(e.newValue);
                if (data.quizId == {{ $quiz->id }}) {
                    const badge = document.getElementById(`question-points-${data.quizId}-${data.questionId}`);
                    if (badge) {
                        badge.textContent = `${parseFloat(data.points).toFixed(2)} درجة`;
                        badge.style.transition = 'all 0.3s ease';
                        badge.style.transform = 'scale(1.1)';
                        setTimeout(() => { badge.style.transform = 'scale(1)'; }, 300);
                    }
                }
            } catch (err) {
                console.error('Error parsing storage event:', err);
            }
        }
    });
});
</script>
@stop
