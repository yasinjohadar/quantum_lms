<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\Subject;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService
    ) {}

    /**
     * عرض قائمة لوحات المتصدرين
     */
    public function index()
    {
        $leaderboards = Leaderboard::with('subject')->orderBy('created_at', 'desc')->get();
        return view('admin.pages.leaderboards.index', compact('leaderboards'));
    }

    /**
     * عرض نموذج إنشاء لوحة
     */
    public function create()
    {
        $subjects = Subject::all();
        return view('admin.pages.leaderboards.create', compact('subjects'));
    }

    /**
     * حفظ لوحة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:global,course,weekly,monthly',
            'subject_id' => 'nullable|exists:subjects,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
            'criteria' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $leaderboard = Leaderboard::create($request->all());

        // تحديث اللوحة
        $this->leaderboardService->updateLeaderboard($leaderboard);

        return redirect()->route('admin.leaderboards.index')
            ->with('success', 'تم إنشاء لوحة المتصدرين بنجاح');
    }

    /**
     * عرض نموذج تعديل لوحة
     */
    public function edit(Leaderboard $leaderboard)
    {
        $subjects = Subject::all();
        return view('admin.pages.leaderboards.edit', compact('leaderboard', 'subjects'));
    }

    /**
     * تحديث لوحة
     */
    public function update(Request $request, Leaderboard $leaderboard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:global,course,weekly,monthly',
            'subject_id' => 'nullable|exists:subjects,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
            'criteria' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $leaderboard->update($request->all());

        // تحديث اللوحة
        $this->leaderboardService->updateLeaderboard($leaderboard);

        return redirect()->route('admin.leaderboards.index')
            ->with('success', 'تم تحديث لوحة المتصدرين بنجاح');
    }

    /**
     * تحديث اللوحة يدوياً
     */
    public function refresh(Leaderboard $leaderboard)
    {
        $this->leaderboardService->updateLeaderboard($leaderboard);

        return redirect()->back()
            ->with('success', 'تم تحديث لوحة المتصدرين بنجاح');
    }
}

