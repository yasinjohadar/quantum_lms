# Kids Play UI + Interactive Option Media — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a child-friendly play theme with Lottie feedback and per-option icon/image/speaker controls on the Interactive Learning Engine.

**Architecture:** Extend Schema option objects with optional media fields; restyle Vite play shell via `themeId: kids`; render media in question modules; drive feedback with local Lottie JSON + existing `/sounds/ile` MP3s.

**Tech Stack:** Laravel SchemaValidator, Vite `interactive-engine`, `lottie-web`, Alpine admin editor, edge-tts MP3 assets (existing)

**Spec:** `docs/superpowers/specs/2026-07-29-kids-play-ui-interactive-media-design.md`

---

### Task 1: Schema media fields + kids defaults

**Files:**
- Modify: `app/InteractiveLearning/Services/SchemaValidator.php`
- Modify: `tests/Unit/InteractiveLearningSchemaValidatorTest.php`

- [ ] Accept optional `icon`, `imageUrl`, `audioUrl` on option-like objects
- [ ] Default `theme.themeId` to `kids` in `emptySchema`
- [ ] Blank options include placeholder `icon`
- [ ] Update/extend unit tests; run pest filter

### Task 2: Install lottie-web + local animations

**Files:**
- Modify: `package.json`
- Create: `public/lottie/ile/success.json`, `try-again.json`, `celebrate.json`

- [ ] `npm install lottie-web`
- [ ] Add compact Lottie JSON assets (or minimal valid fallbacks)

### Task 3: Kids CSS theme

**Files:**
- Modify: `resources/js/interactive-engine/styles.css`

- [ ] `[data-theme-id="kids"]` tokens: light bg, green accent, soft cards
- [ ] Option card, speaker button, progress %, Lottie host styles

### Task 4: Play shell + FeedbackFx Lottie

**Files:**
- Modify: `resources/js/interactive-engine/session/QuizSession.js`
- Modify: `resources/js/interactive-engine/fx/FeedbackFx.js`
- Create: `resources/js/interactive-engine/fx/LottieIcon.js`

- [ ] Kids header (progress %, instructions button, mute)
- [ ] Feedback markup with `#ile-lottie` host
- [ ] Play Lottie on cheer; keep MP3 voice; no WebAudio music

### Task 5: Modules — icon / image / speaker

**Files:**
- Modify: `resources/js/interactive-engine/modules/_helpers.js`
- Modify: `single_choice.js`, `multiple_choice.js`, `true_false.js`, `drag_drop.js`, `matching.js`

- [ ] Shared `renderOptionMedia(opt)` helper
- [ ] Speaker plays `audioUrl` when present
- [ ] Selected state animation

### Task 6: Admin editor + AI icons

**Files:**
- Modify: `resources/views/admin/pages/learning-experiences/edit.blade.php`
- Modify: `app/InteractiveLearning/Services/AiSessionGenerationService.php`
- Modify: `app/InteractiveLearning/Services/AiPatchService.php`

- [ ] Icon field beside options in Alpine editor
- [ ] AI prompts request fitting emoji `icon`

### Task 7: Build + verify

- [ ] `npm run build`
- [ ] `php artisan test --filter=InteractiveLearning`
- [ ] Smoke: open play page, check kids theme + feedback animation
