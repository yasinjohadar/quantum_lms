<?php

namespace App\Services\Curriculum;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SectionSyncPeer;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

class SectionSyncService
{
    public static bool $syncing = false;

    /** @var array<string, array<int, string>> */
    protected static array $syncFields = [
        SubjectSection::class => [
            'title',
            'description',
            'type',
            'order',
            'is_active',
        ],
        Unit::class => [
            'title',
            'description',
            'order',
            'is_active',
        ],
        Lesson::class => [
            'title',
            'description',
            'video_type',
            'video_url',
            'video_id',
            'thumbnail',
            'duration',
            'book_page_from',
            'book_page_to',
            'order',
            'is_active',
            'is_free',
            'is_preview',
        ],
        Quiz::class => [
            'title',
            'description',
            'instructions',
            'image',
            'duration_minutes',
            'show_timer',
            'auto_submit',
            'max_attempts',
            'delay_between_attempts',
            'pass_percentage',
            'total_points',
            'grading_method',
            'shuffle_questions',
            'shuffle_options',
            'questions_per_page',
            'allow_back_navigation',
            'show_result_immediately',
            'show_correct_answers',
            'show_explanation',
            'show_points_per_question',
            'review_options',
            'available_from',
            'available_to',
            'is_active',
            'is_published',
            'requires_password',
            'password',
            'require_webcam',
            'prevent_copy_paste',
            'fullscreen_required',
            'order',
            'scope',
        ],
    ];

    public function syncUpdated(Model $model): void
    {
        if (self::$syncing) {
            return;
        }

        $entityType = $this->entityTypeFor($model);
        if (! $entityType) {
            return;
        }

        $changedFields = array_keys($model->getChanges());
        $syncFields = array_intersect($changedFields, self::$syncFields[$model::class] ?? []);
        if ($syncFields === []) {
            return;
        }

        $payload = [];
        foreach ($syncFields as $field) {
            $payload[$field] = $model->getAttribute($field);
        }

        $peerIds = $this->resolvePeerEntityIds($entityType, (int) $model->getKey());
        if ($peerIds === []) {
            return;
        }

        self::$syncing = true;

        try {
            foreach ($peerIds as $peerId) {
                $peer = $this->resolveModel($entityType, $peerId);
                if (! $peer || $peer->trashed()) {
                    continue;
                }

                $peer->timestamps = false;
                $peer->fill($payload);
                $peer->updated_at = $model->updated_at;
                $peer->saveQuietly();
                $peer->timestamps = true;
            }
        } finally {
            self::$syncing = false;
        }
    }

    public function syncDeleted(Model $model): void
    {
        if (self::$syncing) {
            return;
        }

        $entityType = $this->entityTypeFor($model);
        if (! $entityType) {
            return;
        }

        $entityId = (int) $model->getKey();
        $peerIds = $this->resolvePeerEntityIds($entityType, $entityId);
        if ($peerIds === []) {
            return;
        }

        self::$syncing = true;

        try {
            foreach ($peerIds as $peerId) {
                $peer = $this->resolveModel($entityType, $peerId);
                if (! $peer || $peer->trashed()) {
                    continue;
                }
                $peer->delete();
            }
        } finally {
            self::$syncing = false;
        }
    }

    /**
     * @return array<int, int>
     */
    protected function resolvePeerEntityIds(string $entityType, int $entityId): array
    {
        $canonicalId = SectionSyncPeer::query()
            ->where('entity_type', $entityType)
            ->where('peer_entity_id', $entityId)
            ->value('canonical_entity_id');

        if ($canonicalId) {
            $hubId = (int) $canonicalId;
        } else {
            $hasCanonicalPeers = SectionSyncPeer::query()
                ->where('entity_type', $entityType)
                ->where('canonical_entity_id', $entityId)
                ->exists();

            if (! $hasCanonicalPeers) {
                return [];
            }

            $hubId = $entityId;
        }

        return SectionSyncPeer::query()
            ->where('entity_type', $entityType)
            ->where('canonical_entity_id', $hubId)
            ->pluck('peer_entity_id')
            ->map(fn ($id) => (int) $id)
            ->push($hubId)
            ->reject(fn ($id) => $id === $entityId)
            ->unique()
            ->values()
            ->all();
    }

    protected function entityTypeFor(Model $model): ?string
    {
        return match ($model::class) {
            SubjectSection::class => SectionSyncPeer::TYPE_SECTION,
            Unit::class => SectionSyncPeer::TYPE_UNIT,
            Lesson::class => SectionSyncPeer::TYPE_LESSON,
            Quiz::class => SectionSyncPeer::TYPE_QUIZ,
            default => null,
        };
    }

    protected function resolveModel(string $entityType, int $id): ?Model
    {
        return match ($entityType) {
            SectionSyncPeer::TYPE_SECTION => SubjectSection::withTrashed()->find($id),
            SectionSyncPeer::TYPE_UNIT => Unit::withTrashed()->find($id),
            SectionSyncPeer::TYPE_LESSON => Lesson::withTrashed()->find($id),
            SectionSyncPeer::TYPE_QUIZ => Quiz::withTrashed()->find($id),
            default => null,
        };
    }
}
