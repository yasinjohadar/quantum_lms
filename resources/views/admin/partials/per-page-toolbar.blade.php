@if (isset($paginator) && $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
    @php
        $currentPerPage = $paginator->perPage();
        $presetPerPages = isset($presetPerPages) && is_array($presetPerPages) ? $presetPerPages : [25, 30, 40, 50, 75, 100];
        $customPerPageMax = isset($customPerPageMax) && is_numeric($customPerPageMax) ? (int) $customPerPageMax : 100;
        $isPreset = in_array($currentPerPage, $presetPerPages, true);
    @endphp
    <div id="perPageToolbarContainer" class="d-flex flex-wrap align-items-center gap-2">
        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
            <label for="perPageSelect" class="mb-0 small text-muted text-nowrap">عدد السجلات في الصفحة</label>
            <select id="perPageSelect" class="form-select form-select-sm flex-shrink-0" style="width: auto; min-width: 5.5rem;">
                @foreach ($presetPerPages as $n)
                    <option value="{{ $n }}" @selected($currentPerPage === $n)>{{ $n }}</option>
                @endforeach
                <option value="custom" @selected(!$isPreset)>مخصص</option>
            </select>
        </div>
        <div id="perPageCustomWrap" class="align-items-center gap-1 {{ $isPreset ? 'd-none' : 'd-flex' }}">
            <input type="number"
                   id="perPageCustom"
                   class="form-control form-control-sm"
                   style="width: 5.5rem;"
                   min="1"
                   max="{{ $customPerPageMax }}"
                   value="{{ $currentPerPage }}">
            <button type="button" id="applyCustomPerPage" class="btn btn-sm btn-outline-primary">تطبيق</button>
        </div>
    </div>
@endif
