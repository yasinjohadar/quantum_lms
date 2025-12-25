<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $subjectsQuery = Subject::with(['schoolClass.stage']);

        // فلترة حسب البحث
        if ($request->filled('query')) {
            $search = $request->input('query');
            $subjectsQuery->search($search);
        }

        // فلترة حسب الصف
        if ($request->filled('class_id')) {
            $subjectsQuery->byClass($request->input('class_id'));
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $subjectsQuery->where('is_active', $request->boolean('is_active'));
        }

        $subjects = $subjectsQuery->ordered()->paginate(10);
        $classes = SchoolClass::with('stage')->ordered()->get();

        return view('admin.pages.subjects.index', compact('subjects', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = SchoolClass::with('stage')->ordered()->get();
        return view('admin.pages.subjects.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubjectRequest $request)
    {
        try {
            $data = $request->validated();

            // صورة المادة
            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('subjects/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المادة: ' . $e->getMessage());
                }
            }

            // صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('subjects/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph للمادة: ' . $e->getMessage());
                }
            }

            $data['is_active'] = $request->has('is_active');
            $data['display_in_class'] = $request->has('display_in_class');
            $data['order'] = $request->input('order', 0);

            Subject::create($data);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'تم إضافة المادة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating subject: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المادة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subject = Subject::with([
                'schoolClass.stage',
                'sections' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'sections.units' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'sections.units.lessons' => function ($q) {
                    $q->orderBy('order');
                },
                'sections.units.lessons.attachments' => function ($q) {
                    $q->orderBy('order');
                },
                'sections.units.questions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'sections.units.quizzes' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
            ])->findOrFail($id);
            return view('admin.pages.subjects.show', compact('subject'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'المادة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error showing subject: ' . $e->getMessage());
            return redirect()->route('admin.subjects.index')
                ->with('error', 'حدث خطأ أثناء عرض المادة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $classes = SchoolClass::with('stage')->ordered()->get();
            return view('admin.pages.subjects.edit', compact('subject', 'classes'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'المادة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubjectRequest $request, string $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $data = $request->validated();

            // صورة المادة
            if ($request->hasFile('image')) {
                try {
                    if ($subject->image) {
                        StorageHelper::delete('images', $subject->image);
                    }

                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('subjects/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المادة: ' . $e->getMessage());
                }
            } else {
                unset($data['image']);
            }

            // صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    if ($subject->og_image) {
                        StorageHelper::delete('images', $subject->og_image);
                    }

                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('subjects/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph: ' . $e->getMessage());
                }
            } else {
                unset($data['og_image']);
            }

            $data['is_active'] = $request->has('is_active');
            $data['display_in_class'] = $request->has('display_in_class');
            $data['order'] = $request->input('order', $subject->order);

            $subject->update($data);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'تم تحديث المادة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'المادة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error updating subject: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المادة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subject = Subject::findOrFail($id);

            try {
                if ($subject->image) {
                    StorageHelper::delete('images', $subject->image);
                }
                if ($subject->og_image) {
                    StorageHelper::delete('images', $subject->og_image);
                }
            } catch (\Exception $e) {
                Log::warning('فشل حذف صور المادة: ' . $e->getMessage());
            }

            $subject->delete();

            return redirect()->route('admin.subjects.index')
                ->with('success', 'تم حذف المادة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'المادة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error deleting subject: ' . $e->getMessage());
            return redirect()->route('admin.subjects.index')
                ->with('error', 'حدث خطأ أثناء حذف المادة: ' . $e->getMessage());
        }
    }

    /**
     * عرض الطلاب المنضمين لمادة معينة
     */
    public function enrolledStudents(string $id, Request $request)
    {
        try {
            $subject = Subject::with(['schoolClass.stage'])->findOrFail($id);
            
            $enrollmentsQuery = Enrollment::with(['user', 'enrolledBy'])
                ->where('subject_id', $id);

            // فلترة حسب البحث
            if ($request->filled('search')) {
                $search = $request->input('search');
                $enrollmentsQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }

            // فلترة حسب الحالة
            if ($request->filled('status')) {
                $enrollmentsQuery->where('status', $request->input('status'));
            }

            $enrollments = $enrollmentsQuery->latest('enrolled_at')->paginate(20);

            return view('admin.pages.subjects.enrolled-students', compact('subject', 'enrollments'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'المادة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Error showing enrolled students: ' . $e->getMessage());
            return redirect()->route('admin.subjects.index')
                ->with('error', 'حدث خطأ أثناء عرض الطلاب: ' . $e->getMessage());
        }
    }
}
