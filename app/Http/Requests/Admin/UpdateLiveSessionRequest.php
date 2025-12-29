<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLiveSessionRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['sometimes', 'required', 'date', 'after:now'],
            'duration_minutes' => ['sometimes', 'required', 'integer', 'min:1', 'max:480'],
            'timezone' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:scheduled,live,completed,cancelled'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الجلسة مطلوب.',
            'scheduled_at.after' => 'يجب أن يكون تاريخ الجلسة في المستقبل.',
            'duration_minutes.min' => 'يجب أن تكون مدة الجلسة على الأقل دقيقة واحدة.',
            'duration_minutes.max' => 'يجب ألا تتجاوز مدة الجلسة 480 دقيقة (8 ساعات).',
        ];
    }
}
