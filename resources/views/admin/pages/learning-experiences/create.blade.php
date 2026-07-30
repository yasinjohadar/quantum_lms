@extends('admin.layouts.master')

@section('page-title')
    تجربة تفاعلية جديدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4">
            <h5 class="page-title">إنشاء تجربة تفاعلية</h5>
        </div>
        <div class="card custom-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.learning-experiences.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وضع التجربة</label>
                        <select name="experience_mode" class="form-select">
                            <option value="classic" @selected(old('experience_mode', 'classic') === 'classic')>كلاسيك — قوالب الأنواع الثابتة</option>
                            <option value="dynamic" @selected(old('experience_mode') === 'dynamic')>ديناميك — كتل عرض + رياضيات + مشاهد (Schema 2.0)</option>
                        </select>
                        <div class="form-text">لا يمكن خلط الوضعين داخل نفس التجربة.</div>
                    </div>
                    <button class="btn btn-primary">إنشاء والانتقال للتحرير</button>
                    <a href="{{ route('admin.learning-experiences.index') }}" class="btn btn-light">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
