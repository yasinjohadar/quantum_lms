<?php

namespace App\Jobs\Media;

use App\Models\Media;
use App\Models\MediaConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 2;
    public int $backoff = 30;

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        if (!str_starts_with($this->media->mime_type ?? '', 'image/')) {
            return;
        }

        $conversion = MediaConversion::create([
            'media_id' => $this->media->id,
            'type' => 'optimize',
            'status' => 'pending',
        ]);

        try {
            $conversion->markProcessing();

            $disk = Storage::disk($this->media->disk);
            $content = $disk->get($this->media->path);
            $originalSize = strlen($content);

            // Simple optimization: convert to WebP if not already
            if ($this->media->mime_type !== 'image/webp' && function_exists('imagewebp')) {
                $image = imagecreatefromstring($content);
                if ($image) {
                    ob_start();
                    imagewebp($image, null, 80);
                    $optimized = ob_get_clean();
                    imagedestroy($image);

                    if (strlen($optimized) < $originalSize) {
                        $disk->put($this->media->path, $optimized, ['visibility' => 'public']);
                        
                        $this->media->update([
                            'mime_type' => 'image/webp',
                            'size' => strlen($optimized),
                        ]);
                    }
                }
            }

            $conversion->markCompleted([
                'original_size' => $originalSize,
                'optimized_size' => $this->media->size,
                'savings' => $originalSize > 0 ? round((1 - $this->media->size / $originalSize) * 100, 1) : 0,
            ]);
        } catch (\Exception $e) {
            $conversion->markFailed($e->getMessage());
            Log::error('Image optimization failed', ['media_id' => $this->media->id, 'error' => $e->getMessage()]);
        }
    }
}
