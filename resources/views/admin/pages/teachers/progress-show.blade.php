@extends('admin.layouts.master')

@section('page-title')
    تفاصيل تقدم المعلم: {{ $teacher->name }}
@stop

@push('styles')
    @include('admin.pages.teachers.partials.progress-styles')
@endpush

@section('content')
    <div class="main-content app-content teachers-progress-page">
        <div class="container-fluid">

            <div class="tp-hero my-4">
                <div class="tp-hero__icon">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="tp-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.progress.index') }}">تقدم المعلمين</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $teacher->name }}</li>
                        </ol>
                    </nav>
                    <h4 class="tp-hero__title">تفاصيل تقدم المعلم</h4>
                    <p class="tp-hero__subtitle">{{ $teacher->name }} — أهداف الصفحات والدروس الأسبوعية</p>
                </div>
                <div class="tp-hero__actions">
                    <a href="{{ route('admin.teachers.progress.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> القائمة
                    </a>
                    <a href="{{ route('admin.teachers.progress.history', $teacher) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> إحصائيات سابقة
                    </a>
                    <a href="{{ route('admin.teachers.progress.material-pages', $teacher) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i> صفحات المواد
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-sliders me-1"></i> تخصيص
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @include('admin.pages.teachers.partials.progress-week-filter', [
                'activeWeeks' => $activeWeeks ?? collect(),
                'currentWeek' => $currentWeek ?? null,
                'displayWeekId' => $displayWeekId ?? null,
                'filterMode' => 'redirect',
                'redirectBase' => route('admin.teachers.progress.show', $teacher),
            ])

            {{-- بطاقات الملخص --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="tp-metric tp-metric--info">
                        <div class="tp-metric__head">
                            <div>
                                <div class="tp-metric__title">الدروس المعتمدة</div>
                                <div class="tp-metric__hint">إنجاز فعلي في المواد المخصصة</div>
                            </div>
                            <span class="tp-metric__icon"><i class="bi bi-check2-circle"></i></span>
                        </div>
                        <div class="tp-metric__value">{{ $total_approved_lessons }}</div>
                        <span class="small text-muted">درس معتمد</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tp-metric tp-metric--primary">
                        <div class="tp-metric__head">
                            <div>
                                <div class="tp-metric__title">الصفحات الموكّلة</div>
                                <div class="tp-metric__hint">الإنجاز مقابل الهدف الموكّل</div>
                            </div>
                            <span class="tp-metric__icon"><i class="bi bi-journal-text"></i></span>
                        </div>
                        @if($total_pages_required > 0)
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="tp-metric__value" id="teacherProgressPagesSummaryCompleted">{{ $total_pages_completed }}</span>
                                <span class="text-muted small">من</span>
                                <span class="fw-bold" id="teacherProgressPagesSummaryRequired">{{ $total_pages_required }}</span>
                            </div>
                            <div class="tp-progress">
                                <div id="teacherProgressPagesSummaryBar" class="tp-progress__bar tp-progress__bar--success" style="width: {{ min(100, $total_pages_percentage) }}%;"></div>
                            </div>
                            <div class="mt-2">
                                <span id="teacherProgressPagesSummaryBadge" class="tp-pct tp-pct--info">{{ number_format($total_pages_percentage, 1) }}%</span>
                            </div>
                        @else
                            <p class="mb-0 text-muted small">لا يوجد هدف صفحات موكّل.</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tp-metric tp-metric--warning">
                        <div class="tp-metric__head">
                            <div>
                                <div class="tp-metric__title">هدف الدروس الأسبوعي</div>
                                <div class="tp-metric__hint">الأسبوع المعروض حالياً</div>
                            </div>
                            <span class="tp-metric__icon"><i class="bi bi-calendar-week"></i></span>
                        </div>
                        @if(($weekly_progress['target'] ?? 0) > 0)
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="tp-metric__value">{{ $weekly_progress['completed'] }}</span>
                                <span class="text-muted small">من {{ $weekly_progress['target'] }}</span>
                            </div>
                            <div class="tp-progress">
                                <div class="tp-progress__bar tp-progress__bar--warning" style="width: {{ min(100, $weekly_progress['percentage'] ?? 0) }}%;"></div>
                            </div>
                            @if($weekly_progress['percentage'] !== null)
                                @php $wp = $weekly_progress['percentage']; $wpc = $wp >= 100 ? 'success' : ($wp >= 50 ? 'info' : 'warning'); @endphp
                                <div class="mt-2"><span class="tp-pct tp-pct--{{ $wpc }}">{{ number_format($wp, 1) }}%</span></div>
                            @endif
                        @else
                            <p class="mb-0 text-muted small">لا يوجد هدف دروس لهذا الأسبوع.</p>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tp-metric tp-metric--dark">
                        <div class="tp-metric__head">
                            <div>
                                <div class="tp-metric__title">تراكمي السنة</div>
                                <div class="tp-metric__hint">مجموع أهداف الأسابيع النشطة</div>
                            </div>
                            <span class="tp-metric__icon"><i class="bi bi-bar-chart-line"></i></span>
                        </div>
                        @if(isset($yearWeeksLessons) && ($yearWeeksLessons['year_total_target'] ?? 0) > 0)
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="tp-metric__value" style="color:#334155;">{{ $yearWeeksLessons['year_total_completed'] }}</span>
                                <span class="text-muted small">من {{ $yearWeeksLessons['year_total_target'] }}</span>
                            </div>
                            <div class="tp-progress">
                                <div class="tp-progress__bar tp-progress__bar--dark" style="width: {{ min(100, $yearWeeksLessons['year_percentage'] ?? 0) }}%;"></div>
                            </div>
                            @if($yearWeeksLessons['year_percentage'] !== null)
                                <div class="mt-2"><span class="tp-pct tp-pct--muted">{{ number_format($yearWeeksLessons['year_percentage'], 1) }}%</span></div>
                            @endif
                        @else
                            <p class="mb-0 text-muted small">لا توجد أهداف دروس مسجّلة بعد.</p>
                        @endif
                    </div>
                </div>
            </div>

            @include('admin.pages.teachers.partials.progress-assignments', [
                'teacher' => $teacher,
                'assignedClasses' => $assignedClasses,
                'assignedSubjects' => $assignedSubjects,
                'unassignedClasses' => $unassignedClasses,
                'unassignedSubjects' => $unassignedSubjects,
                'pagesProgressBySubjectId' => collect($pages_progress ?? [])->mapWithKeys(function ($row) {
                    return [$row['subject']->id => [
                        'completed_pages' => $row['completed_pages'],
                        'required_pages' => $row['required_pages'],
                    ]];
                })->all(),
            ])

            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                <div class="tp-card mb-4">
                    <div class="tp-card__header">
                        <div>
                            <span class="tp-card__header-icon"><i class="bi bi-calendar-week"></i></span>
                            أهداف الدروس لكل أسابيع السنة
                        </div>
                        <span class="small text-muted fw-normal">المنفّذ = دروس معتمدة ضمن فترة الأسبوع</span>
                    </div>
                    <div class="tp-card__body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.teachers.week-targets.bulk.store', $teacher) }}" method="POST">
                            @csrf
                            <div class="teacher-weeks-table-wrap">
                                <table class="table teacher-weeks-targets-table table-sm align-middle mb-3">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">الأسبوع</th>
                                            <th>الفترة</th>
                                            <th style="width: 140px;">هدف الدروس</th>
                                            <th style="width: 120px;">المنفّذ</th>
                                            <th style="width: 90px;">النسبة</th>
                                            <th style="min-width: 140px;">التقدم</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeWeeks as $w)
                                            @php
                                                $isPastWeek = $w->end_date->copy()->endOfDay()->lt(now()->startOfDay());
                                                $isCurrentWeek = $w->start_date->copy()->startOfDay()->lte(now()->startOfDay())
                                                    && $w->end_date->copy()->endOfDay()->gte(now()->startOfDay());
                                                $wl = $yearWeeksLessons['per_week'][$w->id] ?? ['target' => 0, 'completed' => 0, 'percentage' => null];
                                                $pct = $wl['percentage'];
                                                $barW = $pct !== null ? min(100, $pct) : 0;
                                                $pctClass = $pct === null ? 'muted' : ($pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning'));
                                                $pastUnmetTarget = $isPastWeek
                                                    && (int) $wl['target'] > 0
                                                    && (int) $wl['completed'] < (int) $wl['target'];
                                            @endphp
                                            <tr @class([
                                                'bg-success bg-opacity-10' => $isCurrentWeek,
                                                'bg-danger bg-opacity-10' => ! $isCurrentWeek && $pastUnmetTarget,
                                            ])>
                                                <td class="fw-semibold">
                                                    {{ $w->week_number }}
                                                    @if($w->title)
                                                        <div class="small text-muted">{{ $w->title }}</div>
                                                    @endif
                                                    @if($isPastWeek)
                                                        <span class="tp-chip tp-chip--class mt-1">مقفل</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }}
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        required
                                                        class="form-control form-control-sm {{ $isPastWeek ? 'bg-light' : '' }}"
                                                        name="required_lessons_targets[{{ $w->id }}]"
                                                        value="{{ old('required_lessons_targets.' . $w->id, $weekTargets[$w->id] ?? (int) ($w->required_lessons_target ?? 0)) }}"
                                                        style="max-width: 120px;"
                                                        @if($isPastWeek) disabled @endif
                                                    >
                                                    @if(isset($weekTargets[$w->id]))
                                                        <div class="small text-success mt-1">override محفوظ</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="small lh-sm">
                                                        <span class="fw-semibold text-success">{{ $wl['completed'] }}</span>
                                                        <span class="text-muted">/ {{ $wl['target'] }} درس</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($pct !== null)
                                                        <span class="tp-pct tp-pct--{{ $pctClass }}">{{ number_format($pct, 1) }}%</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="tp-progress">
                                                        <div class="tp-progress__bar tp-progress__bar--{{ $pctClass === 'success' ? 'success' : ($pctClass === 'info' ? 'info' : 'warning') }}" style="width: {{ $barW }}%;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span><span class="d-inline-block rounded px-2 py-0 me-1 bg-success bg-opacity-10 border border-success border-opacity-25">&nbsp;</span> الأسبوع الحالي</span>
                                    <span><span class="d-inline-block rounded px-2 py-0 me-1 bg-danger bg-opacity-10 border border-danger border-opacity-25">&nbsp;</span> أسبوع منتهٍ دون تحقيق الهدف</span>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm">حفظ أهداف الأسابيع</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="tp-card">
                <div class="tp-card__header">
                    <div>
                        <span class="tp-card__header-icon"><i class="bi bi-journal-bookmark"></i></span>
                        تقدم الصفحات والدروس حسب المادة
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.teachers.approved-lessons', $teacher) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-list-ul me-1"></i> الدروس المعتمدة
                        </a>
                        <a href="{{ route('admin.teachers.progress.material-pages', $teacher) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-arrows-fullscreen me-1"></i> جدول موسّع
                        </a>
                    </div>
                </div>
                <div class="tp-card__body">
                    @include('admin.pages.teachers.partials.progress-pages-table', [
                        'pagesProgress' => $pages_progress ?? [],
                        'showApprovedCol' => true,
                    ])
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
    @can('teacher-assignment-update')
        @can('teacher-assignment-manage-subjects')
            @if($assignedSubjects->isNotEmpty())
                <script>
                    (function () {
                        const weekId = @json($displayWeekId ?? null);
                        const subjectPatchUrls = @json($assignedSubjects->mapWithKeys(function ($sub) use ($teacher) {
                            return [$sub->id => route('admin.teachers.assignments.subject-required-pages', [$teacher, $sub])];
                        })->all());

                        function csrfToken() {
                            const m = document.querySelector('meta[name="csrf-token"]');
                            return m ? m.getAttribute('content') : '';
                        }

                        function setStatus(subjectId, text, isError) {
                            const el = document.querySelector('.teacher-progress-pages-save-status[data-subject-id="' + subjectId + '"]');
                            if (!el) {
                                return;
                            }
                            el.textContent = text || '';
                            el.classList.toggle('text-danger', !!isError);
                            el.classList.toggle('text-success', !isError && !!text);
                        }

                        function applySummary(summary) {
                            if (!summary) {
                                return;
                            }
                            const req = summary.total_pages_required;
                            const done = summary.total_pages_completed;
                            const pct = summary.total_pages_percentage;
                            const elDone = document.getElementById('teacherProgressPagesSummaryCompleted');
                            const elReq = document.getElementById('teacherProgressPagesSummaryRequired');
                            const elBar = document.getElementById('teacherProgressPagesSummaryBar');
                            const elBadge = document.getElementById('teacherProgressPagesSummaryBadge');
                            if (elDone && typeof done === 'number') {
                                elDone.textContent = String(done);
                            }
                            if (elReq && typeof req === 'number') {
                                elReq.textContent = String(req);
                            }
                            if (elBar && typeof pct === 'number') {
                                const w = Math.min(100, pct);
                                elBar.style.width = w + '%';
                            }
                            if (elBadge && typeof pct === 'number') {
                                elBadge.textContent = Number(pct).toFixed(1) + '%';
                            }
                        }

                        function saveRequiredPages(subjectId, input) {
                            const url = subjectPatchUrls[subjectId];
                            if (!url) {
                                return;
                            }
                            const raw = input.value.trim();
                            let body = { required_pages: null, week_id: weekId };
                            if (raw !== '') {
                                const n = parseInt(raw, 10);
                                if (Number.isNaN(n) || n < 0) {
                                    setStatus(subjectId, 'رقم غير صالح', true);
                                    return;
                                }
                                body.required_pages = n;
                            }
                            setStatus(subjectId, 'جاري الحفظ…', false);
                            fetch(url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken()
                                },
                                body: JSON.stringify(body),
                                credentials: 'same-origin'
                            }).then(function (r) {
                                const ct = r.headers.get('content-type') || '';
                                if (ct.indexOf('application/json') !== -1) {
                                    return r.json().then(function (j) {
                                        return { ok: r.ok, json: j };
                                    });
                                }
                                return r.text().then(function (t) {
                                    return { ok: false, json: { message: t } };
                                });
                            }).then(function (res) {
                                if (res.ok && res.json && res.json.ok) {
                                    const d = res.json;
                                    if (d.required_pages === null || d.required_pages === undefined) {
                                        input.value = '';
                                    } else {
                                        input.value = String(d.required_pages);
                                    }
                                    const doneEl = document.querySelector('.teacher-progress-completed-pages[data-subject-id="' + subjectId + '"]');
                                    if (doneEl && typeof d.completed_pages === 'number') {
                                        doneEl.textContent = String(d.completed_pages);
                                    }
                                    setStatus(subjectId, 'تم الحفظ', false);
                                    applySummary(d.summary);
                                    setTimeout(function () {
                                        setStatus(subjectId, '', false);
                                    }, 2000);
                                } else {
                                    const msg = (res.json && (res.json.message || (res.json.errors && 'تحقق من القيمة'))) || 'تعذر الحفظ';
                                    setStatus(subjectId, msg, true);
                                }
                            }).catch(function () {
                                setStatus(subjectId, 'خطأ في الاتصال', true);
                            });
                        }

                        const timers = {};
                        document.querySelectorAll('.teacher-progress-required-pages-input').forEach(function (input) {
                            const sid = input.getAttribute('data-subject-id');
                            if (!sid) {
                                return;
                            }
                            input.addEventListener('input', function () {
                                clearTimeout(timers[sid]);
                                timers[sid] = setTimeout(function () {
                                    saveRequiredPages(sid, input);
                                }, 500);
                            });
                            input.addEventListener('change', function () {
                                clearTimeout(timers[sid]);
                                saveRequiredPages(sid, input);
                            });
                        });
                    })();
                </script>
            @endif
        @endcan
    @endcan
@endsection
