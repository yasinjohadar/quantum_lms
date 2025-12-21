<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyTask;
use Illuminate\Http\Request;

class DailyTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = DailyTask::ordered()->get();
        return view('admin.pages.tasks.daily.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.tasks.daily.create');
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
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        DailyTask::create($request->all());

        return redirect()->route('admin.daily-tasks.index')
            ->with('success', 'تم إنشاء المهمة اليومية بنجاح');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyTask $dailyTask)
    {
        return view('admin.pages.tasks.daily.edit', compact('dailyTask'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyTask $dailyTask)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:attendance,lesson_completion,quiz,question',
            'points_reward' => 'required|integer|min:0',
            'criteria' => 'nullable|array',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $dailyTask->update($request->all());

        return redirect()->route('admin.daily-tasks.index')
            ->with('success', 'تم تحديث المهمة اليومية بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyTask $dailyTask)
    {
        $dailyTask->delete();

        return redirect()->route('admin.daily-tasks.index')
            ->with('success', 'تم حذف المهمة اليومية بنجاح');
    }
}
