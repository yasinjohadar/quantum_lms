<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'section_id' => ['nullable', 'integer', 'exists:subject_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:upload,youtube,vimeo,external'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,ogg,mov', 'max:512000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'book_page_from' => ['nullable', 'integer', 'min:1'],
            'book_page_to' => ['nullable', 'integer', 'min:1', 'gte:book_page_from'],
            'order' => ['nullable', 'integer', 'min:0'],
            'linked_unit_ids' => ['nullable', 'array'],
            'linked_unit_ids.*' => ['integer', 'exists:units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الدرس مطلوب',
            'title.max' => 'عنوان الدرس يجب ألا يتجاوز 255 حرفاً',
            'video_type.required' => 'نوع الفيديو مطلوب',
            'video_type.in' => 'نوع الفيديو غير صالح',
            'video_file.mimes' => 'صيغة ملف الفيديو يجب أن تكون mp4, webm, ogg, أو mov',
            'video_file.max' => 'حجم ملف الفيديو يجب ألا يتجاوز 500 ميجابايت',
            'thumbnail.image' => 'الصورة المصغرة يجب أن تكون صورة',
            'thumbnail.mimes' => 'صيغة الصورة المصغرة يجب أن تكون jpg, jpeg, png, أو webp',
            'thumbnail.max' => 'حجم الصورة المصغرة يجب ألا يتجاوز 2 ميجابايت',
            'book_page_from.integer' => 'من الصفحة يجب أن يكون رقماً صحيحاً',
            'book_page_from.min' => 'من الصفحة يجب أن يكون أكبر من أو يساوي 1',
            'book_page_to.integer' => 'إلى الصفحة يجب أن يكون رقماً صحيحاً',
            'book_page_to.min' => 'إلى الصفحة يجب أن يكون أكبر من أو يساوي 1',
            'book_page_to.gte' => 'إلى الصفحة يجب أن تكون أكبر من أو تساوي من الصفحة',
        ];
    }
}

