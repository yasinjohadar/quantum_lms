@php
    $presentation = $presentation ?? [];
    $mode = $presentation['mode'] ?? 'hidden';
    $text = $presentation['text'] ?? '';
    $wrapperClass = $wrapperClass ?? '';
@endphp
@if($mode === 'free')
    <div class="price-free-wrapper {{ $wrapperClass }}">
        <span class="price-free">{{ $text }}</span>
    </div>
@elseif($mode === 'label')
    <div class="price-label-wrapper {{ $wrapperClass }}">
        <span class="price-label">{{ $text }}</span>
    </div>
@elseif($mode === 'amount')
    <div class="price-content {{ $wrapperClass }}">
        <div class="price-current">
            <span class="price-amount">{{ $text }}</span>
            @if(!empty($presentation['currency_symbol']))
                <span class="price-currency">{{ $presentation['currency_symbol'] }}</span>
            @endif
        </div>
    </div>
@elseif($mode === 'hidden' && !empty($showHiddenText))
    <span class="text-muted">{{ $text }}</span>
@endif
