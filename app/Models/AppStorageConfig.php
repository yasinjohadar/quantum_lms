<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppStorageConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver',
        'config',
        'is_active',
        'priority',
        'redundancy',
        'pricing_config',
        'monthly_budget',
        'cost_alert_threshold',
        'cdn_url',
        'file_types',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'redundancy' => 'boolean',
        'pricing_config' => 'array',
        'monthly_budget' => 'decimal:2',
        'cost_alert_threshold' => 'decimal:2',
        'file_types' => 'array',
    ];

    /**
     * أنواع السواق
     */
    public const DRIVERS = [
        'local' => 'Local Storage',
        's3' => 'Amazon S3',
        'google_drive' => 'Google Drive',
        'dropbox' => 'Dropbox',
        'ftp' => 'FTP',
        'sftp' => 'SFTP',
        'azure' => 'Azure Blob Storage',
        'digitalocean' => 'DigitalOcean Spaces',
        'wasabi' => 'Wasabi',
        'backblaze' => 'Backblaze B2',
        'cloudflare_r2' => 'Cloudflare R2',
    ];

    /**
     * العلاقة مع منشئ الإعداد
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع الإحصائيات
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(AppStorageAnalytic::class, 'storage_config_id');
    }

    /**
     * العلاقة مع Disk Mappings
     */
    public function diskMappings(): HasMany
    {
        return $this->hasMany(StorageDiskMapping::class, 'primary_storage_id');
    }

    /**
     * العلاقة مع Fallback Disk Mappings
     */
    public function fallbackDiskMappings(): HasMany
    {
        return $this->hasMany(StorageDiskMapping::class, 'fallback_storage_ids');
    }

    /**
     * الحصول على الإعدادات (مفكوكة)
     */
    public function getDecryptedConfig(): array
    {
        try {
            return json_decode(Crypt::decryptString($this->config), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * حفظ الإعدادات (مشفرة)
     */
    public function setConfigAttribute($value)
    {
        if (is_array($value)) {
            // إزالة الحقول الفارغة للـ passwords
            $filtered = array_filter($value, function($v) {
                return $v !== null && $v !== '';
            });
            $this->attributes['config'] = Crypt::encryptString(json_encode($filtered));
        } else {
            $this->attributes['config'] = $value;
        }
    }

    /**
     * اختبار الاتصال
     */
    public function testConnection(): array
    {
        try {
            $driverConfig = $this->getDecryptedConfig();
            $driver = $this->driver;
            
            // تطبيع الإعدادات
            $driverConfig = \App\Services\Storage\StorageConfigNormalizer::normalize($driverConfig, $driver);
            
            // إنشاء disk مؤقت مع throw=true لالتقاط الأخطاء
            $diskName = 'app_storage_test_' . $this->id . '_' . time();
            $diskConfig = match($driver) {
                's3', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2' => [
                    'driver' => 's3',
                    'key' => $driverConfig['access_key_id'] ?? '',
                    'secret' => $driverConfig['secret_access_key'] ?? '',
                    'region' => $driverConfig['region'] ?? 'us-east-1',
                    'bucket' => $driverConfig['bucket'] ?? '',
                    'url' => $driverConfig['url'] ?? null,
                    'endpoint' => isset($driverConfig['endpoint']) && !str_starts_with($driverConfig['endpoint'], 'http://') && !str_starts_with($driverConfig['endpoint'], 'https://')
                        ? 'https://' . ltrim($driverConfig['endpoint'], '/')
                        : ($driverConfig['endpoint'] ?? null),
                    'use_path_style_endpoint' => \App\Services\Storage\StorageConfigNormalizer::toBool($driverConfig['use_path_style'] ?? false),
                    'throw' => true,
                ],
                default => \App\Services\Storage\AppStorageFactory::create($this),
            };
            
            if (is_array($diskConfig)) {
                \Illuminate\Support\Facades\Config::set("filesystems.disks.{$diskName}", $diskConfig);
                $disk = \Illuminate\Support\Facades\Storage::disk($diskName);
            } else {
                $disk = $diskConfig;
            }
            
            $testPath = 'test_' . time() . '.txt';
            
            // محاولة الكتابة
            try {
                $result = $disk->put($testPath, 'connection_test_' . now()->toIso8601String());
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $awsCode = $e->getAwsErrorCode() ?? 'unknown';
                $awsType = $e->getAwsErrorType() ?? 'unknown';
                $statusCode = $e->getStatusCode();
                
                Log::error('S3 connection test failed (S3Exception)', [
                    'storage_id' => $this->id,
                    'driver' => $driver,
                    'bucket' => $driverConfig['bucket'] ?? '',
                    'endpoint' => $driverConfig['endpoint'] ?? 'default',
                    'use_path_style' => $driverConfig['use_path_style'] ?? false,
                    'aws_error_code' => $awsCode,
                    'aws_error_type' => $awsType,
                    'status_code' => $statusCode,
                    'message' => $e->getMessage(),
                ]);
                
                $friendlyMessage = match($awsCode) {
                    'InvalidAccessKeyId' => 'مفتاح الوصول (Access Key ID) غير صحيح',
                    'SignatureDoesNotMatch' => 'المفتاح السري (Secret Access Key) غير صحيح',
                    'NoSuchBucket' => 'الـ Bucket غير موجود',
                    'AccessDenied' => 'لا توجد صلاحيات كافية (تحقق من IAM Policy)',
                    'InvalidBucketName' => 'اسم Bucket غير صحيح',
                    'PermanentRedirect' => 'المنطقة (Region) غير صحيحة',
                    default => "خطأ S3: {$awsCode} - {$e->getMessage()}",
                };
                
                return ['success' => false, 'message' => $friendlyMessage];
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                Log::error('S3 connection test failed (network)', [
                    'storage_id' => $this->id,
                    'driver' => $driver,
                    'endpoint' => $driverConfig['endpoint'] ?? 'default',
                    'message' => $e->getMessage(),
                ]);
                return ['success' => false, 'message' => 'فشل الاتصال بالخادم: ' . $e->getMessage()];
            }
            
            if (!$result) {
                // إذا لم يرمِ استثناء ولكن put() أرجع false
                Log::error('S3 put returned false without exception', [
                    'storage_id' => $this->id,
                    'driver' => $driver,
                    'bucket' => $driverConfig['bucket'] ?? '',
                    'endpoint' => $driverConfig['endpoint'] ?? 'default',
                    'use_path_style' => $driverConfig['use_path_style'] ?? false,
                    'region' => $driverConfig['region'] ?? '',
                ]);
                return [
                    'success' => false,
                    'message' => 'فشل الكتابة بدون خطأ واضح. تحقق من: الصلاحيات (IAM Policy)، صحة Bucket، والمنطقة (Region).',
                ];
            }
            
            // محاولة الحذف
            try {
                $disk->delete($testPath);
            } catch (\Exception $e) {
                Log::warning('S3 test file delete failed', [
                    'storage_id' => $this->id,
                    'message' => $e->getMessage(),
                ]);
            }
            
            return ['success' => true, 'message' => 'الاتصال ناجح'];
            
        } catch (\Exception $e) {
            Log::error('Storage connection test failed (general)', [
                'storage_id' => $this->id,
                'driver' => $this->driver,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'فشل الاتصال: ' . $e->getMessage()];
        }
    }
}
