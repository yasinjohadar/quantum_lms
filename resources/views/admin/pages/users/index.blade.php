@extends('admin.layouts.master')

@section('page-title')
    قائمة المستخدمون
@stop



@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كافة المستخدمين</h5>

                </div>
            </div>
            <!-- End Page Header -->

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>نجح!</strong> {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>خطأ!</strong> {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 20px; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>خطأ في البيانات!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Start Content -->


            </div>
            <!-- Page Header Close -->



            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex gap-3">
                            <div class="d-flex gap-2">
                                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">إنشاء مستخدم جديد</a>
                                <a href="{{ route('admin.archived-users.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-archive me-1"></i> الأرشيف
                                </a>
                            </div>

                            <div class="flex-shrink-0">
                                <div class="form-check form-switch form-switch-right form-switch-md">
                                    <form action="{{ route('users.index') }}" method="GET"
                                        class="d-flex align-items-center gap-2">
                                        {{-- حقل البحث --}}
                                        <input style="width: 300px" type="text" name="query" class="form-control"
                                            placeholder="بحث بالاسم أو الإيميل أو الهاتف" value="{{ request('query') }}">

                                        {{-- فلتر الحالة النشطة --}}
                                        <select name="is_active" class="form-select">
                                            <option value="">كل الحالات النشطة</option>
                                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                        </select>


                                        <button type="submit" class="btn btn-secondary">بحث</button>
                                        <a href="{{ route('users.index') }}" class="btn btn-danger">مسح </a>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <div class="card-body">
                            <form id="bulk-archive-form" action="{{ route('admin.users.bulk-archive') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_ids" id="user_ids_input">
                                <input type="hidden" name="reason" id="archive_reason_input">

                                <div class="mb-3">
                                    <button type="button" class="btn btn-warning btn-sm" id="bulk-archive-btn" style="display: none;">
                                        <i class="fas fa-archive me-1"></i> أرشفة المحدد
                                    </button>
                                </div>

                            <p class="text-muted">
                            <div class="">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 40px;">
                                                    <input type="checkbox" id="select-all-users" class="form-check-input">
                                                </th>
                                                <th scope="col" style="width: 40px;">#</th>
                                                <th scope="col" style="min-width: 150px;">اسم المستخدم</th>
                                                <th scope="col" style="min-width: 200px;">البريد</th>
                                                <th scope="col" style="min-width: 120px;">الهاتف</th>
                                                <th scope="col" style="min-width: 130px;">اخر دخول</th>
                                                <th scope="col" style="min-width: 150px;">الأدوار</th>
                                                <th scope="col" style="min-width: 140px;">حالة الحساب</th>
                                                <th scope="col" style="min-width: 120px;">حالة الاتصال</th>
                                                <th scope="col" style="min-width: 200px;">العمليات</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @forelse ($users as $user)
                                                @php
                                                    $userSessions = $sessions->get($user->id);
                                                    $lastSession = $userSessions ? $userSessions->first() : null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        @if(!$user->is_archived)
                                                        <input type="checkbox" name="selected_user_ids[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
                                                        @endif
                                                    </td>
                                                    <th scope="row">{{ $loop->iteration }}</th>

                                                    <td>
                                                        <a href="{{ route('users.show', $user->id) }}"
                                                            class="text-decoration-none">
                                                            {{ $user->name }}
                                                        </a>
                                                    </td>

                                                    <td>
                                                        @if ($user->email)
                                                            <a href="mailto:{{ $user->email }}"
                                                                class="text-primary text-decoration-none"
                                                                title="إرسال بريد إلكتروني">
                                                                {{ $user->email }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($user->phone)
                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}"
                                                                target="_blank"
                                                                class="text-success text-decoration-none me-1"
                                                                title="فتح WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                            {{ $user->phone }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($lastSession)
                                                            {{ \Carbon\Carbon::createFromTimestamp($lastSession->last_activity)->diffForHumans() }}
                                                        @else
                                                            لا توجد جلسات
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @foreach ($user->getRoleNames() as $role)
                                                            <span class="badge bg-primary me-1">{{ $role }}</span>
                                                        @endforeach
                                                    </td>

                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm d-inline-flex align-items-center {{ $user->is_active ? 'btn-success' : 'btn-outline-danger' }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#toggleStatus{{ $user->id }}">
                                                            @if($user->is_active)
                                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                                <span>الحساب مفعل</span>
                                                            @else
                                                                <i class="fa-solid fa-ban me-1"></i>
                                                                <span>الحساب معطل</span>
                                                            @endif
                                                        </button>
                                                    </td>

                                                    <td>
                                                        @php
                                                            // اعتبر المستخدم متصلًا إذا كانت لديه جلسة حالية وآخر نشاط خلال آخر 15 دقيقة
                                                            $isConnected = false;
                                                            if ($lastSession) {
                                                                $lastActivity = \Carbon\Carbon::createFromTimestamp($lastSession->last_activity);
                                                                $isConnected = ($lastSession->is_current ?? true) && $lastActivity->gt(now()->subMinutes(15));
                                                            }
                                                        @endphp

                                                        @if ($isConnected)
                                                            <span class="badge bg-success">متصل الآن</span>
                                                        @else
                                                            <span class="badge bg-secondary">غير متصل</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <div class="d-flex gap-1 flex-wrap">
                                                            <a class="btn btn-info btn-sm"
                                                                href="{{ route('users.edit', $user->id) }}"
                                                                title="تعديل المستخدم">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </a>
                                                            <a class="btn btn-primary btn-sm"
                                                                href="{{ route('users.login-logs', $user->id) }}"
                                                                title="سجلات الدخول">
                                                                <i class="fa-solid fa-sign-in-alt"></i>
                                                            </a>
                                                            @if($user->phone && !$user->phone_verified_at)
                                                            <button type="button" 
                                                                    class="btn btn-success btn-sm send-otp-btn"
                                                                    data-user-id="{{ $user->id }}"
                                                                    data-user-name="{{ $user->name }}"
                                                                    data-user-phone="{{ $user->phone }}"
                                                                    title="إرسال كود التحقق">
                                                                <i class="fa-solid fa-message"></i>
                                                            </button>
                                                            @endif
                                                            @if(!$user->is_archived)
                                                            <button type="button" class="btn btn-warning btn-sm archive-user-btn"
                                                                    data-user-id="{{ $user->id }}"
                                                                    data-user-name="{{ $user->name }}"
                                                                    title="أرشفة المستخدم">
                                                                <i class="fas fa-archive"></i>
                                                            </button>
                                                            @endif
                                                            <a class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                                data-bs-target="#delete{{ $user->id }}"
                                                                title="حذف المستخدم">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </a>
                                                            <a href="#" class="btn btn-warning btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#change_password{{ $user->id }}"
                                                                title="تعديل كلمة السر">
                                                                <i class="fa-solid fa-key"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                {{-- Modals for each user --}}
                                                @include('admin.pages.users.toggle_status', ['user' => $user])
                                                @include('admin.pages.users.delete', ['user' => $user])
                                                @include('admin.pages.users.change_password', ['user' => $user])
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-danger fw-bold">لا توجد
                                                        بيانات متاحة
                                                    </td>
                                                </tr>
                                            @endforelse

                                        </tbody>
                                    </table>

                                    <div class="mt-3">
                                        {{ $users->withQueryString()->links() }}
                                    </div>
                                </div>
                            </div>
                            </form>



                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--End::row-1 -->


        </div>
    </div>
    <!-- End::app-content -->

<!-- Modal أرشفة المستخدم -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="archiveModalLabel">
                    <i class="bi bi-archive-fill me-2"></i> أرشفة المستخدم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="bi bi-archive-fill text-warning" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-center mb-3">هل أنت متأكد من أرشفة هذا المستخدم؟</h5>
                <p class="text-muted text-center mb-3">
                    <strong id="archiveUserName"></strong>
                </p>
                <div class="mb-3">
                    <label for="archiveReason" class="form-label">سبب الأرشفة (اختياري)</label>
                    <textarea class="form-control" id="archiveReason" name="reason" rows="3" 
                              placeholder="أدخل سبب الأرشفة (اختياري)"></textarea>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    سيتم نقل المستخدم إلى الأرشيف ويمكن استعادته لاحقاً.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> إلغاء
                </button>
                <form id="archiveForm" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="reason" id="archiveReasonInput">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-archive me-1"></i> نعم، أرشفة المستخدم
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    // إظهار الرسائل تلقائياً
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk archive functionality
        const selectAllUsers = document.getElementById('select-all-users');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkArchiveBtn = document.getElementById('bulk-archive-btn');
        const bulkArchiveForm = document.getElementById('bulk-archive-form');
        const userIdsInput = document.getElementById('user_ids_input');
        const archiveReasonInput = document.getElementById('archive_reason_input');

        // Select all functionality
        if (selectAllUsers) {
            selectAllUsers.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBulkArchiveBtn();
            });
        }

        // Individual checkbox change
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleBulkArchiveBtn();
                updateSelectAllUsers();
            });
        });

        function toggleBulkArchiveBtn() {
            const checked = document.querySelectorAll('.user-checkbox:checked');
            if (bulkArchiveBtn) {
                bulkArchiveBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
            }
        }

        function updateSelectAllUsers() {
            if (selectAllUsers) {
                const allChecked = userCheckboxes.length > 0 && Array.from(userCheckboxes).every(cb => cb.checked);
                selectAllUsers.checked = allChecked;
            }
        }

        // Bulk archive
        if (bulkArchiveBtn) {
            bulkArchiveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checked = document.querySelectorAll('.user-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                
                if (ids.length === 0) {
                    alert('يرجى اختيار مستخدمين للأرشفة');
                    return;
                }

                const reason = prompt('أدخل سبب الأرشفة (اختياري):');
                if (reason === null) return; // User cancelled

                if (confirm('هل أنت متأكد من أرشفة ' + ids.length + ' مستخدم محدد؟')) {
                    // التأكد من أن الـ form يستخدم POST
                    bulkArchiveForm.method = 'POST';
                    
                    // إزالة أي input fields سابقة لـ user_ids
                    bulkArchiveForm.querySelectorAll('input[name^="user_ids"]').forEach(input => {
                        if (input.id !== 'user_ids_input') {
                            input.remove();
                        }
                    });
                    
                    // إرسال user_ids كـ JSON string (سيتم تحويله في Request)
                    userIdsInput.value = JSON.stringify(ids);
                    archiveReasonInput.value = reason || '';
                    
                    // التأكد من وجود CSRF token
                    if (!bulkArchiveForm.querySelector('input[name="_token"]')) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        bulkArchiveForm.appendChild(csrfInput);
                    }
                    
                    bulkArchiveForm.submit();
                }
            });
        }

        // Individual archive
        document.querySelectorAll('.archive-user-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                
                if (!userId || !userName) {
                    console.error('Missing data attributes');
                    return;
                }
                
                const archiveUserNameEl = document.getElementById('archiveUserName');
                const archiveFormEl = document.getElementById('archiveForm');
                const archiveReasonEl = document.getElementById('archiveReason');
                const archiveReasonInputEl = document.getElementById('archiveReasonInput');
                const archiveModalEl = document.getElementById('archiveModal');
                
                if (!archiveUserNameEl || !archiveFormEl || !archiveModalEl) {
                    console.error('Modal elements not found');
                    return;
                }
                
                archiveUserNameEl.textContent = userName;
                archiveFormEl.action = '{{ route("admin.users.archive", ":id") }}'.replace(':id', userId);
                if (archiveReasonEl) archiveReasonEl.value = '';
                if (archiveReasonInputEl) archiveReasonInputEl.value = '';
                
                const archiveModal = new bootstrap.Modal(archiveModalEl);
                archiveModal.show();
            });
        });

        // Archive form submission
        const archiveForm = document.getElementById('archiveForm');
        if (archiveForm) {
            archiveForm.addEventListener('submit', function(e) {
                const reason = document.getElementById('archiveReason').value;
                document.getElementById('archiveReasonInput').value = reason;
            });
        }

        // إظهار جميع الرسائل
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.display = 'block';
            alert.style.visibility = 'visible';
            alert.style.opacity = '1';
        });
        
        // إخفاء الرسائل تلقائياً بعد 5 ثواني
        setTimeout(function() {
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // إرسال OTP يدوياً
        document.querySelectorAll('.send-otp-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const button = this;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                const userPhone = button.getAttribute('data-user-phone');
                
                // تعطيل الزر وإظهار loading
                const originalHTML = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                
                // إرسال الطلب
                fetch(`/users/${userId}/send-verification-otp`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // إظهار رسالة نجاح
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>نجح!</strong> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                        
                        // إزالة الرسالة بعد 5 ثواني
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    } else {
                        // إظهار رسالة خطأ
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>خطأ!</strong> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                        
                        // إزالة الرسالة بعد 5 ثواني
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>خطأ!</strong> حدث خطأ أثناء إرسال كود التحقق
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    `;
                    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
                    
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                })
                .finally(() => {
                    // إعادة تفعيل الزر
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                });
            });
        });
    });
</script>
@stop
