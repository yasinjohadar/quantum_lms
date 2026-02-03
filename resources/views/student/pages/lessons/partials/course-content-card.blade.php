@if($sections->count() > 0)
    <div class="card mt-3 content-tree-flat">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="bi bi-folder me-2"></i>
                محتوى المادة: {{ $subject->name }}
            </h6>
        </div>
        <div class="card-body p-2">
            @foreach($sections as $section)
                <div class="mb-3 border rounded p-2">
                    <h5 class="mb-2">
                        <i class="bi bi-folder me-2"></i>
                        {{ $section->title }}
                    </h5>
                    @if($section->description)
                        <p class="text-muted mb-3 small">{{ $section->description }}</p>
                    @endif
                    
                    @if($section->units->count() > 0)
                        <div class="accordion" id="section-{{ $section->id }}-{{ $suffix ?? 'sidebar' }}">
                            @foreach($section->units as $unitIndex => $unit)
                                @php
                                    $containsCurrentLesson = $unit->lessons->contains('id', $lesson->id);
                                @endphp
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="unit-heading-{{ $unit->id }}-{{ $suffix ?? 'sidebar' }}">
                                        <button class="accordion-button {{ ($unitIndex > 0 && !$containsCurrentLesson) ? 'collapsed' : '' }}" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#unit-{{ $unit->id }}-{{ $suffix ?? 'sidebar' }}" 
                                                aria-expanded="{{ ($unitIndex === 0 || $containsCurrentLesson) ? 'true' : 'false' }}">
                                            <i class="bi bi-file-text me-2"></i>
                                            <span class="small">{{ $unit->title }}</span>
                                            <span class="badge bg-secondary ms-2 small">{{ $unit->lessons->count() }} درس</span>
                                        </button>
                                    </h2>
                                    <div id="unit-{{ $unit->id }}-{{ $suffix ?? 'sidebar' }}" 
                                         class="accordion-collapse collapse {{ ($unitIndex === 0 || $containsCurrentLesson) ? 'show' : '' }}" 
                                         data-bs-parent="#section-{{ $section->id }}-{{ $suffix ?? 'sidebar' }}">
                                        <div class="accordion-body px-0 pt-2 pb-2">
                                            @if($unit->description)
                                                <p class="text-muted mb-3 small">{{ $unit->description }}</p>
                                            @endif
                                            
                                            @if($unit->lessons->count() > 0)
                                                <div class="list-group mb-2">
                                                    @foreach($unit->lessons as $unitLesson)
                                                        @php
                                                            $lessonUrl = (isset($lesson_route) && $lesson_route === 'student.lessons.show.folders')
                                                                ? route('student.lessons.show.folders', $unitLesson)
                                                                : route('student.lessons.show', $unitLesson->id);
                                                        @endphp
                                                        <a href="{{ $lessonUrl }}" 
                                                           class="list-group-item list-group-item-action border-0 mb-2 rounded {{ $unitLesson->id === $lesson->id ? 'current-lesson' : '' }} {{ $unitLesson->id === $lesson->id ? 'text-white' : 'text-reset' }}">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1 small">
                                                                        <i class="bi bi-play-circle me-2 fs-5 {{ $unitLesson->id === $lesson->id ? 'text-white' : 'text-primary' }}"></i>
                                                                        {{ $unitLesson->title }}
                                                                    </h6>
                                                                    @if($unitLesson->description)
                                                                        <p class="text-muted mb-0 small" style="font-size: 0.75rem;">{{ \Illuminate\Support\Str::limit($unitLesson->description, 60) }}</p>
                                                                    @endif
                                                                    <div class="mt-2">
                                                                        @if($unitLesson->duration)
                                                                            <span class="badge bg-secondary me-2 small">
                                                                                <i class="bi bi-clock me-1"></i>
                                                                                {{ $unitLesson->formatted_duration }}
                                                                            </span>
                                                                        @endif
                                                                        @if($unitLesson->is_free)
                                                                            <span class="badge bg-success small">مجاني</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @if($unitLesson->id === $lesson->id)
                                                                    <i class="bi bi-check-circle-fill text-white"></i>
                                                                @endif
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted mb-0 small">لا توجد دروس في هذه الوحدة</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
