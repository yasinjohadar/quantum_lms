<?php

namespace App\Observers;

use App\Services\Curriculum\SectionSyncService;
use Illuminate\Database\Eloquent\Model;

class CurriculumEntitySyncObserver
{
    public function __construct(
        protected SectionSyncService $syncService
    ) {}

    public function updated(Model $model): void
    {
        $this->syncService->syncUpdated($model);
    }

    public function deleted(Model $model): void
    {
        $this->syncService->syncDeleted($model);
    }
}
