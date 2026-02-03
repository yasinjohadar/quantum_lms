<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VimeoService
{
    protected const OEMBED_URL = 'https://vimeo.com/api/oembed.json';

    /**
     * جلب مدة الفيديو بالثواني من Vimeo oEmbed.
     *
     * @param string $urlOrId رابط فيميو كامل (مثل https://vimeo.com/123456) أو معرف الفيديو فقط
     * @return int|null المدة بالثواني أو null عند الفشل
     */
    public function getVideoDuration(string $urlOrId): ?int
    {
        $url = $this->normalizeVimeoUrl($urlOrId);
        if (!$url) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->get(self::OEMBED_URL, ['url' => $url]);

            if (!$response->successful()) {
                Log::warning('Vimeo oEmbed request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $duration = $data['duration'] ?? null;

            if ($duration === null || !is_numeric($duration)) {
                return null;
            }

            return (int) $duration;
        } catch (\Exception $e) {
            Log::warning('Vimeo oEmbed exception: ' . $e->getMessage(), [
                'url' => $url,
            ]);
            return null;
        }
    }

    /**
     * تحويل الرابط أو المعرف إلى رابط فيميو كامل للـ oEmbed.
     */
    protected function normalizeVimeoUrl(string $urlOrId): ?string
    {
        $urlOrId = trim($urlOrId);
        if (empty($urlOrId)) {
            return null;
        }

        // إذا كان رقماً فقط (معرف فيميو)
        if (preg_match('/^\d+$/', $urlOrId)) {
            return 'https://vimeo.com/' . $urlOrId;
        }

        // إذا كان يحتوي على vimeo.com
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $urlOrId, $matches)) {
            return 'https://vimeo.com/' . $matches[1];
        }

        return null;
    }
}
