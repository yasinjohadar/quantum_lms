@extends('admin.layouts.master')

@section('page-title')
    إضافة وسيلة دفع مخصصة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إضافة وسيلة دفع مخصصة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.custom-payment-methods.index') }}">وسائل الدفع المخصصة</a></li>
                        <li class="breadcrumb-item active">إضافة جديدة</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات وسيلة الدفع</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.custom-payment-methods.store') }}" method="POST" id="paymentMethodForm">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">النوع <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">اختر النوع</option>
                                    <option value="iban" {{ old('type') == 'iban' ? 'selected' : '' }}>IBAN</option>
                                    <option value="code" {{ old('type') == 'code' ? 'selected' : '' }}>كود</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>أخرى</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="accountInfoSection">
                                <label class="form-label">معلومات الحساب <span class="text-danger">*</span></label>
                                <div id="accountInfoFields">
                                    <div class="mb-2">
                                        <input type="text" class="form-control" name="account_info[iban]" placeholder="IBAN" value="{{ old('account_info.iban') }}">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" class="form-control" name="account_info[account_name]" placeholder="اسم الحساب" value="{{ old('account_info.account_name') }}">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" class="form-control" name="account_info[bank_name]" placeholder="اسم البنك" value="{{ old('account_info.bank_name') }}">
                                    </div>
                                </div>
                                @error('account_info')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="codePrefixSection" style="display: none;">
                                <label for="code_prefix" class="form-label">بادئة الكود</label>
                                <input type="text" class="form-control" id="code_prefix" name="code_prefix" value="{{ old('code_prefix') }}" placeholder="مثال: PAY-">
                                @error('code_prefix')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">تعليمات الدفع</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="4">{{ old('instructions') }}</textarea>
                                @error('instructions')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requires_receipt" name="requires_receipt" value="1" {{ old('requires_receipt') ? 'checked' : 'checked' }}>
                                    <label class="form-check-label" for="requires_receipt">
                                        يتطلب رفع وصل
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        نشط
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">الترتيب</label>
                                <input type="number" class="form-control" id="order" name="order" value="{{ old('order', 0) }}" min="0">
                                @error('order')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>حفظ
                                </button>
                                <a href="{{ route('admin.custom-payment-methods.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const accountInfoSection = document.getElementById('accountInfoSection');
    const codePrefixSection = document.getElementById('codePrefixSection');
    
    if (type === 'code') {
        codePrefixSection.style.display = 'block';
    } else {
        codePrefixSection.style.display = 'none';
    }
});
</script>
@endpush
@endsection
