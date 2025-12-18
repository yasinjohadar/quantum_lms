@extends('admin.layouts.master')

@section('page-title')
    إضافة سؤال جديد
@stop

@section('css')
<style>
    .question-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent !important;
    }
    .question-type-card:hover {
        border-color: #6259ca !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(98, 89, 202, 0.3);
    }
    .question-type-card.selected {
        border-color: #6259ca !important;
        background-color: rgba(98, 89, 202, 0.1) !important;
        box-shadow: 0 4px 20px rgba(98, 89, 202, 0.4) !important;
        transform: scale(1.02);
    }
    .question-type-card.selected i {
        transform: scale(1.1);
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
                    <h5 class="page-title fs-21 mb-1">إضافة سؤال جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.questions.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة سؤال جديد</li>
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

    <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" id="questionForm">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                {{-- اختيار نوع السؤال --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-collection me-2"></i> اختر نوع السؤال</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach(\App\Models\Question::TYPES as $key => $value)
                                <div class="col-md-4 col-lg-3">
                                    <div class="question-type-card card p-3 text-center h-100 {{ $selectedType == $key ? 'selected' : '' }}"
                                         data-type="{{ $key }}" style="cursor: pointer;">
                                        <i class="bi {{ \App\Models\Question::TYPE_ICONS[$key] }} fs-2 text-{{ \App\Models\Question::TYPE_COLORS[$key] }} mb-2"></i>
                                        <span class="small fw-medium">{{ $value }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="type" id="questionType" value="{{ $selectedType }}">
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
                            <textarea name="title" class="form-control" rows="3" required 
                                      placeholder="اكتب نص السؤال هنا...">{{ old('title') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">محتوى إضافي (اختياري)</label>
                            <textarea name="content" class="form-control" rows="3" 
                                      placeholder="يمكنك إضافة شرح إضافي أو HTML...">{{ old('content') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">صورة السؤال (اختياري)</label>
                            <input type="file" name="image" class="form-control" accept="image/*" 
                                   onchange="previewImage(this, 'questionImagePreview')">
                            <img id="questionImagePreview" src="#" alt="معاينة" 
                                 class="mt-2 rounded d-none" style="max-width: 200px;">
                        </div>
                    </div>
                </div>

                {{-- خيارات السؤال (للأسئلة ذات الخيارات) --}}
                <div class="card custom-card mb-3" id="optionsCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> خيارات الإجابة</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addOption()">
                            <i class="bi bi-plus-lg me-1"></i> إضافة خيار
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="optionsContainer">
                            {{-- سيتم إضافة الخيارات بواسطة JavaScript --}}
                        </div>
                        <p class="text-muted small mb-0 mt-2" id="optionHint">
                            <i class="bi bi-info-circle me-1"></i>
                            <span id="optionHintText">حدد الإجابة الصحيحة</span>
                        </p>
                    </div>
                </div>

                {{-- حقل الإجابة الرقمية --}}
                <div class="card custom-card mb-3 d-none" id="numericalCard">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-123 me-2"></i> الإجابة الرقمية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="correct_answer" class="form-control" 
                                       value="{{ old('correct_answer') }}" placeholder="مثال: 42">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نسبة التسامح (اختياري)</label>
                                <input type="number" step="0.01" name="tolerance" class="form-control" 
                                       value="{{ old('tolerance', 0) }}" placeholder="مثال: 0.5">
                                <small class="text-muted">الفرق المسموح به عن الإجابة الصحيحة</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- حقول ملء الفراغات --}}
                <div class="card custom-card mb-3 d-none" id="fillBlanksCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-input-cursor me-2"></i> إجابات الفراغات</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addBlank()">
                            <i class="bi bi-plus-lg me-1"></i> إضافة فراغ
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            استخدم <code>[blank]</code> في نص السؤال لتحديد موقع كل فراغ
                        </p>
                        <div id="blanksContainer">
                            <div class="input-group mb-2 blank-item">
                                <span class="input-group-text">فراغ 1</span>
                                <input type="text" name="blank_answers[]" class="form-control" 
                                       placeholder="الإجابة الصحيحة للفراغ الأول">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="case_sensitive" 
                                   id="caseSensitive" {{ old('case_sensitive') ? 'checked' : '' }}>
                            <label class="form-check-label" for="caseSensitive">
                                مطابقة حالة الأحرف (كبيرة/صغيرة)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- شرح الإجابة --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i> شرح الإجابة (اختياري)</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="explanation" class="form-control" rows="3" 
                                  placeholder="اكتب شرحاً للإجابة الصحيحة يظهر بعد الإجابة...">{{ old('explanation') }}</textarea>
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
                                   value="{{ old('default_points', 1) }}" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                            <select name="difficulty" class="form-select" required>
                                @foreach(\App\Models\Question::DIFFICULTIES as $key => $value)
                                    <option value="{{ $key }}" {{ old('difficulty', 'medium') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">التصنيف (اختياري)</label>
                            <input type="text" name="category" class="form-control" 
                                   value="{{ old('category') }}" placeholder="مثال: رياضيات - جبر"
                                   list="categoriesList">
                            <datalist id="categoriesList">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">السؤال نشط</label>
                        </div>
                    </div>
                </div>

                {{-- ربط بالوحدات --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i> ربط بالوحدات (اختياري)</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($preselectedUnit) && $preselectedUnit)
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                سيتم ربط هذا السؤال بالوحدة: <strong>{{ $preselectedUnit->title }}</strong>
                                @if($preselectedUnit->section && $preselectedUnit->section->subject)
                                    <br>
                                    <span class="text-muted">المادة: {{ $preselectedUnit->section->subject->name }}</span>
                                @endif
                            </div>
                        @else
                            <p class="text-muted small mb-2">
                                اترك فارغاً ليكون سؤالاً عاماً
                            </p>
                        @endif
                        <div style="max-height: 250px; overflow-y: auto;">
                            @foreach($units as $unit)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="units[]" 
                                           value="{{ $unit->id }}" id="unit{{ $unit->id }}"
                                           {{ (isset($preselectedUnitId) && $unit->id == $preselectedUnitId) || in_array($unit->id, old('units', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="unit{{ $unit->id }}">
                                        {{ $unit->title }}
                                        @if($unit->section && $unit->section->subject)
                                            <span class="text-muted">
                                                ({{ $unit->section->subject->name }})
                                            </span>
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
                            <i class="bi bi-check-lg me-1"></i> حفظ السؤال
                        </button>
                        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary w-100">
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
console.log('Question create script loaded');

let optionCounter = 0;
let blankCounter = 1;
const currentType = '{{ $selectedType ?? "single_choice" }}';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up question type cards');
    console.log('Current type:', currentType);
    
    // إضافة event listeners لاختيار نوع السؤال
    const cards = document.querySelectorAll('.question-type-card');
    console.log('Found cards:', cards.length);
    
    cards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const type = this.getAttribute('data-type');
            console.log('Card clicked, type:', type);
            selectQuestionType(type);
        });
    });
    
    // إضافة خيارين افتراضيين للأسئلة ذات الخيارات
    if (document.getElementById('optionsCard')) {
        updateFormForType(currentType);
    }
});

function selectQuestionType(type) {
    console.log('selectQuestionType called with:', type);
    
    // إزالة التحديد من الكل
    document.querySelectorAll('.question-type-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    
    // تحديد النوع الجديد
    const selectedCard = document.querySelector('[data-type="' + type + '"]');
    if (selectedCard) {
        selectedCard.classList.add('selected');
        console.log('Selected card for type:', type);
    }
    
    const typeInput = document.getElementById('questionType');
    if (typeInput) {
        typeInput.value = type;
        console.log('Set questionType to:', type);
    }
    
    updateFormForType(type);
}

function updateFormForType(type) {
    console.log('updateFormForType called with:', type);
    
    const optionsCard = document.getElementById('optionsCard');
    const numericalCard = document.getElementById('numericalCard');
    const fillBlanksCard = document.getElementById('fillBlanksCard');
    const optionHintText = document.getElementById('optionHintText');
    
    console.log('Found elements:', {
        optionsCard: !!optionsCard,
        numericalCard: !!numericalCard,
        fillBlanksCard: !!fillBlanksCard,
        optionHintText: !!optionHintText
    });
    
    // إخفاء كل الكروت
    if (optionsCard) optionsCard.classList.add('d-none');
    if (numericalCard) numericalCard.classList.add('d-none');
    if (fillBlanksCard) fillBlanksCard.classList.add('d-none');
    
    // إظهار الكارت المناسب
    var optionTypes = ['single_choice', 'multiple_choice', 'true_false', 'matching', 'ordering', 'drag_drop'];
    
    if (optionTypes.indexOf(type) !== -1) {
        console.log('Showing options card for type:', type);
        if (optionsCard) optionsCard.classList.remove('d-none');
        
        // تحديث النص التوضيحي
        if (optionHintText) {
            switch(type) {
                case 'single_choice':
                    optionHintText.textContent = 'حدد إجابة صحيحة واحدة فقط';
                    break;
                case 'multiple_choice':
                    optionHintText.textContent = 'حدد إجابة أو أكثر صحيحة';
                    break;
                case 'true_false':
                    optionHintText.textContent = 'حدد الإجابة الصحيحة (صح أو خطأ)';
                    break;
                case 'matching':
                    optionHintText.textContent = 'أضف العناصر والأهداف المطابقة لها';
                    break;
                case 'ordering':
                    optionHintText.textContent = 'أدخل العناصر بالترتيب الصحيح';
                    break;
                case 'drag_drop':
                    optionHintText.textContent = 'أضف العناصر القابلة للسحب والإفلات';
                    break;
            }
        }
        
        // إعادة بناء الخيارات حسب النوع
        rebuildOptions(type);
        
    } else if (type === 'numerical') {
        console.log('Showing numerical card');
        if (numericalCard) numericalCard.classList.remove('d-none');
    } else if (type === 'fill_blanks') {
        console.log('Showing fill blanks card');
        if (fillBlanksCard) fillBlanksCard.classList.remove('d-none');
    } else {
        console.log('Type does not need special card:', type);
    }
    // essay و short_answer لا يحتاجان خيارات
}

function rebuildOptions(type) {
    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    optionCounter = 0;
    
    if (type === 'true_false') {
        // إضافة خياري صح وخطأ فقط
        addTrueFalseOptions();
    } else {
        // إضافة خيارين افتراضيين
        addOption();
        addOption();
    }
}

function addTrueFalseOptions() {
    const container = document.getElementById('optionsContainer');
    container.innerHTML = `
        <div class="option-item" id="option0">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="correct_option" value="0" id="correct0">
                <label class="form-check-label fw-semibold" for="correct0">صح (True)</label>
            </div>
            <input type="hidden" name="options[0][content]" value="صح">
        </div>
        <div class="option-item" id="option1">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="correct_option" value="1" id="correct1">
                <label class="form-check-label fw-semibold" for="correct1">خطأ (False)</label>
            </div>
            <input type="hidden" name="options[1][content]" value="خطأ">
        </div>
    `;
    optionCounter = 2;
}

function addOption() {
    const container = document.getElementById('optionsContainer');
    const type = document.getElementById('questionType').value;
    const index = optionCounter++;
    
    let optionHtml = '';
    
    if (type === 'matching') {
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
    } else if (type === 'ordering') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary">${index + 1}</span>
                    <input type="text" name="options[${index}][content]" class="form-control" 
                           placeholder="العنصر رقم ${index + 1}" required>
                    <input type="hidden" name="options[${index}][correct_order]" value="${index + 1}">
                </div>
            </div>
        `;
    } else if (type === 'single_choice') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-start gap-3">
                    <div class="form-check pt-2">
                        <input class="form-check-input" type="radio" name="correct_option" 
                               value="${index}" id="correct${index}" onchange="markCorrect(${index})">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[${index}][content]" class="form-control mb-2" 
                               placeholder="نص الخيار ${index + 1}" required>
                        <input type="hidden" name="options[${index}][is_correct]" id="isCorrect${index}" value="">
                        <input type="text" name="options[${index}][feedback]" class="form-control form-control-sm" 
                               placeholder="ملاحظة عند اختيار هذا الخيار (اختياري)">
                    </div>
                </div>
            </div>
        `;
    } else if (type === 'multiple_choice') {
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-start gap-3">
                    <div class="form-check pt-2">
                        <input class="form-check-input" type="checkbox" name="options[${index}][is_correct]" 
                               value="1" id="correct${index}" onchange="toggleCorrect(${index})">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[${index}][content]" class="form-control mb-2" 
                               placeholder="نص الخيار ${index + 1}" required>
                        <input type="text" name="options[${index}][feedback]" class="form-control form-control-sm" 
                               placeholder="ملاحظة عند اختيار هذا الخيار (اختياري)">
                    </div>
                </div>
            </div>
        `;
    } else {
        // default - drag_drop or others
        optionHtml = `
            <div class="option-item" id="option${index}">
                <button type="button" class="btn btn-sm btn-icon btn-danger-transparent remove-option" 
                        onclick="removeOption(${index})">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="d-flex align-items-start gap-3">
                    <div class="form-check pt-2">
                        <input class="form-check-input" type="checkbox" name="options[${index}][is_correct]" 
                               value="1" id="correct${index}">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="options[${index}][content]" class="form-control" 
                               placeholder="نص الخيار ${index + 1}" required>
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

function markCorrect(index) {
    // إزالة علامة الصحيح من كل الخيارات
    document.querySelectorAll('[id^="isCorrect"]').forEach(input => {
        input.value = '';
    });
    document.querySelectorAll('.option-item').forEach(item => {
        item.classList.remove('correct');
    });
    
    // تحديد الخيار الصحيح
    document.getElementById(`isCorrect${index}`).value = '1';
    document.getElementById(`option${index}`).classList.add('correct');
}

function toggleCorrect(index) {
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
            <input type="text" name="blank_answers[]" class="form-control" 
                   placeholder="الإجابة الصحيحة للفراغ رقم ${blankCounter}">
            <button type="button" class="btn btn-outline-danger" onclick="removeBlank(this)">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `);
}

function removeBlank(btn) {
    btn.closest('.blank-item').remove();
    // إعادة ترقيم الفراغات
    document.querySelectorAll('.blank-item').forEach((item, index) => {
        item.querySelector('.input-group-text').textContent = `فراغ ${index + 1}`;
    });
    blankCounter = document.querySelectorAll('.blank-item').length;
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('d-none');
    }
}
</script>
@stop

