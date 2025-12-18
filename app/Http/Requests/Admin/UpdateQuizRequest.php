<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Quiz;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            
            // إعدادات الوقت
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'show_timer' => ['nullable'],
            'auto_submit' => ['nullable'],
            
            // إعدادات المحاولات
            'max_attempts' => ['nullable', 'integer', 'min:0', 'max:100'],
            'delay_between_attempts' => ['nullable', 'integer', 'min:0'],
            
            // إعدادات التقييم
            'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'grading_method' => ['required', 'string', 'in:' . implode(',', array_keys(Quiz::GRADING_METHODS))],
            
            // إعدادات العرض
            'shuffle_questions' => ['nullable'],
            'shuffle_options' => ['nullable'],
            'questions_per_page' => ['nullable', 'integer', 'min:0'],
            'allow_back_navigation' => ['nullable'],
            
            // إعدادات النتائج
            'show_result_immediately' => ['nullable'],
            'show_correct_answers' => ['nullable'],
            'show_explanation' => ['nullable'],
            'show_points_per_question' => ['nullable'],
            'review_options' => ['required', 'string', 'in:' . implode(',', array_keys(Quiz::REVIEW_OPTIONS))],
            
            // الجدولة
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date', 'after_or_equal:available_from'],
            
            // الحالة
            'is_active' => ['nullable'],
            'is_published' => ['nullable'],
            'requires_password' => ['nullable'],
            'password' => ['nullable', 'string', 'min:4', 'max:50'],
            
            // إعدادات إضافية
            'require_webcam' => ['nullable'],
            'prevent_copy_paste' => ['nullable'],
            'fullscreen_required' => ['nullable'],
            
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'المادة مطلوبة',
            'subject_id.exists' => 'المادة غير موجودة',
            'title.required' => 'عنوان الاختبار مطلوب',
            'title.max' => 'عنوان الاختبار يجب ألا يتجاوز 255 حرف',
            'duration_minutes.min' => 'مدة الاختبار يجب أن تكون دقيقة واحدة على الأقل',
            'duration_minutes.max' => 'مدة الاختبار يجب ألا تتجاوز 600 دقيقة',
            'pass_percentage.required' => 'نسبة النجاح مطلوبة',
            'pass_percentage.min' => 'نسبة النجاح يجب أن تكون 0 أو أكثر',
            'pass_percentage.max' => 'نسبة النجاح يجب ألا تتجاوز 100',
            'grading_method.required' => 'طريقة التقييم مطلوبة',
            'available_to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
        ];
    }
}

