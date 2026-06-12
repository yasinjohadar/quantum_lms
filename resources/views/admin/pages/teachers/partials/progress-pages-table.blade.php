@php
    $showApprovedCol = $showApprovedCol ?? false;
    $compact = $compact ?? false;
@endphp
@if(!empty($pagesProgress))
    <div class="tp-table-wrap">
        <div class="table-responsive">
            <table class="table tp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>المادة</th>
                        <th>الصف</th>
                        @if($showApprovedCol)<th class="text-center">دروس معتمدة</th>@endif
                        <th class="text-center">المطلوب</th>
                        <th class="text-center">المنجز</th>
                        <th class="text-center">المتبقي</th>
                        <th class="text-center" style="min-width: {{ $compact ? '100px' : '130px' }};">النسبة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagesProgress as $row)
                        @php
                            $subj = $row['subject'];
                            $req = (int) ($row['required_pages'] ?? 0);
                            $done = (int) ($row['completed_pages'] ?? 0);
                            $pct = $row['percentage'];
                            $bar = $pct !== null ? min(100, $pct) : 0;
                            $pctClass = $pct === null ? 'muted' : ($pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning'));
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $subj->name }}</span>
                            </td>
                            <td>
                                @if($subj->schoolClass)
                                    <span class="tp-chip tp-chip--class"><i class="bi bi-building"></i> {{ $subj->schoolClass->name }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            @if($showApprovedCol)
                                <td class="text-center">{{ $row['approved_lessons_count'] ?? 0 }}</td>
                            @endif
                            <td class="text-center">{{ $req }}</td>
                            <td class="text-center fw-semibold text-success">{{ $done }}</td>
                            <td class="text-center">{{ $row['remaining_pages'] }}</td>
                            <td>
                                @if($pct !== null)
                                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                                        <span class="tp-pct tp-pct--{{ $pctClass }}">{{ number_format($pct, 1) }}%</span>
                                    </div>
                                    <div class="tp-progress mt-1">
                                        <div class="tp-progress__bar tp-progress__bar--{{ $pctClass === 'success' ? 'success' : ($pctClass === 'info' ? 'info' : 'warning') }}" style="width: {{ $bar }}%;"></div>
                                    </div>
                                @else
                                    <span class="text-muted small">لا هدف</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <p class="text-muted small mb-0">لا توجد مواد مخصصة أو أهداف صفحات.</p>
@endif
