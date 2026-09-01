@extends('admin.layouts.master')

@section('page-title')
    تحرير اختبار تفاعلي
@stop

@push('styles')
<style>
    .ile-edit-page {
        --ile-ink: #0f1c2e;
        --ile-muted: #5b6b7c;
        --ile-line: rgba(15, 28, 46, .08);
        --ile-surface: #ffffff;
        --ile-soft: #f3f6f9;
        --ile-accent: #0d8f7a;
        --ile-accent-2: #e8a838;
        --ile-danger: #d64545;
        --ile-radius: 18px;
        --ile-shadow: 0 10px 30px rgba(15, 28, 46, .06);
    }
    [data-theme-mode=dark] .ile-edit-page {
        --ile-ink: rgba(255, 255, 255, .9);
        --ile-muted: rgba(255, 255, 255, .6);
        --ile-line: rgba(255, 255, 255, .1);
        --ile-surface: #141a26;
        --ile-soft: rgba(255, 255, 255, .05);
        --ile-accent: #17b89b;
        --ile-accent-2: #e8a838;
        --ile-danger: #ef6a6a;
        --ile-shadow: 0 10px 30px rgba(0, 0, 0, .3);
    }
    .ile-edit-page .container-fluid { max-width: 1180px; }

    .ile-hero {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        padding: 1.35rem 1.4rem 1.25rem;
        margin: 1.25rem 0 1.1rem;
        color: #fff;
        background:
            radial-gradient(900px 220px at 100% 0%, rgba(232,168,56,.28), transparent 55%),
            linear-gradient(135deg, #0f1c2e 0%, #164858 48%, #0d8f7a 120%);
        box-shadow: var(--ile-shadow);
    }
    .ile-hero::after {
        content: "";
        position: absolute;
        inset: auto -40px -60px auto;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        pointer-events: none;
    }
    .ile-hero__title {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -.02em;
        margin: 0 0 .55rem;
    }
    .ile-hero__meta { display: flex; flex-wrap: wrap; gap: .45rem; align-items: center; }
    .ile-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 999px;
        padding: .28rem .7rem;
        font-size: .75rem;
        font-weight: 700;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.16);
        color: #fff;
    }
    .ile-chip--live { background: rgba(34,197,94,.22); border-color: rgba(34,197,94,.35); }
    .ile-chip--soft { background: rgba(255,255,255,.1); color: rgba(255,255,255,.88); font-weight: 600; }
    .ile-hero__actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .ile-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
        border-radius: 12px; border: 1px solid transparent;
        padding: .55rem .95rem; font-size: .85rem; font-weight: 700;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        text-decoration: none !important;
    }
    .ile-btn:hover { transform: translateY(-1px); }
    .ile-btn--glass {
        background: rgba(255,255,255,.12); color: #fff; border-color: rgba(255,255,255,.2);
    }
    .ile-btn--glass:hover { background: rgba(255,255,255,.2); color: #fff; }
    .ile-btn--accent {
        background: var(--ile-accent-2); color: #1a1408; border-color: transparent;
        box-shadow: 0 8px 18px rgba(232,168,56,.28);
    }
    .ile-btn--accent:hover { color: #1a1408; filter: brightness(1.04); }
    .ile-btn--solid {
        background: var(--ile-surface); color: var(--ile-ink);
    }
    .ile-btn--solid:hover { color: var(--ile-ink); }
    .ile-btn--ghost {
        background: transparent; color: #fff; border-color: rgba(255,255,255,.28);
    }
    .ile-btn--ghost:hover { background: rgba(255,255,255,.08); color: #fff; }
    .ile-btn--primary {
        background: var(--ile-accent); color: #fff;
        box-shadow: 0 8px 18px rgba(13,143,122,.25);
    }
    .ile-btn--primary:hover { color: #fff; filter: brightness(1.05); }
    .ile-btn--primary:disabled { opacity: .55; transform: none; box-shadow: none; }
    .ile-btn--line {
        background: var(--ile-surface); color: var(--ile-ink); border-color: var(--ile-line);
    }
    .ile-btn--line:hover { border-color: rgba(13,143,122,.35); color: var(--ile-accent); }
    .ile-btn--danger-line {
        background: var(--ile-surface); color: var(--ile-danger); border-color: rgba(214,69,69,.25);
    }

    .ile-panel {
        border: 1px solid var(--ile-line);
        border-radius: var(--ile-radius);
        background: var(--ile-surface);
        box-shadow: var(--ile-shadow);
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .ile-panel__head {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--ile-line);
        background: linear-gradient(180deg, rgba(255,255,255,.03), var(--ile-surface));
    }
    .ile-panel__head h6 {
        margin: 0; font-size: 1rem; font-weight: 800; color: var(--ile-ink);
        display: flex; align-items: center; gap: .45rem;
    }
    .ile-panel__head h6 i { color: var(--ile-accent); }
    .ile-panel__body { padding: 1.15rem; }
    .ile-panel--ai {
        border-color: rgba(13,143,122,.18);
        background:
            linear-gradient(180deg, rgba(13,143,122,.04), transparent 40%),
            var(--ile-surface);
    }
    .ile-panel--ai .ile-panel__head {
        background: linear-gradient(90deg, rgba(13,143,122,.08), rgba(232,168,56,.06));
    }
    .ile-hint {
        color: var(--ile-muted); font-size: .82rem; margin: 0 0 1rem; line-height: 1.55;
    }
    .ile-edit-page .form-label {
        font-weight: 700; font-size: .8rem; color: #334155; margin-bottom: .35rem;
    }
    [data-theme-mode=dark] .ile-edit-page .form-label { color: var(--ile-ink); }
    .ile-edit-page .form-control,
    .ile-edit-page .form-select {
        border-radius: 12px;
        border-color: var(--ile-line);
        padding: .6rem .8rem;
        box-shadow: none;
    }
    .ile-edit-page .form-control:focus,
    .ile-edit-page .form-select:focus {
        border-color: rgba(13,143,122,.45);
        box-shadow: 0 0 0 .2rem rgba(13,143,122,.12);
    }

    .ile-type-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
    .ile-type-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        border: 1px solid var(--ile-line);
        background: var(--ile-soft);
        border-radius: 999px;
        padding: .4rem .75rem;
        font-size: .8rem; font-weight: 650; color: var(--ile-ink);
        cursor: pointer; user-select: none;
        transition: background .15s ease, border-color .15s ease;
    }
    .ile-type-pill:has(input:checked) {
        background: rgba(13,143,122,.12);
        border-color: rgba(13,143,122,.35);
        color: #086655;
    }
    .ile-type-pill input { margin: 0; }

    .ile-status-bar { display: flex; flex-wrap: wrap; gap: .5rem; }
    .ile-status-bar .btn {
        border-radius: 999px; font-weight: 700; padding: .45rem 1rem;
    }

    /* فصل كامل بين طرق توليد الأسئلة: من موضوع نصي، من ملف PDF، من صورة — تابات، كل واحدة بحقولها ونموذجها الخاص */
    .ile-gen-tabs {
        display: flex; flex-wrap: wrap; gap: .3rem;
        border-bottom: 1px solid var(--ile-line);
        margin-bottom: 1rem;
    }
    .ile-gen-tabs .nav-link {
        display: inline-flex; align-items: center; gap: .45rem;
        border: 1px solid transparent; border-bottom: none;
        border-radius: 12px 12px 0 0;
        padding: .6rem 1rem; margin-bottom: -1px;
        font-size: .85rem; font-weight: 700;
        color: var(--ile-muted); background: transparent;
        transition: background .15s ease, color .15s ease;
    }
    .ile-gen-tabs .nav-link:hover { color: var(--ile-ink); }
    .ile-gen-tabs .nav-link.active { color: var(--ile-ink); background: var(--ile-soft); border-color: var(--ile-line); }
    .ile-gen-tabs .nav-link--topic.active i { color: var(--ile-accent); }
    .ile-gen-tabs .nav-link--pdf.active i { color: var(--ile-accent-2); }
    .ile-gen-tabs .nav-link--image.active i { color: #6366f1; }
    .ile-gen-tabs .nav-link--import.active i { color: #0ea5e9; }
    .ile-gen-section {
        border: 1px solid var(--ile-line);
        border-radius: 14px;
        background: var(--ile-soft);
        padding: 1rem;
    }
    .ile-gen-section__desc { color: var(--ile-muted); font-size: .78rem; margin: 0 0 .9rem; line-height: 1.5; }
    .ile-gen-section--topic { border-color: rgba(13,143,122,.25); }
    .ile-gen-section--pdf { border-color: rgba(232,168,56,.3); }
    .ile-gen-section--image { border-color: rgba(99,102,241,.3); }

    .ile-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
        gap: .7rem;
    }
    .ile-type-btn {
        position: relative;
        display: flex; flex-direction: column; align-items: flex-start; gap: .2rem;
        width: 100%; text-align: start;
        border-radius: 14px;
        border: 1px solid var(--ile-line);
        background: linear-gradient(180deg, var(--ile-surface), var(--ile-soft));
        padding: .9rem .95rem .85rem;
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .ile-type-btn::before {
        content: "";
        position: absolute; inset-inline-start: 0; top: 0; bottom: 0; width: 4px;
        background: currentColor; opacity: .85;
    }
    .ile-type-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(15,28,46,.08);
        border-color: rgba(13,143,122,.25);
    }
    .ile-type-btn__name { font-weight: 800; font-size: .92rem; }
    .ile-type-btn__code {
        font-size: .7rem; color: var(--ile-muted); font-family: ui-monospace, monospace;
    }

    .ile-section-bar {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; margin: 1.1rem 0 .75rem;
    }
    .ile-section-bar h6 {
        margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--ile-ink);
    }

    .ile-q-card {
        border: 1px solid var(--ile-line);
        border-radius: 16px;
        overflow: hidden;
        background: var(--ile-surface);
        margin-bottom: .85rem;
        box-shadow: 0 4px 14px rgba(15,28,46,.03);
        transition: box-shadow .15s ease;
    }
    .ile-q-card:hover { box-shadow: 0 8px 22px rgba(15,28,46,.06); }
    .ile-q-card__head {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .9rem 1rem;
        background: linear-gradient(180deg, var(--ile-surface), var(--ile-soft));
        border-bottom: 1px solid transparent;
        cursor: pointer; user-select: none;
    }
    .ile-q-card__body { padding: 1.05rem 1.1rem 1.15rem; border-top: 1px solid var(--ile-line); }
    .ile-badge-type {
        display: inline-flex; align-items: center; gap: .35rem;
        border-radius: 999px; padding: .22rem .7rem;
        font-size: .72rem; font-weight: 800; color: #fff;
    }
    .ile-stem-preview {
        color: var(--ile-muted); font-size: .85rem;
        max-width: min(42vw, 420px);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .ile-sticky-actions {
        position: sticky; bottom: 0; z-index: 20;
        display: flex; flex-wrap: wrap; gap: .55rem; align-items: center;
        background: color-mix(in srgb, var(--ile-surface) 88%, transparent);
        backdrop-filter: blur(12px);
        border: 1px solid var(--ile-line);
        border-radius: 16px 16px 0 0;
        padding: .9rem 1rem;
        margin-top: 1.1rem;
        box-shadow: 0 -8px 24px rgba(15,28,46,.06);
    }

    .ile-edit-page .ile-section-title {
        font-size: .95rem; font-weight: 800; margin-bottom: .75rem; color: var(--ile-ink);
    }
    [x-cloak] { display: none !important; }

    @media (max-width: 767.98px) {
        .ile-hero { padding: 1.1rem; }
        .ile-hero__title { font-size: 1.2rem; }
        .ile-stem-preview { max-width: 46vw; }
    }
</style>
@endpush

@section('content')
@php
    $schema = $experience->schema_json ?? [];
    if (!is_array($schema)) {
        $schema = [];
    }
@endphp
<div class="main-content app-content ile-edit-page"
     x-data="ileEditor(@js($schema), @js($types), @js($blankTemplates), {
        patchUrl: @js(route('admin.learning-experiences.ai.patch', $experience)),
        applyUrl: @js(route('admin.learning-experiences.ai.apply', $experience)),
        generateUrl: @js(route('admin.learning-experiences.ai.generate', $experience)),
        generateApplyUrl: @js(route('admin.learning-experiences.ai.generate-apply', $experience)),
        sourceExtractUrl: @js(route('admin.learning-experiences.ai.source.extract', $experience)),
        sourceGenerateUrl: @js(route('admin.learning-experiences.ai.source.generate', $experience)),
        importApplyUrl: @js(route('admin.learning-experiences.import.apply', $experience)),
        csrf: @js(csrf_token()),
        feedbackPhrases: @js($feedbackPhrases),
     },@js($aiModels->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'provider' => $m->provider, 'is_default' => (bool) $m->is_default, 'supports_vision' => $m->supportsVision()])->values()), @js($blankDynamicTemplates), @js($dynamicInteractionTypes))">
    <div class="container-fluid">

        <div class="ile-hero">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="ile-hero__title">تحرير الاختبار التفاعلي</h1>
                    <div class="ile-hero__meta">
                        <span class="ile-chip ile-chip--live">{{ $experience->status }}</span>
                        <span class="ile-chip" x-text="isDynamic ? 'ديناميك 2.0' : 'كلاسيك 1.0'"></span>
                        <span class="ile-chip" x-text="schema.questions.length + ' سؤال'"></span>
                        <span class="ile-chip ile-chip--soft" x-show="schema.meta?.title" x-text="schema.meta?.title || ''" x-cloak></span>
                    </div>
                </div>
                <div class="ile-hero__actions">
                    <button type="button" class="ile-btn ile-btn--accent" @click="openGeneratePanel = !openGeneratePanel">
                        <i class="bi bi-stars"></i>إنشاء بالذكاء الاصطناعي
                    </button>
                    <button type="button" class="ile-btn ile-btn--glass" @click="requestAiPatch()" :disabled="aiLoading || genLoading">
                        <span x-show="!aiLoading"><i class="bi bi-magic"></i>Improve with AI</span>
                        <span x-show="aiLoading" x-cloak>جاري الاقتراح…</span>
                    </button>
                    <a href="{{ route('learning-experiences.show', $experience) }}" class="ile-btn ile-btn--solid" target="_blank">
                        <i class="bi bi-play-fill"></i>معاينة
                    </a>
                    <a href="{{ route('admin.learning-experiences.index') }}" class="ile-btn ile-btn--ghost">رجوع</a>
                </div>
            </div>
        </div>

        <div class="ile-panel ile-panel--ai" x-show="openGeneratePanel" x-cloak>
            <div class="ile-panel__head">
                <h6><i class="bi bi-stars"></i>إضافة أسئلة</h6>
                <button type="button" class="ile-btn ile-btn--line" style="padding:.35rem .7rem;font-size:.78rem" @click="openGeneratePanel = false">إخفاء</button>
            </div>
            <div class="ile-panel__body">
                <p class="ile-hint">أنشئ أسئلة بالذكاء الاصطناعي من موضوع أو ملف، أو استورد أسئلة جاهزة من ملف — اختر الطريقة من التابات أدناه.</p>

                {{-- تابات مستقلة بالكامل: كل طريقة توليد بحقولها ونموذجها وزر التوليد الخاص بها --}}
                <ul class="ile-gen-tabs" role="tablist">
                    <li>
                        <button type="button" class="nav-link nav-link--topic" :class="activeGenTab === 'topic' ? 'active' : ''" @click="activeGenTab = 'topic'">
                            <i class="bi bi-pencil-square"></i> من موضوع نصي
                        </button>
                    </li>
                    <li>
                        <button type="button" class="nav-link nav-link--pdf" :class="activeGenTab === 'pdf' ? 'active' : ''" @click="activeGenTab = 'pdf'">
                            <i class="bi bi-file-earmark-pdf"></i> من ملف PDF
                        </button>
                    </li>
                    <li>
                        <button type="button" class="nav-link nav-link--image" :class="activeGenTab === 'image' ? 'active' : ''" @click="activeGenTab = 'image'">
                            <i class="bi bi-image"></i> من صورة
                        </button>
                    </li>
                    <li>
                        <button type="button" class="nav-link nav-link--import" :class="activeGenTab === 'import' ? 'active' : ''" @click="activeGenTab = 'import'">
                            <i class="bi bi-file-earmark-arrow-up"></i> استيراد من ملف
                        </button>
                    </li>
                </ul>

                <div>
                    {{-- القسم الأول: توليد من موضوع نصي — يعمل مع أي نموذج نشط --}}
                    <div class="ile-gen-section ile-gen-section--topic" x-show="activeGenTab === 'topic'" x-cloak>
                        <p class="ile-gen-section__desc">يكتب الذكاء الاصطناعي الأسئلة اعتماداً على معرفته العامة بالموضوع. يعمل مع أي نموذج نشط، ولا يحتاج دعم رؤية.</p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">الموضوع *</label>
                                <input type="text" class="form-control" x-model="genTopic.topic" placeholder="مثال: أساسيات الطاقة الشمسية">
                            </div>
                            <div class="col-12">
                                <label class="form-label">الأهداف (اختياري)</label>
                                <input type="text" class="form-control" x-model="genTopic.objectives" placeholder="ما الذي يجب أن يتعلمه الطالب؟">
                            </div>
                            <div class="col-6">
                                <label class="form-label">عدد الأسئلة</label>
                                <input type="number" min="1" max="15" class="form-control" x-model.number="genTopic.count">
                            </div>
                            <div class="col-6">
                                <label class="form-label">الصعوبة</label>
                                <select class="form-select" x-model="genTopic.difficulty">
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">الوضع</label>
                                <select class="form-select" x-model="genTopic.mode">
                                    <option value="replace">استبدال الأسئلة الحالية</option>
                                    <option value="append">إضافة للأسئلة الحالية</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">نموذج AI</label>
                                <select class="form-select" x-model="genTopic.modelId">
                                    <option value="">الافتراضي / الأفضل</option>
                                    <template x-for="m in aiModels" :key="m.id">
                                        <option :value="String(m.id)"
                                                x-text="m.name + (m.is_default ? ' (افتراضي)' : '') + ' — ' + m.provider"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <label class="form-label mb-0">أنواع الأسئلة</label>
                                    <span class="text-muted small">(اختر نوعاً واحداً على الأقل)</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-auto" @click="toggleAllGenTypes('topic')"
                                        x-text="genTopic.types.length === types.length ? 'إلغاء تحديد الكل' : 'تحديد الكل'"></button>
                                </div>
                                <div class="ile-type-pills">
                                    <template x-for="t in types" :key="'gen-topic-'+t.type">
                                        <label class="ile-type-pill">
                                            <input class="form-check-input" type="checkbox" :value="t.type" x-model="genTopic.types">
                                            <span x-text="t.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="ile-btn ile-btn--primary" @click="requestAiGenerate()" :disabled="genTopicLoading || !canGenerateTopic">
                                    <span x-show="!genTopicLoading"><i class="bi bi-stars"></i>توليد من الموضوع</span>
                                    <span x-show="genTopicLoading" x-cloak>جاري التوليد… قد يستغرق دقيقة</span>
                                </button>
                                <span class="text-danger small" x-show="genTopicError" x-text="genTopicError" x-cloak></span>
                                <span class="text-muted small" x-show="!aiModels.length" x-cloak>لا توجد نماذج نشطة — أضف نموذجاً من إعدادات AI.</span>
                            </div>
                        </div>
                    </div>

                    {{-- القسم الثاني: توليد من ملف PDF عادي — نص PDF عادي يعمل مع أي نموذج، وفقط الممسوح ضوئياً يحتاج نموذج رؤية --}}
                    <div class="ile-gen-section ile-gen-section--pdf" x-show="activeGenTab === 'pdf'" x-cloak>
                        <p class="ile-gen-section__desc">يستخرج نص ملف PDF ويولّد منه أسئلة. ملف PDF نصي عادي يعمل مع أي نموذج نشط؛ فقط إن كان الملف ممسوحاً ضوئياً (بلا نص) سيحتاج نموذجاً يدعم الرؤية، ويتطلب Imagick على الخادم.</p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">ملف PDF *</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" accept=".pdf,application/pdf"
                                           @change="onPdfFileChange($event)">
                                    <button type="button" class="btn btn-outline-secondary"
                                            @click="extractPdfFile()"
                                            :disabled="pdfLoading || !pdfFile">
                                        <span x-show="!pdfLoading">تحليل الملف</span>
                                        <span x-show="pdfLoading" x-cloak>جاري التحليل…</span>
                                    </button>
                                </div>
                                <div class="text-danger small mt-1" x-show="pdfError" x-text="pdfError" x-cloak></div>
                            </div>

                            {{-- معاينة المحتوى المستخرج قبل التوليد --}}
                            <template x-if="pdfKind">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <label class="form-label mb-0">المحتوى المستخرج</label>
                                        <span class="badge bg-secondary" x-show="pdfPageCount" x-cloak>
                                            <span x-text="pdfPageCount"></span> صفحة
                                        </span>
                                        <span class="badge bg-secondary" x-show="pdfKind === 'text'" x-cloak>
                                            <span x-text="pdfText.length"></span> حرف
                                        </span>
                                        <span class="badge bg-info" x-show="pdfKind === 'images'" x-cloak>
                                            تحليل بصري — <span x-text="pdfImagesCount"></span> صفحة كصورة
                                        </span>
                                        <span class="text-muted small" x-show="pdfNotes" x-text="pdfNotes" x-cloak></span>
                                    </div>
                                    <template x-if="pdfKind === 'text'">
                                        <div>
                                            <textarea class="form-control" rows="6" dir="auto" x-model="pdfText"
                                                      placeholder="النص المستخرج من الملف…"></textarea>
                                            <div class="form-text">يمكنك حذف الحشو (أرقام الصفحات، الترويسات) قبل التوليد لتحسين جودة الأسئلة.</div>
                                        </div>
                                    </template>
                                    <template x-if="pdfKind === 'images'">
                                        <div class="alert alert-info py-2 mb-0 small">
                                            هذا الملف ممسوح ضوئياً بلا طبقة نص، لذا سيُحلّل بصرياً عبر نموذج يدعم الرؤية. اختر النموذج المناسب أدناه.
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="col-12">
                                <label class="form-label">الأهداف (اختياري)</label>
                                <input type="text" class="form-control" x-model="genPdf.objectives" placeholder="ما الذي يجب أن يتعلمه الطالب؟">
                            </div>
                            <div class="col-6">
                                <label class="form-label">عدد الأسئلة</label>
                                <input type="number" min="1" max="15" class="form-control" x-model.number="genPdf.count">
                            </div>
                            <div class="col-6">
                                <label class="form-label">الصعوبة</label>
                                <select class="form-select" x-model="genPdf.difficulty">
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">الوضع</label>
                                <select class="form-select" x-model="genPdf.mode">
                                    <option value="replace">استبدال الأسئلة الحالية</option>
                                    <option value="append">إضافة للأسئلة الحالية</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">نموذج AI (نص أو رؤية حسب الملف)</label>
                                <select class="form-select" x-model="genPdf.modelId">
                                    <option value="" :disabled="pdfNeedsVisionModel">الافتراضي / الأفضل</option>
                                    <template x-for="m in aiModels" :key="m.id">
                                        <option :value="String(m.id)" :disabled="pdfNeedsVisionModel && !m.supports_vision"
                                                x-text="m.name + (m.is_default ? ' (افتراضي)' : '') + ' — ' + m.provider + (pdfNeedsVisionModel && !m.supports_vision ? ' (لا يدعم الرؤية)' : '')"></option>
                                    </template>
                                </select>
                                <div class="form-text text-warning" x-show="pdfNeedsVisionModel" x-cloak>
                                    هذا الملف ممسوح ضوئياً ويحتاج تحليلاً بصرياً — اختر نموذجاً يدعم الرؤية.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <label class="form-label mb-0">أنواع الأسئلة</label>
                                    <span class="text-muted small">(اختر نوعاً واحداً على الأقل)</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-auto" @click="toggleAllGenTypes('pdf')"
                                        x-text="genPdf.types.length === types.length ? 'إلغاء تحديد الكل' : 'تحديد الكل'"></button>
                                </div>
                                <div class="ile-type-pills">
                                    <template x-for="t in types" :key="'gen-pdf-'+t.type">
                                        <label class="ile-type-pill">
                                            <input class="form-check-input" type="checkbox" :value="t.type" x-model="genPdf.types">
                                            <span x-text="t.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="ile-btn ile-btn--primary" @click="requestPdfGenerate()" :disabled="genPdfLoading || !canGeneratePdf">
                                    <span x-show="!genPdfLoading"><i class="bi bi-stars"></i>توليد من PDF</span>
                                    <span x-show="genPdfLoading" x-cloak>جاري التوليد… قد يستغرق دقيقة</span>
                                </button>
                                <span class="text-danger small" x-show="genPdfError" x-text="genPdfError" x-cloak></span>
                                <span class="text-muted small" x-show="!aiModels.length" x-cloak>لا توجد نماذج نشطة — أضف نموذجاً من إعدادات AI.</span>
                            </div>
                        </div>
                    </div>

                    {{-- القسم الثالث: توليد من صورة — دائماً تحليل بصري، نماذج الرؤية فقط --}}
                    <div class="ile-gen-section ile-gen-section--image" x-show="activeGenTab === 'image'" x-cloak>
                        <p class="ile-gen-section__desc">تُحلَّل الصورة بصرياً دائماً، لذا يظهر هنا نماذج الرؤية (Vision) فقط — OpenAI / OpenRouter / Anthropic / Google / Z.ai.</p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">ملف الصورة *</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"
                                           @change="onImageFileChange($event)">
                                    <button type="button" class="btn btn-outline-secondary"
                                            @click="extractImageFile()"
                                            :disabled="imgLoading || !imgFile">
                                        <span x-show="!imgLoading">تحليل الملف</span>
                                        <span x-show="imgLoading" x-cloak>جاري التحليل…</span>
                                    </button>
                                </div>
                                <div class="text-danger small mt-1" x-show="imgError" x-text="imgError" x-cloak></div>
                            </div>

                            <template x-if="imgToken">
                                <div class="col-12">
                                    <div class="alert alert-info py-2 mb-0 small">
                                        تم استلام الصورة، جاهزة للتحليل البصري. <span x-text="imgNotes"></span>
                                    </div>
                                </div>
                            </template>

                            <div class="col-12">
                                <label class="form-label">الأهداف (اختياري)</label>
                                <input type="text" class="form-control" x-model="genImage.objectives" placeholder="ما الذي يجب أن يتعلمه الطالب؟">
                            </div>
                            <div class="col-6">
                                <label class="form-label">عدد الأسئلة</label>
                                <input type="number" min="1" max="15" class="form-control" x-model.number="genImage.count">
                            </div>
                            <div class="col-6">
                                <label class="form-label">الصعوبة</label>
                                <select class="form-select" x-model="genImage.difficulty">
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">الوضع</label>
                                <select class="form-select" x-model="genImage.mode">
                                    <option value="replace">استبدال الأسئلة الحالية</option>
                                    <option value="append">إضافة للأسئلة الحالية</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">نموذج رؤية (Vision)</label>
                                <select class="form-select" x-model="genImage.modelId">
                                    <template x-for="m in visionModels" :key="m.id">
                                        <option :value="String(m.id)"
                                                x-text="m.name + (m.is_default ? ' (افتراضي)' : '') + ' — ' + m.provider"></option>
                                    </template>
                                </select>
                                <div class="form-text text-warning" x-show="!visionModels.length" x-cloak>
                                    لا توجد نماذج تدعم الرؤية — فعّل قدرة "تحليل الصور (رؤية)" لنموذج من إعدادات AI.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <label class="form-label mb-0">أنواع الأسئلة</label>
                                    <span class="text-muted small">(اختر نوعاً واحداً على الأقل)</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-auto" @click="toggleAllGenTypes('image')"
                                        x-text="genImage.types.length === types.length ? 'إلغاء تحديد الكل' : 'تحديد الكل'"></button>
                                </div>
                                <div class="ile-type-pills">
                                    <template x-for="t in types" :key="'gen-image-'+t.type">
                                        <label class="ile-type-pill">
                                            <input class="form-check-input" type="checkbox" :value="t.type" x-model="genImage.types">
                                            <span x-text="t.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="ile-btn ile-btn--primary" @click="requestImageGenerate()" :disabled="genImageLoading || !canGenerateImage">
                                    <span x-show="!genImageLoading"><i class="bi bi-stars"></i>توليد من الصورة</span>
                                    <span x-show="genImageLoading" x-cloak>جاري التوليد… قد يستغرق دقيقة</span>
                                </button>
                                <span class="text-danger small" x-show="genImageError" x-text="genImageError" x-cloak></span>
                            </div>
                        </div>
                    </div>

                    {{-- القسم الرابع: استيراد أسئلة جاهزة من ملف CSV/MD/JSON — لا علاقة له بالذكاء الاصطناعي --}}
                    <div x-show="activeGenTab === 'import'" x-cloak>
                        @include('admin.pages.learning-experiences.partials.file-import-module')
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="alert alert-warning" x-show="clientErrors.length" x-cloak>
            <strong class="d-block mb-1">تحقق من الحقول:</strong>
            <ul class="mb-0 small">
                <template x-for="err in clientErrors" :key="err"><li x-text="err"></li></template>
            </ul>
        </div>

        @php
            $ileIsContentUploader = auth()->user()->shouldSubmitContentForReview();
            $ileCanReview = auth()->user()->canReviewContent();
        @endphp

        <div class="ile-panel">
            <div class="ile-panel__head">
                <h6><i class="bi bi-broadcast-pin"></i>حالة النشر</h6>
            </div>
            <div class="ile-panel__body">
                @include('admin.pages.learning-experiences.partials.learning-experience-review-fields', [
                    'fieldId' => 'ileReviewEdit',
                    'isEdit' => true,
                    'experience' => $experience,
                ])

                @if($ileIsContentUploader)
                    @if($experience->status === \App\InteractiveLearning\Models\LearningExperience::STATUS_DRAFT)
                        <form method="POST" action="{{ route('admin.learning-experiences.submit-for-review', $experience) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="bi bi-send me-1"></i> إرسال للمراجعة
                            </button>
                        </form>
                    @endif
                @else
                    <div class="ile-status-bar mt-2">
                        @foreach(['draft' => 'مسودة', 'review' => 'مراجعة', 'published' => 'نشر', 'archived' => 'أرشفة'] as $st => $label)
                            @if($experience->canTransitionTo($st) || $experience->status === $st)
                                <form method="POST" action="{{ route('admin.learning-experiences.transition', $experience) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $st }}">
                                    <button class="btn btn-sm {{ $experience->status === $st ? 'btn-primary' : 'btn-outline-primary' }}" {{ $experience->status === $st ? 'disabled' : '' }}>{{ $label }}</button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if($ileCanReview && $experience->status === \App\InteractiveLearning\Models\LearningExperience::STATUS_REVIEW)
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ileApproveModal">
                            <i class="bi bi-check2-circle me-1"></i> موافقة ونشر
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#ileRejectModal">
                            <i class="bi bi-x-circle me-1"></i> رفض
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="ile-panel">
            <div class="ile-panel__head">
                <h6><i class="bi bi-bar-chart-line"></i>محاولات الطلاب</h6>
                <span class="text-muted small fw-normal">
                    {{ number_format($attemptsCount ?? 0) }} محاولة
                    @if(($attemptsCount ?? 0) > 0)
                        · متوسط {{ number_format($attemptsAvg ?? 0, 1) }}%
                    @endif
                </span>
            </div>
            <div class="ile-panel__body">
                @if(($recentAttempts ?? collect())->isEmpty())
                    <p class="text-muted small mb-0">لا توجد محاولات محفوظة بعد. تظهر هنا بعد إكمال الطلاب لاختبار منشور.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>الطالب</th>
                                    <th>النسبة</th>
                                    <th>الدرجة</th>
                                    <th>المدة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttempts as $attempt)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small">{{ $attempt->user->name ?? '—' }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $attempt->user->email ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $attempt->percentage >= 50 ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                                {{ number_format($attempt->percentage, 1) }}%
                                            </span>
                                        </td>
                                        <td class="small">{{ $attempt->score }}/{{ $attempt->total }}</td>
                                        <td class="small text-muted">{{ $attempt->duration }} ث</td>
                                        <td class="small text-muted">
                                            {{ optional($attempt->finished_at ?? $attempt->created_at)->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(($attemptsCount ?? 0) > ($recentAttempts->count()))
                        <p class="text-muted small mt-2 mb-0">عرض آخر {{ $recentAttempts->count() }} من {{ number_format($attemptsCount) }}.</p>
                    @endif
                @endif
            </div>
        </div>

        <div class="ile-panel">
            <div class="ile-panel__head">
                <h6><i class="bi bi-plus-circle"></i>إضافة سؤال</h6>
            </div>
            <div class="ile-panel__body">
                <div class="ile-type-grid">
                    <template x-for="type in availableTypes" :key="type.type">
                        <button type="button" class="ile-type-btn" :style="`color:${type.color};border-color:${type.color}33`" @click="addQuestion(type.type)">
                            <span class="ile-type-btn__name" :style="`color:${type.color}`" x-text="type.name"></span>
                            <span class="ile-type-btn__code" x-text="type.type"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.learning-experiences.update', $experience) }}" @submit="prepareSubmit">
            @csrf
            @method('PUT')
            <input type="hidden" name="schema_json" :value="JSON.stringify(schema)">
            <input type="hidden" name="title" :value="schema.meta?.title || ''">
            <input type="hidden" name="description" :value="description">

            <div class="ile-panel">
                <div class="ile-panel__head">
                    <h6><i class="bi bi-diagram-3"></i>ربط بالمنهج</h6>
                </div>
                <div class="ile-panel__body">
                    {{-- شرطا الظهور للطالب: مربوطة بمادة + منشورة --}}
                    @if(empty($experience->subject_id))
                        <div class="alert alert-warning d-flex align-items-start gap-2 py-2">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <div class="small">
                                <strong>لن تظهر هذه التجربة لأي طالب.</strong>
                                الظهور يتطلب ربطها بمادة <em>و</em> نشرها. اختر المادة أدناه ثم غيّر الحالة إلى «منشور».
                            </div>
                        </div>
                    @elseif($experience->status !== \App\InteractiveLearning\Models\LearningExperience::STATUS_PUBLISHED)
                        <div class="alert alert-info d-flex align-items-start gap-2 py-2">
                            <i class="bi bi-info-circle-fill mt-1"></i>
                            <div class="small">
                                التجربة مربوطة بمادة لكن حالتها <strong>{{ $experience->status }}</strong> —
                                لن يراها الطلاب حتى تصبح <strong>منشور</strong>.
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">نسبة النجاح (%)</label>
                            <input type="number" min="0" max="100" step="1" class="form-control"
                                   name="passing_score"
                                   value="{{ old('passing_score', $experience->passing_score ?? 50) }}">
                            <div class="form-text">تُحدَّد بها حالة النجاح/الرسوب في نتائج الطالب وإحصائياته.</div>
                            @error('passing_score')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحد الأقصى للمحاولات</label>
                            <input type="number" min="0" step="1" class="form-control"
                                   name="max_attempts"
                                   value="{{ old('max_attempts', $experience->max_attempts ?? 0) }}">
                            <div class="form-text">0 = غير محدود. يمنع الطالب من إعادة المحاولة بعد بلوغ العدد.</div>
                            @error('max_attempts')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if(!empty($isFromLesson) && ($selectedLesson ?? null))
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">المادة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedSubject->name ?? '—' }}</strong>
                                </div>
                                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الوحدة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedUnit->title ?? '—' }}</strong>
                                </div>
                                <input type="hidden" name="unit_id" value="{{ $selectedUnit->id ?? '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">الدرس</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedLesson->title }}</strong>
                                </div>
                                <input type="hidden" name="lesson_id" value="{{ $selectedLesson->id }}">
                            </div>
                        </div>
                    @elseif(!empty($isFromSubjectOrUnit) && ($selectedUnit ?? null) && empty($experience->lesson_id))
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">المادة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedSubject->name ?? '—' }}</strong>
                                </div>
                                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الوحدة</label>
                                <div class="form-control bg-light" style="cursor: not-allowed;">
                                    <strong>{{ $selectedUnit->title }}</strong>
                                </div>
                                <input type="hidden" name="unit_id" value="{{ $selectedUnit->id }}">
                            </div>
                        </div>
                    @else
                        @include('admin.pages.quizzes.partials.curriculum-cascade-fields', [
                            'stages' => $stages,
                            'selectedStageId' => $selectedStageId ?? null,
                            'selectedClassId' => $selectedClassId ?? null,
                            'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
                            'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
                            'cascadeRequireStage' => true,
                        ])
                    @endif
                    @error('subject_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('unit_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('lesson_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="ile-panel">
                <div class="ile-panel__head">
                    <h6><i class="bi bi-sliders"></i>إعدادات الجلسة</h6>
                </div>
                <div class="ile-panel__body">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <label class="form-label">عنوان الاختبار</label>
                            <input type="text" class="form-control" x-model="schema.meta.title" @input="validateLive()">
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label">الوصف</label>
                            <input type="text" class="form-control" x-model="description" placeholder="وصف مختصر للاختبار">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">السماح بالرجوع</label>
                            <select class="form-select" x-model="schema.rules.allowBack">
                                <option :value="true">نعم</option>
                                <option :value="false">لا</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">خلط الأسئلة</label>
                            <select class="form-select" x-model="schema.rules.shuffleQuestions">
                                <option :value="false">لا</option>
                                <option :value="true">نعم</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">محاولات لكل سؤال</label>
                            <input type="number" min="1" class="form-control" x-model.number="schema.rules.attemptsPerQuestion">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">إظهار الشرح</label>
                            <select class="form-select" x-model="schema.rules.showExplanation">
                                <option :value="true">نعم</option>
                                <option :value="false">لا</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ile-section-bar">
                <h6>الأسئلة</h6>
                <button type="button" class="ile-btn ile-btn--line" style="padding:.4rem .8rem;font-size:.8rem" @click="openAll = !openAll" x-text="openAll ? 'طيّ الكل' : 'فتح الكل'"></button>
            </div>

            <template x-for="(q, qi) in schema.questions" :key="q.id">
                <div class="ile-q-card">
                    <div class="ile-q-card__head" @click="toggleQuestion(qi)">
                        <div class="d-flex align-items-center gap-2 flex-wrap min-w-0">
                            <span class="ile-badge-type" :style="`background:${typeMeta(q.type)?.color || '#64748b'}`" x-text="typeMeta(q.type)?.name || q.type"></span>
                            <strong x-text="'#' + (qi + 1)"></strong>
                            <span class="ile-stem-preview" x-text="q.stem || 'بدون نص'"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="ile-btn ile-btn--danger-line" style="padding:.3rem .65rem;font-size:.78rem" @click.stop="removeQuestion(qi)">حذف</button>
                            <i class="bi" :class="isOpen(qi) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </div>
                    </div>

                    <div class="ile-q-card__body" x-show="isOpen(qi)" x-cloak>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">نص السؤال</label>
                                <textarea class="form-control" rows="2" x-model="q.stem" @input="onStemInput(q)"></textarea>
                            </div>
                            <template x-if="isDynamic">
                                <div class="col-12">
                                    <label class="form-label">كتل العرض (ديناميك)</label>
                                    <div class="border rounded p-2 bg-light">
                                        <template x-for="(block, bi) in (q.stemBlocks || [])" :key="bi">
                                            <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
                                                <select class="form-select form-select-sm" style="max-width:8rem" x-model="block.type">
                                                    <option value="text">نص</option>
                                                    <option value="math">رياضيات</option>
                                                    <option value="scene">مشهد عدّ</option>
                                                    <option value="sticker">ملصق</option>
                                                    <option value="icon">أيقونة</option>
                                                    <option value="audio">صوت</option>
                                                </select>
                                                <template x-if="block.type === 'text'">
                                                    <input class="form-control form-control-sm" x-model="block.text" placeholder="نص">
                                                </template>
                                                <template x-if="block.type === 'math'">
                                                    <input class="form-control form-control-sm" dir="ltr" x-model="block.latex" placeholder="\\frac{1}{2}">
                                                </template>
                                                <template x-if="block.type === 'scene'">
                                                    <div class="d-flex gap-2 flex-grow-1">
                                                        <input class="form-control form-control-sm" x-model="block.item" placeholder="apple">
                                                        <input type="number" min="0" class="form-control form-control-sm" style="max-width:5rem" x-model.number="block.count">
                                                    </div>
                                                </template>
                                                <template x-if="block.type === 'sticker' || block.type === 'icon'">
                                                    <input class="form-control form-control-sm" x-model="block.name" placeholder="apple / star">
                                                </template>
                                                <template x-if="block.type === 'audio'">
                                                    <input class="form-control form-control-sm" x-model="block.text" placeholder="نص النطق">
                                                </template>
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="q.stemBlocks.splice(bi, 1)">×</button>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addBlock(q, 'text')">+ كتلة نص</button>
                                        <button type="button" class="btn btn-sm btn-outline-success" @click="addBlock(q, 'scene')">+ مشهد عدّ</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="addBlock(q, 'math')">+ رياضيات</button>
                                    </div>
                                </div>
                            </template>
                            <div class="col-6 col-md-3">
                                <label class="form-label">النقاط</label>
                                <input type="number" min="0" step="0.5" class="form-control" x-model.number="q.points">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">الصعوبة</label>
                                <select class="form-select" x-model="q.difficulty">
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">وقت مقترح (ث)</label>
                                <input type="number" min="5" class="form-control" x-model.number="q.estimatedSeconds">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">تلميح</label>
                                <input type="text" class="form-control" :value="(q.hints || [])[0] || ''" @input="q.hints = [$event.target.value].filter(Boolean)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رسالة النجاح <span class="text-muted fw-normal">(مع تسجيل صوتي)</span></label>
                                <div class="input-group">
                                    <select class="form-select" x-model="q.successMessage">
                                        <template x-for="p in feedbackPhrases.success" :key="p.text">
                                            <option :value="p.text" x-text="p.text"></option>
                                        </template>
                                    </select>
                                    <button type="button" class="btn btn-outline-success"
                                            @click="previewPhrase(q.successMessage, 'success')"
                                            title="استمع للتسجيل">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رسالة الخطأ <span class="text-muted fw-normal">(مع تسجيل صوتي)</span></label>
                                <div class="input-group">
                                    <select class="form-select" x-model="q.errorMessage">
                                        <template x-for="p in feedbackPhrases.fail" :key="p.text">
                                            <option :value="p.text" x-text="p.text"></option>
                                        </template>
                                    </select>
                                    <button type="button" class="btn btn-outline-danger"
                                            @click="previewPhrase(q.errorMessage, 'fail')"
                                            title="استمع للتسجيل">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">الشرح بعد الإجابة</label>
                                <textarea class="form-control" rows="2" x-model="q.explanation" placeholder="اختياري"></textarea>
                            </div>

                            <template x-if="q.type === 'true_false'">
                                <div class="col-md-4">
                                    <label class="form-label">الإجابة الصحيحة</label>
                                    <select class="form-select" x-model="q.payload.correct">
                                        <option :value="true">صح</option>
                                        <option :value="false">خطأ</option>
                                    </select>
                                </div>
                            </template>

                            <template x-if="q.type === 'single_choice' || q.type === 'multiple_choice'">
                                <div class="col-12">
                                    <label class="form-label">الخيارات</label>
                                    <template x-for="(opt, oi) in q.payload.options" :key="opt.id">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text" x-text="opt.id"></span>
                                            <input type="text" class="form-control" style="max-width:4.5rem" x-model="opt.icon" placeholder="🦁" title="أيقونة إيموجي">
                                            <input type="text" class="form-control" x-model="opt.label" @input="validateLive()" placeholder="نص الخيار">
                                            <template x-if="q.type === 'single_choice'">
                                                <button type="button" class="btn" :class="q.payload.correctId === opt.id ? 'btn-success' : 'btn-outline-success'" @click="q.payload.correctId = opt.id">صحيح</button>
                                            </template>
                                            <template x-if="q.type === 'multiple_choice'">
                                                <button type="button" class="btn" :class="(q.payload.correctIds || []).includes(opt.id) ? 'btn-success' : 'btn-outline-success'" @click="toggleCorrectId(q, opt.id)">صحيح</button>
                                            </template>
                                            <button type="button" class="btn btn-outline-danger" @click="q.payload.options.splice(oi, 1)">×</button>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addOption(q)">+ خيار</button>
                                </div>
                            </template>

                            <template x-if="q.type === 'drag_drop'">
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">العناصر → المنطقة الصحيحة</label>
                                            <template x-for="item in q.payload.items" :key="item.id">
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" x-model="item.label">
                                                    <select class="form-select" x-model="q.payload.assignments[item.id]">
                                                        <template x-for="zone in q.payload.zones" :key="zone.id">
                                                            <option :value="zone.id" x-text="zone.label"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>
                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="addDragItem(q)">+ عنصر</button>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">المناطق</label>
                                            <template x-for="zone in q.payload.zones" :key="zone.id">
                                                <input type="text" class="form-control mb-2" x-model="zone.label">
                                            </template>
                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="addDragZone(q)">+ منطقة</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="q.type === 'matching'">
                                <div class="col-12">
                                    <label class="form-label mb-2">أزواج المطابقة</label>
                                    <template x-for="left in q.payload.left" :key="left.id">
                                        <div class="row g-2 mb-2 align-items-center">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" placeholder="العنصر" x-model="left.label" dir="auto">
                                            </div>
                                            <div class="col-md-2 text-center text-muted">↔</div>
                                            <div class="col-md-4">
                                                <select class="form-select" x-model="q.payload.pairs[left.id]">
                                                    <template x-for="right in q.payload.right" :key="right.id">
                                                        <option :value="right.id" x-text="right.label"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-outline-danger w-100" @click="removeMatchingLeft(q, left.id)">×</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addMatchingLeft(q)">+ عنصر</button>

                                    <div class="mt-3">
                                        <label class="form-label">تسميات العمود الأيمن</label>
                                        <template x-for="right in q.payload.right" :key="right.id">
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" x-model="right.label" dir="auto">
                                                <button type="button" class="btn btn-outline-danger" @click="removeMatchingRight(q, right.id)">×</button>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addMatchingRight(q)">+ قيمة</button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="['ordering','puzzle_pieces'].includes(q.type)">
                                <div class="col-12">
                                    <label class="form-label">العناصر بالترتيب الصحيح (من الأعلى للأسفل)</label>
                                    <template x-for="(it, ii) in (q.payload.items || q.payload.pieces || [])" :key="it.id">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text" x-text="ii+1"></span>
                                            <input type="text" class="form-control" style="max-width:4.5rem" x-model="it.icon" placeholder="🔢">
                                            <input type="text" class="form-control" x-model="it.label" dir="auto">
                                        </div>
                                    </template>
                                    <p class="small text-muted mb-0">الترتيب الحالي يُحفظ كـ correctOrder تلقائياً عند الحفظ عبر محرر الـ payload.</p>
                                </div>
                            </template>

                            <template x-if="q.type === 'fill_blank'">
                                <div class="col-12">
                                    <label class="form-label">القالب (ضع ___ مكان الفراغ)</label>
                                    <input type="text" class="form-control mb-2" x-model="q.payload.template">
                                    <label class="form-label">وضع الإجابة</label>
                                    <select class="form-select mb-2" x-model="q.payload.mode">
                                        <option value="choice">اختيار من خيارات</option>
                                        <option value="text">كتابة نص</option>
                                    </select>
                                    <template x-if="q.payload.mode !== 'text'">
                                        <div>
                                            <template x-for="opt in q.payload.options" :key="opt.id">
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" style="max-width:4.5rem" x-model="opt.icon">
                                                    <input type="text" class="form-control" x-model="opt.label">
                                                    <button type="button" class="btn" :class="q.payload.correct === opt.id ? 'btn-success' : 'btn-outline-success'" @click="q.payload.correct = opt.id">صحيح</button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="q.payload.mode === 'text'">
                                        <div>
                                            <label class="form-label">الإجابة الصحيحة</label>
                                            <input type="text" class="form-control" x-model="q.payload.correct">
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="q.type === 'categorize'">
                                <div class="col-12">
                                    <label class="form-label">العناصر</label>
                                    <template x-for="it in q.payload.items" :key="it.id">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" style="max-width:4.5rem" x-model="it.icon" placeholder="🔢">
                                            <input type="text" class="form-control" x-model="it.label" placeholder="نص العنصر" dir="auto">
                                            <select class="form-select" x-model="q.payload.correct[it.id]">
                                                <template x-for="cat in q.payload.categories" :key="cat.id">
                                                    <option :value="cat.id" x-text="cat.label"></option>
                                                </template>
                                            </select>
                                            <button type="button" class="btn btn-outline-danger" @click="removeCategorizeItem(q, it.id)">×</button>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addCategorizeItem(q)">+ عنصر</button>

                                    <label class="form-label mt-3">التصنيفات</label>
                                    <template x-for="cat in q.payload.categories" :key="cat.id">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" style="max-width:4.5rem" x-model="cat.icon" placeholder="🏷️">
                                            <input type="text" class="form-control" x-model="cat.label" placeholder="نص التصنيف" dir="auto">
                                            <button type="button" class="btn btn-outline-danger" @click="removeCategorizeCategory(q, cat.id)">×</button>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addCategorizeCategory(q)">+ تصنيف</button>
                                </div>
                            </template>

                            <template x-if="q.type === 'listen_choose'">
                                <div class="col-12">
                                    <label class="form-label">نص الاستماع</label>
                                    <input type="text" class="form-control mb-2" x-model="q.payload.prompt.label">
                                    <template x-for="opt in q.payload.options" :key="opt.id">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" style="max-width:4.5rem" x-model="opt.icon">
                                            <input type="text" class="form-control" x-model="opt.label">
                                            <button type="button" class="btn" :class="q.payload.correctId === opt.id ? 'btn-success' : 'btn-outline-success'" @click="q.payload.correctId = opt.id">صحيح</button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="['connect_lines','memory_cards'].includes(q.type)">
                                <div class="col-12">
                                    <label class="form-label mb-2">أزواج التوصيل/الذاكرة</label>
                                    <template x-for="left in q.payload.left" :key="left.id">
                                        <div class="row g-2 mb-2 align-items-center">
                                            <div class="col-md-5"><input type="text" class="form-control" x-model="left.label" :placeholder="left.icon || ''" dir="auto"></div>
                                            <div class="col-md-2 text-center">↔</div>
                                            <div class="col-md-4">
                                                <select class="form-select" x-model="q.payload.pairs[left.id]">
                                                    <template x-for="right in q.payload.right" :key="right.id">
                                                        <option :value="right.id" x-text="right.label"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-outline-danger w-100" @click="removeMatchingLeft(q, left.id)">×</button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addMatchingLeft(q)">+ عنصر</button>

                                    <div class="mt-3">
                                        <label class="form-label">تسميات العمود الأيمن</label>
                                        <template x-for="right in q.payload.right" :key="right.id">
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" x-model="right.label" dir="auto">
                                                <button type="button" class="btn btn-outline-danger" @click="removeMatchingRight(q, right.id)">×</button>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addMatchingRight(q)">+ قيمة</button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="q.type === 'hotspot'">
                                <div class="col-12">
                                    <label class="form-label">رابط الصورة (اختياري)</label>
                                    <input type="text" class="form-control mb-2" x-model="q.payload.imageUrl" placeholder="https://...">
                                    <template x-for="spot in q.payload.spots" :key="spot.id">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" x-model="spot.label" placeholder="تسمية">
                                            <input type="number" class="form-control" x-model.number="spot.x" title="X%">
                                            <input type="number" class="form-control" x-model.number="spot.y" title="Y%">
                                            <button type="button" class="btn" :class="q.payload.correctId === spot.id ? 'btn-success' : 'btn-outline-success'" @click="q.payload.correctId = spot.id">صحيح</button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="q.type === 'numerical'">
                                <div class="col-md-6">
                                    <label class="form-label">الإجابة الرقمية</label>
                                    <input type="number" step="any" class="form-control" x-model.number="q.payload.correct">
                                </div>
                            </template>
                            <template x-if="q.type === 'numerical'">
                                <div class="col-md-3">
                                    <label class="form-label">التسامح</label>
                                    <input type="number" step="any" class="form-control" x-model.number="q.payload.tolerance">
                                </div>
                            </template>
                            <template x-if="q.type === 'numerical'">
                                <div class="col-md-3">
                                    <label class="form-label">الوحدة</label>
                                    <input type="text" class="form-control" x-model="q.payload.unit">
                                </div>
                            </template>

                            <template x-if="q.type === 'short_answer'">
                                <div class="col-12">
                                    <label class="form-label">الإجابة الصحيحة</label>
                                    <input type="text" class="form-control mb-2" x-model="q.payload.correct">
                                    <label class="form-label">إجابات مقبولة (مفصولة بفاصلة)</label>
                                    <input type="text" class="form-control" :value="(q.payload.acceptedAnswers || []).join('، ')" @input="q.payload.acceptedAnswers = $event.target.value.split(/[,،]/).map(s => s.trim()).filter(Boolean)">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <div class="ile-sticky-actions">
                <button type="submit" class="ile-btn ile-btn--primary" :disabled="clientErrors.length > 0">
                    <i class="bi bi-check2"></i>حفظ الاختبار
                </button>
                <button type="button" class="ile-btn ile-btn--line" @click="validateLive()">تحقق الآن</button>
                <button type="button" class="ile-btn ile-btn--line" @click="undo()" :disabled="!history.length">تراجع</button>
            </div>
        </form>

        <div class="modal fade" id="ilePatchModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">مراجعة اقتراحات AI</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" x-text="patchSummary"></p>
                        <div class="mb-3">
                            <label class="form-label">النية</label>
                            <input type="text" class="form-control" x-model="aiIntent">
                        </div>
                        <template x-for="(op, oi) in patchOperations" :key="oi">
                            <div class="border rounded p-2 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" :id="'op'+oi" x-model="op._selected">
                                    <label class="form-check-label" :for="'op'+oi">
                                        <strong x-text="op.op"></strong>
                                        <span class="text-muted" x-text="op.questionId ? (' — ' + op.questionId) : ''"></span>
                                    </label>
                                </div>
                                <pre class="small mb-0 mt-2 bg-light p-2 rounded" x-text="JSON.stringify(op.fields || op.question || op, null, 2)"></pre>
                            </div>
                        </template>
                        <div class="alert alert-warning" x-show="aiError" x-text="aiError" x-cloak></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-outline-primary" @click="requestAiPatch()" :disabled="aiLoading">إعادة الاقتراح</button>
                        <button type="button" class="btn btn-primary" @click="applySelectedPatches()" :disabled="aiLoading || !selectedOps().length">تطبيق المحدد</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ileGenerateModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">مراجعة الأسئلة المولّدة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-1" x-text="genSummary"></p>
                        <p class="small text-muted mb-3" x-show="genModel" x-text="'النموذج: ' + genModel"></p>
                        <template x-for="(gq, gi) in genQuestions" :key="gq.id || gi">
                            <div class="border rounded p-3 mb-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" :id="'gq'+gi" x-model="gq._selected">
                                    <label class="form-check-label" :for="'gq'+gi">
                                        <span class="badge me-1" :style="`background:${typeMeta(gq.type)?.color || '#64748b'}`" x-text="typeMeta(gq.type)?.name || gq.type"></span>
                                        <strong x-text="'#' + (gi + 1)"></strong>
                                    </label>
                                </div>
                                <div class="fw-semibold mb-1" x-text="gq.stem"></div>
                                <pre class="small mb-0 bg-light p-2 rounded" x-text="JSON.stringify(gq.payload || {}, null, 2)"></pre>
                            </div>
                        </template>
                        <div class="alert alert-warning" x-show="genError" x-text="genError" x-cloak></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-outline-primary" @click="regenerateLast()" :disabled="genLoading">إعادة التوليد</button>
                        <button type="button" class="btn btn-primary" @click="applyGeneratedQuestions()" :disabled="genLoading || !selectedGenQuestions().length">
                            تطبيق المحدد
                            <span x-text="lastGenMode === 'append' ? '(إضافة)' : '(استبدال)'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canReviewContent() && $experience->status === \App\InteractiveLearning\Models\LearningExperience::STATUS_REVIEW)
<div class="modal fade" id="ileApproveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.learning-experiences.approve-review', $experience) }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">الموافقة على نشر الاختبار التفاعلي</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small">ملاحظات (اختياري)</label>
                    <textarea name="review_notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">موافقة ونشر</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ileRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.learning-experiences.reject-review', $experience) }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">رفض نشر الاختبار التفاعلي</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small">ملاحظات <span class="text-danger">*</span></label>
                    <textarea name="review_notes" class="form-control" rows="3" required></textarea>
                    <small class="text-muted d-block mt-1">يجب إضافة ملاحظات توضح سبب الرفض.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">رفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(empty($isFromLesson) && (empty($isFromSubjectOrUnit) || empty($selectedUnit)))
@include('admin.pages.quizzes.partials.curriculum-cascade-script', [
    'selectedStageId' => $selectedStageId ?? null,
    'selectedClassId' => $selectedClassId ?? null,
    'selectedSubjectId' => old('subject_id', $selectedSubjectId ?? ''),
    'selectedUnitId' => old('unit_id', $selectedUnitId ?? ''),
    'cascadeRequireStage' => true,
])
@endif

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
<script>
function ileEditor(initialSchema, types, blankTemplates, urls, aiModels, blankDynamicTemplates, dynamicInteractionTypes) {
    /**
     * عبارات التغذية الراجعة مع تسجيلاتها (من FeedbackPhrases::forPlayer).
     * القائمة مغلقة: كل رسالة يختارها الإدمن لها تسجيل صوتي ينطقها حرفياً.
     */
    const feedbackPhrases = {
        success: (urls.feedbackPhrases && urls.feedbackPhrases.success) || [],
        fail: (urls.feedbackPhrases && urls.feedbackPhrases.fail) || [],
    };
    const phraseTexts = {
        success: feedbackPhrases.success.map(p => p.text),
        fail: feedbackPhrases.fail.map(p => p.text),
    };

    /** اضبط الرسالة على عبارة من القائمة — بالتناوب حسب ترتيب السؤال لتتنوّع. */
    const snapPhrase = (value, kind, index) => {
        const list = phraseTexts[kind] || [];
        if (!list.length) return value || '';
        if (list.includes(value)) return value;
        return list[Math.abs(index || 0) % list.length];
    };

    const ensure = (schema) => {
        schema = schema || {};
        const isDyn = schema.mode === 'dynamic' || schema.version === '2.0';
        schema.mode = isDyn ? 'dynamic' : (schema.mode || 'classic');
        schema.version = isDyn ? '2.0' : (schema.version || '1.0');
        schema.meta = schema.meta || { title: '', locale: 'ar', rtl: true };
        schema.theme = schema.theme || { themeId: 'kids', accent: '#22c55e', font: 'system', density: 'comfortable', motion: 'full', mode: 'light' };
        if (!schema.theme.themeId) schema.theme.themeId = 'kids';
        schema.rules = Object.assign({
            allowBack: true, shuffleQuestions: false, maxWrong: null,
            showExplanation: true, attemptsPerQuestion: 1, timerSeconds: null
        }, schema.rules || {});
        schema.rules.allowBack = !!schema.rules.allowBack;
        schema.rules.shuffleQuestions = !!schema.rules.shuffleQuestions;
        schema.rules.showExplanation = schema.rules.showExplanation !== false;
        schema.messages = schema.messages || { success: [], error: [], encourage: [] };
        if (isDyn) {
            schema.assets = schema.assets || { libraries: ['katex', 'icons', 'stickers', 'lottie', 'tts'] };
        }
        schema.questions = Array.isArray(schema.questions) ? schema.questions : [];
        schema.questions.forEach((q, qi) => {
            // القائمة مغلقة: أي رسالة قديمة خارجها تُضبط على عبارة لها تسجيل صوتي
            q.successMessage = snapPhrase(q.successMessage, 'success', qi);
            q.errorMessage = snapPhrase(q.errorMessage, 'fail', qi);
            if (isDyn) {
                q.interaction = q.interaction || { type: 'single_choice', payload: {} };
                q.type = q.interaction.type || q.type || 'single_choice';
                q.payload = q.interaction.payload || q.payload || {};
                q.stemBlocks = Array.isArray(q.stemBlocks) ? q.stemBlocks : [{ type: 'text', text: q.stem || '' }];
                if (!q.stem) {
                    const tb = q.stemBlocks.find(b => b.type === 'text');
                    q.stem = tb?.text || '';
                }
            }
            q.payload = q.payload || {};
            if (q.type === 'true_false' && typeof q.payload.correct !== 'boolean') q.payload.correct = true;
            if ((q.type === 'single_choice' || q.type === 'multiple_choice' || q.type === 'listen_choose') && !Array.isArray(q.payload.options)) q.payload.options = [];
            if (q.type === 'multiple_choice' && !Array.isArray(q.payload.correctIds)) q.payload.correctIds = [];
            if (q.type === 'drag_drop') {
                q.payload.items = q.payload.items || [];
                q.payload.zones = q.payload.zones || [];
                q.payload.assignments = q.payload.assignments || {};
            }
            if (q.type === 'matching' || q.type === 'connect_lines' || q.type === 'memory_cards') {
                q.payload.left = q.payload.left || [];
                q.payload.right = q.payload.right || [];
                q.payload.pairs = q.payload.pairs || {};
            }
            if (q.type === 'ordering') {
                q.payload.items = q.payload.items || [];
                q.payload.correctOrder = q.payload.correctOrder || q.payload.items.map(i => i.id);
            }
            if (q.type === 'puzzle_pieces') {
                q.payload.pieces = q.payload.pieces || [];
                q.payload.correctOrder = q.payload.correctOrder || q.payload.pieces.map(i => i.id);
            }
            if (q.type === 'fill_blank') {
                q.payload.template = q.payload.template || 'أكمل: ___';
                q.payload.mode = q.payload.mode || 'choice';
                q.payload.options = q.payload.options || [];
            }
            if (q.type === 'categorize') {
                q.payload.items = q.payload.items || [];
                q.payload.categories = q.payload.categories || [];
                q.payload.correct = q.payload.correct || {};
            }
            if (q.type === 'listen_choose') {
                q.payload.prompt = q.payload.prompt || { label: 'استمع', icon: '🎧' };
                q.payload.options = q.payload.options || [];
            }
            if (q.type === 'hotspot') {
                q.payload.spots = q.payload.spots || [];
            }
            if (q.type === 'numerical') {
                if (typeof q.payload.correct !== 'number') q.payload.correct = Number(q.payload.correct || 0);
                q.payload.tolerance = Number(q.payload.tolerance || 0);
            }
            if (q.type === 'short_answer') {
                q.payload.acceptedAnswers = q.payload.acceptedAnswers || [];
            }
        });
        return schema;
    };

    return {
        schema: ensure(JSON.parse(JSON.stringify(initialSchema || {}))),
        types,
        blankTemplates,
        blankDynamicTemplates: blankDynamicTemplates || {},
        dynamicInteractionTypes: dynamicInteractionTypes || [],
        urls,
        aiModels: aiModels || [],
        feedbackPhrases,
        description: @js($experience->description ?? ''),
        /** استمع لتسجيل العبارة المختارة للتأكد من تطابق النص مع الصوت. */
        previewPhrase(text, kind) {
            const row = (this.feedbackPhrases[kind] || []).find(p => p.text === text);
            if (!row || !row.url) return;
            if (this._phraseAudio) {
                try { this._phraseAudio.pause(); } catch (e) {}
            }
            this._phraseAudio = new Audio(row.url);
            const p = this._phraseAudio.play();
            if (p && p.catch) p.catch(() => {});
        },
        _phraseAudio: null,
        get isDynamic() {
            return this.schema.mode === 'dynamic' || this.schema.version === '2.0';
        },
        get availableTypes() {
            if (!this.isDynamic) return this.types;
            const allow = new Set(this.dynamicInteractionTypes || []);
            return (this.types || []).filter(t => allow.has(t.type));
        },
        addBlock(q, type) {
            q.stemBlocks = q.stemBlocks || [];
            const blank = {
                text: { type: 'text', text: '' },
                math: { type: 'math', latex: '1+1' },
                scene: { type: 'scene', item: 'apple', count: 3, layout: 'row' },
                sticker: { type: 'sticker', name: 'star' },
                icon: { type: 'icon', name: 'star' },
                audio: { type: 'audio', text: 'استمع' },
            };
            q.stemBlocks.push(JSON.parse(JSON.stringify(blank[type] || blank.text)));
        },
        onStemInput(q) {
            if (this.isDynamic && Array.isArray(q.stemBlocks)) {
                const tb = q.stemBlocks.find(b => b.type === 'text');
                if (tb) tb.text = q.stem;
                else q.stemBlocks.unshift({ type: 'text', text: q.stem });
            }
            this.validateLive();
        },
        syncDynamicQuestions() {
            if (!this.isDynamic) return;
            this.schema.mode = 'dynamic';
            this.schema.version = '2.0';
            this.schema.questions.forEach(q => {
                q.interaction = { type: q.type, payload: q.payload || {} };
                q.stemBlocks = Array.isArray(q.stemBlocks) && q.stemBlocks.length
                    ? q.stemBlocks
                    : [{ type: 'text', text: q.stem || '' }];
            });
        },
        clientErrors: [],
        aiLoading: false,
        aiIntent: 'اجعل الصياغة عربية بسيطة حماسية لطلاب صغار، وحدّث رسائل النجاح والتشجيع دون تغيير الإجابات الصحيحة',
        aiError: '',
        patchSummary: '',
        patchOperations: [],
        history: [],
        openIndex: 0,
        openAll: false,
        openGeneratePanel: true,
        // كل قسم توليد مستقل بالكامل بحالة تحميل/خطأ خاصة به، حتى لا يتقاطع أحدهما مع الآخر
        genLoading: false,
        genError: '',
        genSummary: '',
        genModel: '',
        genQuestions: [],
        lastGenSource: 'topic', // 'topic' | 'pdf' | 'image' — أي قسم أنتج الدفعة المعروضة حالياً في نافذة المراجعة
        lastGenMode: 'replace',
        activeGenTab: 'topic', // 'topic' | 'pdf' | 'image' — التاب الظاهر حالياً في لوحة التوليد

        // قسم "من موضوع نصي" — يعمل مع أي نموذج نشط
        genTopic: {
            topic: '',
            objectives: '',
            count: 5,
            difficulty: 'medium',
            mode: 'replace',
            modelId: '',
            // لا تحديد افتراضي — يختار المستخدم الأنواع المطلوبة بنفسه
            types: [],
        },
        genTopicLoading: false,
        genTopicError: '',

        // قسم "من ملف PDF" — نص PDF عادي يعمل مع أي نموذج، والممسوح ضوئياً يحتاج نموذج رؤية
        genPdf: {
            objectives: '',
            count: 5,
            difficulty: 'medium',
            mode: 'replace',
            modelId: '',
            types: [],
        },
        genPdfLoading: false,
        genPdfError: '',
        pdfFile: null,
        pdfLoading: false,
        pdfError: '',
        pdfKind: '',        // '' | 'text' | 'images'
        pdfText: '',
        pdfToken: '',
        pdfPageCount: 0,
        pdfImagesCount: 0,
        pdfNotes: '',

        // قسم "من صورة" — دائماً تحليل بصري، نماذج الرؤية فقط
        genImage: {
            objectives: '',
            count: 5,
            difficulty: 'medium',
            mode: 'replace',
            modelId: '',
            types: [],
        },
        genImageLoading: false,
        genImageError: '',
        imgFile: null,
        imgLoading: false,
        imgError: '',
        imgToken: '',
        imgNotes: '',

        get pdfNeedsVisionModel() {
            return this.pdfKind === 'images';
        },

        get pdfSelectedModelSupportsVision() {
            if (!this.genPdf.modelId) return false;
            const m = this.aiModels.find(m => String(m.id) === String(this.genPdf.modelId));
            return Boolean(m && m.supports_vision);
        },

        get imgSelectedModelSupportsVision() {
            if (!this.genImage.modelId) return false;
            const m = this.aiModels.find(m => String(m.id) === String(this.genImage.modelId));
            return Boolean(m && m.supports_vision);
        },

        get visionModels() {
            return (this.aiModels || []).filter(m => m.supports_vision);
        },

        get canGenerateTopic() {
            return Boolean(this.genTopic.types.length) && Boolean(this.genTopic.topic.trim());
        },

        get canGeneratePdf() {
            if (!this.genPdf.types.length) return false;
            if (this.pdfKind === 'images') {
                return Boolean(this.pdfToken) && this.pdfSelectedModelSupportsVision;
            }
            return Boolean(this.pdfText.trim());
        },

        get canGenerateImage() {
            return Boolean(this.genImage.types.length) && Boolean(this.imgToken) && this.imgSelectedModelSupportsVision;
        },

        toggleAllGenTypes(section) {
            const all = this.types || [];
            const target = section === 'pdf' ? this.genPdf : section === 'image' ? this.genImage : this.genTopic;
            target.types = target.types.length === all.length ? [] : all.map(t => t.type);
        },

        resetPdfState() {
            this.pdfKind = '';
            this.pdfText = '';
            this.pdfToken = '';
            this.pdfPageCount = 0;
            this.pdfImagesCount = 0;
            this.pdfNotes = '';
            this.pdfError = '';
        },

        onPdfFileChange(event) {
            this.pdfFile = event.target.files?.[0] || null;
            this.resetPdfState();
        },

        resetImageState() {
            this.imgToken = '';
            this.imgNotes = '';
            this.imgError = '';
        },

        onImageFileChange(event) {
            this.imgFile = event.target.files?.[0] || null;
            this.resetImageState();
        },

        /** يختار أفضل نموذج رؤية متاح (الافتراضي إن كان يدعم الرؤية، وإلا أول نموذج رؤية نشط). */
        pickDefaultVisionModelId() {
            const visionModel = this.aiModels.find(m => m.supports_vision && m.is_default)
                || this.aiModels.find(m => m.supports_vision);

            return visionModel ? String(visionModel.id) : '';
        },

        /** الخطوة 1: رفع ملف PDF واستخراج محتواه للمعاينة (نص أو صور صفحات حسب الحاجة). */
        async extractPdfFile() {
            if (!this.pdfFile) return;
            this.pdfLoading = true;
            this.resetPdfState();
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 170000);
            try {
                const fd = new FormData();
                fd.append('file', this.pdfFile);
                const res = await fetch(this.urls.sourceExtractUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: fd,
                    signal: controller.signal,
                });
                const data = await res.json();
                if (!data.ok) throw new Error(data.message || 'فشل تحليل الملف');
                this.pdfKind = data.kind || '';
                this.pdfText = data.text || '';
                this.pdfToken = data.token || '';
                this.pdfPageCount = data.pageCount || 0;
                this.pdfImagesCount = data.imagesCount || 0;
                this.pdfNotes = data.notes || '';

                // الملف ممسوح ضوئياً ويحتاج نموذجاً يدعم الرؤية — إن كان المختار حالياً لا يدعمها فنبدّله تلقائياً
                if (this.pdfKind === 'images' && !this.pdfSelectedModelSupportsVision) {
                    this.genPdf.modelId = this.pickDefaultVisionModelId();
                }
            } catch (e) {
                this.pdfError = e.name === 'AbortError'
                    ? 'استغرق التحليل وقتاً أطول من المتوقع. جرّب ملفاً أصغر أو تحقق من الاتصال.'
                    : (e.message || 'فشل تحليل الملف');
            } finally {
                clearTimeout(timeoutId);
                this.pdfLoading = false;
            }
        },

        /** الخطوة 1: رفع صورة — تُقرأ فقط، تُحلّل بصرياً دائماً في خطوة التوليد. */
        async extractImageFile() {
            if (!this.imgFile) return;
            this.imgLoading = true;
            this.resetImageState();
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 170000);
            try {
                const fd = new FormData();
                fd.append('file', this.imgFile);
                const res = await fetch(this.urls.sourceExtractUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: fd,
                    signal: controller.signal,
                });
                const data = await res.json();
                if (!data.ok) throw new Error(data.message || 'فشل تحليل الملف');
                this.imgToken = data.token || '';
                this.imgNotes = data.notes || '';

                if (!this.imgSelectedModelSupportsVision) {
                    this.genImage.modelId = this.pickDefaultVisionModelId();
                }
            } catch (e) {
                this.imgError = e.name === 'AbortError'
                    ? 'استغرق التحليل وقتاً أطول من المتوقع. جرّب صورة أصغر أو تحقق من الاتصال.'
                    : (e.message || 'فشل تحليل الملف');
            } finally {
                clearTimeout(timeoutId);
                this.imgLoading = false;
            }
        },

        /** يعيد تشغيل نفس مسار التوليد الذي أنتج الدفعة المعروضة حالياً في نافذة المراجعة. */
        regenerateLast() {
            if (this.lastGenSource === 'pdf') return this.requestPdfGenerate();
            if (this.lastGenSource === 'image') return this.requestImageGenerate();

            return this.requestAiGenerate();
        },

        /** منطق مشترك للخطوة 2 (توليد من مصدر مستخرج) بين قسمَي PDF والصورة، مع إبقاء حالة كل قسم مستقلة. */
        async runSourceGeneration({ genState, isImageKind, token, text, sourceTag, setLoading, setError }) {
            this.lastGenSource = sourceTag;
            this.lastGenMode = genState.mode || 'replace';
            setLoading(true);
            this.genLoading = true;
            setError('');
            this.genError = '';
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 170000);
            let ok = false;
            try {
                const body = {
                    objectives: genState.objectives || '',
                    count: genState.count || 5,
                    difficulty: genState.difficulty || 'medium',
                    types: genState.types,
                    mode: genState.mode || 'replace',
                };
                if (isImageKind) body.token = token;
                else body.text = text;
                if (genState.modelId) body.model_id = parseInt(genState.modelId, 10);

                const res = await fetch(this.urls.sourceGenerateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.urls.csrf,
                    },
                    body: JSON.stringify(body),
                    signal: controller.signal,
                });
                const data = await res.json();
                if (!data.ok) throw new Error(data.message || 'فشل التوليد');

                this.genSummary = data.summary || '';
                this.genModel = data.model || '';
                this.genQuestions = (data.questions || []).map(q => ({ ...q, _selected: true }));
                ok = true;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ileGenerateModal')).show();
            } catch (e) {
                const message = e.name === 'AbortError'
                    ? 'استغرق التوليد وقتاً أطول من المتوقع. جرّب عدد أسئلة أقل أو نموذجاً أسرع.'
                    : (e.message || 'فشل التوليد');
                setError(message);
                this.genError = message;
            } finally {
                clearTimeout(timeoutId);
                setLoading(false);
                this.genLoading = false;
            }

            return ok;
        },

        /** الخطوة 2 لقسم PDF: توليد من النص المستخرج، أو من صور الصفحات إن كان ممسوحاً ضوئياً. */
        async requestPdfGenerate() {
            const isImageKind = this.pdfKind === 'images';
            const ok = await this.runSourceGeneration({
                genState: this.genPdf,
                isImageKind,
                token: this.pdfToken,
                text: this.pdfText,
                sourceTag: 'pdf',
                setLoading: v => { this.genPdfLoading = v; },
                setError: v => { this.genPdfError = v; },
            });
            // الملف المؤقّت يُحذف على الخادم بعد التوليد، فالرمز لم يعد صالحاً
            if (ok && isImageKind) this.pdfToken = '';
        },

        /** الخطوة 2 لقسم الصورة: تحليل بصري دائماً. */
        async requestImageGenerate() {
            const ok = await this.runSourceGeneration({
                genState: this.genImage,
                isImageKind: true,
                token: this.imgToken,
                text: '',
                sourceTag: 'image',
                setLoading: v => { this.genImageLoading = v; },
                setError: v => { this.genImageError = v; },
            });
            if (ok) this.imgToken = '';
        },

        typeMeta(type) { return this.types.find(t => t.type === type); },
        isOpen(qi) { return this.openAll || this.openIndex === qi; },
        toggleQuestion(qi) { this.openIndex = this.openIndex === qi && !this.openAll ? -1 : qi; this.openAll = false; },
        addQuestion(type) {
            const source = this.isDynamic
                ? (this.blankDynamicTemplates[type] || this.blankTemplates[type] || {})
                : (this.blankTemplates[type] || {});
            const tpl = JSON.parse(JSON.stringify(source));
            tpl.id = (crypto.randomUUID && crypto.randomUUID()) || ('q_' + Date.now());
            if (this.isDynamic) {
                tpl.type = tpl.interaction?.type || type;
                tpl.payload = tpl.interaction?.payload || tpl.payload || {};
                tpl.stemBlocks = tpl.stemBlocks || [{ type: 'text', text: tpl.stem || '' }];
            }
            this.pushHistory();
            this.schema.questions.push(tpl);
            this.openIndex = this.schema.questions.length - 1;
            this.validateLive();
        },
        removeQuestion(index) {
            this.pushHistory();
            this.schema.questions.splice(index, 1);
            this.openIndex = Math.min(this.openIndex, this.schema.questions.length - 1);
            this.validateLive();
        },
        addOption(q) {
            const n = q.payload.options?.length || 0;
            const id = String.fromCharCode(97 + n);
            q.payload.options.push({ id, label: 'خيار جديد', icon: '⭐', imageUrl: null, audioUrl: null });
        },
        toggleCorrectId(q, id) {
            q.payload.correctIds = q.payload.correctIds || [];
            const i = q.payload.correctIds.indexOf(id);
            if (i >= 0) q.payload.correctIds.splice(i, 1);
            else q.payload.correctIds.push(id);
        },
        addDragItem(q) {
            const id = 'i' + (q.payload.items.length + 1);
            q.payload.items.push({ id, label: 'عنصر' });
            q.payload.assignments[id] = q.payload.zones[0]?.id || '';
        },
        addDragZone(q) {
            const id = 'z' + (q.payload.zones.length + 1);
            q.payload.zones.push({ id, label: 'منطقة' });
        },
        addCategorizeItem(q) {
            q.payload.items = q.payload.items || [];
            q.payload.categories = q.payload.categories || [];
            q.payload.correct = q.payload.correct || {};
            let n = q.payload.items.length + 1;
            let id = 'i' + n;
            while (q.payload.items.some(it => it.id === id)) { n += 1; id = 'i' + n; }
            q.payload.items.push({ id, label: 'عنصر جديد', icon: '' });
            q.payload.correct[id] = q.payload.categories[0]?.id || '';
        },
        removeCategorizeItem(q, itemId) {
            q.payload.items = (q.payload.items || []).filter(it => it.id !== itemId);
            if (q.payload.correct) delete q.payload.correct[itemId];
        },
        addCategorizeCategory(q) {
            q.payload.categories = q.payload.categories || [];
            let n = q.payload.categories.length + 1;
            let id = 'c' + n;
            while (q.payload.categories.some(cat => cat.id === id)) { n += 1; id = 'c' + n; }
            q.payload.categories.push({ id, label: 'تصنيف جديد', icon: '' });
        },
        removeCategorizeCategory(q, categoryId) {
            q.payload.categories = (q.payload.categories || []).filter(cat => cat.id !== categoryId);
            const fallback = q.payload.categories[0]?.id || '';
            if (q.payload.correct) {
                Object.keys(q.payload.correct).forEach(itemId => {
                    if (q.payload.correct[itemId] === categoryId) q.payload.correct[itemId] = fallback;
                });
            }
        },
        addMatchingLeft(q) {
            q.payload.left = q.payload.left || [];
            q.payload.right = q.payload.right || [];
            q.payload.pairs = q.payload.pairs || {};
            let n = q.payload.left.length + 1;
            let id = 'l' + n;
            while (q.payload.left.some(l => l.id === id)) { n += 1; id = 'l' + n; }
            q.payload.left.push({ id, label: 'عنصر جديد' });
            q.payload.pairs[id] = q.payload.right[0]?.id || '';
        },
        removeMatchingLeft(q, leftId) {
            q.payload.left = (q.payload.left || []).filter(l => l.id !== leftId);
            if (q.payload.pairs) delete q.payload.pairs[leftId];
        },
        addMatchingRight(q) {
            q.payload.right = q.payload.right || [];
            let n = q.payload.right.length + 1;
            let id = 'r' + n;
            while (q.payload.right.some(r => r.id === id)) { n += 1; id = 'r' + n; }
            q.payload.right.push({ id, label: 'قيمة جديدة' });
        },
        removeMatchingRight(q, rightId) {
            q.payload.right = (q.payload.right || []).filter(r => r.id !== rightId);
            const fallback = q.payload.right[0]?.id || '';
            if (q.payload.pairs) {
                Object.keys(q.payload.pairs).forEach(leftId => {
                    if (q.payload.pairs[leftId] === rightId) q.payload.pairs[leftId] = fallback;
                });
            }
        },
        validateLive() {
            const errors = [];
            if (!this.schema.meta?.title?.trim()) errors.push('عنوان التجربة مطلوب.');
            if (!this.schema.questions.length) errors.push('أضف سؤالاً واحداً على الأقل.');
            this.schema.questions.forEach((q, i) => {
                const label = 'السؤال #' + (i + 1);
                if (!q.stem?.trim()) errors.push(label + ': نص السؤال مطلوب.');
                if ((q.type === 'single_choice' || q.type === 'multiple_choice') && (q.payload.options?.length || 0) < 2) {
                    errors.push(label + ': خياران على الأقل.');
                }
                if (q.type === 'multiple_choice' && (q.payload.correctIds?.length || 0) < 1) {
                    errors.push(label + ': حدد إجابة صحيحة واحدة على الأقل.');
                }
            });
            this.clientErrors = errors;
            return errors.length === 0;
        },
        prepareSubmit(e) {
            this.schema.rules.allowBack = this.schema.rules.allowBack === true || this.schema.rules.allowBack === 'true';
            this.schema.rules.shuffleQuestions = this.schema.rules.shuffleQuestions === true || this.schema.rules.shuffleQuestions === 'true';
            this.schema.rules.showExplanation = this.schema.rules.showExplanation === true || this.schema.rules.showExplanation === 'true';
            this.schema.questions.forEach(q => {
                if (q.type === 'true_false') q.payload.correct = q.payload.correct === true || q.payload.correct === 'true';
                if (q.type === 'ordering' && Array.isArray(q.payload?.items)) {
                    q.payload.correctOrder = q.payload.items.map(i => i.id);
                }
                if (q.type === 'puzzle_pieces' && Array.isArray(q.payload?.pieces)) {
                    q.payload.correctOrder = q.payload.pieces.map(i => i.id);
                }
                if (q.type === 'short_answer' && q.payload?.correct && (!q.payload.acceptedAnswers || !q.payload.acceptedAnswers.length)) {
                    q.payload.acceptedAnswers = [q.payload.correct];
                }
            });
            this.syncDynamicQuestions();
            if (!this.validateLive()) { e.preventDefault(); return; }
            // Alpine's :value binding on the hidden field is flushed via microtask and
            // may not have run yet when the native submit fires right after this handler
            // returns — set it explicitly so the just-synced schema is what gets sent.
            const schemaField = e.target.querySelector('input[name="schema_json"]');
            if (schemaField) schemaField.value = JSON.stringify(this.schema);
        },
        pushHistory() {
            this.history.push(JSON.parse(JSON.stringify(this.schema)));
            if (this.history.length > 20) this.history.shift();
        },
        undo() {
            if (!this.history.length) return;
            this.schema = ensure(this.history.pop());
            this.validateLive();
        },
        selectedOps() { return this.patchOperations.filter(op => op._selected !== false); },
        selectedGenQuestions() { return this.genQuestions.filter(q => q._selected !== false); },
        async requestAiPatch() {
            this.aiLoading = true; this.aiError = '';
            try {
                const res = await fetch(this.urls.patchUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: JSON.stringify({ intent: this.aiIntent, schema_json: this.schema }),
                });
                const data = await res.json();
                if (!data.ok) throw new Error(data.message || 'فشل الاقتراح');
                this.patchSummary = data.summary || '';
                this.patchOperations = (data.operations || []).map(op => ({ ...op, _selected: true }));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ilePatchModal')).show();
            } catch (e) {
                this.aiError = e.message || String(e);
                alert(this.aiError);
            } finally { this.aiLoading = false; }
        },
        async applySelectedPatches() {
            const ops = this.selectedOps().map(({ _selected, ...rest }) => rest);
            if (!ops.length) return;
            this.aiLoading = true; this.aiError = '';
            try {
                const res = await fetch(this.urls.applyUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: JSON.stringify({ operations: ops, schema_json: this.schema, persist: true }),
                });
                const data = await res.json();
                if (!data.ok) throw new Error((data.errors || []).join(' ') || 'فشل التطبيق');
                this.pushHistory();
                this.schema = ensure(data.schema);
                this.validateLive();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ilePatchModal')).hide();
                alert('تم تطبيق ' + data.applied + ' عملية.');
            } catch (e) {
                this.aiError = e.message || String(e);
            } finally { this.aiLoading = false; }
        },
        async requestAiGenerate() {
            if (!this.genTopic.topic.trim() || !this.genTopic.types.length) return;
            this.lastGenSource = 'topic';
            this.lastGenMode = this.genTopic.mode || 'replace';
            this.genTopicLoading = this.genLoading = true;
            this.genTopicError = this.genError = '';
            try {
                const body = {
                    topic: this.genTopic.topic.trim(),
                    objectives: this.genTopic.objectives || '',
                    count: this.genTopic.count || 5,
                    difficulty: this.genTopic.difficulty || 'medium',
                    types: this.genTopic.types,
                    mode: this.genTopic.mode || 'replace',
                };
                if (this.genTopic.modelId) body.model_id = parseInt(this.genTopic.modelId, 10);
                const res = await fetch(this.urls.generateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (!data.ok) throw new Error(data.message || 'فشل التوليد');
                this.genSummary = data.summary || '';
                this.genModel = data.model || '';
                this.genQuestions = (data.questions || []).map(q => ({ ...q, _selected: true }));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ileGenerateModal')).show();
            } catch (e) {
                const message = e.message || String(e);
                this.genTopicError = this.genError = message;
                alert(message);
            } finally { this.genTopicLoading = this.genLoading = false; }
        },
        async applyGeneratedQuestions() {
            const questions = this.selectedGenQuestions().map(({ _selected, ...rest }) => rest);
            if (!questions.length) return;
            this.genLoading = true; this.genError = '';
            this.syncDynamicQuestions();
            try {
                const res = await fetch(this.urls.generateApplyUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                    body: JSON.stringify({
                        questions,
                        schema_json: this.schema,
                        mode: this.lastGenMode || 'replace',
                        persist: true,
                    }),
                });
                const data = await res.json();
                if (!data.ok) throw new Error((data.errors || []).join(' ') || 'فشل تطبيق الأسئلة');
                this.pushHistory();
                this.schema = ensure(data.schema);
                this.validateLive();
                this.openIndex = 0;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ileGenerateModal')).hide();
                alert('تم إضافة ' + data.count + ' سؤال/أسئلة.');
            } catch (e) {
                this.genError = e.message || String(e);
            } finally { this.genLoading = false; }
        },
        async applyImportedQuestions(detail) {
            const questions = detail?.questions || [];
            if (!questions.length) return;
            this.syncDynamicQuestions();
            const res = await fetch(detail.applyUrl || this.urls.importApplyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.urls.csrf },
                body: JSON.stringify({
                    questions,
                    schema_json: this.schema,
                    mode: detail.mode || 'append',
                    persist: true,
                }),
            });
            const data = await res.json();
            if (!data.ok) {
                throw new Error((data.errors || []).join(' ') || data.message || 'فشل استيراد الأسئلة');
            }
            this.pushHistory();
            this.schema = ensure(data.schema);
            this.validateLive();
            this.openIndex = 0;
            alert('تم استيراد ' + (data.count || questions.length) + ' سؤال إلى التجربة.');
        },
        init() {
            this.validateLive();
            // قسم "من صورة" يعرض نماذج الرؤية فقط، فنهيّئه بأفضلها مباشرة بدل ترك القائمة فارغة الاختيار
            if (!this.genImage.modelId) this.genImage.modelId = this.pickDefaultVisionModelId();
        }
    };
}
</script>
@endpush
