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
    ) {}

    /**
     * عرض قائمة الاختبارات
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['subject.schoolClass', 'unit', 'creator'])
            ->withCount(['questions', 'attempts']);

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
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

        $quizzes = $query->ordered()->paginate(15)->withQueryString();
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();

        return view('admin.pages.quizzes.index', compact('quizzes', 'subjects'));
    }

    /**
     * عرض صفحة إنشاء اختبار جديد
     */
    public function create(Request $request)
    {
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();
        $units = collect();
        
        if ($request->filled('subject_id')) {
            $units = Unit::whereHas('section', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            })->orderBy('title')->get();
        }

        return view('admin.pages.quizzes.create', compact('subjects', 'units'));
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
            $data['is_published'] = $request->has('is_published');
            $data['requires_password'] = $request->has('requires_password');
            $data['require_webcam'] = $request->has('require_webcam');
            $data['prevent_copy_paste'] = $request->has('prevent_copy_paste');
            $data['fullscreen_required'] = $request->has('fullscreen_required');
            $data['created_by'] = auth()->id();

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
            $data['is_published'] = $request->has('is_published');
            $data['requires_password'] = $request->has('requires_password');
            $data['require_webcam'] = $request->has('require_webcam');
            $data['prevent_copy_paste'] = $request->has('prevent_copy_paste');
            $data['fullscreen_required'] = $request->has('fullscreen_required');

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
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.quizzes.questions', compact('quiz', 'availableQuestions'));
    }

    /**
     * إضافة سؤال للاختبار
     */
    public function addQuestion(Request $request, string $id)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $question = Question::findOrFail($request->question_id);

            // التحقق من عدم وجود السؤال مسبقاً
            if ($quiz->questions()->where('question_id', $question->id)->exists()) {
                return redirect()->back()->with('error', 'السؤال موجود مسبقاً في الاختبار');
            }

            $maxOrder = $quiz->quizQuestions()->max('order') ?? 0;

            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'order' => $maxOrder + 1,
                'points' => $request->points ?? $question->default_points,
                'is_required' => $request->has('is_required') ?? true,
            ]);

            $quiz->calculateTotalPoints();

            return redirect()->back()->with('success', 'تم إضافة السؤال للاختبار بنجاح');

        } catch (\Exception $e) {
            Log::error('Error adding question to quiz: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة السؤال');
        }
    }

    /**
     * إزالة سؤال من الاختبار
     */
    public function removeQuestion(string $id, string $questionId)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->questions()->detach($questionId);
            $quiz->calculateTotalPoints();

            return redirect()->back()->with('success', 'تم إزالة السؤال من الاختبار');

        } catch (\Exception $e) {
            Log::error('Error removing question from quiz: ' . $e->getMessage());
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

            return redirect()->back()->with('success', 'تم تحديث درجة السؤال');

        } catch (\Exception $e) {
            Log::error('Error updating question points: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الدرجة');
        }
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
            
            // التحقق من وجود أسئلة قبل النشر
            if (!$quiz->is_published && $quiz->questions()->count() === 0) {
                return redirect()->back()->with('error', 'لا يمكن نشر اختبار بدون أسئلة');
            }

            $quiz->is_published = !$quiz->is_published;
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
}

