@props([
    'href',
    'icon',
    'title',
    'subtitle',
    'accent' => 'primary',
    'colClass' => 'col-xl-2 col-lg-3 col-md-4 col-sm-6',
])

<div class="{{ $colClass }}">
    <a href="{{ $href }}" class="dashboard-shortcut dashboard-shortcut--{{ $accent }} h-100 text-decoration-none">
        <div class="dashboard-shortcut__body">
            <div class="dashboard-shortcut__icon">
                <i class="{{ $icon }}"></i>
            </div>
            <h6 class="dashboard-shortcut__title">{{ $title }}</h6>
            <small class="dashboard-shortcut__subtitle">{{ $subtitle }}</small>
            @if(isset($badge) || ! $slot->isEmpty())
                <div class="dashboard-shortcut__extra">
                    {{ $badge ?? '' }}
                    {{ $slot }}
                </div>
            @endif
        </div>
    </a>
</div>
