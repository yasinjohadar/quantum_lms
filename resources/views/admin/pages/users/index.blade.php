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
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">إنشاء مستخدم جديد</a>

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
                            <p class="text-muted">
                            <div class="">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
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

                                                @include('admin.pages.users.delete')
                                                @include('admin.pages.users.change_password')
                                                @include('admin.pages.users.toggle_status')
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



                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--End::row-1 -->


        </div>
    </div>
    <!-- End::app-content -->



@stop

@section('js')
<script>
    // إظهار الرسائل تلقائياً
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@stop
