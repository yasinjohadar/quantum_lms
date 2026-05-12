<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageSyncDeadLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'target_disk',
        'batch_id',
        'error',
        'attempts',
        'resolved',
        'resolved_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StorageSyncBatch::class);
    }

    public function markResolved(): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
        ]);
    }
}
