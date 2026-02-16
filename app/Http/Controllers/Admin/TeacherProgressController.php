<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TeacherProgressService;
use Illuminate\Http\Request;

class TeacherProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user-list']);
    }

    /**
     * عرض تقدم المعلمين (صفحات مطلوبة + دروس أسبوعية)
     */
    public function index(Request $request)
    {
        $progress = TeacherProgressService::getAllTeachersProgress();

        return view('admin.pages.teachers.progress', compact('progress'));
    }

    /**
     * عرض تفاصيل تقدم معلم واحد
     */
    public function show(User $teacher)
    {
        if (! $teacher->hasRole('teacher')) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $stats = TeacherProgressService::getTeacherDetailStats($teacher);

        return view('admin.pages.teachers.progress-show', $stats);
    }
}
