<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaConversion;
use App\Models\StorageSyncDeadLetter;
use App\Services\Media\MediaMetricsService;
use Illuminate\Http\Request;

class MediaMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $data = MediaMetricsService::getDashboardData();
        return view('admin.pages.media-monitoring.index', compact('data'));
    }

    public function retryConversion(int $conversionId)
    {
        $conversion = MediaConversion::findOrFail($conversionId);
        
        if ($conversion->canRetry()) {
            $conversion->update([
                'status' => 'pending',
                'error' => null,
            ]);

            // Re-dispatch the appropriate job
            $media = $conversion->media;
            if (str_starts_with($conversion->type, 'variant_')) {
                $variantName = str_replace('variant_', '', $conversion->type);
                dispatch(new \App\Jobs\Media\GenerateThumbnailJob($media, $variantName));
            } elseif ($conversion->type === 'optimize') {
                dispatch(new \App\Jobs\Media\OptimizeImageJob($media));
            } elseif ($conversion->type === 'transcode') {
                dispatch(new \App\Jobs\Media\VideoTranscodeJob($media));
            } elseif ($conversion->type === 'virus_scan') {
                dispatch(new \App\Jobs\Media\VirusScanJob($media));
            }
        }

        return redirect()->back()->with('success', 'تم إعادة محاولة التحويل');
    }

    public function retryDeadLetter(int $deadLetterId)
    {
        $deadLetter = StorageSyncDeadLetter::findOrFail($deadLetterId);
        
        dispatch(new \App\Jobs\StorageSyncJob(
            $deadLetter->file_path,
            $deadLetter->target_disk,
            $deadLetter->batch_id
        ));

        $deadLetter->markResolved();

        return redirect()->back()->with('success', 'تم إعادة محاولة المزامنة');
    }

    public function cleanupOrphans()
    {
        $orphans = Media::orphaned()
            ->whereNull('deleted_at')
            ->where('created_at', '<', now()->subDays(config('storage.retention_days', 30)))
            ->get();

        $count = $orphans->count();
        
        foreach ($orphans as $media) {
            app(\App\Services\Media\MediaManager::class)->forceDelete($media);
        }

        return redirect()->back()->with('success', "تم حذف {$count} ملف يتيم");
    }

    public function cleanupSoftDeleted()
    {
        $deleted = Media::deleted()
            ->where('deleted_at', '<', now()->subDays(config('storage.retention_days', 30)))
            ->get();

        $count = $deleted->count();
        
        foreach ($deleted as $media) {
            app(\App\Services\Media\MediaManager::class)->forceDelete($media);
        }

        return redirect()->back()->with('success', "تم حذف {$count} ملف محذوف نهائياً");
    }
}
