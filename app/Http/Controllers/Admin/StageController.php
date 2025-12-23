<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStageRequest;
use App\Http\Requests\Admin\UpdateStageRequest;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $stagesQuery = Stage::query();

        // فلترة حسب البحث
        if ($request->filled('query')) {
            $search = $request->input('query');
            $stagesQuery->search($search);
        }

        // فلترة حسب الحالة النشطة
        if ($request->filled('is_active')) {
            $stagesQuery->where('is_active', $request->input('is_active'));
        }

        // ترتيب النتائج
        $stages = $stagesQuery->ordered()->paginate(10);

        return view('admin.pages.stages.index', compact('stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.stages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStageRequest $request)
    {
        try {
            $data = $request->validated();

            // معالجة صورة المرحلة
            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('stages/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المرحلة: ' . $e->getMessage());
                }
            }

            // معالجة صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('stages/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph: ' . $e->getMessage());
                }
            }

            // معالجة is_active
            $data['is_active'] = $request->has('is_active');

            // معالجة order
            $data['order'] = $request->input('order', 0);

            Stage::create($data);

            return redirect()->route('admin.stages.index')
                ->with('success', 'تم إضافة المرحلة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المرحلة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $stage = Stage::with(['classes' => function ($q) {
                $q->ordered();
            }])->findOrFail($id);
            return view('admin.pages.stages.show', compact('stage'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'المرحلة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'حدث خطأ أثناء عرض المرحلة: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $stage = Stage::findOrFail($id);
            return view('admin.pages.stages.edit', compact('stage'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'المرحلة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStageRequest $request, string $id)
    {
        try {
            $stage = Stage::findOrFail($id);
            $data = $request->validated();

            // معالجة صورة المرحلة
            if ($request->hasFile('image')) {
                try {
                    // حذف الصورة القديمة
                    if ($stage->image) {
                        StorageHelper::delete('images', $stage->image);
                    }

                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $data['image'] = $image->storeAs('stages/images', $imageName, 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المرحلة: ' . $e->getMessage());
                }
            } else {
                // إبقاء الصورة القديمة
                unset($data['image']);
            }

            // معالجة صورة Open Graph
            if ($request->hasFile('og_image')) {
                try {
                    // حذف الصورة القديمة
                    if ($stage->og_image) {
                        StorageHelper::delete('images', $stage->og_image);
                    }

                    $ogImage = $request->file('og_image');
                    $ogImageName = time() . '_og_' . $ogImage->getClientOriginalName();
                    $data['og_image'] = $ogImage->storeAs('stages/og_images', $ogImageName, 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة Open Graph: ' . $e->getMessage());
                }
            } else {
                // إبقاء الصورة القديمة
                unset($data['og_image']);
            }

            // معالجة is_active
            $data['is_active'] = $request->has('is_active');

            // معالجة order
            $data['order'] = $request->input('order', $stage->order);

            $stage->update($data);

            return redirect()->route('admin.stages.index')
                ->with('success', 'تم تحديث المرحلة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'المرحلة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المرحلة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $stage = Stage::findOrFail($id);

            // حذف الصور
            try {
                if ($stage->image) {
                    StorageHelper::delete('images', $stage->image);
                }
                if ($stage->og_image) {
                    StorageHelper::delete('images', $stage->og_image);
                }
            } catch (\Exception $e) {
                // في حالة فشل حذف الصور، نتابع حذف المرحلة
                \Log::warning('فشل حذف صور المرحلة: ' . $e->getMessage());
            }

            $stage->delete();

            return redirect()->route('admin.stages.index')
                ->with('success', 'تم حذف المرحلة بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'المرحلة المطلوبة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->route('admin.stages.index')
                ->with('error', 'حدث خطأ أثناء حذف المرحلة: ' . $e->getMessage());
        }
    }
}

