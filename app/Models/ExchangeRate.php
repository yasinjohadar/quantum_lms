<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع العملة المصدر
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * العلاقة مع العملة الهدف
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الحصول على سعر الصرف بين عملتين
     */
    public static function getRate($fromCurrencyId, $toCurrencyId)
    {
        if ($fromCurrencyId == $toCurrencyId) {
            return 1;
        }

        $rate = static::active()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->first();

        if ($rate) {
            return $rate->rate;
        }

        // محاولة العكس
        $reverseRate = static::active()
            ->where('from_currency_id', $toCurrencyId)
            ->where('to_currency_id', $fromCurrencyId)
            ->first();

        if ($reverseRate) {
            return 1 / $reverseRate->rate;
        }

        return 1; // افتراضي
    }

    /**
     * تحويل مبلغ من عملة إلى أخرى
     */
    public static function convert($amount, $fromCurrencyId, $toCurrencyId)
    {
        $rate = static::getRate($fromCurrencyId, $toCurrencyId);
        return $amount * $rate;
    }
}
