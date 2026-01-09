<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Question;

class StoreQuestionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $type = $this->input('type');
        
        $rules = [
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(Question::TYPES))],
            'title' => ['required', 'string', 'max:10000'], // زيادة الحد للسماح بـ HTML
            'content' => ['nullable', 'string', 'max:50000'], // زيادة الحد للسماح بـ HTML
            'explanation' => ['nullable', 'string', 'max:10000'], // زيادة الحد للسماح بـ HTML
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'default_points' => ['required', 'numeric', 'min:0', 'max:1000'],
            'case_sensitive' => ['nullable', 'boolean'],
            'tolerance' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_active' => ['nullable'],
            
            // الوحدات المرتبطة
            'units' => ['nullable', 'array'],
            'units.*' => ['exists:units,id'],
            
            // خيارات السؤال
            'options' => ['required_if:type,single_choice,multiple_choice,true_false,matching,ordering', 'array', 'min:2'],
            'options.*.content' => ['required_with:options', 'string'],
            'options.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'options.*.is_correct' => ['nullable'],
            'options.*.match_target' => ['nullable', 'string'],
            'options.*.correct_order' => ['nullable', 'integer', 'min:1'],
            'options.*.feedback' => ['nullable', 'string'],
            
            // للأسئلة الرقمية
            'correct_answer' => ['required_if:type,numerical', 'nullable', 'numeric'],
        ];
        
        // لملء الفراغات - فقط إذا كان نوع السؤال fill_blanks
        if ($type === 'fill_blanks') {
            $rules['blank_answers'] = ['required', 'array', 'min:1'];
            $rules['blank_answers.*'] = ['required', 'string'];
        } else {
            // إذا لم يكن fill_blanks، تجاهل blank_answers تماماً
            $rules['blank_answers'] = ['nullable'];
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع السؤال مطلوب',
            'type.in' => 'نوع السؤال غير صالح',
            'title.required' => 'نص السؤال مطلوب',
            'title.max' => 'نص السؤال كبير جداً',
            'difficulty.required' => 'مستوى الصعوبة مطلوب',
            'difficulty.in' => 'مستوى الصعوبة غير صالح',
            'default_points.required' => 'درجة السؤال مطلوبة',
            'default_points.numeric' => 'درجة السؤال يجب أن تكون رقماً',
            'default_points.min' => 'درجة السؤال يجب أن تكون أكبر من أو تساوي 0',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
            'options.required_if' => 'خيارات السؤال مطلوبة لهذا النوع',
            'options.min' => 'يجب إضافة خيارين على الأقل',
            'options.*.content.required_with' => 'نص الخيار مطلوب',
            'correct_answer.required_if' => 'الإجابة الصحيحة مطلوبة للسؤال الرقمي',
            'blank_answers.required_if' => 'إجابات الفراغات مطلوبة',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'نوع السؤال',
            'title' => 'نص السؤال',
            'content' => 'محتوى السؤال',
            'explanation' => 'شرح الإجابة',
            'image' => 'صورة السؤال',
            'difficulty' => 'مستوى الصعوبة',
            'default_points' => 'درجة السؤال',
            'category' => 'التصنيف',
            'options' => 'خيارات السؤال',
        ];
    }
}

