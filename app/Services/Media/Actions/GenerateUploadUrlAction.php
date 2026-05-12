<?php

namespace App\Services\Media\Actions;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * GenerateUploadUrlAction
 * 
 * يولد Signed Upload URL للرفع المباشر من المتصفح إلى السحابة
 * يدعم: S3 Presigned URLs, multipart uploads
 */
class GenerateUploadUrlAction
{
    public function execute(string $fileName, string $mimeType, int $maxSize, string $disk = 's3', array $options = []): array
    {
        $key = $options['key'] ?? $this->generateKey($fileName);
        $expiresIn = $options['expires_in'] ?? 3600; // 1 hour default

        try {
            $adapter = Storage::disk($disk)->getAdapter();
            
            // S3 Presigned URL
            if ($adapter instanceof \League\Flysystem\AwsS3V3\AwsS3V3Adapter) {
                $client = $adapter->getClient();
                $bucket = $adapter->getBucket();

                $command = $client->getCommand('PutObject', [
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'ContentType' => $mimeType,
                    'ContentLength' => $maxSize,
                ]);

                $signedRequest = $client->createPresignedRequest($command, "+{$expiresIn} seconds");
                
                $uploadUrl = (string) $signedRequest->getUri();
                $headers = [
                    'Content-Type' => $mimeType,
                ];

                // Create Media record
                $media = Media::create([
                    'disk' => $disk,
                    'path' => $key,
                    'provider' => 's3',
                    'visibility' => $options['visibility'] ?? 'public',
                    'mime_type' => $mimeType,
                    'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                    'size' => 0,
                    'sync_status' => 'pending_direct_upload',
                    'metadata' => [
                        'original_name' => $fileName,
                        'max_size' => $maxSize,
                        'upload_method' => 'presigned_url',
                    ],
                ]);

                return [
                    'success' => true,
                    'media_id' => $media->id,
                    'upload_url' => $uploadUrl,
                    'headers' => $headers,
                    'method' => 'PUT',
                    'key' => $key,
                    'expires_at' => now()->addSeconds($expiresIn)->toIso8601String(),
                ];
            }

            // Fallback: generate local upload token
            return $this->generateLocalUploadToken($fileName, $mimeType, $maxSize, $disk, $key);

        } catch (\Exception $e) {
            Log::error('GenerateUploadUrlAction failed', [
                'file' => $fileName,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate upload URL: ' . $e->getMessage(),
            ];
        }
    }

    private function generateKey(string $fileName): string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        return 'uploads/' . date('Y/m/d') . '/' . Str::uuid() . '.' . $extension;
    }

    private function generateLocalUploadToken(string $fileName, string $mimeType, int $maxSize, string $disk, string $key): array
    {
        $token = hash_hmac('sha256', $key . now()->timestamp, config('app.key'));
        
        $media = Media::create([
            'disk' => $disk,
            'path' => $key,
            'provider' => 'local',
            'visibility' => 'public',
            'mime_type' => $mimeType,
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
            'size' => 0,
            'sync_status' => 'pending_token_upload',
            'metadata' => [
                'original_name' => $fileName,
                'max_size' => $maxSize,
                'upload_token' => $token,
                'upload_method' => 'token',
            ],
        ]);

        return [
            'success' => true,
            'media_id' => $media->id,
            'upload_url' => route('media.token-upload', ['token' => $token]),
            'headers' => [
                'X-Upload-Token' => $token,
                'X-Media-Id' => $media->id,
            ],
            'method' => 'POST',
            'key' => $key,
            'expires_at' => now()->addHour()->toIso8601String(),
        ];
    }
}
