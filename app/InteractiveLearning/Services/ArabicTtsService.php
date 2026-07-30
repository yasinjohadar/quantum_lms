<?php

namespace App\InteractiveLearning\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ArabicTtsService
{
    /**
     * Return absolute path to a cached MP3 for the given text, generating if needed.
     */
    public function pathFor(string $text, string $lang = 'ar'): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        $text = Str::limit($text, 200, '');
        if ($text === '') {
            throw new RuntimeException('نص النطق فارغ.');
        }

        $lang = in_array($lang, ['ar', 'en'], true) ? $lang : 'ar';
        $dir = storage_path('app/ile-tts');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $hash = hash('sha256', $lang.'|'.$text);
        $path = $dir.DIRECTORY_SEPARATOR.$hash.'.mp3';
        if (is_file($path) && filesize($path) > 500) {
            return $path;
        }

        if ($this->tryEdgeTts($text, $lang, $path)) {
            return $path;
        }

        if ($this->tryGoogleTts($text, $lang, $path)) {
            return $path;
        }

        throw new RuntimeException('تعذر توليد الصوت العربي. تأكد من توفر edge-tts أو الاتصال بالإنترنت.');
    }

    protected function tryEdgeTts(string $text, string $lang, string $path): bool
    {
        $voice = $lang === 'en'
            ? (config('services.ile_tts.en_voice') ?: 'en-US-JennyNeural')
            : (config('services.ile_tts.ar_voice') ?: 'ar-EG-SalmaNeural');

        $bin = config('services.ile_tts.edge_tts_bin') ?: $this->findEdgeTts();
        if (! $bin) {
            return false;
        }

        $cmd = [
            $bin,
            '--voice', $voice,
            '--rate', '+10%',
            '--pitch', '+6Hz',
            '--text', $text,
            '--write-media', $path,
        ];

        // Prefer python -m edge_tts when bin is python module style
        try {
            $descriptor = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = proc_open($cmd, $descriptor, $pipes, null, null, ['bypass_shell' => true]);
            if (! is_resource($proc)) {
                return false;
            }
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);
            if ($code === 0 && is_file($path) && filesize($path) > 500) {
                return true;
            }
            Log::info('ILE edge-tts failed', ['code' => $code, 'stderr' => Str::limit((string) $stderr, 300)]);
        } catch (\Throwable $e) {
            Log::info('ILE edge-tts exception', ['message' => $e->getMessage()]);
        }

        @unlink($path);

        return false;
    }

    protected function findEdgeTts(): ?string
    {
        $candidates = [
            env('ILE_EDGE_TTS'),
            'edge-tts',
            PHP_OS_FAMILY === 'Windows'
                ? (getenv('LOCALAPPDATA') ?: '').'\\hermes\\hermes-agent\\venv\\Scripts\\edge-tts.exe'
                : null,
        ];

        foreach ($candidates as $bin) {
            if (! $bin) {
                continue;
            }
            if (is_file($bin)) {
                return $bin;
            }
            // On PATH
            if (! str_contains($bin, DIRECTORY_SEPARATOR) && ! str_contains($bin, '/')) {
                return $bin;
            }
        }

        return null;
    }

    protected function tryGoogleTts(string $text, string $lang, string $path): bool
    {
        $tl = $lang === 'en' ? 'en' : 'ar';
        $url = 'https://translate.google.com/translate_tts?'.http_build_query([
            'ie' => 'UTF-8',
            'client' => 'tw-ob',
            'tl' => $tl,
            'q' => $text,
        ]);

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer' => 'https://translate.google.com/',
                ])
                ->get($url);

            if (! $response->successful()) {
                return false;
            }

            $body = $response->body();
            if (strlen($body) < 500) {
                return false;
            }

            file_put_contents($path, $body);

            return is_file($path) && filesize($path) > 500;
        } catch (\Throwable $e) {
            Log::info('ILE google tts failed', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
