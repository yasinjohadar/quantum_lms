<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TeacherProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherOwnProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.user.active', 'admin']);
    }

    /**
     * تفاصيل الدروس المعتمدة وصفحات الكتاب لكل مادة مخصّصة للمعلم الحالي.
     */
    public function approvedLessonsDetail(Request $request): View
    {
        $user = auth()->user();
        if (! $user || ! $user->usesTeacherAssignmentScope()) {
            abort(403, 'هذه الصفحة مخصصة للمعلمين فقط.');
        }

        $subjectId = $request->filled('subject_id') ? (int) $request->input('subject_id') : null;
        $grandTotals = TeacherProgressService::getTeacherApprovedLessonsGrandTotals($user);
        $subjectSummaries = TeacherProgressService::getTeacherApprovedLessonsSubjectSummaries($user);
        $lessons = TeacherProgressService::paginateTeacherApprovedLessons($user, $subjectId, 50);

        return view('admin.pages.teachers.my-approved-lessons', [
            'subjectSummaries' => $subjectSummaries,
            'lessons' => $lessons,
            'selectedSubjectId' => $subjectId,
            'grandTotalPages' => $grandTotals['total_pages'],
            'grandLessonsCount' => $grandTotals['lessons_count'],
            'viewedTeacher' => null,
        ]);
    }
}
