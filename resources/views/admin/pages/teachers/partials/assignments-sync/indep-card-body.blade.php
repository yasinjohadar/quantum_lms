@php
    $canManageSubjects = auth()->user()?->can('teacher-assignment-manage-subjects');
@endphp
<label class="form-label small fw-semibold mb-1">فلترة القسم حسب الصف (مطلوب)</label>
<select class="form-select form-select-sm mb-2" id="indepClassFilter" onchange="syncIndepClassFilterFromIndep()">
    <option value="">— اختر صفاً لعرض المواد —</option>
    @foreach($allClasses as $class)
        <option value="{{ $class->id }}">{{ $class->name }}</option>
    @endforeach
</select>
<div id="indepNeedClassPrompt" class="alert alert-light border small mb-0">
    اختر صفاً من القائمة أدناه لعرض قسم التخصيص المفصّل لهذا الصف فقط.
</div>
<div id="indepListsRow" class="row g-3 d-none mt-2">
    <div class="col-md-6 border-md-end">
        <label class="form-label small fw-semibold mb-1">مواد غير مخصصة لهذا الصف</label>
        <p class="small text-muted mb-2">اضغط «إضافة للمخصّص» لتفعيل المادة وحفظها <strong>فوراً</strong> عبر الخادم، مع تحديث القائمة واللمحة دون إعادة تحميل الصفحة.</p>
        <div class="list-group" id="indepUnassignedList" style="max-height: 380px; overflow-y: auto;">
            @foreach($allSubjects as $subject)
                @if($assignedSubjects->contains('id', $subject->id))
                    @continue
                @endif
                <div class="list-group-item indep-unassigned-row py-2" data-class-id="{{ $subject->class_id ?? '' }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <strong class="small">{{ $subject->name }}</strong>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $subject->schoolClass?->name }}</div>
                        </div>
                        @if($canManageSubjects)
                            <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" onclick="assignSubjectFromHelper({{ $subject->id }})">
                                إضافة للمخصّص
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold mb-1">مواد مخصصة لهذا الصف</label>
        <p class="small text-muted mb-2">عدد الصفحات من الحقول في القائمة الرئيسية أعلاه (زر «تعديل الصفحات»).</p>
        @if($assignedSubjects->isEmpty())
            <p class="text-muted small mb-0">لا مواد مخصصة بعد.</p>
        @else
            <div class="list-group" id="indepAssignedList" style="max-height: 380px; overflow-y: auto;">
                @foreach($assignedSubjects as $sub)
                    <div class="list-group-item py-2 indep-assigned-hint" data-indep-class-id="{{ $sub->class_id }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <strong class="small">{{ $sub->name }}</strong>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $sub->schoolClass?->name }}</div>
                                <span class="badge bg-secondary mt-1">{{ (int) ($sub->pivot->required_pages ?? 0) }} صفحة مطلوبة</span>
                            </div>
                            @if($canManageSubjects)
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <a href="#subject_row_wrap_{{ $sub->id }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">تعديل الصفحات</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="detachSubject({{ $sub->id }})">فصل</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
