@php
    $showStatus = $showStatus ?? false;
    $enableBulk = $enableBulk ?? true;
    $canBulkApprove = $enableBulk && auth()->user()?->can('quiz-approve-review');
    $indexOffset = isset($quizzes) ? ($quizzes->currentPage() - 1) * $quizzes->perPage() : 0;
    $formId = $formId ?? 'rq-quizzes-bulk-form';
@endphp

@if($canBulkApprove)
    <form id="{{ $formId }}"
          method="POST"
          action="{{ route('admin.review-queue.quizzes.bulk-approve') }}"
          class="rq-bulk-form"
          data-rq-bulk="quizzes">
        @csrf
        <input type="hidden" name="approve_all" value="0" data-rq-approve-all>

        <div class="rq-bulk-toolbar mb-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="submit"
                        class="btn btn-sm btn-success"
                        data-rq-approve-selected
                        disabled>
                    <i class="bi bi-check2-square me-1"></i>
                    قبول المحدد
                    <span class="badge bg-light text-dark ms-1" data-rq-selected-count>0</span>
                </button>
                <button type="submit"
                        class="btn btn-sm btn-outline-success"
                        data-rq-approve-all-btn
                        data-confirm="هل تريد قبول جميع الاختبارات قيد المراجعة؟">
                    <i class="bi bi-check-all me-1"></i>
                    قبول الكل
                </button>
                <span class="small text-muted" data-rq-hint>حدّد اختبارات ثم اضغط قبول المحدد، أو قبول الكل.</span>
            </div>
        </div>
@endif

<div class="rq-table-wrap">
    <div class="table-responsive">
        <table class="table rq-table align-middle">
            <thead>
                <tr>
                    @if($canBulkApprove)
                        <th style="width: 42px;">
                            <input type="checkbox"
                                   class="form-check-input"
                                   data-rq-select-all
                                   title="تحديد الكل في الصفحة"
                                   aria-label="تحديد الكل">
                        </th>
                    @endif
                    <th>#</th>
                    <th>عنوان الاختبار</th>
                    <th>المادة / الصف</th>
                    <th>المعلم</th>
                    <th>تاريخ الإرسال</th>
                    @if($showStatus)<th>الحالة</th>@endif
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quizzes as $quiz)
                    @php
                        $subject = $quiz->subject;
                        $class = $subject?->schoolClass;
                    @endphp
                    <tr>
                        @if($canBulkApprove)
                            <td data-label="تحديد">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="ids[]"
                                       value="{{ $quiz->id }}"
                                       data-rq-item>
                            </td>
                        @endif
                        <td data-label="#">{{ $loop->iteration + $indexOffset }}</td>
                        <td data-label="عنوان الاختبار">
                            <div class="rq-item-title">{{ $quiz->title }}</div>
                        </td>
                        <td data-label="المادة / الصف">
                            @if($subject)
                                <span class="rq-chip rq-chip--subject"><i class="bi bi-journal-bookmark"></i> {{ $subject->name }}</span>
                                @if($class)
                                    <span class="rq-chip rq-chip--class ms-1"><i class="bi bi-building"></i> {{ $class->name }}</span>
                                @endif
                            @else
                                <span class="text-muted small">غير محدد</span>
                            @endif
                        </td>
                        <td data-label="المعلم">
                            <span class="small">{{ $quiz->creator->name ?? '—' }}</span>
                        </td>
                        <td data-label="تاريخ الإرسال">
                            @if($quiz->submitted_for_review_at)
                                <span class="small">{{ \Carbon\Carbon::parse($quiz->submitted_for_review_at)->format('Y-m-d H:i') }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        @if($showStatus)
                            <td data-label="الحالة"><span class="rq-status">قيد المراجعة</span></td>
                        @endif
                        <td data-label="إجراءات" class="text-center">
                            <div class="row-action-bar justify-content-center">
                                @can('quiz-show')
                                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}"
                                       class="row-action-btn row-action-btn--primary" title="عرض ومراجعة">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @if($subject)
                                    <a href="{{ route('admin.subjects.show', $subject->id) }}"
                                       class="row-action-btn row-action-btn--info" title="صفحة المادة">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($canBulkApprove)
    </form>
@endif
