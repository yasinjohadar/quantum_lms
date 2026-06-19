<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Services\Storage\MediaStorageService;
use Illuminate\Http\UploadedFile;

class LessonAttachmentService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];

    private const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'];

    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'odp'];

    public function detectType(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());

        if (in_array($extension, self::IMAGE_EXTENSIONS, true) || str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (in_array($extension, self::AUDIO_EXTENSIONS, true) || str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        if (in_array($extension, self::DOCUMENT_EXTENSIONS, true)) {
            return 'document';
        }

        return 'file';
    }

    public function resolveTitle(
        ?string $inputTitle,
        ?UploadedFile $file = null,
        bool $isLink = false,
        ?string $existingFileName = null
    ): string {
        $title = trim((string) $inputTitle);
        if ($title !== '') {
            return $title;
        }

        $fileName = $file?->getClientOriginalName() ?: $existingFileName;
        if ($fileName) {
            $base = pathinfo($fileName, PATHINFO_FILENAME);
            $base = trim((string) $base);
            if ($base !== '') {
                return $base;
            }
        }

        if ($isLink) {
            return 'رابط مرفق';
        }

        return 'مرفق';
    }

    public function nextOrder(Lesson $lesson): int
    {
        return ((int) $lesson->attachments()->max('order')) + 1;
    }

    public function uploadAttachmentFile(UploadedFile $file, ?string $attachmentType = null): array
    {
        $type = $attachmentType ?? $this->detectType($file);
        $fileName = time().'_'.$file->getClientOriginalName();
        $directory = 'lessons/attachments';

        return match ($type) {
            'image' => MediaStorageService::uploadImage($file, $directory, $fileName),
            'audio' => MediaStorageService::upload($file, $directory, 'audio', $fileName),
            'document' => MediaStorageService::uploadDocument($file, $directory, $fileName),
            default => $this->uploadGenericFile($file, $directory, $fileName),
        };
    }

    private function uploadGenericFile(UploadedFile $file, string $directory, string $fileName): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $archiveExtensions = ['zip', 'rar', '7z'];

        if (in_array($extension, $archiveExtensions, true)) {
            return MediaStorageService::upload($file, $directory, 'archive', $fileName);
        }

        return MediaStorageService::upload($file, $directory, 'other', $fileName);
    }

    public function createFromUploadedFile(Lesson $lesson, UploadedFile $file, array $options = []): LessonAttachment
    {
        $type = $options['type'] ?? $this->detectType($file);
        $uploadResult = $this->uploadAttachmentFile($file, $type);

        return LessonAttachment::create([
            'lesson_id' => $lesson->id,
            'title' => $this->resolveTitle($options['title'] ?? null, $file),
            'type' => $type,
            'description' => $options['description'] ?? null,
            'file_path' => $uploadResult['path'],
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'order' => $options['order'] ?? $this->nextOrder($lesson),
            'is_downloadable' => $options['is_downloadable'] ?? true,
            'is_active' => $options['is_active'] ?? true,
        ]);
    }

    public function createFromLink(Lesson $lesson, array $options = []): LessonAttachment
    {
        return LessonAttachment::create([
            'lesson_id' => $lesson->id,
            'title' => $this->resolveTitle($options['title'] ?? null, null, true),
            'type' => 'link',
            'description' => $options['description'] ?? null,
            'url' => $options['url'],
            'order' => $options['order'] ?? $this->nextOrder($lesson),
            'is_downloadable' => $options['is_downloadable'] ?? true,
            'is_active' => $options['is_active'] ?? true,
        ]);
    }
}
