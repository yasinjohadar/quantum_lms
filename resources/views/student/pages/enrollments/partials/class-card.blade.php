@php
    $isPending = isset($pendingClassIdSet[$class->id]);
    $joinableCount = $class->joinable_subjects_count ?? $class->subjects()->where('is_active', true)->count();
    $isPaid = $class->classJoinRequiresPayment();
    $freeCount = $class->getFreeSubjectsCount();
@endphp
<div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3 mb-md-4">
    <article class="enrollment-class-card">
        <div class="enrollment-class-card__media">
            <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="d-block h-100 text-decoration-none" tabindex="-1" aria-hidden="true">
                @if($class->image)
                    <img src="{{ media_public_url($class->image) }}" alt="{{ $class->name }}">
                @else
                    <div class="enrollment-class-card__media-placeholder">
                        <i class="bi bi-building"></i>
                    </div>
                @endif
            </a>
            <span class="enrollment-class-card__badge {{ $isPaid ? 'enrollment-class-card__badge--paid' : 'enrollment-class-card__badge--free' }}"
                  title="{{ $isPaid ? 'صف مدفوع' : 'صف مجاني' }}">
                <i class="bi {{ $isPaid ? 'bi-star-fill' : 'bi-gift-fill' }}" aria-hidden="true"></i>
                {{ $isPaid ? 'مدفوع' : 'مجاني' }}
            </span>
        </div>

        <div class="enrollment-class-card__body">
            <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="enrollment-class-card__title d-block">
                {{ $class->name }}
            </a>
            @if($class->description)
                <p class="enrollment-class-card__desc mb-0">{{ \Illuminate\Support\Str::limit($class->description, 90) }}</p>
            @endif
        </div>

        <div class="enrollment-class-card__actions">
            <span class="enrollment-class-card__meta">
                <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                {{ $joinableCount }} {{ $joinableCount === 1 ? 'مادة' : 'مواد' }}
            </span>
            <span class="enrollment-class-card__meta enrollment-class-card__meta--free">
                <i class="bi bi-gift-fill" aria-hidden="true"></i>
                {{ $freeCount }} {{ $freeCount === 1 ? 'مادة مجانا' : 'مواد مجانا' }}
            </span>

            @if($isPending)
                <button class="btn btn-warning btn-sm enrollment-class-card__btn-pending" type="button" disabled title="طلب انضمام الصف قيد المراجعة">
                    <i class="bi bi-clock me-1"></i>
                    قيد المراجعة
                </button>
            @else
                <button class="btn btn-primary btn-sm enrollment-class-card__btn-join"
                        onclick="requestClassEnrollment({{ $class->id }}, '{{ addslashes($class->name) }}', {{ $isPaid ? 'true' : 'false' }}, this)"
                        type="button">
                    <i class="bi bi-plus-circle me-1"></i>
                    انضم للصف
                </button>
            @endif
        </div>

        <div class="enrollment-class-card__footer">
            <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="enrollment-class-card__footer-link">
                <i class="bi bi-eye" aria-hidden="true"></i>
                عرض المواد والتفاصيل
            </a>
        </div>
    </article>
</div>
