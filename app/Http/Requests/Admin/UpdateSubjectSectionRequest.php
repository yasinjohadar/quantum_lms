<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            // is_active يتم معالجته في الكنترولر، لا حاجة للتحقق هنا
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان القسم مطلوب',
            'title.max' => 'عنوان القسم يجب ألا يتجاوز 255 حرفاً',
            'order.integer' => 'حقل الترتيب يجب أن يكون رقماً صحيحاً',
        ];
    }
}


