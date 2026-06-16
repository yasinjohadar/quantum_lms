@extends('admin.layouts.master')

@section('page-title')
    تعديل لوحة متصدرين
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'تعديل لوحة متصدرين',
            'gamiIcon' => 'bi-graph-up-arrow',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'لوحة المتصدرين', 'url' => route('admin.leaderboards.index')],
                ['label' => 'تعديل', 'active' => true],
            ],
        ])

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="gami-card gami-form-card">
                    <div class="gami-card__header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="gami-card__header-icon"><i class="bi bi-pencil-square"></i></span>
                            <span>معلومات اللوحة</span>
                        </div>
                    </div>
                    <div class="gami-card__body">
                        <form action="{{ route('admin.leaderboards.update', $leaderboard) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $leaderboard->name) }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required id="leaderboard_type">
                                        <option value="global" {{ old('type', $leaderboard->type) == 'global' ? 'selected' : '' }}>عامة</option>
                                        <option value="course" {{ old('type', $leaderboard->type) == 'course' ? 'selected' : '' }}>كورس</option>
                                        <option value="weekly" {{ old('type', $leaderboard->type) == 'weekly' ? 'selected' : '' }}>أسبوعية</option>
                                        <option value="monthly" {{ old('type', $leaderboard->type) == 'monthly' ? 'selected' : '' }}>شهرية</option>
                                    </select>
                                    @error('type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="subject_field" style="display: {{ old('type', $leaderboard->type) == 'course' ? 'block' : 'none' }};">
                                    <label class="form-label">المادة</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">اختر المادة</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ old('subject_id', $leaderboard->subject_id) == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="period_start_field" style="display: {{ in_array(old('type', $leaderboard->type), ['weekly', 'monthly']) ? 'block' : 'none' }};">
                                    <label class="form-label">تاريخ البداية</label>
                                    <input type="datetime-local" name="period_start" class="form-control" value="{{ old('period_start', $leaderboard->period_start ? $leaderboard->period_start->format('Y-m-d\TH:i') : '') }}">
                                    @error('period_start')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3" id="period_end_field" style="display: {{ in_array(old('type', $leaderboard->type), ['weekly', 'monthly']) ? 'block' : 'none' }};">
                                    <label class="form-label">تاريخ النهاية</label>
                                    <input type="datetime-local" name="period_end" class="form-control" value="{{ old('period_end', $leaderboard->period_end ? $leaderboard->period_end->format('Y-m-d\TH:i') : '') }}">
                                    @error('period_end')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $leaderboard->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="gami-form-actions">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.leaderboards.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
    document.getElementById('leaderboard_type').addEventListener('change', function() {
        const type = this.value;
        const subjectField = document.getElementById('subject_field');
        const periodStartField = document.getElementById('period_start_field');
        const periodEndField = document.getElementById('period_end_field');

        if (type === 'course') {
            subjectField.style.display = 'block';
            periodStartField.style.display = 'none';
            periodEndField.style.display = 'none';
        } else if (type === 'weekly' || type === 'monthly') {
            subjectField.style.display = 'none';
            periodStartField.style.display = 'block';
            periodEndField.style.display = 'block';
        } else {
            subjectField.style.display = 'none';
            periodStartField.style.display = 'none';
            periodEndField.style.display = 'none';
        }
    });
</script>
@endpush
