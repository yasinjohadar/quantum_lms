@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الرسالة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل الرسالة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.whatsapp-messages.index') }}">رسائل WhatsApp</a></li>
                        <li class="breadcrumb-item active">تفاصيل الرسالة</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات الرسالة</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">ID</th>
                                <td>{{ $message->id }}</td>
                            </tr>
                            <tr>
                                <th>الاتجاه</th>
                                <td>
                                    @if($message->direction === 'inbound')
                                        <span class="badge bg-info">واردة</span>
                                    @else
                                        <span class="badge bg-primary">صادرة</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>المستقبل</th>
                                <td>{{ $message->contact->wa_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Message ID</th>
                                <td>{{ $message->meta_message_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>النوع</th>
                                <td>{{ $message->type }}</td>
                            </tr>
                            <tr>
                                <th>الحالة</th>
                                <td>
                                    @if($message->status === 'sent')
                                        <span class="badge bg-success">مرسل</span>
                                    @elseif($message->status === 'delivered')
                                        <span class="badge bg-info">مستلم</span>
                                    @elseif($message->status === 'read')
                                        <span class="badge bg-primary">مقروء</span>
                                    @elseif($message->status === 'failed')
                                        <span class="badge bg-danger">فشل</span>
                                    @else
                                        <span class="badge bg-warning">في الانتظار</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>الرسالة</th>
                                <td>{{ $message->body ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء</th>
                                <td>{{ $message->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if($message->error)
                                <tr>
                                    <th>خطأ</th>
                                    <td>
                                        <pre class="bg-danger text-white p-2 rounded">{{ json_encode($message->error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </td>
                                </tr>
                            @endif
                        </table>

                        @if($message->direction === 'outbound' && $message->meta_message_id && is_numeric($message->meta_message_id))
                            <button type="button" id="check-delivery-status-btn" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-sync me-1"></i>تحقق من حالة التسليم (Flaxxa)
                            </button>
                            <small class="text-muted d-block mt-1">
                                قبول الطلب من Flaxxa لا يعني بالضرورة وصول الرسالة فعلياً على واتساب — هذا الزر يستعلم مباشرة عن الحالة الفعلية.
                            </small>
                            <div id="delivery-status-result" class="mt-3" style="display: none;"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('check-delivery-status-btn');
    const resultDiv = document.getElementById('delivery-status-result');
    if (!btn || !resultDiv) return;

    btn.addEventListener('click', function() {
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري التحقق...';

        fetch('{{ route("admin.whatsapp-messages.check-delivery-status", $message) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.style.display = 'block';
            if (!data.success) {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0">' + data.message + '</div>';
                return;
            }

            const d = data.data || {};
            const errorText = d.error || d.error_details;
            if (errorText) {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0"><strong>لم يتم التسليم فعلياً.</strong><br>خطأ Meta: ' + errorText + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-success mb-0">لا يوجد خطأ مسجّل من مزوّد الرسائل حتى الآن. القيمة: ' + (d.value || '-') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-danger mb-0">حدث خطأ: ' + error.message + '</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
});
</script>
@endpush
@stop




