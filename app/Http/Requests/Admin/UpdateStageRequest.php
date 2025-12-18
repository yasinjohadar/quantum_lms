<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStageRequest extends FormRequest
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
        $stageId = $this->route('stage') ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('stages', 'slug')->ignore($stageId),
            ],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
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
            'name.required' => 'حقل اسم المرحلة مطلوب.',
            'name.max' => 'يجب ألا يزيد طول اسم المرحلة عن 255 حرفاً.',
            'slug.unique' => 'هذا الرابط الدائم مستخدم من قبل، يرجى اختيار رابط آخر.',
            'image.image' => 'يجب أن يكون الملف صورة صالحة.',
            'image.mimes' => 'يُسمح فقط بالصور من نوع: jpeg, png, jpg, gif, webp.',
            'image.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت.',
            'og_image.image' => 'يجب أن تكون صورة Open Graph صالحة.',
            'og_image.mimes' => 'يُسمح فقط بالصور من نوع: jpeg, png, jpg, gif, webp.',
            'og_image.max' => 'يجب ألا يتجاوز حجم صورة Open Graph 2 ميجابايت.',
            'order.integer' => 'حقل الترتيب يجب أن يكون رقماً صحيحاً.',
            'order.min' => 'حقل الترتيب يجب أن يكون صفراً أو أكبر.',
        ];
    }
}


