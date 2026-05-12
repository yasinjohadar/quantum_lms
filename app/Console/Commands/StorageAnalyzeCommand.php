<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageMigrationService;
use Illuminate\Console\Command;

class StorageAnalyzeCommand extends Command
{
    protected $signature = 'storage:analyze {disk? : اسم الـ disk المراد تحليله}';
    protected $description = 'تحليل الملفات المحلية التي يمكن ترحيلها إلى السحابة';

    public function handle(StorageMigrationService $migrationService): int
    {
        $disk = $this->argument('disk');
        $analysis = $migrationService->analyzeLocalFiles($disk);

        $this->info("=== تحليل الملفات المحلية ===");
        $this->newLine();

        foreach ($analysis['disks'] as $diskName => $data) {
            $prefixLabel = ! empty($data['prefixes'])
                ? implode(', ', $data['prefixes'])
                : ($data['path_prefix'] ?? '');
            $this->info("📁 {$diskName} ({$prefixLabel})");
            $this->line("   الملفات: {$data['total_files']}");
            $this->line("   الحجم: {$data['total_size_formatted']}");
            $this->newLine();
        }

        $this->info("=== الإجمالي ===");
        $this->line("   الملفات: {$analysis['total_files']}");
        $this->line("   الحجم: {$analysis['total_size_formatted']}");

        return Command::SUCCESS;
    }
}
