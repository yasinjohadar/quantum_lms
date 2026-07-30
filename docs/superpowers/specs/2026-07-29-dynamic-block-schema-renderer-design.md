# Dynamic Block Schema Renderer — Design Spec

**Date:** 2026-07-29  
**Status:** Accepted (implemented — wave 1 shipped in codebase)  
**Parent:** Interactive Learning Engine (`2026-07-29-interactive-learning-engine-design.md`)  
**Related:** Kids Play UI (`2026-07-29-kids-play-ui-interactive-media-design.md`)  
**Product intent:** NotebookLM / Gemini-like flexible presentation while remaining Schema-driven (no AI HTML/JS)

## 1. Context

The Interactive Learning Engine today uses **fixed per-type templates** (`modules/*.js`) and Schema 1.0 payloads. AI session generation fills those payloads, but content quality issues appear (e.g. counting questions without a scene; multiple emojis crammed into `icon`).

The product goal is a **wide, dynamic presentation system**: the AI (and authors) describe *what to show* via rich Schema blocks; the runtime chooses how to render and which **allowlisted** libraries to load (math, icons, stickers). Classic fixed modules remain available.

## 2. Decisions (approved)

| Decision | Choice |
|----------|--------|
| Presentation model | **Rich Schema blocks** rendered by the engine (not free HTML/JS) |
| Delivery wave | **One wave**: AI logic hardening + dynamic renderer together |
| Libraries | **Allowlist only** (bundled / first-party assets) |
| Coexistence | **Two modes** side by side: `classic` and `dynamic` |
| Mixing | **No mix** inside one experience — entire session is one mode |

**Rejected:** AI-emitted HTML/CSS/JS; arbitrary CDN loads; mixing classic and dynamic questions in one session (phase 1).

## 3. Goals

- Experience-level `mode`: `classic` | `dynamic`.
- Dynamic questions composed of **display blocks** + **interaction** (grading payload).
- Math via KaTeX; kid icons/stickers from curated sets; Lottie + TTS remain.
- AI generates Schema only; `ContentLogicChecker` prevents illogical counting/media.
- Classic path keeps working unchanged for existing experiences.
- Kids-friendly, clear, interactive play UI (Alexandria / kids theme preserved).

## 4. Non-goals (phase 1)

- Free CDN / arbitrary third-party script injection by AI
- Mixing `classic` and `dynamic` questions in one session
- Full Notion-like block drag editor
- New interaction types beyond reusing existing grading contracts
- Breaking Schema 1.0 classic experiences (no forced migration)

## 5. Architecture

```
Admin create/generate
    → mode = classic | dynamic
    → schema_json (v1 classic | v2 dynamic)
         ↓
Play runtime
    if classic → existing Registry + modules
    if dynamic → LibraryLoader (allowlist)
                 → BlockRenderer (stemBlocks / optionBlocks)
                 → InteractionModules (answer + grade)
         ↓
AI → JSON only → Normalize → ContentLogicChecker → Admin Review → Apply
```

**Security invariant (unchanged):** AI never emits executable HTML/JS into the runtime. Only allowlisted block types and library keys.

### 5.1 Folder additions (engine)

```
resources/js/interactive-engine/
  dynamic/
    BlockRenderer.js
    LibraryLoader.js
    blocks/          # text, math, icon, sticker, image, audio, scene
    interactions/    # thin adapters reusing classic grade logic where possible
  ...
app/InteractiveLearning/Services/
  ContentLogicChecker.php
  SchemaValidator.php          # extend with mode branch (classic vs dynamic); no second validator class
```

## 6. Schema

### 6.1 Root (dynamic experiences)

```json
{
  "version": "2.0",
  "mode": "dynamic",
  "meta": { "title": "", "locale": "ar", "rtl": true },
  "theme": { "themeId": "kids", "motion": "full" },
  "rules": { "allowBack": true },
  "assets": {
    "libraries": ["katex", "icons", "stickers", "lottie", "tts"]
  },
  "questions": []
}
```

- Classic experiences remain `version: "1.0"` (or omit `mode` → treat as `classic`).
- If `mode` is `dynamic`, validator requires `version: "2.0"` and block-shaped questions.
- Session-level `assets.libraries` is a hint; per-question `assets` may subset. Loader only loads allowlisted keys that are actually referenced.

### 6.2 Dynamic question shape

```json
{
  "id": "uuid",
  "stemBlocks": [],
  "interaction": {
    "type": "single_choice",
    "payload": {
      "options": [
        { "id": "a", "label": "2", "icon": "number-2", "sticker": null },
        { "id": "b", "label": "3", "icon": "number-3", "sticker": null },
        { "id": "c", "label": "5", "icon": "number-5", "sticker": null }
      ],
      "correctId": "b"
    }
  },
  "optionBlocks": {
    "a": []
  },
  "assets": { "libraries": ["katex"], "stickers": ["apple"] },
  "hints": [],
  "explanation": "",
  "successMessage": "",
  "errorMessage": "",
  "points": 1,
  "difficulty": "easy",
  "estimatedSeconds": 20
}
```

Notes:
- Prefer `stemBlocks` over plain `stem`. Optional short `stem` string allowed for admin lists / TTS fallback.
- `optionBlocks[id]` optional rich display beside/under each option; if empty, use option `label` + single `icon`/`sticker`.
- `interaction.type` values align with existing classic types that we support in phase 1 (at minimum: `true_false`, `single_choice`, `multiple_choice`, `numerical`, `short_answer`, `listen_choose`). Interactive game types (`memory_cards`, `drag_drop`, …) may stay classic-only in phase 1 unless an adapter is cheap.

### 6.3 Block types (phase 1 allowlist)

| type | Fields | Render |
|------|--------|--------|
| `text` | `text` | Paragraph (escaped) |
| `math` | `latex` | KaTeX |
| `icon` | `name` | SVG/icon from curated `icons` set (one name) |
| `sticker` | `name` | Image from `public/stickers/ile/{name}.webp` (or png) |
| `image` | `url`, optional `alt` | `<img>` with URL allow rules (same-origin or approved hosts) |
| `audio` | `text` and/or `audioUrl` | Speaker control → TTS / file |
| `scene` | `item` (sticker/icon name), `count` (int ≥ 0), optional `layout` | Repeats item `count` times — **source of truth for counting** |

Unknown block types are dropped at normalize time with a warning; play never executes custom code from Schema.

### 6.4 Golden content rules

1. Counting questions **must** include a `scene` block (or an `image` that is the countable scene). Stem must not imply “what he sees” without a scene/image.
2. Option `icon` / block `icon.name` is **one** curated id — never a string of repeated emojis used as a counter.
3. If an option label is numeric and pairs with a scene-based correct answer, `ContentLogicChecker` ensures `scene.count` equals the correct option’s numeric label (when both parse as integers).
4. `correctId` / `correctIds` must reference existing option ids.
5. Libraries referenced outside allowlist are stripped.

## 7. Allowlisted libraries

| Key | Purpose | Load strategy |
|-----|---------|---------------|
| `katex` | Math | Vite dependency; CSS + render on demand |
| `icons` | Curated kid icons | Local SVG sprite or icon map in repo |
| `stickers` | Kid stickers | Static files under `public/stickers/ile/` |
| `lottie` | Feedback motion | Existing `lottie-web` + `public/lottie/ile/` |
| `tts` | Arabic speech | Existing `/learning-experiences/tts` |

`LibraryLoader`:
- Resolves keys → dynamic `import()` / CSS inject once per session.
- Ignores unknown keys.
- Does not fetch arbitrary URLs dictated by AI.

## 8. Runtime behavior

1. Session reads `mode`. Branch classic vs dynamic.
2. Dynamic: for each question, load needed libraries, render `stemBlocks` via `BlockRenderer`, mount interaction UI, bind speakers/FX.
3. Grading: reuse classic grade functions via thin adapters keyed by `interaction.type`.
4. Block render failure → safe text fallback; session continues.
5. Kids theme + Alexandria font apply to both modes.

## 9. AI generation & logic checking

### 9.1 Dual prompts

- **Classic path:** tighten existing `AiSessionGenerationService` rules (single icon id/emoji; no multi-emoji counters; prefer coherent stems).
- **Dynamic path:** new prompt that emits `stemBlocks`, `interaction`, `assets`; requires `scene` for count topics; requires `math` blocks for formulas.

Admin selects mode before generate; generate refuses to mix.

### 9.2 Pipeline

1. Model returns JSON  
2. Parse + normalize (ids, strip illegal blocks/libs, coerce counts)  
3. `ContentLogicChecker` validate / auto-fix where safe  
4. Hard failures surface in Review UI (question omitted or flagged)  
5. Human Apply → stored `schema_json`

### 9.3 Checker responsibilities (minimum)

- Scene count ↔ correct numeric label consistency  
- Ban multi-grapheme emoji piles in `icon` fields  
- Missing scene when stem matches counting patterns (Arabic cues: كم عدد، عدّ، يراها، …)  
- Dangling `correctId`  
- Empty `stemBlocks`  

Auto-fix examples: clamp `scene.count` to correct label when mismatch is obvious and options are numeric; replace repeated emoji icon with single sticker + scene.

## 10. Admin UX

- Create/Edit: mode selector (`classic` | `dynamic`), locked after first save **or** convertible only via explicit “convert” (phase 1: lock after questions exist to avoid half-migrated schemas).
- Dynamic: block list editor (add/remove/reorder blocks), interaction type + payload fields, live preview pane.
- AI generate → modal Review (preview blocks) → Apply / discard.
- Classic screens unchanged.

## 11. Validation & compatibility

- `SchemaValidator` branches on `mode` / `version`.
- Classic Schema 1.0 remains valid.
- Dynamic requires version `2.0` and block rules above.
- Engine version bump documented in experience `engine_version` when dynamic play ships.
- Play controller passes `mode` into Vite bootstrap config.

## 12. Error handling

| Case | Behavior |
|------|----------|
| Invalid dynamic schema on publish | Block publish; show validator errors |
| Unknown library key | Strip + warning in Review |
| Unknown block type | Strip + warning |
| KaTeX render error | Show raw latex in monospace fallback |
| Missing sticker file | Placeholder sticker |
| Classic experience opened on old engine | Unaffected |

## 13. Testing / acceptance

1. Existing classic experience (e.g. id 5) still plays.  
2. New dynamic experience shows `text` + `math` (KaTeX) + `sticker`.  
3. Counting item uses `scene` with matching correct count; AI cannot save multi-emoji counter as the only visual.  
4. Network tab shows no AI-injected third-party scripts.  
5. Mode is exclusive per experience.  
6. TTS/Lottie still work on dynamic single_choice.  
7. ContentLogicChecker unit tests for scene/correct mismatch and icon pile rejection.

## 14. Implementation phases (within this wave)

1. Spec lock (this doc)  
2. Dynamic schema validator + blank templates  
3. BlockRenderer + LibraryLoader + blocks (`text`, `math`, `icon`, `sticker`, `scene`, `image`, `audio`)  
4. Interaction adapters (choice / true_false / numerical / short_answer / listen_choose)  
5. Play bootstrap mode switch  
6. Admin mode + minimal block editor + preview  
7. AI dynamic prompt + ContentLogicChecker + classic prompt harden  
8. Stickers/icons asset pack (small curated set)  
9. Verification against acceptance list  

## 15. Open points (resolved for phase 1)

- **Layout templates vs free blocks:** free blocks (approved approach 1).  
- **Library expansion:** later admin allowlist growth; not free CDN.  
- **Game types in dynamic:** deferred; use classic experiences for memory/drag/etc. unless adapters land cheaply.

## 16. ADR impact

Amends parent ADR: Schema may be `1.0` classic or `2.0` dynamic; AI still Schema-only; still no AI HTML/JS. Dual runtime paths are explicit and mode-gated.
