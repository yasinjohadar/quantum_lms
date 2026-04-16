@extends('admin.layouts.master')

@section('page-title')
    المحذوفون سوفت
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">المحذوفون سوفت</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.manage') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة للمستخدمين
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.trashed.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">بحث</label>
                            <input type="text" name="query" class="form-control form-control-sm"
                                   placeholder="الاسم أو البريد أو الهاتف"
                                   value="{{ request('query') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">نوع المستخدم</label>
                            <select name="user_type" class="form-select form-select-sm">
                                <option value="" {{ request('user_type') ? '' : 'selected' }}>كل الأنواع</option>
                                <option value="student" {{ request('user_type') === 'student' ? 'selected' : '' }}>طالب</option>
                                <option value="teacher" {{ request('user_type') === 'teacher' ? 'selected' : '' }}>معلم</option>
                                <option value="supervisor" {{ request('user_type') === 'supervisor' ? 'selected' : '' }}>مشرف</option>
                                <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>مدير</option>
                                <option value="other" {{ request('user_type') === 'other' ? 'selected' : '' }}>أخرى</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label mb-1">الدور</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="" {{ request('role') ? '' : 'selected' }}>كل الأدوار</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">حالة الحساب</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="" {{ request('is_active') !== '0' && request('is_active') !== '1' ? 'selected' : '' }}>كل الحالات</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>مفعل</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطل</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.users.trashed.index') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-times me-1"></i> مسح
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">قائمة المستخدمين المحذوفين سوفت ({{ $users->total() }})</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="min-width: 170px;">الاسم</th>
                                <th style="min-width: 140px;">نوع المستخدم</th>
                                <th style="min-width: 200px;">البريد</th>
                                <th style="min-width: 160px;">الهاتف</th>
                                <th style="width: 140px;">حالة الحساب</th>
                                <th style="width: 150px;">حالة السجل</th>
                                <th style="min-width: 180px;">تاريخ الحذف</th>
                                <th style="min-width: 180px;">العمليات</th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>

                                    <td>
                                        {{ $user->name }}
                                    </td>

                                    <td>
                                        {{ $user->primary_role_label }}
                                    </td>

                                    <td>
                                        {{ $user->email ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $user->phone ?: '-' }}
                                    </td>

                                    <td>
                                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $user->is_active ? 'مفعل' : 'معطل' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            محذوف سوفت
                                        </span>
                                    </td>

                                    <td>
                                        {{ $user->deleted_at?->format('Y-m-d H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('user-delete')
                                                <form action="{{ route('admin.users.trashed.force-delete', $user->id) }}" method="POST"
                                                      onsubmit="return confirm('هل أنت متأكد من الحذف النهائي لهذا المستخدم؟');"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف نهائي">
                                                        <i class="fas fa-trash-alt me-1"></i> حذف نهائي
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-danger fw-bold">
                                        لا توجد بيانات متاحة
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

