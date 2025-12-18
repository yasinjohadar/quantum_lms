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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:upload,youtube,vimeo,external'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,ogg,mov', 'max:512000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'order' => ['nullable', 'integer', 'min:0'],
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
        ];
    }
}

