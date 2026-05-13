<h6 class="text-muted small fw-bold mb-2">
    <i class="bi bi-bookmark-check me-1"></i> لمحة: المواد المخصّصة ضمن الصفوف المحددة
</h6>
<p class="small text-muted mb-3">تظهر المواد المخصّصة للمعلم والتابعة لأي من الصفوف التي وضعت عليها علامة في العمود المجاور، مع عدد الصفحات الحالي.</p>
@if($assignedSubjects->isEmpty())
    <p class="small text-muted mb-0">لا توجد مواد مخصّصة بعد.</p>
@else
    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
        @foreach($assignedSubjects as $sub)
            <div class="list-group-item py-2 px-2 summary-assigned-item border-0 border-bottom bg-transparent" data-summary-class-id="{{ $sub->class_id }}">
                <div class="fw-semibold small">{{ $sub->name }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">{{ $sub->schoolClass?->name }}</div>
                <div class="d-flex align-items-center justify-content-between mt-1 flex-wrap gap-1">
                    <span class="badge bg-secondary">{{ (int) ($sub->pivot->required_pages ?? 0) }} صفحة</span>
                    <div class="d-flex gap-1">
                        <a href="#subject_row_wrap_{{ $sub->id }}" class="small text-decoration-none">تعديل</a>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 small" onclick="detachSubject({{ $sub->id }})">فصل</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
