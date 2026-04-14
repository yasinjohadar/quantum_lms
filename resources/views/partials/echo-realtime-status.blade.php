@auth
    @php
        $echoViteAvailable = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    @endphp
    @if (! $echoViteAvailable)
        <div class="header-element d-flex align-items-center me-1" id="echo-realtime-status-wrap" title="شغّل npm run build وارفع مجلد public/build إلى السيرفر">
            <span class="badge rounded-pill bg-danger-transparent text-danger border small">واجهة غير مبنية</span>
        </div>
    @else
        <div class="header-element d-flex align-items-center gap-1 me-1 flex-wrap" id="echo-realtime-status-wrap" title="الإشعارات الفورية (WebSocket) — تشغيل/إيقاف من المتصفح">
            <span class="badge rounded-pill bg-secondary-transparent text-secondary border" id="echo-realtime-status">جاري التحميل…</span>
            <div class="btn-group btn-group-sm" role="group" id="echo-realtime-actions" style="font-size: 0.7rem;">
                <button type="button" class="btn btn-outline-success py-0 px-2 d-none" id="echo-realtime-on" title="تشغيل الاتصال الفوري">تشغيل</button>
                <button type="button" class="btn btn-outline-danger py-0 px-2 d-none" id="echo-realtime-off" title="إيقاف الاتصال الفوري">إيقاف</button>
            </div>
        </div>
    @endif
@endauth
