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

class VideoTranscodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;
    public int $backoff = 60;

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        if (!str_starts_with($this->media->mime_type ?? '', 'video/')) {
            return;
        }

        $conversion = MediaConversion::create([
            'media_id' => $this->media->id,
            'type' => 'transcode',
            'status' => 'pending',
            'config' => ['format' => 'mp4', 'quality' => 'medium'],
        ]);

        try {
            $conversion->markProcessing();

            // Placeholder: In production, use FFmpeg or a cloud transcoder
            // For now, just extract metadata
            $disk = Storage::disk($this->media->disk);
            $size = $disk->size($this->media->path);

            $this->media->update([
                'metadata' => array_merge($this->media->metadata ?? [], [
                    'transcoded' => false,
                    'transcode_note' => 'FFmpeg not configured',
                    'file_size' => $size,
                ]),
            ]);

            $conversion->markCompleted(['note' => 'Transcoding requires FFmpeg']);
            
            Log::info('Video transcode job completed (metadata only)', ['media_id' => $this->media->id]);
        } catch (\Exception $e) {
            $conversion->markFailed($e->getMessage());
            Log::error('Video transcode failed', ['media_id' => $this->media->id, 'error' => $e->getMessage()]);
        }
    }
}
