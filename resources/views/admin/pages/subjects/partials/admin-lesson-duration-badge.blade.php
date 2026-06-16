@props([
    'duration',
    'size' => 'section',
    'title' => 'مجموع مدة الدروس',
])

@once('admin-lesson-duration-styles')
    <style>
        .admin-duration-badge {
            background-color: rgba(var(--warning-rgb), 0.14) !important;
            font-weight: 500;
            line-height: 1.3;
            white-space: nowrap;
        }

        [data-theme-mode="light"] .admin-duration-badge,
        html:not([data-bs-theme="dark"]) .admin-duration-badge {
            color: #b45309 !important;
        }

        .admin-duration-badge--subject {
            font-size: 0.8rem;
        }

        .admin-duration-badge--section {
            font-size: 0.75rem;
        }

        .admin-duration-badge--unit {
            font-size: 0.7rem;
        }
    </style>
@endonce

<span class="badge bg-warning-transparent text-warning admin-duration-badge admin-duration-badge--{{ $size }}" title="{{ $title }}">
    <i class="bi bi-clock me-1" aria-hidden="true"></i>{{ $duration }}
</span>
