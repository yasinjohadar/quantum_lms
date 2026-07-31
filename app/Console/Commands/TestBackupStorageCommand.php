<?php

namespace App\Console\Commands;

use App\Models\AppStorageConfig;
use Illuminate\Console\Command;

class TestBackupStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:test-storage {config? : ID of AppStorageConfig to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار اتصالات أماكن التخزين العامة (AppStorageConfig)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $configId = $this->argument('config');

        if ($configId) {
            $config = AppStorageConfig::find($configId);
            if (! $config) {
                $this->error('إعدادات التخزين غير موجودة.');

                return Command::FAILURE;
            }

            $ok = $this->testConfig($config);

            return $ok ? Command::SUCCESS : Command::FAILURE;
        }

        $configs = AppStorageConfig::where('is_active', true)->get();

        if ($configs->isEmpty()) {
            $this->info('لا توجد أماكن تخزين نشطة.');

            return Command::SUCCESS;
        }

        $allOk = true;
        foreach ($configs as $config) {
            $allOk = $this->testConfig($config) && $allOk;
        }

        return $allOk ? Command::SUCCESS : Command::FAILURE;
    }

    private function testConfig(AppStorageConfig $config): bool
    {
        $this->info("اختبار: {$config->name} ({$config->driver})...");

        $result = $config->testConnection();

        if ($result['success']) {
            $this->info("✓ نجح الاتصال بـ {$config->name}");
        } else {
            $this->error("✗ فشل الاتصال بـ {$config->name}: {$result['message']}");
        }

        return $result['success'];
    }
}
