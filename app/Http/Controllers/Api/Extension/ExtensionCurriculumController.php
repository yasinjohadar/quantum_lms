<?php

namespace App\Http\Controllers\Api\Extension;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\Request;

class ExtensionCurriculumController extends Controller
{
    use BuildsQuestionBankIndex;

    public function classes(Request $request)
    {
        $user = $request->user();
        $query = SchoolClass::active()->ordered();

        if ($user->usesTeacherAssignmentScope() && ! $user->hasAnyRole(['admin', 'supervisor'])) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            if ($classIds->isNotEmpty()) {
                $query->whereIn('id', $classIds);
            }
        } elseif ($user->usesSupervisorAssignmentScope()) {
            $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
            if ($classIds->isNotEmpty()) {
                $query->whereIn('id', $classIds);
            }
        }

        $classes = $query->get(['id', 'name']);

        return response()->json(['data' => $classes]);
    }

    public function subjects(Request $request)
    {
        $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
        ]);

        $user = $request->user();
        $query = Subject::active()->ordered();

        if ($request->filled('class_id')) {
            $schoolClass = SchoolClass::findOrFail((int) $request->class_id);
            $query = $this->subjectsForClassFilterQuery($schoolClass);
        } elseif ($user->usesTeacherAssignmentScope() && ! $user->hasRole('admin')) {
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $query->whereIn('id', $subjectIds);
        } elseif ($user->usesSupervisorAssignmentScope() && ! $user->hasRole('admin')) {
            $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');
            $query->whereIn('id', $subjectIds);
        }

        $subjects = $query->get(['id', 'name', 'class_id']);

        return response()->json(['data' => $subjects]);
    }

    public function units(Request $request)
    {
        $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $subject = Subject::findOrFail((int) $request->subject_id);
        $this->authorizeManagedSubjectAccess($request->user(), $subject);

        $units = Unit::query()
            ->whereHas('section', fn ($q) => $q->where('subject_id', $subject->id))
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json(['data' => $units]);
    }
}
