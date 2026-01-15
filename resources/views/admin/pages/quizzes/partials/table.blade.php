@forelse($quizzes as $quiz)
    <tr>
        <td>{{ $quiz->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                @if($quiz->image)
                    <img src="{{ asset('storage/'.$quiz->image) }}" 
                         class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="bg-primary-transparent text-primary rounded d-flex align-items-center justify-content-center me-2" 
                         style="width: 40px; height: 40px;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                @endif
                <div>
                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                       class="fw-semibold text-decoration-none">
                        {{ $quiz->title }}
                    </a>
                    @if($quiz->unit)
                        <small class="text-muted d-block">{{ $quiz->unit->title }}</small>
                    @endif
                </div>
            </div>
        </td>
        <td>
            <span class="badge bg-light text-dark">
                {{ $quiz->subject->name ?? '-' }}
            </span>
        </td>
        <td>
            <span class="badge bg-primary">{{ $quiz->questions_count }} سؤال</span>
        </td>
        <td>
            <span class="badge bg-info">{{ $quiz->attempts_count }} محاولة</span>
        </td>
        <td>
            <span class="text-muted">{{ $quiz->formatted_duration }}</span>
        </td>
        <td>
            @if($quiz->is_published)
                <span class="badge bg-success">منشور</span>
            @else
                <span class="badge bg-secondary">مسودة</span>
            @endif
            @if(!$quiz->is_active)
                <span class="badge bg-warning">معطل</span>
            @endif
        </td>
        <td>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                   class="btn btn-icon btn-info-transparent" title="عرض">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" 
                   class="btn btn-icon btn-success-transparent" title="الأسئلة">
                    <i class="bi bi-list-check"></i>
                </a>
                <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" 
                   class="btn btn-icon btn-primary-transparent" title="تعديل">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="{{ route('admin.quizzes.results', $quiz->id) }}" 
                   class="btn btn-icon btn-warning-transparent" title="النتائج">
                    <i class="bi bi-bar-chart"></i>
                </a>
                @if($quiz->attempts_count == 0)
                    <button type="button" class="btn btn-icon btn-danger-transparent" 
                            data-bs-toggle="modal" data-bs-target="#deleteQuiz{{ $quiz->id }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
    
    {{-- Modal for Delete Confirmation --}}
    @if($quiz->attempts_count == 0)
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
                                data-bs-dismiss="modal"></button>
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
    @endif
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <i class="bi bi-journal-x display-4 text-muted"></i>
            <p class="text-muted mt-3">لا توجد اختبارات حالياً</p>
            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> إنشاء أول اختبار
            </a>
        </td>
    </tr>
@endforelse
