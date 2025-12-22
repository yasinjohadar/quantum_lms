<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionFile extends Model
{
    use HasFactory;

    protected $table = 'assignment_submission_files';

    protected $fillable = [
        'submission_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'order',
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * العلاقة مع الإرسال
     */
    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }

    /**
     * الحصول على رابط الملف
     */
    public function getFileUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * الحصول على حجم الملف بصيغة مقروءة
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * التحقق من وجود الملف
     */
    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * حذف الملف من التخزين
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::disk('public')->delete($this->file_path);
        }
        return false;
    }
}
