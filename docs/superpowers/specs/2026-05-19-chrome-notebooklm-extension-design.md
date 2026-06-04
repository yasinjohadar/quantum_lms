# Chrome Extension + NotebookLM → Quantum LMS Question Bank

**Date:** 2026-05-19  
**Status:** Approved for implementation

## Goal

Chrome extension (MV3) that authenticates staff via LMS API, extracts Quiz questions from NotebookLM (DOM + JSON fallback), previews them, and imports into a subject question bank.

## Constraints

- NotebookLM has no official public API for quiz generation; export only.
- DOM selectors are versioned (`v1`) and may break on Google UI updates.
- LMS currently had no REST API for questions; added `/api/v1/extension/*` with Sanctum.

## API (`/api/v1/extension`)

| Method | Path | Auth |
|--------|------|------|
| POST | `/auth/login` | Public (throttled) |
| POST | `/auth/logout` | Bearer |
| GET | `/auth/me` | Bearer |
| GET | `/curriculum/classes` | Bearer |
| GET | `/curriculum/subjects?class_id=` | Bearer |
| GET | `/curriculum/units?subject_id=` | Bearer |
| POST | `/questions/import` | Bearer + `question-import` |

### Import payload

```json
{
  "subject_id": 1,
  "unit_id": null,
  "questions": [
    {
      "title": "Question text",
      "type": "single_choice",
      "content": null,
      "explanation": "optional",
      "difficulty": "medium",
      "default_points": 1,
      "options": [{"text": "A", "is_correct": false}]
    }
  ]
}
```

### Import response

```json
{
  "imported": 5,
  "skipped": 1,
  "errors": [{"index": 2, "message": "..."}]
}
```

## Supported question types (phase 1)

- `single_choice`, `multiple_choice`, `true_false`
- `short_answer`, `fill_blanks`, `essay` when payload includes required fields

## Chrome extension layout

```
chrome-extension/
  manifest.json
  config/environments.json
  background/service-worker.js
  content/notebooklm.js
  popup/ (html, css, js)
  options/ (optional env override)
```

## Security

- Bearer tokens via Sanctum (`extension` token name).
- Staff only: active user + `question-import` permission.
- Curriculum scoped via existing teacher/supervisor assignment helpers.
- Rate limits: login 10/min, import 30/min.

## DOM extraction (v1)

See `docs/superpowers/specs/notebooklm-dom-selectors-v1.md`.
