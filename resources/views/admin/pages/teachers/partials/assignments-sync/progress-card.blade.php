{{-- بطاقة ملخص التقدم + أعداد الصفوف/المواد (نفس المحتوى لصفحة التخصيص واستجابة AJAX) --}}
<div class="card border-0 shadow-sm mb-3" id="teacherAssignmentsProgressCard">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold"><i class="bi bi-speedometer2 me-2"></i> ملخص التقدم (قراءة فقط)</span>
        <a href="{{ route('admin.teachers.progress.show', $teacher) }}" class="btn btn-sm btn-outline-primary">تفاصيل أوضح مع الأسابيع</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-muted mb-1">دروس الأسبوع الحالي (منفّذ / هدف)</div>
                <div class="fs-5">
                    <span class="fw-bold text-success">{{ $teacherProgressStats['weekly_progress']['completed'] ?? 0 }}</span>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">{{ $teacherProgressStats['weekly_progress']['target'] ?? 0 }}</span>
                </div>
                @if(($teacherProgressStats['weekly_progress']['percentage'] ?? null) !== null)
                    <span class="badge bg-primary mt-1">{{ number_format($teacherProgressStats['weekly_progress']['percentage'], 1) }}%</span>
                @endif
            </div>
            <div class="col-md-4">
                <div class="small text-muted mb-1">تراكمي السنة (منفّذ / مجموع أهداف الأسابيع)</div>
                @if(($yearWeeksLessons['year_total_target'] ?? 0) > 0)
                    <div class="fs-5">
                        <span class="fw-bold text-success">{{ $yearWeeksLessons['year_total_completed'] }}</span>
                        <span class="text-muted">/</span>
                        <span class="fw-bold">{{ $yearWeeksLessons['year_total_target'] }}</span>
                    </div>
                    @if($yearWeeksLessons['year_percentage'] !== null)
                        <span class="badge bg-dark mt-1">{{ number_format($yearWeeksLessons['year_percentage'], 1) }}%</span>
                    @endif
                @else
                    <span class="text-muted small">لا أهداف دروس بعد</span>
                @endif
            </div>
            <div class="col-md-4">
                <div class="small text-muted mb-1">الصفحات (منجز / موكّل)</div>
                @if(($teacherProgressStats['total_pages_required'] ?? 0) > 0)
                    <div class="fs-5">
                        <span class="fw-bold text-success">{{ $teacherProgressStats['total_pages_completed'] }}</span>
                        <span class="text-muted">/</span>
                        <span class="fw-bold">{{ $teacherProgressStats['total_pages_required'] }}</span>
                    </div>
                    @if($teacherProgressStats['total_pages_percentage'] !== null)
                        <span class="badge bg-success mt-1">{{ number_format($teacherProgressStats['total_pages_percentage'], 1) }}%</span>
                    @endif
                @else
                    <span class="text-muted small">لا هدف صفحات</span>
                @endif
            </div>
        </div>
        <hr class="border-secondary opacity-25 my-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted mb-1">الصفوف المخصّصة للمعلم</div>
                <div class="fs-5">
                    <span class="fw-bold text-primary" id="teacherAssignmentsStatClassesCount">{{ $assignedClasses->count() }}</span>
                    <span class="text-muted small">صف</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted mb-1">المواد المخصّصة للمعلم (إجمالي)</div>
                <div class="fs-5">
                    <span class="fw-bold text-info" id="teacherAssignmentsStatSubjectsCount">{{ $assignedSubjects->count() }}</span>
                    <span class="text-muted small">مادة</span>
                </div>
            </div>
        </div>
    </div>
</div>
