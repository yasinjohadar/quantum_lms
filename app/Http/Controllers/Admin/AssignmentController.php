<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\Lesson;
use App\Services\AssignmentService;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService,
        private ReminderService $reminderService
    ) {
        $this->middleware(['permission:assignment-list'])->only('index');
        $this->middleware(['permission:assignment-create'])->only(['create', 'store']);
        $this->middleware(['permission:assignment-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:assignment-delete'])->only('destroy');
        $this->middleware(['permission:assignment-show'])->only('show');
        $this->middleware(['permission:assignment-publish'])->only('publish');
        $this->middleware(['permission:assignment-unpublish'])->only('unpublish');
        $this->middleware(['permission:assignment-duplicate'])->only('duplicate');
        $this->middleware(['permission:assignment-get-assignable-items'])->only('getAssignableItems');
    }

    /**
     * عرض قائمة الواجبات
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['assignable', 'creator']);

        // إذا كان المستخدم معلم وليس مشرف/مدير
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            
            $query->where(function($q) use ($classIds, $subjectIds) {
                // الواجبات المرتبطة بالمواد من الصفوف المخصصة
                $q->where(function($sq) use ($classIds, $subjectIds) {
                    $sq->where('assignable_type', \App\Models\Subject::class)
                       ->whereHasMorph('assignable', \App\Models\Subject::class, function($subQuery) use ($classIds, $subjectIds) {
                           if ($classIds->isNotEmpty()) {
                               $subQuery->whereIn('class_id', $classIds);
                           }
                           if ($subjectIds->isNotEmpty()) {
                               $subQuery->orWhereIn('id', $subjectIds);
                           }
                       });
                })
                // أو الواجبات المرتبطة بالوحدات من المواد المخصصة
                ->orWhere(function($uq) use ($classIds, $subjectIds) {
                    $uq->where('assignable_type', \App\Models\Unit::class)
                       ->whereHasMorph('assignable', \App\Models\Unit::class, function($unitQuery) use ($classIds, $subjectIds) {
                           $unitQuery->whereHas('section', function($sectionQuery) use ($classIds, $subjectIds) {
                               $sectionQuery->whereHas('subject', function($subjectQuery) use ($classIds, $subjectIds) {
                                   if ($classIds->isNotEmpty()) {
                                       $subjectQuery->whereIn('class_id', $classIds);
                                   }
                                   if ($subjectIds->isNotEmpty()) {
                                       $subjectQuery->orWhereIn('id', $subjectIds);
                                   }
                               });
                           });
                       });
                })
                // أو الواجبات المرتبطة بالدروس من المواد المخصصة
                ->orWhere(function($lq) use ($classIds, $subjectIds) {
                    $lq->where('assignable_type', \App\Models\Lesson::class)
                       ->whereHasMorph('assignable', \App\Models\Lesson::class, function($lessonQuery) use ($classIds, $subjectIds) {
                           $lessonQuery->whereHas('unit.section.subject', function($subjectQuery) use ($classIds, $subjectIds) {
                               if ($classIds->isNotEmpty()) {
                                   $subjectQuery->whereIn('class_id', $classIds);
                               }
                               if ($subjectIds->isNotEmpty()) {
                                   $subjectQuery->orWhereIn('id', $subjectIds);
                               }
                           });
                       });
                });
            });
        }

        // إذا كان المستخدم مشرف وليس مدير
        if ($user->hasRole('supervisor') && !$user->hasRole('admin')) {
            $query->forSupervisor($user->id);
        }

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // فلترة حسب نوع الربط
        if ($request->filled('assignable_type')) {
            $query->where('assignable_type', $request->input('assignable_type'));
        }

        // فلترة حسب حالة النشر
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // فلترة حسب المعلم
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->input('created_by'));
        }

        $assignments = $query->latest()->paginate(20);
        
        // جلب المواد والوحدات والدروس للفلترة
        $subjects = Subject::active()->ordered()->get();
        $units = Unit::active()->get();
        $lessons = Lesson::active()->get();

        return view('admin.pages.assignments.index', compact('assignments', 'subjects', 'units', 'lessons'));
    }

    /**
     * عرض نموذج إنشاء واجب جديد
     */
    public function create(Request $request)
    {
        // جلب العناصر للربط
        $subjects = Subject::active()->ordered()->with('schoolClass')->get();
        $units = Unit::active()->with('section.subject')->get();
        $lessons = Lesson::active()->with('unit.section.subject')->get();

        // تحديد النوع المحدد مسبقاً
        $assignableType = $request->get('type', 'subject');
        $assignableId = $request->get('id');

        // تحضير البيانات للـ JavaScript
        $subjectsJson = $subjects->map(function($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name . ($subject->schoolClass ? ' - ' . $subject->schoolClass->name : ''),
            ];
        })->toJson();

        $unitsJson = $units->map(function($unit) {
            return [
                'id' => $unit->id,
                'title' => $unit->title . ($unit->section && $unit->section->subject ? ' (' . $unit->section->subject->name . ')' : ''),
            ];
        })->toJson();

        $lessonsJson = $lessons->map(function($lesson) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title . ($lesson->unit && $lesson->unit->section && $lesson->unit->section->subject ? ' (' . $lesson->unit->section->subject->name . ')' : ''),
            ];
        })->toJson();

        return view('admin.pages.assignments.create', compact('subjects', 'units', 'lessons', 'assignableType', 'assignableId', 'subjectsJson', 'unitsJson', 'lessonsJson'));
    }

    /**
     * حفظ واجب جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignable_type' => 'required|in:App\\Models\\Subject,App\\Models\\Unit,App\\Models\\Lesson',
            'assignable_id' => 'required|integer',
            'max_score' => 'required|numeric|min:1',
            'due_date' => 'nullable|date|after:now',
            'allow_late_submission' => 'boolean',
            'late_penalty_percentage' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string',
            'max_file_size' => 'required|integer|min:1|max:100',
            'max_files_per_submission' => 'required|integer|min:1|max:20',
            'grading_type' => 'required|in:manual,auto,mixed',
        ]);

        try {
            // التحقق من وجود العنصر المرتبط
            $assignable = app($validated['assignable_type'])->findOrFail($validated['assignable_id']);

            $assignment = $this->assignmentService->createAssignment($validated, Auth::user());

            // إنشاء تذكير تلقائي إذا كان هناك due_date
            if ($assignment->due_date) {
                try {
                    $this->reminderService->createReminder(
                        'assignment',
                        $assignment->id,
                        null, // لجميع المستخدمين
                        [
                            'reminder_type' => 'multiple',
                            'reminder_times' => [1, 24, 168], // ساعة، يوم، أسبوع
                        ]
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to create automatic reminder for assignment: ' . $e->getMessage(), [
                        'assignment_id' => $assignment->id,
                    ]);
                }
            }

            return redirect()->route('admin.assignments.show', $assignment)
                ->with('success', 'تم إنشاء الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating assignment: ' . $e->getMessage(), [
                'request' => $validated,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الواجب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل واجب
     */
    public function show(Assignment $assignment)
    {
        $assignment->load(['assignable', 'creator', 'questions', 'submissions.student']);
        
        $stats = $this->assignmentService->getAssignmentStats($assignment);

        return view('admin.pages.assignments.show', compact('assignment', 'stats'));
    }

    /**
     * عرض نموذج تعديل واجب
     */
    public function edit(Assignment $assignment)
    {
        $subjects = Subject::active()->ordered()->get();
        $units = Unit::active()->with('section.subject')->get();
        $lessons = Lesson::active()->with('unit.section.subject')->get();

        return view('admin.pages.assignments.edit', compact('assignment', 'subjects', 'units', 'lessons'));
    }

    /**
     * تحديث واجب
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignable_type' => 'required|in:App\\Models\\Subject,App\\Models\\Unit,App\\Models\\Lesson',
            'assignable_id' => 'required|integer',
            'max_score' => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
            'allow_late_submission' => 'boolean',
            'late_penalty_percentage' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'allowed_file_types' => 'nullable|array',
            'allowed_file_types.*' => 'string',
            'max_file_size' => 'required|integer|min:1|max:100',
            'max_files_per_submission' => 'required|integer|min:1|max:20',
            'grading_type' => 'required|in:manual,auto,mixed',
        ]);

        try {
            $oldDueDate = $assignment->due_date;
            $this->assignmentService->updateAssignment($assignment, $validated);
            $assignment->refresh();

            // تحديث التذكيرات إذا تغير due_date
            if ($oldDueDate != $assignment->due_date && $assignment->due_date) {
                try {
                    // حذف التذكيرات القديمة
                    \App\Models\EventReminder::where('event_type', 'assignment')
                                             ->where('event_id', $assignment->id)
                                             ->delete();
                    
                    // إنشاء تذكير جديد
                    $this->reminderService->createReminder(
                        'assignment',
                        $assignment->id,
                        null,
                        [
                            'reminder_type' => 'multiple',
                            'reminder_times' => [1, 24, 168],
                        ]
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to update automatic reminder for assignment: ' . $e->getMessage(), [
                        'assignment_id' => $assignment->id,
                    ]);
                }
            }

            return redirect()->route('admin.assignments.show', $assignment)
                ->with('success', 'تم تحديث الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error updating assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'request' => $validated,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الواجب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف واجب
     */
    public function destroy(Assignment $assignment)
    {
        try {
            $assignment->delete();

            return redirect()->route('admin.assignments.index')
                ->with('success', 'تم حذف الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الواجب: ' . $e->getMessage());
        }
    }

    /**
     * نشر واجب
     */
    public function publish(Assignment $assignment)
    {
        try {
            $this->assignmentService->publishAssignment($assignment);

            return redirect()->back()
                ->with('success', 'تم نشر الواجب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء نشر الواجب: ' . $e->getMessage());
        }
    }

    /**
     * إلغاء نشر واجب
     */
    public function unpublish(Assignment $assignment)
    {
        try {
            $this->assignmentService->unpublishAssignment($assignment);

            return redirect()->back()
                ->with('success', 'تم إلغاء نشر الواجب بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إلغاء نشر الواجب: ' . $e->getMessage());
        }
    }

    /**
     * جلب المواد/الوحدات/الدروس (API)
     */
    public function getAssignableItems(Request $request)
    {
        $type = $request->get('type');
        $items = [];

        try {
            if ($type === 'App\\Models\\Subject') {
                $items = Subject::active()->ordered()->with('schoolClass')->get()->map(function($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'class' => $subject->schoolClass->name ?? '',
                    ];
                })->values();
            } elseif ($type === 'App\\Models\\Unit') {
                $items = Unit::active()->with('section.subject')->get()->map(function($unit) {
                    return [
                        'id' => $unit->id,
                        'title' => $unit->title,
                        'section' => $unit->section->title ?? '',
                        'subject' => $unit->section->subject->name ?? '',
                    ];
                })->values();
            } elseif ($type === 'App\\Models\\Lesson') {
                $items = Lesson::active()->with('unit.section.subject')->get()->map(function($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'unit' => $lesson->unit->title ?? '',
                        'subject' => $lesson->unit->section->subject->name ?? '',
                    ];
                })->values();
            }

            return response()->json($items);
        } catch (\Exception $e) {
            \Log::error('Error in getAssignableItems: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * نسخ واجب
     */
    public function duplicate(Assignment $assignment)
    {
        try {
            $newAssignment = $assignment->replicate();
            $newAssignment->title = $assignment->title . ' (نسخة)';
            $newAssignment->is_published = false;
            $newAssignment->published_at = null;
            $newAssignment->created_by = Auth::id();
            $newAssignment->save();

            // نسخ الأسئلة
            foreach ($assignment->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->assignment_id = $newAssignment->id;
                $newQuestion->save();
            }

            return redirect()->route('admin.assignments.edit', $newAssignment)
                ->with('success', 'تم نسخ الواجب بنجاح');
        } catch (\Exception $e) {
            Log::error('Error duplicating assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء نسخ الواجب: ' . $e->getMessage());
        }
    }
}
