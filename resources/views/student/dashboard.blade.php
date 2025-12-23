

@extends('student.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">مرحباً {{ $user->name }} 👋</h4>
                    <p class="mb-0 text-muted">هذه نظرة سريعة على تقدمك وواجباتك واختباراتك القادمة.</p>
                </div>
                <div>
                    <a href="{{ route('student.progress.index') }}" class="btn btn-primary btn-sm">
                        عرض تقدمي التفصيلي
                    </a>
                </div>
            </div>

            <!-- Cards: Progress & Gamification -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card bg-primary-gradient text-fixed-white">
                        <div class="card-body">
                            <h6 class="fs-12 mb-2">متوسط التقدم في جميع موادي</h6>
                            <h3 class="mb-1">{{ $overallAverage }}%</h3>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-fixed-white" role="progressbar"
                                     style="width: {{ $overallAverage }}%;" aria-valuenow="{{ $overallAverage }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card bg-success-gradient text-fixed-white">
                        <div class="card-body">
                            <h6 class="fs-12 mb-2">إجمالي النقاط</h6>
                            <h3 class="mb-1">{{ $totalPoints }}</h3>
                            <p class="mb-0 fs-12 opacity-8">نقاطك من الدروس والاختبارات والإنجازات</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card bg-info-gradient text-fixed-white">
                        <div class="card-body">
                            <h6 class="fs-12 mb-2">مستواي الحالي</h6>
                            <h5 class="mb-1">
                                @if($currentLevel)
                                    المستوى {{ $currentLevel->level_number }} - {{ $currentLevel->name }}
                                @else
                                    لم يتم تحديد مستوى بعد
                                @endif
                            </h5>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-fixed-white" role="progressbar"
                                     style="width: {{ $levelProgress['progress_percentage'] ?? 0 }}%;"
                                     aria-valuenow="{{ $levelProgress['progress_percentage'] ?? 0 }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p class="mb-0 fs-11 opacity-8 mt-1">
                                تقدم نحو المستوى التالي: {{ round($levelProgress['progress_percentage'] ?? 0, 1) }}%
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card bg-warning-gradient text-fixed-white">
                        <div class="card-body">
                            <h6 class="fs-12 mb-2">الإنجازات والشارات</h6>
                            <h3 class="mb-1">{{ $badgesCount }} شارة</h3>
                            <p class="mb-0 fs-12 opacity-8">
                                {{ $achievementsCount }} إنجاز مكتمل
                            </p>
                            <a href="{{ route('student.gamification.dashboard') }}" class="btn btn-sm btn-light mt-2">
                                عرض ملف التحفيز
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress by Subjects & Upcoming -->
            <div class="row">
                <!-- Subjects Progress -->
                <div class="col-xl-7 col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">تقدمي في موادي</h4>
                            <a href="{{ route('student.progress.index') }}" class="fs-12 text-primary">
                                عرض كل المواد
                            </a>
                        </div>
                        <div class="card-body">
                            @php
                                $subjectsCollection = collect($topSubjects ?? []);
                            @endphp
                            @if($subjectsCollection->isEmpty())
                                <p class="text-muted mb-0">لم يتم تسجيل أي مادة بعد أو لم يبدأ التقدم.</p>
                            @else
                                @foreach($subjectsCollection as $item)
                                    @php
                                        $subject = $item['subject'] ?? null;
                                        $p = $item['progress'] ?? [];
                                    @endphp
                                    @if($subject)
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <h6 class="mb-0">{{ $subject->name }}</h6>
                                                    <small class="text-muted">
                                                        {{ $subject->schoolClass->name ?? '' }}
                                                        @if(optional($subject->schoolClass)->stage)
                                                            - {{ $subject->schoolClass->stage->name }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary">
                                                        {{ $p['overall_percentage'] ?? 0 }}%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="progress progress-xs mb-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $p['overall_percentage'] ?? 0 }}%;"
                                                     aria-valuenow="{{ $p['overall_percentage'] ?? 0 }}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between fs-11 text-muted">
                                                <span>الدروس: {{ $p['lessons_completed'] ?? 0 }}/{{ $p['lessons_total'] ?? 0 }}</span>
                                                <span>الاختبارات: {{ $p['quizzes_completed'] ?? 0 }}/{{ $p['quizzes_total'] ?? 0 }}</span>
                                                <span>الأسئلة: {{ $p['questions_completed'] ?? 0 }}/{{ $p['questions_total'] ?? 0 }}</span>
                                            </div>
                                            <hr class="my-2">
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Assignments & Events -->
                <div class="col-xl-5 col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">الواجبات القادمة</h4>
                            <a href="{{ route('student.assignments.index') }}" class="fs-12 text-primary">
                                كل الواجبات
                            </a>
                        </div>
                        <div class="card-body">
                            @if(($upcomingAssignments ?? collect())->isEmpty())
                                <p class="text-muted mb-0">لا توجد واجبات قادمة حالياً.</p>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($upcomingAssignments as $assignment)
                                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                            <div class="me-2">
                                                <a href="{{ route('student.assignments.show', $assignment->id) }}" class="fw-semibold">
                                                    {{ $assignment->title }}
                                                </a>
                                                <div class="fs-11 text-muted">
                                                    التسليم: {{ optional($assignment->due_date)->format('Y-m-d H:i') }}
                                                </div>
                                            </div>
                                            <span class="badge bg-outline-danger">
                                                واجب
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">الاختبارات والواجبات (الأيام القادمة)</h4>
                            <a href="{{ route('student.calendar.index') }}" class="fs-12 text-primary">
                                عرض التقويم
                            </a>
                        </div>
                        <div class="card-body">
                            @php
                                $eventsCollection = collect($upcomingEvents ?? []);
                            @endphp
                            @if($eventsCollection->isEmpty())
                                <p class="text-muted mb-0">لا توجد أحداث مجدولة في الأيام القادمة.</p>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($eventsCollection as $event)
                                        @php
                                            $type = $event['type'] ?? $event['event_type'] ?? null;
                                            $start = \Carbon\Carbon::parse($event['start'] ?? now());
                                        @endphp
                                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                            <div class="me-2">
                                                <div class="fw-semibold">{{ $event['title'] ?? '' }}</div>
                                                <div class="fs-11 text-muted">
                                                    {{ $start->format('Y-m-d H:i') }}
                                                </div>
                                            </div>
                                            @if($type === 'quiz')
                                                <span class="badge bg-warning">اختبار</span>
                                            @elseif($type === 'assignment')
                                                <span class="badge bg-danger">واجب</span>
                                            @else
                                                <span class="badge bg-secondary">حدث</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mt-3">
                <div class="col-md-3 col-sm-6 mb-2">
                    <a href="{{ route('student.subjects') }}" class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-24 mb-2"><i class="bi bi-play-circle"></i></div>
                            <h6 class="mb-1">الدروس والمواد</h6>
                            <p class="text-muted fs-12 mb-0">الانتقال لموادك ودروسك</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <a href="{{ route('student.assignments.index') }}" class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-24 mb-2"><i class="bi bi-journal-check"></i></div>
                            <h6 class="mb-1">الواجبات</h6>
                            <p class="text-muted fs-12 mb-0">إدارة واجباتك وإرسال الحلول</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <a href="{{ route('student.library.index') }}" class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-24 mb-2"><i class="bi bi-book"></i></div>
                            <h6 class="mb-1">المكتبة الرقمية</h6>
                            <p class="text-muted fs-12 mb-0">الوصول للكتب والملفات التعليمية</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <a href="{{ route('student.gamification.dashboard') }}" class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-24 mb-2"><i class="bi bi-trophy"></i></div>
                            <h6 class="mb-1">التحفيز والإنجازات</h6>
                            <p class="text-muted fs-12 mb-0">عرض نقاطك ومستوياتك وإنجازاتك</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
