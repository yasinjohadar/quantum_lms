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
            'parent_id' => ['nullable', 'integer', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'sync_mirrored_sections' => ['sometimes', 'boolean'],
            'linked_section_ids' => ['nullable', 'array'],
            'linked_section_ids.*' => ['integer', 'exists:subject_sections,id'],
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

