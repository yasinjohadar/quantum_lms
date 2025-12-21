@extends('admin.layouts.master')

@section('page-title')
    إرسال إشعار مخصص
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إرسال إشعار مخصص</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">إرسال إشعار</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">إرسال إشعار مخصص</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.notifications.store') }}" method="POST" id="notification-form">
                            @csrf

                            <!-- Target Type -->
                            <div class="mb-4">
                                <label class="form-label">اختر الهدف <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="target_type" id="target_subject" value="subject" checked>
                                            <label class="form-check-label" for="target_subject">
                                                <i class="fe fe-book me-2"></i> مادة واحدة
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="target_type" id="target_class" value="class">
                                            <label class="form-check-label" for="target_class">
                                                <i class="fe fe-users me-2"></i> صف معين
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="target_type" id="target_group" value="group">
                                            <label class="form-check-label" for="target_group">
                                                <i class="fe fe-layers me-2"></i> مجموعة
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="target_type" id="target_individual" value="individual">
                                            <label class="form-check-label" for="target_individual">
                                                <i class="fe fe-user me-2"></i> طلاب محددين
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Selection -->
                            <div class="mb-4" id="subject-selection">
                                <label class="form-label">اختر المادة <span class="text-danger">*</span></label>
                                <select class="form-select" name="subject_id" id="subject_id">
                                    <option value="">-- اختر المادة --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">
                                            {{ $subject->name }} 
                                            @if($subject->schoolClass)
                                                ({{ $subject->schoolClass->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="subject-info"></div>
                            </div>

                            <!-- Class Selection -->
                            <div class="mb-4 d-none" id="class-selection">
                                <label class="form-label">اختر الصف <span class="text-danger">*</span></label>
                                <select class="form-select" name="class_id" id="class_id">
                                    <option value="">-- اختر الصف --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">
                                            {{ $class->name }}
                                            @if($class->stage)
                                                ({{ $class->stage->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="class-info"></div>
                            </div>

                            <!-- Group Selection -->
                            <div class="mb-4 d-none" id="group-selection">
                                <label class="form-label">اختر المجموعة <span class="text-danger">*</span></label>
                                <select class="form-select" name="group_id" id="group_id">
                                    <option value="">-- اختر المجموعة --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">
                                            {{ $group->name }}
                                            @if($group->description)
                                                - {{ Str::limit($group->description, 50) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="group-info"></div>
                            </div>

                            <!-- Individual Users Selection -->
                            <div class="mb-4 d-none" id="individual-selection">
                                <label class="form-label">اختر الطلاب <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_ids[]" id="user_ids" multiple size="10">
                                    <!-- سيتم ملؤها عبر AJAX -->
                                </select>
                                <div class="form-text">يمكنك اختيار عدة طلاب باستخدام Ctrl/Cmd</div>
                            </div>

                            <!-- Title -->
                            <div class="mb-4">
                                <label class="form-label">عنوان الإشعار <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" 
                                       placeholder="مثال: إعلان مهم" required maxlength="255">
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label class="form-label">نص الإشعار <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" id="message" rows="5" 
                                          placeholder="اكتب نص الإشعار هنا..." required maxlength="1000"></textarea>
                                <div class="form-text">
                                    <span id="char-count">0</span> / 1000 حرف
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="mb-4">
                                <label class="form-label">معاينة الإشعار</label>
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 id="preview-title" class="text-muted">عنوان الإشعار</h6>
                                        <p id="preview-message" class="text-muted mb-0">نص الإشعار</p>
                                        <small class="text-muted" id="preview-target">الهدف: --</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-send me-2"></i> إرسال الإشعار
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fe fe-refresh-cw me-2"></i> إعادة تعيين
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stats Sidebar -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">إحصائيات</h3>
                    </div>
                    <div class="card-body">
                        <div id="target-stats" class="text-center py-4">
                            <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                                <i class="fe fe-info fs-40 text-muted"></i>
                            </div>
                            <p class="text-muted mb-0">اختر الهدف لعرض الإحصائيات</p>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">مساعدة</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fe fe-book text-primary me-2"></i>
                                <strong>مادة واحدة:</strong> سيتم إرسال الإشعار لجميع الطلاب المسجلين في المادة
                            </li>
                            <li class="mb-2">
                                <i class="fe fe-users text-info me-2"></i>
                                <strong>صف معين:</strong> سيتم إرسال الإشعار لجميع الطلاب المسجلين في مواد الصف
                            </li>
                            <li class="mb-2">
                                <i class="fe fe-layers text-success me-2"></i>
                                <strong>مجموعة:</strong> سيتم إرسال الإشعار لجميع الطلاب في المجموعة (مباشرين أو من خلال الصفوف/المواد)
                            </li>
                            <li>
                                <i class="fe fe-user text-warning me-2"></i>
                                <strong>طلاب محددين:</strong> يمكنك اختيار طلاب محددين يدوياً
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

@push('scripts')
<script>
    // Handle target type change
    document.querySelectorAll('input[name="target_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateSelectionVisibility();
            loadTargetStats();
        });
    });

    function updateSelectionVisibility() {
        const targetType = document.querySelector('input[name="target_type"]:checked').value;
        
        // Hide all selections
        document.getElementById('subject-selection').classList.add('d-none');
        document.getElementById('class-selection').classList.add('d-none');
        document.getElementById('group-selection').classList.add('d-none');
        document.getElementById('individual-selection').classList.add('d-none');
        
        // Show selected
        switch(targetType) {
            case 'subject':
                document.getElementById('subject-selection').classList.remove('d-none');
                break;
            case 'class':
                document.getElementById('class-selection').classList.remove('d-none');
                break;
            case 'group':
                document.getElementById('group-selection').classList.remove('d-none');
                break;
            case 'individual':
                document.getElementById('individual-selection').classList.remove('d-none');
                loadAllUsers();
                break;
        }
        
        updatePreview();
    }

    // Load target stats
    function loadTargetStats() {
        const targetType = document.querySelector('input[name="target_type"]:checked').value;
        const targetId = getTargetId();
        
        if (!targetId) {
            document.getElementById('target-stats').innerHTML = `
                <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                    <i class="fe fe-info fs-40 text-muted"></i>
                </div>
                <p class="text-muted mb-0">اختر الهدف لعرض الإحصائيات</p>
            `;
            return;
        }
        
        fetch(`/admin/notifications/target-users?target_type=${targetType}&target_id=${targetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('target-stats').innerHTML = `
                        <div class="text-center">
                            <div class="avatar avatar-xl bg-primary-transparent rounded-circle mx-auto mb-3">
                                <i class="fe fe-users fs-40 text-primary"></i>
                            </div>
                            <h3 class="mb-1">${data.count}</h3>
                            <p class="text-muted mb-0">طالب سيستلم الإشعار</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function getTargetId() {
        const targetType = document.querySelector('input[name="target_type"]:checked').value;
        switch(targetType) {
            case 'subject':
                return document.getElementById('subject_id').value;
            case 'class':
                return document.getElementById('class_id').value;
            case 'group':
                return document.getElementById('group_id').value;
            default:
                return null;
        }
    }

    // Load all users for individual selection
    function loadAllUsers() {
        fetch('/admin/notifications/all-users')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('user_ids');
                select.innerHTML = '';
                if (data.success && data.users) {
                    data.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.name} (${user.email})`;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Update preview
    function updatePreview() {
        const title = document.getElementById('title').value || 'عنوان الإشعار';
        const message = document.getElementById('message').value || 'نص الإشعار';
        const targetType = document.querySelector('input[name="target_type"]:checked').value;
        
        let targetText = '--';
        switch(targetType) {
            case 'subject':
                const subjectSelect = document.getElementById('subject_id');
                targetText = subjectSelect.options[subjectSelect.selectedIndex]?.text || '--';
                break;
            case 'class':
                const classSelect = document.getElementById('class_id');
                targetText = classSelect.options[classSelect.selectedIndex]?.text || '--';
                break;
            case 'group':
                const groupSelect = document.getElementById('group_id');
                targetText = groupSelect.options[groupSelect.selectedIndex]?.text || '--';
                break;
            case 'individual':
                const userSelect = document.getElementById('user_ids');
                targetText = `${userSelect.selectedOptions.length} طالب محدد`;
                break;
        }
        
        document.getElementById('preview-title').textContent = title;
        document.getElementById('preview-message').textContent = message;
        document.getElementById('preview-target').textContent = `الهدف: ${targetText}`;
    }

    // Character count
    document.getElementById('message').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('char-count').textContent = count;
        updatePreview();
    });

    document.getElementById('title').addEventListener('input', updatePreview);

    // Watch for selection changes
    document.getElementById('subject_id')?.addEventListener('change', function() {
        loadTargetStats();
        updatePreview();
    });
    document.getElementById('class_id')?.addEventListener('change', function() {
        loadTargetStats();
        updatePreview();
    });
    document.getElementById('group_id')?.addEventListener('change', function() {
        loadTargetStats();
        updatePreview();
    });
    document.getElementById('user_ids')?.addEventListener('change', updatePreview);

    // Form submission
    document.getElementById('notification-form').addEventListener('submit', function(e) {
        const targetType = document.querySelector('input[name="target_type"]:checked').value;
        const targetId = getTargetId();
        
        if (targetType !== 'individual' && !targetId) {
            e.preventDefault();
            alert('يرجى اختيار الهدف');
            return false;
        }
        
        if (targetType === 'individual') {
            const selectedUsers = Array.from(document.getElementById('user_ids').selectedOptions);
            if (selectedUsers.length === 0) {
                e.preventDefault();
                alert('يرجى اختيار طالب واحد على الأقل');
                return false;
            }
        }
        
        if (!confirm('هل أنت متأكد من إرسال هذا الإشعار؟')) {
            e.preventDefault();
            return false;
        }
    });

    function resetForm() {
        document.getElementById('notification-form').reset();
        updateSelectionVisibility();
        updatePreview();
        document.getElementById('char-count').textContent = '0';
    }

    // Initialize
    updateSelectionVisibility();
</script>
@endpush

