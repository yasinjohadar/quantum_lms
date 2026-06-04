<?php

namespace App\Http\Controllers\Api\Extension;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Services\ExtensionImport\ExtensionImportException;
use App\Services\ExtensionImport\ExtensionQuestionPersister;
use App\Services\ExtensionImport\NotebookLmQuestionMapper;
use App\Support\QuestionImportCurriculumValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtensionQuestionImportController extends Controller
{
    use BuildsQuestionBankIndex;

    public function import(
        Request $request,
        NotebookLmQuestionMapper $mapper,
        ExtensionQuestionPersister $persister
    ) {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'questions' => ['required', 'array', 'min:1', 'max:200'],
            'questions.*.title' => ['required_without:questions.*.question', 'string'],
            'questions.*.type' => ['nullable', 'string', Rule::in(array_keys(Question::TYPES))],
        ]);

        $subject = Subject::findOrFail((int) $validated['subject_id']);
        $this->authorizeManagedSubjectAccess($request->user(), $subject);

        $curriculumError = QuestionImportCurriculumValidator::validate(
            $request->input('class_id'),
            (string) $subject->id,
            $request->input('unit_id')
        );

        if ($curriculumError) {
            return response()->json(['message' => $curriculumError], 422);
        }

        try {
            $mapped = $mapper->normalizeMany($validated['questions']);
            $result = $persister->persistMany(
                $mapped['questions'],
                $subject->id,
                $request->filled('unit_id') ? (int) $request->unit_id : null,
                (int) $request->user()->id
            );

            $result['errors'] = array_merge($mapped['errors'], $result['errors']);
            $result['skipped'] += count($mapped['errors']);

            return response()->json([
                'success' => $result['imported'] > 0,
                'subject_id' => $subject->id,
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
                'message' => $this->buildMessage($result),
            ]);
        } catch (ExtensionImportException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  array{imported: int, skipped: int, errors: array}  $result
     */
    protected function buildMessage(array $result): string
    {
        $parts = [];

        if ($result['imported'] > 0) {
            $parts[] = 'تم حفظ '.$result['imported'].' سؤال';
        }

        if ($result['skipped'] > 0) {
            $parts[] = 'تعذر حفظ '.$result['skipped'].' سؤال';
        }

        return $parts !== [] ? implode('. ', $parts).'.' : 'لم يتم حفظ أي سؤال.';
    }
}
