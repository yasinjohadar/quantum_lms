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

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fe fe-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">إرسال إشعار مخصص</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.notifications.store') }}" method="POST" id="notification-form">
                            @csrf

                            <!-- Target Type - Now a Dropdown -->
                            <div class="mb-4">
                                <label class="form-label">اختر نوع الهدف <span class="text-danger">*</span></label>
                                <select class="form-select" name="target_type" id="target_type" onchange="handleTargetTypeChange()">
                                    <option value="subject">
                                        مادة واحدة
                                    </option>
                                    <option value="class">
                                        صف معين
                                    </option>
                                    <option value="group">
                                        مجموعة
                                    </option>
                                    <option value="individual">
                                        طلاب محددين
                                    </option>
                                </select>
                            </div>

                            <!-- Subject Selection -->
                            <div class="mb-4" id="subject-selection">
                                <label class="form-label">اختر المادة <span class="text-danger">*</span></label>
                                <select class="form-select" name="subject_id" id="subject_id" onchange="loadTargetStats(); updatePreview();">
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
                            <div class="mb-4" id="class-selection" style="display: none;">
                                <label class="form-label">اختر الصف <span class="text-danger">*</span></label>
                                <select class="form-select" name="class_id" id="class_id" onchange="loadTargetStats(); updatePreview();">
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
                            <div class="mb-4" id="group-selection" style="display: none;">
                                <label class="form-label">اختر المجموعة <span class="text-danger">*</span></label>
                                <select class="form-select" name="group_id" id="group_id" onchange="loadTargetStats(); updatePreview();">
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
                            <div class="mb-4" id="individual-selection" style="display: none;">
                                <label class="form-label">اختر الطلاب <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_ids[]" id="user_ids" multiple size="10" onchange="updatePreview();">
                                    <!-- سيتم ملؤها عبر AJAX -->
                                </select>
                                <div class="form-text">يمكنك اختيار عدة طلاب باستخدام Ctrl/Cmd</div>
                            </div>

                            <!-- Title -->
                            <div class="mb-4">
                                <label class="form-label">عنوان الإشعار <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" 
                                       placeholder="مثال: إعلان مهم" required maxlength="255" oninput="updatePreview();">
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label class="form-label">نص الإشعار <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" id="message" rows="5" 
                                          placeholder="اكتب نص الإشعار هنا..." required maxlength="1000" oninput="updateCharCount(); updatePreview();"></textarea>
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
                                <strong>مجموعة:</strong> سيتم إرسال الإشعار لجميع الطلاب في المجموعة
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

<script>
// Global functions - defined in global scope
function handleTargetTypeChange() {
    var targetType = document.getElementById('target_type').value;
    
    // Get all selection divs
    var subjectDiv = document.getElementById('subject-selection');
    var classDiv = document.getElementById('class-selection');
    var groupDiv = document.getElementById('group-selection');
    var individualDiv = document.getElementById('individual-selection');
    
    // Hide all selection divs
    if (subjectDiv) subjectDiv.style.display = 'none';
    if (classDiv) classDiv.style.display = 'none';
    if (groupDiv) groupDiv.style.display = 'none';
    if (individualDiv) individualDiv.style.display = 'none';
    
    // Reset all select values
    document.getElementById('subject_id').value = '';
    document.getElementById('class_id').value = '';
    document.getElementById('group_id').value = '';
    
    var userSelect = document.getElementById('user_ids');
    for (var i = 0; i < userSelect.options.length; i++) {
        userSelect.options[i].selected = false;
    }
    
    // Show the selected one
    if (targetType === 'subject') {
        if (subjectDiv) subjectDiv.style.display = 'block';
    } else if (targetType === 'class') {
        if (classDiv) classDiv.style.display = 'block';
    } else if (targetType === 'group') {
        if (groupDiv) groupDiv.style.display = 'block';
    } else if (targetType === 'individual') {
        if (individualDiv) individualDiv.style.display = 'block';
        loadAllUsers();
    }
    
    // Reset stats
    document.getElementById('target-stats').innerHTML = 
        '<div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">' +
        '<i class="fe fe-info fs-40 text-muted"></i>' +
        '</div>' +
        '<p class="text-muted mb-0">اختر الهدف لعرض الإحصائيات</p>';
    
    updatePreview();
}

function loadTargetStats() {
    var targetType = document.getElementById('target_type').value;
    var targetId = getTargetId();
    
    if (!targetId) {
        document.getElementById('target-stats').innerHTML = 
            '<div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">' +
            '<i class="fe fe-info fs-40 text-muted"></i>' +
            '</div>' +
            '<p class="text-muted mb-0">اختر الهدف لعرض الإحصائيات</p>';
        return;
    }
    
    fetch('/admin/notifications/target-users?target_type=' + targetType + '&target_id=' + targetId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('target-stats').innerHTML = 
                    '<div class="text-center">' +
                    '<div class="avatar avatar-xl bg-primary-transparent rounded-circle mx-auto mb-3">' +
                    '<i class="fe fe-users fs-40 text-primary"></i>' +
                    '</div>' +
                    '<h3 class="mb-1">' + data.count + '</h3>' +
                    '<p class="text-muted mb-0">طالب سيستلم الإشعار</p>' +
                    '</div>';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function getTargetId() {
    var targetType = document.getElementById('target_type').value;
    if (targetType === 'subject') {
        return document.getElementById('subject_id').value;
    } else if (targetType === 'class') {
        return document.getElementById('class_id').value;
    } else if (targetType === 'group') {
        return document.getElementById('group_id').value;
    }
    return null;
}

function loadAllUsers() {
    fetch('/admin/notifications/all-users')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var select = document.getElementById('user_ids');
            select.innerHTML = '';
            if (data.success && data.users) {
                data.users.forEach(function(user) {
                    var option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + ' (' + user.email + ')';
                    select.appendChild(option);
                });
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function updatePreview() {
    var title = document.getElementById('title').value || 'عنوان الإشعار';
    var message = document.getElementById('message').value || 'نص الإشعار';
    var targetType = document.getElementById('target_type').value;
    
    var targetText = '--';
    if (targetType === 'subject') {
        var subjectSelect = document.getElementById('subject_id');
        if (subjectSelect.selectedIndex > 0) {
            targetText = subjectSelect.options[subjectSelect.selectedIndex].text;
        }
    } else if (targetType === 'class') {
        var classSelect = document.getElementById('class_id');
        if (classSelect.selectedIndex > 0) {
            targetText = classSelect.options[classSelect.selectedIndex].text;
        }
    } else if (targetType === 'group') {
        var groupSelect = document.getElementById('group_id');
        if (groupSelect.selectedIndex > 0) {
            targetText = groupSelect.options[groupSelect.selectedIndex].text;
        }
    } else if (targetType === 'individual') {
        var userSelect = document.getElementById('user_ids');
        var count = 0;
        for (var i = 0; i < userSelect.options.length; i++) {
            if (userSelect.options[i].selected) count++;
        }
        targetText = count + ' طالب محدد';
    }
    
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-message').textContent = message;
    document.getElementById('preview-target').textContent = 'الهدف: ' + targetText;
}

function updateCharCount() {
    var count = document.getElementById('message').value.length;
    document.getElementById('char-count').textContent = count;
}

function resetForm() {
    document.getElementById('notification-form').reset();
    document.getElementById('target_type').value = 'subject';
    handleTargetTypeChange();
    updatePreview();
    document.getElementById('char-count').textContent = '0';
}

// Form validation on submit
document.getElementById('notification-form').onsubmit = function(e) {
    var targetType = document.getElementById('target_type').value;
    var targetId = getTargetId();
    
    if (targetType !== 'individual' && !targetId) {
        e.preventDefault();
        alert('يرجى اختيار الهدف');
        return false;
    }
    
    if (targetType === 'individual') {
        var userSelect = document.getElementById('user_ids');
        var hasSelected = false;
        for (var i = 0; i < userSelect.options.length; i++) {
            if (userSelect.options[i].selected) {
                hasSelected = true;
                break;
            }
        }
        if (!hasSelected) {
            e.preventDefault();
            alert('يرجى اختيار طالب واحد على الأقل');
            return false;
        }
    }
    
    if (!confirm('هل أنت متأكد من إرسال هذا الإشعار؟')) {
        e.preventDefault();
        return false;
    }
    
    return true;
};

// Initialize on page load
handleTargetTypeChange();
</script>
@stop
