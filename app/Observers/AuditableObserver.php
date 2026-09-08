<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditableObserver
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    public function created(Model $model): void
    {
        $this->record('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->record(method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'force_deleted' : 'deleted', $model);
    }

    public function restored(Model $model): void
    {
        $this->record('restored', $model);
    }

    private function record(string $event, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $subjectKey = $this->subjectKey($model);
        if (! $subjectKey) {
            return;
        }

        [$old, $new] = match ($event) {
            'created' => [[], $this->relevantAttributes($model->getAttributes())],
            'updated' => $this->updateDiff($model),
            default => [null, $this->relevantAttributes($model->getAttributes())],
        };

        // updateDiff() يعيد null عندما لا يستحق التغيير التسجيل (مثل إعادة ترتيب فقط)
        if ($event === 'updated' && $old === null) {
            return;
        }

        $this->auditLog->logModelEvent(Auth::user(), $event, $model, $subjectKey, $old ?? [], $new ?? []);
    }

    private function updateDiff(Model $model): array
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes) || array_keys($changes) === ['order']) {
            return [null, null];
        }

        $old = $this->relevantAttributes(array_intersect_key($model->getOriginal(), $changes));

        return [$old, $this->relevantAttributes($changes)];
    }

    private function relevantAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip(['created_at', 'updated_at', 'deleted_at']));
    }

    private function subjectKey(Model $model): ?string
    {
        return match (get_class($model)) {
            Stage::class => 'stage',
            SchoolClass::class => 'class',
            Subject::class => 'subject',
            SubjectSection::class => 'subject_section',
            Unit::class => 'unit',
            Lesson::class => 'lesson',
            Question::class => 'question',
            Quiz::class => 'quiz',
            default => null,
        };
    }
}
