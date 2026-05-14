@extends('admin.layouts.master')

@section('page-title')
    تفاصيل تقدم المعلم: {{ $teacher->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل تقدم المعلم: {{ $teacher->name }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.teachers.progress.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> تقدم المعلمين
                    </a>
                    <a href="{{ route('admin.teachers.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-1"></i> قائمة المعلمين
                    </a>
                    <a href="{{ route('admin.teachers.progress.history', $teacher) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> إحصائيات سابقة
                    </a>
                    <a href="{{ route('admin.teachers.assignments', $teacher->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-tie me-1"></i> تخصيص
                    </a>
                    <a href="{{ route('admin.teachers.progress.material-pages', $teacher) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i> صفحات المواد
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

            {{-- فلتر الأسبوع وعرض السياق --}}
            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label class="form-label mb-0">عرض إحصائيات الأسبوع:</label>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="if(this.value){ window.location.href = '{{ route('admin.teachers.progress.show', $teacher) }}?week_id=' + this.value; }">
                                <option value="">الأسبوع الحالي</option>
                                @foreach($activeWeeks as $w)
                                    <option value="{{ $w->id }}" {{ isset($displayWeekId) && $displayWeekId == $w->id ? 'selected' : '' }}>
                                        {{ $w->title ?? 'الأسبوع ' . $w->week_number }} ({{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }})
                                    </option>
                                @endforeach
                            </select>
                            @if(isset($currentWeek) && $currentWeek)
                                <span class="small text-muted">الأسبوع المعروض: {{ $currentWeek->title ?? 'الأسبوع ' . $currentWeek->week_number }} ({{ $currentWeek->start_date->format('Y-m-d') }} - {{ $currentWeek->end_date->format('Y-m-d') }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- بطاقات الملخص --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">الدروس المعتمدة في المواد المخصصة</h6>
                            <p class="small text-muted mb-2">إجمالي دروس منشورة ومعتمدة (ليست «هدفاً» بل الإنجاز الفعلي في المحتوى).</p>
                            <h3 class="mb-0 text-primary">{{ $total_approved_lessons }}</h3>
                            <span class="small text-muted">درس معتمد</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">الصفحات الموكّلة — الإنجاز</h6>
                            @if($total_pages_required > 0)
                                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-2">
                                    <span class="fs-4 fw-bold text-success" id="teacherProgressPagesSummaryCompleted">{{ $total_pages_completed }}</span>
                                    <span class="text-muted">من</span>
                                    <span class="fs-4 fw-bold" id="teacherProgressPagesSummaryRequired">{{ $total_pages_required }}</span>
                                    <span class="small text-muted">صفحة</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div id="teacherProgressPagesSummaryBar" class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $total_pages_percentage) }}%;" aria-valuenow="{{ min(100, $total_pages_percentage) }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="mt-2"><span id="teacherProgressPagesSummaryBadge" class="badge bg-info text-dark">{{ number_format($total_pages_percentage, 1) }}%</span></div>
                            @else
                                <p class="mb-0 text-muted">لا يوجد هدف صفحات موكّل في المواد المخصصة.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm h-100 border border-warning">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">هدف الدروس الأسبوعي (الأسبوع المعروض)</h6>
                            @if(($weekly_progress['target'] ?? 0) > 0)
                                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-2">
                                    <span class="fs-4 fw-bold text-success">{{ $weekly_progress['completed'] }}</span>
                                    <span class="text-muted">من</span>
                                    <span class="fs-4 fw-bold">{{ $weekly_progress['target'] }}</span>
                                    <span class="small text-muted">درس معتمد هذا الأسبوع</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, $weekly_progress['percentage'] ?? 0) }}%;"></div>
                                </div>
                                @if($weekly_progress['percentage'] !== null)
                                    <div class="mt-2"><span class="badge {{ $weekly_progress['percentage'] >= 100 ? 'bg-success' : ($weekly_progress['percentage'] >= 50 ? 'bg-info text-dark' : 'bg-secondary') }}">{{ number_format($weekly_progress['percentage'], 1) }}%</span></div>
                                @endif
                            @else
                                <p class="mb-0 text-muted">لا يوجد هدف دروس لهذا الأسبوع (اضبط الجدول أدناه أو الهدف العام للمعلم).</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">تراكمي السنة (كل الأسابيع النشطة)</h6>
                            @if(isset($yearWeeksLessons) && ($yearWeeksLessons['year_total_target'] ?? 0) > 0)
                                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-2">
                                    <span class="fs-4 fw-bold text-success">{{ $yearWeeksLessons['year_total_completed'] }}</span>
                                    <span class="text-muted">من</span>
                                    <span class="fs-4 fw-bold">{{ $yearWeeksLessons['year_total_target'] }}</span>
                                    <span class="small text-muted">درس (مجموع أهداف الأسابيع)</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-dark" role="progressbar" style="width: {{ min(100, $yearWeeksLessons['year_percentage'] ?? 0) }}%;"></div>
                                </div>
                                @if($yearWeeksLessons['year_percentage'] !== null)
                                    <div class="mt-2"><span class="badge bg-dark">{{ number_format($yearWeeksLessons['year_percentage'], 1) }}%</span></div>
                                @endif
                            @else
                                <p class="mb-0 text-muted">لا توجد أهداف دروس مسجّلة للأسابيع بعد.</p>
                            @endif
                        </div>
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

            {{-- أهداف الأسابيع (Bulk override per week) --}}
            @if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2"></i>أهداف الدروس لكل أسابيع السنة (لهذا المعلم)</h6>
                        <span class="small text-muted">يمكنك إدخال قيمة مختلفة لكل أسبوع ثم حفظها دفعة واحدة. الأسابيع المنتهية مقفلة. «المنفّذ» = عدد الدروس المعتمدة التي يقع تاريخ اعتمادها (<code class="small">reviewed_at</code>) ضمن تواريخ ذلك الأسبوع الدراسي في مواد هذا المعلم.</span>
                    </div>
                    <div class="card-body">
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
                            <div class="table-responsive teacher-weeks-table-wrap">
                                <table class="table teacher-weeks-targets-table table-sm align-middle mb-3">
                                    <thead class="table-light">
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
                                                        <div class="small text-muted">
                                                            <span class="badge bg-secondary">مقفل</span>
                                                        </div>
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
                                                        <div>
                                                            <span class="text-muted">المنفّذ:</span>
                                                            <span class="fw-semibold text-success">{{ $wl['completed'] }}</span>
                                                            <span class="text-muted small">درسًا</span>
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="text-muted">الهدف:</span>
                                                            <span class="fw-semibold">{{ $wl['target'] }}</span>
                                                            <span class="text-muted small">درسًا</span>
                                                        </div>
                                                    </div>
                                                    <div class="small text-muted mt-1">حسب <code class="small">reviewed_at</code> ضمن فترة الأسبوع.</div>
                                                </td>
                                                <td>
                                                    @if($pct !== null)
                                                        <span class="badge {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info text-dark' : 'bg-warning text-dark') }}">{{ number_format($pct, 1) }}%</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar {{ ($pct ?? 0) >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $barW }}%;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <p class="small text-muted mb-2">ملاحظة: إذا تركت القيمة فارغة سيعتبرها النظام 0.</p>
                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span><span class="d-inline-block rounded px-2 py-0 me-1 bg-success bg-opacity-10 border border-success border-opacity-25">&nbsp;</span> الأسبوع الحالي</span>
                                    <span><span class="d-inline-block rounded px-2 py-0 me-1 bg-danger bg-opacity-10 border border-danger border-opacity-25">&nbsp;</span> أسبوع منتهٍ ولم يُحقق الهدف (المنفّذ أقل من الهدف)</span>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">حفظ أهداف الأسابيع</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- جدول تقدم الصفحات حسب المادة --}}
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-journal-bookmark me-2"></i>
                            تقدم الصفحات والدروس حسب المادة
                        </h6>
                        <small class="text-muted">لكل مادة: الصفحات الموكّلة (المطلوبة) مقابل ما تم إنجازه من صفحات الدروس المعتمدة، وعدد الدروس المعتمدة في المادة.</small>
                    </div>
                    <a href="{{ route('admin.teachers.progress.material-pages', $teacher) }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                        <i class="bi bi-arrows-fullscreen me-1"></i> صفحة الجدول الموسّعة
                    </a>
                </div>
                <div class="card-body">
                    @if(!empty($pages_progress))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th class="text-center">الدروس المعتمدة<br><span class="small fw-normal text-muted">(في المادة)</span></th>
                                        <th class="text-center">الصفحات الموكّلة</th>
                                        <th class="text-center">الصفحات المنجزة</th>
                                        <th class="text-center">المتبقي</th>
                                        <th class="text-center" style="min-width: 120px;">نسبة الصفحات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pages_progress as $row)
                                        @php
                                            $subj = $row['subject'];
                                            $req = (int) ($row['required_pages'] ?? 0);
                                            $done = (int) ($row['completed_pages'] ?? 0);
                                            $pct = $row['percentage'];
                                            $bar = $pct !== null ? min(100, $pct) : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $subj->name }}</td>
                                            <td class="small text-muted">{{ $subj->schoolClass?->name ?? '—' }}</td>
                                            <td class="text-center">{{ $row['approved_lessons_count'] ?? 0 }}</td>
                                            <td class="text-center">{{ $req }}</td>
                                            <td class="text-center text-success fw-semibold">{{ $done }}</td>
                                            <td class="text-center">{{ $row['remaining_pages'] }}</td>
                                            <td>
                                                @if($pct !== null)
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info text-dark' : 'bg-warning text-dark') }}">{{ number_format($pct, 1) }}%</span>
                                                    </div>
                                                    <div class="progress mt-1" style="height: 8px;">
                                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $bar }}%;"></div>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">لا هدف صفحات</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">لا توجد مواد مخصصة لهذا المعلم.</p>
                    @endif
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
                                elBar.setAttribute('aria-valuenow', String(w));
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

@push('styles')
    <style>
        .teacher-weeks-table-wrap {
            padding: 0.25rem 0.15rem 0.5rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table {
            border-collapse: separate;
            border-spacing: 0 0.65rem;
            width: 100%;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table thead th {
            border: none;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            background-color: var(--bs-table-bg, #f8f9fa);
            padding-bottom: 0.6rem;
            vertical-align: middle;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody td {
            border-block: 1px solid rgba(0, 0, 0, 0.1);
            border-inline-start: none;
            border-inline-end: 1px solid rgba(0, 0, 0, 0.07);
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            vertical-align: middle;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:first-child {
            border-inline-start: 1px solid rgba(0, 0, 0, 0.1);
            border-start-start-radius: 0.45rem;
            border-end-start-radius: 0.45rem;
        }

        .teacher-weeks-table-wrap table.teacher-weeks-targets-table tbody tr td:last-child {
            border-start-end-radius: 0.45rem;
            border-end-end-radius: 0.45rem;
        }
    </style>
@endpush
