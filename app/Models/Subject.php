<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'class_id',
        'image',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'order',
        'is_active',
        'display_in_class',
        'price',
        'is_free',
        'pricing_mode',
        'is_free_override',
        'free_join_auto_approve',
        'can_purchase_separately',
        'show_price',
        'default_currency_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_in_class' => 'boolean',
        'order' => 'integer',
        'class_id' => 'integer',
        'price' => 'decimal:2',
        'is_free' => 'boolean',
        'is_free_override' => 'boolean',
        'free_join_auto_approve' => 'boolean',
        'can_purchase_separately' => 'boolean',
        'show_price' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Subject $subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name . '-' . ($subject->class_id ?? ''));
            }
        });

        static::updating(function (Subject $subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name . '-' . ($subject->class_id ?? ''));
            }
        });
    }

    /**
     * العلاقة مع الصف الدراسي.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * الأقسام التابعة لهذه المادة.
     */
    public function sections()
    {
        return $this->hasMany(SubjectSection::class, 'subject_id')->orderBy('order');
    }

    /**
     * الأقسام المرتبطة بهذه المادة عبر section_subjects (أقسام من مواد أخرى تظهر هنا).
     */
    public function linkedSections(): BelongsToMany
    {
        return $this->belongsToMany(SubjectSection::class, 'section_subjects', 'subject_id', 'section_id')->withTimestamps();
    }

    /**
     * جميع الأقسام المعروضة في هذه المادة: الأصلية + المرتبطة.
     */
    public function allSections()
    {
        $primary = $this->relationLoaded('sections')
            ? $this->sections
            : $this->sections()->get();
        $linked = $this->relationLoaded('linkedSections')
            ? $this->linkedSections
            : $this->linkedSections()->get();
        return $primary->merge($linked)->unique('id')->sortBy('order')->values();
    }

    /**
     * نطاق المواد النشطة فقط.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب المواد.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في المواد.
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
     * نطاق الفلترة حسب الصف.
     */
    public function scopeByClass($query, $classId)
    {
        if (! $classId) {
            return $query;
        }

        return $query->where('class_id', $classId);
    }

    /**
     * العلاقة مع الانضمامات
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'subject_id');
    }

    /**
     * العلاقة مع الطلاب (Many-to-Many through enrollments)
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'subject_id', 'user_id')
                    ->withPivot(['enrolled_by', 'enrolled_at', 'status', 'notes'])
                    ->withTimestamps();
    }

    /**
     * إجمالي مدة كل الدروس في المادة بالثواني (كل درس يُحسب مرة واحدة فقط).
     * يجمع الدروس من الأقسام الأصلية والمرتبطة ثم يجمع duration للدروس المميزة بـ id.
     */
    public function getTotalDurationSeconds(): int
    {
        $sectionIds = $this->allSections()->pluck('id')->toArray();
        if (empty($sectionIds)) {
            return 0;
        }
        $unitIds = Unit::whereIn('section_id', $sectionIds)->pluck('id')->toArray();
        if (empty($unitIds)) {
            return 0;
        }
        $lessonIdsFromUnits = Lesson::whereIn('unit_id', $unitIds)->pluck('id');
        $lessonIdsFromLinked = DB::table('lesson_units')->whereIn('unit_id', $unitIds)->pluck('lesson_id');
        $uniqueIds = $lessonIdsFromUnits->merge($lessonIdsFromLinked)->unique()->values()->all();
        if (empty($uniqueIds)) {
            return 0;
        }
        return (int) Lesson::whereIn('id', $uniqueIds)->sum(DB::raw('COALESCE(duration, 0)'));
    }

    /**
     * Accessors - إجمالي الدروس في الكورس
     */
    public function getTotalLessonsAttribute(): int
    {
        return $this->sections()
            ->with(['units.lessons' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->sum(function($section) {
                return $section->units->sum(function($unit) {
                    return $unit->lessons->count();
                });
            });
    }

    /**
     * Accessors - إجمالي الاختبارات في الكورس
     */
    public function getTotalQuizzesAttribute(): int
    {
        return \App\Models\Quiz::where('subject_id', $this->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->count();
    }

    /**
     * Accessors - إجمالي الأسئلة في الكورس
     */
    public function getTotalQuestionsAttribute(): int
    {
        $unitIds = $this->sections()
            ->with('units')
            ->get()
            ->flatMap(function($section) {
                return $section->units->pluck('id');
            })
            ->toArray();

        return \App\Models\Question::whereHas('units', function($query) use ($unitIds) {
                $query->whereIn('units.id', $unitIds);
            })
            ->where('is_active', true)
            ->count();
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
        if (!$currencyId) {
            $currencyId = $this->default_currency_id ?? Currency::getDefault()->id;
        }

        $price = $this->prices()->active()->forCurrency($currencyId)->first();
        return $price ? $price->price : 0;
    }

    /**
     * الحصول على جميع الأسعار النشطة
     */
    public function getActivePrices()
    {
        return $this->prices()->active()->with('currency')->get();
    }

    /**
     * العلاقة مع المعلمين المخصصين لهذه المادة
     */
    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
                    ->withPivot(['assigned_by', 'assigned_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * الحصول على السعر الفعلي مع مراعاة الـ pricing_mode
     */
    public function getEffectivePrice($currencyId = null): float
    {
        return app(\App\Services\Pricing\SubjectPricingResolver::class)->getEffectivePrice($this, $currencyId);
    }

    /**
     * هل المادة مجانية فعلياً؟
     */
    public function isEffectivelyFree(): bool
    {
        return app(\App\Services\Pricing\SubjectPricingResolver::class)->isEffectivelyFree($this);
    }

    /**
     * هل يمكن شراء المادة بشكل منفصل؟
     */
    public function canPurchaseSeparately(): bool
    {
        return app(\App\Services\Pricing\SubjectPricingResolver::class)->canPurchaseSeparately($this);
    }

    /**
     * الحصول على وضع التسعير
     */
    public function getPricingMode(): \App\Enums\PricingMode
    {
        return app(\App\Services\Pricing\SubjectPricingResolver::class)->resolvePricingMode($this);
    }

    /**
     * هل المستخدم لديه وصول لهذه المادة؟
     */
    public function hasAccess(?User $user = null): bool
    {
        if (!$user) {
            return $this->isEffectivelyFree();
        }

        return app(\App\Services\Pricing\AccessResolver::class)->hasSubjectAccess($user, $this);
    }

    /**
     * نوع الوصول للمادة (للعرض في الواجهة)
     */
    public function getAccessType(?User $user = null): string
    {
        if (!$user) {
            return $this->isEffectivelyFree() ? 'free' : 'requires_purchase';
        }

        return app(\App\Services\Pricing\AccessResolver::class)->getSubjectAccessType($user, $this);
    }

    /**
     * Badge الوصول للمادة
     */
    public function getAccessBadge(?User $user = null): array
    {
        return app(\App\Services\Pricing\AccessResolver::class)->getSubjectBadge($this, $user);
    }

    /**
     * الحصول على بيانات الوصول كـ DTO
     */
    public function getAccessData(?User $user = null, $currencyId = null): \App\DataTransferObjects\SubjectAccessData
    {
        return app(\App\Services\Pricing\PricingResolver::class)->resolveSubjectAccessData($this, $user, $currencyId);
    }

    /**
     * هل يُقبل انضمام المادة المجانية تلقائياً؟ القيمة الافتراضية true للتوافق مع النسخ السابقة.
     */
    public function effectiveFreeJoinAutoApprove(): bool
    {
        if ($this->free_join_auto_approve === null) {
            return true;
        }

        return (bool) $this->free_join_auto_approve;
    }

    /**
     * هل يجب حجب الوصول المجاني للمادة حتى موافقة إدارية؟
     * ينطبق على مسار «مجانية دائماً» أو pricing_mode = free.
     */
    public function gatesFreeEnrollmentUntilApproved(): bool
    {
        if (! ($this->is_free_override || $this->pricing_mode === 'free')) {
            return false;
        }

        return ! $this->effectiveFreeJoinAutoApprove();
    }

    /**
     * هل يتطلب انضمام هذه المادة (مسار مجاني) موافقة إدارية؟
     * الأشد يفوز: إعداد الصف أو إعداد المادة.
     */
    public function freeSubjectEnrollmentRequiresApproval(): bool
    {
        $class = $this->relationLoaded('schoolClass')
            ? $this->schoolClass
            : $this->schoolClass()->first();

        if ($class?->gatesFreeEnrollmentUntilApproved()) {
            return true;
        }

        return $this->gatesFreeEnrollmentUntilApproved();
    }
}

