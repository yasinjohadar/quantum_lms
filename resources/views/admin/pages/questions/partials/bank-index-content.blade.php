@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li class="small">{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>
@endif

<div class="card custom-card mb-3">
    <div class="card-body">
        <form action="{{ $formAction }}" method="GET" id="questionBankFilters">
            <div class="row g-3">
                @if(!isset($subject) && ($schoolClasses ?? collect())->isNotEmpty())
                    <div class="col-md-2">
                        <label class="form-label" for="filter_class_id">الصف</label>
                        <select name="class_id" id="filter_class_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($schoolClasses as $schoolClass)
                                <option value="{{ $schoolClass->id }}" {{ (string) request('class_id') === (string) $schoolClass->id ? 'selected' : '' }}>{{ $schoolClass->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="filter_subject_id">المادة</label>
                        <select name="subject_id" id="filter_subject_id" class="form-select" @if(!request('class_id')) disabled @endif>
                            <option value="">{{ request('class_id') ? 'الكل' : 'اختر الصف أولاً' }}</option>
                            @foreach(($initialSubjects ?? collect()) as $filterSubject)
                                <option value="{{ $filterSubject->id }}" {{ (string) request('subject_id') === (string) $filterSubject->id ? 'selected' : '' }}>{{ $filterSubject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-{{ !isset($subject) && ($schoolClasses ?? collect())->isNotEmpty() ? '2' : '3' }}">
                    <label class="form-label">بحث</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="filter_search" class="form-control" placeholder="ابحث بعنوان السؤال..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">النوع</label>
                    <select name="type" id="filter_type" class="form-select">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Question::TYPES as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ request('type') == $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الصعوبة</label>
                    <select name="difficulty" id="filter_difficulty" class="form-select">
                        <option value="">الكل</option>
                        <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                        <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                        <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                    </select>
                </div>
                @if(isset($subject) && $units->isNotEmpty())
                    <div class="col-md-2">
                        <label class="form-label">الوحدة</label>
                        <select name="unit_id" id="filter_unit_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" id="filter_is_active" class="form-select">
                        <option value="">الكل</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">ترتيب</label>
                    <select name="sort" id="filter_sort" class="form-select">
                        <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم</option>
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>العنوان</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" id="questionBankFilterSubmit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $bulkDeleteUrl = null;
    if (auth()->user()->can('question-delete')) {
        if (isset($subject)) {
            $bulkDeleteUrl = route('admin.subjects.questions.destroy-multiple', array_merge([$subject], request()->query()));
        } else {
            $bulkDeleteUrl = route('admin.questions.destroy-multiple', request()->query());
        }
    }
@endphp

<div id="questionBankResults">
    @include('admin.pages.questions.partials.bank-index-results', [
        'questions' => $questions,
        'subject' => $subject ?? null,
        'createRoute' => $createRoute,
        'showGlobalTools' => $showGlobalTools ?? false,
        'bulkDeleteUrl' => $bulkDeleteUrl,
    ])
</div>

@can('question-delete')
    @if($bulkDeleteUrl)
        @include('admin.pages.questions.partials.bank-index-bulk-delete', ['bulkDeleteUrl' => $bulkDeleteUrl])
    @endif
@endcan

@if(isset($subject))
    @can('quiz-add-question')
    <div class="modal fade" id="addQuestionToQuizModal" tabindex="-1" aria-labelledby="addQuestionToQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="addQuestionToQuizModalLabel">إضافة إلى اختبار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3" id="addToQuizQuestionPreview"></p>
                    <div id="addToQuizLoading" class="text-center py-4 d-none">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        <span class="ms-2 text-muted small">جاري تحميل الاختبارات...</span>
                    </div>
                    <div id="addToQuizError" class="alert alert-danger small d-none mb-0"></div>
                    <div id="addToQuizEmpty" class="text-center py-4 d-none">
                        <i class="bi bi-journal-x display-6 text-muted"></i>
                        <p class="text-muted small mt-2 mb-0">لا توجد اختبارات لهذه المادة</p>
                    </div>
                    <div class="list-group list-group-flush d-none" id="addToQuizList" style="max-height: 320px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endif
