@forelse($quizzes as $quiz)
    @php
        $reviewUiClass = match($quiz->review_status) {
            'approved' => 'ui-quiz-review--approved',
            'pending_review' => 'ui-quiz-review--pending',
            'rejected' => 'ui-quiz-review--rejected',
            default => 'ui-quiz-review--draft',
        };
    @endphp
    <tr>
        <td class="text-muted">{{ $quiz->id }}</td>
        <td>
            <div class="ui-quiz-cell">
                @if($quiz->image)
                    <img src="{{ media_public_url($quiz->image) }}"
                         alt="{{ $quiz->title }}"
                         class="ui-quiz-thumb">
                @else
                    <div class="ui-quiz-thumb ui-quiz-thumb--placeholder">
                        <i class="bi bi-journal-check"></i>
                    </div>
                @endif
                <div class="min-width-0">
                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="ui-quiz-title">
                        {{ $quiz->title }}
                    </a>
                    @if($quiz->unit)
                        <span class="ui-quiz-meta">{{ $quiz->unit->title }}</span>
                    @endif
                </div>
            </div>
        </td>
        <td class="quizzes-col-subject">
            <span class="ui-quiz-subject-pill">{{ $quiz->subject->name ?? '—' }}</span>
        </td>
        <td>
            <span class="ui-quiz-count ui-quiz-count--questions">
                <i class="bi bi-question-circle"></i> {{ $quiz->questions_count }} سؤال
            </span>
        </td>
        <td class="quizzes-col-attempts">
            <span class="ui-quiz-count ui-quiz-count--attempts">
                <i class="bi bi-people"></i> {{ $quiz->attempts_count }} محاولة
            </span>
        </td>
        <td class="quizzes-col-duration">
            <span class="ui-quiz-duration">{{ $quiz->formatted_duration }}</span>
        </td>
        <td>
            @if($quiz->is_published)
                <span class="ui-quiz-status ui-quiz-status--published"><i class="bi bi-check-circle"></i> منشور</span>
            @else
                <span class="ui-quiz-status ui-quiz-status--draft"><i class="bi bi-file-earmark"></i> مسودة</span>
            @endif
            @if(!$quiz->is_active)
                <span class="ui-quiz-status ui-quiz-status--inactive"><i class="bi bi-pause-circle"></i> معطل</span>
            @endif
        </td>
        <td class="quizzes-col-review">
            <span class="ui-quiz-review {{ $reviewUiClass }}">{{ $quiz->review_status_name }}</span>
        </td>
        <td>
            <div class="row-action-bar">
                @can('quiz-show')
                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}"
                       class="row-action-btn row-action-btn--info"
                       title="عرض">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('quiz-questions')
                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}"
                       class="row-action-btn row-action-btn--success"
                       title="الأسئلة">
                        <i class="bi bi-list-check"></i>
                    </a>
                @endcan
                @can('quiz-edit')
                    <a href="{{ route('admin.quizzes.edit', $quiz->id) }}"
                       class="row-action-btn row-action-btn--primary"
                       title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('quiz-results')
                    <a href="{{ route('admin.quizzes.results', $quiz->id) }}"
                       class="row-action-btn row-action-btn--warning"
                       title="النتائج">
                        <i class="bi bi-bar-chart"></i>
                    </a>
                @endcan
                @can('quiz-delete')
                    <button type="button"
                            class="row-action-btn row-action-btn--danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteQuiz{{ $quiz->id }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>

    @can('quiz-delete')
        <div class="modal fade" id="deleteQuiz{{ $quiz->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="border-0 text-center pt-4 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <span class="me-2 fs-4 text-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </span>
                            <h5 class="modal-title mb-0 fw-bold">حذف الاختبار</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 start-0 m-3"
                                data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="text-center mt-2">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger text-white shadow-sm"
                             style="width:80px;height:80px;">
                            <i class="bi bi-trash fs-2"></i>
                        </div>
                    </div>
                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center pt-0 pb-3 px-4">
                            <p class="mb-1 text-muted">هل أنت متأكد من حذف الاختبار:</p>
                            <p class="fw-bold mb-1">{{ $quiz->title }}</p>
                            @if($quiz->attempts_count > 0)
                                <p class="text-danger small mb-0 mt-2">
                                    سيتم حذف {{ $quiz->attempts_count }} محاولة طالب مرتبطة بهذا الاختبار. لا يمكن التراجع.
                                </p>
                            @endif
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2"
                                    data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-trash me-1"></i> حذف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@empty
    <tr>
        <td colspan="9">
            <div class="quizzes-index-empty">
                <i class="bi bi-journal-x"></i>
                <p class="mb-3">لا توجد اختبارات حالياً</p>
                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار
                </a>
            </div>
        </td>
    </tr>
@endforelse
