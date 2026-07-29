{{--
  صندوق شرح قابل للطي — الاستخدام:
  @include('partials.gamification-help-box', ['helpKey' => 'admin.badges', 'showQueueStatus' => true])
--}}
@php
    $helpKey = $helpKey ?? null;
    $helpAll = config('gamification_help', []);
    // المفاتيح مثل admin.achievements تحتوي نقطة — لا تستخدم config('...'.$helpKey)
    $help = ($helpKey && is_array($helpAll)) ? ($helpAll[$helpKey] ?? null) : null;
    $showQueueStatus = (bool) ($showQueueStatus ?? false);
    $queueStatus = $showQueueStatus ? app(\App\Services\QueueHealthService::class)->snapshot() : null;
    $storageKey = 'gami_help_collapsed_'.($helpKey ?: 'default');
@endphp

@if($help)
<div class="gami-help-box mb-3" data-gami-help data-storage-key="{{ $storageKey }}">
    <button type="button" class="gami-help-box__toggle" data-gami-help-toggle aria-expanded="true">
        <span class="gami-help-box__toggle-left">
            <i class="bi bi-info-circle"></i>
            <strong>{{ $help['title'] ?? 'دليل الاستخدام' }}</strong>
        </span>
        <span class="gami-help-box__toggle-right">
            <span class="gami-help-box__hint">إظهار / إخفاء</span>
            <i class="bi bi-chevron-up gami-help-box__chevron"></i>
        </span>
    </button>

    <div class="gami-help-box__body" data-gami-help-body>
        @if(!empty($help['summary']))
            <p class="gami-help-box__summary mb-3">{{ $help['summary'] }}</p>
        @endif

        @if($showQueueStatus && $queueStatus)
            @php
                $badgeClass = match ($queueStatus['status']) {
                    'running', 'sync' => 'bg-success',
                    'stopped' => 'bg-danger',
                    default => 'bg-secondary',
                };
            @endphp
            <div class="gami-help-box__queue alert alert-light border mb-3 py-2 px-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <strong><i class="bi bi-hdd-stack me-1"></i>حالة الطابور:</strong>
                    <span class="badge {{ $badgeClass }}">{{ $queueStatus['label'] }}</span>
                    <span class="text-muted small">المحرك: {{ $queueStatus['driver'] }}</span>
                    @if($queueStatus['pending'] !== null)
                        <span class="badge bg-primary-transparent text-primary">معلّق: {{ $queueStatus['pending'] }}</span>
                    @endif
                    @if($queueStatus['failed'] !== null)
                        <span class="badge bg-danger-transparent text-danger">فاشل: {{ $queueStatus['failed'] }}</span>
                    @endif
                    @if($queueStatus['stuck'] !== null && $queueStatus['stuck'] > 0)
                        <span class="badge bg-warning text-dark">عالق: {{ $queueStatus['stuck'] }}</span>
                    @endif
                </div>
                <div class="small text-muted mb-1">{{ $queueStatus['detail'] }}</div>
                <div class="small text-muted">{{ $queueStatus['gamification_note'] }}</div>
            </div>
        @endif

        <div class="row g-3">
            @if(!empty($help['usage']))
                <div class="col-md-4">
                    <h6 class="gami-help-box__section-title"><i class="bi bi-list-check me-1"></i>طريقة الاستخدام</h6>
                    <ul class="gami-help-box__list mb-0">
                        @foreach($help['usage'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(!empty($help['auto']))
                <div class="col-md-4">
                    <h6 class="gami-help-box__section-title text-success"><i class="bi bi-lightning-charge me-1"></i>تلقائي</h6>
                    <ul class="gami-help-box__list mb-0">
                        @foreach($help['auto'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(!empty($help['manual']))
                <div class="col-md-4">
                    <h6 class="gami-help-box__section-title text-primary"><i class="bi bi-hand-index me-1"></i>يدوي</h6>
                    <ul class="gami-help-box__list mb-0">
                        @foreach($help['manual'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if(!empty($help['notes']))
            <div class="mt-3 small text-muted">
                @foreach($help['notes'] as $note)
                    <div><i class="bi bi-exclamation-circle me-1"></i>{{ $note }}</div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
    .gami-help-box {
        border: 1px solid rgba(14, 165, 233, 0.25);
        border-radius: 12px;
        background: rgba(14, 165, 233, 0.06);
        overflow: hidden;
    }
    .gami-help-box__toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.1rem;
        border: 0;
        background: transparent;
        text-align: start;
        color: inherit;
        cursor: pointer;
    }
    .gami-help-box__toggle-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .gami-help-box__toggle-right {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: #64748b;
        font-size: 0.8rem;
        white-space: nowrap;
    }
    .gami-help-box__chevron { transition: transform .2s ease; }
    .gami-help-box.is-collapsed .gami-help-box__chevron { transform: rotate(180deg); }
    .gami-help-box.is-collapsed .gami-help-box__body { display: none; }
    .gami-help-box__body { padding: 0 1.1rem 1.1rem; }
    .gami-help-box__summary { margin: 0; color: #334155; line-height: 1.6; }
    .gami-help-box__section-title { font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem; }
    .gami-help-box__list {
        padding-inline-start: 1.1rem;
        margin: 0;
        color: #475569;
        font-size: 0.875rem;
        line-height: 1.65;
    }
    .gami-help-box__list li + li { margin-top: 0.25rem; }
</style>

<script>
(function () {
    document.querySelectorAll('[data-gami-help]').forEach(function (box) {
        if (box.dataset.bound === '1') return;
        box.dataset.bound = '1';
        var key = box.getAttribute('data-storage-key');
        var toggle = box.querySelector('[data-gami-help-toggle]');
        var collapsed = false;
        try { collapsed = localStorage.getItem(key) === '1'; } catch (e) {}
        // أول زيارة: مفتوح. بعد الطي يُحفظ.
        if (collapsed) {
            box.classList.add('is-collapsed');
            toggle.setAttribute('aria-expanded', 'false');
        }
        toggle.addEventListener('click', function () {
            var nowCollapsed = !box.classList.contains('is-collapsed');
            box.classList.toggle('is-collapsed', nowCollapsed);
            toggle.setAttribute('aria-expanded', nowCollapsed ? 'false' : 'true');
            try { localStorage.setItem(key, nowCollapsed ? '1' : '0'); } catch (e) {}
        });
    });
})();
</script>
@endif
