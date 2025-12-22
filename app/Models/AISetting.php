<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AISetting extends Model
{
    use HasFactory;

    protected $table = 'ai_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * أنواع القيم
     */
    public const TYPES = [
        'string' => 'نص',
        'integer' => 'رقم',
        'boolean' => 'نعم/لا',
        'json' => 'JSON',
    ];

    /**
     * الحصول على القيمة المحولة
     */
    public function getValueAttribute($value)
    {
        return match($this->type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * حفظ القيمة
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match($this->type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * الحصول على إعداد
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * تعيين إعداد
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null, bool $isPublic = false): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }
}
