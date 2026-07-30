# Dynamic Block Schema Renderer — Implementation Plan

> **For agentic workers:** Execute task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. User requested **inline execution** in this session.

**Goal:** Ship experience `mode: classic|dynamic` with Schema 2.0 block rendering (KaTeX, icons, stickers, scene), ContentLogicChecker, and hardened AI generation — without AI HTML/JS.

**Architecture:** Classic path unchanged. Dynamic experiences use `stemBlocks` + `interaction` reshaped to classic modules for grading. `LibraryLoader` only loads allowlisted Vite deps / local assets.

**Tech Stack:** Laravel SchemaValidator/ContentLogicChecker, Vite JS engine, KaTeX, Pest tests.

---

### Task 1: SchemaValidator dual-mode + blank dynamic templates

**Files:**
- Modify: `app/InteractiveLearning/Services/SchemaValidator.php`
- Modify: `tests/Unit/InteractiveLearningSchemaValidatorTest.php`

- [ ] Add `SCHEMA_VERSION_DYNAMIC = '2.0'`, resolve mode (`dynamic` if mode/version says so else `classic`)
- [ ] Classic validation unchanged for version `1.0`
- [ ] Dynamic: version `2.0`, validate `stemBlocks`, `interaction.type` + payload via existing payload validators
- [ ] `emptySchema($title, $mode = 'classic')`, `makeBlankDynamicQuestion($interactionType)`
- [ ] Pest tests for valid dynamic counting question + reject empty stemBlocks

### Task 2: ContentLogicChecker

**Files:**
- Create: `app/InteractiveLearning/Services/ContentLogicChecker.php`
- Create: `tests/Unit/ContentLogicCheckerTest.php`

- [ ] Detect counting stems without scene/image
- [ ] Scene count vs correct numeric label
- [ ] Reject multi-grapheme emoji piles in icon fields (classic + dynamic)
- [ ] `fix(array $question): array` applies safe auto-fixes

### Task 3: Engine dynamic renderer

**Files:**
- Create: `resources/js/interactive-engine/dynamic/allowlist.js`
- Create: `resources/js/interactive-engine/dynamic/LibraryLoader.js`
- Create: `resources/js/interactive-engine/dynamic/BlockRenderer.js`
- Create: `resources/js/interactive-engine/dynamic/blocks/*.js` (or single blocks.js)
- Create: `resources/js/interactive-engine/dynamic/stickers.js` (name → emoji/SVG fallback)
- Create: `resources/js/interactive-engine/dynamic/toClassicQuestion.js`
- Modify: `resources/js/interactive-engine/session/QuizSession.js`
- Modify: `resources/js/interactive-engine/styles.css`
- Modify: `package.json` (add `katex`)

- [ ] `npm install katex`
- [ ] BlockRenderer renders text/math/icon/sticker/image/audio/scene
- [ ] QuizSession: if `schema.mode === 'dynamic'`, render stemBlocks; mount module from `toClassicQuestion(q)`

### Task 4: Admin mode + minimal dynamic editor

**Files:**
- Modify: `resources/views/admin/pages/learning-experiences/create.blade.php`
- Modify: `app/InteractiveLearning/Http/Controllers/Admin/LearningExperienceController.php`
- Modify: `resources/views/admin/pages/learning-experiences/edit.blade.php`

- [ ] Create form: `experience_mode` classic|dynamic
- [ ] Store seeds empty schema with correct version/mode
- [ ] Edit: show badge; for dynamic questions edit stemBlocks JSON-ish list (text/scene/math) + interaction payload via existing type editors mapped from `interaction`

### Task 5: AI generate dynamic + harden classic

**Files:**
- Modify: `app/InteractiveLearning/Services/AiSessionGenerationService.php`
- Modify: Admin generate to pass `experience_mode` from schema.mode
- Wire ContentLogicChecker after normalize

- [ ] Classic: single-icon rule + strip emoji piles
- [ ] Dynamic: prompt for stemBlocks/interaction; normalize + checker

### Task 6: Build + verify

- [ ] `php artisan test --filter=InteractiveLearning`
- [ ] `php artisan test --filter=ContentLogic`
- [ ] `npm run build`
- [ ] Manual: create dynamic experience with scene + math, play locally

---

**Spec coverage:** Architecture §5, Schema §6, libraries §7, runtime §8, AI §9, admin §10, validation §11, acceptance §13.
