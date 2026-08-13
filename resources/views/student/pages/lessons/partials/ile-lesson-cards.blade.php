{{-- بطاقات اختبارات تفاعلية للدرس — يتوقع $experiences و $ileAttempts (keyBy experience id) --}}
@php $bare = !empty($bare); @endphp
@if(($experiences ?? collect())->count() > 0)
    @unless($bare)
    <div class="card mt-3">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="bi bi-joystick me-2"></i>
                اختبارات تفاعلية لهذا الدرس
            </h6>
        </div>
        <div class="card-body">
    @endunless
            <div class="row {{ $bare ? 'g-3 p-3' : '' }}">
                @foreach($experiences as $experience)
                    @php
                        $attempt = ($ileAttempts ?? collect())->get($experience->id);
                        $hasAttempt = $attempt !== null;
                        $passed = $hasAttempt && (bool) $attempt->passed;
                        $qCount = $experience->questionsCount();
                    @endphp
                    <div class="col-md-6 mb-3">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="bg-success-transparent rounded d-flex align-items-center justify-content-center me-3"
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-joystick text-success fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 fw-semibold">{{ $experience->title }}</h6>
                                            @if($hasAttempt)
                                                <span class="badge bg-{{ $passed ? 'success' : 'danger' }}">
                                                    <i class="bi bi-{{ $passed ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                    {{ number_format((float) $attempt->percentage, 1) }}%
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-circle me-1"></i>
                                                    لم يتم البدء
                                                </span>
                                            @endif
                                        </div>
                                        @if($experience->description)
                                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($experience->description, 60) }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge bg-success-transparent text-success">
                                                <i class="bi bi-question-circle me-1"></i>
                                                {{ $qCount }} سؤال
                                            </span>
                                            <span class="badge bg-info-transparent text-info">
                                                <i class="bi bi-joystick me-1"></i>
                                                تفاعلي
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('learning-experiences.show', $experience) }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-play-circle me-1"></i>
                                                {{ $hasAttempt ? 'إعادة اللعب' : 'بدء الاختبار' }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
    @unless($bare)
        </div>
    </div>
    @endunless
@endif
