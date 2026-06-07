<div class="d-flex flex-wrap gap-2 mb-3">
    @if($showGlobalTools ?? false)
        <a href="{{ route('admin.questions.index', ['filter' => 'orphan']) }}" class="btn btn-sm {{ request('filter') == 'orphan' ? 'btn-warning' : 'btn-outline-warning' }}">
            <i class="bi bi-exclamation-circle me-1"></i> أسئلة غير مرتبطة
        </a>
        <a href="{{ route('admin.questions.index', ['with_deleted' => '1']) }}" class="btn btn-sm {{ request('with_deleted') == '1' ? 'btn-danger' : 'btn-outline-danger' }}">
            <i class="bi bi-trash me-1"></i> سلة المحذوفات
        </a>
    @endif
    <span class="badge bg-info-transparent text-info d-flex align-items-center" id="questionBankTotalBadge">
        <i class="bi bi-info-circle me-1"></i>
        إجمالي الأسئلة: {{ $questions->total() }}
    </span>
</div>

@php
    $canExportWord = auth()->user()->can('question-export');
    $canBulkDelete = auth()->user()->can('question-delete') && !empty($bulkDeleteUrl);
    $showBulkSelection = ($canExportWord || $canBulkDelete) && $questions->count() > 0;
@endphp

@if($showBulkSelection)
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="questionBulkToolbar"
        @if(!empty($bulkIdsUrl)) data-bulk-ids-url="{{ $bulkIdsUrl }}" @endif
        data-filtered-total="{{ $questions->total() }}">
        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllQuestionsOnPageBtn">
            <i class="bi bi-check2-square me-1"></i> تحديد الكل في الصفحة
        </button>
        @if($questions->total() > $questions->count())
            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllQuestionsFilteredBtn">
                <i class="bi bi-check2-all me-1"></i> تحديد كل النتائج المفلترة ({{ $questions->total() }})
            </button>
        @endif
        <div id="questionBulkActionsBar" class="d-none d-flex align-items-center flex-wrap gap-2 ms-auto">
            <span class="fw-semibold small"><span id="questionSelectedCount">0</span> سؤال محدد</span>
            @can('question-export')
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exportQuestionsWordModal" data-export-scope="selected">
                    <i class="bi bi-file-earmark-word me-1"></i> تصدير المحدد
                </button>
            @endcan
            @if($canBulkDelete)
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBulkDeleteQuestionsModal">
                    <i class="bi bi-trash me-1"></i> حذف المحدد
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearQuestionSelectionBtn">إلغاء التحديد</button>
        </div>
    </div>
@endif

<div class="row" id="questionBankCards">
    @forelse($questions as $question)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card custom-card question-card question-type-{{ $question->type }} h-100" data-question-id="{{ $question->id }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-2">
                            @if($showBulkSelection)
                                @php
                                    $disableSelection = $canBulkDelete && ! $canExportWord && $question->quizzes_count > 0;
                                @endphp
                                <input type="checkbox"
                                    class="form-check-input question-bulk-checkbox mt-1 flex-shrink-0"
                                    value="{{ $question->id }}"
                                    @if($disableSelection) disabled title="مستخدم في اختبار" aria-label="لا يمكن تحديد سؤال مستخدم في اختبار" @else aria-label="تحديد السؤال" @endif>
                            @endif
                            <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}">
                                {{ $question->type_label }}
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown">
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

                    <h6 class="fw-semibold mb-2">
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

                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="badge question-points-badge">
                            <i class="bi bi-star me-1"></i>{{ $question->default_points }} نقطة
                        </span>
                        <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}">
                            {{ $question->difficulty_label }}
                        </span>
                        @if(!$question->is_active)
                            <span class="badge bg-secondary">غير نشط</span>
                        @endif
                    </div>

                    @php
                        $curriculumRows = $question->curriculumLocations();
                        $extraCurriculumCount = max(0, $curriculumRows->count() - 2);
                    @endphp
                    <div class="border-top pt-2 mt-2 question-card-curriculum">
                        @if($curriculumRows->isEmpty())
                            <small class="text-muted">
                                <i class="bi bi-globe me-1"></i> سؤال عام
                            </small>
                        @else
                            <table class="table table-sm table-borderless mb-0">
                                <thead>
                                    <tr class="text-muted">
                                        <th class="ps-0 py-0 fw-normal">الصف</th>
                                        <th class="py-0 fw-normal">المادة</th>
                                        <th class="pe-0 py-0 fw-normal">الوحدة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($curriculumRows->take(2) as $row)
                                        <tr>
                                            <td class="ps-0 py-1"><span class="fw-semibold">{{ $row['class'] ?: '—' }}</span></td>
                                            <td class="py-1"><span class="fw-semibold">{{ $row['subject'] ?: '—' }}</span></td>
                                            <td class="pe-0 py-1"><span class="text-primary fw-semibold">{{ $row['unit'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($extraCurriculumCount > 0)
                                <small class="text-muted">+{{ $extraCurriculumCount }} موقع إضافي</small>
                            @endif
                        @endif
                    </div>
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
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3">
                        @if(isset($subject))
                            لا توجد أسئلة لهذه المادة حالياً
                        @else
                            لا توجد أسئلة تطابق الفلاتر المحددة
                        @endif
                    </p>
                    @can('question-create')
                        <a href="{{ $createRoute }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> إضافة أول سؤال
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($questions->hasPages())
    <div class="d-flex justify-content-center mt-3" id="questionBankPagination">
        {{ $questions->links() }}
    </div>
@else
    <div id="questionBankPagination"></div>
@endif
