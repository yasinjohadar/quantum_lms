@extends('admin.layouts.master')

@section('page-title')
    اختبار HTML جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4">
            <h5 class="page-title">إنشاء اختبار HTML بالذكاء الاصطناعي</h5>
            <p class="text-muted mb-0">حدد الموضوع بدقة وأنواع الأسئلة، ثم ولّد الصفحة من شاشة التحرير.</p>
        </div>
        <div class="card custom-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ai-html-quizzes.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">موضوع التوليد <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control" value="{{ old('topic') }}" required
                               placeholder="اكتب الموضوع بدقة، مثال: جمع الأعداد الصحيحة حتى 20 فقط">
                        <div class="form-text">سيُمرَّر حرفياً للذكاء الاصطناعي — كل الأسئلة ستدور حول هذا النص فقط.</div>
                        @error('topic')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الأهداف التعليمية</label>
                        <textarea name="objectives" class="form-control" rows="2">{{ old('objectives') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">أنواع الأسئلة <span class="text-danger">*</span></label>
                        <div class="form-text mb-2">اختر نوعاً واحداً أو أكثر — الواجهة تُبنى وفق هذه الأنواع حول الموضوع.</div>
                        <div class="row g-2">
                            @php $oldTypes = old('question_types', ['single_choice']); @endphp
                            @foreach($questionTypes as $key => $meta)
                                <div class="col-12 col-md-6">
                                    <label class="border rounded-3 p-2 d-flex gap-2 align-items-start w-100 h-100" style="cursor:pointer;">
                                        <input type="checkbox" class="form-check-input mt-1" name="question_types[]" value="{{ $key }}"
                                               @checked(in_array($key, $oldTypes, true))>
                                        <span>
                                            <strong class="d-block">{{ $meta['label'] }}</strong>
                                            <small class="text-muted">{{ $meta['hint'] }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('question_types')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error('question_types.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">عدد الأسئلة (3–8)</label>
                            <input type="number" name="question_count" class="form-control" min="3" max="8" value="{{ old('question_count', 5) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الصعوبة</label>
                            <select name="difficulty" class="form-select">
                                <option value="easy" @selected(old('difficulty') === 'easy')>سهل</option>
                                <option value="medium" @selected(old('difficulty', 'medium') === 'medium')>متوسط</option>
                                <option value="hard" @selected(old('difficulty') === 'hard')>صعب</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نموذج الذكاء الاصطناعي</label>
                            <select name="ai_model_id" class="form-select">
                                <option value="">الافتراضي</option>
                                @foreach($aiModels as $model)
                                    <option value="{{ $model->id }}" @selected((string) old('ai_model_id') === (string) $model->id)>
                                        {{ $model->name }} ({{ $model->provider }})@if($model->is_default) ★@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تلميحات التفاعل (اختياري)</label>
                        <textarea name="interaction_hints" class="form-control" rows="2" placeholder="مثال: ألوان زاهية، مؤقت بسيط، مكافآت بصرية...">{{ old('interaction_hints') }}</textarea>
                        <div class="form-text">توجيه إضافي للمظهر/الإيقاع — أنواع الأسئلة أعلاه هي الأساس.</div>
                    </div>
                    <button class="btn btn-primary">إنشاء والانتقال للتحرير</button>
                    <a href="{{ route('admin.ai-html-quizzes.index') }}" class="btn btn-light">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
