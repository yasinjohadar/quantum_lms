<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type', // string, integer, boolean, json, text
        'group', // general, reports, analytics, dashboard, etc.
        'description',
    ];

    protected $casts = [
        'value' => 'string', // سيتم التحويل حسب النوع
    ];

    /**
     * أنواع الإعدادات
     */
    public const TYPES = [
        'string' => 'نص',
        'integer' => 'رقم',
        'boolean' => 'نعم/لا',
        'json' => 'JSON',
        'text' => 'نص طويل',
    ];

    /**
     * مجموعات الإعدادات
     */
    public const GROUPS = [
        'general' => 'عام',
        'reports' => 'التقارير',
        'analytics' => 'التحليلات',
        'dashboard' => 'لوحة التحكم',
        'notifications' => 'الإشعارات',
        'export' => 'التصدير',
        'gamification' => 'التحفيز',
        'zoom' => 'Zoom',
        'ai' => 'الذكاء الاصطناعي',
        'email' => 'البريد الإلكتروني',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'phone_verification' => 'Phone Verification',
    ];

    /**
     * Scopes
     */
    public function scopeOfGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Helper Methods
     */
    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function setValueAttribute($value)
    {
        if ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getGroupNameAttribute(): string
    {
        return self::GROUPS[$this->group] ?? $this->group;
    }

    /**
     * Static helper to get setting value
     */
    public static function get($key, $default = null)
    {
        $setting = self::byKey($key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Static helper to set setting value
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key, 'group' => $group],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}

