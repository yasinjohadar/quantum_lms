@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التقييم
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل التقييم</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">التقييمات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">معلومات التقييم</div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">المستخدم</label>
                                <p class="mb-0">
                                    @if($review->is_anonymous)
                                        <span class="text-muted">مجهول</span>
                                    @else
                                        <strong>{{ $review->user->name ?? 'مستخدم محذوف' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">المقيَّم</label>
                                <p class="mb-0">
                                    <span class="badge bg-info-transparent">{{ $review->reviewable_type === 'App\Models\Subject' ? 'مادة' : 'صف' }}</span>
                                    <strong>{{ $review->reviewable->name ?? 'غير معروف' }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">التقييم</label>
                                <div class="d-flex align-items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fe fe-star fs-24 {{ $i <= $review->rating ? 'text-warning fill' : 'text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-2 fs-16 fw-bold">{{ $review->rating }} / 5</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">الحالة</label>
                                <p class="mb-0">
                                    @if($review->status === 'approved')
                                        <span class="badge bg-success-transparent">معتمد</span>
                                    @elseif($review->status === 'rejected')
                                        <span class="badge bg-danger-transparent">مرفوض</span>
                                    @else
                                        <span class="badge bg-warning-transparent">قيد المراجعة</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($review->title)
                            <div class="mb-3">
                                <label class="form-label text-muted">العنوان</label>
                                <p class="mb-0"><strong>{{ $review->title }}</strong></p>
                            </div>
                        @endif

                        @if($review->comment)
                            <div class="mb-3">
                                <label class="form-label text-muted">المراجعة</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $review->comment }}
                                </div>
                            </div>
                        @endif

                        @if($review->rejected_reason)
                            <div class="mb-3">
                                <label class="form-label text-muted">سبب الرفض</label>
                                <div class="p-3 bg-danger-transparent rounded">
                                    {{ $review->rejected_reason }}
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">عدد "مفيد"</label>
                                <p class="mb-0"><span class="badge bg-primary-transparent">{{ $review->is_helpful_count }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">تاريخ الإنشاء</label>
                                <p class="mb-0">{{ $review->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>

                        @if($review->approved_by)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">وافق عليه</label>
                                    <p class="mb-0">{{ $review->approver->name ?? 'غير معروف' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">تاريخ الموافقة</label>
                                    <p class="mb-0">{{ $review->approved_at ? $review->approved_at->format('Y-m-d H:i:s') : '-' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($review->votes->count() > 0)
                    <div class="card custom-card mt-3">
                        <div class="card-header">
                            <div class="card-title">الأصوات "مفيد"</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($review->votes as $vote)
                                            <tr>
                                                <td>{{ $vote->user->name ?? 'مستخدم محذوف' }}</td>
                                                <td>{{ $vote->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">الإجراءات</div>
                    </div>
                    <div class="card-body">
                        @if($review->status === 'pending')
                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fe fe-check me-1"></i> الموافقة على التقييم
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fe fe-x me-1"></i> رفض التقييم
                            </button>
                        @endif

                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fe fe-trash-2 me-1"></i> حذف التقييم
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">رفض التقييم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">رفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

