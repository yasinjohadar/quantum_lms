<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicWeek;
use App\Models\TeacherWeekTarget;
use App\Models\User;
use App\Services\AcademicWeekService;
use App\Services\TeacherProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:teacher-progress-view']);
    }

    /**
     * عرض تقدم المعلمين (صفحات مطلوبة + دروس أسبوعية)
     */
    public function index(Request $request)
    {
        $weekId = $request->input('week_id') ? (int) $request->input('week_id') : null;
        $progress = TeacherProgressService::getAllTeachersProgress($weekId);
        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $currentWeek = AcademicWeekService::getCurrentAcademicWeek();

        return view('admin.pages.teachers.progress', compact('progress', 'activeWeeks', 'currentWeek'));
    }

    /**
     * عرض تفاصيل تقدم معلم واحد
     */
    public function show(Request $request, User $teacher)
    {
        if (! $teacher->hasRole('teacher')) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $weekId = $request->input('week_id') ? (int) $request->input('week_id') : null;
        $stats = TeacherProgressService::getTeacherDetailStats($teacher, $weekId);
        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $currentWeek = $stats['weekly_progress']['current_week'] ?? null;
        $displayWeekId = $weekId ?? $currentWeek?->id;
        $activeWeekIds = $activeWeeks->pluck('id')->all();
        $weekTargets = ! empty($activeWeekIds)
            ? TeacherWeekTarget::query()
                ->where('teacher_id', $teacher->id)
                ->whereIn('academic_week_id', $activeWeekIds)
                ->pluck('required_lessons_target', 'academic_week_id')
                ->toArray()
            : [];

        return view('admin.pages.teachers.progress-show', array_merge($stats, [
            'activeWeeks' => $activeWeeks,
            'currentWeek' => $currentWeek,
            'displayWeekId' => $displayWeekId,
            'weekTargets' => $weekTargets,
        ]));
    }

    /**
     * صفحة إحصائيات سابقة للمعلم (الأسابيع الماضية فقط في السنة النشطة).
     */
    public function history(User $teacher)
    {
        if (! $teacher->hasRole('teacher')) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $stats = TeacherProgressService::getTeacherDetailStats($teacher, null);
        $pastWeeksProgress = TeacherProgressService::getTeacherPastWeeksProgress($teacher);

        return view('admin.pages.teachers.progress-history', array_merge($stats, [
            'pastWeeksProgress' => $pastWeeksProgress,
        ]));
    }

    /**
     * حفظ أو تحديث هدف الدروس الأسبوعية المخصص للمعلم لأسبوع معين
     */
    public function storeWeekTarget(Request $request, User $teacher)
    {
        if (! $teacher->hasRole('teacher')) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $request->validate([
            'academic_week_id' => 'required|exists:academic_weeks,id',
            'required_lessons_target' => 'required|integer|min:0',
        ]);

        $week = AcademicWeek::findOrFail((int) $request->academic_week_id);
        if (AcademicWeekService::isWeekPast($week)) {
            return redirect()->back()->with('error', 'لا يمكن تعديل هدف أسبوع منتهٍ');
        }

        TeacherWeekTarget::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'academic_week_id' => $request->academic_week_id,
            ],
            ['required_lessons_target' => (int) $request->required_lessons_target]
        );

        return redirect()->back()->with('success', 'تم حفظ الهدف المخصص للأسبوع');
    }

    /**
     * حفظ أهداف الدروس الأسبوعية للمعلم لعدة أسابيع دفعة واحدة
     */
    public function storeWeekTargetsBulk(Request $request, User $teacher)
    {
        if (! $teacher->hasRole('teacher')) {
            return redirect()->back()->with('error', 'المستخدم المحدد ليس معلم');
        }

        $targets = $request->input('required_lessons_targets', []);
        if (! is_array($targets)) {
            $targets = [];
        }

        // Normalize empty values to 0
        foreach ($targets as $weekId => $value) {
            if ($value === '' || $value === null) {
                $targets[$weekId] = 0;
            }
        }

        $validator = Validator::make(
            ['required_lessons_targets' => $targets],
            [
                'required_lessons_targets' => ['required', 'array'],
                'required_lessons_targets.*' => ['required', 'integer', 'min:0'],
            ],
            [],
            ['required_lessons_targets.*' => 'هدف الدروس']
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $activeWeeks = AcademicWeekService::getActiveYearWeeks();
        $activeWeekIds = $activeWeeks->pluck('id')->map(fn ($id) => (string) $id)->all();
        $activeWeekIdsInt = $activeWeeks->pluck('id')->all();
        $activeWeeksById = $activeWeeks->keyBy('id');

        // If any submitted week is past, reject the whole request (anti-tampering).
        foreach ($targets as $weekId => $value) {
            if (! in_array((string) $weekId, $activeWeekIds, true) && ! in_array((int) $weekId, $activeWeekIdsInt, true)) {
                continue;
            }

            $week = $activeWeeksById->get((int) $weekId);
            if ($week && AcademicWeekService::isWeekPast($week)) {
                return redirect()->back()->with('error', 'لا يمكن تعديل أهداف أسبوع منتهٍ');
            }
        }

        $updated = 0;
        foreach ($targets as $weekId => $value) {
            // Only allow saving for active-year weeks
            if (! in_array((string) $weekId, $activeWeekIds, true) && ! in_array((int) $weekId, $activeWeekIdsInt, true)) {
                continue;
            }

            TeacherWeekTarget::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'academic_week_id' => (int) $weekId,
                ],
                ['required_lessons_target' => (int) $value]
            );
            $updated++;
        }

        return redirect()->back()->with('success', 'تم حفظ أهداف الأسابيع (' . $updated . ')');
    }
}
