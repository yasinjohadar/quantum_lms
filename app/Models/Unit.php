<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'section_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'section_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع القسم.
     */
    public function section()
    {
        return $this->belongsTo(SubjectSection::class, 'section_id');
    }

    /**
     * العلاقة مع الدروس.
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * العلاقة مع جميع الاختبارات المرتبطة بهذه الوحدة (قد تكون عامة أو تابعة لدروس).
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('order');
    }

    /**
     * اختبارات عامة للوحدة (لا تتبع درساً محدداً).
     */
    public function unitQuizzes()
    {
        return $this->hasMany(Quiz::class)
            ->whereNull('lesson_id')
            ->orderBy('order');
    }

    /**
     * اختبارات تابعة لدروس هذه الوحدة (لكل درس اختبار/اختبارات خاصة).
     * مفيدة إذا احتجنا إحصائيات على مستوى الوحدة لكل اختبارات الدروس.
     */
    public function lessonQuizzes()
    {
        return $this->hasMany(Quiz::class)
            ->whereNotNull('lesson_id')
            ->orderBy('order');
    }

    /**
     * العلاقة مع الأسئلة
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_units')->withTimestamps();
    }

    /**
     * Scope للوحدات النشطة.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للترتيب.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
