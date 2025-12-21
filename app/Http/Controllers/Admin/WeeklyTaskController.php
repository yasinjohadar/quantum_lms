<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeeklyTask;
use Illuminate\Http\Request;

class WeeklyTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = WeeklyTask::ordered()->get();
        return view('admin.pages.tasks.weekly.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.tasks.weekly.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:attendance,lesson_completion,quiz,question',
            'points_reward' => 'required|integer|min:0',
            'criteria' => 'nullable|array',
            'start_day' => 'required|integer|min:1|max:7',
            'end_day' => 'required|integer|min:1|max:7',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        WeeklyTask::create($request->all());

        return redirect()->route('admin.weekly-tasks.index')
            ->with('success', 'تم إنشاء المهمة الأسبوعية بنجاح');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeeklyTask $weeklyTask)
    {
        return view('admin.pages.tasks.weekly.edit', compact('weeklyTask'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeeklyTask $weeklyTask)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:attendance,lesson_completion,quiz,question',
            'points_reward' => 'required|integer|min:0',
            'criteria' => 'nullable|array',
            'start_day' => 'required|integer|min:1|max:7',
            'end_day' => 'required|integer|min:1|max:7',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $weeklyTask->update($request->all());

        return redirect()->route('admin.weekly-tasks.index')
            ->with('success', 'تم تحديث المهمة الأسبوعية بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeeklyTask $weeklyTask)
    {
        $weeklyTask->delete();

        return redirect()->route('admin.weekly-tasks.index')
            ->with('success', 'تم حذف المهمة الأسبوعية بنجاح');
    }
}
