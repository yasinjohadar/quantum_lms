@extends('admin.layouts.master')

@section('page-title')
    تعديل سعر الصرف
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تعديل سعر الصرف</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.exchange-rates.index') }}">أسعار الصرف</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات سعر الصرف</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.exchange-rates.update', $exchangeRate->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="from_currency_id" class="form-label">من عملة <span class="text-danger">*</span></label>
                                <select class="form-select" id="from_currency_id" name="from_currency_id" required>
                                    <option value="">اختر العملة</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ old('from_currency_id', $exchangeRate->from_currency_id) == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->code }} - {{ $currency->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_currency_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="to_currency_id" class="form-label">إلى عملة <span class="text-danger">*</span></label>
                                <select class="form-select" id="to_currency_id" name="to_currency_id" required>
                                    <option value="">اختر العملة</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ old('to_currency_id', $exchangeRate->to_currency_id) == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->code }} - {{ $currency->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_currency_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="rate" class="form-label">سعر الصرف <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="rate" name="rate" value="{{ old('rate', $exchangeRate->rate) }}" step="0.000001" min="0.000001" required>
                                @error('rate')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $exchangeRate->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        نشط
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>حفظ
                                </button>
                                <a href="{{ route('admin.exchange-rates.index') }}" class="btn btn-secondary">
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
@endsection
