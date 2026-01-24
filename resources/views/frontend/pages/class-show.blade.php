@extends('frontend.layouts.master')

@section('content')

<!-- Class Show Section Start -->
<section class="class-show-section">
    <!-- Class Header - Full Width -->
    <div class="class-header mb-5">
        <div class="container">
            <div class="class-header-content">
                <h1 class="class-header-title">{{ $class->name }}</h1>
                @if($class->stage)
                    <p class="class-header-stage">
                        <i class="fa-solid fa-layer-group"></i>
                        {{ $class->stage->name }}
                    </p>
                @endif
                @if($class->description)
                    <p class="class-header-description">{{ $class->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="container">

        <!-- Subjects Section -->
        <div class="subjects-section">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="section-title">المواد الدراسية</h2>
                    <p class="section-description">اختر المادة المناسبة لك وابدأ التعلم</p>
                </div>
            </div>
            
            <div class="row">
                @forelse($subjects as $subject)
                    <div class="col-lg-3 col-md-6 col-12 mb-4">
                        <div class="class-card">
                            <div class="class-card-image">
                                @if($subject['image'])
                                    <img src="{{ asset('storage/' . $subject['image']) }}" alt="{{ $subject['name'] }}" class="img-fluid">
                                @else
                                    <div class="class-card-placeholder">
                                        <i class="fa-solid fa-book"></i>
                                    </div>
                                @endif
                                
                                <!-- Students Oval -->
                                @if($subject['enrolled_students_count'] > 0)
                                    <div class="students-oval">
                                        <div class="students-avatars">
                                            @foreach($subject['enrolled_students'] as $student)
                                                <div class="student-avatar">
                                                    @if($student['avatar'])
                                                        <img src="{{ asset('storage/' . $student['avatar']) }}" alt="{{ $student['name'] }}">
                                                    @else
                                                        <div class="avatar-placeholder">
                                                            {{ strtoupper(mb_substr($student['name'], 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <span class="students-count">+ {{ $subject['enrolled_students_count'] }} طالب</span>
                                    </div>
                                @endif
                            </div>
                            <div class="class-card-body">
                                <h3 class="class-card-title">{{ $subject['name'] }}</h3>
                                
                                <!-- Price Section -->
                                <div class="class-card-price">
                                    @if($subject['is_free'] || $subject['price'] == 0)
                                        <div class="price-free-wrapper">
                                            <span class="price-free">مجاني</span>
                                        </div>
                                    @else
                                        <div class="price-content">
                                            <div class="price-current">
                                                <span class="price-amount">{{ number_format($subject['price'], 2) }}</span>
                                                <span class="price-currency">{{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}</span>
                                            </div>
                                            @if($subject['old_price'] > $subject['price'])
                                                <span class="price-old">
                                                    {{ number_format($subject['old_price'], 2) }} {{ $subject['currency']->symbol ?? $subject['currency']->code ?? '' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <a href="#" class="class-card-btn enroll-btn">
                                    سجل الآن
                                    <i class="fa-solid fa-angles-left ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <p class="text-muted">لا توجد مواد متاحة حالياً</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
<!-- Class Show Section End -->

@endsection
