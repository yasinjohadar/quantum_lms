<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\DistinguishedStudent;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\Storage\MediaStorageService;

class DistinguishedStudentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:distinguished-students-list'])->only(['index']);
        $this->middleware(['permission:distinguished-students-create'])->only(['create', 'store']);
        $this->middleware(['permission:distinguished-students-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:distinguished-students-delete'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistinguishedStudent::with(['user', 'schoolClass']);

        if ($request->filled('query')) {
            $search = $request->input('query');
            $query->where(function ($q) use ($search) {
                $q->where('quote', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('schoolClass', fn ($c) => $c->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $items = $query->ordered()->paginate(10);

        return view('admin.pages.distinguished-students.index', compact('items'));
    }

    /**
     * Return students enrolled in the given class (for dropdown).
     */
    public function studentsByClass(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);

        $students = ClassEnrollment::where('class_id', $request->class_id)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->get()
            ->map(function ($enrollment) {
                $user = $enrollment->user;
                if (! $user) {
                    return null;
                }

                return ['id' => $user->id, 'name' => $user->name];
            })
            ->filter()
            ->values();

        return response()->json($students);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = SchoolClass::active()->ordered()->get();

        return view('admin.pages.distinguished-students.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'user_id' => 'required|exists:users,id',
            'quote' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ];

        $validated = $request->validate($rules);

        $enrolled = ClassEnrollment::where('class_id', $validated['class_id'])
            ->where('user_id', $validated['user_id'])
            ->where('status', 'approved')
            ->exists();

        if (! $enrolled) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'الطالب المحدد غير مسجل في هذا الصف أو غير معتمد.');
        }

        try {
            $data = [
                'class_id' => $validated['class_id'],
                'user_id' => $validated['user_id'],
                'quote' => $validated['quote'],
                'order' => (int) ($request->input('order', 0)),
                'is_active' => $request->has('is_active'),
            ];

            if ($request->hasFile('photo')) {
                $uploadResult = MediaStorageService::uploadImage($request->file('photo'), 'distinguished-students');
                $data['photo'] = $uploadResult['path'];
            }

            DistinguishedStudent::create($data);

            return redirect()->route('admin.distinguished-students.index')
                ->with('success', 'تم إضافة الطالب المتميز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء الإضافة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistinguishedStudent $distinguishedStudent)
    {
        $classes = SchoolClass::active()->ordered()->get();
        $distinguishedStudent->load(['user', 'schoolClass']);

        return view('admin.pages.distinguished-students.edit', [
            'item' => $distinguishedStudent,
            'classes' => $classes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistinguishedStudent $distinguishedStudent)
    {
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'user_id' => 'required|exists:users,id',
            'quote' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ];

        $validated = $request->validate($rules);

        $enrolled = ClassEnrollment::where('class_id', $validated['class_id'])
            ->where('user_id', $validated['user_id'])
            ->where('status', 'approved')
            ->exists();

        if (! $enrolled) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'الطالب المحدد غير مسجل في هذا الصف أو غير معتمد.');
        }

        try {
            $data = [
                'class_id' => $validated['class_id'],
                'user_id' => $validated['user_id'],
                'quote' => $validated['quote'],
                'order' => (int) ($request->input('order', $distinguishedStudent->order)),
                'is_active' => $request->has('is_active'),
            ];

            if ($request->hasFile('photo')) {
                if ($distinguishedStudent->photo) {
                    MediaStorageService::delete($distinguishedStudent->photo);
                }
                $uploadResult = MediaStorageService::uploadImage($request->file('photo'), 'distinguished-students');
                $data['photo'] = $uploadResult['path'];
            }

            $distinguishedStudent->update($data);

            return redirect()->route('admin.distinguished-students.index')
                ->with('success', 'تم تحديث الطالب المتميز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistinguishedStudent $distinguishedStudent)
    {
        try {
            if ($distinguishedStudent->photo && Storage::disk('public')->exists($distinguishedStudent->photo)) {
                Storage::disk('public')->delete($distinguishedStudent->photo);
            }
            $distinguishedStudent->delete();

            return redirect()->route('admin.distinguished-students.index')
                ->with('success', 'تم حذف الطالب المتميز بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('admin.distinguished-students.index')
                ->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }
}
