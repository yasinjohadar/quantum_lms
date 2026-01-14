<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $classesQuery = SchoolClass::with('stage');

        // فلترة حسب البحث
        if ($request->filled('query')) {
            $search = $request->input('query');
            $classesQuery->search($search);
        }

        // فلترة حسب المرحلة
        if ($request->filled('stage_id')) {
            $classesQuery->byStage($request->input('stage_id'));
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $classesQuery->where('is_active', $request->boolean('is_active'));
        }

        $classes = $classesQuery->ordered()->paginate(10);
        $stages = Stage::ordered()->get();

        // إذا كان طلب Ajax، إرجاع JSON
        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.classes.partials.table', compact('classes'))->render();
            $pagination = view('admin.pages.classes.partials.pagination', compact('classes'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $classes->total(),
            ]);
        }

        return view('admin.pages.classes.index', compact('classes', 'stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stages = Stage::ordered()->get();
        return view('admin.pages.classes.create', compact('stages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassRequest $request)
    {
        try {
            $data = $request->validated();

            // صورة الصف
            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('classes/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة الصف: ' . $e->getMessage());
                }
            }

            // صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('classes/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph للصف: ' . $e->getMessage());
                }
            }

            $data['is_active'] = $request->has('is_active');
            $data['order'] = $request->input('order', 0);

            SchoolClass::create($data);

            return redirect()->route('admin.classes.index')
                ->with('success', 'تم إضافة الصف بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating class: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الصف: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $class = SchoolClass::with(['stage', 'subjects'])->findOrFail($id);
            return view('admin.pages.classes.show', compact('class'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'الصف المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error showing class: ' . $e->getMessage());
            return redirect()->route('admin.classes.index')
                ->with('error', 'حدث خطأ أثناء عرض الصف: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $class = SchoolClass::findOrFail($id);
            $stages = Stage::ordered()->get();
            return view('admin.pages.classes.edit', compact('class', 'stages'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'الصف المطلوب غير موجود');
        } catch (\Exception $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassRequest $request, string $id)
    {
        try {
            $class = SchoolClass::findOrFail($id);
            $data = $request->validated();

            // صورة الصف
            if ($request->hasFile('image')) {
                try {
                    if ($class->image) {
                        StorageHelper::delete('images', $class->image);
                    }

                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('classes/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة الصف: ' . $e->getMessage());
                }
            } else {
                unset($data['image']);
            }

            // صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    if ($class->og_image) {
                        StorageHelper::delete('images', $class->og_image);
                    }

                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('classes/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph: ' . $e->getMessage());
                }
            } else {
                unset($data['og_image']);
            }

            $data['is_active'] = $request->has('is_active');
            $data['order'] = $request->input('order', $class->order);

            $class->update($data);

            return redirect()->route('admin.classes.index')
                ->with('success', 'تم تحديث الصف بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'الصف المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error updating class: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الصف: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            try {
                if ($class->image) {
                    StorageHelper::delete('images', $class->image);
                }
                if ($class->og_image) {
                    StorageHelper::delete('images', $class->og_image);
                }
            } catch (\Exception $e) {
                Log::warning('فشل حذف صور الصف: ' . $e->getMessage());
            }

            $class->delete();

            return redirect()->route('admin.classes.index')
                ->with('success', 'تم حذف الصف بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'الصف المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error deleting class: ' . $e->getMessage());
            return redirect()->route('admin.classes.index')
                ->with('error', 'حدث خطأ أثناء حذف الصف: ' . $e->getMessage());
        }
    }

    /**
     * عرض الطلاب المنضمين لصف معين
     */
    public function enrolledStudents(string $id, Request $request)
    {
        try {
            $class = SchoolClass::with('stage')->findOrFail($id);
            
            // جلب enrollments للمواد التابعة لهذا الصف
            $enrollmentsQuery = Enrollment::with(['user', 'subject', 'enrolledBy'])
                ->whereHas('subject', function ($q) use ($id) {
                    $q->where('class_id', $id);
                });

            // فلترة حسب البحث
            if ($request->filled('search')) {
                $search = $request->input('search');
                $enrollmentsQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }

            // فلترة حسب المادة
            if ($request->filled('subject_id')) {
                $enrollmentsQuery->where('subject_id', $request->input('subject_id'));
            }

            // فلترة حسب الحالة
            if ($request->filled('status')) {
                $enrollmentsQuery->where('status', $request->input('status'));
            }

            $enrollments = $enrollmentsQuery->latest('enrolled_at')->paginate(20);
            
            // جلب المواد التابعة للصف للفلترة
            $subjects = \App\Models\Subject::where('class_id', $id)
                ->active()
                ->ordered()
                ->get();

            return view('admin.pages.classes.enrolled-students', compact('class', 'enrollments', 'subjects'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'الصف المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error showing enrolled students: ' . $e->getMessage());
            return redirect()->route('admin.classes.index')
                ->with('error', 'حدث خطأ أثناء عرض الطلاب: ' . $e->getMessage());
        }
    }
}
