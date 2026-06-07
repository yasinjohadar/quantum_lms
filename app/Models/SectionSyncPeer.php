<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSyncPeer extends Model
{
    public const TYPE_SECTION = 'section';

    public const TYPE_UNIT = 'unit';

    public const TYPE_LESSON = 'lesson';

    public const TYPE_QUIZ = 'quiz';

    protected $fillable = [
        'sync_group_id',
        'entity_type',
        'canonical_entity_id',
        'peer_entity_id',
        'target_subject_id',
    ];

    public function targetSubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'target_subject_id');
    }
}
