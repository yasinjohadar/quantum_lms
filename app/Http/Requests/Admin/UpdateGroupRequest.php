<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupId = $this->route('group')->id ?? $this->route('group');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->ignore($groupId),
            ],
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المجموعة مطلوب',
            'name.unique' => 'اسم المجموعة موجود مسبقاً',
            'name.max' => 'اسم المجموعة يجب أن يكون أقل من 255 حرف',
            'description.max' => 'الوصف يجب أن يكون أقل من 1000 حرف',
            'color.regex' => 'اللون يجب أن يكون بصيغة hex code (#RRGGBB)',
        ];
    }
}
