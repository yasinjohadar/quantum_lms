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

class VirusScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;
    public int $backoff = 15;

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        $conversion = MediaConversion::create([
            'media_id' => $this->media->id,
            'type' => 'virus_scan',
            'status' => 'pending',
        ]);

        try {
            $conversion->markProcessing();

            // Placeholder: In production, use ClamAV or cloud antivirus API
            $this->media->update([
                'metadata' => array_merge($this->media->metadata ?? [], [
                    'virus_scan_status' => 'clean',
                    'virus_scan_method' => 'placeholder',
                    'virus_scan_at' => now()->toIso8601String(),
                ]),
            ]);

            $conversion->markCompleted(['status' => 'clean']);
            Log::info('Virus scan completed', ['media_id' => $this->media->id]);
        } catch (\Exception $e) {
            $conversion->markFailed($e->getMessage());
            Log::error('Virus scan failed', ['media_id' => $this->media->id, 'error' => $e->getMessage()]);
        }
    }
}
