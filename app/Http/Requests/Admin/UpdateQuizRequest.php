<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesQuizCurriculum;
use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    use ValidatesQuizCurriculum;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'section_id' => ['nullable', 'integer', 'exists:subject_sections,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],

            // إعدادات الوقت
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'show_timer' => ['nullable', 'boolean'],
            'auto_submit' => ['nullable', 'boolean'],

            // إعدادات المحاولات
            'max_attempts' => ['nullable', 'integer', 'min:0', 'max:100'],
            'delay_between_attempts' => ['nullable', 'integer', 'min:0'],

            // إعدادات التقييم
            'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'grading_method' => ['required', 'string', 'in:'.implode(',', array_keys(Quiz::GRADING_METHODS))],

            // إعدادات العرض
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'questions_per_page' => ['nullable', 'integer', 'min:0'],
            'allow_back_navigation' => ['nullable', 'boolean'],

            // إعدادات النتائج
            'show_result_immediately' => ['nullable', 'boolean'],
            'show_correct_answers' => ['nullable', 'boolean'],
            'show_explanation' => ['nullable', 'boolean'],
            'show_points_per_question' => ['nullable', 'boolean'],
            'review_options' => ['required', 'string', 'in:'.implode(',', array_keys(Quiz::REVIEW_OPTIONS))],

            // الجدولة
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date', 'after_or_equal:available_from'],

            // الحالة
            'is_active' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'requires_password' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:4', 'max:50'],

            // إعدادات إضافية
            'require_webcam' => ['nullable', 'boolean'],
            'prevent_copy_paste' => ['nullable', 'boolean'],
            'fullscreen_required' => ['nullable', 'boolean'],

            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareQuizCurriculumForValidation();

        $duration = $this->input('duration_minutes');
        if ($duration === '' || $duration === null || (int) $duration <= 0) {
            $duration = null;
        }

        $this->merge([
            'duration_minutes' => $duration,
            'show_timer' => $this->boolean('show_timer'),
            'auto_submit' => $this->boolean('auto_submit'),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_options' => $this->boolean('shuffle_options'),
            'allow_back_navigation' => $this->boolean('allow_back_navigation'),
            'show_result_immediately' => $this->boolean('show_result_immediately'),
            'show_correct_answers' => $this->boolean('show_correct_answers'),
            'show_explanation' => $this->boolean('show_explanation'),
            'show_points_per_question' => $this->boolean('show_points_per_question'),
            'is_active' => $this->boolean('is_active'),
            'is_published' => $this->boolean('is_published'),
            'requires_password' => $this->boolean('requires_password'),
            'require_webcam' => $this->boolean('require_webcam'),
            'prevent_copy_paste' => $this->boolean('prevent_copy_paste'),
            'fullscreen_required' => $this->boolean('fullscreen_required'),
        ]);
    }

    public function withValidator($validator): void
    {
        $this->withQuizCurriculumValidator($validator);
    }

    public function messages(): array
    {
        return [
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
