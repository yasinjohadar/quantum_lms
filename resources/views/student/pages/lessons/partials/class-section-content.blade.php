{{-- محتوى قسم صف واحد: عنوان + واتساب + شبكة المواد أو رسالة عدم وجود مواد --}}
@php
    $class = $class ?? null;
    $subjects = $subjects ?? collect();
@endphp
@if($class)
    <div class="row">
        <div class="col-12 mb-3">
            <h5 class="fw-semibold">
                <i class="bi bi-book-half me-2 text-primary"></i>
                المواد الدراسية في {{ $class->name }}
                @if($class->stage)
                    <small class="text-muted">({{ $class->stage->name }})</small>
                @endif
            </h5>
            @if($class->whatsapp_group_url)
                <a href="{{ $class->whatsapp_group_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm mt-2">
                    <i class="fa-brands fa-whatsapp me-1"></i>
                    انضم لمجموعة واتساب لهذا الصف
                </a>
            @endif
        </div>
        @if($subjects->count() > 0)
            @foreach($subjects as $subject)
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                    <div class="card custom-card h-100 position-relative text-decoration-none">
                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="stretched-link" aria-label="عرض محتوى {{ $subject->name }}"></a>
                        @if($subject->image)
                            <div class="card-img-top subject-card-image position-relative overflow-hidden">
                                <div class="bg-primary-gradient d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100" id="subject-img-fallback-{{ $subject->id }}" style="display: none;">
                                    <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                                </div>
                                <img src="{{ asset('storage/' . $subject->image) }}" class="position-relative w-100 h-100" style="object-fit: cover;" alt="{{ $subject->name }}"
                                     onerror="this.style.display='none'; document.getElementById('subject-img-fallback-{{ $subject->id }}').style.display='flex';">
                            </div>
                        @else
                            <div class="card-img-top subject-card-image bg-primary-gradient d-flex align-items-center justify-content-center">
                                <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <div class="card-body subject-card-body">
                            <h6 class="card-title fw-semibold">{{ $subject->name }}</h6>
                            <span class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>
                                عرض المحتوى
                            </span>
                        </div>
                        @php
                            $enrollment = $subject->enrollments->first();
                        @endphp
                        @if($enrollment && $enrollment->enrolled_at)
                            <div class="card-footer">
                                <span class="card-text text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    تاريخ الانضمام: {{ $enrollment->enrolled_at->format('Y-m-d') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
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
