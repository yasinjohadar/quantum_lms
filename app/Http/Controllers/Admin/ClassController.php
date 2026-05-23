<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Models\ClassFeature;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use App\Services\Storage\MediaStorageService;

class ClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:class-create'])->only(['create', 'store']);
        $this->middleware(['permission:class-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:class-delete'])->only('destroy');
        $this->middleware(['permission:class-enrolled-students'])->only('enrolledStudents');
        $this->middleware(['permission:class-toggle-status'])->only('toggleStatus');
        $this->middleware(['permission:class-edit'])->only('reorder');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeClassIndexAccess(auth()->user());

        $classesQuery = SchoolClass::with('stage');

        // إذا كان المستخدم معلم وليس مشرف/مدير، عرض فقط الصفوف المخصصة له
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $allowedClassIds = $user->getTeacherAllowedClassIds();
            if ($allowedClassIds->isEmpty()) {
                $classesQuery->whereRaw('1 = 0');
            } else {
                $classesQuery->whereIn('id', $allowedClassIds);
            }
        }

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

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $classes = $classesQuery->ordered()->paginate($perPage);
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
     * إعادة ترتيب الصفوف (السحب والإفلات). يستقبل ترتيب الصفوف في الصفحة الحالية
     * ويعيد تعيين عمود order لجميع الصفوف المطابقة للفلاتر.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:classes,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'query' => ['nullable', 'string'],
            'stage_id' => ['nullable', 'integer', 'exists:stages,id'],
            'is_active' => ['nullable', 'string', 'in:0,1'],
        ]);

        $order = array_map('intval', $request->input('order'));
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        $classesQuery = SchoolClass::query();
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $allowedClassIds = $user->getTeacherAllowedClassIds();
            if ($allowedClassIds->isEmpty()) {
                $classesQuery->whereRaw('1 = 0');
            } else {
                $classesQuery->whereIn('id', $allowedClassIds);
            }
        }
        if ($request->filled('query')) {
            $classesQuery->search($request->input('query'));
        }
        if ($request->filled('stage_id')) {
            $classesQuery->byStage($request->input('stage_id'));
        }
        if ($request->filled('is_active')) {
            $classesQuery->where('is_active', $request->boolean('is_active'));
        }

        $allIds = $classesQuery->ordered()->pluck('id')->toArray();
        $offset = ($page - 1) * $perPage;
        $expectedPageIds = array_slice($allIds, $offset, $perPage);

        $sortedOrder = $order;
        sort($sortedOrder);
        $sortedExpected = $expectedPageIds;
        sort($sortedExpected);
        if (count($order) !== count($expectedPageIds) || $sortedOrder !== $sortedExpected) {
            return response()->json([
                'success' => false,
                'message' => 'ترتيب الصفوف لا يطابق الصفحة الحالية.',
            ], 422);
        }

        $order = array_map('intval', $request->input('order'));
        $newAllIds = array_merge(
            array_slice($allIds, 0, $offset),
            $order,
            array_slice($allIds, $offset + count($order))
        );

        foreach ($newAllIds as $index => $id) {
            SchoolClass::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
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
                    $uploadResult = MediaStorageService::uploadImage($image, 'classes/images', $imageName);
                    $data['image'] = $uploadResult['path'];
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة الصف: ' . $e->getMessage());
                }
            }

            $data['is_active'] = $request->has('is_active');
            $data['order'] = $request->input('order', 0);
            $data['price'] = $request->input('price', 0);
            $data['is_free'] = $request->has('is_free') || $request->input('price', 0) == 0;
            $data['show_price'] = $data['is_free'] ? true : $request->has('show_price');
            $data = \App\Support\AdminCustomPriceLabelInput::merge($data, $request);
            $data['allow_subjects_purchase'] = $request->has('allow_subjects_purchase');
            $data['default_currency_id'] = $request->input('default_currency_id');
            $joinRequiresPayment = ! $data['is_free'] && (float) $data['price'] > 0;
            $data['free_join_auto_approve'] = $joinRequiresPayment
                ? true
                : $request->boolean('free_join_auto_approve', true);

            $class = SchoolClass::create($data);

            // معالجة الأسعار المتعددة
            if ($request->has('prices')) {
                foreach ($request->prices as $currencyId => $priceData) {
                    if (isset($priceData['price']) && $priceData['price'] > 0) {
                        \App\Models\Price::create([
                            'pricable_type' => get_class($class),
                            'pricable_id' => $class->id,
                            'currency_id' => $currencyId,
                            'price' => $priceData['price'],
                            'is_active' => isset($priceData['is_active']),
                        ]);
                    }
                }
            }

            // معالجة خصائص الصف (حتى 10)
            if ($request->has('features')) {
                $labels = array_values(array_filter(array_map('trim', (array) $request->features), fn ($v) => $v !== ''));
                $labels = array_slice($labels, 0, 10);
                foreach ($labels as $order => $label) {
                    ClassFeature::create([
                        'class_id' => $class->id,
                        'label' => $label,
                        'order' => $order,
                    ]);
                }
            }

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
            $class = SchoolClass::with(['stage', 'subjects', 'features'])->findOrFail($id);
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeClassShowAccess($user);
            $this->authorizeManagedClassAccess($user, $class);
            
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
            $class = SchoolClass::with('features')->findOrFail($id);
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedClassAccess($user, $class);
            
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
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedClassAccess($user, $class);
            $data = $request->validated();

            // صورة الصف
            if ($request->hasFile('image')) {
                try {
                    if ($class->image) {
                        MediaStorageService::delete($class->image);
                    }

                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $uploadResult = MediaStorageService::uploadImage($image, 'classes/images', $imageName);
                    $data['image'] = $uploadResult['path'];
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة الصف: ' . $e->getMessage());
                }
            } else {
                unset($data['image']);
            }

            $data['is_active'] = $request->has('is_active');
            $data['order'] = $request->input('order', $class->order);
            $data['price'] = $request->input('price', 0);
            $data['is_free'] = $request->has('is_free') || $request->input('price', 0) == 0;
            $data['show_price'] = $data['is_free'] ? true : $request->has('show_price');
            $data = \App\Support\AdminCustomPriceLabelInput::merge($data, $request);
            $data['allow_subjects_purchase'] = $request->has('allow_subjects_purchase');
            $data['default_currency_id'] = $request->input('default_currency_id');
            $joinRequiresPayment = ! $data['is_free'] && (float) $data['price'] > 0;
            $data['free_join_auto_approve'] = $joinRequiresPayment
                ? true
                : $request->boolean('free_join_auto_approve', true);

            $class->update($data);

            // معالجة خصائص الصف (حذف القديمة وإعادة إنشائها من الطلب، حتى 10)
            ClassFeature::where('class_id', $class->id)->delete();
            if ($request->has('features')) {
                $labels = array_values(array_filter(array_map('trim', (array) $request->features), fn ($v) => $v !== ''));
                $labels = array_slice($labels, 0, 10);
                foreach ($labels as $order => $label) {
                    ClassFeature::create([
                        'class_id' => $class->id,
                        'label' => $label,
                        'order' => $order,
                    ]);
                }
            }

            // معالجة الأسعار المتعددة
            if ($request->has('prices')) {
                foreach ($request->prices as $currencyId => $priceData) {
                    if (isset($priceData['id'])) {
                        // تحديث سعر موجود
                        $price = \App\Models\Price::find($priceData['id']);
                        if ($price) {
                            $price->update([
                                'price' => $priceData['price'] ?? 0,
                                'is_active' => isset($priceData['is_active']),
                            ]);
                        }
                    } else {
                        // إنشاء سعر جديد
                        if (isset($priceData['price']) && $priceData['price'] > 0) {
                            \App\Models\Price::updateOrCreate(
                                [
                                    'pricable_type' => get_class($class),
                                    'pricable_id' => $class->id,
                                    'currency_id' => $currencyId,
                                ],
                                [
                                    'price' => $priceData['price'],
                                    'is_active' => isset($priceData['is_active']),
                                ]
                            );
                        }
                    }
                }
            }

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
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedClassAccess($user, $class);

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
            $this->authorizeManagedClassAccess(auth()->user(), $class);
            
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

    /**
     * تبديل حالة الصف (تفعيل / إلغاء تفعيل)
     */
    public function toggleStatus(SchoolClass $class)
    {
        try {
            $class->is_active = !$class->is_active;
            $class->save();

            $statusText = $class->is_active ? 'نشط' : 'غير نشط';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة الصف إلى: {$statusText}");
        } catch (\Exception $e) {
            Log::error('Error toggling class status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'فشل تحديث حالة الصف');
        }
    }

    private function authorizeManagedClassAccess($user, SchoolClass $class): void
    {
        if ($user->usesTeacherAssignmentScope()) {
            if (!$user->isAssignedToClass($class->id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الصف');
            }
            return;
        }

        if ($user->usesSupervisorAssignmentScope()) {
            if (!$user->isAssignedToClassAsSupervisor($class->id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الصف');
            }
        }
    }

    private function authorizeClassIndexAccess($user): void
    {
        if ($user->usesTeacherAssignmentScope()) {
            return;
        }

        if (!$user->can('class-list')) {
            abort(403, 'غير مصرح لك بالوصول');
        }
    }

    private function authorizeClassShowAccess($user): void
    {
        if ($user->usesTeacherAssignmentScope() || $user->usesSupervisorAssignmentScope()) {
            return;
        }

        if (!$user->can('class-show')) {
            abort(403, 'غير مصرح لك بالوصول');
        }
    }
}
