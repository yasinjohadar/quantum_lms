<form method="GET" action="{{ route('admin.lessons.index') }}" class="lessons-index-filters" id="lessonsFilterForm">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label">بحث</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" id="lessonsSearchInput" class="form-control border-start-0"
                       placeholder="عنوان الدرس..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">الصف</label>
            <select name="class_id" id="lessonsClassFilter" class="form-select">
                <option value="">كل الصفوف</option>
                @foreach($classes ?? [] as $class)
                    <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                        @if($class->stage) — {{ $class->stage->name }} @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">المادة</label>
            <select name="subject_id" id="lessonsSubjectFilter" class="form-select" {{ ! request('class_id') ? 'disabled' : '' }}>
                <option value="">{{ request('class_id') ? 'كل المواد' : 'اختر الصف أولاً' }}</option>
                @if(request('class_id'))
                    @foreach($subjects ?? [] as $subject)
                        @if((int) $subject->class_id === (int) request('class_id'))
                            <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endif
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">القسم</label>
            <select name="section_id" id="lessonsSectionFilter" class="form-select" {{ ! request('subject_id') ? 'disabled' : '' }}>
                <option value="">{{ request('subject_id') ? 'كل الأقسام' : 'اختر المادة أولاً' }}</option>
                @foreach($sections ?? [] as $section)
                    <option value="{{ $section->id }}" {{ (string) request('section_id') === (string) $section->id ? 'selected' : '' }}>
                        {{ $section->path_title ?? $section->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">الوحدة</label>
            <select name="unit_id" id="lessonsUnitFilter" class="form-select" {{ ! request('section_id') ? 'disabled' : '' }}>
                <option value="">{{ request('section_id') ? 'كل الوحدات' : 'اختر القسم أولاً' }}</option>
                @foreach($units ?? [] as $unit)
                    <option value="{{ $unit->id }}" {{ (string) request('unit_id') === (string) $unit->id ? 'selected' : '' }}>
                        {{ $unit->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">حالة المراجعة</label>
            <select name="review_status" class="form-select lessons-auto-filter">
                <option value="">الكل</option>
                @foreach(\App\Models\Lesson::REVIEW_STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ request('review_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">نوع الفيديو</label>
            <select name="video_type" class="form-select lessons-auto-filter">
                <option value="">الكل</option>
                @foreach(\App\Models\Lesson::VIDEO_TYPES as $value => $label)
                    <option value="{{ $value }}" {{ request('video_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">الحالة</label>
            <select name="is_active" class="form-select lessons-auto-filter">
                <option value="">الكل</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطّل</option>
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">الموضع</label>
            <select name="placement" class="form-select lessons-auto-filter">
                <option value="">الكل</option>
                <option value="unit" {{ request('placement') === 'unit' ? 'selected' : '' }}>داخل وحدة</option>
                <option value="section" {{ request('placement') === 'section' ? 'selected' : '' }}>مباشرة في قسم</option>
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">نوع السجل</label>
            <select name="link_role" class="form-select lessons-auto-filter">
                <option value="">الكل</option>
                <option value="original" {{ request('link_role') === 'original' ? 'selected' : '' }}>أصل</option>
                <option value="mirror" {{ request('link_role') === 'mirror' ? 'selected' : '' }}>نسخة متزامنة</option>
            </select>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label">الارتباطات</label>
            <select name="link_presence" class="form-select lessons-auto-filter">
                <option value="any" {{ request('link_presence', 'any') === 'any' ? 'selected' : '' }}>أي</option>
                <option value="has_sync" {{ request('link_presence') === 'has_sync' ? 'selected' : '' }}>له نسخ sync</option>
                <option value="has_legacy" {{ request('link_presence') === 'has_legacy' ? 'selected' : '' }}>له ربط legacy</option>
                <option value="has_any_link" {{ request('link_presence') === 'has_any_link' ? 'selected' : '' }}>أي ارتباط</option>
                <option value="none" {{ request('link_presence') === 'none' ? 'selected' : '' }}>بدون ارتباط</option>
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill" id="lessonsSearchBtn">
                <i class="bi bi-search me-1"></i> بحث
            </button>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-outline-secondary" title="مسح">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </div>
</form>
