@if($showGlobalTools ?? false)
    <div class="qb-quick-links">
        <a href="{{ route('admin.questions.index', ['filter' => 'orphan']) }}"
           class="qb-quick-link qb-quick-link--warning {{ request('filter') == 'orphan' ? 'qb-quick-link--active' : '' }}">
            <i class="bi bi-exclamation-circle"></i> أسئلة غير مرتبطة
        </a>
        <a href="{{ route('admin.questions.index', ['with_deleted' => '1']) }}"
           class="qb-quick-link qb-quick-link--danger {{ request('with_deleted') == '1' ? 'qb-quick-link--active' : '' }}">
            <i class="bi bi-trash"></i> سلة المحذوفات
        </a>
        <span class="qb-quick-link" style="cursor: default; pointer-events: none;" id="questionBankTotalBadge">
            <i class="bi bi-collection"></i> إجمالي: {{ number_format($questions->total()) }}
        </span>
    </div>
@else
    <div class="mb-3">
        <span class="qb-quick-link" style="cursor: default; pointer-events: none;" id="questionBankTotalBadge">
            <i class="bi bi-collection"></i> إجمالي: {{ number_format($questions->total()) }}
        </span>
    </div>
@endif

@php
    $canExportWord = auth()->user()->can('question-export');
    $canBulkDelete = auth()->user()->can('question-delete') && !empty($bulkDeleteUrl);
    $showBulkSelection = ($canExportWord || $canBulkDelete) && $questions->count() > 0;
@endphp

@if($showBulkSelection)
    <div class="qb-bulk-bar" id="questionBulkToolbar"
        @if(!empty($bulkIdsUrl)) data-bulk-ids-url="{{ $bulkIdsUrl }}" @endif
        data-filtered-total="{{ $questions->total() }}">
        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllQuestionsOnPageBtn">
            <i class="bi bi-check2-square me-1"></i> تحديد الصفحة
        </button>
        @if($questions->total() > $questions->count())
            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllQuestionsFilteredBtn">
                <i class="bi bi-check2-all me-1"></i> تحديد الكل ({{ number_format($questions->total()) }})
            </button>
        @endif
        <div id="questionBulkActionsBar" class="d-none qb-bulk-bar__actions">
            <span class="qb-bulk-selected-count"><span id="questionSelectedCount">0</span> محدد</span>
            @can('question-export')
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exportQuestionsWordModal" data-export-scope="selected">
                    <i class="bi bi-file-earmark-word me-1"></i> تصدير
                </button>
            @endcan
            @if($canBulkDelete)
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBulkDeleteQuestionsModal">
                    <i class="bi bi-trash me-1"></i> حذف
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearQuestionSelectionBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
@endif

<div class="qb-grid" id="questionBankCards">
    @forelse($questions as $question)
        <div class="card custom-card question-card question-type-{{ $question->type }} d-flex flex-column" data-question-id="{{ $question->id }}">
            <div class="card-body d-flex flex-column">
                <div class="qb-card-top">
                    <div class="qb-card-top__left">
                        @if($showBulkSelection)
                            @php
                                $disableSelection = $canBulkDelete && ! $canExportWord && $question->quizzes_count > 0;
                            @endphp
                            <input type="checkbox"
                                class="form-check-input question-bulk-checkbox mt-1 flex-shrink-0"
                                value="{{ $question->id }}"
                                @if($disableSelection) disabled title="مستخدم في اختبار" aria-label="لا يمكن تحديد سؤال مستخدم في اختبار" @else aria-label="تحديد السؤال" @endif>
                        @endif
                        <span class="badge qb-type-badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                            {{ $question->type_label }}
                        </span>
                    </div>
                    <div class="qb-card-menu dropdown">
                        <button class="btn btn-sm" data-bs-toggle="dropdown" aria-label="خيارات السؤال">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @can('question-show')
                                <li><a class="dropdown-item" href="{{ route('admin.questions.show', $question->id) }}"><i class="bi bi-eye me-2"></i>عرض</a></li>
                            @endcan
                            @can('question-edit')
                                <li><a class="dropdown-item" href="{{ route('admin.questions.edit', $question->id) }}{{ isset($subject) ? '?subject_id='.$subject->id : '' }}"><i class="bi bi-pencil me-2"></i>تعديل</a></li>
                            @endcan
                            @if(isset($subject))
                                @can('quiz-add-question')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item add-to-quiz-btn"
                                            data-question-id="{{ $question->id }}"
                                            data-subject-id="{{ $subject->id }}"
                                            data-question-points="{{ $question->default_points }}">
                                            <i class="bi bi-journal-plus me-2"></i> إضافة إلى اختبار
                                        </button>
                                    </li>
                                @endcan
                            @endif
                            @can('question-delete')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestion{{ $question->id }}">
                                        <i class="bi bi-trash me-2"></i>حذف
                                    </button>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </div>

                <h6 class="mb-2">
                    @can('question-show')
                        <a href="{{ route('admin.questions.show', $question->id) }}" class="text-decoration-none question-text-body question-card-preview d-block">
                            {!! format_question_markup($question->title) !!}
                        </a>
                    @else
                        <span class="question-text-body question-card-preview d-block">{!! format_question_markup($question->title) !!}</span>
                    @endcan
                </h6>

                @if(question_content_differs_from_title($question->title, $question->content))
                    <p class="text-muted small mb-2 question-text-body question-card-preview">{!! format_question_markup($question->content) !!}</p>
                @endif

                <div class="qb-meta-badges">
                    <span class="badge question-points-badge">
                        <i class="bi bi-star-fill me-1"></i>{{ $question->default_points }} نقطة
                    </span>
                    <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}">
                        {{ $question->difficulty_label }}
                    </span>
                    @if(!$question->is_active)
                        <span class="badge bg-secondary-transparent text-secondary">غير نشط</span>
                    @endif
                </div>

                @php
                    $curriculumRows = $question->curriculumLocations();
                    $extraCurriculumCount = max(0, $curriculumRows->count() - 2);
                @endphp
                <div class="qb-curriculum">
                    @if($curriculumRows->isEmpty())
                        <span class="qb-curriculum--global">
                            <i class="bi bi-globe2"></i> سؤال عام
                        </span>
                    @else
                        @foreach($curriculumRows->take(2) as $row)
                            <div class="qb-curriculum-row">
                                @if($row['class'])
                                    <span class="qb-curriculum-pill">{{ $row['class'] }}</span>
                                @endif
                                @if($row['subject'])
                                    <span class="qb-curriculum-pill">{{ $row['subject'] }}</span>
                                @endif
                                <span class="qb-curriculum-pill qb-curriculum-pill--unit">{{ $row['unit'] }}</span>
                            </div>
                        @endforeach
                        @if($extraCurriculumCount > 0)
                            <span class="qb-curriculum-more">+{{ $extraCurriculumCount }} موقع إضافي</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        @can('question-delete')
        <div class="modal fade" id="deleteQuestion{{ $question->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="border-0 text-center pt-4 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <span class="me-2 fs-4 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            <h5 class="modal-title mb-0 fw-bold">حذف السؤال</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center pt-0 pb-3 px-4">
                            <p class="mb-1 text-muted">هل أنت متأكد من حذف السؤال:</p>
                            <p class="fw-bold mb-1">{{ Str::limit(strip_tags($question->title), 50) }}</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> حذف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    @empty
        <div class="qb-empty">
            <div class="qb-empty__icon">
                <i class="bi bi-inbox"></i>
            </div>
            <h6 class="fw-bold mb-1">
                @if(isset($subject))
                    لا توجد أسئلة لهذه المادة
                @else
                    لا توجد أسئلة تطابق الفلاتر
                @endif
            </h6>
            <p class="text-muted small mb-3">جرّب تغيير معايير البحث أو أضف سؤالاً جديداً</p>
            @can('question-create')
                <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> إضافة أول سؤال
                </a>
            @endcan
        </div>
    @endforelse
</div>

@if($questions->hasPages())
    <div class="qb-pagination" id="questionBankPagination">
        {{ $questions->links() }}
    </div>
@else
    <div id="questionBankPagination"></div>
@endif
