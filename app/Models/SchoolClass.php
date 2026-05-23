<?php

namespace App\Models;

use App\Models\Concerns\HasFrontendPriceLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory, HasFrontendPriceLabel, SoftDeletes;

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
        'show_price',
        'use_custom_price_label',
        'custom_price_label',
        'default_currency_id',
        'allow_subjects_purchase',
        'free_join_auto_approve',
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
        'show_price' => 'boolean',
        'use_custom_price_label' => 'boolean',
        'allow_subjects_purchase' => 'boolean',
        'free_join_auto_approve' => 'boolean',
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

    /**
     * العلاقة مع الطلاب المتميزين (عروض الصفحة الرئيسية)
     */
    public function distinguishedStudents()
    {
        return $this->hasMany(DistinguishedStudent::class, 'class_id');
    }

    /**
     * هل المستخدم لديه وصول لهذا الصف؟
     */
    public function hasAccess(?User $user = null): bool
    {
        if ($this->is_free) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return app(\App\Services\Pricing\AccessResolver::class)->hasClassAccess($user, $this);
    }

    /**
     * الحصول على بيانات الوصول كـ DTO
     */
    public function getAccessData(?User $user = null, $currencyId = null): \App\DataTransferObjects\ClassAccessData
    {
        return app(\App\Services\Pricing\PricingResolver::class)->resolveClassAccessData($this, $user, $currencyId);
    }

    /**
     * الحصول على المواد مع بيانات التسعير والوصول
     */
    public function getSubjectsWithAccess(?User $user = null, $currencyId = null)
    {
        $resolver = app(\App\Services\Pricing\PricingResolver::class);

        return $this->subjects()
            ->active()
            ->ordered()
            ->get()
            ->map(function ($subject) use ($user, $currencyId, $resolver) {
                return $resolver->resolveSubjectAccessData($subject, $user, $currencyId);
            });
    }

    /**
     * هل يُتوقّع طلب دفع عند «الانضمام للصف كاملاً»؟ (متطابق مع StudentEnrollmentController::requestClassEnrollment)
     */
    public function classJoinRequiresPayment(?int $currencyId = null): bool
    {
        if ($this->is_free) {
            return false;
        }

        return (float) $this->getPrice($currencyId) > 0;
    }

    /**
     * هل يُقبل الانضمام للمسار المجاني (بدون طلب دفع) تلقائياً؟
     * القيمة الافتراضية true للتوافق مع النسخ السابقة وعند null.
     */
    public function effectiveFreeJoinAutoApprove(): bool
    {
        if ($this->free_join_auto_approve === null) {
            return true;
        }

        return (bool) $this->free_join_auto_approve;
    }

    /**
     * هل يجب حجب الوصول المجاني التلقائي حتى موافقة إدارية؟
     * ينطبق فقط عندما لا يُطلب دفع لانضمام الصف.
     */
    public function gatesFreeEnrollmentUntilApproved(): bool
    {
        if ($this->classJoinRequiresPayment()) {
            return false;
        }

        return ! $this->effectiveFreeJoinAutoApprove();
    }
}

