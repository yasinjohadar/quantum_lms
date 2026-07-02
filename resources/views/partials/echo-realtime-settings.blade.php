@auth
    @php
        $echoViteAvailable = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    @endphp
    <div class="card border mb-4" id="echo-realtime-status-wrap">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="mb-1 fw-semibold">
                        <i class="fe fe-zap me-1 text-warning"></i>
                        الإشعارات الفورية
                    </h6>
                    <p class="mb-0 text-muted small">
                        تصل الإشعارات تلقائياً عند تفعيل النظام. يمكنك إيقاف الاتصال المباشر مؤقتاً من هنا.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if (! $echoViteAvailable)
                        <span class="badge rounded-pill bg-danger-transparent text-danger border">واجهة غير مبنية — تواصل مع الإدارة</span>
                    @else
                        <span class="badge rounded-pill bg-secondary-transparent text-secondary border" id="echo-realtime-status">جاري التحميل…</span>
                        <div class="btn-group btn-group-sm" role="group" id="echo-realtime-actions">
                            <button type="button" class="btn btn-outline-success d-none" id="echo-realtime-on" title="تشغيل الاتصال الفوري">تشغيل</button>
                            <button type="button" class="btn btn-outline-danger d-none" id="echo-realtime-off" title="إيقاف الاتصال الفوري">إيقاف</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endauth
