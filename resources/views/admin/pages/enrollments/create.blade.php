@extends('admin.layouts.master')

@section('page-title')
    إضافة انضمامات جديدة
@stop

@section('css')
<style>
    .student-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .student-card:hover {
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
    }
    .student-card.selected {
        border-color: #4f46e5;
        background: #eef2ff;
    }
    .subject-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .subject-card:hover {
        border-color: #10b981;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
    }
    .subject-card.selected {
        border-color: #10b981;
        background: #ecfdf5;
    }
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }
    .preview-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-top: 24px;
    }
    .counter-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        background: #4f46e5;
        color: white;
    }
    .loading-spinner {
        display: none;
    }
    .loading-spinner.active {
        display: inline-block;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة انضمامات جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة جديدة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
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

            <form action="{{ route('admin.enrollments.store') }}" method="POST" id="enrollmentForm">
                @csrf

                <div class="row">
                    <!-- قسم البحث والفلترة -->
                    <div class="col-xl-12">
                        <div class="card custom-card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-funnel me-2"></i> البحث والفلترة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="filter-section">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">بحث عن الطلاب</label>
                                            <input type="text" 
                                                   id="searchInput" 
                                                   class="form-control" 
                                                   placeholder="الاسم، البريد، الهاتف، أو المعرف">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">المرحلة</label>
                                            <select id="stageFilter" class="form-select">
                                                <option value="">كل المراحل</option>
                                                @foreach($stages as $stage)
                                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">الصف</label>
                                            <select id="classFilter" class="form-select">
                                                <option value="">كل الصفوف</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" data-stage="{{ $class->stage_id }}">
                                                        {{ $class->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">حالة المستخدم</label>
                                            <select id="statusFilter" class="form-select">
                                                <option value="">كل الحالات</option>
                                                <option value="1">نشط</option>
                                                <option value="0">غير نشط</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-flex gap-2">
                                                <button type="button" id="searchBtn" class="btn btn-primary w-100">
                                                    <i class="bi bi-search me-1"></i> بحث
                                                </button>
                                                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary w-100">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="text-muted">
                                                    <span id="resultsCount">0</span> طالب
                                                </span>
                                                <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                                    <span class="visually-hidden">جاري التحميل...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم اختيار الطلاب -->
                    <div class="col-xl-6">
                        <div class="card custom-card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-people me-2"></i> اختيار الطلاب
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" id="selectAllStudents" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check-all me-1"></i> تحديد الكل
                                    </button>
                                    <button type="button" id="deselectAllStudents" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x-square me-1"></i> إلغاء الكل
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <span class="badge bg-primary">
                                        تم تحديد <span id="selectedStudentsCount">0</span> طالب
                                    </span>
                                </div>
                                <div id="studentsList" style="max-height: 500px; overflow-y: auto;">
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-search display-4 d-block mb-3"></i>
                                        <p>استخدم البحث والفلترة للعثور على الطلاب</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم اختيار المواد -->
                    <div class="col-xl-6">
                        <div class="card custom-card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-book me-2"></i> اختيار المواد
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" id="selectAllSubjects" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-all me-1"></i> تحديد الكل
                                    </button>
                                    <button type="button" id="deselectAllSubjects" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x-square me-1"></i> إلغاء الكل
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">اختر الصف أولاً</label>
                                    <select id="subjectClassFilter" class="form-select">
                                        <option value="">كل الصفوف</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">
                                                {{ $class->name }} - {{ $class->stage->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <span class="badge bg-success">
                                        تم تحديد <span id="selectedSubjectsCount">0</span> مادة
                                    </span>
                                </div>
                                <div id="subjectsList" style="max-height: 500px; overflow-y: auto;">
                                    @foreach($subjects as $subject)
                                        <div class="subject-card" data-subject-id="{{ $subject->id }}" data-class-id="{{ $subject->class_id }}">
                                            <div class="form-check">
                                                <input class="form-check-input subject-checkbox" 
                                                       type="checkbox" 
                                                       name="subject_ids[]" 
                                                       value="{{ $subject->id }}" 
                                                       id="subject_{{ $subject->id }}">
                                                <label class="form-check-label w-100" for="subject_{{ $subject->id }}">
                                                    <div class="fw-semibold">{{ $subject->name }}</div>
                                                    <small class="text-muted">
                                                        {{ $subject->schoolClass->name ?? '' }}
                                                        @if($subject->schoolClass && $subject->schoolClass->stage)
                                                            - {{ $subject->schoolClass->stage->name }}
                                                        @endif
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم المعاينة والإعدادات -->
                    <div class="col-xl-12">
                        <div class="preview-section">
                            <h5 class="mb-3 text-white">
                                <i class="bi bi-eye me-2"></i> معاينة الانضمامات
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="display-4 fw-bold" id="previewStudents">0</div>
                                        <div class="text-white-50">طالب</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="display-4 fw-bold" id="previewSubjects">0</div>
                                        <div class="text-white-50">مادة</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="display-4 fw-bold" id="previewTotal">0</div>
                                        <div class="text-white-50">انضمام</div>
                                    </div>
                                </div>
                            </div>
                            <div id="duplicateWarning" class="alert alert-warning mt-3" style="display: none;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span id="duplicateCount">0</span> انضمام موجود مسبقاً وسيتم تخطيه
                            </div>
                        </div>

                        <div class="card custom-card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-gear me-2"></i> إعدادات إضافية
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">حالة الانضمام</label>
                                        <select name="status" class="form-select">
                                            <option value="active" selected>نشط</option>
                                            <option value="suspended">معلق</option>
                                            <option value="completed">مكتمل</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">ملاحظات (اختياري)</label>
                                        <textarea name="notes" class="form-control" rows="3" 
                                                  placeholder="أضف ملاحظات حول هذه الانضمامات..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الأزرار -->
                    <div class="col-xl-12">
                        <div class="d-flex gap-2 justify-content-end mt-3">
                            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-check-circle me-1"></i> إضافة الانضمامات
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedStudents = new Set();
    let allStudents = [];
    let searchTimeout;

    // Hidden inputs for selected students
    const form = document.getElementById('enrollmentForm');
    
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const stageFilter = document.getElementById('stageFilter');
    const classFilter = document.getElementById('classFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const studentsList = document.getElementById('studentsList');
    const loadingSpinner = document.querySelector('.loading-spinner');
    const resultsCount = document.getElementById('resultsCount');

    // Subject filtering
    const subjectClassFilter = document.getElementById('subjectClassFilter');
    const subjectsList = document.getElementById('subjectsList');
    const subjectCards = document.querySelectorAll('.subject-card');

    // Counters
    const selectedStudentsCount = document.getElementById('selectedStudentsCount');
    const selectedSubjectsCount = document.getElementById('selectedSubjectsCount');
    const previewStudents = document.getElementById('previewStudents');
    const previewSubjects = document.getElementById('previewSubjects');
    const previewTotal = document.getElementById('previewTotal');
    const duplicateWarning = document.getElementById('duplicateWarning');
    const duplicateCount = document.getElementById('duplicateCount');
    const submitBtn = document.getElementById('submitBtn');

    // Search students function
    function searchStudents() {
        loadingSpinner.classList.add('active');
        
        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (stageFilter.value) params.append('stage_id', stageFilter.value);
        if (classFilter.value) params.append('class_id', classFilter.value);
        if (statusFilter.value) params.append('is_active', statusFilter.value);

        // الحصول على CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch(`{{ route('admin.enrollments.search-students') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Response error:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            loadingSpinner.classList.remove('active');
            if (data.success) {
                allStudents = data.data;
                displayStudents(data.data);
                resultsCount.textContent = data.count;
            } else {
                // عرض رسالة خطأ
                studentsList.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'حدث خطأ أثناء البحث'}
                    </div>
                `;
                resultsCount.textContent = '0';
            }
        })
        .catch(error => {
            loadingSpinner.classList.remove('active');
            console.error('Fetch Error:', error);
            studentsList.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    حدث خطأ أثناء الاتصال بالخادم. يرجى المحاولة مرة أخرى.
                    <br><small>${error.message}</small>
                </div>
            `;
            resultsCount.textContent = '0';
        });
    }

    // Display students
    function displayStudents(students) {
        if (students.length === 0) {
            studentsList.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                    <p>لا توجد نتائج</p>
                </div>
            `;
            return;
        }

        studentsList.innerHTML = students.map(student => {
            const isSelected = selectedStudents.has(student.id);
            return `
                <div class="student-card ${isSelected ? 'selected' : ''}" data-student-id="${student.id}">
                    <div class="form-check">
                        <input class="form-check-input student-checkbox" 
                               type="checkbox" 
                               ${isSelected ? 'checked' : ''}
                               id="student_${student.id}">
                        <label class="form-check-label w-100" for="student_${student.id}">
                            <div class="d-flex align-items-center gap-2">
                                ${student.photo && student.photo !== null ? 
                                    `<img src="/storage/${student.photo}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">` :
                                    `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        ${student.name ? student.name.charAt(0) : 'U'}
                                    </div>`
                                }
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${student.name}</div>
                                    <small class="text-muted">${student.email || ''}</small>
                                    ${student.phone ? `<br><small class="text-muted">${student.phone}</small>` : ''}
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-${student.is_active ? 'success' : 'danger'}-transparent text-${student.is_active ? 'success' : 'danger'}">
                                        ${student.is_active ? 'نشط' : 'غير نشط'}
                                    </span>
                                    ${student.enrolled_subjects_count > 0 ? 
                                        `<br><small class="text-muted">${student.enrolled_subjects_count} مادة</small>` : 
                                        ''
                                    }
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            `;
        }).join('');

        // Attach event listeners
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const studentId = parseInt(this.id.replace('student_', ''));
                const card = this.closest('.student-card');
                
                if (this.checked) {
                    selectedStudents.add(studentId);
                    card.classList.add('selected');
                } else {
                    selectedStudents.delete(studentId);
                    card.classList.remove('selected');
                }
                
                updateCounters();
                updateFormInputs();
            });
        });

        // Card click handler
        document.querySelectorAll('.student-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('.student-checkbox');
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Update form hidden inputs
    function updateFormInputs() {
        // Remove existing hidden inputs
        document.querySelectorAll('input[name="user_ids[]"]').forEach(input => {
            if (input.type === 'hidden') input.remove();
        });

        // Add new hidden inputs
        selectedStudents.forEach(studentId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = studentId;
            form.appendChild(input);
        });
    }

    // Update counters
    function updateCounters() {
        const selectedSubjects = document.querySelectorAll('.subject-checkbox:checked').length;
        
        selectedStudentsCount.textContent = selectedStudents.size;
        selectedSubjectsCount.textContent = selectedSubjects;
        previewStudents.textContent = selectedStudents.size;
        previewSubjects.textContent = selectedSubjects;
        previewTotal.textContent = selectedStudents.size * selectedSubjects;

        // Enable/disable submit button
        submitBtn.disabled = selectedStudents.size === 0 || selectedSubjects === 0;

        // Check for duplicates (simplified - would need server-side check)
        checkDuplicates();
    }

    // Check for duplicate enrollments
    function checkDuplicates() {
        // This would ideally be done server-side
        // For now, we'll just show a warning if there are many enrollments
        const total = selectedStudents.size * document.querySelectorAll('.subject-checkbox:checked').length;
        if (total > 50) {
            duplicateWarning.style.display = 'block';
            duplicateCount.textContent = 'قد يكون هناك';
        } else {
            duplicateWarning.style.display = 'none';
        }
    }

    // Subject filtering function
    function filterSubjectsByClass() {
        const classId = subjectClassFilter.value;
        subjectCards.forEach(card => {
            if (!classId || card.dataset.classId === classId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        updateCounters();
    }

    // Subject filtering
    subjectClassFilter.addEventListener('change', filterSubjectsByClass);

    // Auto-select class if provided
    @if(isset($selectedClassId) && $selectedClassId)
        document.addEventListener('DOMContentLoaded', function() {
            const classFilter = document.getElementById('subjectClassFilter');
            if (classFilter) {
                classFilter.value = '{{ $selectedClassId }}';
                filterSubjectsByClass();
            }
        });
    @endif

    // Auto-select subject if provided
    @if(isset($selectedSubjectId) && $selectedSubjectId)
        document.addEventListener('DOMContentLoaded', function() {
            const subjectCheckbox = document.getElementById('subject_{{ $selectedSubjectId }}');
            if (subjectCheckbox) {
                subjectCheckbox.checked = true;
                subjectCheckbox.closest('.subject-card').classList.add('selected');
                
                // Set class filter if subject has a class
                const subjectCard = subjectCheckbox.closest('.subject-card');
                const classId = subjectCard.getAttribute('data-class-id');
                if (classId) {
                    document.getElementById('subjectClassFilter').value = classId;
                    filterSubjectsByClass();
                }
                
                updateSubjectSelection();
            }
        });
    @endif

    // Subject selection
    document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.subject-card');
            if (this.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
            updateCounters();
        });
    });

    // Subject card click
    document.querySelectorAll('.subject-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.subject-checkbox');
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    // Select all buttons
    document.getElementById('selectAllStudents').addEventListener('click', function() {
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    document.getElementById('deselectAllStudents').addEventListener('click', function() {
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    document.getElementById('selectAllSubjects').addEventListener('click', function() {
        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            if (!checkbox.checked && checkbox.closest('.subject-card').style.display !== 'none') {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    document.getElementById('deselectAllSubjects').addEventListener('click', function() {
        document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });

    // Search with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchStudents, 500);
    });

    // Filter change handlers
    stageFilter.addEventListener('change', function() {
        // Filter classes by stage
        const stageId = this.value;
        Array.from(classFilter.options).forEach(option => {
            if (option.value === '') return;
            if (!stageId || option.dataset.stage === stageId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });

    searchBtn.addEventListener('click', searchStudents);
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        stageFilter.value = '';
        classFilter.value = '';
        statusFilter.value = '';
        Array.from(classFilter.options).forEach(option => {
            option.style.display = 'block';
        });
        searchStudents();
    });

    // Initial search - عرض جميع المستخدمين في البداية
    // يمكن تغيير هذا لاحقاً إذا أردت عرض طلاب فقط
    searchStudents();
    
    // إضافة error handling أفضل
    window.addEventListener('error', function(e) {
        console.error('Global error:', e);
    });
});
</script>
@stop

