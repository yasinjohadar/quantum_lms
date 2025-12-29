<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class GetJoinTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('student');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Minimal validation - authorization is handled by policy
        ];
    }
}
