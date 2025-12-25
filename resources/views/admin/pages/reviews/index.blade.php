@extends('admin.layouts.master')

@section('page-title')
    إدارة التقييمات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة التقييمات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">التقييمات</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reviews.settings') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-gear me-1"></i> الإعدادات
                </a>
            </div>
        </div>
        <!-- End Page Header -->

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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">إجمالي التقييمات</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-primary-transparent">
                                <i class="fe fe-star fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">قيد المراجعة</p>
                                <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-warning-transparent">
                                <i class="fe fe-clock fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">معتمدة</p>
                                <h4 class="mb-0 text-success">{{ $stats['approved'] }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-success-transparent">
                                <i class="fe fe-check-circle fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">متوسط التقييم</p>
                                <h4 class="mb-0">{{ number_format($stats['average_rating'], 1) }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-info-transparent">
                                <i class="fe fe-bar-chart-2 fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">قائمة التقييمات</h5>
                        </div>
                        
                        <form method="GET" action="{{ route('admin.reviews.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label class="form-label small mb-1 d-block">البحث</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                            
                            <div style="min-width: 140px;">
                                <label class="form-label small mb-1 d-block">الحالة</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">كل الحالات</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمدة</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                                </select>
                            </div>

                            <div style="min-width: 130px;">
                                <label class="form-label small mb-1 d-block">النوع</label>
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">كل الأنواع</option>
                                    <option value="subject" {{ request('type') === 'subject' ? 'selected' : '' }}>مواد</option>
                                    <option value="class" {{ request('type') === 'class' ? 'selected' : '' }}>صفوف</option>
                                </select>
                            </div>

                            <div style="min-width: 160px;">
                                <label class="form-label small mb-1 d-block">المادة</label>
                                <select name="subject_id" class="form-select form-select-sm">
                                    <option value="">كل المواد</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="min-width: 160px;">
                                <label class="form-label small mb-1 d-block">الصف</label>
                                <select name="class_id" class="form-select form-select-sm">
                                    <option value="">كل الصفوف</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="min-width: 100px;">
                                <label class="form-label small mb-1 d-block">التقييم</label>
                                <select name="rating" class="form-select form-select-sm">
                                    <option value="">الكل</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> مسح
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        @if($reviews->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </th>
                                            <th>#</th>
                                            <th>المستخدم</th>
                                            <th>المقيَّم</th>
                                            <th>التقييم</th>
                                            <th>العنوان</th>
                                            <th>الحالة</th>
                                            <th>مفيد</th>
                                            <th>التاريخ</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reviews as $review)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="review_ids[]" value="{{ $review->id }}" class="form-check-input review-checkbox">
                                                </td>
                                                <td>{{ $review->id }}</td>
                                                <td>
                                                    @if($review->is_anonymous)
                                                        <span class="text-muted">مجهول</span>
                                                    @else
                                                        {{ $review->user->name ?? 'مستخدم محذوف' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-transparent">{{ $review->reviewable_type === 'App\Models\Subject' ? 'مادة' : 'صف' }}</span>
                                                    {{ $review->reviewable->name ?? 'غير معروف' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fe fe-star {{ $i <= $review->rating ? 'text-warning fill' : 'text-muted' }}"></i>
                                                        @endfor
                                                        <span class="ms-2">{{ $review->rating }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ Str::limit($review->title ?? 'بدون عنوان', 30) }}</td>
                                                <td>
                                                    @if($review->status === 'approved')
                                                        <span class="badge bg-success-transparent">معتمد</span>
                                                    @elseif($review->status === 'rejected')
                                                        <span class="badge bg-danger-transparent">مرفوض</span>
                                                    @else
                                                        <span class="badge bg-warning-transparent">قيد المراجعة</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-transparent">{{ $review->is_helpful_count }}</span>
                                                </td>
                                                <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-info" title="عرض">
                                                            <i class="fe fe-eye"></i>
                                                        </a>
                                                        @if($review->status === 'pending')
                                                            <button type="button" class="btn btn-sm btn-success" title="موافقة" onclick="approveReview({{ $review->id }})">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $review->id }}" title="رفض">
                                                                <i class="fe fe-x"></i>
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $review->id }}" title="حذف">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($reviews->count() > 0)
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                                            <i class="fe fe-check me-1"></i> موافقة على المحدد
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()">
                                            <i class="fe fe-x me-1"></i> رفض المحدد
                                        </button>
                                    </div>
                                    <div>
                                        {{ $reviews->links() }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-inbox fs-48 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد تقييمات</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals خارج الجدول -->
@foreach($reviews as $review)
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal{{ $review->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST">
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal{{ $review->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <div class="avatar avatar-xl bg-danger-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                                <i class="fe fe-trash-2 fs-1 text-danger"></i>
                            </div>
                        </div>
                        <h5 class="modal-title mb-3">تأكيد الحذف</h5>
                        <p class="text-muted mb-4">هل أنت متأكد من حذف هذا التقييم؟ لا يمكن التراجع عن هذه العملية.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fe fe-x-circle me-1"></i> إلغاء
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-trash-2 me-1"></i> حذف
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulk-reject-form" method="POST" action="{{ route('admin.reviews.bulk-reject') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">رفض التقييمات المحددة</h5>
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

<!-- Hidden Forms for actions -->
<form id="approve-form" method="POST" style="display: none;">
    @csrf
</form>

<form id="bulk-approve-form" method="POST" action="{{ route('admin.reviews.bulk-approve') }}" style="display: none;">
    @csrf
</form>

@endsection

@section('js')
<script>
    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';

    // Select All
    document.getElementById('select-all')?.addEventListener('change', function() {
        document.querySelectorAll('.review-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Approve single review
    function approveReview(reviewId) {
        if (confirm('هل أنت متأكد من الموافقة على هذا التقييم؟')) {
            const form = document.getElementById('approve-form');
            form.action = '/admin/reviews/' + reviewId + '/approve';
            form.submit();
        }
    }

    // Bulk approve
    function bulkApprove() {
        const selected = document.querySelectorAll('.review-checkbox:checked');
        if (selected.length === 0) {
            alert('يرجى تحديد تقييم واحد على الأقل');
            return;
        }

        if (confirm(`هل أنت متأكد من الموافقة على ${selected.length} تقييم؟`)) {
            const form = document.getElementById('bulk-approve-form');
            
            // Clear previous inputs
            form.querySelectorAll('input[name="review_ids[]"]').forEach(input => input.remove());
            
            // Add selected IDs
            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'review_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
            
            form.submit();
        }
    }

    // Bulk reject
    function bulkReject() {
        const selected = document.querySelectorAll('.review-checkbox:checked');
        if (selected.length === 0) {
            alert('يرجى تحديد تقييم واحد على الأقل');
            return;
        }

        const form = document.getElementById('bulk-reject-form');
        
        // Clear previous inputs
        form.querySelectorAll('input[name="review_ids[]"]').forEach(input => input.remove());
        
        // Add selected IDs
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'review_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });

        new bootstrap.Modal(document.getElementById('bulkRejectModal')).show();
    }
</script>
@endsection
