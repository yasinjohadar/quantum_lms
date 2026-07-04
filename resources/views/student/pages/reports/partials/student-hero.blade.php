@php
    $student = $student ?? null;
@endphp

@if($student)
    <div class="student-reports-hero mb-4">
        <div class="student-reports-hero__avatar">
            @if($student->photo)
                <img src="{{ media_public_url($student->photo) }}" alt="{{ $student->name }}">
            @else
                <div class="student-reports-hero__avatar-fallback">
                    <i class="bi bi-person-fill"></i>
                </div>
            @endif
        </div>
        <div class="student-reports-hero__body">
            <h2 class="student-reports-hero__name">{{ $student->name }}</h2>
            <div class="student-reports-hero__chips">
                @if($student->email)
                    <span class="student-reports-hero__chip">
                        <i class="bi bi-envelope-fill"></i>
                        {{ $student->email }}
                    </span>
                @endif
                @if($student->phone)
                    <span class="student-reports-hero__chip">
                        <i class="bi bi-phone-fill"></i>
                        {{ $student->phone }}
                    </span>
                @endif
                <span class="student-reports-hero__chip">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->format('Y-m-d H:i') }}
                </span>
            </div>
        </div>
    </div>
@endif
