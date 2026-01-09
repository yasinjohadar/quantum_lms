<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Models\Subject;
use App\Imports\QuestionsImport;
use App\Exports\QuestionsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use Maatwebsite\Excel\Facades\Excel;

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
                    StorageHelper::delete('images', $question->image);
                }
                $data['image'] = $request->file('image')->store('questions', 'public');
            } elseif ($request->boolean('remove_image')) {
                if ($question->image) {
                    StorageHelper::delete('images', $question->image);
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
                StorageHelper::delete('images', $question->image);
            }

            // حذف صور الخيارات
            foreach ($question->options as $option) {
                if ($option->image) {
                    StorageHelper::delete('images', $option->image);
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
                            StorageHelper::delete('images', $option->image);
                        }
                        $imageFile = $optionData['image'];
                        $imageName = time() . '_' . $imageFile->getClientOriginalName();
                        $data['image'] = StorageHelper::store('images', 'question_options/' . $imageName, file_get_contents($imageFile->getRealPath()), 'image') ? 'question_options/' . $imageName : $imageFile->store('question_options', 'public');
                    } elseif (isset($optionData['remove_image']) && $optionData['remove_image']) {
                        if ($option->image) {
                            StorageHelper::delete('images', $option->image);
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
                    $imageFile = $optionData['image'];
                    $imageName = time() . '_' . $imageFile->getClientOriginalName();
                    $imagePath = 'question_options/' . $imageName;
                    $data['image'] = StorageHelper::store('images', $imagePath, file_get_contents($imageFile->getRealPath()), 'image') ? $imagePath : $imageFile->store('question_options', 'public');
                }

                $option = QuestionOption::create($data);
                $existingIds[] = $option->id;
            }
        }

        // حذف الخيارات المحذوفة
        $toDelete = $question->options()->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $option) {
            if ($option->image) {
                StorageHelper::delete('images', $option->image);
            }
            $option->delete();
        }
    }

    /**
     * رفع صورة من TinyMCE
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            ]);

            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('questions/images', $imageName, 'public');
                
                $url = asset('storage/' . $path);
                
                return response()->json([
                    'location' => $url
                ]);
            }

            return response()->json([
                'error' => 'لم يتم رفع الملف'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error uploading image for TinyMCE: ' . $e->getMessage());
            return response()->json([
                'error' => 'حدث خطأ أثناء رفع الصورة: ' . $e->getMessage()
            ], 500);
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
     * تصدير ملف Template للاستيراد
     */
    public function exportTemplate()
    {
        try {
            $data = [
                [
                    'type' => 'single_choice',
                    'title' => 'ما هي عاصمة مصر؟',
                    'content' => 'اختر الإجابة الصحيحة',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'option1' => 'القاهرة',
                    'option1_correct' => 'true',
                    'option2' => 'الإسكندرية',
                    'option2_correct' => 'false',
                    'option3' => 'الجيزة',
                    'option3_correct' => 'false',
                    'option4' => 'أسوان',
                    'option4_correct' => 'false',
                ],
                [
                    'type' => 'multiple_choice',
                    'title' => 'ما هي دول الخليج؟',
                    'content' => 'اختر جميع الإجابات الصحيحة',
                    'difficulty' => 'medium',
                    'points' => 2,
                    'category' => 'جغرافيا',
                    'option1' => 'السعودية',
                    'option1_correct' => 'true',
                    'option2' => 'الإمارات',
                    'option2_correct' => 'true',
                    'option3' => 'مصر',
                    'option3_correct' => 'false',
                    'option4' => 'الكويت',
                    'option4_correct' => 'true',
                ],
                [
                    'type' => 'true_false',
                    'title' => 'القاهرة هي عاصمة مصر',
                    'content' => '',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'option1' => 'صح',
                    'option1_correct' => 'true',
                    'option2' => 'خطأ',
                    'option2_correct' => 'false',
                ],
                [
                    'type' => 'short_answer',
                    'title' => 'ما هي عاصمة السعودية؟',
                    'content' => 'اكتب الإجابة',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'جغرافيا',
                    'case_sensitive' => 'false',
                ],
                [
                    'type' => 'numerical',
                    'title' => 'ما هو ناتج 5 × 5؟',
                    'content' => '',
                    'difficulty' => 'easy',
                    'points' => 1,
                    'category' => 'رياضيات',
                    'correct_answer' => 25,
                    'tolerance' => 0,
                ],
            ];

            return Excel::download(new QuestionsTemplateExport($data), 'questions_template.xlsx');
            
        } catch (\Exception $e) {
            Log::error('Error exporting template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير الملف');
        }
    }

    /**
     * عرض صفحة الاستيراد
     */
    public function showImport()
    {
        return view('admin.pages.questions.import');
    }

    /**
     * استيراد الأسئلة
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10MB max
            'column_mapping' => ['nullable', 'string'], // JSON string
        ]);

        try {
            $columnMapping = [];
            if ($request->filled('column_mapping')) {
                $columnMapping = json_decode($request->column_mapping, true) ?? [];
            }
            
            $import = new QuestionsImport($columnMapping);
            
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            
            $message = "تم استيراد {$successCount} سؤال بنجاح";
            
            if ($errorCount > 0) {
                $message .= "، وحدثت {$errorCount} أخطاء";
                
                // حفظ الأخطاء في الجلسة لعرضها
                session()->flash('import_errors', $errors);
            }
            
            return redirect()
                ->route('admin.questions.index')
                ->with('success', $message)
                ->with('import_summary', [
                    'success' => $successCount,
                    'errors' => $errorCount,
                    'total' => $successCount + $errorCount,
                ]);
                
        } catch (\Exception $e) {
            Log::error('Error importing questions: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage());
        }
    }
}

