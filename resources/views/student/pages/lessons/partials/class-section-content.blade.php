{{-- محتوى قسم صف واحد: عنوان + واتساب + شبكة المواد أو رسالة عدم وجود مواد --}}
@php
    $class = $class ?? null;
    $subjects = $subjects ?? collect();
@endphp
@if($class)
    <div class="row">
        @if($subjects->count() > 0)
            <div class="col-12">
            <div class="row row-cols-2 row-cols-lg-5 g-3 subject-cards-row">
            @foreach($subjects as $subject)
                <div class="col">
                    <div class="card custom-card h-100 position-relative text-decoration-none">
                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="stretched-link" aria-label="عرض محتوى {{ $subject->name }}"></a>
                        @if($subject->image)
                            <div class="card-img-top subject-card-image position-relative overflow-hidden">
                                <div class="bg-primary-gradient d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100" id="subject-img-fallback-{{ $subject->id }}" style="display: none;">
                                    <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                                </div>
                                <img src="{{ media_public_url($subject->image) }}" class="position-relative w-100 h-100" style="object-fit: cover;" alt="{{ $subject->name }}"
                                     onerror="this.style.display='none'; document.getElementById('subject-img-fallback-{{ $subject->id }}').style.display='flex';">
                            </div>
                        @else
                            <div class="card-img-top subject-card-image bg-primary-gradient d-flex align-items-center justify-content-center">
                                <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
            </div>
            </div>
        @else
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-0">لا توجد مواد دراسية في هذا الصف</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
