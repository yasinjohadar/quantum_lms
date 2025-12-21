<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'label',
        'type', // text, number, date, select, textarea
        'options', // JSON - للأنواع select
        'default_value',
        'is_required',
        'is_active',
        'order',
        'model_type', // User, Subject, Lesson, etc.
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * أنواع الحقول
     */
    public const TYPES = [
        'text' => 'نص',
        'number' => 'رقم',
        'date' => 'تاريخ',
        'select' => 'قائمة منسدلة',
        'textarea' => 'نص طويل',
        'boolean' => 'نعم/لا',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

