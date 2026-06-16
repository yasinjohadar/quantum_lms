@php
    $gamiIcon = $gamiIcon ?? 'bi-trophy';
    $gamiSubtitle = $gamiSubtitle ?? null;
    $gamiBreadcrumbs = $gamiBreadcrumbs ?? [];
@endphp
<div class="gami-hero my-4">
    <div class="gami-hero__icon">
        <i class="bi {{ $gamiIcon }}"></i>
    </div>
    <div class="gami-hero__content">
        @if(!empty($gamiBreadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    @foreach($gamiBreadcrumbs as $crumb)
                        @if(!empty($crumb['active']))
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h4 class="gami-hero__title">{{ $gamiTitle }}</h4>
        @if($gamiSubtitle)
            <p class="gami-hero__subtitle">{{ $gamiSubtitle }}</p>
        @endif
    </div>
    @if(isset($gamiStatValue))
        <div class="gami-stat-mini">
            <span class="gami-stat-mini__value">{{ is_numeric($gamiStatValue) ? number_format($gamiStatValue) : $gamiStatValue }}</span>
            <span class="gami-stat-mini__label">{{ $gamiStatLabel ?? '' }}</span>
        </div>
    @endif
    @isset($gamiHeroActions)
        <div class="gami-hero__actions">
            {!! $gamiHeroActions !!}
        </div>
    @endisset
</div>
