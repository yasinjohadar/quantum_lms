<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'stage_id',
        'image',
        'description',
        'whatsapp_group_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'order',
        'is_active',
        'price',
        'is_free',
        'default_currency_id',
        'allow_subjects_purchase',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'stage_id' => 'integer',
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'allow_subjects_purchase' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (SchoolClass $class) {
            if (empty($class->slug)) {
                $class->slug = Str::slug($class->name . '-' . ($class->stage_id ?? ''));
            }
        });

        static::updating(function (SchoolClass $class) {
            if (empty($class->slug)) {
                $class->slug = Str::slug($class->name . '-' . ($class->stage_id ?? ''));
            }
        });
    }

    /**
     * العلاقة مع المرحلة الدراسية.
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    /**
     * العلاقة مع المواد الدراسية.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    /**
     * العلاقة مع خصائص الصف (حتى 10).
     */
    public function features()
    {
        return $this->hasMany(ClassFeature::class, 'class_id')->orderBy('order');
    }

    /**
     * العلاقة مع آراء المنصة المرتبطة بهذا الصف
     */
    public function platformReviews()
    {
        return $this->hasMany(PlatformReview::class, 'class_id');
    }

    /**
     * نطاق الصفوف النشطة فقط.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب الصفوف.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في الصفوف.
     */
    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('meta_title', 'like', '%' . $search . '%')
              ->orWhere('meta_description', 'like', '%' . $search . '%');
        });
    }

    /**
     * نطاق الفلترة حسب المرحلة.
     */
    public function scopeByStage($query, $stageId)
    {
        if (! $stageId) {
            return $query;
        }

        return $query->where('stage_id', $stageId);
    }

    /**
     * العلاقة مع المشتريات
     */
    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    /**
     * العلاقة مع الأسعار
     */
    public function prices()
    {
        return $this->morphMany(Price::class, 'pricable');
    }

    /**
     * العلاقة مع العملة الافتراضية
     */
    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    /**
     * الحصول على السعر بعملة معينة
     */
    public function getPrice($currencyId = null)
    {
        // إذا كان الصف مجاني، إرجاع 0
        if ($this->is_free) {
            return 0;
        }

        if (!$currencyId) {
            $currencyId = $this->default_currency_id ?? Currency::getDefault()->id;
        }

        // البحث أولاً في جدول prices
        $price = $this->prices()->active()->forCurrency($currencyId)->first();
        
        // إذا وجد سعر في جدول prices، إرجاعه
        if ($price) {
            return $price->price;
        }

        // إذا لم يوجد سعر في جدول prices، استخدام الحقل price المباشر كـ fallback
        if ($this->price && $this->price > 0) {
            return $this->price;
        }

        // إذا لم يوجد أي سعر، إرجاع 0
        return 0;
    }

    /**
     * الحصول على جميع الأسعار النشطة
     */
    public function getActivePrices()
    {
        return $this->prices()->active()->with('currency')->get();
    }

    /**
     * العلاقة مع المعلمين المخصصين لهذا الصف
     */
    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'class_id', 'teacher_id')
                    ->withPivot(['assigned_by', 'assigned_at', 'notes'])
                    ->withTimestamps();
    }
}

