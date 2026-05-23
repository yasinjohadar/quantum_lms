<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\Unit;
use Illuminate\Validation\Validator;

trait ValidatesQuizCurriculum
{
    /**
     * @return list<string>
     */
    protected function curriculumNullableFields(): array
    {
        return ['subject_id', 'unit_id', 'section_id', 'lesson_id'];
    }

    protected function prepareQuizCurriculumForValidation(): void
    {
        $merge = [];

        foreach ($this->curriculumNullableFields() as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    protected function withQuizCurriculumValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $subjectId = $this->input('subject_id');
            $unitId = $this->input('unit_id');

            if ($unitId && $subjectId) {
                $belongs = Unit::whereKey($unitId)
                    ->whereHas('section', fn ($q) => $q->where('subject_id', $subjectId))
                    ->exists();

                if (! $belongs) {
                    $v->errors()->add('unit_id', 'الوحدة المحددة لا تنتمي للمادة المختارة.');
                }
            }
        });
    }
}
