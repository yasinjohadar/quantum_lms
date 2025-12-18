<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\UpdateGroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groupsQuery = Group::withCount(['users', 'classes', 'subjects']);

        // فلترة حسب البحث
        if ($request->filled('query')) {
            $search = $request->input('query');
            $groupsQuery->search($search);
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $groupsQuery->where('is_active', $request->boolean('is_active'));
        }

        $groups = $groupsQuery->ordered()->paginate(10);

        return view('admin.pages.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['is_active'] = (bool) $request->input('is_active', false);

            $group = Group::create($data);

            return redirect()->route('admin.groups.index')
                ->with('success', 'تم إنشاء المجموعة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating group: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $group = Group::withCount(['users', 'classes', 'subjects'])
                ->with([
                    'users' => function ($q) {
                        $q->limit(10)->orderBy('name');
                    },
                    'classes.stage',
                    'subjects.schoolClass.stage',
                    'createdBy'
                ])
                ->findOrFail($id);

            return view('admin.pages.groups.show', compact('group'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error showing group: ' . $e->getMessage());
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء عرض المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $group = Group::findOrFail($id);
            return view('admin.pages.groups.edit', compact('group'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, string $id)
    {
        try {
            $group = Group::findOrFail($id);
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $group->update($data);

            return redirect()->route('admin.groups.index')
                ->with('success', 'تم تحديث المجموعة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error updating group: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $group = Group::findOrFail($id);
            $group->delete();

            return redirect()->route('admin.groups.index')
                ->with('success', 'تم حذف المجموعة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error deleting group: ' . $e->getMessage());
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة إدارة الطلاب
     */
    public function manageStudents(string $id)
    {
        try {
            $group = Group::with(['users' => function ($q) {
                $q->orderBy('name');
            }])->findOrFail($id);

            return view('admin.pages.groups.manage-students', compact('group'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error managing students: ' . $e->getMessage());
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة إدارة الطلاب: ' . $e->getMessage());
        }
    }

    /**
     * إضافة طلاب للمجموعة
     */
    public function addStudents(Request $request, string $id)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $group = Group::findOrFail($id);
            $addedBy = auth()->id();
            $notes = $request->input('notes');
            $userIds = $request->input('user_ids');

            $attached = [];
            $skipped = 0;

            foreach ($userIds as $userId) {
                // التحقق من عدم وجود الطالب في المجموعة مسبقاً
                if ($group->users()->where('user_id', $userId)->exists()) {
                    $skipped++;
                    continue;
                }

                $group->users()->attach($userId, [
                    'added_by' => $addedBy,
                    'added_at' => now(),
                    'notes' => $notes,
                ]);

                $attached[] = $userId;
            }

            DB::commit();

            $successCount = count($attached);
            $message = "تم إضافة {$successCount} طالب بنجاح";
            
            if ($skipped > 0) {
                $message .= "، وتم تخطي {$skipped} طالب موجود مسبقاً";
            }

            return redirect()
                ->route('admin.groups.manage-students', $group->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding students to group: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الطلاب: ' . $e->getMessage());
        }
    }

    /**
     * إزالة طالب من المجموعة
     */
    public function removeStudent(string $id, string $userId)
    {
        try {
            $group = Group::findOrFail($id);
            $group->users()->detach($userId);

            return redirect()
                ->route('admin.groups.manage-students', $group->id)
                ->with('success', 'تم إزالة الطالب من المجموعة بنجاح');

        } catch (\Exception $e) {
            Log::error('Error removing student from group: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إزالة الطالب');
        }
    }

    /**
     * عرض صفحة إدارة الصفوف
     */
    public function manageClasses(string $id)
    {
        try {
            $group = Group::with(['classes.stage'])->findOrFail($id);
            $stages = Stage::ordered()->get();
            $classes = SchoolClass::with('stage')->active()->ordered()->get();

            return view('admin.pages.groups.manage-classes', compact('group', 'stages', 'classes'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error managing classes: ' . $e->getMessage());
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة إدارة الصفوف: ' . $e->getMessage());
        }
    }

    /**
     * إضافة صفوف للمجموعة
     */
    public function addClasses(Request $request, string $id)
    {
        $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'required|exists:classes,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $group = Group::findOrFail($id);
            $addedBy = auth()->id();
            $notes = $request->input('notes');
            $classIds = $request->input('class_ids');

            $attached = [];
            $skipped = 0;

            foreach ($classIds as $classId) {
                // التحقق من عدم وجود الصف في المجموعة مسبقاً
                if ($group->classes()->where('class_id', $classId)->exists()) {
                    $skipped++;
                    continue;
                }

                $group->classes()->attach($classId, [
                    'added_by' => $addedBy,
                    'added_at' => now(),
                    'notes' => $notes,
                ]);

                $attached[] = $classId;
            }

            DB::commit();

            $successCount = count($attached);
            $message = "تم إضافة {$successCount} صف بنجاح";
            
            if ($skipped > 0) {
                $message .= "، وتم تخطي {$skipped} صف موجود مسبقاً";
            }

            return redirect()
                ->route('admin.groups.manage-classes', $group->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding classes to group: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الصفوف: ' . $e->getMessage());
        }
    }

    /**
     * إزالة صف من المجموعة
     */
    public function removeClass(string $id, string $classId)
    {
        try {
            $group = Group::findOrFail($id);
            $group->classes()->detach($classId);

            return redirect()
                ->route('admin.groups.manage-classes', $group->id)
                ->with('success', 'تم إزالة الصف من المجموعة بنجاح');

        } catch (\Exception $e) {
            Log::error('Error removing class from group: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إزالة الصف');
        }
    }

    /**
     * عرض صفحة إدارة المواد
     */
    public function manageSubjects(string $id)
    {
        try {
            $group = Group::with(['subjects.schoolClass.stage'])->findOrFail($id);
            $classes = SchoolClass::with('stage')->active()->ordered()->get();
            $subjects = Subject::with(['schoolClass.stage'])->active()->ordered()->get();

            return view('admin.pages.groups.manage-subjects', compact('group', 'classes', 'subjects'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.groups.index')
                ->with('error', 'المجموعة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error managing subjects: ' . $e->getMessage());
            return redirect()->route('admin.groups.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة إدارة المواد: ' . $e->getMessage());
        }
    }

    /**
     * إضافة مواد للمجموعة
     */
    public function addSubjects(Request $request, string $id)
    {
        $request->validate([
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|exists:subjects,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $group = Group::findOrFail($id);
            $addedBy = auth()->id();
            $notes = $request->input('notes');
            $subjectIds = $request->input('subject_ids');

            $attached = [];
            $skipped = 0;

            foreach ($subjectIds as $subjectId) {
                // التحقق من عدم وجود المادة في المجموعة مسبقاً
                if ($group->subjects()->where('subject_id', $subjectId)->exists()) {
                    $skipped++;
                    continue;
                }

                $group->subjects()->attach($subjectId, [
                    'added_by' => $addedBy,
                    'added_at' => now(),
                    'notes' => $notes,
                ]);

                $attached[] = $subjectId;
            }

            DB::commit();

            $successCount = count($attached);
            $message = "تم إضافة {$successCount} مادة بنجاح";
            
            if ($skipped > 0) {
                $message .= "، وتم تخطي {$skipped} مادة موجودة مسبقاً";
            }

            return redirect()
                ->route('admin.groups.manage-subjects', $group->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding subjects to group: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المواد: ' . $e->getMessage());
        }
    }

    /**
     * إزالة مادة من المجموعة
     */
    public function removeSubject(string $id, string $subjectId)
    {
        try {
            $group = Group::findOrFail($id);
            $group->subjects()->detach($subjectId);

            return redirect()
                ->route('admin.groups.manage-subjects', $group->id)
                ->with('success', 'تم إزالة المادة من المجموعة بنجاح');

        } catch (\Exception $e) {
            Log::error('Error removing subject from group: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إزالة المادة');
        }
    }
}
