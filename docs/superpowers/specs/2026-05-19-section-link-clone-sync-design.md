# Section Link Clone Sync — Design Spec

Date: 2026-05-19

## Problem

Previously, linking a section to another subject used a single `subject_sections` row plus `section_subjects` pivot. Deleting the section removed it from all subjects (`onDelete cascade` on pivot).

## Solution

When linking section **S** to subject **T**:

1. Create a **full copy** of the section subtree (sections, units, lessons, quizzes, attachments, quiz question links).
2. Assign a shared `sync_group_id` and register entity pairs in `section_sync_peers`.
3. **Bidirectional sync** on content fields (last-write-wins via `updated_at`).
4. Deleting the **canonical** section only removes the original; mirrors remain in other subjects.
5. Removing a link soft-deletes the mirror for that subject only.

## Schema

### `subject_sections`

| Column | Purpose |
|--------|---------|
| `sync_group_id` | UUID grouping all synced copies |
| `is_sync_canonical` | True for the original tree |
| `cloned_from_section_id` | Anchor section this mirror was cloned from |

### `section_sync_peers`

Maps canonical entity IDs to mirror entity IDs per `entity_type` (`section`, `unit`, `lesson`, `quiz`).

## Services

- `SectionCloneService` — clone/remove subtrees, canonical delete vs mirror delete
- `SectionSyncService` — propagate updates/deletes to peers (loop guard via `$syncing`)

## Migration

```bash
php artisan sections:migrate-links-to-copies
php artisan sections:migrate-links-to-copies --dry-run
```

Converts legacy `section_subjects` pivot rows into full copies, then removes pivot rows.

## Limitations (v1)

- Simultaneous edits: last-write-wins
- Question bank questions are not duplicated (quiz links reuse same `question_id`)
- Large section trees may be slow (single DB transaction per link)
