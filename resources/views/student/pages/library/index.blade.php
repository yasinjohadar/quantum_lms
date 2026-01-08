@extends('student.layouts.master')

@section('page-title')
    المكتبة الرقمية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">المكتبة الرقمية</h5>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="GET" action="{{ route('student.library.index') }}" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category_id" class="form-select">
                                    <option value="">كل التصنيفات</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    @foreach(\App\Models\LibraryItem::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">بحث</button>
                            </div>
                        </form>
                        
                        @if(request()->hasAny(['search', 'category_id', 'type', 'subject_id']))
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="text-muted small">الفلاتر النشطة:</span>
                                    
                                    @if(request('search'))
                                        <span class="badge bg-primary">
                                            بحث: {{ request('search') }}
                                            <a href="{{ route('student.library.index', array_merge(request()->except('search'), ['page' => 1])) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    
                                    @if(request('category_id'))
                                        @php
                                            $selectedCategory = $categories->firstWhere('id', request('category_id'));
                                        @endphp
                                        @if($selectedCategory)
                                            <span class="badge bg-info">
                                                تصنيف: {{ $selectedCategory->name }}
                                                <a href="{{ route('student.library.index', array_merge(request()->except('category_id'), ['page' => 1])) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                            </span>
                                        @endif
                                    @endif
                                    
                                    @if(request('type'))
                                        <span class="badge bg-secondary">
                                            نوع: {{ \App\Models\LibraryItem::TYPES[request('type')] ?? request('type') }}
                                            <a href="{{ route('student.library.index', array_merge(request()->except('type'), ['page' => 1])) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    
                                    @if(request('subject_id'))
                                        @php
                                            $selectedSubject = $subjects->firstWhere('id', request('subject_id'));
                                        @endphp
                                        @if($selectedSubject)
                                            <span class="badge bg-success">
                                                مادة: {{ $selectedSubject->name }}
                                                <a href="{{ route('student.library.index', array_merge(request()->except('subject_id'), ['page' => 1])) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                            </span>
                                        @endif
                                    @endif
                                    
                                    <a href="{{ route('student.library.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> مسح الكل
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($items as $item)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        @if($item->thumbnail)
                            <img src="{{ Storage::disk('public')->url($item->thumbnail) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fe fe-file" style="font-size: 48px; color: #ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->title }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($item->description, 100) }}</p>
                            
                            <div class="mb-2">
                                <span class="badge bg-info">{{ $item->category->name ?? '-' }}</span>
                                <span class="badge bg-secondary">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span>
                                @if($item->is_featured)
                                    <span class="badge bg-warning">مميز</span>
                                @endif
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i> {{ $item->download_count }} تحميل
                                    <i class="fas fa-eye ms-2 me-1"></i> {{ $item->view_count }} مشاهدة
                                </small>
                            </div>

                            @if($item->total_ratings > 0)
                                <div class="mb-2">
                                    <span class="text-warning">★</span> {{ number_format($item->average_rating, 1) }} ({{ $item->total_ratings }})
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <a href="{{ route('student.library.show', $item->id) }}" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                </a>
                                <button type="button" class="btn {{ in_array($item->id, $favoriteIds ?? []) ? 'btn-danger' : 'btn-outline-danger' }} btn-sm toggle-favorite-btn" 
                                        data-item-id="{{ $item->id }}"
                                        data-favorited="{{ in_array($item->id, $favoriteIds ?? []) ? 'true' : 'false' }}">
                                    <i class="{{ in_array($item->id, $favoriteIds ?? []) ? 'fas' : 'far' }} fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        لا توجد عناصر متاحة في المكتبة حالياً.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const isFavorited = this.getAttribute('data-favorited') === 'true';
            const icon = this.querySelector('i');
            
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
                        this.classList.add('btn-danger');
                        this.classList.remove('btn-outline-danger');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.setAttribute('data-favorited', 'false');
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-danger');
                    }
                    
                    // إظهار رسالة نجاح (اختياري)
                    if (data.message) {
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                        alert.style.zIndex = '9999';
                        alert.innerHTML = `
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        document.body.appendChild(alert);
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                    }
                } else {
                    alert(data.message || 'حدث خطأ أثناء تحديث المفضلة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء تحديث المفضلة');
            });
        });
    });
});
</script>
@endpush
@stop

