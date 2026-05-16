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
        'ai' => 'الذكاء الاصطناعي',
        'email' => 'البريد الإلكتروني',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'phone_verification' => 'Phone Verification',
        'social' => 'روابط التواصل الاجتماعي',
        'storage' => 'التخزين والملفات',
        'payments' => 'المدفوعات',
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

    /** هل يُطلب رفع وصل التحويل البنكي (IBAN) من الطالب؟ */
    public static function ibanReceiptRequired(): bool
    {
        return (bool) self::get('payments_iban_receipt_required', true);
    }

    /** تعليمات مخصصة تظهر للطالب عند اختيار التحويل البنكي */
    public static function ibanStudentInstructions(): string
    {
        $v = self::get('payments_iban_student_instructions', '');

        return is_string($v) ? $v : '';
    }

    public static function ibanDisplayName(): string
    {
        $v = self::get('payments_iban_display_name', 'تحويل بنكي (IBAN)');

        return is_string($v) && trim($v) !== '' ? trim($v) : 'تحويل بنكي (IBAN)';
    }

    /**
     * @return array{iban: string, bank_name: string, account_holder: string}
     */
    public static function ibanAccountDetails(): array
    {
        $iban = self::get('payments_iban_account_iban', '');
        $bankName = self::get('payments_iban_account_bank_name', '');
        $holder = self::get('payments_iban_account_holder', '');

        return [
            'iban' => is_string($iban) ? trim($iban) : '',
            'bank_name' => is_string($bankName) ? trim($bankName) : '',
            'account_holder' => is_string($holder) ? trim($holder) : '',
        ];
    }

    public static function ibanPendingMessage(): string
    {
        $default = 'الطلب قيد المعالجة. يجب التواصل مع المشرفة لتأكيد الاشتراك.';
        $v = self::get('payments_iban_pending_message', $default);

        return is_string($v) && trim($v) !== '' ? trim($v) : $default;
    }
}

