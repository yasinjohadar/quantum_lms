{{-- يظهر عند إعدادات تمنع الإشعارات الفورية على السيرفر (محليًا غالبًا لا يظهر شيء) --}}
@auth
    @php
        $broadcastDriver = strtolower((string) config('broadcasting.default', ''));
        $viteOk = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    @endphp
    @if ($broadcastDriver !== 'reverb')
        <div class="alert alert-danger alert-dismissible fade show rounded-0 border-0 mb-0 py-2 px-3 small" role="alert">
            <strong>تنبيه (السيرفر):</strong> الإشعارات الفورية لن تصل للمتصفح حتى يكون البث عبر Reverb.
            القيمة الحالية: <code>{{ $broadcastDriver !== '' ? $broadcastDriver : 'فارغ' }}</code> —
            عيّن في <code>.env</code>: <code>BROADCAST_CONNECTION=reverb</code> ثم نفّذ <code>php artisan config:clear</code>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif
    @if (! $viteOk)
        <div class="alert alert-warning alert-dismissible fade show rounded-0 border-0 mb-0 py-2 px-3 small" role="alert">
            <strong>تنبيه (السيرفر):</strong> لا يوجد <code>public/build/manifest.json</code> (ولا ملف <code>hot</code>) —
            لن يُحمّل سكربت Echo ولن تظهر شارة الحالة وأزرار تشغيل/إيقاف الفوري.
            نفّذ <code>npm run build</code> وارفع مجلد <code>public/build</code> كاملاً إلى السيرفر.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif
@endauth
