<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TeacherProgressService;
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
    public function approvedLessonsDetail(): View
    {
        $user = auth()->user();
        if (! $user || ! $user->usesTeacherAssignmentScope()) {
            abort(403, 'هذه الصفحة مخصصة للمعلمين فقط.');
        }

        $bySubject = TeacherProgressService::getTeacherApprovedLessonsDetailBySubject($user);
        $grandTotalPages = (int) array_sum(array_column($bySubject, 'total_pages'));
        $grandLessonsCount = (int) array_sum(array_column($bySubject, 'lessons_count'));

        return view('admin.pages.teachers.my-approved-lessons', [
            'bySubject' => $bySubject,
            'grandTotalPages' => $grandTotalPages,
            'grandLessonsCount' => $grandLessonsCount,
            'viewedTeacher' => null,
        ]);
    }
}
