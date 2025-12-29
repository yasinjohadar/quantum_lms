<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLiveSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sessionable_type' => ['required', 'string', Rule::in([\App\Models\Subject::class, \App\Models\Lesson::class])],
            'sessionable_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'timezone' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sessionable_type.required' => 'نوع الجلسة مطلوب.',
            'sessionable_id.required' => 'المادة أو الدرس مطلوب.',
            'title.required' => 'عنوان الجلسة مطلوب.',
            'scheduled_at.required' => 'تاريخ ووقت الجلسة مطلوب.',
            'scheduled_at.after' => 'يجب أن يكون تاريخ الجلسة في المستقبل.',
            'duration_minutes.required' => 'مدة الجلسة مطلوبة.',
            'duration_minutes.min' => 'يجب أن تكون مدة الجلسة على الأقل دقيقة واحدة.',
            'duration_minutes.max' => 'يجب ألا تتجاوز مدة الجلسة 480 دقيقة (8 ساعات).',
            'timezone.required' => 'المنطقة الزمنية مطلوبة.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that sessionable_id exists for the given sessionable_type
            $sessionableType = $this->input('sessionable_type');
            $sessionableId = $this->input('sessionable_id');

            if ($sessionableType && $sessionableId) {
                if ($sessionableType === \App\Models\Subject::class) {
                    if (!\App\Models\Subject::find($sessionableId)) {
                        $validator->errors()->add('sessionable_id', 'المادة المحددة غير موجودة.');
                    }
                } elseif ($sessionableType === \App\Models\Lesson::class) {
                    if (!\App\Models\Lesson::find($sessionableId)) {
                        $validator->errors()->add('sessionable_id', 'الدرس المحدد غير موجود.');
                    }
                }
            }
        });
    }
}
