@extends('admin.layouts.master')

@section('page-title')
    تعديل السؤال
@stop

@section('css')
<style>
    .question-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .question-type-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    .question-type-card.selected {
        border-color: var(--primary-color);
        background-color: rgba(var(--primary-rgb), 0.05);
    }
    .option-item {
        background: var(--custom-white);
        border: 1px solid var(--default-border);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        position: relative;
    }
    .option-item.correct {
        border-color: #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }
    .remove-option {
        position: absolute;
        top: 10px;
        left: 10px;
    }
    .matching-pair {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 10px;
        align-items: center;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل السؤال #{{ $question->id }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل السؤال</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>يوجد أخطاء في البيانات:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.questions.update', $question->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                {{-- نوع السؤال (للقراءة فقط) --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-collection me-2"></i> نوع السؤال</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="question-type-card card p-3 text-center selected" style="min-width: 120px;">
                                <i class="bi {{ $question->type_icon }} fs-2 text-{{ $question->type_color }} mb-2"></i>
                                <span class="small fw-medium">{{ $question->type_name }}</span>
                            </div>
                            <div class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                لا يمكن تغيير نوع السؤال بعد الإنشاء
                            </div>
                        </div>
                        <input type="hidden" name="type" value="{{ $question->type }}">
                    </div>
                </div>

                {{-- محتوى السؤال --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i> محتوى السؤال</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                            <textarea name="title" class="form-control" rows="3" required>{{ old('title', $question->title) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">محتوى إضافي (اختياري)</label>
                            <textarea name="content" class="form-control" rows="3">{{ old('content', $question->content) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">صورة السؤال</label>
                            @if($question->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$question->image) }}" 
                                         class="rounded" style="max-width: 200px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_image" 
                                               value="1" id="removeImage">
                                        <label class="form-check-label text-danger small" for="removeImage">
                                            حذف الصورة
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                {{-- خيارات السؤال --}}
                @if($question->has_options)
                    <div class="card custom-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> خيارات الإجابة</h6>
                            @if(!in_array($question->type, ['true_false']))
                                <button type="button" class="btn btn-sm btn-primary" onclick="addOption()">
                                    <i class="bi bi-plus-lg me-1"></i> إضافة خيار
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div id="optionsContainer">
                                @foreach($question->options as $index => $option)
                                    @if($question->type === 'matching')
                                        <div class="option-item" id="option{{ $index }}">
                                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                                                    onclick="removeOption({{ $index }})">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="matching-pair">
                                                <input type="text" name="options[{{ $index }}][content]" class="form-control" 
                                                       value="{{ $option->content }}" placeholder="العنصر" required>
                                                <i class="bi bi-arrow-left-right text-muted"></i>
                                                <input type="text" name="options[{{ $index }}][match_target]" class="form-control" 
                                                       value="{{ $option->match_target }}" placeholder="الهدف المطابق" required>
                                            </div>
                                        </div>
                                    @elseif($question->type === 'ordering')
                                        <div class="option-item" id="option{{ $index }}">
                                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                                                    onclick="removeOption({{ $index }})">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary">{{ $option->correct_order }}</span>
                                                <input type="text" name="options[{{ $index }}][content]" class="form-control" 
                                                       value="{{ $option->content }}" required>
                                                <input type="hidden" name="options[{{ $index }}][correct_order]" value="{{ $option->correct_order }}">
                                            </div>
                                        </div>
                                    @elseif($question->type === 'true_false')
                                        <div class="option-item {{ $option->is_correct ? 'correct' : '' }}" id="option{{ $index }}">
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" 
                                                       value="{{ $index }}" id="correct{{ $index }}" 
                                                       {{ $option->is_correct ? 'checked' : '' }}
                                                       onchange="markCorrectEdit({{ $index }})">
                                                <label class="form-check-label fw-semibold" for="correct{{ $index }}">
                                                    {{ $option->content }}
                                                </label>
                                            </div>
                                            <input type="hidden" name="options[{{ $index }}][content]" value="{{ $option->content }}">
                                            <input type="hidden" name="options[{{ $index }}][is_correct]" 
                                                   id="isCorrect{{ $index }}" value="{{ $option->is_correct ? '1' : '' }}">
                                        </div>
                                    @elseif($question->type === 'single_choice')
                                        <div class="option-item {{ $option->is_correct ? 'correct' : '' }}" id="option{{ $index }}">
                                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                                                    onclick="removeOption({{ $index }})">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="form-check pt-2">
                                                    <input class="form-check-input" type="radio" name="correct_option" 
                                                           value="{{ $index }}" id="correct{{ $index }}" 
                                                           {{ $option->is_correct ? 'checked' : '' }}
                                                           onchange="markCorrectEdit({{ $index }})">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="text" name="options[{{ $index }}][content]" class="form-control mb-2" 
                                                           value="{{ $option->content }}" required>
                                                    <input type="hidden" name="options[{{ $index }}][is_correct]" 
                                                           id="isCorrect{{ $index }}" value="{{ $option->is_correct ? '1' : '' }}">
                                                    <input type="text" name="options[{{ $index }}][feedback]" class="form-control form-control-sm" 
                                                           value="{{ $option->feedback }}" placeholder="ملاحظة عند اختيار هذا الخيار (اختياري)">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="option-item {{ $option->is_correct ? 'correct' : '' }}" id="option{{ $index }}">
                                            <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                                                    onclick="removeOption({{ $index }})">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="form-check pt-2">
                                                    <input class="form-check-input" type="checkbox" name="options[{{ $index }}][is_correct]" 
                                                           value="1" id="correct{{ $index }}" 
                                                           {{ $option->is_correct ? 'checked' : '' }}
                                                           onchange="toggleCorrectEdit({{ $index }})">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="text" name="options[{{ $index }}][content]" class="form-control mb-2" 
                                                           value="{{ $option->content }}" required>
                                                    <input type="text" name="options[{{ $index }}][feedback]" class="form-control form-control-sm" 
                                                           value="{{ $option->feedback }}" placeholder="ملاحظة عند اختيار هذا الخيار (اختياري)">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($question->type === 'numerical')
                    <div class="card custom-card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-123 me-2"></i> الإجابة الرقمية</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="correct_answer" class="form-control" 
                                           value="{{ old('correct_answer', $question->options->first()->content ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">نسبة التسامح</label>
                                    <input type="number" step="0.01" name="tolerance" class="form-control" 
                                           value="{{ old('tolerance', $question->tolerance) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($question->type === 'fill_blanks')
                    <div class="card custom-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-input-cursor me-2"></i> إجابات الفراغات</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addBlank()">
                                <i class="bi bi-plus-lg me-1"></i> إضافة فراغ
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="blanksContainer">
                                @foreach($question->blank_answers ?? [] as $index => $answer)
                                    <div class="input-group mb-2 blank-item">
                                        <span class="input-group-text">فراغ {{ $index + 1 }}</span>
                                        <input type="text" name="blank_answers[]" class="form-control" value="{{ $answer }}">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeBlank(this)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="case_sensitive" 
                                       id="caseSensitive" {{ $question->case_sensitive ? 'checked' : '' }}>
                                <label class="form-check-label" for="caseSensitive">
                                    مطابقة حالة الأحرف
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- شرح الإجابة --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i> شرح الإجابة</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="explanation" class="form-control" rows="3">{{ old('explanation', $question->explanation) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- إعدادات السؤال --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i> إعدادات السؤال</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">الدرجة الافتراضية <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="default_points" class="form-control" 
                                   value="{{ old('default_points', $question->default_points) }}" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                            <select name="difficulty" class="form-select" required>
                                @foreach(\App\Models\Question::DIFFICULTIES as $key => $value)
                                    <option value="{{ $key }}" {{ old('difficulty', $question->difficulty) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">التصنيف</label>
                            <input type="text" name="category" class="form-control" 
                                   value="{{ old('category', $question->category) }}" list="categoriesList">
                            <datalist id="categoriesList">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="isActive" {{ old('is_active', $question->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">السؤال نشط</label>
                        </div>
                    </div>
                </div>

                {{-- ربط بالوحدات --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i> ربط بالوحدات</h6>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 250px; overflow-y: auto;">
                            @php $selectedUnits = old('units', $question->units->pluck('id')->toArray()); @endphp
                            @foreach($units as $unit)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="units[]" 
                                           value="{{ $unit->id }}" id="unit{{ $unit->id }}"
                                           {{ in_array($unit->id, $selectedUnits) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="unit{{ $unit->id }}">
                                        {{ $unit->title }}
                                        @if($unit->section && $unit->section->subject)
                                            <span class="text-muted">({{ $unit->section->subject->name }})</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="card custom-card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.questions.show', $question->id) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i> إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
<script>
let optionCounter = {{ $question->options->count() }};
let blankCounter = {{ count($question->blank_answers ?? []) ?: 1 }};
const questionType = '{{ $question->type }}';

function addOption() {
    const container = document.getElementById('optionsContainer');
    const index = optionCounter++;
    
    let optionHtml = '';
    
    if (questionType === 'matching') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="matching-pair">
                    <input type="text" name="options[${index}][content]" class="form-control" 
                           placeholder="العنصر" required>
                    <i class="bi bi-arrow-left-right text-muted"></i>
                    <input type="text" name="options[${index}][match_target]" class="form-control" 
                           placeholder="الهدف المطابق" required>
                </div>
            </div>
        `;
    } else if (questionType === 'ordering') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary">${index + 1}</span>
                    <input type="text" name="options[${index}][content]" class="form-control" required>
                    <input type="hidden" name="options[${index}][correct_order]" value="${index + 1}">
                </div>
            </div>
        `;
    } else if (questionType === 'single_choice') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-start gap-3">
                    <div class="form-check pt-2">
                        <input class="form-check-input" type="radio" name="correct_option" 
                               value="${index}" id="correct${index}" onchange="markCorrectEdit(${index})">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[${index}][content]" class="form-control mb-2" required>
                        <input type="hidden" name="options[${index}][is_correct]" id="isCorrect${index}" value="">
                        <input type="text" name="options[${index}][feedback]" class="form-control form-control-sm" 
                               placeholder="ملاحظة (اختياري)">
                    </div>
                </div>
            </div>
        `;
    } else {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-start gap-3">
                    <div class="form-check pt-2">
                        <input class="form-check-input" type="checkbox" name="options[${index}][is_correct]" 
                               value="1" id="correct${index}" onchange="toggleCorrectEdit(${index})">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[${index}][content]" class="form-control mb-2" required>
                        <input type="text" name="options[${index}][feedback]" class="form-control form-control-sm" 
                               placeholder="ملاحظة (اختياري)">
                    </div>
                </div>
            </div>
        `;
    }
    
    container.insertAdjacentHTML('beforeend', optionHtml);
}

function removeOption(index) {
    const option = document.getElementById(`option${index}`);
    if (option) {
        option.remove();
    }
}

function markCorrectEdit(index) {
    document.querySelectorAll('[id^="isCorrect"]').forEach(input => {
        input.value = '';
    });
    document.querySelectorAll('.option-item').forEach(item => {
        item.classList.remove('correct');
    });
    
    document.getElementById(`isCorrect${index}`).value = '1';
    document.getElementById(`option${index}`).classList.add('correct');
}

function toggleCorrectEdit(index) {
    const checkbox = document.getElementById(`correct${index}`);
    const optionItem = document.getElementById(`option${index}`);
    
    if (checkbox.checked) {
        optionItem.classList.add('correct');
    } else {
        optionItem.classList.remove('correct');
    }
}

function addBlank() {
    const container = document.getElementById('blanksContainer');
    blankCounter++;
    
    container.insertAdjacentHTML('beforeend', `
        <div class="input-group mb-2 blank-item">
            <span class="input-group-text">فراغ ${blankCounter}</span>
            <input type="text" name="blank_answers[]" class="form-control">
            <button type="button" class="btn btn-outline-danger" onclick="removeBlank(this)">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `);
}

function removeBlank(btn) {
    btn.closest('.blank-item').remove();
    document.querySelectorAll('.blank-item').forEach((item, index) => {
        item.querySelector('.input-group-text').textContent = `فراغ ${index + 1}`;
    });
    blankCounter = document.querySelectorAll('.blank-item').length;
}
</script>
@stop

