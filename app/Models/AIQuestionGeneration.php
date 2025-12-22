<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQuestionGeneration extends Model
{
    use HasFactory;

    protected $table = 'ai_question_generations';

    protected $fillable = [
        'user_id',
        'subject_id',
        'lesson_id',
        'source_type',
        'source_content',
        'prompt',
        'question_type',
        'number_of_questions',
        'difficulty_level',
        'ai_model_id',
        'status',
        'generated_questions',
        'error_message',
        'tokens_used',
        'cost',
    ];

    protected $casts = [
        'number_of_questions' => 'integer',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'generated_questions' => 'array',
    ];

    /**
     * أنواع المصادر
     */
    public const SOURCE_TYPES = [
        'lesson_content' => 'محتوى الدرس',
        'manual_text' => 'نص يدوي',
        'topic' => 'موضوع',
    ];

    /**
     * أنواع الأسئلة
     */
    public const QUESTION_TYPES = [
        'single_choice' => 'اختيار واحد',
        'multiple_choice' => 'اختيار متعدد',
        'true_false' => 'صح/خطأ',
        'short_answer' => 'إجابة قصيرة',
        'essay' => 'مقالي',
        'matching' => 'مطابقة',
        'ordering' => 'ترتيب',
        'fill_blanks' => 'ملء الفراغات',
        'numerical' => 'رقمي',
        'drag_drop' => 'سحب وإفلات',
        'mixed' => 'مختلط',
    ];

    /**
     * مستويات الصعوبة
     */
    public const DIFFICULTIES = [
        'easy' => 'سهل',
        'medium' => 'متوسط',
        'hard' => 'صعب',
        'mixed' => 'مختلط',
    ];

    /**
     * الحالات
     */
    public const STATUSES = [
        'pending' => 'معلق',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * العلاقة مع الدرس
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * العلاقة مع الموديل
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * معالجة التوليد
     */
    public function process(): void
    {
        // سيتم تنفيذ هذا في Service
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function saveQuestions(): \Illuminate\Support\Collection
    {
        // سيتم تنفيذ هذا في Service
        return collect();
    }

    /**
     * الحصول على الحالة
     */
    public function getStatus(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
