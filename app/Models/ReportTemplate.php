<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'config',
        'is_active',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * أنواع التقارير المتاحة
     */
    const TYPES = [
        'student' => 'تقرير طالب',
        'subject' => 'تقرير مادة',
        'class' => 'تقرير صف',
        'system' => 'تقرير نظام',
    ];

    /**
     * العلاقة مع منشئ القالب
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * نطاق القوالب النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق الفلترة حسب النوع
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * نطاق القوالب الافتراضية
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}

