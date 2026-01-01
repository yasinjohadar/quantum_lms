@extends('admin.layouts.master')

@section('page-title')
    تعديل الجلسة الحية - {{ $liveSession->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center my-4">
            <h5 class="page-title mb-0">تعديل الجلسة الحية</h5>
            <a href="{{ route('admin.live-sessions.show', $liveSession) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.live-sessions.update', $liveSession) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">عنوان الجلسة <span class="text-danger">*</span></label>
                            <input type="text" name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="عنوان الجلسة"
                                   value="{{ old('title', $liveSession->title) }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="وصف الجلسة">{{ old('description', $liveSession->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">تاريخ ووقت الجلسة <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_at" 
                                   class="form-control @error('scheduled_at') is-invalid @enderror" 
                                   value="{{ old('scheduled_at', $liveSession->scheduled_at->format('Y-m-d\TH:i')) }}" required>
                            @error('scheduled_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">المدة (بالدقائق) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" 
                                   class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   placeholder="60"
                                   value="{{ old('duration_minutes', $liveSession->duration_minutes) }}" 
                                   min="1" max="480" required>
                            @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">المنطقة الزمنية <span class="text-danger">*</span></label>
                            <input type="text" name="timezone" 
                                   class="form-control @error('timezone') is-invalid @enderror" 
                                   placeholder="UTC"
                                   value="{{ old('timezone', $liveSession->timezone) }}" required>
                            @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="scheduled" {{ old('status', $liveSession->status) === 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                <option value="live" {{ old('status', $liveSession->status) === 'live' ? 'selected' : '' }}>جارية</option>
                                <option value="completed" {{ old('status', $liveSession->status) === 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="cancelled" {{ old('status', $liveSession->status) === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ التعديلات
                            </button>
                            <a href="{{ route('admin.live-sessions.show', $liveSession) }}" class="btn btn-secondary">
                                إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




