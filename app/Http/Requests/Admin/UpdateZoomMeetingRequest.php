<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZoomMeetingRequest extends FormRequest
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
            'scheduled_at' => ['sometimes', 'date', 'after:now'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1', 'max:480'],
            'timezone' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Scheduled time must be in the future.',
            'duration_minutes.min' => 'Duration must be at least 1 minute.',
            'duration_minutes.max' => 'Duration cannot exceed 480 minutes (8 hours).',
        ];
    }
}
