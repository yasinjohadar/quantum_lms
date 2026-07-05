<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user-edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('user_ids') && is_string($this->input('user_ids'))) {
            $decoded = json_decode($this->input('user_ids'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['user_ids' => $decoded]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'يجب تحديد مستخدم واحد على الأقل.',
            'user_ids.min' => 'يجب تحديد مستخدم واحد على الأقل.',
            'roles.required' => 'يجب اختيار دور واحد على الأقل.',
            'roles.min' => 'يجب اختيار دور واحد على الأقل.',
            'roles.*.exists' => 'أحد الأدوار المختارة غير موجود.',
        ];
    }
}
