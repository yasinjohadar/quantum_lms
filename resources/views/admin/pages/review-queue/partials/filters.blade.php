@php
    $filterAction = $filterAction ?? route('admin.review-queue.lessons');
    $resetRoute = $resetRoute ?? $filterAction;
@endphp
<div class="rq-card mb-4">
    <div class="rq-card__header">
        <span><span class="rq-card__header-icon"><i class="bi bi-funnel"></i></span> تصفية وبحث</span>
    </div>
    <div class="rq-card__body">
        <form method="GET" class="rq-filters">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-muted">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="عنوان..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-muted">الصف</label>
                    <select name="class_id" class="form-select">
                        <option value="">كل الصفوف</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-muted">المادة</label>
                    <select name="subject_id" class="form-select">
                        <option value="">كل المواد</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i> بحث
                    </button>
                    <a href="{{ $resetRoute }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
