<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportQuestionsWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('question-export') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scope' => ['required', Rule::in(['filtered', 'selected'])],
            'order' => ['required', Rule::in(['list_order', 'by_type'])],
            'ids' => ['required_if:scope,selected', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:questions,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(array_keys(Question::TYPES))],
            'difficulty' => ['nullable', Rule::in(array_keys(Question::DIFFICULTIES))],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'unit_id' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest', 'title'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scope.required' => 'يرجى تحديد نطاق التصدير.',
            'order.required' => 'يرجى تحديد ترتيب الأسئلة في الملف.',
            'ids.required_if' => 'يرجى تحديد سؤال واحد على الأقل للتصدير.',
            'ids.min' => 'يرجى تحديد سؤال واحد على الأقل للتصدير.',
        ];
    }
}
