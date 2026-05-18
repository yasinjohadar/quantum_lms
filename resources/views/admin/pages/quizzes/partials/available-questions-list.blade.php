@if($availableQuestions->isEmpty())
    <div class="text-center py-4" id="availableQuestionsEmpty">
        <i class="bi bi-search display-6 text-muted"></i>
        <p class="text-muted mt-2">لا توجد أسئلة متاحة</p>
        @can('question-create')
        <a href="{{ route('admin.questions.create', ['quiz_id' => $quiz->id]) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> إنشاء سؤال جديد
        </a>
        @endcan
    </div>
@else
    <div class="list-group list-group-flush available-questions-list" style="max-height: 500px; overflow-y: auto;">
        @foreach($availableQuestions as $question)
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }}" style="font-size: 0.65rem;">
                                <i class="bi {{ $question->type_icon }}"></i>
                                {{ $question->type_name }}
                            </span>
                            <span class="badge bg-{{ $question->difficulty_color }}-transparent text-{{ $question->difficulty_color }}" style="font-size: 0.65rem;">
                                {{ $question->difficulty_name }}
                            </span>
                        </div>
                        <p class="mb-1 small">{{ Str::limit(strip_tags($question->title), 60) }}</p>
                        @php
                            $inlineImages = [];
                            if (!empty($question->title)) {
                                preg_match_all('/<img[^>]+src="([^"]+)"/i', $question->title, $matches);
                                $inlineImages = $matches[1] ?? [];
                            }
                            if (!empty($question->image)) {
                                $inlineImages[] = media_public_url($question->image);
                            }
                        @endphp
                        @if(!empty($inlineImages))
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach(array_slice($inlineImages, 0, 3) as $imgSrc)
                            <img src="{{ $imgSrc }}"
                                 alt="صورة السؤال"
                                 class="question-image-thumb"
                                 loading="lazy"
                                 data-full-image="{{ $imgSrc }}"
                                 onerror="this.style.display='none';">
                            @endforeach
                            @if(count($inlineImages) > 3)
                            <span class="badge bg-secondary align-self-center">+{{ count($inlineImages) - 3 }}</span>
                            @endif
                        </div>
                        @endif
                        <small class="text-muted">{{ $question->default_points }} درجة</small>
                    </div>
                    @can('quiz-add-question')
                    <button type="button"
                            class="btn btn-sm btn-success-transparent add-question-btn"
                            title="إضافة للاختبار"
                            data-question-id="{{ $question->id }}"
                            data-quiz-id="{{ $quiz->id }}"
                            data-points="{{ $question->default_points }}">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 available-questions-pagination">
        {{ $availableQuestions->links() }}
    </div>
@endif
