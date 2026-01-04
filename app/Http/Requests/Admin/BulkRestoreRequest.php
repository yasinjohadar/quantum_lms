<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkRestoreRequest extends FormRequest
{
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archived_user_ids.required' => 'يرجى اختيار مستخدمين للاستعادة',
            'archived_user_ids.array' => 'صيغة البيانات غير صحيحة',
            'archived_user_ids.min' => 'يرجى اختيار مستخدم واحد على الأقل',
            'archived_user_ids.*.exists' => 'واحد أو أكثر من المستخدمين المحددين غير موجود',
        ];
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add permission check later
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        if ($this->has('archived_user_ids') && is_string($this->input('archived_user_ids'))) {
            $this->merge([
                'archived_user_ids' => json_decode($this->input('archived_user_ids'), true),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'archived_user_ids' => ['required', 'array', 'min:1'],
            'archived_user_ids.*' => ['required', 'integer', 'exists:archived_users,id'],
        ];
    }
}
