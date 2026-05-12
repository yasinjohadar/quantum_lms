@extends('admin.layouts.master')

@section('page-title')
    ترحيل التخزين السحابي
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ترحيل الملفات من اللوكال إلى السحابة</h5>
                <p class="text-muted small mb-0">
                    المسار: <strong>الإعدادات</strong> → <strong>التخزين</strong> → <strong>ترحيل للسحابة</strong>
                    — تأكد من ضبط <a href="{{ route('admin.app-storage.configs.index') }}">أماكن التخزين</a>
                    و <a href="{{ route('admin.storage-disk-mappings.index') }}">Disk Mappings</a> قبل الترحيل.
                </p>
            </div>
        </div>

        <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
            <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-1"></i> كيف يعمل النظام؟</h6>
            <ul class="mb-0 small ps-3">
                <li><strong>عرض الصور والملفات:</strong> يُفحص السحابة أولاً؛ إن وُجد الملف هناك يُعرض منها. إن لم يوجد، يُجلب من التخزين المحلي (<code>{{ config('storage.fallback_disk', 'public') }}</code>) — الملفات القديمة على اللوكال تبقى تعمل.</li>
                <li><strong>الرفع الجديد:</strong> يتبع <strong>وضع التخزين</strong> في الإعدادات (مثل <code>cloud_first</code>): يُفضّل السحابة ثم اللوكال عند الفشل.</li>
                <li><strong>هذه الصفحة:</strong> لنسخ الملفات <strong>الموجودة حالياً على السيرفر المحلي</strong> إلى السحابة دفعات (أو دفعة واحدة متزامنة للكميات الصغيرة).</li>
            </ul>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card custom-card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>خيارات الترحيل</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="batch-size" class="form-label small">حجم الدفعة</label>
                            <select id="batch-size" class="form-select form-select-sm">
                                @foreach([10, 25, 50, 100, 200, 500] as $sz)
                                    <option value="{{ $sz }}" @selected($sz === 50)>{{ $sz }} ملفاً</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="sync-mode" title="تنفيذ فوري في نفس الطلب (قد يطول للملفات الكثيرة)">
                            <label class="form-check-label small" for="sync-mode">ترحيل متزامن (بدون طابور)</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input border-danger" type="checkbox" id="delete-local-each">
                            <label class="form-check-label small text-danger" for="delete-local-each">حذف كل ملف محلي بعد رفعه للسحابة مباشرة</label>
                        </div>
                        <p class="text-muted small mt-2 mb-0">لا تفعّل «حذف بعد الرفع» إلا إذا كنت متأكداً من نجاح السحابة. للتنظيف الآمن بعد الترحيل استخدم زر «تنظيف» بعد «تحقق».</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>الملفات المحلية المتاحة للترحيل</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-analysis">
                                <i class="bi bi-arrow-clockwise me-1"></i>تحديث التحليل
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">إجمالي الملفات</div>
                                    <div class="fs-4 fw-bold" id="total-files">{{ $analysis['total_files'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">إجمالي الحجم</div>
                                    <div class="fs-4 fw-bold" id="total-size">{{ $analysis['total_size_formatted'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">أقراص بها ملفات</div>
                                    <div class="fs-4 fw-bold" id="disk-count">{{ count($analysis['disks']) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>الـ Disk</th>
                                        <th>المسارات</th>
                                        <th>الملفات</th>
                                        <th>الحجم</th>
                                        <th style="min-width: 220px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="disks-table">
                                    @foreach($analysis['disks'] as $diskName => $data)
                                    <tr data-disk-row="{{ $diskName }}">
                                        <td><span class="badge bg-primary">{{ $diskName }}</span></td>
                                        <td><code class="small">{{ $data['path_prefix'] ?? implode(', ', $data['prefixes'] ?? []) }}</code></td>
                                        <td>{{ $data['total_files'] }}</td>
                                        <td>{{ $data['total_size_formatted'] }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm flex-wrap">
                                                <button type="button" class="btn btn-outline-success btn-migrate-disk" data-disk="{{ $diskName }}">
                                                    <i class="bi bi-cloud-upload me-1"></i>ترحيل
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-verify-disk" data-disk="{{ $diskName }}">
                                                    <i class="bi bi-check-circle me-1"></i>تحقق
                                                </button>
                                                <button type="button" class="btn btn-outline-warning btn-cleanup-disk" data-disk="{{ $diskName }}">
                                                    <i class="bi bi-trash me-1"></i>تنظيف محلي
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if(empty($analysis['disks']))
                                    <tr id="disks-empty-row">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                            لا توجد ملفات محلية ضمن المسارات المعروفة، أو لا يوجد تخزين سحابي نشط مربوط بهذه الأقراص.
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if($analysis['total_files'] > 0)
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-lg btn-success" id="btn-migrate-all">
                                <i class="bi bi-cloud-upload me-2"></i>ترحيل جميع الأقراص
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>سجل عمليات الترحيل</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">تحديث القائمة</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الـ Disk</th>
                                        <th>الحالة</th>
                                        <th>التقدم</th>
                                        <th>الملفات</th>
                                        <th>التاريخ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches['items'] ?? [] as $batch)
                                    <tr>
                                        <td>{{ $batch->id }}</td>
                                        <td>{{ $batch->name }}</td>
                                        <td><span class="badge bg-info">{{ $batch->disk_name }}</span></td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'running' => 'primary',
                                                    'completed' => 'success',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'warning',
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'قيد الانتظار',
                                                    'running' => 'جاري',
                                                    'completed' => 'مكتمل',
                                                    'failed' => 'فشل',
                                                    'cancelled' => 'ملغي',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$batch->status] ?? 'secondary' }}">
                                                {{ $statusLabels[$batch->status] ?? $batch->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 6px; width: 100px;">
                                                <div class="progress-bar bg-success" style="width: {{ $batch->progress_percentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $batch->progress_percentage }}%</small>
                                        </td>
                                        <td>
                                            <small>
                                                <span class="text-success">{{ $batch->successful_files }}</span> /
                                                <span class="text-danger">{{ $batch->failed_files }}</span> /
                                                <span class="text-muted">{{ $batch->total_files }}</span>
                                            </small>
                                        </td>
                                        <td><small>{{ $batch->started_at?->diffForHumans() ?? '-' }}</small></td>
                                        <td>
                                            @if(in_array($batch->status, ['pending', 'running'], true))
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-batch-status" data-batch-id="{{ $batch->id }}">تقدم</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-batch-cancel" data-batch-id="{{ $batch->id }}">إلغاء</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if(empty($batches['items']))
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">لا توجد عمليات ترحيل سابقة</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verifyModalLabel">نتائج التحقق</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body" id="verify-result">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2 mb-0">جاري التحقق...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="batchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تقدم الدفعة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="batch-modal-body">
                        <p class="small text-muted mb-2">معرّف الدفعة: <strong id="batch-modal-id">—</strong></p>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="batch-modal-progress" style="width: 0%"></div>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0" id="batch-modal-detail" style="max-height: 200px; overflow: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const URLS = @json($migrationUrls);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function migrationPayload() {
        const batchSize = parseInt(document.getElementById('batch-size').value, 10) || 50;
        const syncMode = document.getElementById('sync-mode').checked;
        const deleteLocal = document.getElementById('delete-local-each').checked;
        return {
            batch_size: batchSize,
            async: !syncMode,
            delete_local: deleteLocal
        };
    }

    function startMigration(diskName) {
        const extra = deleteLocalEachConfirm();
        if (extra === false) return;
        if (!confirm('بدء ترحيل جميع ملفات القرص «' + diskName + '» إلى السحابة؟')) return;

        fetch(URLS.migrate, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(Object.assign({ disk_name: diskName }, migrationPayload()))
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('تم بدء الترحيل: ' + data.message + '\nرقم الدفعة: ' + data.batch_id);
                openBatchPoll(data.batch_id);
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'فشل بدء الترحيل');
            }
        })
        .catch(() => alert('حدث خطأ في الاتصال بالخادم'));
    }

    function deleteLocalEachConfirm() {
        const del = document.getElementById('delete-local-each').checked;
        if (!del) return true;
        return confirm('تنبيه: سيتم حذف كل ملف من السيرفر المحلي فور نجاح رفعه للسحابة. هل أنت متأكد؟');
    }

    function startAllMigration() {
        if (deleteLocalEachConfirm() === false) return;
        if (!confirm('بدء ترحيل جميع الأقراص التي لها تخزين سحابي؟ قد يستغرق وقتاً.')) return;

        fetch(URLS.migrateAll, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(migrationPayload())
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'تم بدء الترحيل لجميع الأقراص');
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'فشل الترحيل');
            }
        })
        .catch(() => alert('حدث خطأ في الاتصال'));
    }

    function verifyMigration(diskName) {
        const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
        modal.show();
        document.getElementById('verify-result').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0">جاري التحقق...</p></div>';

        fetch(URLS.verifyBase + '/' + encodeURIComponent(diskName), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success === false || data.error) {
                document.getElementById('verify-result').innerHTML = '<div class="alert alert-danger mb-0">' + (data.error || data.message || 'فشل التحقق') + '</div>';
                return;
            }
            document.getElementById('verify-result').innerHTML =
                '<div class="row text-center g-2">' +
                '<div class="col-6 col-md-3"><div class="p-3 border rounded"><div class="text-muted small">محلي</div><div class="fs-4">' + (data.total_local ?? 0) + '</div></div></div>' +
                '<div class="col-6 col-md-3"><div class="p-3 border rounded bg-success text-white"><div class="small">على السحابة</div><div class="fs-4">' + (data.synced_to_cloud ?? 0) + '</div></div></div>' +
                '<div class="col-6 col-md-3"><div class="p-3 border rounded bg-danger text-white"><div class="small">ناقص</div><div class="fs-4">' + (data.missing_from_cloud ?? 0) + '</div></div></div>' +
                '<div class="col-6 col-md-3"><div class="p-3 border rounded"><div class="text-muted small">نسبة التطابق</div><div class="fs-4">' + (data.sync_percentage ?? 0) + '%</div></div></div>' +
                '</div>' +
                (data.missing_files && data.missing_files.length ? '<div class="mt-3"><h6 class="small">عينة ملفات لم تُرصد على السحابة:</h6><ul class="list-unstyled small mb-0">' +
                    data.missing_files.map(f => '<li><code>' + f + '</code></li>').join('') + '</ul></div>' : '');
        })
        .catch(() => {
            document.getElementById('verify-result').innerHTML = '<div class="text-danger text-center">فشل التحقق</div>';
        });
    }

    function cleanupLocal(diskName) {
        if (!confirm('سيتم حذف الملفات المحلية فقط إذا وُجدت نسخة لها على السحابة. القرص: «' + diskName + '». متابعة؟')) return;

        fetch(URLS.cleanupBase + '/' + encodeURIComponent(diskName), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('تم حذف ' + (data.deleted ?? 0) + ' ملفاً محلياً (كانت لها نسخة على السحابة).');
                location.reload();
            } else {
                alert(data.error || 'فشل التنظيف');
            }
        })
        .catch(() => alert('حدث خطأ'));
    }

    function renderAnalysisTable(analysis) {
        const tbody = document.getElementById('disks-table');
        const disks = analysis.disks || {};
        const keys = Object.keys(disks);
        let html = '';
        keys.forEach(function (diskName) {
            const d = disks[diskName];
            const prefix = d.path_prefix || (d.prefixes || []).join(', ');
            html += '<tr data-disk-row="' + diskName + '">' +
                '<td><span class="badge bg-primary">' + diskName + '</span></td>' +
                '<td><code class="small">' + (prefix || '') + '</code></td>' +
                '<td>' + (d.total_files || 0) + '</td>' +
                '<td>' + (d.total_size_formatted || '') + '</td>' +
                '<td><div class="btn-group btn-group-sm flex-wrap">' +
                '<button type="button" class="btn btn-outline-success btn-migrate-disk" data-disk="' + diskName + '"><i class="bi bi-cloud-upload me-1"></i>ترحيل</button>' +
                '<button type="button" class="btn btn-outline-info btn-verify-disk" data-disk="' + diskName + '"><i class="bi bi-check-circle me-1"></i>تحقق</button>' +
                '<button type="button" class="btn btn-outline-warning btn-cleanup-disk" data-disk="' + diskName + '"><i class="bi bi-trash me-1"></i>تنظيف محلي</button>' +
                '</div></td></tr>';
        });
        if (!keys.length) {
            html = '<tr id="disks-empty-row"><td colspan="5" class="text-center text-muted py-4">لا توجد ملفات للترحيل أو لا يوجد تخزين سحابي مفعّل.</td></tr>';
        }
        tbody.innerHTML = html;
        document.getElementById('total-files').textContent = analysis.total_files ?? 0;
        document.getElementById('total-size').textContent = analysis.total_size_formatted ?? '';
        document.getElementById('disk-count').textContent = keys.length;
        bindDiskButtons();
    }

    function refreshAnalysis() {
        fetch(URLS.analyze, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => renderAnalysisTable(data))
            .catch(() => alert('تعذر تحديث التحليل'));
    }

    function bindDiskButtons() {
        document.querySelectorAll('.btn-migrate-disk').forEach(btn => {
            btn.onclick = () => startMigration(btn.getAttribute('data-disk'));
        });
        document.querySelectorAll('.btn-verify-disk').forEach(btn => {
            btn.onclick = () => verifyMigration(btn.getAttribute('data-disk'));
        });
        document.querySelectorAll('.btn-cleanup-disk').forEach(btn => {
            btn.onclick = () => cleanupLocal(btn.getAttribute('data-disk'));
        });
    }

    let batchPollTimer = null;
    function openBatchPoll(batchId) {
        document.getElementById('batch-modal-id').textContent = batchId;
        const modal = new bootstrap.Modal(document.getElementById('batchModal'));
        modal.show();
        if (batchPollTimer) clearInterval(batchPollTimer);
        const tick = () => {
            fetch(URLS.batchBase + '/' + batchId, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    const d = res.data;
                    const pct = d.progress_percentage ?? 0;
                    document.getElementById('batch-modal-progress').style.width = pct + '%';
                    document.getElementById('batch-modal-detail').textContent = JSON.stringify(d, null, 2);
                    if (d.is_complete) {
                        clearInterval(batchPollTimer);
                        batchPollTimer = null;
                    }
                })
                .catch(() => {});
        };
        tick();
        batchPollTimer = setInterval(tick, 2500);
        document.getElementById('batchModal').addEventListener('hidden.bs.modal', function onHide() {
            document.getElementById('batchModal').removeEventListener('hidden.bs.modal', onHide);
            if (batchPollTimer) clearInterval(batchPollTimer);
            batchPollTimer = null;
        });
    }

    function cancelBatch(batchId) {
        if (!confirm('إلغاء الدفعة #' + batchId + '؟')) return;
        fetch(URLS.batchBase + '/' + batchId + '/cancel', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message || (data.success ? 'تم' : 'فشل'));
            location.reload();
        })
        .catch(() => alert('خطأ في الإلغاء'));
    }

    document.getElementById('btn-refresh-analysis')?.addEventListener('click', refreshAnalysis);
    document.getElementById('btn-migrate-all')?.addEventListener('click', startAllMigration);
    document.querySelectorAll('.btn-batch-status').forEach(btn => {
        btn.addEventListener('click', () => openBatchPoll(btn.getAttribute('data-batch-id')));
    });
    document.querySelectorAll('.btn-batch-cancel').forEach(btn => {
        btn.addEventListener('click', () => cancelBatch(btn.getAttribute('data-batch-id')));
    });
    bindDiskButtons();
})();
</script>
@endpush
