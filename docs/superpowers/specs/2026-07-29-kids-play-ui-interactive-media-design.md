# Kids Play UI + Interactive Option Media — Design Spec

**Date:** 2026-07-29  
**Status:** Accepted (pending user file review)  
**Parent:** Interactive Learning Engine (`2026-07-29-interactive-learning-engine-design.md`)  
**Scope:** Play-surface redesign for young students + option-level icons/audio + Lottie feedback

## 1. Context

The Interactive Learning Engine MVP already supports five question types, Arabic spoken cheer clips (`/sounds/ile/*.mp3`), and a dark play UI. Target learners are **young children**. A reference product UI (“Smart Animal World”) shows the desired feel: light soft UI, large touch targets, picture+label options, and a speaker on every choice.

NotebookLM/Gemini-style **micro-interactivity** is achieved with **Lottie** animations for feedback (not music synths). Spoken feedback stays on recorded MP3 clips.

## 2. Decision

Implement **Approach 1** (approved):

1. Full **kids play theme** for the Vite engine (default for new experiences).
2. **Per-option media**: `icon` (emoji) and optional `imageUrl` / `audioUrl`.
3. **Lottie** for success / try-again / celebrate feedback icons.
4. Keep existing question types and Schema 1.0 contracts; extend option objects only.
5. Defer AI image generation and Rive to a later phase.

## 3. Goals

- Play screen feels like a kids game: light colors, large cards, clear progress.
- Every choice can show an emoji/image and play its label (or `audioUrl`) via a speaker button.
- Correct/wrong/results use Lottie + existing Arabic voice MP3s (no synth “music”).
- Admin editor and AI generation can fill `icon` without breaking validation.
- No AI HTML/JS injection.

## 4. Non-goals (this phase)

- AI-generated images for options
- Rive runtime
- Multi-theme marketplace beyond `default` + `kids`
- Changing attempt scoring / API contracts beyond optional media fields

## 5. Play UI (theme `kids`)

| Element | Behavior |
|---------|----------|
| Shell | Light soft background (green/cream accents), rounded cards, large type |
| Header | Experience title + “نسبة التقدم” bar with percent |
| Instructions | Optional “استمع للتعليمات” button → plays `schema.meta.instructionsAudioUrl` or a short local clip if absent |
| Question card | Large stem; modules render kid-styled controls |
| Feedback | Lottie slot + Arabic phrase + existing cheer voice |
| Footer | Large CTAs: «يلا نتحقق!» / «يلا نكمّل!» / «رجوع» |
| Mute | Retain 🔊/🔇 for voice clips |

`schema.theme.themeId = 'kids'` activates this skin. Existing dark `default` remains available.

## 6. Option media contract

Extend choice-like items:

```json
{
  "id": "a",
  "label": "الأسد",
  "icon": "🦁",
  "imageUrl": null,
  "audioUrl": null
}
```

Applies to:

- `single_choice` / `multiple_choice` → `payload.options[]`
- `drag_drop` → `payload.items[]` and `payload.zones[]` (icon optional)
- `matching` → `payload.left[]` and `payload.right[]`
- `true_false` → fixed icons (✅ / ❌) with optional speak of «صح» / «خطأ»

**Playback rule:** speaker plays `audioUrl` if set; otherwise skips speech for that option in phase 1 (do not invent browser TTS music). Authors/AI may attach pre-generated clips later. Cheer voices for feedback stay on `/sounds/ile/`.

**Display rule:** if `imageUrl` set, show image; else show `icon` emoji (fallback ⭐).

## 7. Lottie feedback

- Dependency: `lottie-web` in the Vite engine bundle.
- Assets (local JSON): `public/lottie/ile/{success,try-again,celebrate}.json`
- Mount inside `#ile-feedback` / results card; play once on state change.
- Respect `theme.motion === 'reduced'|'off'` → static fallback emoji/icon.

## 8. Schema validation

`SchemaValidator` accepts optional string fields `icon`, `imageUrl`, `audioUrl` on option-like objects. Empty/null OK. Reject non-string types. Do not require them for validity.

Default blank options in `makeBlankQuestion` include a placeholder `icon`.

Default `emptySchema` / new experiences: `theme.themeId = 'kids'` and kid message lists already used.

## 9. Authoring + AI

- Admin edit UI: emoji/icon input beside each option label; optional URL fields collapsed under “وسائط”.
- `AiSessionGenerationService` / patch prompts: instruct model to set kid-friendly Arabic labels and a fitting `icon` emoji per option; still no HTML/JS.
- Full generation remains Schema-only.

## 10. Modules / engine changes

| File area | Change |
|-----------|--------|
| `styles.css` | Kids theme tokens + soft option cards + speaker button |
| `QuizSession.js` | Kids shell markup, progress %, instructions button, Lottie host |
| `fx/FeedbackFx.js` | Drive Lottie + existing MP3s (no WebAudio music) |
| `modules/*` | Render icon/image + speaker; selected state animation |
| `SchemaValidator.php` | Optional media fields; kids defaults |
| `AiSessionGenerationService.php` | Prompt for icons |

## 11. Risks & mitigations

| Risk | Mitigation |
|------|------------|
| Lottie files missing | Fallback emoji; engine still works |
| Option `audioUrl` empty | Speaker disabled/greyed; feedback voices still work |
| Old schemas without icons | Fallbacks; no migration required |
| Bundle size | Few small Lottie JSON files; tree-shake unused |

## 12. Acceptance criteria

1. Opening a published experience with `themeId: kids` shows the light kids shell.
2. Single/multiple choice options show icon/image and a speaker control.
3. Correct answer shows Lottie success + Arabic cheer MP3.
4. Wrong answer shows Lottie try-again + Arabic encourage MP3.
5. Results pass state shows celebrate Lottie + pass voice.
6. Schemas without media fields still validate and play.
7. AI generate fills `icon` on new options when possible.

## 13. Implementation order (high level)

1. Schema optional fields + kids defaults  
2. Kids CSS theme + shell  
3. Option UI (icon/image/speaker) in modules  
4. Lottie wiring in feedback/results  
5. Admin editor fields  
6. AI prompt updates  
7. Build + manual playtest
