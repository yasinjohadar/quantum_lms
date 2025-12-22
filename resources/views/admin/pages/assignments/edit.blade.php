@extends('admin.layouts.master')

@section('page-title')
    تعديل واجب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل واجب</h5>
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
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
                    <form method="POST" action="{{ route('admin.assignments.update', $assignment) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">البيانات الأساسية</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $assignment->title) }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $assignment->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">التعليمات</label>
                                <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror" rows="5">{{ old('instructions', $assignment->instructions) }}</textarea>
                                @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-4">الربط</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نوع الربط <span class="text-danger">*</span></label>
                                <select name="assignable_type" id="assignable_type" class="form-select @error('assignable_type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    <option value="App\Models\Subject" {{ old('assignable_type', $assignment->assignable_type) == 'App\Models\Subject' ? 'selected' : '' }}>مادة</option>
                                    <option value="App\Models\Unit" {{ old('assignable_type', $assignment->assignable_type) == 'App\Models\Unit' ? 'selected' : '' }}>وحدة</option>
                                    <option value="App\Models\Lesson" {{ old('assignable_type', $assignment->assignable_type) == 'App\Models\Lesson' ? 'selected' : '' }}>درس</option>
                                </select>
                                @error('assignable_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اختر العنصر <span class="text-danger">*</span></label>
                                <select name="assignable_id" id="assignable_id" class="form-select @error('assignable_id') is-invalid @enderror" required>
                                    <option value="">اختر العنصر</option>
                                </select>
                                @error('assignable_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-4">إعدادات الدرجات والمواعيد</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">الدرجة الكاملة <span class="text-danger">*</span></label>
                                <input type="number" name="max_score" class="form-control @error('max_score') is-invalid @enderror"
                                       value="{{ old('max_score', $assignment->max_score) }}" min="1" step="0.01" required>
                                @error('max_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">موعد التسليم</label>
                                <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date', $assignment->due_date?->format('Y-m-d\TH:i')) }}">
                                @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">عدد المحاولات <span class="text-danger">*</span></label>
                                <input type="number" name="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror"
                                       value="{{ old('max_attempts', $assignment->max_attempts) }}" min="1" max="10" required>
                                @error('max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="allow_late_submission" value="1" class="form-check-input" id="allow_late_submission"
                                           {{ old('allow_late_submission', $assignment->allow_late_submission) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_late_submission">
                                        السماح بالتسليم المتأخر
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="late_penalty_div" style="display: {{ old('allow_late_submission', $assignment->allow_late_submission) ? 'block' : 'none' }};">
                                <label class="form-label">نسبة خصم التأخير (%)</label>
                                <input type="number" name="late_penalty_percentage" class="form-control @error('late_penalty_percentage') is-invalid @enderror"
                                       value="{{ old('late_penalty_percentage', $assignment->late_penalty_percentage) }}" min="0" max="100" step="0.01">
                                @error('late_penalty_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-4">إعدادات الملفات</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">الحد الأقصى لحجم الملف (MB) <span class="text-danger">*</span></label>
                                <input type="number" name="max_file_size" class="form-control @error('max_file_size') is-invalid @enderror"
                                       value="{{ old('max_file_size', $assignment->max_file_size) }}" min="1" max="100" required>
                                @error('max_file_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">الحد الأقصى لعدد الملفات <span class="text-danger">*</span></label>
                                <input type="number" name="max_files_per_submission" class="form-control @error('max_files_per_submission') is-invalid @enderror"
                                       value="{{ old('max_files_per_submission', $assignment->max_files_per_submission) }}" min="1" max="20" required>
                                @error('max_files_per_submission')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">أنواع الملفات المسموحة</label>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @php
                                        $fileTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
                                        $oldTypes = old('allowed_file_types', $assignment->getAllowedFileTypesArray());
                                    @endphp
                                    @foreach($fileTypes as $type)
                                        <div class="form-check">
                                            <input type="checkbox" name="allowed_file_types[]" value="{{ $type }}" 
                                                   class="form-check-input" id="file_type_{{ $type }}"
                                                   {{ in_array($type, $oldTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="file_type_{{ $type }}">
                                                {{ strtoupper($type) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">اتركه فارغاً للسماح بجميع الأنواع</small>
                            </div>

                            <div class="col-12">
                                <h6 class="text-primary mb-3 mt-4">نوع التصحيح</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">نوع التصحيح <span class="text-danger">*</span></label>
                                <select name="grading_type" class="form-select @error('grading_type') is-invalid @enderror" required>
                                    <option value="manual" {{ old('grading_type', $assignment->grading_type) == 'manual' ? 'selected' : '' }}>تصحيح يدوي</option>
                                    <option value="auto" {{ old('grading_type', $assignment->grading_type) == 'auto' ? 'selected' : '' }}>تصحيح تلقائي</option>
                                    <option value="mixed" {{ old('grading_type', $assignment->grading_type) == 'mixed' ? 'selected' : '' }}>مزيج</option>
                                </select>
                                @error('grading_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ
                                </button>
                                <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
    // البيانات المحملة مسبقاً من الـ Server
    const itemsData = {
        'App\\Models\\Subject': {!! $subjectsJson !!},
        'App\\Models\\Unit': {!! $unitsJson !!},
        'App\\Models\\Lesson': {!! $lessonsJson !!}
    };

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, initializing assignment edit form...');
        console.log('Items data:', itemsData);
        
        const assignableTypeSelect = document.getElementById('assignable_type');
        const assignableIdSelect = document.getElementById('assignable_id');
        
        if (!assignableTypeSelect || !assignableIdSelect) {
            console.error('Required elements not found!');
            return;
        }

        // دالة لتحديث قائمة العناصر
        function updateItemsList() {
            const type = assignableTypeSelect.value;
            console.log('Type changed to:', type);
            
            assignableIdSelect.innerHTML = '<option value="">اختر العنصر</option>';
            assignableIdSelect.disabled = !type;

            if (!type) {
                return;
            }

            const items = itemsData[type];
            console.log('Items for type:', items);

            if (items && Array.isArray(items) && items.length > 0) {
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name || item.title;
                    if (item.id == {{ $assignment->assignable_id }}) {
                        option.selected = true;
                    }
                    assignableIdSelect.appendChild(option);
                });
                assignableIdSelect.disabled = false;
                console.log('Options loaded successfully, count:', items.length);
            } else {
                assignableIdSelect.innerHTML = '<option value="">لا توجد عناصر متاحة</option>';
                assignableIdSelect.disabled = true;
                console.warn('No items found for type:', type);
            }
        }

        // تحديث قائمة العناصر عند تغيير نوع الربط
        assignableTypeSelect.addEventListener('change', updateItemsList);

        // إظهار/إخفاء خصم التأخير
        const allowLateSubmission = document.getElementById('allow_late_submission');
        const latePenaltyDiv = document.getElementById('late_penalty_div');
        
        if (allowLateSubmission && latePenaltyDiv) {
            allowLateSubmission.addEventListener('change', function() {
                latePenaltyDiv.style.display = this.checked ? 'block' : 'none';
            });
            
            // تهيئة أولية
            if (allowLateSubmission.checked) {
                latePenaltyDiv.style.display = 'block';
            }
        }

        // تهيئة أولية - تحميل العناصر إذا كان النوع محدد مسبقاً
        if (assignableTypeSelect.value) {
            console.log('Initial type selected:', assignableTypeSelect.value);
            updateItemsList();
        }
    });
</script>
@endpush

