<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    /**
     * عرض قائمة الأسئلة (بنك الأسئلة)
     */
    public function index(Request $request)
    {
        $query = Question::with(['units', 'creator', 'options'])
            ->withCount(['quizzes']);

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // تصفية حسب النوع
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // تصفية حسب الصعوبة
        if ($request->filled('difficulty')) {
            $query->ofDifficulty($request->difficulty);
        }

        // تصفية حسب التصنيف
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        // تصفية حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // تصفية حسب الوحدة
        if ($request->filled('unit_id')) {
            if ($request->unit_id === 'general') {
                $query->general();
            } else {
                $query->inUnits([$request->unit_id]);
            }
        }

        $questions = $query->latest()->paginate(20)->withQueryString();
        
        // للقوائم المنسدلة
        $units = Unit::with('section.subject')->orderBy('title')->get();
        $categories = Question::distinct()->whereNotNull('category')->pluck('category');
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();

        return view('admin.pages.questions.index', compact(
            'questions', 
            'units', 
            'categories',
            'subjects'
        ));
    }

    /**
     * عرض صفحة إنشاء سؤال جديد
     */
    public function create(Request $request)
    {
        $units = Unit::with('section.subject.schoolClass')->orderBy('title')->get();
        $categories = Question::distinct()->whereNotNull('category')->pluck('category');
        $selectedType = $request->type ?? 'single_choice';
        
        // إذا تم تمرير unit_id فسيتم تحديد الوحدة تلقائياً
        $preselectedUnitId = $request->unit_id;
        $preselectedUnit = $preselectedUnitId ? Unit::with('section.subject')->find($preselectedUnitId) : null;
        
        return view('admin.pages.questions.create', compact('units', 'categories', 'selectedType', 'preselectedUnitId', 'preselectedUnit'));
    }

    /**
     * حفظ سؤال جديد
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['case_sensitive'] = $request->has('case_sensitive');
            $data['created_by'] = auth()->id();

            // رفع الصورة
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('questions', 'public');
            }

            // معالجة الوسوم
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = array_filter($data['tags']);
            }

            // إنشاء السؤال
            $question = Question::create($data);

            // ربط الوحدات
            if ($request->filled('units')) {
                $question->units()->sync($request->units);
            }

            // إنشاء الخيارات
            if ($request->filled('options') && $question->has_options) {
                $this->saveOptions($question, $request->options);
            }

            // للأسئلة الرقمية - إنشاء خيار يحتوي على الإجابة الصحيحة
            if ($question->type === 'numerical' && $request->filled('correct_answer')) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $request->correct_answer,
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.questions.index')
                ->with('success', 'تم إنشاء السؤال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء السؤال: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل سؤال
     */
    public function show(string $id)
    {
        $question = Question::with(['units.section.subject', 'creator', 'options', 'quizzes'])
            ->findOrFail($id);
            
        return view('admin.pages.questions.show', compact('question'));
    }

    /**
     * عرض صفحة تعديل سؤال
     */
    public function edit(string $id)
    {
        $question = Question::with(['units', 'options'])->findOrFail($id);
        $units = Unit::with('section.subject.schoolClass')->orderBy('title')->get();
        $categories = Question::distinct()->whereNotNull('category')->pluck('category');
        
        return view('admin.pages.questions.edit', compact('question', 'units', 'categories'));
    }

    /**
     * تحديث سؤال
     */
    public function update(UpdateQuestionRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $question = Question::findOrFail($id);
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['case_sensitive'] = $request->has('case_sensitive');

            // رفع صورة جديدة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                if ($question->image) {
                    Storage::disk('public')->delete($question->image);
                }
                $data['image'] = $request->file('image')->store('questions', 'public');
            } elseif ($request->boolean('remove_image')) {
                if ($question->image) {
                    Storage::disk('public')->delete($question->image);
                }
                $data['image'] = null;
            }

            // معالجة الوسوم
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = array_filter($data['tags']);
            }

            // تحديث السؤال
            $question->update($data);

            // تحديث الوحدات
            $question->units()->sync($request->units ?? []);

            // تحديث الخيارات
            if ($question->has_options) {
                $this->updateOptions($question, $request->options ?? []);
            }

            // للأسئلة الرقمية
            if ($question->type === 'numerical' && $request->filled('correct_answer')) {
                $question->options()->delete();
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $request->correct_answer,
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.questions.index')
                ->with('success', 'تم تحديث السؤال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث السؤال: ' . $e->getMessage());
        }
    }

    /**
     * حذف سؤال
     */
    public function destroy(string $id)
    {
        try {
            $question = Question::findOrFail($id);
            
            // التحقق من عدم استخدام السؤال في اختبارات
            if ($question->quizzes()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف السؤال لأنه مستخدم في اختبارات');
            }

            // حذف الصورة
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }

            // حذف صور الخيارات
            foreach ($question->options as $option) {
                if ($option->image) {
                    Storage::disk('public')->delete($option->image);
                }
            }

            $question->delete();

            return redirect()
                ->route('admin.questions.index')
                ->with('success', 'تم حذف السؤال بنجاح');

        } catch (\Exception $e) {
            Log::error('Error deleting question: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف السؤال: ' . $e->getMessage());
        }
    }

    /**
     * نسخ سؤال
     */
    public function duplicate(string $id)
    {
        try {
            DB::beginTransaction();

            $original = Question::with(['units', 'options'])->findOrFail($id);
            
            // نسخ السؤال
            $newQuestion = $original->replicate();
            $newQuestion->title = $original->title . ' (نسخة)';
            $newQuestion->created_by = auth()->id();
            $newQuestion->save();

            // نسخ الوحدات
            $newQuestion->units()->sync($original->units->pluck('id'));

            // نسخ الخيارات
            foreach ($original->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.questions.edit', $newQuestion->id)
                ->with('success', 'تم نسخ السؤال بنجاح، يمكنك تعديله الآن');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating question: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء نسخ السؤال');
        }
    }

    /**
     * تبديل حالة السؤال
     */
    public function toggleStatus(string $id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->is_active = !$question->is_active;
            $question->save();

            $status = $question->is_active ? 'تفعيل' : 'إلغاء تفعيل';

            return redirect()
                ->back()
                ->with('success', "تم {$status} السؤال بنجاح");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة السؤال');
        }
    }

    /**
     * حفظ خيارات السؤال
     */
    protected function saveOptions(Question $question, array $options): void
    {
        foreach ($options as $index => $optionData) {
            $option = new QuestionOption([
                'question_id' => $question->id,
                'content' => $optionData['content'],
                'is_correct' => isset($optionData['is_correct']),
                'match_target' => $optionData['match_target'] ?? null,
                'correct_order' => $optionData['correct_order'] ?? null,
                'feedback' => $optionData['feedback'] ?? null,
                'order' => $index + 1,
            ]);

            // رفع صورة الخيار
            if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $option->image = $optionData['image']->store('question_options', 'public');
            }

            $option->save();
        }
    }

    /**
     * تحديث خيارات السؤال
     */
    protected function updateOptions(Question $question, array $options): void
    {
        $existingIds = [];

        foreach ($options as $index => $optionData) {
            $data = [
                'content' => $optionData['content'],
                'is_correct' => isset($optionData['is_correct']),
                'match_target' => $optionData['match_target'] ?? null,
                'correct_order' => $optionData['correct_order'] ?? null,
                'feedback' => $optionData['feedback'] ?? null,
                'order' => $index + 1,
            ];

            if (!empty($optionData['id'])) {
                // تحديث خيار موجود
                $option = QuestionOption::find($optionData['id']);
                if ($option) {
                    // رفع صورة جديدة
                    if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($option->image) {
                            Storage::disk('public')->delete($option->image);
                        }
                        $data['image'] = $optionData['image']->store('question_options', 'public');
                    } elseif (isset($optionData['remove_image']) && $optionData['remove_image']) {
                        if ($option->image) {
                            Storage::disk('public')->delete($option->image);
                        }
                        $data['image'] = null;
                    }

                    $option->update($data);
                    $existingIds[] = $option->id;
                }
            } else {
                // إنشاء خيار جديد
                $data['question_id'] = $question->id;
                
                if (isset($optionData['image']) && $optionData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $data['image'] = $optionData['image']->store('question_options', 'public');
                }

                $option = QuestionOption::create($data);
                $existingIds[] = $option->id;
            }
        }

        // حذف الخيارات المحذوفة
        $toDelete = $question->options()->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $option) {
            if ($option->image) {
                Storage::disk('public')->delete($option->image);
            }
            $option->delete();
        }
    }

    /**
     * تصدير الأسئلة
     */
    public function export(Request $request)
    {
        // يمكن إضافة وظيفة التصدير لاحقاً
        return redirect()->back()->with('info', 'ميزة التصدير قيد التطوير');
    }

    /**
     * استيراد الأسئلة
     */
    public function import(Request $request)
    {
        // يمكن إضافة وظيفة الاستيراد لاحقاً
        return redirect()->back()->with('info', 'ميزة الاستيراد قيد التطوير');
    }
}

