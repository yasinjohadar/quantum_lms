<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الوحدة مطلوب',
            'title.max' => 'عنوان الوحدة يجب ألا يتجاوز 255 حرفاً',
            'order.integer' => 'حقل الترتيب يجب أن يكون رقماً صحيحاً',
        ];
    }
}

