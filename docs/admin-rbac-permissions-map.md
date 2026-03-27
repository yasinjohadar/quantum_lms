# Admin RBAC Permissions Map (Teachers/Supervisors)

This document standardizes the permissions used for teacher and supervisor operations and links each feature to a clear permission.

## Naming Rules

- Format: `resource-action`
- Read actions: `list`, `show`
- Write actions: `create`, `edit`, `update`, `delete`
- Domain actions: explicit verbs (`approve-review`, `submit-for-review`, `manage-classes`, `manage-subjects`)

## Teachers

### Teacher Assignments

- `teacher-assignment-list`: open teacher assignment list page.
- `teacher-assignment-show`: open single teacher assignment page.
- `teacher-assignment-update`: save assignment changes.
- `teacher-assignment-manage-classes`: update class assignments (`classes[]`).
- `teacher-assignment-manage-subjects`: update subject assignments (`subjects[]`, `required_pages[]`).
- `teacher-progress-view`: open teacher progress pages.
- `academic-year-list`: open academic years page.
- `academic-week-list`: open academic weeks page.

## Supervisors

### Supervisor Assignments

- `supervisor-assignment-list`: open supervisor assignment list page.
- `supervisor-assignment-show`: open single supervisor assignment page.
- `supervisor-assignment-update`: save assignment changes.
- `supervisor-assignment-manage-classes`: update class assignments (`classes[]`).
- `supervisor-assignment-manage-subjects`: update subject assignments (`subjects[]`).

## Ability Contract (Unified Role Gating)

- `isPlatformAdmin()`: admin bypass for scope and review restrictions.
- `usesTeacherAssignmentScope()`: teacher-only scope (excludes admin and supervisor hybrid).
- `usesSupervisorAssignmentScope()`: supervisor scope (excludes admin).
- `canReviewContent()`: review authority by permissions (not role-name coupling).
- `shouldSubmitContentForReview()`: content uploader flow for non-review teachers.

## Sidebar Guarding Matrix

- "تخصيص المشرفين": `supervisor-assignment-list`
- "تخصيص المعلمين": `teacher-assignment-list`
- "تقدم المعلمين": `teacher-progress-view`
- "السنوات والأسابيع الدراسية": `academic-year-list` OR `academic-week-list`

## Preset Usage (Multi-Supervisor Types)

- `supervisor-content-review`:
  - queue access + approve/reject flows.
- `supervisor-quiz-followup`:
  - quiz attempts + grading follow-up + stats.
- user can hold both presets; final capabilities are permission union.
- avoid direct `hasRole('supervisor')` coupling in UI/controller logic.

## Acceptance Checklist

- supervisor with `supervisor-content-review` sees review queue and can approve/reject.
- supervisor with `supervisor-quiz-followup` sees quiz follow-up only.
- hybrid supervisor (both presets) sees combined features.
- teacher uploader can submit for review but cannot approve.
- admin bypass remains full access.
- direct URL access for unauthorized actions returns `403`.

## Notes

- Keep backend middleware/authorization as source of truth.
- UI visibility (`@can`, `@canany`) must mirror backend permissions to avoid leaking inaccessible actions.
