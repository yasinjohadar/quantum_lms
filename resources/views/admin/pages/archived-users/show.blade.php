@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المستخدم المؤرشف
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المستخدم المؤرشف</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.archived-users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة للأرشيف
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

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">معلومات المستخدم المؤرشف</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">الاسم:</th>
                                            <td>{{ $archivedUser->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>البريد الإلكتروني:</th>
                                            <td>{{ $archivedUser->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>الهاتف:</th>
                                            <td>{{ $archivedUser->phone ?? '-' }}</td>
                                        </tr>
                                        @if($archivedUser->student_id)
                                        <tr>
                                            <th>رقم الطالب:</th>
                                            <td>{{ $archivedUser->student_id }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>تاريخ الميلاد:</th>
                                            <td>{{ $archivedUser->date_of_birth?->format('Y-m-d') ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>الجنس:</th>
                                            <td>
                                                @if($archivedUser->gender === 'male')
                                                    ذكر
                                                @elseif($archivedUser->gender === 'female')
                                                    أنثى
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>العنوان:</th>
                                            <td>{{ $archivedUser->address ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>الحالة:</th>
                                            <td>
                                                @if($archivedUser->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">تاريخ الأرشفة:</th>
                                            <td>{{ $archivedUser->archived_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>أرشف بواسطة:</th>
                                            <td>{{ $archivedUser->archivedByUser?->name ?? '-' }}</td>
                                        </tr>
                                        @if($archivedUser->archive_reason)
                                        <tr>
                                            <th>سبب الأرشفة:</th>
                                            <td>{{ $archivedUser->archive_reason }}</td>
                                        </tr>
                                        @endif
                                        @if($archivedUser->restored_at)
                                        <tr>
                                            <th>تاريخ الاستعادة:</th>
                                            <td>{{ $archivedUser->restored_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>استعاد بواسطة:</th>
                                            <td>{{ $archivedUser->restoredByUser?->name ?? '-' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>آخر تسجيل دخول:</th>
                                            <td>{{ $archivedUser->last_login_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>عنوان IP الأخير:</th>
                                            <td>{{ $archivedUser->last_login_ip ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ إنشاء السجل:</th>
                                            <td>{{ $archivedUser->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>آخر تحديث:</th>
                                            <td>{{ $archivedUser->updated_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                @if(!$archivedUser->restored_at)
                                <form action="{{ route('admin.archived-users.restore', $archivedUser->id) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('هل أنت متأكد من استعادة هذا المستخدم؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-undo me-1"></i> استعادة المستخدم
                                    </button>
                                </form>
                                @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    هذا المستخدم تم استعادته بالفعل في {{ $archivedUser->restored_at->format('Y-m-d H:i:s') }}
                                </div>
                                @endif

                                <form action="{{ route('admin.archived-users.destroy', $archivedUser->id) }}" 
                                      method="POST" class="d-inline ms-2" 
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل نهائياً؟ هذا الإجراء لا يمكن التراجع عنه.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-1"></i> حذف نهائي
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
