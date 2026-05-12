<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\MediaUsage;
use App\Models\MediaConversion;
use App\Models\StorageSyncDeadLetter;
use App\Services\Media\MediaManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ==================== Media List ====================
    public function index(Request $request)
    {
        $query = Media::with(['uploader', 'variants'])
            ->whereNull('deleted_at');

        // Filters
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }
        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->sync_status);
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }
        if ($request->filled('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type . '%');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', "%{$search}%")
                  ->orWhere('checksum', 'like', "%{$search}%");
            });
        }

        $media = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Media::whereNull('deleted_at')->count(),
            'synced' => Media::whereNull('deleted_at')->where('is_synced', true)->count(),
            'pending' => Media::whereNull('deleted_at')->where('is_synced', false)->count(),
            'orphaned' => Media::whereNull('deleted_at')->where('reference_count', 0)->count(),
            'total_size' => Media::whereNull('deleted_at')->sum('size'),
        ];

        return view('admin.pages.media.index', compact('media', 'stats'));
    }

    // ==================== Media Detail ====================
    public function show(Media $medium)
    {
        $medium->load(['uploader', 'variants', 'usages.model', 'conversions']);

        $usages = $medium->usages()->with('model')->get();
        $variants = $medium->variants;
        $conversions = $medium->conversions()->orderBy('created_at', 'desc')->get();

        return view('admin.pages.media.show', compact('medium', 'usages', 'variants', 'conversions'));
    }

    // ==================== Delete Media ====================
    public function destroy(Media $medium)
    {
        app(MediaManager::class)->forceDelete($medium);
        return redirect()->route('admin.media.index')->with('success', 'تم حذف الملف نهائياً');
    }

    public function softDelete(Media $medium)
    {
        $medium->softDelete();
        return redirect()->back()->with('success', 'تم حذف الملف مؤقتاً');
    }

    public function restore(Media $medium)
    {
        $medium->restore();
        return redirect()->back()->with('success', 'تم استعادة الملف');
    }

    public function syncNow(Media $medium)
    {
        dispatch(new \App\Jobs\StorageSyncJob($medium->path, $medium->disk));
        return redirect()->back()->with('success', 'تم جدولة المزامنة');
    }

    // ==================== Dead Letters ====================
    public function deadLetters(Request $request)
    {
        $query = StorageSyncDeadLetter::with('batch');

        if ($request->filled('resolved')) {
            $query->where('resolved', $request->resolved === '1');
        }
        if ($request->filled('target_disk')) {
            $query->where('target_disk', $request->target_disk);
        }

        $deadLetters = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => StorageSyncDeadLetter::count(),
            'unresolved' => StorageSyncDeadLetter::where('resolved', false)->count(),
            'resolved' => StorageSyncDeadLetter::where('resolved', true)->count(),
        ];

        return view('admin.pages.media.dead-letters', compact('deadLetters', 'stats'));
    }

    public function retryDeadLetter(StorageSyncDeadLetter $deadLetter)
    {
        dispatch(new \App\Jobs\StorageSyncJob(
            $deadLetter->file_path,
            $deadLetter->target_disk,
            $deadLetter->batch_id
        ));
        $deadLetter->markResolved();
        return redirect()->back()->with('success', 'تم إعادة محاولة المزامنة');
    }

    public function deleteDeadLetter(StorageSyncDeadLetter $deadLetter)
    {
        $deadLetter->delete();
        return redirect()->back()->with('success', 'تم حذف السجل');
    }

    public function resolveAllDeadLetters()
    {
        StorageSyncDeadLetter::where('resolved', false)->update(['resolved' => true, 'resolved_at' => now()]);
        return redirect()->back()->with('success', 'تم تحديد الكل كمحلول');
    }

    // ==================== Conversions ====================
    public function conversions(Request $request)
    {
        $query = MediaConversion::with('media');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $conversions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => MediaConversion::count(),
            'pending' => MediaConversion::where('status', 'pending')->count(),
            'processing' => MediaConversion::where('status', 'processing')->count(),
            'completed' => MediaConversion::where('status', 'completed')->count(),
            'failed' => MediaConversion::where('status', 'failed')->count(),
        ];

        return view('admin.pages.media.conversions', compact('conversions', 'stats'));
    }

    public function retryConversion(MediaConversion $conversion)
    {
        if ($conversion->canRetry()) {
            $conversion->update(['status' => 'pending', 'error' => null]);
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

    public function deleteConversion(MediaConversion $conversion)
    {
        $conversion->delete();
        return redirect()->back()->with('success', 'تم حذف السجل');
    }

    // ==================== Orphaned Files ====================
    public function orphans(Request $request)
    {
        $query = Media::orphaned()
            ->whereNull('deleted_at')
            ->with('uploader');

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        $orphans = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Media::orphaned()->whereNull('deleted_at')->count(),
            'total_size' => Media::orphaned()->whereNull('deleted_at')->sum('size'),
            'by_provider' => Media::orphaned()->whereNull('deleted_at')
                ->select('provider', DB::raw('count(*) as count'))
                ->groupBy('provider')
                ->get()
                ->mapWithKeys(fn($r) => [$r->provider => $r->count])
                ->toArray(),
        ];

        return view('admin.pages.media.orphans', compact('orphans', 'stats'));
    }

    public function deleteOrphans(Request $request)
    {
        $ids = $request->input('ids', []);
        $query = Media::orphaned()->whereNull('deleted_at');
        
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $count = $query->count();
        $query->each(fn($media) => app(MediaManager::class)->forceDelete($media));

        return redirect()->back()->with('success', "تم حذف {$count} ملف يتيم");
    }
}
