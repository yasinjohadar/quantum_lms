<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait BuildsQuestionBankIndex
{
    use DeletesQuestions;
    protected function buildQuestionIndexQuery(Request $request, ?Subject $subject = null): Builder
    {
        $query = Question::with(['units.section.subject.schoolClass', 'creator', 'options', 'subject'])
            ->withCount(['quizzes']);

        if ($subject !== null) {
            $query->forSubject($subject);
        }

        $this->applyTeacherQuestionScope($query);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('difficulty')) {
            $query->ofDifficulty($request->difficulty);
        }

        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('unit_id')) {
            if ($request->unit_id === 'general') {
                $query->general();
            } else {
                $query->inUnits([(int) $request->unit_id]);
            }
        }

        if ($subject === null) {
            if ($request->filled('subject_id')) {
                $query->forSubject((int) $request->subject_id);
            } elseif ($request->filled('class_id')) {
                $classId = (int) $request->class_id;
                $query->where(function ($q) use ($classId) {
                    $q->whereHas('subject', fn ($sq) => $sq->where('class_id', $classId))
                        ->orWhereHas('units.section.subject', fn ($sq) => $sq->where('class_id', $classId));
                });
            }
        }

        $sort = $request->get('sort', 'latest');
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'title') {
            $query->orderBy('title');
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * @return array{units: \Illuminate\Support\Collection, categories: \Illuminate\Support\Collection, subjects: \Illuminate\Support\Collection, schoolClasses?: \Illuminate\Support\Collection, initialSubjects?: \Illuminate\Support\Collection}
     */
    protected function questionIndexFilterLists(?Subject $subject = null, ?Request $request = null): array
    {
        if ($subject !== null) {
            $units = Unit::query()
                ->whereHas('section', fn ($q) => $q->where('subject_id', $subject->id))
                ->with('section.subject')
                ->orderBy('title')
                ->get();

            return [
                'units' => $units,
                'categories' => Question::forSubject($subject)->distinct()->whereNotNull('category')->pluck('category'),
                'subjects' => collect([$subject]),
            ];
        }

        $initialSubjects = collect();
        if ($request?->filled('class_id')) {
            $schoolClass = SchoolClass::find((int) $request->class_id);
            if ($schoolClass) {
                $initialSubjects = $this->subjectsForClassFilterQuery($schoolClass)->get(['id', 'name']);
            }
        }

        return [
            'units' => Unit::with('section.subject')->orderBy('title')->get(),
            'categories' => Question::distinct()->whereNotNull('category')->pluck('category'),
            'subjects' => Subject::with('schoolClass')->orderBy('name')->get(),
            'schoolClasses' => SchoolClass::active()->ordered()->get(),
            'initialSubjects' => $initialSubjects,
        ];
    }

    /**
     * مواد الصف لقائمة فلتر بنك الأسئلة (مع تقييد نطاق المعلم/المشرف).
     */
    protected function subjectsForClassFilterQuery(SchoolClass $schoolClass): \Illuminate\Database\Eloquent\Builder
    {
        $query = Subject::where('class_id', $schoolClass->id)->active()->ordered();

        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope() && ! $user->hasAnyRole(['admin', 'supervisor'])) {
            if (! $user->isAssignedToClass($schoolClass->id)) {
                $assignedSubjectIds = $user->assignedSubjects()->pluck('subjects.id');
                $query->whereIn('id', $assignedSubjectIds);
            }
        } elseif ($user->usesSupervisorAssignmentScope()) {
            if (! $user->isAssignedToClassAsSupervisor($schoolClass->id)) {
                $assignedSubjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');
                $query->whereIn('id', $assignedSubjectIds);
            }
        }

        return $query;
    }

    protected function authorizeManagedSubjectAccess($user, Subject $subject): void
    {
        if ($user->usesTeacherAssignmentScope()) {
            if (! $user->isAssignedToSubject($subject->id) && ! $user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
            }

            return;
        }

        if ($user->usesSupervisorAssignmentScope()) {
            if (! $user->isAssignedToSubjectAsSupervisor($subject->id) && ! $user->isAssignedToClassAsSupervisor($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
            }
        }
    }
}
