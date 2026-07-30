@extends('admin.layouts.master')

@section('page-title')
    تحرير اختبار HTML
@stop

@php
    $meta = $quiz->prompt_meta ?? [];
    $selectedTypes = $meta['question_types'] ?? ['single_choice'];
    if (! is_array($selectedTypes)) {
        $selectedTypes = ['single_choice'];
    }
    $statusLabels = [
        'draft' => 'مسودة',
        'review' => 'مراجعة',
        'published' => 'منشور',
        'archived' => 'مؤرشف',
    ];
@endphp

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 my-4">
            <div>
                <h5 class="page-title mb-1">{{ $quiz->title }}</h5>
                <span class="badge bg-secondary">{{ $statusLabels[$quiz->status] ?? $quiz->status }}</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($quiz->hasBundle())
                    <a href="{{ route('ai-html-quizzes.show', $quiz) }}" class="btn btn-success btn-sm" target="_blank">
                        <i class="bi bi-play-fill"></i> تشغيل
                    </a>
                @endif
                <a href="{{ route('admin.ai-html-quizzes.index') }}" class="btn btn-light btn-sm">رجوع</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card custom-card mb-3">
                    <div class="card-header"><strong>توليد بالذكاء الاصطناعي</strong></div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label">الموضوع <span class="text-danger">*</span></label>
                            <input type="text" id="ai-topic" class="form-control" value="{{ $meta['topic'] ?? $quiz->title }}" required>
                            <div class="form-text">يُمرَّر حرفياً — كل الأسئلة حول هذا الموضوع فقط.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">الأهداف</label>
                            <textarea id="ai-objectives" class="form-control" rows="2">{{ $meta['objectives'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">أنواع الأسئلة</label>
                            <div class="row g-1" id="ai-question-types">
                                @foreach($questionTypes as $key => $typeMeta)
                                    <div class="col-12 col-sm-6">
                                        <label class="small d-flex gap-2 align-items-start mb-1">
                                            <input type="checkbox" class="form-check-input mt-1 ai-type-cb" value="{{ $key }}"
                                                   @checked(in_array($key, $selectedTypes, true))>
                                            <span>{{ $typeMeta['label'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">عدد الأسئلة</label>
                                <input type="number" id="ai-count" class="form-control" min="3" max="8" value="{{ $meta['question_count'] ?? 5 }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">الصعوبة</label>
                                <select id="ai-difficulty" class="form-select">
                                    @foreach(['easy'=>'سهل','medium'=>'متوسط','hard'=>'صعب'] as $k=>$v)
                                        <option value="{{ $k }}" @selected(($meta['difficulty'] ?? 'medium') === $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">نموذج AI</label>
                            <select id="ai-model" class="form-select">
                                <option value="">الافتراضي</option>
                                @foreach($aiModels as $model)
                                    <option value="{{ $model->id }}" @selected((string)($meta['ai_model_id'] ?? '') === (string)$model->id)>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تلميحات التفاعل</label>
                            <textarea id="ai-hints" class="form-control" rows="2" placeholder="مثال: أزرار كبيرة، حركات ناعمة، ألوان زاهية...">{{ $meta['interaction_hints'] ?? '' }}</textarea>
                            <div class="form-text">
                                لا يمكن تحميل مكتبات CDN عشوائية (أمان الـ sandbox). الخط <strong>Alexandria</strong> يُحقن تلقائياً — اطلبه في التلميحات/التحسينات مع تصميم قوي بـ CSS/JS فقط.
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" id="btn-ai-generate">
                                <i class="bi bi-stars"></i> توليد
                            </button>
                            <button type="button" class="btn btn-success" id="btn-ai-apply" disabled>
                                <i class="bi bi-check2"></i> اعتماد الحزمة
                            </button>
                        </div>
                        <div id="ai-status" class="small text-muted mt-2"></div>
                        <div id="ai-summary" class="small mt-2"></div>
                    </div>
                </div>

                <div class="card custom-card mb-3">
                    <div class="card-header"><strong>تحسين عبر برومبت</strong></div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            بعد وجود حزمة، اكتب تحسينات محددة دون إعادة التوليد من الصفر. أمثلة جاهزة بالأسفل.
                        </p>
                        <div class="mb-2">
                            <label class="form-label">برومبت التحسين</label>
                            <textarea id="ai-refine-prompt" class="form-control" rows="4" placeholder="مثال: اجعل التصميم أقوى بخط Alexandria، كبّر الأزرار، حسّن المطابقة والسحب، أضف حركات أوضح مع الحفاظ على الإجابات الصحيحة...">{{ $meta['last_refine_prompt'] ?? '' }}</textarea>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-3" id="refine-presets">
                            <button type="button" class="btn btn-sm btn-outline-secondary refine-preset" data-prompt="اجعل التصميم أقوى وأجمل باستخدام خط Alexandria فقط، مع تدرجات وظلال وأزرار أكبر وتسلسل بصري أوضح للأطفال، دون تغيير منطق الإجابات الصحيحة.">تصميم أقوى + Alexandria</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary refine-preset" data-prompt="حسّن عرض كل نوع سؤال ليكون أوضح وأكثر تفاعلية (اختيار، مطابقة، سحب، ذاكرة، فراغ) مع تغذية راجعة بصرية وصوتية أقوى، دون مكتبات خارجية.">تفاعل أفضل للأنواع</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary refine-preset" data-prompt="بسّط النصوص واجعل الواجهة أسرع وأوضح على الجوال مع الحفاظ على نفس الأسئلة والإجابات.">تحسين للجوال</button>
                        </div>
                        <button type="button" class="btn btn-warning" id="btn-ai-refine" @disabled(! $quiz->hasBundle())>
                            <i class="bi bi-magic"></i> تطبيق التحسينات
                        </button>
                        <div id="ai-refine-status" class="small text-muted mt-2"></div>
                    </div>
                </div>

                <div class="card custom-card mb-3">
                    <div class="card-header"><strong>حالة النشر</strong></div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        @foreach(['draft','review','published','archived'] as $st)
                            <form method="POST" action="{{ route('admin.ai-html-quizzes.transition', $quiz) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button class="btn btn-sm {{ $quiz->status === $st ? 'btn-dark' : 'btn-outline-secondary' }}"
                                    @disabled(! $quiz->canTransitionTo($st) && $quiz->status !== $st)
                                    type="submit">
                                    {{ $statusLabels[$st] }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header"><strong>بيانات أساسية</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.ai-html-quizzes.update', $quiz) }}" id="quiz-meta-form">
                            @csrf
                            @method('PUT')
                            <div class="mb-2">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="title" id="field-title" class="form-control" value="{{ old('title', $quiz->title) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $quiz->description) }}</textarea>
                            </div>
                            <input type="hidden" name="topic" id="field-topic" value="{{ $meta['topic'] ?? '' }}">
                            <input type="hidden" name="objectives" id="field-objectives" value="{{ $meta['objectives'] ?? '' }}">
                            <input type="hidden" name="question_count" id="field-count" value="{{ $meta['question_count'] ?? 5 }}">
                            <input type="hidden" name="difficulty" id="field-difficulty" value="{{ $meta['difficulty'] ?? 'medium' }}">
                            <input type="hidden" name="interaction_hints" id="field-hints" value="{{ $meta['interaction_hints'] ?? '' }}">
                            <input type="hidden" name="ai_model_id" id="field-model" value="{{ $meta['ai_model_id'] ?? '' }}">
                            <div class="mb-2">
                                <label class="form-label">HTML</label>
                                <textarea name="bundle_html" id="field-html" class="form-control font-monospace" rows="6">{{ old('bundle_html', $quiz->bundle_html) }}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">CSS</label>
                                <textarea name="bundle_css" id="field-css" class="form-control font-monospace" rows="4">{{ old('bundle_css', $quiz->bundle_css) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">JS</label>
                                <textarea name="bundle_js" id="field-js" class="form-control font-monospace" rows="6">{{ old('bundle_js', $quiz->bundle_js) }}</textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">حفظ</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card custom-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>معاينة (iframe sandbox)</strong>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reload-preview">تحديث</button>
                    </div>
                    <div class="card-body p-0" style="min-height: 640px;">
                        @if($quiz->hasBundle())
                            <iframe
                                id="preview-frame"
                                title="معاينة الاختبار"
                                src="{{ route('admin.ai-html-quizzes.preview', $quiz) }}"
                                sandbox="allow-scripts allow-same-origin"
                                style="width:100%;height:640px;border:0;background:#f8fafc;"
                            ></iframe>
                        @else
                            <div id="preview-empty" class="p-5 text-center text-muted">
                                ولّد حزمة ثم اعتمدها لعرض المعاينة هنا.
                            </div>
                            <iframe
                                id="preview-frame"
                                title="معاينة الاختبار"
                                sandbox="allow-scripts allow-same-origin"
                                style="width:100%;height:640px;border:0;background:#f8fafc;display:none;"
                            ></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = @json(csrf_token());
    const generateUrl = @json(route('admin.ai-html-quizzes.ai.generate', $quiz));
    const refineUrl = @json(route('admin.ai-html-quizzes.ai.refine', $quiz));
    const applyUrl = @json(route('admin.ai-html-quizzes.ai.apply', $quiz));
    const previewUrl = @json(route('admin.ai-html-quizzes.preview', $quiz));

    let pendingBundle = null;

    const statusEl = document.getElementById('ai-status');
    const summaryEl = document.getElementById('ai-summary');
    const refineStatusEl = document.getElementById('ai-refine-status');
    const applyBtn = document.getElementById('btn-ai-apply');
    const refineBtn = document.getElementById('btn-ai-refine');
    const frame = document.getElementById('preview-frame');

    function setStatus(msg, isError) {
        statusEl.textContent = msg || '';
        statusEl.className = 'small mt-2 ' + (isError ? 'text-danger' : 'text-muted');
    }

    function setRefineStatus(msg, isError) {
        refineStatusEl.textContent = msg || '';
        refineStatusEl.className = 'small mt-2 ' + (isError ? 'text-danger' : 'text-muted');
    }

    function selectedQuestionTypes() {
        return Array.from(document.querySelectorAll('.ai-type-cb:checked')).map(function (el) {
            return el.value;
        });
    }

    function fillBundleFields(bundle) {
        pendingBundle = bundle;
        document.getElementById('field-html').value = bundle.html || '';
        document.getElementById('field-css').value = bundle.css || '';
        document.getElementById('field-js').value = bundle.js || '';
        if (bundle.title) {
            document.getElementById('field-title').value = bundle.title;
        }
        summaryEl.textContent = bundle.summary || '';
        applyBtn.disabled = false;
        if (refineBtn) refineBtn.disabled = false;
    }

    document.querySelectorAll('.refine-preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('ai-refine-prompt').value = btn.getAttribute('data-prompt') || '';
        });
    });

    document.getElementById('btn-ai-generate').addEventListener('click', async function () {
        applyBtn.disabled = true;
        pendingBundle = null;
        summaryEl.textContent = '';

        const topic = (document.getElementById('ai-topic').value || '').trim();
        const questionTypes = selectedQuestionTypes();
        if (!topic) {
            setStatus('اكتب الموضوع بدقة قبل التوليد.', true);
            return;
        }
        if (!questionTypes.length) {
            setStatus('اختر نوع سؤال واحداً على الأقل.', true);
            return;
        }

        setStatus('جاري التوليد… قد يستغرق دقيقة.');

        const body = {
            topic: topic,
            objectives: document.getElementById('ai-objectives').value,
            question_count: Number(document.getElementById('ai-count').value) || 5,
            difficulty: document.getElementById('ai-difficulty').value,
            question_types: questionTypes,
            interaction_hints: document.getElementById('ai-hints').value,
            ai_model_id: document.getElementById('ai-model').value || null,
        };

        try {
            const res = await fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'فشل التوليد');
            }
            fillBundleFields(data.bundle);
            setStatus('تم التوليد عبر: ' + (data.model || 'AI') + ' — راجع ثم اعتمد.');
        } catch (e) {
            setStatus(e.message || String(e), true);
        }
    });

    document.getElementById('btn-ai-refine').addEventListener('click', async function () {
        const refinePrompt = (document.getElementById('ai-refine-prompt').value || '').trim();
        if (!refinePrompt) {
            setRefineStatus('اكتب برومبت التحسين أولاً.', true);
            return;
        }
        const html = document.getElementById('field-html').value || '';
        const css = document.getElementById('field-css').value || '';
        const js = document.getElementById('field-js').value || '';
        if (!(html + css + js).trim()) {
            setRefineStatus('لا توجد حزمة للتحسين. ولّد أولاً.', true);
            return;
        }

        applyBtn.disabled = true;
        setRefineStatus('جاري تطبيق التحسينات…');

        try {
            const res = await fetch(refineUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    refine_prompt: refinePrompt,
                    title: document.getElementById('field-title').value,
                    html: html,
                    css: css,
                    js: js,
                    ai_model_id: document.getElementById('ai-model').value || null,
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'فشل التحسين');
            }
            fillBundleFields(data.bundle);
            setRefineStatus('تم التحسين عبر: ' + (data.model || 'AI') + ' — راجع ثم اعتمد الحزمة.');
            setStatus('حزمة محسّنة جاهزة للاعتماد.');
        } catch (e) {
            setRefineStatus(e.message || String(e), true);
        }
    });

    document.getElementById('btn-ai-apply').addEventListener('click', async function () {
        if (!pendingBundle) {
            pendingBundle = {
                title: document.getElementById('field-title').value,
                html: document.getElementById('field-html').value,
                css: document.getElementById('field-css').value,
                js: document.getElementById('field-js').value,
                summary: summaryEl.textContent || '',
            };
        }
        setStatus('جاري الاعتماد…');
        try {
            const res = await fetch(applyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    ...pendingBundle,
                    prompt_meta: {
                        topic: document.getElementById('ai-topic').value.trim(),
                        objectives: document.getElementById('ai-objectives').value,
                        question_count: Number(document.getElementById('ai-count').value) || 5,
                        difficulty: document.getElementById('ai-difficulty').value,
                        question_types: selectedQuestionTypes(),
                        interaction_hints: document.getElementById('ai-hints').value,
                        last_refine_prompt: document.getElementById('ai-refine-prompt').value,
                        ai_model_id: document.getElementById('ai-model').value || null,
                    },
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'فشل الاعتماد');
            }
            setStatus('تم الاعتماد. تحديث المعاينة…');
            const empty = document.getElementById('preview-empty');
            if (empty) empty.style.display = 'none';
            frame.style.display = 'block';
            frame.src = (data.preview_url || previewUrl) + '?t=' + Date.now();
            applyBtn.disabled = true;
            pendingBundle = null;
            setTimeout(function () { window.location.reload(); }, 800);
        } catch (e) {
            setStatus(e.message || String(e), true);
        }
    });

    document.getElementById('btn-reload-preview').addEventListener('click', function () {
        if (frame && frame.src) {
            frame.src = previewUrl + '?t=' + Date.now();
        }
    });
})();
</script>
@endpush
