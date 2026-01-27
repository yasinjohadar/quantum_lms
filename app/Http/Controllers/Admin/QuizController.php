<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class QuizController extends Controller
{
    public function __construct(
        private ReminderService $reminderService
    ) {
        $this->middleware(['permission:quiz-list'])->only('index');
        $this->middleware(['permission:quiz-create'])->only(['create', 'store']);
        $this->middleware(['permission:quiz-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:quiz-delete'])->only('destroy');
        $this->middleware(['permission:quiz-show'])->only('show');
        $this->middleware(['permission:quiz-questions'])->only('questions');
        $this->middleware(['permission:quiz-add-question'])->only('addQuestion');
        $this->middleware(['permission:quiz-remove-question'])->only('removeQuestion');
        $this->middleware(['permission:quiz-reorder-questions'])->only('reorderQuestions');
        $this->middleware(['permission:quiz-update-question-points'])->only('updateQuestionPoints');
        $this->middleware(['permission:quiz-duplicate'])->only('duplicate');
        $this->middleware(['permission:quiz-toggle-publish'])->only('togglePublish');
        $this->middleware(['permission:quiz-preview'])->only('preview');
        $this->middleware(['permission:quiz-results'])->only('results');
        $this->middleware(['permission:quiz-export-results'])->only('exportResults');
        $this->middleware(['permission:quiz-get-subjects-by-class'])->only('getSubjectsByClass');
        $this->middleware(['permission:quiz-get-units'])->only('getUnits');
        $this->middleware(['permission:quiz-approve-review'])->only('approveReview');
        $this->middleware(['permission:quiz-reject-review'])->only('rejectReview');
        $this->middleware(['permission:quiz-submit-for-review'])->only('submitForReview');
    }

    /**
     * عرض قائمة الاختبارات
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['subject.schoolClass', 'unit', 'creator'])
            ->withCount(['questions', 'attempts']);

        // إذا كان المستخدم معلم وليس مشرف/مدير
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            
            $query->whereHas('subject', function($q) use ($classIds, $subjectIds) {
                // المواد من الصفوف المخصصة
                if ($classIds->isNotEmpty()) {
                    $q->whereIn('class_id', $classIds);
                }
                // أو المواد المخصصة مباشرة
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('id', $subjectIds);
                }
            });
        }

        // إذا كان المستخدم مشرف وليس مدير
        if ($user->hasRole('supervisor') && !$user->hasRole('admin')) {
            $query->forSupervisor($user->id);
        }

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // تصفية حسب الصف
        if ($request->filled('class_id')) {
            $query->whereHas('subject', function($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }

        // تصفية حسب المادة
        if ($request->filled('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        // تصفية حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // تصفية حسب النشر
        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published === '1');
        }

        // تصفية حسب حالة المراجعة
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        }

        $quizzes = $query->ordered()->paginate(15)->withQueryString();
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();
        $classes = SchoolClass::with('stage')->orderBy('name')->get();

        // إذا كان طلب Ajax، إرجاع JSON
        if ($request->expectsJson() || $request->ajax()) {
            $html = view('admin.pages.quizzes.partials.table', compact('quizzes'))->render();
            $pagination = view('admin.pages.quizzes.partials.pagination', compact('quizzes'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $quizzes->total(),
            ]);
        }

        return view('admin.pages.quizzes.index', compact('quizzes', 'subjects', 'classes'));
    }

    /**
     * عرض صفحة إنشاء اختبار جديد
     */
    public function create(Request $request)
    {
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();
        $units = collect();
        
        $selectedSubjectId = $request->get('subject_id');
        $selectedUnitId = $request->get('unit_id');
        $selectedLessonId = $request->get('lesson_id');
        
        $selectedSubject = null;
        $selectedUnit = null;
        $selectedClass = null;
        $selectedLesson = null;
        $isFromSubjectOrUnit = false;
        $isFromLesson = false;
        
        // في حال تم تمرير درس، نحمّل الدرس والوحدة والمادة المرتبطة به
        if ($selectedLessonId) {
            $selectedLesson = Lesson::with('unit.section.subject.schoolClass')->find($selectedLessonId);
            if ($selectedLesson && $selectedLesson->unit && $selectedLesson->unit->section) {
                $selectedUnit = $selectedLesson->unit;
                $selectedSubject = $selectedUnit->section->subject;
                $selectedClass = $selectedSubject?->schoolClass;
                $selectedSubjectId = $selectedSubject?->id;
                $selectedUnitId = $selectedUnit?->id;
                $isFromSubjectOrUnit = true;
                $isFromLesson = true;

                // تحميل وحدات المادة المرتبطة بالدرس (للاستمرارية إن احتجنا)
                $units = Unit::whereHas('section', function ($q) use ($selectedSubjectId) {
                    $q->where('subject_id', $selectedSubjectId);
                })->orderBy('title')->get();
            }
        }

        if ($selectedSubjectId) {
            $selectedSubject = Subject::with('schoolClass')->find($selectedSubjectId);
            if ($selectedSubject) {
                $selectedClass = $selectedSubject->schoolClass;
                $isFromSubjectOrUnit = true;
                
                $units = Unit::whereHas('section', function ($q) use ($selectedSubjectId) {
                    $q->where('subject_id', $selectedSubjectId);
                })->orderBy('title')->get();
            }
        }
        
        if ($selectedUnitId) {
            $selectedUnit = Unit::with('section.subject.schoolClass')->find($selectedUnitId);
            if ($selectedUnit) {
                if (!$selectedSubject) {
                    $selectedSubject = $selectedUnit->section->subject;
                    $selectedClass = $selectedSubject->schoolClass;
                }
                $isFromSubjectOrUnit = true;
                
                // إذا لم تكن الوحدات محملة بعد، قم بتحميلها
                if ($units->isEmpty() && $selectedSubject) {
                    $subjectId = $selectedSubject->id;
                    $units = Unit::whereHas('section', function ($q) use ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    })->orderBy('title')->get();
                }
            }
        }

        return view('admin.pages.quizzes.create', compact(
            'subjects', 
            'units', 
            'selectedSubjectId', 
            'selectedUnitId',
            'selectedLessonId',
            'selectedSubject',
            'selectedUnit',
            'selectedClass',
            'selectedLesson',
            'isFromSubjectOrUnit',
            'isFromLesson'
        ));
    }

    /**
     * حفظ اختبار جديد
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // معالجة الـ checkboxes
            $data['show_timer'] = $request->has('show_timer');
            $data['auto_submit'] = $request->has('auto_submit');
            $data['shuffle_questions'] = $request->has('shuffle_questions');
            $data['shuffle_options'] = $request->has('shuffle_options');
            $data['allow_back_navigation'] = $request->has('allow_back_navigation');
            $data['show_result_immediately'] = $request->has('show_result_immediately');
            $data['show_correct_answers'] = $request->has('show_correct_answers');
            $data['show_explanation'] = $request->has('show_explanation');
            $data['show_points_per_question'] = $request->has('show_points_per_question');
            $data['is_active'] = $request->has('is_active');
            $data['requires_password'] = $request->has('requires_password');
            $data['require_webcam'] = $request->has('require_webcam');
            $data['prevent_copy_paste'] = $request->has('prevent_copy_paste');
            $data['fullscreen_required'] = $request->has('fullscreen_required');
            $data['created_by'] = auth()->id();

            // منطق المراجعة: إذا كان المستخدم معلم وليس مشرف أو مدير
            $user = auth()->user();
            $isTeacher = $user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor']);

            if ($isTeacher) {
                // إذا حاول نشر الاختبار، ضعه في حالة قيد المراجعة
                if ($request->has('is_published')) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['is_published'] = false; // لا يتم النشر مباشرة
                } else {
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                    $data['is_published'] = false;
                }
            } else {
                // المشرف والمدير يمكنهم النشر مباشرة
                $data['is_published'] = $request->has('is_published');
                if ($data['is_published']) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_APPROVED;
                } else {
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                }
            }

            // نوع الاختبار والتبعية
            $data['lesson_id'] = $request->input('lesson_id');
            // إن لم يتم تمرير scope نعتبره اختبار وحدة
            $data['scope'] = $request->input('scope', $data['lesson_id'] ? 'lesson' : 'unit');

            // رفع الصورة
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('quizzes', 'public');
            }

            // إنشاء الاختبار
            $quiz = Quiz::create($data);

            DB::commit();

            return redirect()
                ->route('admin.quizzes.questions', $quiz->id)
                ->with('success', 'تم إنشاء الاختبار بنجاح، يمكنك الآن إضافة الأسئلة');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quiz: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل اختبار
     */
    public function show(string $id)
    {
        $quiz = Quiz::with([
            'subject.schoolClass',
            'unit',
            'creator',
            'questions.options',
            'attempts' => function ($q) {
                $q->latest()->limit(10);
            },
            'attempts.user'
        ])->withCount(['questions', 'attempts'])->findOrFail($id);

        // إحصائيات
        $stats = [
            'total_attempts' => $quiz->attempts_count,
            'passed_count' => $quiz->attempts()->passed()->count(),
            'failed_count' => $quiz->attempts()->failed()->count(),
            'average_score' => $quiz->attempts()->completed()->avg('percentage') ?? 0,
            'highest_score' => $quiz->attempts()->completed()->max('percentage') ?? 0,
            'lowest_score' => $quiz->attempts()->completed()->min('percentage') ?? 0,
        ];

        return view('admin.pages.quizzes.show', compact('quiz', 'stats'));
    }

    /**
     * عرض صفحة تعديل اختبار
     */
    public function edit(string $id)
    {
        $quiz = Quiz::findOrFail($id);
        
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            if (!$user->isAssignedToSubject($quiz->subject_id) && 
                !$user->isAssignedToClass($quiz->subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الاختبار');
            }
        }
        
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();
        $units = Unit::whereHas('section', function ($q) use ($quiz) {
            $q->where('subject_id', $quiz->subject_id);
        })->orderBy('title')->get();

        return view('admin.pages.quizzes.edit', compact('quiz', 'subjects', 'units'));
    }

    /**
     * تحديث اختبار
     */
    public function update(UpdateQuizRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $quiz = Quiz::findOrFail($id);
            $data = $request->validated();
            
            // معالجة الـ checkboxes
            $data['show_timer'] = $request->has('show_timer');
            $data['auto_submit'] = $request->has('auto_submit');
            $data['shuffle_questions'] = $request->has('shuffle_questions');
            $data['shuffle_options'] = $request->has('shuffle_options');
            $data['allow_back_navigation'] = $request->has('allow_back_navigation');
            $data['show_result_immediately'] = $request->has('show_result_immediately');
            $data['show_correct_answers'] = $request->has('show_correct_answers');
            $data['show_explanation'] = $request->has('show_explanation');
            $data['show_points_per_question'] = $request->has('show_points_per_question');
            $data['is_active'] = $request->has('is_active');
            $data['requires_password'] = $request->has('requires_password');
            $data['require_webcam'] = $request->has('require_webcam');
            $data['prevent_copy_paste'] = $request->has('prevent_copy_paste');
            $data['fullscreen_required'] = $request->has('fullscreen_required');

            // منطق المراجعة: إذا كان المستخدم معلم وليس مشرف أو مدير
            $user = auth()->user();
            $isTeacher = $user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor']);

            if ($isTeacher) {
                // إذا كان الاختبار في حالة pending أو rejected وكان المعلم يحاول نشره
                if ($request->has('is_published') && in_array($quiz->review_status, [Quiz::REVIEW_STATUS_PENDING, Quiz::REVIEW_STATUS_REJECTED])) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['review_notes'] = null; // مسح الملاحظات القديمة
                    $data['is_published'] = false;
                } elseif ($request->has('is_published')) {
                    // إذا كان draft ويحاول نشره
                    $data['review_status'] = Quiz::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['is_published'] = false;
                } else {
                    // إذا لم يحاول نشره، يبقى draft
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                    $data['is_published'] = false;
                }
            } else {
                // المشرف والمدير
                $data['is_published'] = $request->has('is_published');
                if ($data['is_published'] && $quiz->review_status !== Quiz::REVIEW_STATUS_APPROVED) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_APPROVED;
                } elseif (!$data['is_published']) {
                    $data['review_status'] = Quiz::REVIEW_STATUS_DRAFT;
                }
            }

            // الحفاظ على نوع التبعية (scope) وربط الدرس كما هو حالياً
            // (يمكن توسيع ذلك لاحقاً إذا أردنا تغيير النوع من شاشة التعديل)
            $data['lesson_id'] = $quiz->lesson_id;
            $data['scope'] = $quiz->scope ?? ($quiz->lesson_id ? 'lesson' : 'unit');

            // رفع صورة جديدة
            if ($request->hasFile('image')) {
                if ($quiz->image) {
                    StorageHelper::delete('images', $quiz->image);
                }
                $data['image'] = $request->file('image')->store('quizzes', 'public');
            } elseif ($request->boolean('remove_image')) {
                if ($quiz->image) {
                    StorageHelper::delete('images', $quiz->image);
                }
                $data['image'] = null;
            }

            // إزالة كلمة المرور إذا لم يعد مطلوباً
            if (!$data['requires_password']) {
                $data['password'] = null;
            }

            $quiz->update($data);

            DB::commit();

            return redirect()
                ->route('admin.quizzes.show', $quiz->id)
                ->with('success', 'تم تحديث الاختبار بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating quiz: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * حذف اختبار
     */
    public function destroy(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);

            // التحقق من التخصيص
            $user = auth()->user();
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
                if (!$user->isAssignedToSubject($quiz->subject_id) && 
                    !$user->isAssignedToClass($quiz->subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذا الاختبار');
                }
            }

            // التحقق من وجود محاولات
            if ($quiz->attempts()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف الاختبار لوجود محاولات مسجلة');
            }

            // حذف الصورة
            if ($quiz->image) {
                StorageHelper::delete('images', $quiz->image);
            }

            $quiz->delete();

            return redirect()
                ->route('admin.quizzes.index')
                ->with('success', 'تم حذف الاختبار بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting quiz: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف الاختبار');
        }
    }

    /**
     * صفحة إدارة أسئلة الاختبار
     */
    public function questions(string $id, Request $request)
    {
        $quiz = Quiz::with(['questions.options', 'subject'])->findOrFail($id);
        
        // جلب الصفوف والمواد للفلاتر
        $classes = SchoolClass::with('stage')->ordered()->get();
        $subjects = Subject::with('schoolClass')->active()->ordered()->get();
        
        // إذا كان هناك class_id محدد، فلتر المواد
        if ($request->filled('class_id')) {
            $subjects = $subjects->where('class_id', $request->input('class_id'));
        }
        
        // الأسئلة المتاحة للإضافة
        $availableQuestions = Question::with(['units', 'options'])
            ->active()
            ->whereNotIn('id', $quiz->questions->pluck('id'))
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->ofType($request->type);
            })
            ->when($request->filled('difficulty'), function ($q) use ($request) {
                $q->ofDifficulty($request->difficulty);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->search($request->search);
            })
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->whereHas('units.section.subject', function ($query) use ($request) {
                    $query->where('class_id', $request->class_id);
                });
            })
            ->when($request->filled('subject_id'), function ($q) use ($request) {
                $q->whereHas('units.section', function ($query) use ($request) {
                    $query->where('subject_id', $request->subject_id);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.quizzes.questions', compact('quiz', 'availableQuestions', 'classes', 'subjects'));
    }

    /**
     * إضافة سؤال للاختبار
     */
    public function addQuestion(Request $request, string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $question = Question::with(['options', 'units'])->findOrFail($request->question_id);

            // التحقق من عدم وجود السؤال مسبقاً
            if ($quiz->questions()->where('question_id', $question->id)->exists()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'السؤال موجود مسبقاً في الاختبار'
                    ], 400);
                }
                return redirect()->back()->with('error', 'السؤال موجود مسبقاً في الاختبار');
            }

            $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;

            $quizQuestion = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'order' => $maxOrder + 1,
                'points' => $request->points ?? $question->default_points,
                'is_required' => $request->has('is_required') ?? true,
            ]);

            $quiz->calculateTotalPoints();
            $quiz->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة السؤال للاختبار بنجاح',
                    'question' => [
                        'id' => $question->id,
                        'title' => $question->title,
                        'type' => $question->type,
                        'type_name' => $question->type_name,
                        'type_color' => $question->type_color,
                        'type_icon' => $question->type_icon,
                        'points' => $quizQuestion->points,
                        'order' => $quizQuestion->order,
                    ],
                    'statistics' => [
                        'total_questions' => $quiz->questions->count(),
                        'total_points' => $quiz->total_points,
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'تم إضافة السؤال للاختبار بنجاح');

        } catch (\Exception $e) {
            Log::error('Error adding question to quiz: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إضافة السؤال: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة السؤال');
        }
    }

    /**
     * ربط مجموعة من الأسئلة بالاختبار
     */
    public static function attachQuestionsToQuiz(int $quizId, array $questionIds): int
    {
        try {
            $quiz = Quiz::findOrFail($quizId);
            $attachedCount = 0;

            // الحصول على آخر order
            $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;

            foreach ($questionIds as $questionId) {
                // التحقق من وجود السؤال
                $question = Question::find($questionId);
                if (!$question) {
                    continue;
                }

                // التحقق من عدم وجود السؤال مسبقاً في الاختبار
                if ($quiz->quizQuestions()->where('question_id', $questionId)->exists()) {
                    continue;
                }

                // إضافة السؤال للاختبار
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                    'order' => ++$maxOrder,
                    'points' => $question->default_points,
                    'is_required' => true,
                ]);

                $attachedCount++;
            }

            // إعادة حساب إجمالي النقاط
            if ($attachedCount > 0) {
                $quiz->calculateTotalPoints();
            }

            return $attachedCount;
        } catch (\Exception $e) {
            Log::error('Error attaching questions to quiz: ' . $e->getMessage(), [
                'quiz_id' => $quizId,
                'question_ids' => $questionIds,
            ]);
            throw $e;
        }
    }

    /**
     * إزالة سؤال من الاختبار
     */
    public function removeQuestion(Request $request, string $id, string $questionId)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $question = Question::findOrFail($questionId);
            
            // الحصول على معلومات السؤال قبل الحذف
            $quizQuestion = QuizQuestion::where('quiz_id', $quiz->id)
                ->where('question_id', $questionId)
                ->first();
            
            $points = $quizQuestion ? $quizQuestion->points : 0;
            
            $quiz->questions()->detach($questionId);
            $quiz->calculateTotalPoints();
            $quiz->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إزالة السؤال من الاختبار',
                    'question' => [
                        'id' => $question->id,
                        'title' => $question->title,
                        'type' => $question->type,
                        'type_name' => $question->type_name,
                        'type_color' => $question->type_color,
                        'type_icon' => $question->type_icon,
                        'difficulty' => $question->difficulty,
                        'difficulty_name' => $question->difficulty_name,
                        'default_points' => $question->default_points,
                    ],
                    'statistics' => [
                        'total_questions' => $quiz->questions->count(),
                        'total_points' => $quiz->total_points,
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'تم إزالة السؤال من الاختبار');

        } catch (\Exception $e) {
            Log::error('Error removing question from quiz: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إزالة السؤال: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'حدث خطأ أثناء إزالة السؤال');
        }
    }

    /**
     * تحديث ترتيب الأسئلة
     */
    public function reorderQuestions(Request $request, string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $order = $request->order;

            foreach ($order as $index => $questionId) {
                QuizQuestion::where('quiz_id', $quiz->id)
                    ->where('question_id', $questionId)
                    ->update(['order' => $index + 1]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error reordering questions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث درجة سؤال في الاختبار
     */
    public function updateQuestionPoints(Request $request, string $id, string $questionId)
    {
        try {
            $request->validate([
                'points' => ['required', 'numeric', 'min:0', 'max:1000'],
            ]);

            $quiz = Quiz::findOrFail($id);
            
            QuizQuestion::where('quiz_id', $quiz->id)
                ->where('question_id', $questionId)
                ->update(['points' => $request->points]);

            $quiz->calculateTotalPoints();
            $quiz->refresh();

            // إرجاع JSON response إذا كان الطلب Ajax
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث درجة السؤال بنجاح',
                    'points' => (float) $request->points,
                    'total_points' => (float) $quiz->total_points,
                ]);
            }

            // إرجاع redirect للطلبات العادية
            return redirect()->back()->with('success', 'تم تحديث درجة السؤال');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات المدخلة غير صحيحة',
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating question points: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث الدرجة: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الدرجة');
        }
    }

    /**
     * AJAX endpoint للحصول على المواد حسب الصف
     */
    public function getSubjectsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $subjects = Subject::with('schoolClass.stage')
            ->where('class_id', $request->input('class_id'))
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    /**
     * نسخ اختبار
     */
    public function duplicate(string $id)
    {
        try {
            DB::beginTransaction();

            $original = Quiz::with('questions')->findOrFail($id);
            
            // نسخ الاختبار
            $newQuiz = $original->replicate();
            $newQuiz->title = $original->title . ' (نسخة)';
            $newQuiz->is_published = false;
            $newQuiz->created_by = auth()->id();
            $newQuiz->save();

            // نسخ الأسئلة
            foreach ($original->quizQuestions as $quizQuestion) {
                QuizQuestion::create([
                    'quiz_id' => $newQuiz->id,
                    'question_id' => $quizQuestion->question_id,
                    'order' => $quizQuestion->order,
                    'points' => $quizQuestion->points,
                    'is_required' => $quizQuestion->is_required,
                    'shuffle_options' => $quizQuestion->shuffle_options,
                ]);
            }

            $newQuiz->calculateTotalPoints();

            DB::commit();

            return redirect()
                ->route('admin.quizzes.edit', $newQuiz->id)
                ->with('success', 'تم نسخ الاختبار بنجاح، يمكنك تعديله الآن');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating quiz: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'حدث خطأ أثناء نسخ الاختبار');
        }
    }

    /**
     * تبديل حالة النشر
     */
    public function togglePublish(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $user = auth()->user();
            $isTeacher = $user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor']);
            
            // إذا كان المستخدم معلم، لا يمكنه النشر مباشرة
            if ($isTeacher) {
                return redirect()->back()->with('error', 'يجب إرسال الاختبار للمراجعة أولاً. لا يمكنك النشر مباشرة.');
            }
            
            // التحقق من وجود أسئلة قبل النشر
            if (!$quiz->is_published && $quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن نشر اختبار بدون أسئلة');
            }

            $quiz->is_published = !$quiz->is_published;
            if ($quiz->is_published) {
                $quiz->review_status = Quiz::REVIEW_STATUS_APPROVED;
            }
            $quiz->save();

            $status = $quiz->is_published ? 'نشر' : 'إلغاء نشر';

            return redirect()->back()->with('success', "تم {$status} الاختبار بنجاح");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حالة الاختبار');
        }
    }

    /**
     * معاينة الاختبار
     */
    public function preview(string $id)
    {
        $quiz = Quiz::with(['questions.options', 'subject'])->findOrFail($id);
        
        return view('admin.pages.quizzes.preview', compact('quiz'));
    }

    /**
     * عرض نتائج الاختبار
     */
    public function results(string $id)
    {
        $quiz = Quiz::with(['subject'])->findOrFail($id);
        
        $attempts = $quiz->attempts()
            ->with(['user'])
            ->completed()
            ->latest('finished_at')
            ->paginate(20);

        return view('admin.pages.quizzes.results', compact('quiz', 'attempts'));
    }

    /**
     * تصدير نتائج الاختبار
     */
    public function exportResults(string $id)
    {
        // يمكن إضافة وظيفة التصدير لاحقاً
        return redirect()->back()->with('info', 'ميزة التصدير قيد التطوير');
    }

    /**
     * الحصول على الوحدات حسب المادة (AJAX)
     */
    public function getUnits(Request $request)
    {
        $units = Unit::whereHas('section', function ($q) use ($request) {
            $q->where('subject_id', $request->subject_id);
        })->orderBy('title')->get(['id', 'title']);

        return response()->json($units);
    }

    /**
     * إرسال الاختبار للمراجعة
     */
    public function submitForReview(string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $user = auth()->user();
            
            // التحقق من أن المستخدم معلم
            if (!$user->hasRole('teacher') || $user->hasAnyRole(['admin', 'supervisor'])) {
                abort(403, 'غير مصرح لك بإرسال الاختبار للمراجعة');
            }
            
            // التحقق من وجود أسئلة
            if ($quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن إرسال اختبار بدون أسئلة للمراجعة');
            }
            
            // التحقق من التخصيص
            if (!$user->isAssignedToSubject($quiz->subject_id) && 
                !$user->isAssignedToClass($quiz->subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الاختبار');
            }
            
            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_PENDING,
                'submitted_for_review_at' => now(),
                'review_notes' => null, // مسح الملاحظات القديمة
                'is_published' => false,
            ]);

            return redirect()->back()->with('success', 'تم إرسال الاختبار للمراجعة بنجاح. سيتم مراجعته من قبل المشرف/الأدمن.');

        } catch (\Exception $e) {
            Log::error('Error submitting quiz for review: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال الاختبار للمراجعة');
        }
    }

    /**
     * الموافقة على نشر الاختبار
     */
    public function approveReview(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $quiz = Quiz::findOrFail($id);
            
            // التحقق من الصلاحية (admin/supervisor فقط)
            $user = auth()->user();
            if (!$user->hasAnyRole(['admin', 'supervisor'])) {
                abort(403, 'غير مصرح لك بالموافقة على نشر الاختبار');
            }
            
            // التحقق من وجود أسئلة
            if ($quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن الموافقة على نشر اختبار بدون أسئلة');
            }

            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_APPROVED,
                'is_published' => true,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'تم الموافقة على نشر الاختبار بنجاح.');

        } catch (\Exception $e) {
            Log::error('Error approving quiz review: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء الموافقة على نشر الاختبار');
        }
    }

    /**
     * رفض نشر الاختبار مع ملاحظات
     */
    public function rejectReview(Request $request, string $id)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        try {
            $quiz = Quiz::findOrFail($id);
            
            // التحقق من الصلاحية (admin/supervisor فقط)
            $user = auth()->user();
            if (!$user->hasAnyRole(['admin', 'supervisor'])) {
                abort(403, 'غير مصرح لك برفض نشر الاختبار');
            }

            $quiz->update([
                'review_status' => Quiz::REVIEW_STATUS_REJECTED,
                'is_published' => false,
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'تم رفض نشر الاختبار وتم إرسال الملاحظات للمعلم.');

        } catch (\Exception $e) {
            Log::error('Error rejecting quiz review: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء رفض نشر الاختبار');
        }
    }
}

