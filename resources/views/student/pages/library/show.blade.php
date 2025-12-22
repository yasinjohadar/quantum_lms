@extends('student.layouts.master')

@section('page-title')
    {{ $item->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">{{ $item->title }}</h5>
            <a href="{{ route('student.library.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="mb-3">{{ $item->title }}</h4>
                        
                        <div class="mb-3">
                            <span class="badge bg-info me-2">{{ $item->category->name ?? '-' }}</span>
                            <span class="badge bg-secondary me-2">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span>
                            @if($item->is_featured)
                                <span class="badge bg-warning">مميز</span>
                            @endif
                        </div>

                        @if($item->description)
                            <div class="mb-3">
                                <p>{{ $item->description }}</p>
                            </div>
                        @endif

                        @if($item->tags->count() > 0)
                            <div class="mb-3">
                                <strong>الوسوم:</strong>
                                @foreach($item->tags as $tag)
                                    <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex gap-2 mb-3">
                            @if($item->file_path)
                                <form action="{{ route('student.library.download', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-download me-1"></i> تحميل
                                    </button>
                                </form>
                                <a href="{{ route('student.library.preview', $item->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> معاينة
                                </a>
                            @elseif($item->external_url)
                                <a href="{{ $item->external_url }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> فتح الرابط
                                </a>
                            @endif
                            <button type="button" class="btn btn-outline-danger toggle-favorite-btn" 
                                    data-item-id="{{ $item->id }}"
                                    data-favorited="{{ $isFavorited ? 'true' : 'false' }}">
                                <i class="fas fa-heart {{ $isFavorited ? '' : 'far' }}"></i>
                                <span class="ms-1">
                                    {{ $isFavorited ? 'إزالة من المفضلة' : 'إضافة للمفضلة' }}
                                </span>
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i> {{ $item->download_count }} تحميل
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i> {{ $item->view_count }} مشاهدة
                                </small>
                            </div>
                            <div class="col-md-4">
                                @if($item->total_ratings > 0)
                                    <small>
                                        <span class="text-warning">★</span> {{ number_format($item->average_rating, 1) }} ({{ $item->total_ratings }})
                                    </small>
                                @else
                                    <small class="text-muted">لا يوجد تقييمات</small>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">التقييمات</h5>
                        @if($userRating)
                            <div class="alert alert-info">
                                <strong>تقييمك:</strong> 
                                <span class="text-warning">★</span> {{ $userRating->rating }}
                                @if($userRating->comment)
                                    <p class="mb-0 mt-2">{{ $userRating->comment }}</p>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('student.library.rate', $item->id) }}" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">التقييم</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="">اختر التقييم</option>
                                        <option value="5">5 - ممتاز</option>
                                        <option value="4">4 - جيد جداً</option>
                                        <option value="3">3 - جيد</option>
                                        <option value="2">2 - مقبول</option>
                                        <option value="1">1 - ضعيف</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">تعليق (اختياري)</label>
                                    <textarea name="comment" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">إرسال التقييم</button>
                            </form>
                        @endif

                        <h6 class="mb-3">تقييمات الآخرين</h6>
                        @forelse($item->ratings()->where('user_id', '!=', auth()->id())->latest()->limit(10)->get() as $rating)
                            <div class="mb-3 p-3 border rounded">
                                <strong>{{ $rating->user->name ?? 'مستخدم' }}</strong>
                                <span class="text-warning ms-2">★ {{ $rating->rating }}</span>
                                @if($rating->comment)
                                    <p class="mb-0 mt-2">{{ $rating->comment }}</p>
                                @endif
                                <small class="text-muted">{{ $rating->created_at->diffForHumans() }}</small>
                            </div>
                        @empty
                            <p class="text-muted">لا توجد تقييمات أخرى</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات إضافية</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>المادة:</strong> {{ $item->subject->name ?? 'عام' }}</p>
                        <p><strong>من رفع:</strong> {{ $item->uploader->name ?? '-' }}</p>
                        <p><strong>تاريخ الإضافة:</strong> {{ $item->created_at->format('Y-m-d') }}</p>
                        @if($item->file_path)
                            <p><strong>حجم الملف:</strong> {{ $item->formatted_file_size }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.toggle-favorite-btn');
    if (btn) {
        btn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const isFavorited = this.getAttribute('data-favorited') === 'true';
            const icon = this.querySelector('i');
            const span = this.querySelector('span');
            
            fetch(`/student/library/${itemId}/toggle-favorite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_favorited) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.setAttribute('data-favorited', 'true');
                        if (span) span.textContent = 'إزالة من المفضلة';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.setAttribute('data-favorited', 'false');
                        if (span) span.textContent = 'إضافة للمفضلة';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
</script>
@endpush
@stop

