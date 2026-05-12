<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Storage\StorageMigrationService;
use App\Services\Storage\CloudFirstStorageRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorageMigrationController extends Controller
{
    protected StorageMigrationService $migrationService;
    protected CloudFirstStorageRouter $router;

    public function __construct(StorageMigrationService $migrationService, CloudFirstStorageRouter $router)
    {
        $this->middleware(['auth', 'admin']);
        $this->migrationService = $migrationService;
        $this->router = $router;
    }

    public function index()
    {
        $analysis = $this->migrationService->analyzeLocalFiles();
        $batches = $this->migrationService->getBatches(10);

        $migrationUrls = [
            'migrate' => route('admin.storage-migration.migrate'),
            'migrateAll' => route('admin.storage-migration.migrate-all'),
            'analyze' => route('admin.storage-migration.analyze'),
            'verifyBase' => url('/admin/storage-migration/verify'),
            'cleanupBase' => url('/admin/storage-migration/cleanup'),
            'batchBase' => url('/admin/storage-migration/batch'),
        ];

        return view('admin.pages.storage-migration.index', compact('analysis', 'batches', 'migrationUrls'));
    }

    public function analyze(?string $disk = null)
    {
        $analysis = $this->migrationService->analyzeLocalFiles($disk);
        return response()->json($analysis);
    }

    public function startMigration(Request $request)
    {
        $validated = $request->validate([
            'disk_name' => 'required|string',
            'batch_size' => 'nullable|integer|min:10|max:500',
            'async' => 'nullable|boolean',
            'delete_local' => 'nullable|boolean',
        ]);

        try {
            $batch = $this->migrationService->startMigration(
                $validated['disk_name'],
                $validated['batch_size'] ?? 50,
                $validated['async'] ?? true,
                (bool) ($validated['delete_local'] ?? false)
            );

            return response()->json([
                'success' => true,
                'batch_id' => $batch->id,
                'total_files' => $batch->total_files,
                'message' => "تم بدء ترحيل {$batch->total_files} ملف",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function startAllMigration(Request $request)
    {
        $validated = $request->validate([
            'batch_size' => 'nullable|integer|min:10|max:500',
            'async' => 'nullable|boolean',
            'delete_local' => 'nullable|boolean',
        ]);

        $results = $this->migrationService->migrateAll(
            $validated['batch_size'] ?? 50,
            $validated['async'] ?? true,
            (bool) ($validated['delete_local'] ?? false)
        );

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'تم بدء ترحيل جميع الملفات',
        ]);
    }

    public function batchStatus(int $batchId)
    {
        $status = $this->migrationService->getBatchStatus($batchId);
        
        if (!$status) {
            return response()->json(['success' => false, 'message' => 'الدفعة غير موجودة'], 404);
        }

        return response()->json(['success' => true, 'data' => $status]);
    }

    public function cancelBatch(int $batchId)
    {
        $result = $this->migrationService->cancelBatch($batchId);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'تم إلغاء الدفعة' : 'فشل الإلغاء',
        ]);
    }

    public function verify(string $diskName)
    {
        $verification = $this->migrationService->verifyMigration($diskName);
        return response()->json($verification);
    }

    public function cleanup(string $diskName)
    {
        $result = $this->migrationService->cleanupLocalAfterMigration($diskName);
        return response()->json($result);
    }

    public function batches()
    {
        $batches = $this->migrationService->getBatches(20);
        return response()->json($batches);
    }
}
