# NotebookLM DOM Selectors v1

**Last updated:** 2026-05-20

## Prerequisites

1. Open a **Quiz** artifact in NotebookLM (`https://notebooklm.google.com/`).
2. Ensure quiz questions are visible (scroll if lazy-loaded).
3. Open the Quantum LMS extension popup and click **استخراج من الصفحة**.

## Extraction strategy

The content script (`chrome-extension/content/notebooklm.js`) runs in **all frames** (`all_frames` + `match_origin_as_fallback`). The background worker merges results from every frame via `webNavigation.getAllFrames`.

Strategies (in order):

1. **`data-app-data` on `app-root`** — JSON embedded in NotebookLM iframes.
2. **Quiz panel UI** — detect `Question N of M`, options `A.`–`D.`, and `Right answer` labels.
3. **Auto-pagination** — clicks Next up to 30 times to collect all questions in a quiz.
4. **Embedded `<script>` JSON** — quiz arrays in page scripts.
5. **Fallback** — paste JSON manually in the popup (NotebookLM Quiz Exporter format).

## Expected normalized output

```json
{
  "source": "notebooklm_dom",
  "questions": [
    {
      "title": "نص السؤال",
      "type": "single_choice",
      "explanation": "",
      "difficulty": "medium",
      "default_points": 1,
      "options": [
        {"text": "الخيار أ", "is_correct": true},
        {"text": "الخيار ب", "is_correct": false}
      ]
    }
  ]
}
```

## Maintenance

When Google changes the UI:

1. Reproduce on live Quiz page.
2. Update selectors in `notebooklm.js` only.
3. Bump version in `manifest.json`.
4. Document changes in this file.

## User guide (Arabic)

دليل المعلم الكامل: [`docs/guides/notebooklm-extension-teacher-guide.md`](../../guides/notebooklm-extension-teacher-guide.md)

If extraction fails, export JSON from a compatible tool and use **لصق JSON** in the popup.
