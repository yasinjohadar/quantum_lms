@extends('admin.layouts.master')

@section('page-title')
    إدارة أسئلة الاختبار
@stop

@section('css')
<style>
    .question-item {
        cursor: grab;
        transition: all 0.2s ease;
    }
    .question-item:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
    }
    .question-item.dragging {
        opacity: 0.5;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة أسئلة: {{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">إدارة الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary fs-6 d-flex align-items-center" id="header-total-points">
                        <span id="header-questions-count">{{ $quiz->questions->count() }}</span> سؤال | <span id="header-points-value">{{ $quiz->total_points }}</span> درجة
                    </span>
                </div>
            </div>
            <!-- Page Header Close -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- أسئلة الاختبار الحالية --}}
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ol me-2"></i>
                        أسئلة الاختبار ({{ $quiz->questions->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">لم يتم إضافة أسئلة بعد</p>
                            <p class="text-muted small">اختر أسئلة من بنك الأسئلة على اليسار</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush" id="quizQuestions">
                            @foreach($quiz->questions as $index => $question)
                                <div class="list-group-item question-item" data-id="{{ $question->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start flex-grow-1">
                                            <i class="bi bi-grip-vertical text-muted me-2 mt-1" style="cursor: grab;"></i>
                                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                            <div class="flex-grow-1">
                                                <span class="badge bg-{{ $question->type_color }}-transparent text-{{ $question->type_color }} mb-1" style="font-size: 0.65rem;">
                                                    <i class="bi {{ $question->type_icon }}"></i>
                                                    {{ $question->type_name }}
                                                </span>
                                                <p class="mb-1 small">{{ Str::limit($question->title, 80) }}</p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input type="number" 
                                                               class="form-control form-control-sm question-points-input" 
                                                               value="{{ $question->pivot->points }}" 
                                                               data-question-id="{{ $question->id }}"
                                                               data-quiz-id="{{ $quiz->id }}"
                                                               style="width: 100px;" 
                                                               step="0.5" 
                                                               min="0"
                                                               max="1000">
                                                        <span class="text-muted small">درجة</span>
                                                        <span class="points-update-status ms-1" style="display: none;">
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-sm btn-icon btn-danger-transparent remove-question-btn" 
                                                title="إزالة من الاختبار"
                                                data-question-id="{{ $question->id }}"
                                                data-quiz-id="{{ $quiz->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- بنك الأسئلة المتاحة --}}
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-database me-2"></i>
                        بنك الأسئلة المتاحة
                    </h6>
                </div>
                <div class="card-body">
                    {{-- فلتر --}}
                    <form action="{{ route('admin.quizzes.questions', $quiz->id) }}" method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="class_id" id="classFilter" class="form-select form-select-sm">
                                    <option value="">كل الصفوف</option>
                                    @foreach($classes ?? [] as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }} - {{ $class->stage?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="subject_id" id="subjectFilter" class="form-select form-select-sm" {{ !request('class_id') ? 'disabled' : '' }}>
                                    <option value="">اختر المادة</option>
                                    @if(request('class_id'))
                                        @foreach($subjects ?? [] as $subject)
                                            @if($subject->class_id == request('class_id'))
                                                <option value="{{ $subject->id }}" 
                                                        data-class-id="{{ $subject->class_id }}"
                                                        {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">كل الأنواع</option>
                                    @foreach(\App\Models\Question::TYPES as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="difficulty" class="form-select form-select-sm">
                                    <option value="">كل المستويات</option>
                                    @foreach(\App\Models\Question::DIFFICULTIES as $key => $value)
                                        <option value="{{ $key }}" {{ request('difficulty') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($availableQuestions->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-search display-6 text-muted"></i>
                            <p class="text-muted mt-2">لا توجد أسئلة متاحة</p>
                            <a href="{{ route('admin.questions.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> إنشاء سؤال جديد
                            </a>
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
                                            <p class="mb-1 small">{{ Str::limit($question->title, 60) }}</p>
                                            <small class="text-muted">{{ $question->default_points }} درجة</small>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-sm btn-success-transparent add-question-btn" 
                                                title="إضافة للاختبار"
                                                data-question-id="{{ $question->id }}"
                                                data-quiz-id="{{ $quiz->id }}"
                                                data-points="{{ $question->default_points }}">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            {{ $availableQuestions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal تأكيد حذف السؤال --}}
    <div class="modal fade" id="confirmRemoveQuestionModal" tabindex="-1" aria-labelledby="confirmRemoveQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="modal-title mb-3" id="confirmRemoveQuestionModalLabel">تأكيد الإزالة</h5>
                    <p class="text-muted mb-0">هل تريد إزالة هذا السؤال من الاختبار؟</p>
                    <p class="text-muted small mt-2">لا يمكن التراجع عن هذا الإجراء</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                        <i class="bi bi-trash me-1"></i>حذف السؤال
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- أزرار التحكم --}}
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">إجمالي الأسئلة:</span>
                        <span class="fw-bold" id="total-questions-count">{{ $quiz->questions->count() }}</span>
                        <span class="mx-2">|</span>
                        <span class="text-muted">إجمالي الدرجات:</span>
                        <span class="fw-bold" id="total-points-display">{{ $quiz->total_points }}</span>
                    </div>
                    <div class="btn-list">
                        <a href="{{ route('admin.ai.question-generations.create-advanced', ['quiz_id' => $quiz->id, 'subject_id' => $quiz->subject_id]) }}" class="btn btn-outline-info">
                            <i class="fas fa-magic me-1"></i> توليد أسئلة بالذكاء الاصطناعي
                        </a>
                        <a href="{{ route('admin.questions.create') }}" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i> إنشاء سؤال جديد
                        </a>
                        <a href="{{ route('admin.quizzes.show', $quiz->id) }}" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> انتهيت
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quizQuestions = document.getElementById('quizQuestions');
    
    if (quizQuestions) {
        new Sortable(quizQuestions, {
            animation: 150,
            handle: '.bi-grip-vertical',
            ghostClass: 'dragging',
            onEnd: function(evt) {
                const items = quizQuestions.querySelectorAll('.question-item');
                const order = Array.from(items).map(item => item.dataset.id);
                
                fetch('{{ route("admin.quizzes.reorder-questions", $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث الأرقام
                        items.forEach((item, index) => {
                            item.querySelector('.badge.bg-secondary').textContent = index + 1;
                        });
                    }
                });
            }
        });
    }

    // تعريف CSRF token مرة واحدة لاستخدامه في جميع الـ Ajax requests
    const csrfToken = '{{ csrf_token() }}';

    // ربط فلتر الصف بالمواد (Dependent Dropdown)
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');

    console.log('Initializing class-subject filter');
    console.log('Class filter found:', !!classFilter);
    console.log('Subject filter found:', !!subjectFilter);

    if (classFilter && subjectFilter) {
        // حفظ جميع خيارات المواد الأصلية من الـ HTML
        const allSubjectOptions = [];
        Array.from(subjectFilter.options).forEach(option => {
            if (option.value) {
                allSubjectOptions.push({
                    value: option.value,
                    text: option.textContent,
                    classId: option.getAttribute('data-class-id'),
                    selected: option.selected
                });
            }
        });

        // دالة لجلب المواد حسب الصف
        function loadSubjectsByClass(classId, preserveSelected = false) {
            const selectedSubjectId = preserveSelected ? subjectFilter.value : null;
            
            if (!classId || classId === '') {
                // إذا لم يتم اختيار صف، تعطيل select المواد وإفراغه
                subjectFilter.disabled = true;
                subjectFilter.innerHTML = '<option value="">اختر المادة</option>';
            } else {
                // جلب المواد الخاصة بالصف المحدد عبر Ajax
                const route = '{{ route("admin.quizzes.get-subjects-by-class") }}';
                
                console.log('Loading subjects for class:', classId);
                console.log('Route:', route);
                
                // إظهار loading
                subjectFilter.disabled = true;
                subjectFilter.innerHTML = '<option value="">جاري التحميل...</option>';

                fetch(`${route}?class_id=${encodeURIComponent(classId)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Subjects data received:', data);
                    subjectFilter.disabled = false;
                    subjectFilter.innerHTML = '<option value="">اختر المادة</option>';
                    
                    if (data.success && data.data && Array.isArray(data.data)) {
                        if (data.data.length === 0) {
                            subjectFilter.innerHTML = '<option value="">لا توجد مواد لهذا الصف</option>';
                        } else {
                            data.data.forEach(subject => {
                                const option = document.createElement('option');
                                option.value = subject.id;
                                option.textContent = subject.name;
                                option.setAttribute('data-class-id', subject.class_id || '');
                                
                                // إذا كان هذا هو المادة المحددة مسبقاً، حدده
                                if (preserveSelected && selectedSubjectId && selectedSubjectId == subject.id) {
                                    option.selected = true;
                                }
                                
                                subjectFilter.appendChild(option);
                            });
                            console.log(`Loaded ${data.data.length} subjects`);
                        }
                    } else {
                        console.warn('No subjects found or invalid response:', data);
                        subjectFilter.innerHTML = '<option value="">لا توجد مواد</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects:', error);
                    subjectFilter.disabled = false;
                    subjectFilter.innerHTML = '<option value="">خطأ في التحميل</option>';
                });
            }
        }

        // إضافة event listener على select الصف
        classFilter.addEventListener('change', function() {
            const classId = this.value;
            console.log('Class changed to:', classId);
            // إعادة تعيين المادة عند تغيير الصف
            loadSubjectsByClass(classId, false);
        });

        // عند تحميل الصفحة، فلترة المواد حسب الصف المحدد (إن وجد)
        const selectedClassId = classFilter.value;
        if (selectedClassId) {
            // تحميل المواد الخاصة بالصف المحدد فوراً عبر Ajax
            // هذا يضمن أن المواد المحدثة من قاعدة البيانات يتم جلبها
            loadSubjectsByClass(selectedClassId, true); // الحفاظ على المادة المحددة إن كانت صحيحة
        } else {
            // إذا لم يكن هناك صف محدد، تعطيل select المواد
            subjectFilter.disabled = true;
            subjectFilter.innerHTML = '<option value="">اختر المادة</option>';
        }
    }

    // تحديث درجات الأسئلة عبر Ajax
    const pointsInputs = document.querySelectorAll('.question-points-input');
    const updateTimeout = {};

    pointsInputs.forEach(input => {
        // حفظ القيمة الأصلية
        let originalValue = input.value;

        input.addEventListener('input', function() {
            const questionId = this.dataset.questionId;
            const quizId = this.dataset.quizId;
            const newValue = parseFloat(this.value);

            // التحقق من صحة القيمة
            if (isNaN(newValue) || newValue < 0 || newValue > 1000) {
                this.classList.add('is-invalid');
                return;
            } else {
                this.classList.remove('is-invalid');
            }

            // إخفاء رسالة النجاح السابقة
            const statusIcon = this.closest('.d-flex').querySelector('.points-update-status');
            if (statusIcon) {
                statusIcon.style.display = 'none';
            }

            // إلغاء timeout السابق إن وجد
            if (updateTimeout[questionId]) {
                clearTimeout(updateTimeout[questionId]);
            }

            // إضافة loading indicator
            this.classList.add('updating');
            if (statusIcon) {
                statusIcon.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm text-primary"></i>';
                statusIcon.style.display = 'inline-block';
            }

            // debounce: انتظار 500ms قبل الإرسال
            updateTimeout[questionId] = setTimeout(() => {
                const route = '{{ route("admin.quizzes.update-question-points", [":quizId", ":questionId"]) }}'
                    .replace(':quizId', quizId)
                    .replace(':questionId', questionId);

                fetch(route, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ points: newValue })
                })
                .then(response => response.json())
                .then(data => {
                    this.classList.remove('updating');
                    
                    if (data.success) {
                        // عرض رسالة النجاح
                        if (statusIcon) {
                            statusIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                            statusIcon.style.display = 'inline-block';
                            
                            // إخفاء الرسالة بعد 2 ثانية
                            setTimeout(() => {
                                statusIcon.style.display = 'none';
                            }, 2000);
                        }

                        // تحديث إجمالي الدرجات
                        if (data.total_points !== undefined) {
                            updateTotalPoints(data.total_points);
                        }

                        // إرسال تحديث إلى صفحة عرض الاختبار عبر BroadcastChannel
                        try {
                            const channel = new BroadcastChannel('quiz-question-updates');
                            channel.postMessage({
                                type: 'points-updated',
                                quizId: quizId,
                                questionId: questionId,
                                points: newValue,
                                totalPoints: data.total_points
                            });
                            
                            // Fallback: استخدام localStorage
                            localStorage.setItem('quiz-question-updated', JSON.stringify({
                                quizId: quizId,
                                questionId: questionId,
                                points: newValue,
                                totalPoints: data.total_points
                            }));
                            // إزالة العنصر بعد إرساله لضمان trigger الـ event في المرة القادمة
                            setTimeout(() => {
                                localStorage.removeItem('quiz-question-updated');
                            }, 100);
                        } catch (err) {
                            console.warn('BroadcastChannel not supported:', err);
                        }

                        originalValue = newValue;
                    } else {
                        // في حالة الخطأ، استعادة القيمة الأصلية
                        this.value = originalValue;
                        if (statusIcon) {
                            statusIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                            statusIcon.style.display = 'inline-block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating points:', error);
                    this.classList.remove('updating');
                    this.value = originalValue;
                    
                    if (statusIcon) {
                        statusIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                        statusIcon.style.display = 'inline-block';
                    }
                });
            }, 500);
        });

        // عند فقدان التركيز، التأكد من حفظ القيمة
        input.addEventListener('blur', function() {
            if (updateTimeout[this.dataset.questionId]) {
                clearTimeout(updateTimeout[this.dataset.questionId]);
                // إرسال فوري عند فقدان التركيز
                this.dispatchEvent(new Event('input'));
            }
        });
    });

    // دالة تحديث إجمالي الدرجات
    function updateTotalPoints(totalPoints) {
        const formattedPoints = parseFloat(totalPoints).toFixed(2);
        
        // تحديث في الـ header
        const headerPointsValue = document.getElementById('header-points-value');
        if (headerPointsValue) {
            headerPointsValue.textContent = formattedPoints;
        }

        // تحديث في الـ footer
        const footerTotalPoints = document.getElementById('total-points-display');
        if (footerTotalPoints) {
            footerTotalPoints.textContent = formattedPoints;
        }
    }

    // ============================================
    // Ajax لإضافة وحذف الأسئلة
    // ============================================

    // إضافة سؤال للاختبار
    console.log('Quiz question management initialized');
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-question-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-question-btn');
            const questionId = btn.dataset.questionId;
            const quizId = btn.dataset.quizId;
            const points = btn.dataset.points || 10;

            console.log('Adding question:', { questionId, quizId, points });

            // تعطيل الزر أثناء المعالجة
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i>';

            fetch(`/admin/quizzes/${quizId}/add-question`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    points: points
                })
            })
            .then(response => {
                console.log('Add question response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Add question response data:', data);
                if (data.success) {
                    // إضافة السؤال إلى قائمة أسئلة الاختبار
                    addQuestionToQuizList(data.question, data.statistics);
                    
                    // إزالة السؤال من قائمة الأسئلة المتاحة
                    const questionItem = btn.closest('.list-group-item');
                    if (questionItem) {
                        questionItem.style.transition = 'opacity 0.3s';
                        questionItem.style.opacity = '0';
                        setTimeout(() => {
                            questionItem.remove();
                        }, 300);
                    }

                    // تحديث الإحصائيات
                    updateStatistics(data.statistics);
                    
                    // إظهار رسالة نجاح
                    showNotification('success', data.message);
                } else {
                    showNotification('error', data.message || 'حدث خطأ أثناء إضافة السؤال');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-plus-lg"></i>';
                }
            })
            .catch(error => {
                console.error('Error adding question:', error);
                showNotification('error', 'حدث خطأ أثناء إضافة السؤال');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus-lg"></i>';
            });
        }

        // حذف سؤال من الاختبار
        if (e.target.closest('.remove-question-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.remove-question-btn');
            const questionId = btn.dataset.questionId;
            const quizId = btn.dataset.quizId;

            console.log('Removing question:', { questionId, quizId });

            // حفظ البيانات في الـ modal
            const modal = document.getElementById('confirmRemoveQuestionModal');
            const confirmBtn = document.getElementById('confirmRemoveBtn');
            
            // إزالة event listeners السابقة
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // إضافة event listener جديد
            newConfirmBtn.addEventListener('click', function() {
                // إغلاق الـ modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }

                // تعطيل الزر أثناء المعالجة
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i>';

                fetch(`/admin/quizzes/${quizId}/remove-question/${questionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Remove question response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Remove question response data:', data);
                    if (data.success) {
                        // إزالة السؤال من قائمة أسئلة الاختبار
                        const questionItem = btn.closest('.question-item');
                        if (questionItem) {
                            questionItem.style.transition = 'opacity 0.3s';
                            questionItem.style.opacity = '0';
                            setTimeout(() => {
                                questionItem.remove();
                                // إعادة ترقيم الأسئلة
                                renumberQuestions();
                            }, 300);
                        }

                        // إضافة السؤال مرة أخرى إلى قائمة الأسئلة المتاحة (إذا كان يطابق الفلاتر)
                        addQuestionToAvailableList(data.question);

                        // تحديث الإحصائيات
                        updateStatistics(data.statistics);
                        
                        // إظهار رسالة نجاح
                        showNotification('success', data.message);
                    } else {
                        showNotification('error', data.message || 'حدث خطأ أثناء إزالة السؤال');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error removing question:', error);
                    showNotification('error', 'حدث خطأ أثناء إزالة السؤال');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-x-lg"></i>';
                });
            });

            // فتح الـ modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    });

    // دالة لإضافة سؤال إلى قائمة أسئلة الاختبار
    function addQuestionToQuizList(question, statistics) {
        const quizQuestionsList = document.getElementById('quizQuestions');
        if (!quizQuestionsList) return;

        // التحقق من وجود رسالة "لم يتم إضافة أسئلة بعد"
        const emptyMessage = quizQuestionsList.querySelector('.text-center');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        // إنشاء عنصر السؤال الجديد
        const questionItem = document.createElement('div');
        questionItem.className = 'list-group-item question-item';
        questionItem.setAttribute('data-id', question.id);
        
        const questionCount = quizQuestionsList.querySelectorAll('.question-item').length + 1;
        
        questionItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-start flex-grow-1">
                    <i class="bi bi-grip-vertical text-muted me-2 mt-1" style="cursor: grab;"></i>
                    <span class="badge bg-secondary me-2">${questionCount}</span>
                    <div class="flex-grow-1">
                        <span class="badge bg-${question.type_color}-transparent text-${question.type_color} mb-1" style="font-size: 0.65rem;">
                            <i class="bi ${question.type_icon}"></i>
                            ${question.type_name}
                        </span>
                        <p class="mb-1 small">${question.title.length > 80 ? question.title.substring(0, 80) + '...' : question.title}</p>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <input type="number" 
                                       class="form-control form-control-sm question-points-input" 
                                       value="${question.points}" 
                                       data-question-id="${question.id}"
                                       data-quiz-id="{{ $quiz->id }}"
                                       style="width: 100px;" 
                                       step="0.5" 
                                       min="0"
                                       max="1000">
                                <span class="text-muted small">درجة</span>
                                <span class="points-update-status ms-1" style="display: none;">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" 
                        class="btn btn-sm btn-icon btn-danger-transparent remove-question-btn" 
                        title="إزالة من الاختبار"
                        data-question-id="${question.id}"
                        data-quiz-id="{{ $quiz->id }}">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;

        // إضافة السؤال إلى القائمة
        quizQuestionsList.appendChild(questionItem);

        // إعادة تهيئة SortableJS
        if (window.quizSortable) {
            window.quizSortable.destroy();
        }
        initSortable();

        // إعادة ربط event listener على input النقاط
        const newInput = questionItem.querySelector('.question-points-input');
        if (newInput) {
            setupPointsInput(newInput);
        }
    }

    // دالة لإضافة سؤال إلى قائمة الأسئلة المتاحة
    function addQuestionToAvailableList(question) {
        const availableQuestionsList = document.querySelector('.available-questions-list');
        if (!availableQuestionsList) return;

        // إنشاء عنصر السؤال الجديد
        const questionItem = document.createElement('div');
        questionItem.className = 'list-group-item';
        
        const difficultyBadge = getDifficultyBadge(question.difficulty, question.difficulty_name);
        
        questionItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-start flex-grow-1">
                    <i class="bi bi-plus-circle text-success me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex gap-2 mb-1">
                            <span class="badge bg-${question.type_color}-transparent text-${question.type_color}" style="font-size: 0.65rem;">
                                <i class="bi ${question.type_icon}"></i>
                                ${question.type_name}
                            </span>
                            ${difficultyBadge}
                        </div>
                        <p class="mb-1 small">${question.title.length > 60 ? question.title.substring(0, 60) + '...' : question.title}</p>
                        <small class="text-muted">${question.default_points} درجة</small>
                    </div>
                </div>
                <button type="button" 
                        class="btn btn-sm btn-success-transparent add-question-btn" 
                        title="إضافة للاختبار"
                        data-question-id="${question.id}"
                        data-quiz-id="{{ $quiz->id }}"
                        data-points="${question.default_points}">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        `;

        // إضافة السؤال في البداية
        availableQuestionsList.insertBefore(questionItem, availableQuestionsList.firstChild);
    }

    // دالة للحصول على badge الصعوبة
    function getDifficultyBadge(difficulty, difficultyName) {
        const badges = {
            'easy': '<span class="badge bg-success-transparent text-success" style="font-size: 0.65rem;">سهل</span>',
            'medium': '<span class="badge bg-warning-transparent text-warning" style="font-size: 0.65rem;">متوسط</span>',
            'hard': '<span class="badge bg-danger-transparent text-danger" style="font-size: 0.65rem;">صعب</span>'
        };
        return badges[difficulty] || `<span class="badge bg-secondary-transparent text-secondary" style="font-size: 0.65rem;">${difficultyName}</span>`;
    }

    // دالة لإعادة ترقيم الأسئلة
    function renumberQuestions() {
        const quizQuestionsList = document.getElementById('quizQuestions');
        if (!quizQuestionsList) return;

        const questionItems = quizQuestionsList.querySelectorAll('.question-item');
        questionItems.forEach((item, index) => {
            const badge = item.querySelector('.badge.bg-secondary');
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }

    // دالة لتحديث الإحصائيات
    function updateStatistics(statistics) {
        // تحديث عدد الأسئلة
        const headerQuestionsCount = document.getElementById('header-questions-count');
        if (headerQuestionsCount) {
            headerQuestionsCount.textContent = statistics.total_questions;
        }

        const footerQuestionsCount = document.getElementById('total-questions-count');
        if (footerQuestionsCount) {
            footerQuestionsCount.textContent = statistics.total_questions;
        }

        // تحديث إجمالي الدرجات
        updateTotalPoints(statistics.total_points);

        // تحديث عنوان البطاقة
        const cardHeader = document.querySelector('.card-header h6');
        if (cardHeader) {
            cardHeader.innerHTML = `<i class="bi bi-list-ol me-2"></i>أسئلة الاختبار (${statistics.total_questions})`;
        }
    }

    // دالة لإظهار الإشعارات
    function showNotification(type, message) {
        // إنشاء عنصر الإشعار
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // إضافة الإشعار في بداية الصفحة
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // إزالة الإشعار تلقائياً بعد 5 ثوان
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }

    // دالة لإعداد input النقاط
    function setupPointsInput(input) {
        let originalValue = input.value;
        const questionId = input.dataset.questionId;

        input.addEventListener('input', function() {
            const quizId = this.dataset.quizId;
            const newValue = parseFloat(this.value);

            if (isNaN(newValue) || newValue < 0 || newValue > 1000) {
                this.classList.add('is-invalid');
                return;
            } else {
                this.classList.remove('is-invalid');
            }

            const statusIcon = this.closest('.d-flex').querySelector('.points-update-status');
            if (statusIcon) {
                statusIcon.style.display = 'none';
            }

            if (updateTimeout[questionId]) {
                clearTimeout(updateTimeout[questionId]);
            }

            this.classList.add('updating');
            if (statusIcon) {
                statusIcon.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm text-primary"></i>';
                statusIcon.style.display = 'inline-block';
            }

            updateTimeout[questionId] = setTimeout(() => {
                const route = '{{ route("admin.quizzes.update-question-points", [":quizId", ":questionId"]) }}'
                    .replace(':quizId', quizId)
                    .replace(':questionId', questionId);

                fetch(route, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ points: newValue })
                })
                .then(response => response.json())
                .then(data => {
                    this.classList.remove('updating');
                    
                    if (data.success) {
                        if (statusIcon) {
                            statusIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                            statusIcon.style.display = 'inline-block';
                            setTimeout(() => {
                                statusIcon.style.display = 'none';
                            }, 2000);
                        }
                        if (data.total_points !== undefined) {
                            updateTotalPoints(data.total_points);
                        }
                        originalValue = newValue;
                    } else {
                        this.value = originalValue;
                        if (statusIcon) {
                            statusIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                            statusIcon.style.display = 'inline-block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating points:', error);
                    this.classList.remove('updating');
                    this.value = originalValue;
                    if (statusIcon) {
                        statusIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                        statusIcon.style.display = 'inline-block';
                    }
                });
            }, 500);
        });
    }

    // دالة لإعادة تهيئة SortableJS
    function initSortable() {
        const quizQuestions = document.getElementById('quizQuestions');
        if (quizQuestions) {
            window.quizSortable = new Sortable(quizQuestions, {
                animation: 150,
                handle: '.bi-grip-vertical',
                ghostClass: 'dragging',
                onEnd: function(evt) {
                    const items = quizQuestions.querySelectorAll('.question-item');
                    const order = Array.from(items).map(item => item.dataset.id);
                    
                    fetch('{{ route("admin.quizzes.reorder-questions", $quiz->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renumberQuestions();
                        }
                    });
                }
            });
        }
    }

    // تهيئة SortableJS عند تحميل الصفحة (بالفعل مُهيأة في بداية الكود)
});
</script>
<style>
.question-points-input.updating {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
.spinner-border-sm {
    width: 0.875rem;
    height: 0.875rem;
    border-width: 0.125em;
}
</style>
@stop

