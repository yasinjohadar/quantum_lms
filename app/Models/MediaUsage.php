<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'model_type',
        'model_id',
        'collection',
        'field',
        'usage_context',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function attach(Media $media, $model, string $collection = 'default', ?string $field = null, ?string $context = null): self
    {
        $usage = self::firstOrCreate([
            'media_id' => $media->id,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'collection' => $collection,
            'field' => $field,
        ], [
            'usage_context' => $context,
        ]);

        $media->incrementReference();

        return $usage;
    }

    public static function detach(Media $media, $model, string $collection = 'default', ?string $field = null): bool
    {
        $deleted = self::where('media_id', $media->id)
            ->where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('collection', $collection)
            ->when($field, fn($q) => $q->where('field', $field))
            ->delete();

        if ($deleted) {
            $media->decrementReference();
        }

        return $deleted > 0;
    }

    public static function detachAll(Media $media): int
    {
        $count = self::where('media_id', $media->id)->count();
        self::where('media_id', $media->id)->delete();

        if ($count > 0) {
            $media->reference_count = max(0, $media->reference_count - $count);
            $media->save();
        }

        return $count;
    }
}
