<?php

namespace App\Jobs\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\MediaConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;

class GenerateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Media $media,
        public string $variantName = 'thumbnail',
        public int $width = 150,
        public int $height = 150
    ) {}

    public function handle(): void
    {
        if (!str_starts_with($this->media->mime_type ?? '', 'image/')) {
            return;
        }

        $conversion = MediaConversion::create([
            'media_id' => $this->media->id,
            'type' => 'variant_' . $this->variantName,
            'status' => 'pending',
            'config' => ['width' => $this->width, 'height' => $this->height, 'variant' => $this->variantName],
        ]);

        try {
            $conversion->markProcessing();

            $disk = Storage::disk($this->media->disk);
            $content = $disk->get($this->media->path);

            $manager = new ImageManager(['driver' => 'gd']);
            $image = $manager->make($content);
            $image->resize($this->width, $this->height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $variantPath = $this->media->path . '/variants/' . $this->variantName . '.webp';
            $variantContent = $image->encode('webp', 80)->toDataUri();
            // Extract base64 content
            $variantContent = base64_decode(explode(',', $variantContent)[1]);

            $disk->put($variantPath, $variantContent, ['visibility' => 'public']);

            $variant = $this->media->variants()->updateOrCreate(
                ['name' => $this->variantName],
                [
                    'disk' => $this->media->disk,
                    'path' => $variantPath,
                    'mime_type' => 'image/webp',
                    'size' => strlen($variantContent),
                    'is_generated' => true,
                    'generated_at' => now(),
                ]
            );

            $conversion->markCompleted(['variant_id' => $variant->id]);

            Log::info('Thumbnail generated', [
                'media_id' => $this->media->id,
                'variant' => $this->variantName,
            ]);
        } catch (\Exception $e) {
            $conversion->markFailed($e->getMessage());
            Log::error('Thumbnail generation failed', [
                'media_id' => $this->media->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            }
        }
    }
}
