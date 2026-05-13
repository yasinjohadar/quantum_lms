@php
    $showClassActions = auth()->user()?->can('teacher-assignment-update') && auth()->user()->can('teacher-assignment-manage-classes');
    $showSubjectActions = auth()->user()?->can('teacher-assignment-update') && auth()->user()->can('teacher-assignment-manage-subjects');
    $pagesProgressBySubjectId = $pagesProgressBySubjectId ?? [];
    $subjectCols = 4 + ($showSubjectActions ? 1 : 0);
@endphp
{{-- الصفوف والمواد المخصصة للمعلم (عرض + إضافة/فصل عند توفر الصلاحيات) --}}
<div class="card shadow-sm border-0 mb-4" id="teacher-progress-assignments">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-person-badge me-2"></i>
            الصفوف والمواد المخصصة لهذا المعلم
        </h6>
        @can('teacher-assignment-show')
            <a href="{{ route('admin.teachers.assignments', $teacher) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-user-tie me-1"></i> فتح صفحة التخصيص الكاملة
            </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-4">
            {{-- الصفوف --}}
            <div class="col-lg-6">
                <h6 class="text-muted small fw-bold mb-2"><i class="bi bi-building me-1"></i> الصفوف</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الصف</th>
                                <th>المرحلة</th>
                                    @if($showClassActions)
                                            <th class="text-center" style="width: 90px;">إجراء</th>
                                    @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedClasses as $class)
                                <tr>
                                    <td class="fw-semibold">{{ $class->name }}</td>
                                    <td class="small text-muted">{{ $class->stage?->name ?? '—' }}</td>
                                    @if($showClassActions)
                                            <td class="text-center">
                                                <form action="{{ route('admin.teachers.assignments.detach-class', [$teacher, $class]) }}" method="POST" class="d-inline" onsubmit="return confirm('فصل هذا الصف عن المعلم؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    @if(request()->filled('week_id'))
                                                        <input type="hidden" name="week_id" value="{{ (int) request('week_id') }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">فصل</button>
                                                </form>
                                            </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $showClassActions ? 3 : 2 }}" class="text-muted text-center py-3">لا توجد صفوف مخصصة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('teacher-assignment-update')
                    @can('teacher-assignment-manage-classes')
                        @if($unassignedClasses->isNotEmpty())
                            <form action="{{ route('admin.teachers.assignments.attach-class', $teacher) }}" method="POST" class="mt-3 d-flex flex-wrap gap-2 align-items-end">
                                @csrf
                                @if(request()->filled('week_id'))
                                    <input type="hidden" name="week_id" value="{{ (int) request('week_id') }}">
                                @endif
                                <div class="flex-grow-1" style="min-width: 200px;">
                                    <label class="form-label small mb-1">إضافة صف</label>
                                    <select name="class_id" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>اختر صفاً…</option>
                                        @foreach($unassignedClasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }} @if($class->stage) — {{ $class->stage->name }} @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">إضافة</button>
                            </form>
                        @endif
                    @endcan
                @endcan
            </div>

            {{-- المواد --}}
            <div class="col-lg-6">
                <h6 class="text-muted small fw-bold mb-2"><i class="bi bi-book me-1"></i> المواد</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>المادة</th>
                                <th>الصف</th>
                                <th class="text-center small" style="min-width: 110px;">صفحات مطلوبة</th>
                                <th class="text-center small" style="min-width: 110px;">الصفحات المنجزة</th>
                                @if($showSubjectActions)
                                    <th class="text-center" style="width: 90px;">إجراء</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedSubjects as $subject)
                                @php
                                    $rp = $subject->pivot->required_pages ?? null;
                                    $pp = $pagesProgressBySubjectId[$subject->id] ?? null;
                                    $completedPages = $pp !== null
                                        ? (int) ($pp['completed_pages'] ?? 0)
                                        : \App\Services\TeacherProgressService::getSubjectCompletedPages($subject->id);
                                @endphp
                                <tr data-subject-progress-row="{{ $subject->id }}">
                                    <td class="fw-semibold">
                                        {{ $subject->name }}
                                        @if($subject->trashed())
                                            <span class="badge bg-secondary ms-1 align-middle">مؤرشفة</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $subject->schoolClass?->name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($showSubjectActions)
                                            <input type="number"
                                                   min="0"
                                                   class="form-control form-control-sm mx-auto teacher-progress-required-pages-input"
                                                   style="max-width: 110px;"
                                                   name="required_pages_live[{{ $subject->id }}]"
                                                   data-subject-id="{{ $subject->id }}"
                                                   value="{{ $rp !== null && $rp !== '' ? (int) $rp : '' }}"
                                                   placeholder="—"
                                                   aria-label="صفحات مطلوبة — {{ $subject->name }}">
                                            <span class="d-block small text-muted mt-1 teacher-progress-pages-save-status" data-subject-id="{{ $subject->id }}" aria-live="polite"></span>
                                        @else
                                            <span class="small">{{ $rp !== null && $rp !== '' ? (int) $rp : '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success fw-semibold teacher-progress-completed-pages" data-subject-id="{{ $subject->id }}">{{ $completedPages }}</span>
                                    </td>
                                    @if($showSubjectActions)
                                        <td class="text-center">
                                            <form action="{{ route('admin.teachers.assignments.detach-subject', [$teacher, $subject]) }}" method="POST" class="d-inline" onsubmit="return confirm('فصل هذه المادة عن المعلم؟');">
                                                @csrf
                                                @method('DELETE')
                                                @if(request()->filled('week_id'))
                                                    <input type="hidden" name="week_id" value="{{ (int) request('week_id') }}">
                                                @endif
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">فصل</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $subjectCols }}" class="text-muted text-center py-3">لا توجد مواد مخصصة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('teacher-assignment-update')
                    @can('teacher-assignment-manage-subjects')
                        @if($unassignedSubjects->isNotEmpty())
                            <form action="{{ route('admin.teachers.assignments.attach-subject', $teacher) }}" method="POST" class="mt-3">
                                @csrf
                                @if(request()->filled('week_id'))
                                    <input type="hidden" name="week_id" value="{{ (int) request('week_id') }}">
                                @endif
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">إضافة مادة</label>
                                        <select name="subject_id" class="form-select form-select-sm" required>
                                            <option value="" disabled selected>اختر مادة…</option>
                                            @foreach($unassignedSubjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }} @if($subject->schoolClass) — {{ $subject->schoolClass->name }} @endif</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">صفحات مطلوبة (اختياري)</label>
                                        <input type="number" name="required_pages" class="form-control form-control-sm" min="0" placeholder="—">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">إضافة</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endcan
                @endcan
            </div>
        </div>
    </div>
</div>
