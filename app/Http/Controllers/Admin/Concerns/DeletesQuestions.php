<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Helpers\StorageHelper;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait DeletesQuestions
{
    /**
     * @return null|string null on success; 'used_in_quizzes' or 'error' on failure
     */
    protected function deleteQuestionSafely(Question $question): ?string
    {
        if ($question->quizzes()->count() > 0) {
            return 'used_in_quizzes';
        }

        try {
            if ($question->image) {
                StorageHelper::delete('images', $question->image);
            }

            foreach ($question->options as $option) {
                if ($option->image) {
                    StorageHelper::delete('images', $option->image);
                }
            }

            $question->delete();

            return null;
        } catch (\Exception $e) {
            Log::error('Error deleting question '.$question->id.': '.$e->getMessage());

            return 'error';
        }
    }

    /**
     * @param  Collection<int, Question>|iterable<Question>  $questions
     * @return array{deleted: int, skipped_quiz: int, failed: int}
     */
    protected function bulkDeleteQuestions(iterable $questions): array
    {
        $deleted = 0;
        $skippedQuiz = 0;
        $failed = 0;

        foreach ($questions as $question) {
            $question->loadMissing('options');
            $result = $this->deleteQuestionSafely($question);

            if ($result === null) {
                $deleted++;
            } elseif ($result === 'used_in_quizzes') {
                $skippedQuiz++;
            } else {
                $failed++;
            }
        }

        return [
            'deleted' => $deleted,
            'skipped_quiz' => $skippedQuiz,
            'failed' => $failed,
        ];
    }

    /**
     * @return array{type: string, message: string}
     */
    protected function bulkDeleteFlashMessage(int $deleted, int $skippedQuiz, int $failed): array
    {
        $parts = [];

        if ($deleted > 0) {
            $parts[] = 'تم حذف '.$deleted.' '.($deleted === 1 ? 'سؤال' : 'أسئلة');
        }

        if ($skippedQuiz > 0) {
            $parts[] = 'تعذر حذف '.$skippedQuiz.' '.($skippedQuiz === 1 ? 'سؤال' : 'أسئلة').' لأنها مستخدمة في اختبارات';
        }

        if ($failed > 0) {
            $parts[] = 'فشل حذف '.$failed.' '.($failed === 1 ? 'سؤال' : 'أسئلة');
        }

        if ($deleted === 0 && $skippedQuiz === 0 && $failed === 0) {
            return [
                'type' => 'error',
                'message' => 'لم يتم حذف أي سؤال. تحقق من التحديد والصلاحيات.',
            ];
        }

        if ($deleted === 0) {
            return [
                'type' => 'error',
                'message' => implode('. ', $parts).'.',
            ];
        }

        if ($skippedQuiz > 0 || $failed > 0) {
            return [
                'type' => 'warning',
                'message' => implode('. ', $parts).'.',
            ];
        }

        return [
            'type' => 'success',
            'message' => implode('. ', $parts).'.',
        ];
    }

    protected function applyTeacherQuestionScope(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('teacher') && ! $user->hasAnyRole(['admin', 'supervisor'])) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');

            $unitIds = \App\Models\Unit::whereHas('section', function ($q) use ($classIds, $subjectIds) {
                $q->whereHas('subject', function ($sq) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $sq->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        $sq->orWhereIn('id', $subjectIds);
                    }
                });
            })->pluck('id');

            $query->where(function ($q) use ($unitIds) {
                if ($unitIds->isNotEmpty()) {
                    $q->whereHas('units', function ($uq) use ($unitIds) {
                        $uq->whereIn('units.id', $unitIds);
                    });
                }
            });
        }

        return $query;
    }
}
