<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIMessageAttachment extends Model
{
    use HasFactory;

    protected $table = 'ai_message_attachments';

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'content',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * أنواع الملفات المدعومة
     */
    public const FILE_TYPES = [
        'image' => 'صورة',
        'document' => 'مستند',
        'text' => 'نص',
    ];

    /**
     * الملفات المدعومة
     */
    public const ALLOWED_EXTENSIONS = [
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'txt'],
        'text' => ['txt', 'md'],
    ];

    /**
     * العلاقة مع الرسالة
     */
    public function message()
    {
        return $this->belongsTo(AIMessage::class, 'message_id');
    }

    /**
     * الحصول على URL الملف
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * الحصول على حجم الملف المنسق
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * التحقق من نوع الملف
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * التحقق من أن الملف قابل للقراءة من قبل AI
     */
    public function isAIFriendly(): bool
    {
        return in_array($this->file_type, ['image', 'text']);
    }
}
