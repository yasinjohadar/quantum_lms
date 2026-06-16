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
use App\Services\Storage\MediaStorageService;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:subject-create'])->only(['create', 'store']);
        $this->middleware(['permission:subject-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:subject-delete'])->only('destroy');
        $this->middleware(['permission:subject-enrolled-students'])->only('enrolledStudents');
        $this->middleware(['permission:subject-toggle-status'])->only('toggleStatus');
        $this->middleware(['permission:subject-edit'])->only('reorder');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeSubjectIndexAccess(auth()->user());

        $subjectsQuery = Subject::with(['schoolClass.stage']);

        // إذا كان المستخدم معلم وليس مشرف/مدير
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $allowedSubjectIds = $user->getTeacherAllowedSubjectIds();
            if ($allowedSubjectIds->isEmpty()) {
                $subjectsQuery->whereRaw('1 = 0');
            } else {
                $subjectsQuery->whereIn('id', $allowedSubjectIds);
            }
        }

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

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $subjects = $subjectsQuery->ordered()->paginate($perPage);
        $classes = SchoolClass::with('stage')->ordered()->get();

        // إذا كان طلب Ajax، إرجاع JSON
        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.subjects.partials.table', compact('subjects'))->render();
            $pagination = view('admin.pages.subjects.partials.pagination', compact('subjects'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $subjects->total(),
            ]);
        }

        return view('admin.pages.subjects.index', compact('subjects', 'classes'));
    }

    /**
     * إعادة ترتيب المواد (السحب والإفلات). يستقبل ترتيب المواد في الصفحة الحالية
     * ويعيد تعيين عمود order لجميع المواد المطابقة للفلاتر.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:subjects,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'query' => ['nullable', 'string'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'is_active' => ['nullable', 'string', 'in:0,1'],
        ]);

        $order = array_map('intval', $request->input('order'));
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 25);

        $subjectsQuery = Subject::query();
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $allowedSubjectIds = $user->getTeacherAllowedSubjectIds();
            if ($allowedSubjectIds->isEmpty()) {
                $subjectsQuery->whereRaw('1 = 0');
            } else {
                $subjectsQuery->whereIn('id', $allowedSubjectIds);
            }
        }
        if ($request->filled('query')) {
            $subjectsQuery->search($request->input('query'));
        }
        if ($request->filled('class_id')) {
            $subjectsQuery->byClass($request->input('class_id'));
        }
        if ($request->filled('is_active')) {
            $subjectsQuery->where('is_active', $request->boolean('is_active'));
        }

        $allIds = $subjectsQuery->ordered()->pluck('id')->toArray();
        $orderCount = count($order);

        $offset = ($page - 1) * $perPage;
        $expectedPageIds = array_slice($allIds, $offset, $perPage);
        $sortedOrder = $order;
        sort($sortedOrder);
        $sortedExpected = $expectedPageIds;
        sort($sortedExpected);
        $strictMatch = (count($expectedPageIds) === $orderCount && $sortedOrder === $sortedExpected);

        if (!$strictMatch) {
            $foundOffset = null;
            for ($i = 0; $i <= count($allIds) - $orderCount; $i++) {
                $segment = array_slice($allIds, $i, $orderCount);
                $segSorted = $segment;
                sort($segSorted);
                if ($segSorted === $sortedOrder) {
                    if ($foundOffset !== null) {
                        $foundOffset = null;
                        break;
                    }
                    $foundOffset = $i;
                }
            }
            if ($foundOffset === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'ترتيب المواد لا يطابق الصفحة الحالية.',
                ], 422);
            }
            $offset = $foundOffset;
        }

        $order = array_map('intval', $request->input('order'));
        $newAllIds = array_merge(
            array_slice($allIds, 0, $offset),
            $order,
            array_slice($allIds, $offset + count($order))
        );

        foreach ($newAllIds as $index => $id) {
            Subject::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $classes = SchoolClass::with('stage')->ordered()->get();
        $selectedClassId = $request->input('class_id');
        $selectedClass = $selectedClassId ? SchoolClass::with('stage')->find($selectedClassId) : null;
        
        return view('admin.pages.subjects.create', compact('classes', 'selectedClassId', 'selectedClass'));
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
                    $uploadResult = MediaStorageService::uploadImage($image, 'subjects/images', $imageName);
                    $data['image'] = $uploadResult['path'];
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المادة: ' . $e->getMessage());
                }
            }

            $data['is_active'] = $request->has('is_active');
            $data['display_in_class'] = $request->has('display_in_class');
            $data['order'] = $request->input('order', 0);
            $data['price'] = $request->input('price', 0);
            $data['is_free'] = $request->has('is_free') || $request->input('price', 0) == 0;
            $data['pricing_mode'] = $request->input('pricing_mode', 'inherit');
            $data['is_free_override'] = $request->has('is_free_override');
            $data['free_join_auto_approve'] = $request->has('is_free_override')
                ? $request->boolean('free_join_auto_approve', true)
                : null;
            $data['can_purchase_separately'] = $request->has('can_purchase_separately');
            $data['show_price'] = $request->has('show_price');
            $data = \App\Support\AdminCustomPriceLabelInput::merge(
                $data,
                $request,
                (bool) ($data['is_free_override'] ?? false) || (bool) ($data['is_free'] ?? false)
            );
            $data['default_currency_id'] = $request->input('default_currency_id');

            $subject = Subject::create($data);

            // Invalidate pricing cache
            try {
                app(\App\Services\Pricing\PricingCacheManager::class)->invalidateSubject($subject);
            } catch (\Exception $e) {
                Log::warning('Failed to invalidate pricing cache: ' . $e->getMessage());
            }

            // معالجة الأسعار المتعددة
            if ($request->has('prices')) {
                foreach ($request->prices as $currencyId => $priceData) {
                    if (isset($priceData['price']) && $priceData['price'] > 0) {
                        \App\Models\Price::create([
                            'pricable_type' => get_class($subject),
                            'pricable_id' => $subject->id,
                            'currency_id' => $currencyId,
                            'price' => $priceData['price'],
                            'is_active' => isset($priceData['is_active']),
                        ]);
                    }
                }
            }

            $this->syncPricingModeFromPrices($subject, $request);

            if ($request->filled('return_to_class_id')) {
                return redirect()->route('admin.classes.show', $request->input('return_to_class_id'))
                    ->with('success', 'تم إضافة المادة بنجاح');
            }

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
                'sections.linkedSubjects' => function ($q) {
                    $q->with('schoolClass.stage');
                },
                'sections.clonedFromSection.subject.schoolClass.stage',
                'sections.directLessons' => function ($q) {
                    $q->orderBy('order')
                        ->with([
                            'attachments',
                            'quizzes',
                            'linkedUnits.section.subject',
                            'clonedFromLesson.unit.section.subject',
                            'clonedFromLesson.section.subject',
                        ]);
                },
                'sections.units' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'sections.units.lessons' => function ($q) {
                    $q->orderBy('order')->with('unit.section.subject');
                },
                'sections.units.linkedLessons' => function ($q) {
                    $q->orderBy('lessons.order')->with(['linkedUnits.section.subject', 'unit.section.subject', 'clonedFromLesson.unit.section.subject', 'clonedFromLesson.section.subject']);
                },
                'sections.units.lessons.linkedUnits' => function ($q) {
                    $q->orderBy('order')->with('section.subject');
                },
                'sections.units.lessons.attachments' => function ($q) {
                    $q->orderBy('order');
                },
                'sections.units.lessons.quizzes' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'sections.units.questions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'sections.units.quizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
                'sections.units.linkedQuizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
                'sections.units.mirroredInSections' => function ($q) {
                    $q->with('subject.schoolClass.stage');
                },
                'sections.units.clonedFromUnit.section.subject.schoolClass.stage',
                'sections.mirroredUnits' => function ($q) {
                    $q->orderByPivot('order')->orderBy('title');
                },
                'sections.mirroredUnits.lessons' => function ($q) {
                    $q->orderBy('order')->with('unit.section.subject');
                },
                'sections.mirroredUnits.linkedLessons' => function ($q) {
                    $q->orderBy('lessons.order')->with(['linkedUnits.section.subject', 'unit.section.subject', 'clonedFromLesson.unit.section.subject', 'clonedFromLesson.section.subject']);
                },
                'sections.mirroredUnits.lessons.linkedUnits' => function ($q) {
                    $q->orderBy('order')->with('section.subject');
                },
                'sections.mirroredUnits.lessons.attachments' => function ($q) {
                    $q->orderBy('order');
                },
                'sections.mirroredUnits.lessons.quizzes' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'sections.mirroredUnits.questions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'sections.mirroredUnits.quizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
                'sections.mirroredUnits.linkedQuizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
                'linkedSections' => function ($q) {
                    $q->with('subject.schoolClass.stage')->orderBy('order')->orderBy('title');
                },
                'linkedSections.directLessons' => function ($q) {
                    $q->orderBy('order')
                        ->with([
                            'attachments',
                            'quizzes',
                            'linkedUnits.section.subject',
                            'clonedFromLesson.unit.section.subject',
                            'clonedFromLesson.section.subject',
                        ]);
                },
                'linkedSections.units' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'linkedSections.units.linkedLessons' => function ($q) {
                    $q->orderBy('lessons.order')->with([
                        'linkedUnits.section.subject',
                        'unit.section.subject',
                        'clonedFromLesson.unit.section.subject',
                        'clonedFromLesson.section.subject',
                    ]);
                },
                'linkedSections.units.lessons' => function ($q) {
                    $q->orderBy('order')->with(['unit.section.subject', 'linkedUnits.section.subject', 'clonedFromLesson.unit.section.subject', 'clonedFromLesson.section.subject']);
                },
                'linkedSections.units.lessons.attachments' => function ($q) {
                    $q->orderBy('order');
                },
                'linkedSections.units.quizzes' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'linkedSections.units.questions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'linkedSections.units.mirroredInSections' => function ($q) {
                    $q->with('subject.schoolClass.stage');
                },
                'linkedSections.units.clonedFromUnit.section.subject.schoolClass.stage',
                'linkedSections.mirroredUnits' => function ($q) {
                    $q->orderByPivot('order')->orderBy('title');
                },
                'linkedSections.mirroredUnits.lessons' => function ($q) {
                    $q->orderBy('order')->with('unit.section.subject');
                },
                'linkedSections.mirroredUnits.linkedLessons' => function ($q) {
                    $q->orderBy('lessons.order')->with(['linkedUnits.section.subject', 'unit.section.subject', 'clonedFromLesson.unit.section.subject', 'clonedFromLesson.section.subject']);
                },
                'linkedSections.mirroredUnits.lessons.linkedUnits' => function ($q) {
                    $q->orderBy('order')->with('section.subject');
                },
                'linkedSections.mirroredUnits.lessons.attachments' => function ($q) {
                    $q->orderBy('order');
                },
                'linkedSections.mirroredUnits.lessons.quizzes' => function ($q) {
                    $q->orderBy('order')->orderBy('title');
                },
                'linkedSections.mirroredUnits.questions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'linkedSections.mirroredUnits.quizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
                'linkedSections.mirroredUnits.linkedQuizzes' => function ($q) {
                    $q->with('linkedUnits.section.subject.schoolClass.stage')
                        ->orderBy('order')->orderBy('title');
                },
            ])->findOrFail($id);
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeSubjectShowAccess($user);
            $this->authorizeManagedSubjectAccess($user, $subject);

            // هيكل المواد/أقسام/وحدات لربط الدرس بوحدات إضافية في مودال التعديل
            $linkableSubjectsQuery = Subject::with([
                'schoolClass.stage',
                'sections' => fn ($q) => $q->orderBy('order')->orderBy('title'),
                'sections.units' => fn ($q) => $q->orderBy('order')->orderBy('title'),
            ]);
            if ($user->usesTeacherAssignmentScope()) {
                $classIds = $user->assignedClasses()->pluck('classes.id');
                $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
                $linkableSubjectsQuery->where(function ($q) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $q->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        $q->orWhereIn('id', $subjectIds);
                    }
                });
            } elseif ($user->usesSupervisorAssignmentScope()) {
                $classIds = $user->assignedClassesAsSupervisor()->pluck('classes.id');
                $subjectIds = $user->assignedSubjectsAsSupervisor()->pluck('subjects.id');
                $linkableSubjectsQuery->where(function ($q) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty()) {
                        $q->whereIn('class_id', $classIds);
                    }
                    if ($subjectIds->isNotEmpty()) {
                        $q->orWhereIn('id', $subjectIds);
                    }
                    if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                        $q->whereRaw('1 = 0');
                    }
                });
            }
            $linkableSubjects = $linkableSubjectsQuery->ordered()->get();
            $linkableStructure = $linkableSubjects->map(function ($s) {
                return [
                    'id' => $s->id,
                    'class_id' => $s->class_id ?? null,
                    'name' => $s->name,
                    'class_name' => $s->schoolClass->name ?? '',
                    'stage_name' => $s->schoolClass->stage->name ?? '',
                    'sections' => $s->sections->map(fn ($sec) => [
                        'id' => $sec->id,
                        'title' => $sec->title,
                        'path_title' => $sec->path_title,
                        'units' => $sec->units->map(fn ($u) => ['id' => $u->id, 'title' => $u->title])->values(),
                    ])->values(),
                ];
            })->values();

            $linkableClasses = $linkableSubjects->pluck('schoolClass')->filter()->unique('id')->values()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'stage_name' => $c->stage->name ?? '',
            ])->values();

            return view('admin.pages.subjects.show', compact('subject', 'linkableSubjects', 'linkableStructure', 'linkableClasses'));
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
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedSubjectAccess($user, $subject);
            
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
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedSubjectAccess($user, $subject);
            $data = $request->validated();

            // صورة المادة
            if ($request->hasFile('image')) {
                try {
                    if ($subject->image) {
                        MediaStorageService::delete($subject->image);
                    }

                    $image = $request->file('image');
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $uploadResult = MediaStorageService::uploadImage($image, 'subjects/images', $imageName);
                    $data['image'] = $uploadResult['path'];
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'حدث خطأ أثناء رفع صورة المادة: ' . $e->getMessage());
                }
            } else {
                unset($data['image']);
            }

            $data['is_active'] = $request->has('is_active');
            $data['display_in_class'] = $request->has('display_in_class');
            $data['order'] = $request->input('order', $subject->order);
            $data['price'] = $request->input('price', 0);
            $data['is_free'] = $request->has('is_free') || $request->input('price', 0) == 0;
            $data['pricing_mode'] = $request->input('pricing_mode', $subject->pricing_mode ?? 'inherit');
            $data['is_free_override'] = $request->has('is_free_override');
            $data['free_join_auto_approve'] = $request->has('is_free_override')
                ? $request->boolean('free_join_auto_approve', true)
                : null;
            $data['can_purchase_separately'] = $request->has('can_purchase_separately');
            $data['show_price'] = $request->has('show_price');
            $data = \App\Support\AdminCustomPriceLabelInput::merge(
                $data,
                $request,
                (bool) ($data['is_free_override'] ?? false) || (bool) ($data['is_free'] ?? false)
            );
            $data['default_currency_id'] = $request->input('default_currency_id');

            $subject->update($data);

            // Invalidate pricing cache
            try {
                $cacheManager = app(\App\Services\Pricing\PricingCacheManager::class);
                $cacheManager->invalidateSubject($subject);
                $cacheManager->invalidateOnPriceChange($subject);
            } catch (\Exception $e) {
                Log::warning('Failed to invalidate pricing cache: ' . $e->getMessage());
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
                                    'pricable_type' => get_class($subject),
                                    'pricable_id' => $subject->id,
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

            $this->syncPricingModeFromPrices($subject, $request);

            if ($request->filled('return_to_class_id')) {
                return redirect()->route('admin.classes.show', $request->return_to_class_id)
                    ->with('success', 'تم تحديث المادة بنجاح');
            }
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
            
            // التحقق من التخصيص
            $user = auth()->user();
            $this->authorizeManagedSubjectAccess($user, $subject);

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

    /**
     * تبديل حالة المادة (تفعيل / إلغاء تفعيل)
     */
    public function toggleStatus(Subject $subject)
    {
        try {
            $subject->is_active = !$subject->is_active;
            $subject->save();

            $statusText = $subject->is_active ? 'نشطة' : 'غير نشطة';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة المادة إلى: {$statusText}");
        } catch (\Exception $e) {
            Log::error('Error toggling subject status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'فشل تحديث حالة المادة');
        }
    }

    private function authorizeManagedSubjectAccess($user, Subject $subject): void
    {
        if ($user->usesTeacherAssignmentScope()) {
            if (!$user->isAssignedToSubject($subject->id) && !$user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
            }
            return;
        }

        if ($user->usesSupervisorAssignmentScope()) {
            if (!$user->isAssignedToSubjectAsSupervisor($subject->id) && !$user->isAssignedToClassAsSupervisor($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
            }
        }
    }

    private function authorizeSubjectIndexAccess($user): void
    {
        if ($user->usesTeacherAssignmentScope()) {
            return;
        }

        if (!$user->can('subject-list')) {
            abort(403, 'غير مصرح لك بالوصول');
        }
    }

    private function authorizeSubjectShowAccess($user): void
    {
        if ($user->usesTeacherAssignmentScope() || $user->usesSupervisorAssignmentScope()) {
            return;
        }

        if (!$user->can('subject-show')) {
            abort(403, 'غير مصرح لك بالوصول');
        }
    }

    /**
     * When a subject has an active price and separate purchase, persist pricing_mode as paid
     * so storefront logic matches admin intent (avoids inherit-on-free-class treating it as free).
     */
    private function syncPricingModeFromPrices(Subject $subject, \Illuminate\Http\Request $request): void
    {
        if ($request->has('is_free_override')) {
            $subject->update([
                'pricing_mode' => 'free',
                'can_purchase_separately' => false,
            ]);

            return;
        }

        if (! $request->has('can_purchase_separately')) {
            return;
        }

        $hasPositiveActivePrice = $subject->prices()
            ->active()
            ->where('price', '>', 0)
            ->exists();

        if ($hasPositiveActivePrice) {
            $subject->update(['pricing_mode' => 'paid']);
        }
    }
}
