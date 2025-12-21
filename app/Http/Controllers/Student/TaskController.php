<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * عرض مهام الطالب
     */
    public function index()
    {
        $user = Auth::user();
        
        $dailyTasks = $this->taskService->getDailyTasks();
        $weeklyTasks = $this->taskService->getWeeklyTasks();
        $userTasks = $this->taskService->getUserTasks($user);

        // تجميع المهام حسب النوع
        $dailyUserTasks = $userTasks->where('taskable_type', \App\Models\DailyTask::class)->keyBy('taskable_id');
        $weeklyUserTasks = $userTasks->where('taskable_type', \App\Models\WeeklyTask::class)->keyBy('taskable_id');

        return view('student.pages.tasks.index', [
            'dailyTasks' => $dailyTasks,
            'weeklyTasks' => $weeklyTasks,
            'dailyUserTasks' => $dailyUserTasks,
            'weeklyUserTasks' => $weeklyUserTasks,
        ]);
    }
}
