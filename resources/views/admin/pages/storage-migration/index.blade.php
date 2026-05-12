@extends('admin.layouts.master')

@section('title', 'ترحيل التخزين السحابي')

@section('breadcrumb-title', 'ترحيل الملفات إلى السحابة')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
    <li class="breadcrumb-item active">ترحيل الملفات</li>
@endsection

@section('content')
<div class="row">
    {{-- تحليل الملفات المحلية --}}
    <div class="col-12 mb-3">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>الملفات المحلية المتاحة للترحيل</h6>
                <button class="btn btn-sm btn-primary" onclick="refreshAnalysis()">
                    <i class="bi bi-arrow-clockwise me-1"></i>تحديث
                </button>
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
                            <div class="text-muted small">الأقراص النشطة</div>
                            <div class="fs-4 fw-bold">{{ count($analysis['disks']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>الـ Disk</th>
                                <th>المسار</th>
                                <th>الملفات</th>
                                <th>الحجم</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="disks-table">
                            @foreach($analysis['disks'] as $diskName => $data)
                            <tr>
                                <td><span class="badge bg-primary">{{ $diskName }}</span></td>
                                <td><code>{{ $data['path_prefix'] }}</code></td>
                                <td>{{ $data['total_files'] }}</td>
                                <td>{{ $data['total_size_formatted'] }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="startMigration('{{ $diskName }}')">
                                            <i class="bi bi-cloud-upload me-1"></i>ترحيل
                                        </button>
                                        <button class="btn btn-outline-info" onclick="verifyMigration('{{ $diskName }}')">
                                            <i class="bi bi-check-circle me-1"></i>تحقق
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="cleanupLocal('{{ $diskName }}')">
                                            <i class="bi bi-trash me-1"></i>تنظيف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if(empty($analysis['disks']))
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle display-6 d-block mb-2"></i>
                                    لا توجد ملفات محلية أو لم يتم ضبط تخزين سحابي
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($analysis['total_files'] > 0)
                <div class="text-center mt-3">
                    <button class="btn btn-lg btn-success" onclick="startAllMigration()">
                        <i class="bi bi-cloud-upload me-2"></i>ترحيل جميع الملفات إلى السحابة
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- سجل الدفعات --}}
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>سجل عمليات الترحيل</h6>
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
                                            'running' => 'جاري التشغيل',
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
                            </tr>
                            @endforeach
                            @if(empty($batches['items']))
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">لا توجد عمليات ترحيل سابقة</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal التحقق --}}
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">نتائج التحقق</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="verify-result">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">جاري التحقق...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function startMigration(diskName) {
    if (!confirm(`هل تريد ترحيل جميع ملفات "${diskName}" إلى السحابة؟`)) return;

    fetch(`/admin/storage-migration/migrate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ disk_name: diskName, batch_size: 50, async: true })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(`✅ ${data.message} (Batch #${data.batch_id})`);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(`❌ ${data.message}`);
        }
    })
    .catch(() => alert('حدث خطأ أثناء بدء الترحيل'));
}

function startAllMigration() {
    if (!confirm('هل تريد ترحيل جميع الملفات من جميع الأقراص إلى السحابة؟')) return;

    fetch(`/admin/storage-migration/migrate-all`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ batch_size: 50, async: true })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم بدء ترحيل جميع الملفات');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(`❌ ${data.message || 'فشل الترحيل'}`);
        }
    })
    .catch(() => alert('حدث خطأ'));
}

function verifyMigration(diskName) {
    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
    
    document.getElementById('verify-result').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">جاري التحقق...</p></div>';

    fetch(`/admin/storage-migration/verify/${diskName}`)
    .then(r => r.json())
    .then(data => {
        document.getElementById('verify-result').innerHTML = `
            <div class="row text-center">
                <div class="col-3"><div class="p-3 border rounded"><div class="text-muted small">الملفات المحلية</div><div class="fs-4">${data.total_local}</div></div></div>
                <div class="col-3"><div class="p-3 border rounded bg-success text-white"><div class="small">تم ترحيلها</div><div class="fs-4">${data.synced_to_cloud}</div></div></div>
                <div class="col-3"><div class="p-3 border rounded bg-danger text-white"><div class="small">مفقودة</div><div class="fs-4">${data.missing_from_cloud}</div></div></div>
                <div class="col-3"><div class="p-3 border rounded"><div class="text-muted small">النسبة</div><div class="fs-4">${data.sync_percentage}%</div></div></div>
            </div>
            ${data.missing_files && data.missing_files.length > 0 ? `
                <div class="mt-3">
                    <h6>ملفات مفقودة من السحابة:</h6>
                    <ul class="list-unstyled small">
                        ${data.missing_files.map(f => `<li><code>${f}</code></li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        `;
    })
    .catch(() => {
        document.getElementById('verify-result').innerHTML = '<div class="text-danger text-center">فشل التحقق</div>';
    });
}

function cleanupLocal(diskName) {
    if (!confirm(`⚠️ هل تريد حذف الملفات المحلية لـ "${diskName}"؟\nتأكد من اكتمال الترحيل أولاً!`)) return;

    fetch(`/admin/storage-migration/cleanup/${diskName}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(`✅ تم حذف ${data.deleted} ملف محلي`);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(`❌ ${data.error || 'فشل التنظيف'}`);
        }
    })
    .catch(() => alert('حدث خطأ'));
}

function refreshAnalysis() {
    location.reload();
}
</script>
@endpush
