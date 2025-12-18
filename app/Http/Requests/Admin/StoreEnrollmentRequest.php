<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
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
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|exists:subjects,id',
            'status' => 'nullable|in:active,suspended,completed',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'يجب اختيار طالب واحد على الأقل',
            'user_ids.array' => 'صيغة الطلاب غير صحيحة',
            'user_ids.min' => 'يجب اختيار طالب واحد على الأقل',
            'user_ids.*.required' => 'يجب اختيار طالب صحيح',
            'user_ids.*.exists' => 'الطالب المحدد غير موجود',
            'subject_ids.required' => 'يجب اختيار مادة واحدة على الأقل',
            'subject_ids.array' => 'صيغة المواد غير صحيحة',
            'subject_ids.min' => 'يجب اختيار مادة واحدة على الأقل',
            'subject_ids.*.required' => 'يجب اختيار مادة صحيحة',
            'subject_ids.*.exists' => 'المادة المحددة غير موجودة',
            'status.in' => 'حالة الانضمام غير صحيحة',
            'notes.max' => 'الملاحظات يجب أن تكون أقل من 1000 حرف',
        ];
    }
}
